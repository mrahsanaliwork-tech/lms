<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Stripe integration via direct HTTP calls (no stripe-php library required).
 * Implements webhook signature verification manually with HMAC-SHA256.
 */
class StripeService
{
    private string $secretKey;
    private string $webhookSecret;
    private string $baseUrl = 'https://api.stripe.com/v1';

    public function __construct()
    {
        $this->secretKey     = config('services.stripe.secret', '');
        $this->webhookSecret = config('services.stripe.webhook_secret', '');
    }

    // ── Checkout Session ───────────────────────────────────────────

    /**
     * Create a Stripe Checkout Session for a course purchase.
     */
    public function createCheckoutSession(
        int    $courseId,
        string $courseTitle,
        int    $amountCents,   // amount in cents
        string $currency,
        string $successUrl,
        string $cancelUrl,
        array  $metadata = [],
    ): array {
        $this->assertConfigured();

        $params = [
            'payment_method_types[]' => 'card',
            'mode'                   => 'payment',
            'success_url'            => $successUrl,
            'cancel_url'             => $cancelUrl,
            'line_items[0][price_data][currency]'                  => $currency,
            'line_items[0][price_data][unit_amount]'               => $amountCents,
            'line_items[0][price_data][product_data][name]'        => $courseTitle,
            'line_items[0][price_data][product_data][description]' => "Course enrollment: {$courseTitle}",
            'line_items[0][quantity]'                              => 1,
        ];

        foreach ($metadata as $key => $value) {
            $params["metadata[{$key}]"] = $value;
        }

        $response = $this->post('/checkout/sessions', $params);

        return $response;
    }

    /**
     * Retrieve a completed checkout session by ID.
     */
    public function retrieveSession(string $sessionId): array
    {
        $this->assertConfigured();
        return $this->get("/checkout/sessions/{$sessionId}");
    }

    // ── Webhook signature verification ────────────────────────────

    /**
     * Verify a Stripe webhook signature and return the decoded event.
     * Throws RuntimeException on invalid signature or timestamp.
     *
     * @throws RuntimeException
     */
    public function constructEvent(string $payload, string $sigHeader): array
    {
        if (!$this->webhookSecret) {
            throw new RuntimeException('STRIPE_WEBHOOK_SECRET is not configured.');
        }

        // Parse Stripe-Signature header: t=timestamp,v1=hash,...
        $parts = [];
        foreach (explode(',', $sigHeader) as $part) {
            [$k, $v] = array_pad(explode('=', $part, 2), 2, '');
            $parts[$k][] = $v;
        }

        $timestamp = (int) ($parts['t'][0] ?? 0);
        $signatures = $parts['v1'] ?? [];

        // Reject events older than 5 minutes to prevent replay attacks
        if (abs(time() - $timestamp) > 300) {
            throw new RuntimeException('Webhook timestamp is too old.');
        }

        $signedPayload = "{$timestamp}.{$payload}";
        $expected      = hash_hmac('sha256', $signedPayload, $this->webhookSecret);

        $valid = false;
        foreach ($signatures as $sig) {
            if (hash_equals($expected, $sig)) {
                $valid = true;
                break;
            }
        }

        if (!$valid) {
            throw new RuntimeException('Webhook signature verification failed.');
        }

        $event = json_decode($payload, true);
        if (!$event) {
            throw new RuntimeException('Invalid webhook payload JSON.');
        }

        return $event;
    }

    // ── Internal HTTP helpers ──────────────────────────────────────

    private function post(string $path, array $params): array
    {
        $response = Http::withBasicAuth($this->secretKey, '')
            ->asForm()
            ->timeout(15)
            ->post($this->baseUrl . $path, $params);

        return $this->handleResponse($response, 'POST', $path);
    }

    private function get(string $path, array $query = []): array
    {
        $response = Http::withBasicAuth($this->secretKey, '')
            ->timeout(15)
            ->get($this->baseUrl . $path, $query);

        return $this->handleResponse($response, 'GET', $path);
    }

    private function handleResponse(\Illuminate\Http\Client\Response $response, string $method, string $path): array
    {
        if ($response->successful()) {
            return $response->json();
        }

        $body    = $response->json();
        $message = $body['error']['message'] ?? 'Unknown Stripe error';
        $code    = $response->status();

        Log::error("Stripe API error [{$method} {$path}]", ['status' => $code, 'message' => $message]);

        if ($code === 402) {
            throw new RuntimeException("Payment declined: {$message}");
        }

        throw new RuntimeException("Stripe error: {$message}");
    }

    private function assertConfigured(): void
    {
        if (!$this->secretKey) {
            throw new RuntimeException('STRIPE_SECRET is not configured.');
        }
    }
}

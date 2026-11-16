<?php

namespace App\Listeners;

use App\Services\AutomationProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Generic queued listener — each event listener below extends this,
 * builds the context array, and delegates to AutomationProcessor.
 */
abstract class ProcessAutomationTrigger implements ShouldQueue
{
    public function __construct(protected AutomationProcessor $processor) {}

    abstract protected function trigger(): string;
    abstract protected function buildContext(object $event): array;

    public function handle(object $event): void
    {
        $this->processor->processWorkflow($this->trigger(), $this->buildContext($event));
    }
}

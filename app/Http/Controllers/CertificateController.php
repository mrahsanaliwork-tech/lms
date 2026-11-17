<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Course;
use App\Services\CertificateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CertificateController extends Controller
{
    public function __construct(private CertificateService $certificateService) {}

    public function index(Request $request)
    {
        $student = $request->user()->student;

        abort_if(!$student, 403, 'Student profile not found.');

        $certificates = Certificate::with('course:id,title,thumbnail')
            ->where('student_id', $student->id)
            ->latest('issued_at')
            ->get()
            ->map(fn($c) => [
                'id'                 => $c->id,
                'certificate_number' => $c->certificate_number,
                'issued_at'          => $c->issued_at->format('F j, Y'),
                'course_title'       => $c->course?->title ?? 'Unknown Course',
                'course_thumbnail'   => $c->course?->thumbnail
                    ? Storage::url($c->course->thumbnail)
                    : null,
                'download_url'       => route('student.certificates.download', $c->id),
                'verify_url'         => url('/certificates/verify/' . $c->certificate_number),
                'has_file'           => $c->file_path && Storage::exists($c->file_path),
            ]);

        return Inertia::render('Student/Certificates/Index', [
            'certificates' => $certificates,
        ]);
    }

    public function download(Request $request, Certificate $certificate)
    {
        // Ensure the student owns this certificate
        $student = $request->user()->student;
        abort_if(!$student || $certificate->student_id !== $student->id, 403);

        // Regenerate if file is missing
        if (!$certificate->file_path || !Storage::exists($certificate->file_path)) {
            $this->certificateService->regeneratePdf($certificate);
            $certificate->refresh();
        }

        return response()->download(
            Storage::path($certificate->file_path),
            'certificate-' . $certificate->certificate_number . '.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    public function verify(string $certNumber)
    {
        $certificate = Certificate::with(['student.user', 'course:id,title,thumbnail'])
            ->where('certificate_number', $certNumber)
            ->first();

        return Inertia::render('Certificates/Verify', [
            'certificate' => $certificate ? [
                'certificate_number' => $certificate->certificate_number,
                'student_name'       => $certificate->student?->user?->name ?? 'Unknown',
                'course_title'       => $certificate->course?->title ?? 'Unknown Course',
                'issued_at'          => $certificate->issued_at->format('F j, Y'),
                'is_valid'           => true,
            ] : null,
            'cert_number' => $certNumber,
        ]);
    }
}

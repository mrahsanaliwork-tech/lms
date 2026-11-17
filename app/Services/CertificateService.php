<?php

namespace App\Services;

use App\Events\CourseCompleted;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Student;
use App\Notifications\CertificateIssued;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class CertificateService
{
    public function generate(Student $student, Course $course): Certificate
    {
        // Idempotent — return existing certificate if already issued
        $existing = Certificate::where('student_id', $student->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $certNumber = $this->generateCertNumber();
        $verifyUrl  = url('/certificates/verify/' . $certNumber);

        // Render PDF
        $pdf = Pdf::loadView('certificates.template', [
            'studentName'       => $student->user->name,
            'courseTitle'       => $course->title,
            'issuedAt'          => now()->format('F j, Y'),
            'certificateNumber' => $certNumber,
            'verifyUrl'         => $verifyUrl,
        ])->setPaper('a4', 'landscape');

        $filename  = 'certificates/' . Str::slug($certNumber) . '.pdf';
        Storage::put($filename, $pdf->output());

        $certificate = Certificate::create([
            'student_id'         => $student->id,
            'course_id'          => $course->id,
            'certificate_number' => $certNumber,
            'issued_at'          => now(),
            'file_path'          => $filename,
        ]);

        // Notify student
        try {
            $student->user->notify(new CertificateIssued(
                $course->title,
                $course->id,
                '/student/certificates'
            ));
        } catch (Throwable) {}

        // Fire CourseCompleted event for automation workflows
        CourseCompleted::dispatch($student->user, $course);

        return $certificate;
    }

    private function generateCertNumber(): string
    {
        do {
            $number = 'CERT-' . now()->format('Y') . '-' . strtoupper(Str::random(6));
        } while (Certificate::where('certificate_number', $number)->exists());

        return $number;
    }

    public function regeneratePdf(Certificate $certificate): void
    {
        $course  = $certificate->course;
        $student = $certificate->student;

        $pdf = Pdf::loadView('certificates.template', [
            'studentName'       => $student->user->name,
            'courseTitle'       => $course->title,
            'issuedAt'          => $certificate->issued_at->format('F j, Y'),
            'certificateNumber' => $certificate->certificate_number,
            'verifyUrl'         => url('/certificates/verify/' . $certificate->certificate_number),
        ])->setPaper('a4', 'landscape');

        Storage::put($certificate->file_path, $pdf->output());
    }
}

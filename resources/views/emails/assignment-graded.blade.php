<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: system-ui, sans-serif; color: #374151; background: #f9fafb; margin: 0; padding: 32px 16px; }
        .card { background: #fff; border-radius: 12px; max-width: 520px; margin: 0 auto; padding: 36px; border: 1px solid #e5e7eb; }
        h1 { font-size: 20px; font-weight: 700; margin: 0 0 4px; color: #111827; }
        .sub { font-size: 13px; color: #6b7280; margin-bottom: 24px; }
        .score { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 20px; text-align: center; margin-bottom: 24px; }
        .score-num { font-size: 40px; font-weight: 800; color: #16a34a; }
        .score-denom { font-size: 20px; color: #6b7280; }
        .feedback { background: #f8fafc; border-left: 3px solid #6366f1; padding: 14px 16px; border-radius: 0 8px 8px 0; font-size: 14px; color: #374151; }
        .footer { margin-top: 28px; font-size: 12px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Assignment graded</h1>
        <p class="sub">{{ $assignment->course?->title }}</p>

        <p style="font-size:15px; margin-bottom: 20px;">
            Your submission for <strong>{{ $assignment->title }}</strong> has been reviewed.
        </p>

        <div class="score">
            <div>
                <span class="score-num">{{ $submission->grade }}</span>
                <span class="score-denom">/ {{ $assignment->max_marks }}</span>
            </div>
            <div style="font-size:13px; color:#6b7280; margin-top:4px;">
                {{ round(($submission->grade / $assignment->max_marks) * 100) }}%
            </div>
        </div>

        @if ($submission->feedback)
        <p style="font-size:13px; color:#6b7280; margin-bottom:8px; font-weight:600; text-transform:uppercase; letter-spacing:.05em;">Instructor feedback</p>
        <div class="feedback">{{ $submission->feedback }}</div>
        @endif

        <div class="footer">
            Graded on {{ $submission->graded_at?->format('M j, Y \a\t g:i A') }}
        </div>
    </div>
</body>
</html>

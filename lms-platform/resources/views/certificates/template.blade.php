<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Certificate of Completion</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'DejaVu Sans', sans-serif;
        background: #fff;
        color: #1a1a2e;
        width: 100%;
        height: 100%;
    }

    .page {
        width: 842px;
        height: 595px;
        position: relative;
        overflow: hidden;
        background: #fff;
    }

    /* Decorative corner borders */
    .border-frame {
        position: absolute;
        inset: 18px;
        border: 1.5px solid #c9a84c;
    }
    .border-frame-inner {
        position: absolute;
        inset: 24px;
        border: 3px solid #1a1a2e;
    }

    /* Gold gradient header band */
    .header-band {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 10px;
        background: #c9a84c;
    }
    .footer-band {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 10px;
        background: #c9a84c;
    }

    /* Corner ornaments */
    .corner {
        position: absolute;
        width: 48px;
        height: 48px;
    }
    .corner-tl { top: 30px; left: 30px; border-top: 3px solid #c9a84c; border-left: 3px solid #c9a84c; }
    .corner-tr { top: 30px; right: 30px; border-top: 3px solid #c9a84c; border-right: 3px solid #c9a84c; }
    .corner-bl { bottom: 30px; left: 30px; border-bottom: 3px solid #c9a84c; border-left: 3px solid #c9a84c; }
    .corner-br { bottom: 30px; right: 30px; border-bottom: 3px solid #c9a84c; border-right: 3px solid #c9a84c; }

    /* Content layout */
    .content {
        position: absolute;
        inset: 36px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 10px 60px;
    }

    .org-name {
        font-size: 11px;
        font-weight: bold;
        letter-spacing: 4px;
        text-transform: uppercase;
        color: #c9a84c;
        margin-bottom: 4px;
    }

    .cert-title {
        font-size: 32px;
        font-weight: bold;
        color: #1a1a2e;
        letter-spacing: 2px;
        margin-bottom: 2px;
        text-transform: uppercase;
    }

    .cert-subtitle {
        font-size: 11px;
        letter-spacing: 5px;
        text-transform: uppercase;
        color: #888;
        margin-bottom: 20px;
    }

    .divider {
        width: 80px;
        height: 2px;
        background: #c9a84c;
        margin: 0 auto 20px auto;
    }

    .presented-to {
        font-size: 10px;
        letter-spacing: 3px;
        text-transform: uppercase;
        color: #888;
        margin-bottom: 8px;
    }

    .student-name {
        font-size: 30px;
        font-style: italic;
        color: #1a1a2e;
        margin-bottom: 16px;
        font-weight: bold;
    }

    .completion-text {
        font-size: 10px;
        color: #555;
        line-height: 1.6;
        margin-bottom: 8px;
        max-width: 480px;
    }

    .course-name {
        font-size: 15px;
        font-weight: bold;
        color: #1a1a2e;
        margin-bottom: 20px;
        padding: 6px 20px;
        border-top: 1px solid #c9a84c;
        border-bottom: 1px solid #c9a84c;
    }

    /* Footer row */
    .footer-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        width: 100%;
        margin-top: 16px;
        padding: 0 20px;
    }

    .sig-block {
        text-align: center;
        width: 150px;
    }

    .sig-line {
        border-top: 1px solid #1a1a2e;
        margin-bottom: 4px;
    }

    .sig-name {
        font-size: 9px;
        font-weight: bold;
        letter-spacing: 1px;
        color: #1a1a2e;
    }

    .sig-title {
        font-size: 8px;
        color: #888;
    }

    .cert-meta {
        text-align: center;
    }

    .cert-number {
        font-size: 8px;
        color: #aaa;
        letter-spacing: 1px;
    }

    .cert-date {
        font-size: 10px;
        font-weight: bold;
        color: #555;
    }

    .cert-label {
        font-size: 8px;
        color: #aaa;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 2px;
    }

    .seal-circle {
        width: 70px;
        height: 70px;
        border: 3px solid #c9a84c;
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        background: #fffdf5;
    }

    .seal-text {
        font-size: 7px;
        font-weight: bold;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: #c9a84c;
        text-align: center;
        line-height: 1.4;
    }

    /* Watermark star ornament */
    .watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 320px;
        color: rgba(201, 168, 76, 0.03);
        z-index: 0;
    }
</style>
</head>
<body>
<div class="page">
    <!-- Decorative bands -->
    <div class="header-band"></div>
    <div class="footer-band"></div>

    <!-- Border frame -->
    <div class="border-frame"></div>
    <div class="border-frame-inner"></div>

    <!-- Corner ornaments -->
    <div class="corner corner-tl"></div>
    <div class="corner corner-tr"></div>
    <div class="corner corner-bl"></div>
    <div class="corner corner-br"></div>

    <!-- Watermark -->
    <div class="watermark">&#9733;</div>

    <!-- Main content -->
    <div class="content">
        <p class="org-name">{{ config('app.name', 'LMS Platform') }}</p>
        <h1 class="cert-title">Certificate</h1>
        <p class="cert-subtitle">of Completion</p>
        <div class="divider"></div>

        <p class="presented-to">This is to certify that</p>
        <p class="student-name">{{ $studentName }}</p>

        <p class="completion-text">
            has successfully completed all requirements and demonstrated proficiency in
        </p>
        <p class="course-name">{{ $courseTitle }}</p>

        <!-- Footer row -->
        <div class="footer-row">
            <!-- Date block -->
            <div class="cert-meta">
                <p class="cert-label">Date Issued</p>
                <p class="cert-date">{{ $issuedAt }}</p>
            </div>

            <!-- Seal -->
            <div>
                <div class="seal-circle">
                    <p class="seal-text">VERIFIED<br>CERTIFICATE<br>&#9733;</p>
                </div>
            </div>

            <!-- Certificate number -->
            <div class="cert-meta">
                <p class="cert-label">Certificate No.</p>
                <p class="cert-date" style="font-size:9px;">{{ $certificateNumber }}</p>
                <p class="cert-number" style="margin-top:3px;">Verify: {{ $verifyUrl }}</p>
            </div>
        </div>
    </div>
</div>
</body>
</html>

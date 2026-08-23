<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>{{ $jobOpening ? 'Apply for ' . $jobOpening->title : 'General Application' }} — Careers</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    @if (global_setting()->google_recaptcha_status == 'active' && global_setting()->google_recaptcha_v2_status == 'active')
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        :root {
            --emerald: #059669;
            --emerald-light: #10b981;
            --emerald-mid: #34d399;
            --emerald-pale: #6ee7b7;
            --emerald-glow: rgba(5, 150, 105, 0.35);
            --teal: #0d9488;
            --amber: #d97706;
            --amber-light: #fbbf24;
            --amber-glow: rgba(217, 119, 6, 0.45);
            --cyan: #06b6d4;
            --dark-bg: #021a12;
            --dark-mid: #031f16;
            --dark-card: rgba(4, 28, 18, 0.72);
            --dark-elevated: rgba(5, 38, 24, 0.85);
            --text-primary: #ecfdf5;
            --text-muted: rgba(209, 250, 229, 0.62);
            --border: rgba(255, 255, 255, 0.08);
            --border-em: rgba(5, 150, 105, 0.22);
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--dark-bg);
            color: var(--text-primary);
            overflow-x: hidden;
        }

        .grid-bg::after {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                linear-gradient(rgba(255, 255, 255, 0.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.025) 1px, transparent 1px);
            background-size: 44px 44px;
            mask-image: radial-gradient(ellipse at center, black 40%, transparent 90%);
        }

        /* ════════════════════ NAV ════════════════════ */
        nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 200;
            height: 70px;
            background: rgba(2, 20, 12, 0.88);
            backdrop-filter: blur(18px);
            border-bottom: 1px solid var(--border);
        }

        .nav-inner {
            max-width: 1180px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            height: 100%;
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .nav-logo-icon {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--emerald), var(--teal));
            display: grid;
            place-items: center;
            box-shadow: 0 6px 20px var(--emerald-glow);
        }

        .nav-logo-icon i {
            font-size: 1.1rem;
            color: #fff;
        }

        .nav-brand-text {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--text-primary);
            letter-spacing: -0.02em;
        }

        .nav-brand-text span {
            color: var(--emerald-mid);
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-outline-nav {
            border: 1px solid var(--border-em);
            color: var(--emerald-pale);
            font-size: 13px;
            font-weight: 700;
            padding: 8px 18px;
            border-radius: 12px;
            text-decoration: none;
            transition: background .2s, border-color .2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-outline-nav:hover {
            background: rgba(5, 150, 105, 0.1);
            border-color: var(--emerald-mid);
        }

        /* ════════════════════ HERO ════════════════════ */
        .apply-hero {
            position: relative;
            overflow: hidden;
            padding-top: 70px;
            background:
                radial-gradient(circle at 12% 20%, rgba(217, 119, 6, 0.14), transparent 22%),
                radial-gradient(circle at 86% 18%, rgba(5, 150, 105, 0.22), transparent 26%),
                radial-gradient(circle at 60% 85%, rgba(6, 182, 212, 0.12), transparent 22%),
                linear-gradient(140deg, #010e09 0%, #021810 40%, #031f14 100%);
        }

        .hero-orb {
            position: absolute;
            border-radius: 999px;
            filter: blur(12px);
            pointer-events: none;
            animation: floatOrb 11s ease-in-out infinite;
        }

        .hero-orb-1 {
            top: 6%;
            left: 4%;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(217, 119, 6, 0.38), transparent);
            opacity: .5;
        }

        .hero-orb-2 {
            bottom: 2%;
            right: 5%;
            width: 240px;
            height: 240px;
            background: radial-gradient(circle, rgba(5, 150, 105, 0.42), transparent);
            opacity: .5;
            animation-delay: -4s;
        }

        .apply-hero-inner {
            max-width: 700px;
            margin: 0 auto;
            padding: 3rem 2rem 2.4rem;
            position: relative;
            z-index: 1;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--emerald-mid);
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 1.4rem;
            transition: gap .2s, color .2s;
        }

        .back-link:hover {
            gap: 12px;
            color: var(--emerald-pale);
        }

        .apply-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 7px 16px;
            border-radius: 999px;
            margin-bottom: 1.1rem;
            background: rgba(5, 150, 105, 0.1);
            border: 1px solid var(--border-em);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--emerald-mid);
        }

        .kicker-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--amber-light), var(--amber));
            box-shadow: 0 0 12px var(--amber-glow);
        }

        .apply-hero h1 {
            font-size: clamp(1.7rem, 3.2vw, 2.5rem);
            font-weight: 800;
            line-height: 1.08;
            letter-spacing: -0.03em;
            color: var(--text-primary);
            margin-bottom: 0.6rem;
        }

        .apply-hero p {
            color: var(--text-muted);
            font-size: 0.98rem;
            line-height: 1.6;
        }

        /* ════════════════════ FORM SECTION ════════════════════ */
        section {
            padding: 0 0 70px;
        }

        .sec-inner {
            max-width: 700px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .form-card {
            background: rgba(4, 25, 16, 0.75);
            border: 1px solid var(--border-em);
            border-radius: 24px;
            padding: clamp(1.6rem, 3vw, 2.6rem);
            backdrop-filter: blur(14px);
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.35);
        }

        /* Alerts */
        .alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 14px 16px;
            border-radius: 14px;
            margin-bottom: 1.4rem;
            font-size: 14px;
            line-height: 1.55;
        }

        .alert i {
            font-size: 1.1rem;
            margin-top: 1px;
            flex-shrink: 0;
        }

        .alert-success {
            background: rgba(5, 150, 105, 0.12);
            border: 1px solid rgba(52, 211, 153, 0.3);
            color: #a7f3d0;
        }

        .alert-success i {
            color: #34d399;
        }

        .alert-danger {
            background: rgba(248, 113, 113, 0.1);
            border: 1px solid rgba(248, 113, 113, 0.28);
            color: #fecaca;
        }

        .alert-danger i {
            color: #f87171;
        }

        .alert-danger ul {
            margin: 4px 0 0 18px;
            padding: 0;
        }

        .alert-danger li {
            margin-bottom: 2px;
        }

        /* Form fields */
        .field {
            margin-bottom: 1.3rem;
        }

        .field label {
            display: block;
            margin-bottom: 9px;
            color: #d1fae5;
            font-size: 0.92rem;
            font-weight: 700;
        }

        .required {
            color: #f87171;
        }

        .field input[type=text],
        .field input[type=email],
        .field input[type=tel],
        .field textarea {
            width: 100%;
            height: 50px;
            border: 1px solid rgba(255, 255, 255, 0.11);
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.06);
            color: #f0fdf4;
            padding: 0 16px;
            font-size: 0.96rem;
            font-family: 'DM Sans', sans-serif;
            transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
        }

        .field textarea {
            height: auto;
            padding: 14px 16px;
            resize: vertical;
            line-height: 1.55;
        }

        .field input::placeholder,
        .field textarea::placeholder {
            color: rgba(209, 250, 229, 0.38);
        }

        .field input:focus,
        .field textarea:focus {
            outline: none;
            border-color: rgba(52, 211, 153, 0.65);
            box-shadow: 0 0 0 5px rgba(52, 211, 153, 0.10);
            transform: translateY(-1px);
        }

        /* File upload */
        .file-drop {
            position: relative;
            border: 1.5px dashed var(--border-em);
            border-radius: 16px;
            background: rgba(5, 150, 105, 0.05);
            padding: 22px 18px;
            text-align: center;
            transition: border-color .2s, background .2s;
        }

        .file-drop:hover {
            border-color: var(--emerald-mid);
            background: rgba(5, 150, 105, 0.08);
        }

        .file-drop i {
            font-size: 1.6rem;
            color: var(--emerald-mid);
            margin-bottom: 8px;
            display: block;
        }

        .file-drop-text {
            font-size: 13.5px;
            color: var(--text-muted);
        }

        .file-drop-text strong {
            color: var(--emerald-pale);
        }

        .file-hint {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 8px;
        }

        .field input[type=file] {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .file-drop {
            position: relative;
            cursor: pointer;
        }

        /* Submit */
        .btn-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            height: 54px;
            border: 0;
            border-radius: 18px;
            background: linear-gradient(135deg, #059669, #0d9488);
            color: #fff;

            font-size: 1rem;
            font-weight: 800;
            letter-spacing: 0.02em;
            box-shadow: 0 18px 34px rgba(5, 150, 105, 0.28);
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 24px 40px rgba(5, 150, 105, 0.38);
        }

        .g-recaptcha {
            margin-top: 6px;
        }

        /* ════════════════════ FOOTER ════════════════════ */
        footer {
            border-top: 1px solid var(--border);
            background: rgba(1, 10, 6, 0.95);
            padding: 56px 0 32px;
        }

        .footer-grid {
            max-width: 1180px;
            margin: 0 auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 3rem;
        }

        .footer-brand p {
            font-size: 14px;
            color: var(--text-muted);
            line-height: 1.7;
            margin-top: .8rem;
            max-width: 280px;
        }

        .footer-col h4 {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--emerald-mid);
            margin-bottom: 1.2rem;
        }

        .footer-col ul {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .footer-col ul a {
            font-size: 14px;
            color: var(--text-muted);
            text-decoration: none;
            transition: color .2s;
        }

        .footer-col ul a:hover {
            color: var(--emerald-pale);
        }

        .footer-bottom {
            max-width: 1180px;
            margin: 2.6rem auto 0;
            padding: 1.6rem 2rem 0;
            border-top: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 13px;
            color: var(--text-muted);
            flex-wrap: wrap;
            gap: .5rem;
        }

        .footer-bottom a {
            color: var(--emerald-mid);
            text-decoration: none;
        }

        /* ════════════════════ ANIMATIONS ════════════════════ */
        @keyframes floatOrb {

            0%,
            100% {
                transform: translateY(0) translateX(0);
            }

            50% {
                transform: translateY(-22px) translateX(12px);
            }
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(22px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .anim-1 {
            animation: fadeUp .6s ease .05s both;
        }

        .anim-2 {
            animation: fadeUp .6s ease .16s both;
        }

        .anim-3 {
            animation: fadeUp .6s ease .27s both;
        }

        /* ════════════════════ RESPONSIVE ════════════════════ */
        @media (max-width: 760px) {
            .footer-grid {
                grid-template-columns: 1fr 1fr;
                gap: 2rem;
            }

            .nav-right .btn-outline-nav span {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .footer-grid {
                grid-template-columns: 1fr;
            }

            .form-card {
                border-radius: 18px;
            }
        }
    </style>
</head>

<body>

    <!-- ══ NAV ════════════════════════════════════════════ -->
    <nav>
        <div class="nav-inner">
            <a href="{{ url('/') }}" class="nav-logo">
                <div class="nav-logo-icon"><i class="ri-team-line"></i></div>
                <span class="nav-brand-text">Speed <span>HRM</span></span>
            </a>
            <div class="nav-right">
                <a href="{{ route('careers.index') }}" class="btn-outline-nav"><i class="ri-briefcase-4-line"></i>
                    <span>All Openings</span></a>
            </div>
        </div>
    </nav>

    <!-- ══ HERO ══════════════════════════════════════════ -->
    <div class="apply-hero grid-bg">
        <div class="hero-orb hero-orb-1"></div>
        <div class="hero-orb hero-orb-2"></div>
        <div class="apply-hero-inner">
            <a href="{{ $jobOpening ? route('careers.show', $jobOpening->public_slug) : route('careers.index') }}"
                class="back-link anim-1">
                <i class="ri-arrow-left-line"></i> {{ $jobOpening ? 'Back to job details' : 'Back to all openings' }}
            </a>

            <div class="apply-badge anim-2">
                <span class="kicker-dot"></span> {{ $jobOpening ? 'Job Application' : 'General Application' }}
            </div>

            <h1 class="anim-3">{{ $jobOpening ? 'Apply for ' . $jobOpening->title : 'General Application' }}</h1>
            <p class="anim-3">Fill in your details below and our team will get back to you shortly.</p>
        </div>
    </div>

    <!-- ══ FORM ═══════════════════════════════════════════ -->
    <section>
        <div class="sec-inner">
            <div class="form-card">

                @if (session('success'))
                    <div class="alert alert-success">
                        <i class="ri-checkbox-circle-fill"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">
                        <i class="ri-error-warning-fill"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <i class="ri-error-warning-fill"></i>
                        <ul>
                            @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('careers.apply.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="job_opening_slug" value="{{ $jobOpening->public_slug ?? '' }}">

                    <div class="field">
                        <label for="name">Full name <span class="required">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}"
                            placeholder="Enter your full name" required>
                    </div>

                    <div class="field">
                        <label for="email">Email <span class="required">*</span></label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                            placeholder="you@example.com" required>
                    </div>

                    <div class="field">
                        <label for="mobile">Mobile</label>
                        <input type="tel" id="mobile" name="mobile" value="{{ old('mobile') }}"
                            placeholder="+966 5X XXX XXXX">
                    </div>

                    <div class="field">
                        <label for="cover_note">Cover note</label>
                        <textarea id="cover_note" name="cover_note" rows="5"
                            placeholder="Tell us a little about yourself and why you're a great fit...">{{ old('cover_note') }}</textarea>
                    </div>

                    <div class="field">
                        <label for="resume">Resume</label>
                        <div class="file-drop">
                            <input type="file" id="resume" name="resume" accept=".pdf,.doc,.docx">
                            <i class="ri-upload-cloud-2-line"></i>
                            <div class="file-drop-text"><strong>Click to upload</strong> or drag your resume here</div>
                            <div class="file-hint">PDF or Word, max 5MB</div>
                        </div>
                    </div>

                    @if (global_setting()->google_recaptcha_status == 'active' && global_setting()->google_recaptcha_v2_status == 'active')
                        <div class="g-recaptcha" data-sitekey="{{ global_setting()->google_recaptcha_v2_site_key }}">
                        </div>
                    @endif

                    <button type="submit" class="btn-submit">
                        <span>Submit Application</span>
                        {{-- <i class="ri-send-plane-line"></i> --}}
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- ══ FOOTER ═════════════════════════════════════════ -->
    <footer>
        <div class="footer-grid">
            <div class="footer-brand">
                <a href="{{ url('/') }}" class="nav-logo" style="display:inline-flex;">
                    <div class="nav-logo-icon"><i class="ri-team-line"></i></div>
                    <span class="nav-brand-text" style="margin-left:12px;">Speed <span>HRM</span></span>
                </a>
                <p>Internal HR and Employee Management System by Speed Logistics Saudi Arabia. Managing people
                    operations from Al Khobar across KSA.</p>
            </div>
            <div class="footer-col">
                <h4>Speed Logi</h4>
                <ul>
                    <li><a href="https://speedlogi.sa/">Main Platform</a></li>
                    <li><a href="https://speedlogi.sa/systems.html">All Systems</a></li>
                    <li><a href="https://dms.speedlogi.sa/login">DMS Login</a></li>
                    <li><a href="https://dobs.speedlogi.sa/register">Driver Registration</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Contact</h4>
                <ul>
                    <li><a href="tel:0138202887">0138202887</a></li>
                    <li><a href="mailto:info@speedlogi.sa">info@speedlogi.sa</a></li>
                    <li><a href="#">Al Mada Tower, Al Khobar</a></li>
                    <li><a href="https://www.linkedin.com/company/speedlogi">LinkedIn</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <span>© 2026 Speed Logistics. All rights reserved.</span>
            <span>Speed HRM — Careers · <a href="https://speedlogi.sa">speedlogi.sa</a></span>
        </div>
    </footer>

    <script>
        (function() {
            const input = document.getElementById('resume');
            const textEl = document.querySelector('.file-drop-text');
            if (!input || !textEl) return;
            const defaultHTML = textEl.innerHTML;
            input.addEventListener('change', function() {
                if (input.files && input.files.length > 0) {
                    textEl.innerHTML = '<strong>Selected:</strong> ' + input.files[0].name;
                } else {
                    textEl.innerHTML = defaultHTML;
                }
            });
        })();
    </script>

</body>

</html>

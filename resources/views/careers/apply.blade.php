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
            --danger: #f87171;
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
            max-width: 760px;
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
            max-width: 760px;
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

        /* ════════════════════ STEP INDICATOR ════════════════════ */
        .ms-steps-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            padding-bottom: 26px;
            margin-bottom: 28px;
            border-bottom: 1px solid var(--border);
            flex-wrap: wrap;
        }

        .ms-step {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .ms-step-circle {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: 2px solid var(--border-em);
            background: rgba(255, 255, 255, 0.02);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            color: var(--text-muted);
            transition: all .25s;
            flex-shrink: 0;
        }

        .ms-step-label {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 600;
            white-space: nowrap;
            transition: color .25s;
        }

        .ms-step.active .ms-step-circle {
            border-color: var(--emerald-mid);
            background: linear-gradient(135deg, var(--emerald), var(--teal));
            color: #fff;
            box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.12);
        }

        .ms-step.active .ms-step-label {
            color: var(--emerald-pale);
        }

        .ms-step.done .ms-step-circle {
            border-color: var(--emerald);
            background: rgba(5, 150, 105, 0.16);
            color: var(--emerald-mid);
        }

        .ms-step.done .ms-step-label {
            color: var(--emerald-mid);
        }

        .ms-step-line {
            height: 2px;
            width: 30px;
            background: var(--border);
            flex-shrink: 0;
            transition: background .25s;
            margin: 0 4px;
        }

        .ms-step-line.done {
            background: var(--emerald);
        }

        .form-step {
            display: none;
        }

        .form-step.active {
            display: block;
            animation: fadeUp .35s ease both;
        }

        .step-title {
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: .3rem;
        }

        .step-subtitle {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 1.4rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.1rem 1.3rem;
        }

        .form-row .field-full {
            grid-column: 1 / -1;
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
            color: var(--danger);
        }

        .field input[type=text],
        .field input[type=email],
        .field input[type=tel],
        .field input[type=number],
        .field input[type=date],
        .field select,
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

        .field select {
            appearance: none;
            -webkit-appearance: none;
            color-scheme: dark;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%2334d399'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            background-size: 18px 18px;
            padding-right: 40px;
            cursor: pointer;
        }

        .field select option {
            background: #04150e;
            color: #f0fdf4;
        }

        .field input[type=date] {
            color-scheme: dark;
            cursor: pointer;
        }

        .field textarea {
            height: auto;
            padding: 14px 16px;
            resize: vertical;
            line-height: 1.55;
            min-height: 110px;
        }

        .field input::placeholder,
        .field textarea::placeholder {
            color: rgba(209, 250, 229, 0.38);
        }

        .field input:focus,
        .field select:focus,
        .field textarea:focus {
            outline: none;
            border-color: rgba(52, 211, 153, 0.65);
            box-shadow: 0 0 0 5px rgba(52, 211, 153, 0.10);
            transform: translateY(-1px);
        }

        .field.has-error input,
        .field.has-error select,
        .field.has-error textarea {
            border-color: var(--danger) !important;
        }

        .field.has-error .file-drop {
            border-color: var(--danger) !important;
        }

        .field-error {
            display: none;
            color: var(--danger);
            font-size: 12px;
            margin-top: 6px;
        }

        .field.has-error .field-error {
            display: block;
        }

        .hidden-field {
            display: none !important;
        }

        /* File upload */
        .file-drop {
            position: relative;
            border: 1.5px dashed var(--border-em);
            border-radius: 16px;
            background: rgba(5, 150, 105, 0.05);
            padding: 18px 16px;
            text-align: center;
            transition: border-color .2s, background .2s;
            cursor: pointer;
        }

        .file-drop:hover {
            border-color: var(--emerald-mid);
            background: rgba(5, 150, 105, 0.08);
        }

        .file-drop i {
            font-size: 1.4rem;
            color: var(--emerald-mid);
            margin-bottom: 6px;
            display: block;
        }

        .file-drop-text {
            font-size: 13px;
            color: var(--text-muted);
        }

        .file-drop-text strong {
            color: var(--emerald-pale);
        }

        .file-hint {
            font-size: 11.5px;
            color: var(--text-muted);
            margin-top: 6px;
        }

        .field input[type=file] {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        /* Nav buttons */
        .ms-nav-buttons {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.6rem;
            gap: 12px;
        }

        .btn-step {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: inherit;
            font-weight: 800;
            font-size: 14px;
            padding: 13px 26px;
            border-radius: 14px;
            cursor: pointer;
            border: 0;
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .btn-step-next,
        .btn-step-submit {
            background: linear-gradient(135deg, #059669, #0d9488);
            color: #fff;
            box-shadow: 0 16px 30px var(--emerald-glow);
            margin-left: auto;
        }

        .btn-step-next:hover,
        .btn-step-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 22px 38px var(--emerald-glow);
        }

        .btn-step-submit:disabled {
            opacity: .65;
            cursor: not-allowed;
            transform: none;
        }

        .btn-step-prev {
            background: transparent;
            border: 1px solid var(--border-em);
            color: var(--emerald-pale);
        }

        .btn-step-prev:hover {
            background: rgba(5, 150, 105, 0.1);
        }

        .g-recaptcha {
            margin: 0 0 1.2rem;
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
                transform: translateY(14px);
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

            .form-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .footer-grid {
                grid-template-columns: 1fr;
            }

            .form-card {
                border-radius: 18px;
            }

            .ms-step-label {
                display: none;
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

                <form method="POST" action="{{ route('careers.apply.store') }}" enctype="multipart/form-data"
                    id="apply-form" novalidate>
                    @csrf
                    <input type="hidden" name="job_opening_slug" value="{{ $jobOpening->public_slug ?? '' }}">

                    {{-- ── STEP INDICATOR ── --}}
                    <div class="ms-steps-wrapper" id="ms-steps-wrapper">
                        <div class="ms-step active" data-step="1">
                            <div class="ms-step-circle">1</div>
                            <span class="ms-step-label">Basic Info</span>
                        </div>
                        <div class="ms-step-line" id="line-1"></div>
                        <div class="ms-step" data-step="2">
                            <div class="ms-step-circle">2</div>
                            <span class="ms-step-label">Documents</span>
                        </div>
                        <div class="ms-step-line" id="line-2"></div>
                        <div class="ms-step" data-step="3">
                            <div class="ms-step-circle">3</div>
                            <span class="ms-step-label">Personal Detail</span>
                        </div>
                        <div class="ms-step-line" id="line-3"></div>
                        <div class="ms-step" data-step="4">
                            <div class="ms-step-circle">4</div>
                            <span class="ms-step-label">Other Detail</span>
                        </div>
                    </div>

                    {{-- ══════════════════════════════
                         STEP 1 — Basic Info
                    ══════════════════════════════ --}}
                    <div class="form-step active" id="form-step-1">
                        <div class="step-title">Basic Info</div>
                        <div class="step-subtitle">Tell us who you are.</div>

                        <div class="form-row">
                            <div class="field">
                                <label for="salutation">Salutation <span class="required">*</span></label>
                                <select id="salutation" name="salutation" required>
                                    <option value="">--</option>
                                    @foreach ($salutations as $salutation)
                                        <option value="{{ $salutation->value }}" @selected(old('salutation') === $salutation->value)>
                                            {{ $salutation->label() }}</option>
                                    @endforeach
                                </select>
                                <div class="field-error">Salutation is required.</div>
                            </div>
                            <div class="field">
                                <label for="name">Full name <span class="required">*</span></label>
                                <input type="text" id="name" name="name" value="{{ old('name') }}"
                                    placeholder="Enter your full name" required>
                                <div class="field-error">Full name is required.</div>
                            </div>
                            <div class="field">
                                <label for="email">Email <span class="required">*</span></label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}"
                                    placeholder="you@example.com" required>
                                <div class="field-error">A valid email is required.</div>
                            </div>
                            <div class="field">
                                <label for="date_of_birth">Date of Birth <span class="required">*</span></label>
                                <input type="date" id="date_of_birth" name="date_of_birth"
                                    value="{{ old('date_of_birth') }}"
                                    max="{{ now()->subYears(15)->format('Y-m-d') }}" required>
                                <div class="field-error">Date of Birth is required.</div>
                            </div>
                            <div class="field field-full">
                                <label for="image">Profile Picture <span class="required">*</span></label>
                                <div class="file-drop" data-target="image">
                                    <input type="file" id="image" name="image"
                                        accept=".png,.jpg,.jpeg,.svg,.bmp" required>
                                    <i class="ri-user-3-line"></i>
                                    <div class="file-drop-text"><strong>Click to upload</strong> or drag your photo
                                        here</div>
                                    <div class="file-hint">PNG or JPG, max 5MB</div>
                                </div>
                                <div class="field-error">Profile picture is required.</div>
                            </div>

                            <div class="field field-full">
                                <label for="resume">Resume / CV <span class="required">*</span></label>
                                <div class="file-drop">
                                    <input type="file" id="resume" name="resume" accept=".pdf,.doc,.docx"
                                        required>
                                    <i class="ri-upload-cloud-2-line"></i>
                                    <div class="file-drop-text"><strong>Click to upload</strong> or drag your resume
                                        here</div>
                                    <div class="file-hint">PDF or Word, max 5MB</div>
                                </div>
                                <div class="field-error">Resume / CV is required.</div>
                            </div>
                        </div>

                        <div class="ms-nav-buttons">
                            <div></div>
                            <button type="button" class="btn-step btn-step-next" data-next="2">
                                Next &nbsp;<i class="ri-arrow-right-line"></i>
                            </button>
                        </div>
                    </div>

                    {{-- ══════════════════════════════
                         STEP 2 — Document Details
                    ══════════════════════════════ --}}
                    <div class="form-step" id="form-step-2">
                        <div class="step-title">Document Details</div>
                        <div class="step-subtitle">Select your employee type — the fields below adjust
                            automatically.</div>

                        <div class="form-row">
                            <div class="field field-full">
                                <label for="employee_type">Employee Type <span class="required">*</span></label>
                                <select id="employee_type" name="employee_type" required>
                                    <option value="expat"
                                        {{ old('employee_type', 'expat') === 'expat' ? 'selected' : '' }}>
                                        Expat</option>
                                    <option value="saudi" {{ old('employee_type') === 'saudi' ? 'selected' : '' }}>
                                        Saudi</option>
                                </select>
                            </div>

                            {{-- EXPAT ONLY --}}
                            <div class="field expat-only-field">
                                <label for="iqama_no">Iqama No <span class="required expat-required">*</span></label>
                                <input type="text" id="iqama_no" name="iqama_no" value="{{ old('iqama_no') }}"
                                    placeholder="Iqama No" required>
                                <div class="field-error">Iqama No is required.</div>
                            </div>
                            <div class="field expat-only-field">
                                <label for="iqama_profession">Iqama Profession <span
                                        class="required expat-required">*</span></label>
                                <input type="text" id="iqama_profession" name="iqama_profession"
                                    value="{{ old('iqama_profession') }}" placeholder="Iqama Profession" required>
                                <div class="field-error">Iqama Profession is required.</div>
                            </div>
                            <div class="field expat-only-field">
                                <label for="iqama_expiry_date">Iqama Expiry Date <span
                                        class="required expat-required">*</span></label>
                                <input type="date" id="iqama_expiry_date" name="iqama_expiry_date"
                                    value="{{ old('iqama_expiry_date') }}" required>
                                <div class="field-error">Iqama Expiry Date is required.</div>
                            </div>
                            <div class="field expat-only-field">
                                <label for="iqama_image">Iqama Image <span
                                        class="required expat-required">*</span></label>
                                <div class="file-drop" data-target="iqama_image">
                                    <input type="file" id="iqama_image" name="iqama_image"
                                        accept=".png,.jpg,.jpeg,.svg,.bmp" required>
                                    <i class="ri-file-shield-2-line"></i>
                                    <div class="file-drop-text"><strong>Click to upload</strong> Iqama image</div>
                                    <div class="file-hint">PNG or JPG, max 5MB</div>
                                </div>
                                <div class="field-error">Iqama Image is required.</div>
                            </div>

                            {{-- SAUDI ONLY --}}
                            <div class="field saudi-only-field hidden-field">
                                <label for="national_id">National ID No <span
                                        class="required saudi-required">*</span></label>
                                <input type="text" id="national_id" name="national_id"
                                    value="{{ old('national_id') }}" placeholder="National ID No" required>
                                <div class="field-error">National ID No is required.</div>
                            </div>
                            <div class="field saudi-only-field hidden-field">
                                <label for="national_id_expiry_date">National ID Expiry Date <span
                                        class="required saudi-required">*</span></label>
                                <input type="date" id="national_id_expiry_date" name="national_id_expiry_date"
                                    value="{{ old('national_id_expiry_date') }}" required>
                                <div class="field-error">National ID Expiry Date is required.</div>
                            </div>
                            <div class="field saudi-only-field hidden-field">
                                <label for="national_id_image">National ID Image <span
                                        class="required saudi-required">*</span></label>
                                <div class="file-drop" data-target="national_id_image">
                                    <input type="file" id="national_id_image" name="national_id_image"
                                        accept=".png,.jpg,.jpeg,.svg,.bmp" required>
                                    <i class="ri-id-card-line"></i>
                                    <div class="file-drop-text"><strong>Click to upload</strong> National ID image
                                    </div>
                                    <div class="file-hint">PNG or JPG, max 5MB</div>
                                </div>
                                <div class="field-error">National ID Image is required.</div>
                            </div>

                            {{-- SHARED --}}
                            <div class="field">
                                <label for="passport_no">Passport No <span class="required">*</span></label>
                                <input type="text" id="passport_no" name="passport_no"
                                    value="{{ old('passport_no') }}" placeholder="Passport No" required>
                                <div class="field-error">Passport No is required.</div>
                            </div>
                            <div class="field">
                                <label for="passport_expiry_date">Passport Expiry Date <span
                                        class="required">*</span></label>
                                <input type="date" id="passport_expiry_date" name="passport_expiry_date"
                                    value="{{ old('passport_expiry_date') }}" required>
                                <div class="field-error">Passport Expiry Date is required.</div>
                            </div>
                            <div class="field field-full">
                                <label for="passport_image">Passport Image <span class="required">*</span></label>
                                <div class="file-drop" data-target="passport_image">
                                    <input type="file" id="passport_image" name="passport_image"
                                        accept=".png,.jpg,.jpeg,.svg,.bmp" required>
                                    <i class="ri-passport-line"></i>
                                    <div class="file-drop-text"><strong>Click to upload</strong> Passport image</div>
                                    <div class="file-hint">PNG or JPG, max 5MB</div>
                                </div>
                                <div class="field-error">Passport Image is required.</div>
                            </div>
                        </div>

                        <div class="ms-nav-buttons">
                            <button type="button" class="btn-step btn-step-prev" data-prev="1">
                                <i class="ri-arrow-left-line"></i> &nbsp;Previous
                            </button>
                            <button type="button" class="btn-step btn-step-next" data-next="3">
                                Next &nbsp;<i class="ri-arrow-right-line"></i>
                            </button>
                        </div>
                    </div>

                    {{-- ══════════════════════════════
                         STEP 3 — Personal Detail
                    ══════════════════════════════ --}}
                    <div class="form-step" id="form-step-3">
                        <div class="step-title">Personal Detail</div>
                        <div class="step-subtitle">A little more about you.</div>

                        <div class="form-row">
                            <div class="field">
                                <label for="country_id">Country <span class="required">*</span></label>
                                <select id="country_id" name="country_id" required>
                                    <option value="">--</option>
                                    @foreach ($countries as $item)
                                        <option value="{{ $item->id }}" @selected(old('country_id') == $item->id)>
                                            {{ $item->nicename }}</option>
                                    @endforeach
                                </select>
                                <div class="field-error">Country is required.</div>
                            </div>
                            <div class="field">
                                <label for="mobile">Mobile <span class="required">*</span></label>
                                <input type="tel" id="mobile" name="mobile" value="{{ old('mobile') }}"
                                    placeholder="+966 5X XXX XXXX" required>
                                <div class="field-error">Mobile is required.</div>
                            </div>
                            <div class="field">
                                <label for="gender">Gender <span class="required">*</span></label>
                                <select id="gender" name="gender" required>
                                    <option value="">--</option>
                                    <option value="male" @selected(old('gender') === 'male')>Male</option>
                                    <option value="female" @selected(old('gender') === 'female')>Female</option>
                                    <option value="others" @selected(old('gender') === 'others')>Others</option>
                                </select>
                                <div class="field-error">Gender is required.</div>
                            </div>
                            <div class="field">
                                <label for="basic_salary">Expected Salary <span class="required">*</span></label>
                                <input type="number" min="0" step="0.01" id="basic_salary"
                                    name="basic_salary" value="{{ old('basic_salary') }}"
                                    placeholder="Expected basic salary" required>
                                <div class="field-error">Expected Salary is required.</div>
                            </div>
                            <div class="field field-full">
                                <label for="address">Address <span class="required">*</span></label>
                                <textarea id="address" name="address" rows="4" placeholder="Your current address" required>{{ old('address') }}</textarea>
                                <div class="field-error">Address is required.</div>
                            </div>
                        </div>

                        <div class="ms-nav-buttons">
                            <button type="button" class="btn-step btn-step-prev" data-prev="2">
                                <i class="ri-arrow-left-line"></i> &nbsp;Previous
                            </button>
                            <button type="button" class="btn-step btn-step-next" data-next="4">
                                Next &nbsp;<i class="ri-arrow-right-line"></i>
                            </button>
                        </div>
                    </div>

                    {{-- ══════════════════════════════
                         STEP 4 — Other Detail (final)
                    ══════════════════════════════ --}}
                    <div class="form-step" id="form-step-4">
                        <div class="step-title">Other Detail</div>
                        <div class="step-subtitle">Almost done.</div>

                        <div class="form-row">
                            <div class="field">
                                <label for="linkedin_username">LinkedIn ID <span class="required">*</span></label>
                                <input type="text" id="linkedin_username" name="linkedin_username"
                                    value="{{ old('linkedin_username') }}" placeholder="linkedin.com/in/yourname"
                                    required>
                                <div class="field-error">LinkedIn ID is required.</div>
                            </div>
                            <div class="field">
                                <label for="marital_status">Marital Status <span class="required">*</span></label>
                                <select id="marital_status" name="marital_status" required>
                                    <option value="">--</option>
                                    @foreach ($maritalStatuses as $status)
                                        <option value="{{ $status->value }}" @selected(old('marital_status') === $status->value)>
                                            {{ $status->label() }}</option>
                                    @endforeach
                                </select>
                                <div class="field-error">Marital Status is required.</div>
                            </div>

                            {{-- NEW: Note field --}}
                            <div class="field field-full">
                                <label for="notes">Other Detail (Optional)</label>
                                <textarea id="notes" name="notes" rows="4" placeholder="Anything else you'd like us to know? (optional)">{{ old('notes') }}</textarea>
                                <div class="field-error">Other detail is invalid.</div>
                            </div>
                        </div>

                        @if (global_setting()->google_recaptcha_status == 'active' && global_setting()->google_recaptcha_v2_status == 'active')
                            <div class="g-recaptcha"
                                data-sitekey="{{ global_setting()->google_recaptcha_v2_site_key }}">
                            </div>
                        @endif

                        <div class="ms-nav-buttons">
                            <button type="button" class="btn-step btn-step-prev" data-prev="3">
                                <i class="ri-arrow-left-line"></i> &nbsp;Previous
                            </button>
                            <button type="submit" class="btn-step btn-step-submit" id="submit-btn">
                                <span>Submit Application</span>
                            </button>
                        </div>
                    </div>
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
            'use strict';

            var totalSteps = 4;
            var currentStep = 1;

            var wrapper = document.getElementById('ms-steps-wrapper');
            var nextBtns = document.querySelectorAll('.btn-step-next');
            var prevBtns = document.querySelectorAll('.btn-step-prev');

            function goToStep(step) {
                document.querySelectorAll('.form-step').forEach(function(el) {
                    el.classList.remove('active');
                });
                document.getElementById('form-step-' + step).classList.add('active');

                for (var s = 1; s <= totalSteps; s++) {
                    var stepEl = wrapper.querySelector('[data-step="' + s + '"]');
                    var lineEl = document.getElementById('line-' + s);
                    stepEl.classList.remove('active', 'done');
                    if (lineEl) lineEl.classList.remove('done');

                    if (s < step) {
                        stepEl.classList.add('done');
                        if (lineEl) lineEl.classList.add('done');
                    } else if (s === step) {
                        stepEl.classList.add('active');
                    }
                }

                currentStep = step;
                document.getElementById('apply-form').scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }

            function setError(fieldId, hasError) {
                var input = document.getElementById(fieldId);
                if (!input) return;
                var wrap = input.closest('.field');
                if (!wrap) return;
                wrap.classList.toggle('has-error', hasError);
            }

            function isEmptyVal(id) {
                var el = document.getElementById(id);
                return !el || el.value.trim() === '';
            }

            function isEmptyFile(id) {
                var el = document.getElementById(id);
                return !el || !el.files || el.files.length === 0;
            }

            function validateStep(step) {
                var ok = true;

                if (step === 1) {
                    ['salutation', 'name', 'email', 'date_of_birth'].forEach(function(id) {
                        if (isEmptyVal(id)) {
                            setError(id, true);
                            ok = false;
                        } else {
                            setError(id, false);
                        }
                    });

                    var email = document.getElementById('email').value.trim();
                    var emailReg = /^[\w.+-]+@[\w-]+\.[a-zA-Z]{2,}$/;
                    if (email === '' || !emailReg.test(email)) {
                        setError('email', true);
                        ok = false;
                    } else {
                        setError('email', false);
                    }

                    ['image', 'resume'].forEach(function(id) {
                        if (isEmptyFile(id)) {
                            setError(id, true);
                            ok = false;
                        } else {
                            setError(id, false);
                        }
                    });
                }

                if (step === 2) {
                    var isSaudi = document.getElementById('employee_type').value === 'saudi';

                    if (isEmptyVal('employee_type')) {
                        setError('employee_type', true);
                        ok = false;
                    } else {
                        setError('employee_type', false);
                    }

                    if (!isSaudi) {
                        ['iqama_no', 'iqama_profession', 'iqama_expiry_date'].forEach(function(id) {
                            if (isEmptyVal(id)) {
                                setError(id, true);
                                ok = false;
                            } else {
                                setError(id, false);
                            }
                        });
                        if (isEmptyFile('iqama_image')) {
                            setError('iqama_image', true);
                            ok = false;
                        } else {
                            setError('iqama_image', false);
                        }
                    } else {
                        ['national_id', 'national_id_expiry_date'].forEach(function(id) {
                            if (isEmptyVal(id)) {
                                setError(id, true);
                                ok = false;
                            } else {
                                setError(id, false);
                            }
                        });
                        if (isEmptyFile('national_id_image')) {
                            setError('national_id_image', true);
                            ok = false;
                        } else {
                            setError('national_id_image', false);
                        }
                    }

                    ['passport_no', 'passport_expiry_date'].forEach(function(id) {
                        if (isEmptyVal(id)) {
                            setError(id, true);
                            ok = false;
                        } else {
                            setError(id, false);
                        }
                    });
                    if (isEmptyFile('passport_image')) {
                        setError('passport_image', true);
                        ok = false;
                    } else {
                        setError('passport_image', false);
                    }
                }

                if (step === 3) {
                    ['country_id', 'mobile', 'gender', 'basic_salary', 'address'].forEach(function(id) {
                        if (isEmptyVal(id)) {
                            setError(id, true);
                            ok = false;
                        } else {
                            setError(id, false);
                        }
                    });
                }

                return ok;
            }

            nextBtns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var next = parseInt(btn.getAttribute('data-next'), 10);
                    var current = next - 1;
                    if (!validateStep(current)) return;
                    goToStep(next);
                });
            });

            prevBtns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    goToStep(parseInt(btn.getAttribute('data-prev'), 10));
                });
            });

            // ── EMPLOYEE TYPE TOGGLE ──────────────────────────────
            function toggleEmployeeTypeFields() {
                var isSaudi = document.getElementById('employee_type').value === 'saudi';

                document.querySelectorAll('.expat-only-field').forEach(function(el) {
                    el.classList.toggle('hidden-field', isSaudi);
                    el.querySelectorAll('input, select').forEach(function(inp) {
                        inp.disabled = isSaudi;
                        inp.required = !isSaudi;
                    });
                });
                document.querySelectorAll('.saudi-only-field').forEach(function(el) {
                    el.classList.toggle('hidden-field', !isSaudi);
                    el.querySelectorAll('input, select').forEach(function(inp) {
                        inp.disabled = !isSaudi;
                        inp.required = isSaudi;
                    });
                });
            }

            document.getElementById('employee_type').addEventListener('change', toggleEmployeeTypeFields);
            toggleEmployeeTypeFields();

            // ── FILE DROP LABEL UPDATE ────────────────────────────
            document.querySelectorAll('.file-drop').forEach(function(drop) {
                var input = drop.querySelector('input[type=file]');
                var textEl = drop.querySelector('.file-drop-text');
                var defaultHTML = textEl.innerHTML;
                input.addEventListener('change', function() {
                    if (input.files && input.files.length > 0) {
                        textEl.innerHTML = '<strong>Selected:</strong> ' + input.files[0].name;
                        var wrap = input.closest('.field');
                        if (wrap) wrap.classList.remove('has-error');
                    } else {
                        textEl.innerHTML = defaultHTML;
                    }
                });
            });

            // ── SUBMIT GUARD (avoid double-submit) ────────────────
            document.getElementById('apply-form').addEventListener('submit', function() {
                var btn = document.getElementById('submit-btn');
                btn.disabled = true;
                btn.querySelector('span').textContent = 'Submitting...';
            });
        })();
    </script>

</body>

</html>

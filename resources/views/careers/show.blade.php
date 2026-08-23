<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $jobOpening->title }} — Careers · Speed HRM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
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

        .btn-nav-cta {
            background: linear-gradient(135deg, var(--emerald), var(--teal));
            color: #fff;
            font-size: 13px;
            font-weight: 800;
            padding: 9px 20px;
            border-radius: 12px;
            text-decoration: none;
            box-shadow: 0 8px 24px var(--emerald-glow);
            transition: transform .2s, box-shadow .2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-nav-cta:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 30px var(--emerald-glow);
        }

        /* ════════════════════ HERO ════════════════════ */
        .job-hero {
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
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, rgba(217, 119, 6, 0.38), transparent);
            opacity: .5;
        }

        .hero-orb-2 {
            bottom: 4%;
            right: 5%;
            width: 260px;
            height: 260px;
            background: radial-gradient(circle, rgba(5, 150, 105, 0.42), transparent);
            opacity: .5;
            animation-delay: -4s;
        }

        .job-hero-inner {
            max-width: 900px;
            margin: 0 auto;
            padding: 3.4rem 2rem 3rem;
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
            margin-bottom: 1.6rem;
            transition: gap .2s, color .2s;
        }

        .back-link:hover {
            gap: 12px;
            color: var(--emerald-pale);
        }

        .job-title-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 7px 16px;
            border-radius: 999px;
            margin-bottom: 1.2rem;
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

        .job-hero h1 {
            font-size: clamp(1.9rem, 3.6vw, 2.9rem);
            font-weight: 800;
            line-height: 1.06;
            letter-spacing: -0.03em;
            color: var(--text-primary);
            margin-bottom: 1.1rem;
        }

        .job-meta-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 2rem;
        }

        .job-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            padding: 7px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border);
            color: var(--text-muted);
        }

        .job-tag i {
            color: var(--emerald-mid);
            font-size: 14px;
        }

        .job-tag.amber i {
            color: var(--amber-light);
        }

        .job-tag.cyan i {
            color: var(--cyan);
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, var(--emerald), var(--teal));
            color: #fff;
            font-size: 14px;
            font-weight: 800;
            padding: 14px 28px;
            border-radius: 16px;
            text-decoration: none;
            box-shadow: 0 16px 32px var(--emerald-glow);
            transition: transform .2s, box-shadow .2s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 22px 40px var(--emerald-glow);
        }

        /* ════════════════════ CONTENT ════════════════════ */
        section {
            padding: 0 0 70px;
        }

        .sec-inner {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .content-panel {
            background: rgba(4, 25, 16, 0.7);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2.2rem 2.4rem;
            margin-bottom: 1.4rem;
            transition: border-color .25s;
        }

        .content-panel:hover {
            border-color: var(--border-em);
        }

        .panel-head {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 1.2rem;
        }

        .panel-icon {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            flex-shrink: 0;
            background: rgba(5, 150, 105, 0.1);
            border: 1px solid var(--border-em);
            display: grid;
            place-items: center;
            font-size: 1.05rem;
            color: var(--emerald-mid);
        }

        .panel-icon.amber {
            background: rgba(217, 119, 6, 0.1);
            border-color: rgba(217, 119, 6, 0.2);
            color: var(--amber-light);
        }

        .panel-title {
            font-size: 1.15rem;
            font-weight: 800;
            letter-spacing: -0.01em;
        }

        /* Prose styling for injected HTML (description / requirements) */
        .job-prose {
            color: var(--text-muted);
            font-size: 15px;
            line-height: 1.75;
        }

        .job-prose p {
            margin: 0 0 1rem;
        }

        .job-prose p:last-child {
            margin-bottom: 0;
        }

        .job-prose ul,
        .job-prose ol {
            margin: 0 0 1rem 1.3rem;
            padding: 0;
        }

        .job-prose li {
            margin-bottom: 0.5rem;
        }

        .job-prose li::marker {
            color: var(--emerald-mid);
        }

        .job-prose strong,
        .job-prose b {
            color: var(--text-primary);
        }

        .job-prose a {
            color: var(--emerald-mid);
        }

        .job-prose h1,
        .job-prose h2,
        .job-prose h3,
        .job-prose h4 {
            color: var(--text-primary);
            margin: 1.2rem 0 0.6rem;
            font-family: 'Syne', sans-serif;
        }

        .job-prose h1:first-child,
        .job-prose h2:first-child,
        .job-prose h3:first-child,
        .job-prose h4:first-child {
            margin-top: 0;
        }

        /* ════════════════════ APPLY CTA ════════════════════ */
        .apply-cta {
            text-align: center;
            padding: 2.6rem 2rem;
            background:
                radial-gradient(circle at 50% 0%, rgba(5, 150, 105, 0.16), transparent 60%),
                rgba(4, 25, 16, 0.7);
            border: 1px solid var(--border-em);
            border-radius: 22px;
        }

        .apply-cta p {
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 1.4rem;
        }

        /* ════════════════════ FOOTER ════════════════════ */
        footer {
            border-top: 1px solid var(--border);
            background: rgba(1, 10, 6, 0.95);
            padding: 64px 0 36px;
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
            margin: 3rem auto 0;
            padding: 1.8rem 2rem 0;
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

        .anim-4 {
            animation: fadeUp .6s ease .38s both;
        }

        /* ════════════════════ RESPONSIVE ════════════════════ */
        @media (max-width: 760px) {
            .content-panel {
                padding: 1.7rem 1.5rem;
            }

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

            .btn-primary {
                width: 100%;
                justify-content: center;
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
                <a href="{{ url('/login') }}" class="btn-nav-cta"><i class="ri-login-box-line"></i> Login</a>
            </div>
        </div>
    </nav>

    <!-- ══ HERO ══════════════════════════════════════════ -->
    <div class="job-hero grid-bg">
        <div class="hero-orb hero-orb-1"></div>
        <div class="hero-orb hero-orb-2"></div>
        <div class="job-hero-inner">
            <a href="{{ route('careers.index') }}" class="back-link anim-1">
                <i class="ri-arrow-left-line"></i> Back to all openings
            </a>

            <div class="job-title-badge anim-2">
                <span class="kicker-dot"></span> Open Position
            </div>

            <h1 class="anim-3">{{ $jobOpening->title }}</h1>

            <div class="job-meta-row anim-3">
                @if ($jobOpening->department?->team_name)
                    <span class="job-tag"><i class="ri-building-4-line"></i>
                        {{ $jobOpening->department->team_name }}</span>
                @endif
                @if ($jobOpening->branch)
                    <span class="job-tag cyan"><i class="ri-map-pin-line"></i> {{ $jobOpening->branch->name }}</span>
                @endif
                @if ($jobOpening->employment_type)
                    <span class="job-tag amber"><i class="ri-time-line"></i> {{ $jobOpening->employment_type }}</span>
                @endif
                @if ($jobOpening->closes_at)
                    <span class="job-tag {{ now()->toDateString() > $jobOpening->closes_at->toDateString() ? 'amber' : '' }}">
                        <i class="ri-calendar-close-line"></i>
                        {{ now()->toDateString() > $jobOpening->closes_at->toDateString() ? 'Closed on' : 'Apply by' }}
                        {{ $jobOpening->closes_at->format('d M Y') }}
                    </span>
                @endif
            </div>

            @php $jobClosed = $jobOpening->closes_at && now()->toDateString() > $jobOpening->closes_at->toDateString(); @endphp

            @if ($jobClosed)
                <span class="btn-primary" style="opacity:.5;cursor:not-allowed;pointer-events:none;">
                    <i class="ri-lock-line"></i> Applications Closed
                </span>
            @else
                <a class="btn-primary" href="{{ route('careers.apply', $jobOpening->public_slug) }}">
                    <i class="ri-send-plane-line"></i> Apply for this Role
                </a>
            @endif
        </div>
    </div>

    <!-- ══ CONTENT ═══════════════════════════════════════════ -->
    <section>
        <div class="sec-inner">
            @if (session('error'))
                <div class="content-panel" style="border-color: rgba(248,113,113,0.28);">
                    <p style="color:#fecaca;font-size:14px;"><i class="ri-error-warning-fill"></i> {{ session('error') }}</p>
                </div>
            @endif
            @if ($jobOpening->description)
                <div class="content-panel">
                    <div class="panel-head">
                        <div class="panel-icon"><i class="ri-file-text-line"></i></div>
                        <div class="panel-title">Description</div>
                    </div>
                    <div class="job-prose">{!! $jobOpening->description !!}</div>
                </div>
            @endif

            @if ($jobOpening->requirements)
                <div class="content-panel">
                    <div class="panel-head">
                        <div class="panel-icon amber"><i class="ri-list-check-3"></i></div>
                        <div class="panel-title">Requirements</div>
                    </div>
                    <div class="job-prose">{!! $jobOpening->requirements !!}</div>
                </div>
            @endif

            <div class="apply-cta">
                <p>Ready to take the next step? Submit your application for this role below.</p>
                @php $jobClosed = $jobOpening->closes_at && now()->toDateString() > $jobOpening->closes_at->toDateString(); @endphp

                @if ($jobClosed)
                    <span class="btn-primary" style="opacity:.5;cursor:not-allowed;pointer-events:none;">
                        <i class="ri-lock-line"></i> Applications Closed
                    </span>
                @else
                    <a class="btn-primary" href="{{ route('careers.apply', $jobOpening->public_slug) }}">
                        <i class="ri-send-plane-line"></i> Apply for this Role
                    </a>
                @endif
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

</body>

</html>

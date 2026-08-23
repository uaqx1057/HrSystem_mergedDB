<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Careers — Speed HRM</title>
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
        .careers-hero {
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
            top: 8%;
            left: 5%;
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, rgba(217, 119, 6, 0.38), transparent);
            opacity: .5;
        }

        .hero-orb-2 {
            bottom: 5%;
            right: 6%;
            width: 280px;
            height: 280px;
            background: radial-gradient(circle, rgba(5, 150, 105, 0.42), transparent);
            opacity: .5;
            animation-delay: -4s;
        }

        .careers-hero-inner {
            max-width: 1180px;
            margin: 0 auto;
            padding: 5rem 2rem 4rem;
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .hero-kicker {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 8px 16px;
            border-radius: 999px;
            margin-bottom: 1.6rem;
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

        .careers-hero h1 {
            font-size: clamp(2.2rem, 4vw, 3.6rem);
            font-weight: 800;
            line-height: 1.02;
            letter-spacing: -0.03em;
            color: var(--text-primary);
            margin-bottom: 1.2rem;
        }

        .careers-hero h1 em {
            font-style: normal;
            color: var(--emerald-mid);
        }

        .hero-desc {
            color: var(--text-muted);
            font-size: 1.05rem;
            line-height: 1.65;
            max-width: 560px;
            margin: 0 auto;
        }

        /* ════════════════════ SECTIONS ════════════════════ */
        section {
            padding: 70px 0;
        }

        .sec-inner {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .sec-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 2.2rem;
            flex-wrap: wrap;
        }

        .sec-title-sm {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .role-count {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.06em;
            color: var(--emerald-mid);
            background: rgba(5, 150, 105, 0.1);
            border: 1px solid var(--border-em);
            padding: 7px 14px;
            border-radius: 999px;
        }

        /* ════════════════════ JOB CARDS ════════════════════ */
        .jobs-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .job-card {
            background: rgba(4, 25, 16, 0.7);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 1.7rem 1.9rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
            transition: border-color .25s, transform .2s, background .25s;
            position: relative;
            overflow: hidden;
        }

        .job-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--emerald), transparent);
            opacity: 0;
            transition: opacity .3s;
        }

        .job-card:hover::before {
            opacity: 1;
        }

        .job-card:hover {
            border-color: var(--border-em);
            transform: translateY(-2px);
            background: var(--dark-elevated);
        }

        .job-card-main {
            min-width: 0;
        }

        .job-title {
            font-size: 1.12rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 8px;
            letter-spacing: -0.01em;
        }

        .job-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .job-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            padding: 5px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border);
            color: var(--text-muted);
        }

        .job-tag i {
            color: var(--emerald-mid);
            font-size: 13px;
        }

        .job-tag.amber i {
            color: var(--amber-light);
        }

        .job-tag.cyan i {
            color: var(--cyan);
        }

        .job-card-action {
            flex-shrink: 0;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, var(--emerald), var(--teal));
            color: #fff;
            font-size: 14px;
            font-weight: 800;
            padding: 13px 24px;
            border-radius: 14px;
            text-decoration: none;
            box-shadow: 0 14px 28px var(--emerald-glow);
            transition: transform .2s, box-shadow .2s;
            white-space: nowrap;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 34px var(--emerald-glow);
        }

        .btn-ghost {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid var(--border-em);
            color: var(--emerald-pale);
            font-size: 14px;
            font-weight: 700;
            padding: 13px 24px;
            border-radius: 14px;
            text-decoration: none;
            transition: background .2s, border-color .2s;
            white-space: nowrap;
        }

        .btn-ghost:hover {
            background: rgba(5, 150, 105, 0.1);
            border-color: var(--emerald-mid);
        }

        /* ════════════════════ EMPTY STATE ════════════════════ */
        .empty-state {
            text-align: center;
            padding: 3.4rem 2rem;
            background: rgba(4, 25, 16, 0.7);
            border: 1px dashed var(--border-em);
            border-radius: 20px;
        }

        .empty-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 1.2rem;
            border-radius: 16px;
            background: rgba(5, 150, 105, 0.1);
            border: 1px solid var(--border-em);
            display: grid;
            place-items: center;
            font-size: 1.6rem;
            color: var(--emerald-mid);
        }

        .empty-state p {
            color: var(--text-muted);
            font-size: 15px;
            max-width: 420px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* ════════════════════ CTA ════════════════════ */
        .cta-section {
            position: relative;
            overflow: hidden;
            text-align: center;
            background:
                radial-gradient(circle at 50% 50%, rgba(5, 150, 105, 0.18), transparent 50%),
                radial-gradient(circle at 20% 20%, rgba(217, 119, 6, 0.10), transparent 30%),
                var(--dark-mid);
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
        }

        .cta-inner {
            position: relative;
            z-index: 1;
            max-width: 620px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .cta-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 1.3rem;
            padding: 8px 18px;
            border-radius: 999px;
            background: rgba(5, 150, 105, 0.1);
            border: 1px solid var(--border-em);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--emerald-mid);
        }

        .cta-title {
            font-size: clamp(1.7rem, 3vw, 2.4rem);
            font-weight: 800;
            line-height: 1.08;
            letter-spacing: -0.03em;
            margin-bottom: 1rem;
        }

        .cta-sub {
            color: var(--text-muted);
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 2rem;
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
            animation: fadeUp .65s ease .05s both;
        }

        .anim-2 {
            animation: fadeUp .65s ease .18s both;
        }

        .anim-3 {
            animation: fadeUp .65s ease .31s both;
        }

        /* ════════════════════ RESPONSIVE ════════════════════ */
        @media (max-width: 760px) {
            .job-card {
                flex-direction: column;
                align-items: stretch;
                text-align: left;
            }

            .job-card-action {
                width: 100%;
            }

            .job-card-action .btn-primary {
                width: 100%;
                justify-content: center;
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

            .sec-head {
                align-items: flex-start;
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
                <a href="{{ url('/') }}" class="btn-outline-nav"><i class="ri-arrow-left-line"></i> <span>Back to
                        Home</span></a>
                <a href="{{ url('/login') }}" class="btn-nav-cta"><i class="ri-login-box-line"></i> Login</a>
            </div>
        </div>
    </nav>

    <!-- ══ HERO ══════════════════════════════════════════ -->
    <div class="careers-hero grid-bg">
        <div class="hero-orb hero-orb-1"></div>
        <div class="hero-orb hero-orb-2"></div>
        <div class="careers-hero-inner">
            <div class="hero-kicker anim-1">
                <span class="kicker-dot"></span>
                Careers · Speed Logi
            </div>
            <h1 class="anim-2">Build the future of <em>logistics</em> with us.</h1>
            <p class="hero-desc anim-3">Explore open roles across the Speed Logi team in Saudi Arabia — from operations
                and fleet to technology and people. Find where you fit.</p>
        </div>
    </div>

    <!-- ══ OPEN POSITIONS ═══════════════════════════════════ -->
    <section id="positions">
        <div class="sec-inner">
            <div class="sec-head">
                <h2 class="sec-title-sm">Open Positions</h2>
                <span class="role-count">{{ $jobOpenings->count() }} {{ Str::plural('role', $jobOpenings->count()) }}
                    open</span>
            </div>

            <div class="jobs-list">
                @forelse($jobOpenings as $job)
                    <div class="job-card">
                        <div class="job-card-main">
                            <div class="job-title">{{ $job->title }}</div>
                            <div class="job-meta">
                                @if ($job->department?->team_name)
                                    <span class="job-tag"><i class="ri-building-4-line"></i>
                                        {{ $job->department->team_name }}</span>
                                @endif
                                @if ($job->branch)
                                    <span class="job-tag cyan"><i class="ri-map-pin-line"></i>
                                        {{ $job->branch->name }}</span>
                                @endif
                                @if ($job->employment_type)
                                    <span class="job-tag amber"><i class="ri-time-line"></i>
                                        {{ $job->employment_type }}</span>
                                @endif
                                @if ($job->closes_at)
                                    <span class="job-tag"><i class="ri-calendar-close-line"></i> Apply by {{ $job->closes_at->format('d M Y') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="job-card-action">
                            <a class="btn-primary" href="{{ route('careers.show', $job->public_slug) }}">
                                View &amp; Apply <i class="ri-arrow-right-line"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="empty-icon"><i class="ri-briefcase-4-line"></i></div>
                        <p>There are no open positions right now, but you can still apply generally below — we'll reach
                            out when a role matches your profile.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- ══ GENERAL APPLICATION CTA ══════════════════════════ -->
    <section class="cta-section">
        <div class="cta-inner">
            <div class="cta-badge"><span class="kicker-dot"></span> Didn't find your fit?</div>
            <h2 class="cta-title">Submit a general application</h2>
            <p class="cta-sub">Don't see a role that fits right now? Send us your details and we'll reach out as soon as
                something matches your skills.</p>
            <a class="btn-primary" href="{{ route('careers.apply.general') }}"
                style="font-size:15px;padding:15px 32px;">
                <i class="ri-send-plane-line"></i> Submit a General Application
            </a>
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

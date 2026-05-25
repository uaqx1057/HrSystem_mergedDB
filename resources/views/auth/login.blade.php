<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR SpeedLogi — Employee Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --emerald: #059669;
            --emerald-light: #10b981;
            --emerald-glow: rgba(5, 150, 105, 0.38);
            --teal: #0d9488;
            --amber: #d97706;
            --amber-glow: rgba(217, 119, 6, 0.52);
            --cyan: #06b6d4;
            --dark-bg: #021a12;
            --dark-mid: #041f16;
            --dark-card: rgba(4, 25, 18, 0.62);
            --text-primary: #ecfdf5;
            --text-muted: rgba(209, 250, 229, 0.68);
            --border: rgba(255, 255, 255, 0.09);
        }

        html, body {
            width: 100%;
            min-height: 100%;
            overflow: hidden;
            font-family: 'DM Sans', sans-serif;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: #021a12;
        }
        .invalid-feedback{
            display:block;
            margin-top:8px;
            color:#fca5a5;
            font-size:.85rem;
        }
        /* ── SCREEN ── */
        .hrs-screen {
            position: relative;
            min-height: 100vh;
            min-height: 100svh;
            height: 100vh;
            height: 100svh;
            display: grid;
            place-items: center;
            width: 100%;
            padding: 16px;
            overflow: hidden;
        }

        .hrs-screen::before {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                radial-gradient(circle at 14% 18%, rgba(217, 119, 6, 0.16), transparent 22%),
                radial-gradient(circle at 84% 20%, rgba(5, 150, 105, 0.26), transparent 24%),
                radial-gradient(circle at 70% 82%, rgba(6, 182, 212, 0.18), transparent 22%),
                linear-gradient(135deg, #010e09 0%, #021810 38%, #031f14 100%);
            transform: scale(1.02);
        }

        .hrs-screen::after {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                linear-gradient(rgba(255, 255, 255, 0.028) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.028) 1px, transparent 1px);
            background-size: 44px 44px;
            mask-image: radial-gradient(circle at center, black 42%, transparent 92%);
            opacity: 0.45;
        }

        /* ── FLOATING ORBS ── */
        .hrs-orb {
            position: absolute;
            border-radius: 999px;
            filter: blur(10px);
            opacity: 0.52;
            animation: hrsFloat 10s ease-in-out infinite;
        }

        .hrs-orb--one {
            top: 8%;
            left: 6%;
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, rgba(217, 119, 6, 0.50), rgba(217, 119, 6, 0));
        }

        .hrs-orb--two {
            right: 8%;
            bottom: 12%;
            width: 280px;
            height: 280px;
            background: radial-gradient(circle, rgba(5, 150, 105, 0.55), rgba(5, 150, 105, 0));
            animation-delay: -3s;
        }

        /* ── SHELL ── */
        .hrs-shell {
            position: relative;
            z-index: 1;
            width: min(1180px, calc(100vw - 32px));
            max-height: calc(100svh - 32px);
            margin: 0 auto;
            border: 1px solid rgba(255, 255, 255, 0.09);
            border-radius: 34px;
            overflow: hidden;
            background: rgba(3, 16, 10, 0.60);
            box-shadow: 0 32px 100px rgba(1, 8, 5, 0.48);
            backdrop-filter: blur(14px);
            animation: hrsShellIn 0.75s cubic-bezier(0.2, 0.9, 0.2, 1) both;
        }

        /* ── PANEL ── */
        .hrs-panel {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(360px, 0.95fr);
            min-height: 0;
            height: min(760px, calc(100svh - 32px));
        }

        /* ── BRAND SIDE (light) ── */
        .hrs-brand {
            position: relative;
            padding: clamp(22px, 2.8vw, 42px);
            color: #0a1f14;
            background:
                radial-gradient(circle at top left, rgba(5, 150, 105, 0.10), transparent 28%),
                linear-gradient(180deg, rgba(255, 255, 255, 0.97), rgba(240, 253, 244, 0.93));
            overflow: hidden;
        }

        .hrs-brand::before {
            content: '';
            position: absolute;
            inset: auto -12% -18% auto;
            width: 340px;
            height: 340px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(217, 119, 6, 0.16), rgba(217, 119, 6, 0));
        }

        .hrs-brand::after {
            content: '';
            position: absolute;
            inset: 14% auto auto -10%;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(5, 150, 105, 0.12), rgba(5, 150, 105, 0));
        }

        .hrs-brand-inner {
            position: relative;
            z-index: 1;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            gap: clamp(16px, 2vh, 24px);
        }

        /* ── BADGE ── */
        .hrs-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            width: fit-content;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(5, 150, 105, 0.08);
            color: #065f46;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            backdrop-filter: blur(12px);
            
        }

        .hrs-badge-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            box-shadow: 0 0 18px rgba(217, 119, 6, 0.55);
        }

        /* ── LOGO MARK ── */
        .hrs-logo-mark {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-top: 4px;
            animation: hrsBrandRise 0.8s ease-out 0.12s both;
        }

        .hrs-logo-icon {
            width: 56px;
            height: 56px;
            border-radius: 18px;
            background: linear-gradient(135deg, #059669, #0d9488);
            display: grid;
            place-items: center;
            box-shadow: 0 8px 24px rgba(5, 150, 105, 0.32);
            flex: 0 0 auto;
        }

        .hrs-logo-icon i {
            font-size: 1.6rem;
            color: #fff;
        }

        .hrs-logo-text {
            display: flex;
            flex-direction: column;
            line-height: 1;
        }

        .hrs-logo-name {
            
            font-size: 1.9rem;
            font-weight: 800;
            color: #064e3b;
            letter-spacing: -0.04em;
        }

        .hrs-logo-sub {
            font-size: 0.78rem;
            font-weight: 600;
            color: #059669;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            margin-top: 2px;
        }

        /* ── TITLE & TEXT ── */
        .hrs-title {
            margin: 14px 0 10px;
            
            font-size: clamp(2rem, 3.4vw, 3.7rem);
            line-height: 0.96;
            font-weight: 800;
            letter-spacing: -0.04em;
            color: #064e3b;
        }

        .hrs-title span {
            color: #059669;
        }

        .hrs-text {
            max-width: 520px;
            margin: 0;
            color: #4b7a62;
            font-size: 0.95rem;
            line-height: 1.55;
        }

        /* ── HIGHLIGHTS ── */
        .hrs-highlights {
            display: grid;
            gap: 10px;
            margin-top: 18px;
        }

        .hrs-highlight {
            display: flex;
            gap: 14px;
            align-items: flex-start;
            padding: 14px 16px;
            border: 1px solid rgba(5, 150, 105, 0.14);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: blur(10px);
        }

        .hrs-highlight-icon {
            width: 42px;
            height: 42px;
            flex: 0 0 auto;
            display: grid;
            place-items: center;
            border-radius: 14px;
            background: linear-gradient(135deg, rgba(217, 119, 6, 0.26), rgba(5, 150, 105, 0.22));
            font-size: 1.1rem;
        }

        .hrs-highlight strong {
            display: block;
            margin-bottom: 4px;
            font-size: 0.98rem;
            color: #1a3d2b;
            font-weight: 700;
        }

        .hrs-highlight span {
            color: #4b7a62;
            font-size: 0.88rem;
            line-height: 1.45;
        }

        /* ── FORM SIDE (dark) ── */
        .hrs-form-wrap {
            position: relative;
            display: grid;
            align-items: center;
            padding: clamp(22px, 2.8vw, 40px);
            color: var(--text-primary);
            background:
                radial-gradient(circle at 82% 18%, rgba(217, 119, 6, 0.13), transparent 24%),
                radial-gradient(circle at 10% 82%, rgba(6, 182, 212, 0.10), transparent 22%),
                linear-gradient(180deg, rgba(4, 28, 18, 0.96), rgba(2, 18, 11, 0.94));
        }

        .hrs-form-card {
            max-width: 430px;
            margin: 0 auto;
            animation: hrsFormIn 0.75s cubic-bezier(0.2, 0.9, 0.2, 1) 0.08s both;
        }

        .hrs-kicker {
            margin-bottom: 10px;
            color: #34d399;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            
        }

        .hrs-heading {
            margin: 0;
            color: #f0fdf4;
            
            font-size: clamp(1.8rem, 3vw, 2.6rem);
            line-height: 1.02;
            font-weight: 800;
            letter-spacing: -0.04em;
        }

        .hrs-subtitle {
            margin: 10px 0 0;
            color: rgba(209, 250, 229, 0.72);
            font-size: 0.95rem;
            line-height: 1.55;
        }

        /* ── FORM ── */
        .hrs-form {
            margin-top: 24px;
        }

        .hrs-label-row,
        .hrs-remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .hrs-label {
            display: inline-block;
            margin-bottom: 10px;
            color: #d1fae5;
            font-size: 0.92rem;
            font-weight: 700;
        }

        .hrs-required {
            color: #f87171;
        }

        .hrs-input-wrap {
            position: relative;
        }

        .hrs-input,
        .hrs-password-input {
            width: 100%;
            height: 54px;
            border: 1px solid rgba(255, 255, 255, 0.11);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.07);
            color: #f0fdf4;
            padding: 0 18px;
            font-size: 0.98rem;
            font-family: 'DM Sans', sans-serif;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }

        .hrs-password-input {
            padding-right: 58px;
        }

        .hrs-input:focus,
        .hrs-password-input:focus {
            outline: none;
            border-color: rgba(52, 211, 153, 0.65);
            box-shadow: 0 0 0 5px rgba(52, 211, 153, 0.10);
            transform: translateY(-1px);
        }

        .hrs-input::placeholder,
        .hrs-password-input::placeholder {
            color: rgba(209, 250, 229, 0.40);
        }

        .hrs-password-toggle {
            position: absolute;
            top: 50%;
            right: 10px;
            width: 40px;
            height: 40px;
            display: grid;
            place-items: center;
            border: 0;
            border-radius: 12px;
            background: transparent;
            color: #6ee7b7;
            transform: translateY(-50%);
            cursor: pointer;
            transition: background 0.2s ease, color 0.2s ease;
        }

        .hrs-password-toggle:hover {
            background: rgba(52, 211, 153, 0.12);
            color: #ffffff;
        }

        .hrs-link {
            color: #6ee7b7;
            text-decoration: none;
            font-weight: 700;
        }

        .hrs-link:hover {
            color: #a7f3d0;
        }

        .hrs-checkbox {
            width: 18px;
            height: 18px;
            margin-top: 0.15rem;
            border-color: rgba(255, 255, 255, 0.30);
            background-color: rgba(255, 255, 255, 0.07);
            accent-color: #059669;
            cursor: pointer;
        }

        .hrs-checkbox-label {
            color: rgba(209, 250, 229, 0.72);
            font-size: 0.95rem;
            cursor: pointer;
        }

        .hrs-submit {
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

        .hrs-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 24px 40px rgba(5, 150, 105, 0.38);
        }

        .hrs-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 20px 0 0;
            color: rgba(209, 250, 229, 0.38);
            font-size: 0.82rem;
        }

        .hrs-divider::before,
        .hrs-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255, 255, 255, 0.08);
        }

        .hrs-mb3 { margin-bottom: 0.75rem; }
        .hrs-mb4 { margin-bottom: 1rem; }
        .hrs-mt2 { margin-top: 0.5rem; }
        .hrs-mt4 { margin-top: 1rem; }
        .hrs-mb0 { margin-bottom: 0; }
        .hrs-m0  { margin: 0; }

        /* ── KEYFRAMES ── */
        @keyframes hrsShellIn {
            from { opacity: 0; transform: translateY(24px) scale(0.98); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        @keyframes hrsBrandRise {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes hrsFormIn {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes hrsFloat {
            0%, 100% { transform: translateY(0) translateX(0); }
            50%       { transform: translateY(-18px) translateX(10px); }
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 991px) {
            .hrs-screen   { padding: 12px; }
            .hrs-shell    { width: min(680px, calc(100vw - 24px)); max-height: calc(100svh - 24px); border-radius: 24px; }
            .hrs-panel    { grid-template-columns: 1fr; height: min(760px, calc(100svh - 24px)); }
            .hrs-brand    { min-height: 0; padding-bottom: 18px; }
            .hrs-highlights { gap: 8px; margin-top: 14px; }
            .hrs-highlight  { padding: 11px 12px; border-radius: 18px; }
            .hrs-highlight-icon { width: 36px; height: 36px; border-radius: 12px; font-size: 0.95rem; }
            .hrs-highlight strong { margin-bottom: 3px; font-size: 0.92rem; }
            .hrs-highlight span, .hrs-text { font-size: 0.84rem; line-height: 1.4; }
        }

        @media (max-width: 767px) {
            .hrs-screen  { min-height: 100vh; min-height: 100svh; height: 100vh; height: 100svh; padding: 10px; }
            .hrs-shell   { width: calc(100vw - 20px); max-height: calc(100svh - 20px); border-radius: 24px; }
            .hrs-panel   { height: calc(100svh - 20px); }
            .hrs-brand, .hrs-form-wrap { padding: 18px 16px; }
            .hrs-title   { font-size: 1.95rem; }
        }

        @media (max-height: 860px) {
            .hrs-brand, .hrs-form-wrap { padding-top: 18px; padding-bottom: 18px; }
            .hrs-logo-icon { width: 48px; height: 48px; }
            .hrs-logo-name { font-size: 1.6rem; }
            .hrs-title { font-size: clamp(1.8rem, 3vw, 3.2rem); margin-bottom: 8px; }
            .hrs-text, .hrs-subtitle { font-size: 0.9rem; line-height: 1.45; }
            .hrs-form { margin-top: 18px; }
        }

        @media (max-height: 760px) {
            .hrs-text    { display: block; font-size: 0.8rem; line-height: 1.35; }
            .hrs-highlights { display: grid; gap: 6px; margin-top: 10px; }
            .hrs-highlight  { gap: 10px; padding: 9px 10px; border-radius: 16px; }
            .hrs-highlight-icon { width: 32px; height: 32px; border-radius: 10px; font-size: 0.9rem; }
            .hrs-highlight strong { font-size: 0.86rem; }
            .hrs-highlight span  { font-size: 0.78rem; line-height: 1.3; }
            .hrs-title   { font-size: clamp(1.6rem, 2.8vw, 2.8rem); }
            .hrs-shell   { max-height: calc(100svh - 16px); }
            .hrs-panel   { height: calc(100svh - 16px); }
        }
    </style>
</head>

<body>
    <div class="hrs-screen">
        <div class="hrs-orb hrs-orb--one"></div>
        <div class="hrs-orb hrs-orb--two"></div>

        <div class="hrs-shell">
            <div class="hrs-panel">

                <!-- ── BRAND SIDE ── -->
                <div class="hrs-brand">
                    <div class="hrs-brand-inner">
                        <div>
                            <div class="hrs-badge">
                                <span class="hrs-badge-dot"></span>
                                People Operations
                            </div>

                            <div class="hrs-logo-mark hrs-mt2">
                                <div class="hrs-logo-icon">
                                    <i class="ri-team-line"></i>
                                </div>
                                <div class="hrs-logo-text">
                                    <span class="hrs-logo-name">Speed Logi</span>
                                </div>
                            </div>

                            <h1 class="hrs-title">Unified control for your <span>people</span> team</h1>
                            <p class="hrs-text">
                                Manage employees, track attendance, run payroll, and handle approvals from one intelligent HR workspace built for modern organizations.
                            </p>
                        </div>

                        <div class="hrs-highlights">
                          

                            <div class="hrs-highlight">
                                <div class="hrs-highlight-icon"><i class="ri-calendar-check-line"></i></div>
                                <div>
                                    <strong>Attendance & Leave Management</strong>
                                    <span>Real-time clock-in tracking, leave approvals, and shift scheduling across all departments.</span>
                                </div>
                            </div>
                            <div class="hrs-highlight">
                                <div class="hrs-highlight-icon"><i class="ri-user-heart-line"></i></div>
                                <div>
                                    <strong>Advance Salary and Company assets</strong>
                                    <span>Request and approve advace salaries and manage company assets.</span>
                                </div>
                            </div>
                            <div class="hrs-highlight">
                                <div class="hrs-highlight-icon"><i class="ri-shield-keyhole-line"></i></div>
                                <div>
                                    <strong>Role-Based Secure Access</strong>
                                    <span>HR managers, team leads, and employees each see exactly what they need — nothing more.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── FORM SIDE ── -->
                <div class="hrs-form-wrap">
                    <div class="hrs-form-card">
                        <div class="hrs-kicker">Employee Management System</div>
                        <h2 class="hrs-heading">Welcome back</h2>
                        <p class="hrs-subtitle">Sign in to continue into the HR control center with your authorized account.</p>

                        <form method="POST"
                            action="{{ route('login') }}"
                            autocomplete="on"
                            class="hrs-form ajax-form"
                            id="hrs-login-form">

                            @csrf

                            <div class="hrs-mb3">
                                <label for="email" class="hrs-label">
                                    Email <span class="hrs-required">*</span>
                                </label>

                                <div class="hrs-input-wrap">
                                    <input
                                        type="email"
                                        id="email"
                                        name="email"
                                        class="hrs-input @error('email') is-invalid @enderror"
                                        placeholder="Enter your work email"
                                        autocomplete="email"
                                        value="{{ old('email') }}"
                                        required
                                    >
                                </div>

                                @error('email')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="hrs-mb3">
                                <div class="hrs-label-row">
                                    <label for="password" class="hrs-label hrs-mb0">
                                        Password <span class="hrs-required">*</span>
                                    </label>

                                    <a href="{{ url('forgot-password') }}" class="hrs-link">
                                        Forgot password?
                                    </a>
                                </div>

                                <div class="hrs-input-wrap hrs-mt2">
                                    <input
                                        type="password"
                                        id="password"
                                        name="password"
                                        class="hrs-password-input @error('password') is-invalid @enderror"
                                        placeholder="Enter your password"
                                        autocomplete="current-password"
                                        required
                                    >

                                    <button type="button"
                                            class="hrs-password-toggle"
                                            id="password-toggle"
                                            aria-label="Toggle password visibility">

                                        <i class="ri-eye-off-fill"></i>
                                    </button>
                                </div>

                                @error('password')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="hrs-remember-row hrs-mb4 hrs-mt4">
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <input type="checkbox"
                                        class="hrs-checkbox"
                                        name="remember"
                                        id="remember">

                                    <label class="hrs-checkbox-label" for="remember">
                                        Remember Me
                                    </label>
                                </div>
                            </div>

                            <button type="submit"
                                    class="hrs-submit"
                                    id="submit-login">

                                <span>Sign In</span>
                                <i class="ri-arrow-right-line"></i>
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        // Password toggle
        (function () {
            const btn = document.getElementById('password-toggle');
            const inp = document.getElementById('password');
            if (!btn || !inp) return;
            btn.addEventListener('click', function () {
                const isPass = inp.type === 'password';
                inp.type = isPass ? 'text' : 'password';
                const icon = btn.querySelector('i');
                icon.classList.toggle('ri-eye-off-fill', !isPass);
                icon.classList.toggle('ri-eye-fill', isPass);
            });
        })();
    $(document).ready(function () {

        $("#hrs-login-form").submit(function () {

            const button = $('#submit-login');

            button.prop("disabled", true);

            button.html(`
                <span class="spinner-border spinner-border-sm"></span>
                Signing In...
            `);
        });

    });
</script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Speed HRM — HR & Employee Management System</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }

    :root {
      --emerald:       #059669;
      --emerald-light: #10b981;
      --emerald-mid:   #34d399;
      --emerald-pale:  #6ee7b7;
      --emerald-glow:  rgba(5, 150, 105, 0.35);
      --teal:          #0d9488;
      --amber:         #d97706;
      --amber-light:   #fbbf24;
      --amber-glow:    rgba(217, 119, 6, 0.45);
      --cyan:          #06b6d4;
      --dark-bg:       #021a12;
      --dark-mid:      #031f16;
      --dark-card:     rgba(4, 28, 18, 0.72);
      --dark-elevated: rgba(5, 38, 24, 0.85);
      --text-primary:  #ecfdf5;
      --text-muted:    rgba(209, 250, 229, 0.62);
      --border:        rgba(255, 255, 255, 0.08);
      --border-em:     rgba(5, 150, 105, 0.22);
    }

    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--dark-bg);
      color: var(--text-primary);
      overflow-x: hidden;
    }

    /* ── GRID BG UTILITY ── */
    .grid-bg::after {
      content: '';
      position: absolute;
      inset: 0; pointer-events: none;
      background:
        linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
      background-size: 44px 44px;
      mask-image: radial-gradient(ellipse at center, black 40%, transparent 90%);
    }

    /* ════════════════════ NAV ════════════════════ */
    nav {
      position: fixed; top: 0; left: 0; right: 0; z-index: 200;
      height: 70px;
      background: rgba(2, 20, 12, 0.88);
      backdrop-filter: blur(18px);
      border-bottom: 1px solid var(--border);
    }
    .nav-inner {
      max-width: 1180px; margin: 0 auto;
      display: flex; align-items: center; justify-content: space-between;
      padding: 0 2rem; height: 100%;
    }
    .nav-logo { display: flex; align-items: center; gap: 12px; text-decoration: none; }
    .nav-logo-icon {
      width: 38px; height: 38px; border-radius: 12px;
      background: linear-gradient(135deg, var(--emerald), var(--teal));
      display: grid; place-items: center;
      box-shadow: 0 6px 20px var(--emerald-glow);
    }
    .nav-logo-icon i { font-size: 1.1rem; color: #fff; }
    .nav-brand-text {
       font-size: 1.15rem; font-weight: 800;
      color: var(--text-primary); letter-spacing: -0.02em;
    }
    .nav-brand-text span { color: var(--emerald-mid); }
    .nav-links { display: flex; align-items: center; gap: 2rem; list-style: none; }
    .nav-links a { color: var(--text-muted); font-size: 14px; text-decoration: none; transition: color .2s; }
    .nav-links a:hover { color: var(--text-primary); }
    .nav-right { display: flex; align-items: center; gap: 10px; }
    .btn-outline-nav {
      border: 1px solid var(--border-em); color: var(--emerald-pale);
       font-size: 13px; font-weight: 700;
      padding: 8px 18px; border-radius: 12px; text-decoration: none;
      transition: background .2s, border-color .2s;
    }
    .btn-outline-nav:hover { background: rgba(5,150,105,0.1); border-color: var(--emerald-mid); }
    .btn-nav-cta {
      background: linear-gradient(135deg, var(--emerald), var(--teal));
      color: #fff;  font-size: 13px; font-weight: 800;
      padding: 9px 20px; border-radius: 12px; text-decoration: none;
      box-shadow: 0 8px 24px var(--emerald-glow);
      transition: transform .2s, box-shadow .2s;
    }
    .btn-nav-cta:hover { transform: translateY(-1px); box-shadow: 0 12px 30px var(--emerald-glow); }

    /* ════════════════════ HERO ════════════════════ */
    .hero {
      position: relative; overflow: hidden;
      min-height: 100vh; display: flex; align-items: center;
      padding-top: 70px;
      background:
        radial-gradient(circle at 12% 20%, rgba(217,119,6,0.14), transparent 22%),
        radial-gradient(circle at 86% 18%, rgba(5,150,105,0.22), transparent 26%),
        radial-gradient(circle at 60% 85%, rgba(6,182,212,0.12), transparent 22%),
        linear-gradient(140deg, #010e09 0%, #021810 40%, #031f14 100%);
    }
    .hero-orb {
      position: absolute; border-radius: 999px; filter: blur(12px); pointer-events: none;
      animation: floatOrb 11s ease-in-out infinite;
    }
    .hero-orb-1 { top:8%; left:5%; width:260px; height:260px; background:radial-gradient(circle, rgba(217,119,6,0.38),transparent); opacity:.55; }
    .hero-orb-2 { bottom:10%; right:6%; width:320px; height:320px; background:radial-gradient(circle, rgba(5,150,105,0.42),transparent); opacity:.55; animation-delay:-4s; }
    .hero-orb-3 { top:40%; left:50%; width:200px; height:200px; background:radial-gradient(circle, rgba(6,182,212,0.22),transparent); opacity:.4; animation-delay:-7s; }

    .hero-inner {
      max-width: 1180px; margin: 0 auto; padding: 6rem 2rem 5rem;
      display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 5rem; align-items: center;
      position: relative; z-index: 1;
    }
    .hero-kicker {
      display: inline-flex; align-items: center; gap: 10px;
      padding: 8px 16px; border-radius: 999px; margin-bottom: 1.6rem;
      background: rgba(5,150,105,0.1); border: 1px solid var(--border-em);
       font-size: 11px; font-weight: 700;
      letter-spacing: 0.12em; text-transform: uppercase; color: var(--emerald-mid);
    }
    .kicker-dot {
      width: 8px; height: 8px; border-radius: 50%;
      background: linear-gradient(135deg, var(--amber-light), var(--amber));
      box-shadow: 0 0 12px var(--amber-glow);
    }
    .hero h1 {
       font-size: clamp(2.6rem, 4.5vw, 4.2rem);
      font-weight: 800; line-height: 0.96; letter-spacing: -0.04em;
      color: var(--text-primary); margin-bottom: 1.4rem;
    }
    .hero h1 em { font-style: normal; color: var(--emerald-mid); }
    .hero h1 strong { font-style: normal; color: var(--amber-light); }
    .hero-desc {
      color: var(--text-muted); font-size: 1.05rem; line-height: 1.65;
      max-width: 500px; margin-bottom: 2.4rem;
    }
    .hero-actions { display: flex; gap: 12px; flex-wrap: wrap; }
    .btn-primary {
      display: inline-flex; align-items: center; gap: 8px;
      background: linear-gradient(135deg, var(--emerald), var(--teal));
      color: #fff;  font-size: 14px; font-weight: 800;
      padding: 14px 28px; border-radius: 16px; text-decoration: none;
      box-shadow: 0 16px 32px var(--emerald-glow);
      transition: transform .2s, box-shadow .2s;
    }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 22px 40px var(--emerald-glow); }
    .btn-ghost {
      display: inline-flex; align-items: center; gap: 8px;
      border: 1px solid var(--border-em); color: var(--emerald-pale);
       font-size: 14px; font-weight: 700;
      padding: 14px 28px; border-radius: 16px; text-decoration: none;
      transition: background .2s, border-color .2s;
    }
    .btn-ghost:hover { background: rgba(5,150,105,0.1); border-color: var(--emerald-mid); }

    /* HERO VISUAL CARD */
    .hero-card {
      background: rgba(3,18,11,0.72); border: 1px solid var(--border-em);
      border-radius: 24px; overflow: hidden;
      box-shadow: 0 32px 80px rgba(0,0,0,0.5);
      backdrop-filter: blur(14px);
      animation: cardIn .9s cubic-bezier(0.2,0.9,0.2,1) .2s both;
    }
    .card-topbar {
      background: rgba(5,150,105,0.08); border-bottom: 1px solid var(--border-em);
      padding: 14px 20px; display: flex; align-items: center; justify-content: space-between;
    }
    .card-dots { display: flex; gap: 6px; }
    .card-dots span { width: 10px; height: 10px; border-radius: 50%; }
    .card-title-bar {
       font-size: 12px; font-weight: 700;
      color: var(--emerald-mid); letter-spacing: 0.08em; text-transform: uppercase;
    }
    .card-live {
      display: flex; align-items: center; gap: 6px;
      font-size: 11px; color: #34d399; font-weight: 600;
    }
    .live-dot {
      width: 7px; height: 7px; border-radius: 50%; background: #34d399;
      animation: pulse 1.8s ease-in-out infinite;
    }
    .card-body { padding: 20px; }
    .card-stats { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-bottom: 16px; }
    .cstat {
      background: rgba(5,150,105,0.07); border: 1px solid var(--border-em);
      border-radius: 14px; padding: 14px 12px; text-align: center;
    }
    .cstat-val {
       font-size: 1.8rem; font-weight: 800;
      color: var(--emerald-mid); line-height: 1; margin-bottom: 4px;
    }
    .cstat-val.amber { color: var(--amber-light); }
    .cstat-val.cyan  { color: var(--cyan); }
    .cstat-lbl { font-size: 11px; color: var(--text-muted); }
    .card-rows { display: flex; flex-direction: column; gap: 8px; }
    .card-row {
      background: rgba(255,255,255,0.03); border: 1px solid var(--border);
      border-radius: 12px; padding: 12px 14px;
      display: flex; align-items: center; justify-content: space-between;
    }
    .row-left { display: flex; align-items: center; gap: 11px; }
    .avatar {
      width: 34px; height: 34px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
       font-size: 11px; font-weight: 800;
      flex-shrink: 0;
    }
    .av-em { background: rgba(5,150,105,0.18); color: var(--emerald-mid); }
    .av-am { background: rgba(217,119,6,0.18); color: var(--amber-light); }
    .av-cy { background: rgba(6,182,212,0.18); color: var(--cyan); }
    .row-name { font-size: 13px; font-weight: 600; color: var(--text-primary); }
    .row-role { font-size: 11px; color: var(--text-muted); }
    .badge {
      font-size: 10px; font-weight: 700; padding: 4px 10px; border-radius: 999px;
      letter-spacing: 0.04em;
    }
    .b-green { background: rgba(52,211,153,0.12); color: #34d399; }
    .b-amber { background: rgba(251,191,36,0.12); color: var(--amber-light); }
    .b-cyan  { background: rgba(6,182,212,0.12); color: var(--cyan); }

    /* ════════════════════ STATS BAR ════════════════════ */
    .statsbar {
      border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);
      background: var(--dark-card);
    }
    .statsbar-inner {
      max-width: 1180px; margin: 0 auto; padding: 0 2rem;
      display: grid; grid-template-columns: repeat(4,1fr);
    }
    .sbar-item {
      padding: 2.8rem 1rem; text-align: center;
      border-right: 1px solid var(--border);
    }
    .sbar-item:last-child { border-right: none; }
    .sbar-num {
       font-size: 2.6rem; font-weight: 800;
      background: linear-gradient(135deg, var(--emerald-mid), var(--cyan));
      -webkit-background-clip: text; -webkit-text-fill-color: transparent;
      background-clip: text; display: block; margin-bottom: 4px;
    }
    .sbar-num.amber-grad {
      background: linear-gradient(135deg, var(--amber-light), var(--amber));
      -webkit-background-clip: text; background-clip: text;
    }
    .sbar-desc { font-size: 14px; color: var(--text-muted); }

    /* ════════════════════ SECTIONS ════════════════════ */
    section { padding: 100px 0; }
    .sec-inner { max-width: 1180px; margin: 0 auto; padding: 0 2rem; }
    .sec-kicker {
       font-size: 11px; font-weight: 700;
      letter-spacing: 0.14em; text-transform: uppercase;
      color: var(--emerald-mid); margin-bottom: 1rem;
    }
    .sec-title {
      
      font-size: clamp(1.9rem, 3vw, 2.8rem); font-weight: 800;
      line-height: 1.05; letter-spacing: -0.03em; margin-bottom: 1rem;
    }
    .sec-sub {
      color: var(--text-muted); font-size: 1.02rem; line-height: 1.65; max-width: 560px;
    }

    /* ════════════════════ FEATURES GRID ════════════════════ */
    .features-grid {
      display: grid; grid-template-columns: repeat(3,1fr); gap: 1px;
      background: var(--border); border: 1px solid var(--border);
      border-radius: 20px; overflow: hidden; margin-top: 3.5rem;
    }
    .feat-card {
      background: var(--dark-card); padding: 2.2rem;
      transition: background .25s;
    }
    .feat-card:hover { background: var(--dark-elevated); }
    .feat-icon {
      width: 48px; height: 48px; border-radius: 14px;
      background: rgba(5,150,105,0.1); border: 1px solid var(--border-em);
      display: grid; place-items: center; margin-bottom: 1.3rem;
      font-size: 1.35rem; color: var(--emerald-mid);
    }
    .feat-icon.amber-ic { background: rgba(217,119,6,0.1); border-color: rgba(217,119,6,0.2); color: var(--amber-light); }
    .feat-icon.cyan-ic  { background: rgba(6,182,212,0.1); border-color: rgba(6,182,212,0.2); color: var(--cyan); }
    .feat-num {
       font-size: 11px; font-weight: 700;
      letter-spacing: 0.1em; color: var(--emerald-mid); margin-bottom: .5rem;
    }
    .feat-num.amber { color: var(--amber-light); }
    .feat-num.cyan  { color: var(--cyan); }
    .feat-title {
       font-size: 1.05rem; font-weight: 800;
      margin-bottom: .6rem; color: var(--text-primary);
    }
    .feat-desc { font-size: 14px; color: var(--text-muted); line-height: 1.65; }
    .feat-list { list-style: none; margin-top: .9rem; display: flex; flex-direction: column; gap: 7px; }
    .feat-list li {
      font-size: 13px; color: var(--text-muted);
      display: flex; align-items: flex-start; gap: 8px; line-height: 1.5;
    }
    .feat-list li i { color: var(--emerald-mid); font-size: 14px; flex-shrink: 0; margin-top: 1px; }

    /* ════════════════════ MODULES ════════════════════ */
    .modules-alt { background: rgba(3,18,11,0.6); border-top:1px solid var(--border); border-bottom:1px solid var(--border); }
    .modules-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 3.5rem; }
    .mod-card {
      background: rgba(4,25,16,0.7); border: 1px solid var(--border);
      border-radius: 18px; padding: 2rem;
      transition: border-color .25s, transform .2s;
      position: relative; overflow: hidden;
    }
    .mod-card::before {
      content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px;
      background: linear-gradient(90deg, transparent, var(--emerald), transparent);
      opacity: 0; transition: opacity .3s;
    }
    .mod-card:hover::before { opacity: 1; }
    .mod-card:hover { border-color: var(--border-em); transform: translateY(-3px); }
    .mod-card.featured {
      background: linear-gradient(135deg, rgba(5,150,105,0.1), rgba(13,148,136,0.06));
      border-color: var(--border-em);
    }
    .mod-card.featured::before { opacity: 1; }
    .mod-head { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.2rem; }
    .mod-icon {
      width: 52px; height: 52px; border-radius: 15px;
      background: rgba(5,150,105,0.12); border: 1px solid var(--border-em);
      display: grid; place-items: center; font-size: 1.4rem; color: var(--emerald-mid);
    }
    .mod-icon.amber { background: rgba(217,119,6,0.12); border-color: rgba(217,119,6,0.2); color: var(--amber-light); }
    .mod-badge {
       font-size: 10px; font-weight: 700;
      letter-spacing: 0.08em; text-transform: uppercase;
      padding: 5px 12px; border-radius: 999px;
    }
    .mb-live { background: rgba(52,211,153,0.12); color: #34d399; }
    .mb-soon { background: rgba(217,119,6,0.12); color: var(--amber-light); }
    .mod-title {  font-size: 1.15rem; font-weight: 800; margin-bottom: .5rem; }
    .mod-desc { font-size: 14px; color: var(--text-muted); line-height: 1.65; margin-bottom: 1.2rem; }
    .mod-tags { display: flex; flex-wrap: wrap; gap: 6px; }
    .mtag {
      font-size: 11px; padding: 4px 10px; border-radius: 999px;
      background: rgba(255,255,255,0.04); border: 1px solid var(--border);
      color: var(--text-muted);
    }

    /* ════════════════════ WORKFLOW ════════════════════ */
    .workflow-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 5rem; align-items: start; }
    .wf-steps { position: relative; }
    .wf-line {
      position: absolute; left: 23px; top: 40px; bottom: 80px; width: 2px;
      background: linear-gradient(to bottom, var(--emerald), rgba(5,150,105,0.1));
    }
    .wf-step { display: flex; gap: 2rem; padding: 1.5rem 0; position: relative; }
    .wf-num {
      width: 48px; height: 48px; border-radius: 14px; flex-shrink: 0;
      background: rgba(4,25,16,0.9); border: 1px solid var(--border-em);
      display: grid; place-items: center; position: relative; z-index: 1;
       font-size: 14px; font-weight: 800; color: var(--emerald-mid);
    }
    .wf-content { padding-top: 4px; }
    .wf-title {  font-size: 1.05rem; font-weight: 800; margin-bottom: .4rem; }
    .wf-desc { font-size: 14px; color: var(--text-muted); line-height: 1.65; }
    .wf-right { padding-top: 1rem; }
    .access-card {
      background: rgba(3,18,11,0.8); border: 1px solid var(--border-em);
      border-radius: 20px; padding: 2rem; margin-bottom: 1.5rem;
    }
    .ac-title {
       font-size: 12px; font-weight: 700;
      letter-spacing: 0.1em; text-transform: uppercase; color: var(--emerald-mid);
      margin-bottom: 1.2rem;
    }
    .ac-row {
      display: flex; align-items: center; justify-content: space-between;
      padding: 12px 16px; background: rgba(5,150,105,0.05); border: 1px solid var(--border);
      border-radius: 12px; margin-bottom: 8px;
    }
    .ac-row:last-child { margin-bottom: 0; }
    .ac-left { display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 600; }
    .ac-left i { color: var(--emerald-mid); }
    .ac-link {
      font-size: 12px; color: var(--emerald-mid); font-weight: 700; text-decoration: none;
      display: flex; align-items: center; gap: 4px;
      transition: color .2s;
    }
    .ac-link:hover { color: var(--emerald-pale); }

    /* ════════════════════ ROLES ════════════════════ */
    .roles-section { background: rgba(3,18,11,0.6); border-top:1px solid var(--border); border-bottom:1px solid var(--border); }
    .roles-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 1rem; margin-top: 3.5rem; }
    .role-card {
      background: rgba(4,25,16,0.7); border: 1px solid var(--border);
      border-radius: 16px; padding: 1.8rem; text-align: center;
      transition: border-color .2s, transform .2s;
    }
    .role-card:hover { border-color: var(--border-em); transform: translateY(-2px); }
    .role-icon-wrap {
      width: 56px; height: 56px; border-radius: 16px; margin: 0 auto 1rem;
      background: rgba(5,150,105,0.1); border: 1px solid var(--border-em);
      display: grid; place-items: center; font-size: 1.4rem; color: var(--emerald-mid);
    }
    .role-icon-wrap.a { background:rgba(217,119,6,0.1); border-color:rgba(217,119,6,0.2); color:var(--amber-light); }
    .role-icon-wrap.b { background:rgba(6,182,212,0.1); border-color:rgba(6,182,212,0.2); color:var(--cyan); }
    .role-icon-wrap.c { background:rgba(52,211,153,0.1); border-color:rgba(52,211,153,0.2); color:var(--emerald-mid); }
    .role-title {  font-size: 15px; font-weight: 800; margin-bottom: .5rem; }
    .role-desc { font-size: 13px; color: var(--text-muted); line-height: 1.6; }

    /* ════════════════════ WHY ════════════════════ */
    .why-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 5rem; align-items: center; margin-top: 3.5rem; }
    .why-list { display: flex; flex-direction: column; gap: 1.2rem; }
    .why-item {
      display: flex; gap: 1.2rem; padding: 1.4rem 1.6rem;
      background: rgba(4,25,16,0.7); border: 1px solid var(--border);
      border-radius: 16px; transition: border-color .2s;
    }
    .why-item:hover { border-color: var(--border-em); }
    .why-item-icon {
      width: 44px; height: 44px; flex-shrink: 0; border-radius: 12px;
      background: linear-gradient(135deg, rgba(217,119,6,0.25), rgba(5,150,105,0.2));
      display: grid; place-items: center; font-size: 1.2rem;
    }
    .why-item strong { display: block;  font-size: 15px; font-weight: 800; margin-bottom: 4px; }
    .why-item p { font-size: 13px; color: var(--text-muted); line-height: 1.55; margin: 0; }
    .why-visual {
      background: rgba(3,18,11,0.8); border: 1px solid var(--border-em);
      border-radius: 20px; padding: 2rem;
    }
    .wv-head {
      display: flex; align-items: center; gap: 10px; margin-bottom: 1.5rem;
       font-size: 13px; font-weight: 700; color: var(--emerald-mid);
    }
    .wv-head i { font-size: 1.1rem; }
    .progress-row { margin-bottom: 1.1rem; }
    .prog-label { display: flex; justify-content: space-between; font-size: 13px; color: var(--text-muted); margin-bottom: 8px; }
    .prog-label span:last-child { color: var(--emerald-mid); font-weight: 700; }
    .prog-bar { height: 6px; background: rgba(255,255,255,0.07); border-radius: 999px; overflow: hidden; }
    .prog-fill { height: 100%; border-radius: 999px; background: linear-gradient(90deg, var(--emerald), var(--teal)); }
    .prog-fill.amber { background: linear-gradient(90deg, var(--amber), var(--amber-light)); }
    .prog-fill.cyan  { background: linear-gradient(90deg, var(--teal), var(--cyan)); }
    .wv-divider { height: 1px; background: var(--border); margin: 1.5rem 0; }
    .mini-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .mini-stat {
      background: rgba(5,150,105,0.06); border: 1px solid var(--border-em);
      border-radius: 12px; padding: 14px; text-align: center;
    }
    .mini-stat-val {  font-size: 1.5rem; font-weight: 800; color: var(--emerald-mid); }
    .mini-stat-val.amber { color: var(--amber-light); }
    .mini-stat-lbl { font-size: 11px; color: var(--text-muted); margin-top: 3px; }

    /* ════════════════════ CTA ════════════════════ */
    .cta-section {
      position: relative; overflow: hidden; text-align: center;
      background:
        radial-gradient(circle at 50% 50%, rgba(5,150,105,0.18), transparent 50%),
        radial-gradient(circle at 20% 20%, rgba(217,119,6,0.10), transparent 30%),
        var(--dark-mid);
      border-top: 1px solid var(--border);
    }
    .cta-inner { position: relative; z-index: 1; }
    .cta-badge {
      display: inline-flex; align-items: center; gap: 8px; margin-bottom: 1.5rem;
      padding: 8px 18px; border-radius: 999px;
      background: rgba(5,150,105,0.1); border: 1px solid var(--border-em);
       font-size: 11px; font-weight: 700;
      letter-spacing: 0.12em; text-transform: uppercase; color: var(--emerald-mid);
    }
    .cta-title {
      
      font-size: clamp(2rem, 3.5vw, 3.2rem); font-weight: 800;
      line-height: 1.05; letter-spacing: -0.03em; margin-bottom: 1.2rem;
    }
    .cta-sub { color: var(--text-muted); font-size: 1.05rem; max-width: 520px; margin: 0 auto 2.5rem; line-height: 1.65; }
    .cta-actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; margin-bottom: 3rem; }
    .cta-trust { display: flex; justify-content: center; gap: 2.5rem; flex-wrap: wrap; }
    .trust-item {
      display: flex; align-items: center; gap: 8px; font-size: 14px; color: var(--text-muted);
    }
    .trust-item i { color: var(--emerald-mid); }

    /* ════════════════════ FOOTER ════════════════════ */
    footer {
      border-top: 1px solid var(--border);
      background: rgba(1,10,6,0.95); padding: 64px 0 36px;
    }
    .footer-grid {
      max-width: 1180px; margin: 0 auto; padding: 0 2rem;
      display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 3rem;
    }
    .footer-brand p { font-size: 14px; color: var(--text-muted); line-height: 1.7; margin-top: .8rem; max-width: 280px; }
    .footer-col h4 {
       font-size: 12px; font-weight: 700;
      letter-spacing: 0.1em; text-transform: uppercase; color: var(--emerald-mid);
      margin-bottom: 1.2rem;
    }
    .footer-col ul { list-style: none; display: flex; flex-direction: column; gap: 10px; }
    .footer-col ul a { font-size: 14px; color: var(--text-muted); text-decoration: none; transition: color .2s; }
    .footer-col ul a:hover { color: var(--emerald-pale); }
    .footer-bottom {
      max-width: 1180px; margin: 3rem auto 0; padding: 1.8rem 2rem 0;
      border-top: 1px solid var(--border);
      display: flex; align-items: center; justify-content: space-between;
      font-size: 13px; color: var(--text-muted); flex-wrap: wrap; gap: .5rem;
    }
    .footer-bottom a { color: var(--emerald-mid); text-decoration: none; }

    /* ════════════════════ ANIMATIONS ════════════════════ */
    @keyframes floatOrb {
      0%,100% { transform: translateY(0) translateX(0); }
      50%      { transform: translateY(-22px) translateX(12px); }
    }
    @keyframes pulse {
      0%,100% { opacity: 1; transform: scale(1); }
      50%      { opacity: .5; transform: scale(0.8); }
    }
    @keyframes cardIn {
      from { opacity: 0; transform: translateY(30px) scale(0.97); }
      to   { opacity: 1; transform: translateY(0) scale(1); }
    }
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(22px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .anim-1 { animation: fadeUp .65s ease .05s both; }
    .anim-2 { animation: fadeUp .65s ease .18s both; }
    .anim-3 { animation: fadeUp .65s ease .31s both; }
    .anim-4 { animation: fadeUp .65s ease .44s both; }

    /* ════════════════════ RESPONSIVE ════════════════════ */
    @media (max-width: 960px) {
      .hero-inner { grid-template-columns: 1fr; gap: 3rem; }
      .hero-card  { display: none; }
      .statsbar-inner { grid-template-columns: 1fr 1fr; }
      .sbar-item { border-right: none; border-bottom: 1px solid var(--border); }
      .features-grid { grid-template-columns: 1fr; }
      .modules-grid { grid-template-columns: 1fr; }
      .workflow-grid { grid-template-columns: 1fr; gap: 3rem; }
      .roles-grid { grid-template-columns: 1fr 1fr; }
      .why-grid { grid-template-columns: 1fr; gap: 2.5rem; }
      .footer-grid { grid-template-columns: 1fr 1fr; gap: 2rem; }
      .nav-links { display: none; }
    }
    @media (max-width: 580px) {
      .roles-grid { grid-template-columns: 1fr; }
      .hero h1 { font-size: 2.4rem; }
      section { padding: 70px 0; }
    }
  </style>
</head>
<body>

<!-- ══ NAV ════════════════════════════════════════════ -->
<nav>
  <div class="nav-inner">
    <a href="#" class="nav-logo">
      <div class="nav-logo-icon"><i class="ri-team-line"></i></div>
      <span class="nav-brand-text">Speed <span>HRM</span></span>
    </a>
    <ul class="nav-links">
      <li><a href="#features">Features</a></li>
      <li><a href="#modules">Modules</a></li>
      <li><a href="#workflow">Workflow</a></li>
      <li><a href="#access">Access</a></li>
      <li><a href="#contact">Contact</a></li>
    </ul>
    <div class="nav-right">
      <a href="/login" class="btn-outline-nav">Login</a>
      <a href="#contact" class="btn-nav-cta">Request Access</a>
    </div>
  </div>
</nav>

<!-- ══ HERO ══════════════════════════════════════════ -->
<section style="padding:0;">
  <div class="hero grid-bg">
    <div class="hero-orb hero-orb-1"></div>
    <div class="hero-orb hero-orb-2"></div>
    <div class="hero-orb hero-orb-3"></div>

    <div class="hero-inner">
      <div>
        <div class="hero-kicker anim-1">
          <span class="kicker-dot"></span>
          People Operations · Speed Logi Internal Systems
        </div>
        <h1 class="anim-2">
          Unified control<br/>for your <em>people</em><br/>& <strong>workforce.</strong>
        </h1>
        <p class="hero-desc anim-3">
          Speed HRM brings employee records, attendance tracking, leave management, payroll, and HR administration into one intelligent workspace — built in-house for the Speed Logistics team in Saudi Arabia.
        </p>
        <div class="hero-actions anim-4">
          <a href="#modules" class="btn-primary"><i class="ri-grid-line"></i> Explore Modules</a>
          <a href="#contact" class="btn-ghost"><i class="ri-mail-line"></i> Request Access</a>
        </div>
      </div>

      <!-- Live dashboard mockup -->
      <div class="hero-card">
        <div class="card-topbar">
          <div class="card-dots">
            <span style="background:#34d399;"></span>
            <span style="background:var(--amber-light);"></span>
            <span style="background:var(--cyan);"></span>
          </div>
          <span class="card-title-bar">HR Dashboard</span>
          <span class="card-live"><span class="live-dot"></span> Live</span>
        </div>
        <div class="card-body">
          <div class="card-stats">
            <div class="cstat">
              <div class="cstat-val">148</div>
              <div class="cstat-lbl">Total Staff</div>
            </div>
            <div class="cstat">
              <div class="cstat-val amber">132</div>
              <div class="cstat-lbl">Present Today</div>
            </div>
            <div class="cstat">
              <div class="cstat-val cyan">7</div>
              <div class="cstat-lbl">On Leave</div>
            </div>
          </div>
          <div class="ac-title"><i class="ri-lock-line" style="margin-right:6px;"></i>System Access</div>
          <div class="ac-row">
            <div class="ac-left"><i class="ri-dashboard-line"></i> HR Admin Panel</div>
            <a href="#contact" class="ac-link">Request Access <i class="ri-arrow-right-s-line"></i></a>
          </div>
          <div class="ac-row">
            <div class="ac-left"><i class="ri-user-line"></i> Employee Portal</div>
            <a href="#contact" class="ac-link">Get Login <i class="ri-arrow-right-s-line"></i></a>
          </div>
          <div class="ac-row">
            <div class="ac-left"><i class="ri-car-line"></i> Rider HR Records</div>
            <a href="https://dobs.speedlogi.sa/register" class="ac-link">DOBS Portal <i class="ri-arrow-right-s-line"></i></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══ STATS BAR ══════════════════════════════════════ -->
<div class="statsbar">
  <div class="statsbar-inner">
    <div class="sbar-item">
      <span class="sbar-num">148+</span>
      <span class="sbar-desc">Employees Managed</span>
    </div>
    <div class="sbar-item">
      <span class="sbar-num amber-grad">6</span>
      <span class="sbar-desc">Core HR Modules</span>
    </div>
    <div class="sbar-item">
      <span class="sbar-num">24/7</span>
      <span class="sbar-desc">System Availability</span>
    </div>
    <div class="sbar-item">
      <span class="sbar-num amber-grad">KSA</span>
      <span class="sbar-desc">Compliance Ready</span>
    </div>
  </div>
</div>

<!-- ══ FEATURES ═══════════════════════════════════════ -->
<section id="features">
  <div class="sec-inner">
    <div class="sec-kicker">Core Capabilities</div>
    <h2 class="sec-title">Core HR capabilities,<br/>arranged like an operating system.</h2>
    <p class="sec-sub">Every module reflects the real workflow of the Speed Logi team — from rider onboarding to monthly payroll, structured for clarity and speed.</p>

    <div class="features-grid">
      <div class="feat-card">
        <div class="feat-icon"><i class="ri-user-line"></i></div>
        <div class="feat-num">01</div>
        <div class="feat-title">Employee Records</div>
        <div class="feat-desc">Central profile for every team member — role, contract, documents, and employment history in one place.</div>
        <ul class="feat-list">
          <li><i class="ri-checkbox-circle-line"></i> Personal &amp; job information</li>
          <li><i class="ri-checkbox-circle-line"></i> Contract and document storage</li>
          <li><i class="ri-checkbox-circle-line"></i> Role and department mapping</li>
        </ul>
      </div>
      <div class="feat-card">
        <div class="feat-icon amber-ic"><i class="ri-time-line"></i></div>
        <div class="feat-num amber">02</div>
        <div class="feat-title">Attendance Tracking</div>
        <div class="feat-desc">Real-time clock-in and out visibility with daily summaries, absence flags, and shift tracking across departments.</div>
        <ul class="feat-list">
          <li><i class="ri-checkbox-circle-line"></i> Real-time clock-in records</li>
          <li><i class="ri-checkbox-circle-line"></i> Absence and tardiness flags</li>
          <li><i class="ri-checkbox-circle-line"></i> Monthly attendance reports</li>
        </ul>
      </div>
      <div class="feat-card">
        <div class="feat-icon cyan-ic"><i class="ri-calendar-check-line"></i></div>
        <div class="feat-num cyan">03</div>
        <div class="feat-title">Leave Management</div>
        <div class="feat-desc">Request, approve, and track all leave types with balance counters, calendar views, and manager workflows.</div>
        <ul class="feat-list">
          <li><i class="ri-checkbox-circle-line"></i> Annual, sick &amp; emergency leave</li>
          <li><i class="ri-checkbox-circle-line"></i> Manager approval workflows</li>
          <li><i class="ri-checkbox-circle-line"></i> Leave balance dashboards</li>
        </ul>
      </div>
      <div class="feat-card">
        <div class="feat-icon amber-ic"><i class="ri-money-dollar-circle-line"></i></div>
        <div class="feat-num amber">04</div>
        <div class="feat-title">Payroll & Salary Advances</div>
        <div class="feat-desc">Salary records, deductions, advance salary requests, company asset tracking, and payslip generation aligned with KSA standards.</div>
        <ul class="feat-list">
          <li><i class="ri-checkbox-circle-line"></i> Monthly payroll processing</li>
          <li><i class="ri-checkbox-circle-line"></i> Advance salary requests &amp; approvals</li>
          <li><i class="ri-checkbox-circle-line"></i> Company asset management</li>
        </ul>
      </div>
      <div class="feat-card">
        <div class="feat-icon"><i class="ri-user-add-line"></i></div>
        <div class="feat-num">05</div>
        <div class="feat-title">Onboarding & Offboarding</div>
        <div class="feat-desc">Structured checklists and document workflows for new hires and departing staff — no manual gaps.</div>
        <ul class="feat-list">
          <li><i class="ri-checkbox-circle-line"></i> New hire document collection</li>
          <li><i class="ri-checkbox-circle-line"></i> Access provisioning steps</li>
          <li><i class="ri-checkbox-circle-line"></i> Exit clearance workflows</li>
        </ul>
      </div>
      <div class="feat-card">
        <div class="feat-icon cyan-ic"><i class="ri-bar-chart-2-line"></i></div>
        <div class="feat-num cyan">06</div>
        <div class="feat-title">HR Reports & Analytics</div>
        <div class="feat-desc">Headcount trends, attendance summaries, payroll overviews, and workforce insights for leadership.</div>
        <ul class="feat-list">
          <li><i class="ri-checkbox-circle-line"></i> Headcount and turnover tracking</li>
          <li><i class="ri-checkbox-circle-line"></i> Attendance and leave analytics</li>
          <li><i class="ri-checkbox-circle-line"></i> Exportable HR reports</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- ══ MODULES ════════════════════════════════════════ -->
<section id="modules" class="modules-alt">
  <div class="sec-inner">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:4rem;align-items:end;margin-bottom:3.5rem;">
      <div>
        <div class="sec-kicker">System Modules</div>
        <h2 class="sec-title">An in-house HR stack for the full employee lifecycle.</h2>
      </div>
      <div>
        <p class="sec-sub" style="margin-top:.5rem;">
          Speed HRM is a dedicated internal system inside the Speed Logi platform — connecting HR administration, employee self-service, and field rider records under one roof.
        </p>
      </div>
    </div>

    <div class="modules-grid">
      <div class="mod-card featured">
        <div class="mod-head">
          <div class="mod-icon"><i class="ri-dashboard-line"></i></div>
          <span class="mod-badge mb-live">Live</span>
        </div>
        <div class="mod-title">HR Administration Panel</div>
        <div class="mod-desc">Full control for HR managers — employee records, contract oversight, leave approvals, payroll, and workforce reporting in one dashboard.</div>
        <div class="mod-tags">
          <span class="mtag">Employee Profiles</span>
          <span class="mtag">Leave Approvals</span>
          <span class="mtag">Payroll</span>
          <span class="mtag">Reports</span>
        </div>
      </div>
      <div class="mod-card">
        <div class="mod-head">
          <div class="mod-icon amber"><i class="ri-user-heart-line"></i></div>
          <span class="mod-badge mb-live">Live</span>
        </div>
        <div class="mod-title">Employee Self-Service Portal</div>
        <div class="mod-desc">Every team member can view their profile, apply for leave, request salary advances, check payslips, and update contact details without manual HR intervention.</div>
        <div class="mod-tags">
          <span class="mtag">Leave Requests</span>
          <span class="mtag">Salary Advance</span>
          <span class="mtag">Payslips</span>
          <span class="mtag">Profile</span>
        </div>
      </div>
      <div class="mod-card">
        <div class="mod-head">
          <div class="mod-icon"><i class="ri-car-line"></i></div>
          <span class="mod-badge mb-live">Live</span>
        </div>
        <div class="mod-title">Driver & Rider HR Records</div>
        <div class="mod-desc">A dedicated HR layer for field riders integrated with the DOBS portal — tracks registration, activation, compliance, and employment lifecycle.</div>
        <div class="mod-tags">
          <span class="mtag">DOBS Integration</span>
          <span class="mtag">Driver Lifecycle</span>
          <span class="mtag">Compliance</span>
        </div>
      </div>
      <div class="mod-card">
        <div class="mod-head">
          <div class="mod-icon amber"><i class="ri-smartphone-line"></i></div>
          <span class="mod-badge mb-soon">Coming Soon</span>
        </div>
        <div class="mod-title">Mobile HR App</div>
        <div class="mod-desc">An Android and iOS companion app for employees and managers to handle HR tasks on the go — aligned with Speed's field-first operating model.</div>
        <div class="mod-tags">
          <span class="mtag">Android & iOS</span>
          <span class="mtag">Push Notifications</span>
          <span class="mtag">Field Access</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══ WORKFLOW ════════════════════════════════════════ -->
<section id="workflow">
  <div class="sec-inner">
    <div class="workflow-grid">
      <div>
        <div class="sec-kicker">Workflow</div>
        <h2 class="sec-title">From hire to exit,<br/>one clean HR chain.</h2>
        <p class="sec-sub" style="margin-bottom:2rem;">
          Speed HRM is structured so every people operation follows a predictable, visible path — no scattered records, no manual gaps in the process.
        </p>
        <div class="wf-steps">
          <div class="wf-line"></div>
          <div class="wf-step">
            <div class="wf-num">01</div>
            <div class="wf-content">
              <div class="wf-title">New Hire Onboarding</div>
              <div class="wf-desc">Employee profile created, documents collected, and system access provisioned through a structured onboarding checklist.</div>
            </div>
          </div>
          <div class="wf-step">
            <div class="wf-num">02</div>
            <div class="wf-content">
              <div class="wf-title">Daily Attendance Logging</div>
              <div class="wf-desc">Clock-in and out tracked in real time. Absences and late arrivals flagged automatically for manager review.</div>
            </div>
          </div>
          <div class="wf-step">
            <div class="wf-num">03</div>
            <div class="wf-content">
              <div class="wf-title">Leave &amp; Advance Requests</div>
              <div class="wf-desc">Employees submit leave and salary advance requests through the portal. Managers approve or decline with full team visibility.</div>
            </div>
          </div>
          <div class="wf-step">
            <div class="wf-num">04</div>
            <div class="wf-content">
              <div class="wf-title">Payroll Processing</div>
              <div class="wf-desc">Attendance data feeds into monthly payroll. Deductions, bonuses, and payslips generated and stored per cycle.</div>
            </div>
          </div>
          <div class="wf-step">
            <div class="wf-num">05</div>
            <div class="wf-content">
              <div class="wf-title">Exit &amp; Offboarding</div>
              <div class="wf-desc">Structured exit workflow covers clearance, final settlement, document handover, and system access removal.</div>
            </div>
          </div>
        </div>
      </div>

      <div class="wf-right">
        <div class="access-card">
          <div class="ac-title"><i class="ri-lock-line" style="margin-right:6px;"></i>System Access</div>
          <div class="ac-row">
            <div class="ac-left"><i class="ri-dashboard-line"></i> HR Admin Panel</div>
            <a href="#contact" class="ac-link">Request Access <i class="ri-arrow-right-s-line"></i></a>
          </div>
          <div class="ac-row">
            <div class="ac-left"><i class="ri-user-line"></i> Employee Portal</div>
            <a href="#contact" class="ac-link">Get Login <i class="ri-arrow-right-s-line"></i></a>
          </div>
          <div class="ac-row">
            <div class="ac-left"><i class="ri-car-line"></i> Rider HR Records</div>
            <a href="https://dobs.speedlogi.sa/register" class="ac-link">DOBS Portal <i class="ri-arrow-right-s-line"></i></a>
          </div>
        </div>

        <!-- Progress visual -->
        <div class="why-visual">
          <div class="wv-head"><i class="ri-pie-chart-line"></i> Workforce Overview</div>
          <div class="progress-row">
            <div class="prog-label"><span>Attendance Rate</span><span>89%</span></div>
            <div class="prog-bar"><div class="prog-fill" style="width:89%"></div></div>
          </div>
          <div class="progress-row">
            <div class="prog-label"><span>Leave Utilization</span><span>62%</span></div>
            <div class="prog-bar"><div class="prog-fill amber" style="width:62%"></div></div>
          </div>
          <div class="progress-row">
            <div class="prog-label"><span>Payroll Processed</span><span>97%</span></div>
            <div class="prog-bar"><div class="prog-fill cyan" style="width:97%"></div></div>
          </div>
          <div class="wv-divider"></div>
          <div class="mini-stats">
            <div class="mini-stat">
              <div class="mini-stat-val">9</div>
              <div class="mini-stat-lbl">Depts. Active</div>
            </div>
            <div class="mini-stat">
              <div class="mini-stat-val amber">4</div>
              <div class="mini-stat-lbl">Leave Pending</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══ ACCESS ROLES ═══════════════════════════════════ -->
<section id="access" class="roles-section">
  <div class="sec-inner">
    <div style="text-align:center;max-width:580px;margin:0 auto;">
      <div class="sec-kicker">Access Levels</div>
      <h2 class="sec-title">Designed for every role<br/>in the Speed team.</h2>
      <p class="sec-sub" style="margin:0 auto;">Role-based access ensures HR managers, team leads, employees, and field riders each see exactly what they need — nothing more.</p>
    </div>
    <div class="roles-grid">
      <div class="role-card">
        <div class="role-icon-wrap c"><i class="ri-shield-user-line"></i></div>
        <div class="role-title">HR Manager</div>
        <div class="role-desc">Full admin access — manage all records, approve leave, run payroll, and generate workforce reports.</div>
      </div>
      <div class="role-card">
        <div class="role-icon-wrap a"><i class="ri-team-line"></i></div>
        <div class="role-title">Department Manager</div>
        <div class="role-desc">Review team attendance, approve or decline leave requests, and track department headcount.</div>
      </div>
      <div class="role-card">
        <div class="role-icon-wrap b"><i class="ri-user-line"></i></div>
        <div class="role-title">Employee</div>
        <div class="role-desc">View profile, request leave, submit salary advance requests, check payslips, and update contact details.</div>
      </div>
      <div class="role-card">
        <div class="role-icon-wrap c"><i class="ri-e-bike-line"></i></div>
        <div class="role-title">Field Rider</div>
        <div class="role-desc">Access employment status, leave balance, and attendance records synced with DOBS registration.</div>
      </div>
    </div>
  </div>
</section>

<!-- ══ CTA ════════════════════════════════════════════ -->
<section class="cta-section" id="contact">
  <div class="sec-inner cta-inner">
    <div class="cta-badge"><span class="kicker-dot"></span> Get Started</div>
    <h2 class="cta-title">Bring cleaner HR operations<br/>to the Speed team.</h2>
    <p class="cta-sub">Speed HRM is an internal system. Contact the operations team to request access or learn more about how it fits within the wider Speed Logi platform.</p>
    <div class="cta-actions">
      <a href="mailto:info@speedlogi.sa?subject=HRM Access Request" class="btn-primary" style="font-size:15px;padding:15px 32px;">
        <i class="ri-mail-send-line"></i> Request Access
      </a>
      <a href="/login" class="btn-ghost" style="font-size:15px;padding:15px 32px;">
        <i class="ri-apps-line"></i> View All Systems
      </a>
    </div>
    <div class="cta-trust">
      <div class="trust-item"><i class="ri-checkbox-circle-line"></i> Internal system access</div>
      <div class="trust-item"><i class="ri-checkbox-circle-line"></i> KSA labor law aligned</div>
      <div class="trust-item"><i class="ri-checkbox-circle-line"></i> Integrated with Speed Logi stack</div>
      <div class="trust-item"><i class="ri-checkbox-circle-line"></i> 24/7 operational uptime</div>
    </div>
  </div>
</section>

<!-- ══ FOOTER ═════════════════════════════════════════ -->
<footer>
  <div class="footer-grid">
    <div class="footer-brand">
      <a href="#" class="nav-logo" style="display:inline-flex;">
        <div class="nav-logo-icon"><i class="ri-team-line"></i></div>
        <span class="nav-brand-text" style="margin-left:12px;">Speed <span>HRM</span></span>
      </a>
      <p>Internal HR and Employee Management System by Speed Logistics Saudi Arabia. Managing people operations from Al Khobar across KSA.</p>
    </div>
    <div class="footer-col">
      <h4>System</h4>
      <ul>
        <li><a href="#features">Features</a></li>
        <li><a href="#modules">Modules</a></li>
        <li><a href="#workflow">Workflow</a></li>
        <li><a href="#access">Access Levels</a></li>
      </ul>
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
    <span>Speed HRM — Internal HR &amp; Employee System · <a href="https://speedlogi.sa">speedlogi.sa</a></span>
  </div>
</footer>

</body>
</html>
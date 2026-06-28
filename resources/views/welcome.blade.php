<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Lara Poll · Live polling</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <style>
        :root {
            --paper:   #f6f3ec;
            --ink:     #1c1a17;
            --faint:   #8a847a;
            --line:    #ddd6c9;
            --mark:    #b5482e;
            --mark-bg: #efe7da;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; }
        body {
            background: var(--paper);
            color: var(--ink);
            font-family: 'Fraunces', Georgia, serif;
            -webkit-font-smoothing: antialiased;
            display: flex;
            flex-direction: column;
        }
        .mono { font-family: 'JetBrains Mono', monospace; }

        .topbar { border-bottom: 1px solid var(--line); }
        .topbar-inner {
            max-width: 38rem; margin: 0 auto; padding: 1.1rem 1.5rem;
            display: flex; justify-content: space-between; align-items: baseline;
        }
        .brand { font-weight: 600; font-size: 1.05rem; letter-spacing: -0.01em; }
        .brand-dot { color: var(--mark); }
        .nav { display: flex; gap: 1.1rem; align-items: baseline; }
        .nav a, .nav button {
            font-family: 'JetBrains Mono', monospace; font-size: 0.72rem;
            color: var(--faint); text-decoration: none; letter-spacing: 0.02em;
            background: none; border: none; cursor: pointer;
        }
        .nav a:hover, .nav button:hover { color: var(--ink); }
        .nav .strong { color: var(--ink); }

        .hero {
            flex: 1; display: flex; flex-direction: column; justify-content: center;
            max-width: 38rem; margin: 0 auto; padding: 4rem 1.5rem; width: 100%;
        }
        .eyebrow {
            font-family: 'JetBrains Mono', monospace; font-size: 0.7rem;
            letter-spacing: 0.12em; text-transform: uppercase; color: var(--faint);
            margin-bottom: 1.2rem; display: flex; align-items: center; gap: 0.5rem;
        }
        .pulse { width: 7px; height: 7px; border-radius: 50%; background: var(--mark); position: relative; }
        .pulse::after {
            content: ''; position: absolute; inset: 0; border-radius: 50%;
            background: var(--mark); animation: ripple 1.8s ease-out infinite;
        }
        @keyframes ripple { 0%{transform:scale(1);opacity:.5} 100%{transform:scale(3);opacity:0} }
        @media (prefers-reduced-motion: reduce) { .pulse::after { animation: none; } }

        .headline {
            font-size: clamp(2.4rem, 7vw, 3.6rem); line-height: 1.05;
            font-weight: 500; letter-spacing: -0.02em; margin-bottom: 1.2rem;
        }
        .headline em { font-style: italic; color: var(--mark); }
        .sub {
            font-size: 1.1rem; line-height: 1.5; color: var(--faint);
            max-width: 30rem; margin-bottom: 2.4rem;
        }

        .actions { display: flex; gap: 0.8rem; flex-wrap: wrap; align-items: center; }
        .btn {
            font-family: 'JetBrains Mono', monospace; font-size: 0.82rem;
            letter-spacing: 0.03em; padding: 0.85rem 1.6rem; border-radius: 4px;
            text-decoration: none; transition: opacity 0.15s, transform 0.1s; cursor: pointer;
        }
        .btn:active { transform: translateY(1px); }
        .btn-solid { background: var(--ink); color: var(--paper); border: 1px solid var(--ink); }
        .btn-solid:hover { opacity: 0.85; }
        .btn-ghost { background: transparent; color: var(--ink); border: 1px solid var(--line); }
        .btn-ghost:hover { border-color: var(--ink); }

        .how {
            margin-top: 3.5rem; padding-top: 1.6rem; border-top: 1px solid var(--line);
            display: flex; gap: 2rem; flex-wrap: wrap;
        }
        .step { flex: 1; min-width: 8rem; }
        .step-n {
            font-family: 'JetBrains Mono', monospace; font-size: 0.7rem;
            color: var(--mark); margin-bottom: 0.4rem;
        }
        .step-t { font-size: 0.95rem; line-height: 1.4; }

        .foot {
            text-align: center; padding: 1.6rem 0;
            font-family: 'JetBrains Mono', monospace; font-size: 0.68rem;
            color: var(--faint); letter-spacing: 0.04em; border-top: 1px solid var(--line);
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="topbar-inner">
            <span class="brand">Lara Poll<span class="brand-dot">.</span></span>
            <nav class="nav">
                @auth
                    @if(auth()->user()->is_admin)
                        <a href="/admin">admin →</a>
                    @endif
                    <span class="strong">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit">log out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}">log in</a>
                    <a href="{{ route('register') }}" class="strong">sign up</a>
                @endauth
            </nav>
        </div>
    </header>

    <section class="hero">
        <p class="eyebrow"><span class="pulse"></span> live polling</p>

        <h1 class="headline">Ask a question.<br>Watch the room <em>decide.</em></h1>

        <p class="sub">
            Lara Poll counts votes the moment they land — no refresh, no waiting. Spin up a poll,
            share the link, and watch the bars move in real time.
        </p>

        <div class="actions">
            <a href="{{ route('polls.index') }}" class="btn btn-solid">Browse polls</a>
            @guest
                <a href="{{ route('register') }}" class="btn btn-ghost">Create an account</a>
            @endguest
        </div>

        <div class="how">
            <div class="step">
                <div class="step-n">first</div>
                <p class="step-t">Pick a poll, or sign in to make your own.</p>
            </div>
            <div class="step">
                <div class="step-n">then</div>
                <p class="step-t">Cast your vote — one per person.</p>
            </div>
            <div class="step">
                <div class="step-n">live</div>
                <p class="step-t">Results update as everyone weighs in.</p>
            </div>
        </div>
    </section>

    <footer class="foot">made with Lara Poll · live polling</footer>
</body>
</html>
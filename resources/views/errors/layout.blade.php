<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title') — {{ $branding['appName'] ?? config('app.name') }}</title>
        <link rel="icon" type="image/png" href="{{ $branding['faviconUrl'] ?? '/favicon.ico' }}">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|fraunces:600,700&display=swap" rel="stylesheet" />
        <style>
            :root {
                --brand: {{ $branding['primaryColor'] ?? '#6366f1' }};
                --paper: #faf8f5;
                --warm-800: #292524;
                --warm-900: #1c1917;
                --slate-400: #94a3b8;
                --slate-500: #64748b;
                --slate-600: #475569;
            }

            * { box-sizing: border-box; margin: 0; padding: 0; }

            body {
                min-height: 100vh;
                font-family: Inter, ui-sans-serif, system-ui, sans-serif;
                color: var(--warm-800);
                background: var(--paper);
                -webkit-font-smoothing: antialiased;
            }

            .page {
                position: relative;
                display: flex;
                min-height: 100vh;
                flex-direction: column;
                overflow: hidden;
            }

            .mesh {
                position: absolute;
                inset: 0;
                z-index: 0;
                background-color: var(--paper);
                background-image:
                    url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E"),
                    radial-gradient(ellipse at 20% 30%, rgba(251, 207, 232, 0.25) 0%, transparent 50%),
                    radial-gradient(ellipse at 80% 70%, rgba(199, 210, 254, 0.2) 0%, transparent 50%),
                    linear-gradient(180deg, #faf8f5 0%, #f8f6f3 50%, #faf8f5 100%);
            }

            .orb {
                position: absolute;
                border-radius: 9999px;
                filter: blur(64px);
                pointer-events: none;
            }

            .orb-1 {
                left: -6rem;
                top: 0;
                width: 28rem;
                height: 28rem;
                background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, rgba(99, 102, 241, 0) 70%);
            }

            .orb-2 {
                right: -4rem;
                top: 25%;
                width: 24rem;
                height: 24rem;
                background: radial-gradient(circle, rgba(139, 92, 246, 0.12) 0%, rgba(139, 92, 246, 0) 70%);
            }

            .dot-grid {
                position: absolute;
                inset: 0;
                opacity: 0.35;
                background-image: radial-gradient(circle, rgba(99, 102, 241, 0.12) 1px, transparent 1px);
                background-size: 24px 24px;
            }

            .watermark {
                position: absolute;
                inset: 0;
                z-index: 1;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
                pointer-events: none;
                user-select: none;
            }

            .watermark span {
                font-family: Fraunces, Georgia, serif;
                font-size: clamp(12rem, 32vw, 22rem);
                font-weight: 700;
                line-height: 1;
                letter-spacing: -0.05em;
                background: linear-gradient(180deg, rgba(231, 229, 228, 0.85) 0%, rgba(224, 231, 255, 0.35) 50%, transparent 100%);
                -webkit-background-clip: text;
                background-clip: text;
                color: transparent;
            }

            .header,
            .main,
            .footer {
                position: relative;
                z-index: 2;
            }

            .header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                max-width: 80rem;
                width: 100%;
                margin: 0 auto;
                padding: 1.5rem 1rem;
            }

            @media (min-width: 640px) {
                .header { padding-left: 1.5rem; padding-right: 1.5rem; }
            }

            .logo img { height: 2.25rem; width: auto; display: block; }
            .logo strong { font-size: 1.125rem; color: var(--warm-900); text-decoration: none; }

            .badge {
                border: 1px solid rgba(231, 229, 228, 0.8);
                background: rgba(250, 248, 245, 0.6);
                backdrop-filter: blur(8px);
                border-radius: 9999px;
                padding: 0.25rem 0.75rem;
                font-size: 0.6875rem;
                font-weight: 600;
                letter-spacing: 0.2em;
                text-transform: uppercase;
                color: var(--slate-500);
            }

            .main {
                flex: 1;
                display: flex;
                flex-direction: column;
                justify-content: center;
                max-width: 48rem;
                width: 100%;
                margin: 0 auto;
                padding: 1rem 1rem 4rem;
            }

            @media (min-width: 640px) {
                .main { padding-left: 1.5rem; padding-right: 1.5rem; }
            }

            .eyebrow {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                margin-bottom: 1.5rem;
            }

            .eyebrow-line {
                width: 2.5rem;
                height: 1px;
                background: linear-gradient(90deg, var(--brand), #8b5cf6);
            }

            .eyebrow-text {
                font-size: 0.75rem;
                font-weight: 600;
                letter-spacing: 0.25em;
                text-transform: uppercase;
                color: var(--brand);
            }

            h1 {
                font-family: Fraunces, Georgia, serif;
                font-size: clamp(2.25rem, 5vw, 3.75rem);
                font-weight: 700;
                line-height: 1.05;
                letter-spacing: -0.03em;
                color: var(--warm-900);
            }

            .message {
                margin-top: 1.25rem;
                max-width: 36rem;
                font-size: clamp(1rem, 2.2vw, 1.25rem);
                line-height: 1.65;
                color: var(--slate-600);
            }

            .actions {
                margin-top: 2.5rem;
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
            }

            @media (min-width: 640px) {
                .actions { flex-direction: row; align-items: center; }
            }

            .btn-primary {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.75rem 1.75rem;
                border-radius: 9999px;
                font-size: 0.875rem;
                font-weight: 600;
                text-decoration: none;
                color: #fff;
                background: var(--brand);
                box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
                transition: transform 0.2s, box-shadow 0.2s, opacity 0.2s;
            }

            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 20px rgba(99, 102, 241, 0.25);
            }

            .btn-secondary {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.75rem 1.75rem;
                border-radius: 9999px;
                font-size: 0.875rem;
                font-weight: 600;
                text-decoration: none;
                color: var(--warm-800);
                background: var(--paper);
                border: 1px solid #e7e5e4;
                transition: transform 0.2s, border-color 0.2s, box-shadow 0.2s;
            }

            .btn-secondary:hover {
                transform: translateY(-2px);
                border-color: #c7d2fe;
                box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
            }

            .meta {
                margin-top: 4rem;
                padding-top: 2rem;
                border-top: 1px solid rgba(231, 229, 228, 0.8);
                display: grid;
                gap: 1rem;
            }

            @media (min-width: 640px) {
                .meta { grid-template-columns: repeat(3, 1fr); }
            }

            .meta-label {
                font-size: 0.6875rem;
                font-weight: 600;
                letter-spacing: 0.08em;
                text-transform: uppercase;
                color: var(--slate-400);
            }

            .meta-link {
                display: inline-block;
                margin-top: 0.25rem;
                font-size: 0.875rem;
                font-weight: 500;
                color: var(--warm-800);
                text-decoration: none;
            }

            .meta-link:hover { color: var(--brand); text-decoration: underline; }

            .meta-value {
                margin-top: 0.25rem;
                font-size: 0.875rem;
                font-weight: 500;
                color: var(--slate-600);
            }

            .footer {
                border-top: 1px solid rgba(231, 229, 228, 0.6);
                background: rgba(250, 248, 245, 0.4);
                backdrop-filter: blur(8px);
                padding: 1.25rem 1rem;
                text-align: center;
                font-size: 0.75rem;
                color: var(--slate-400);
            }
        </style>
    </head>
    <body>
        <div class="page">
            <div class="mesh" aria-hidden="true">
                <div class="orb orb-1"></div>
                <div class="orb orb-2"></div>
                <div class="dot-grid"></div>
            </div>

            <div class="watermark" aria-hidden="true">
                <span>@yield('code')</span>
            </div>

            <header class="header">
                <a href="{{ url('/') }}" class="logo">
                    @if (! empty($branding['siteLogoUrl']))
                        <img src="{{ $branding['siteLogoUrl'] }}" alt="{{ $branding['appName'] ?? config('app.name') }}">
                    @else
                        <strong>{{ $branding['appName'] ?? config('app.name') }}</strong>
                    @endif
                </a>
                <span class="badge">Error @yield('code')</span>
            </header>

            <main class="main">
                <div class="eyebrow">
                    <span class="eyebrow-line"></span>
                    <span class="eyebrow-text">@yield('code')</span>
                </div>

                <h1>@yield('title')</h1>
                <p class="message">@yield('message')</p>

                <div class="actions">
                    <a href="{{ url('/') }}" class="btn-primary">Go to homepage</a>
                    <a href="javascript:history.back()" class="btn-secondary">Go back</a>
                </div>

                <div class="meta">
                    <div>
                        <p class="meta-label">Need help?</p>
                        <a href="{{ route('docs') }}" class="meta-link">Browse documentation</a>
                    </div>
                    <div>
                        <p class="meta-label">Product</p>
                        <a href="{{ route('features') }}" class="meta-link">Explore features</a>
                    </div>
                    <div>
                        <p class="meta-label">Status</p>
                        <p class="meta-value">HTTP @yield('code')</p>
                    </div>
                </div>
            </main>

            <footer class="footer">
                &copy; {{ date('Y') }} {{ $branding['appName'] ?? config('app.name') }}
            </footer>
        </div>
    </body>
</html>

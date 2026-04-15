<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sitio en desarrollo - {{ $company->name ?? 'Sushi Burger Experience' }}</title>
    <style>
        :root {
            --bg: #020617;
            --card: #020617;
            --accent: #7c3aed;
            --accent-soft: rgba(124, 58, 237, 0.3);
            --text: #f9fafb;
            --muted: #9ca3af;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(circle at top left, rgba(124,58,237,0.18), transparent 55%),
                radial-gradient(circle at bottom right, rgba(236,72,153,0.18), transparent 55%),
                #020617;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "SF Pro Text", sans-serif;
            color: var(--text);
            padding: 16px;
        }
        .card {
            position: relative;
            max-width: 420px;
            width: 100%;
            padding: 32px 24px 24px;
            border-radius: 24px;
            background: radial-gradient(circle at top, rgba(15,23,42,0.9), rgba(15,23,42,0.98));
            border: 1px solid rgba(148,163,184,0.25);
            box-shadow:
                0 30px 80px rgba(15, 23, 42, 0.95),
                0 0 0 1px rgba(15,23,42,0.9);
            backdrop-filter: blur(26px);
        }
        h1 {
            font-size: 2.2rem;
            line-height: 1.1;
            letter-spacing: -0.03em;
            margin-bottom: 10px;
        }
        .brand {
            margin-bottom: 16px;
            font-weight: 600;
            font-size: 0.95rem;
            color: #e5e7eb;
        }
        .subtitle {
            font-size: 0.98rem;
            color: var(--muted);
            line-height: 1.6;
            margin-bottom: 24px;
        }
        .login-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 22px;
            border-radius: 999px;
            border: none;
            background: linear-gradient(135deg, #7c3aed, #ec4899);
            color: #f9fafb;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 14px 35px rgba(124,58,237,0.55);
            transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
        }
        .login-button:hover {
            transform: translateY(-1px);
            filter: brightness(1.05);
            box-shadow: 0 18px 45px rgba(124,58,237,0.7);
        }
        .login-button span.icon {
            display: inline-flex;
            width: 18px;
            height: 18px;
            border-radius: 999px;
            background: rgba(15,23,42,0.65);
            align-items: center;
            justify-content: center;
            font-size: 11px;
        }
        .footer {
            margin-top: 20px;
            border-top: 1px solid rgba(15,23,42,0.9);
            padding-top: 14px;
            font-size: 0.75rem;
            color: #6b7280;
            display: flex;
            justify-content: space-between;
            gap: 8px;
            flex-wrap: wrap;
        }
        .footer a {
            color: inherit;
            text-decoration: none;
            font-weight: 600;
        }
        .glow-orbit {
            position: absolute;
            inset: -1px;
            border-radius: inherit;
            background: conic-gradient(from 140deg, rgba(129,140,248,0.8), rgba(236,72,153,0.7), rgba(56,189,248,0.9), rgba(129,140,248,0.9));
            opacity: 0.28;
            filter: blur(26px);
            z-index: -1;
        }
        @media (max-width: 480px) {
            .card { padding: 26px 18px 20px; }
            h1 { font-size: 1.6rem; }
        }
    </style>
</head>
<body>
<div class="card">
    <div class="glow-orbit"></div>
    <div class="pill">
        {{ $company->name ?? 'Tu marca' }}
    </div>

    <div class="brand">
        {{ $company->name ?? 'Tu marca' }}
    </div>

    <h1>Sitio en desarrollo</h1>

    <p class="subtitle">
        Estamos preparando una nueva experiencia para tus pedidos.
    </p>

    <a href="{{ route('login') }}" class="login-button">
        <span class="icon">↪</span>
        <span>Iniciar sesión</span>
    </a>

    <div class="footer">
        <span>Temporariamente fuera de línea para el público</span>
        <span>Powered by <a href="https://mvdstudio.com.uy" target="_blank" rel="noopener noreferrer">MVD Studio</a></span>
    </div>
</div>
</body>
</html>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to MaplePHP</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:       #0d0d0f;
            --surface:  #16161a;
            --border:   #242428;
            --accent:   #48B585;
            --accent2:  #297252;
            --text:     #e8e8ea;
            --muted:    #6b6b75;
            --code-bg:  #1c1c21;
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px 24px;
            line-height: 1.6;
        }

        /* Radial glow behind the hero */
        body::before {
            content: '';
            position: fixed;
            top: -20%;
            left: 50%;
            transform: translateX(-50%);
            width: 700px;
            height: 500px;
            background: radial-gradient(ellipse at center, rgba(72,181,133,.13) 0%, transparent 70%);
            pointer-events: none;
        }

        .wrap {
            width: 100%;
            max-width: 760px;
        }

        /* ── Hero ── */
        .hero {
            text-align: center;
            padding-bottom: 56px;
            animation: fadeUp .6s ease both;
        }

        .logo {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 28px;
        }

        .logo svg {
            width: 40px;
            height: auto;
            flex-shrink: 0;
        }

        .logo-name {
            font-size: 1.1rem;
            font-weight: 600;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--muted);
        }

        .hero h1 {
            font-size: clamp(2.4rem, 6vw, 3.6rem);
            font-weight: 700;
            letter-spacing: -.02em;
            line-height: 1.1;
            margin-bottom: 20px;
        }

        .hero h1 span {
            background: linear-gradient(90deg, var(--accent), #7dd4ab);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero p {
            font-size: 1.1rem;
            color: var(--muted);
            max-width: 480px;
            margin: 0 auto 36px;
        }

        .hero-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 24px;
            border-radius: 8px;
            font-size: .9rem;
            font-weight: 500;
            text-decoration: none;
            transition: opacity .15s, transform .15s;
        }

        .btn:hover { opacity: .85; transform: translateY(-1px); }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            color: #fff;
        }

        .btn-ghost {
            border: 1px solid var(--border);
            color: var(--text);
            background: var(--surface);
        }

        /* ── Feature grid ── */
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 1px;
            background: var(--border);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            animation: fadeUp .6s .1s ease both;
        }

        .feature {
            background: var(--surface);
            padding: 24px;
        }

        .feature-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: var(--code-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
            font-size: 1.1rem;
        }

        .feature h3 {
            font-size: .9rem;
            font-weight: 600;
            margin-bottom: 6px;
            color: var(--text);
        }

        .feature p {
            font-size: .8rem;
            color: var(--muted);
            line-height: 1.5;
        }

        /* ── Code snippet ── */
        .snippet {
            margin-top: 32px;
            background: var(--code-bg);
            border: 1px solid var(--border);
            border-radius: 10px;
            overflow: hidden;
            animation: fadeUp .6s .2s ease both;
        }

        .snippet-bar {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            border-bottom: 1px solid var(--border);
        }

        .dot { width: 10px; height: 10px; border-radius: 50%; }
        .dot-r { background: #ff5f57; }
        .dot-y { background: #febc2e; }
        .dot-g { background: #28c840; }

        .snippet-label {
            margin-left: auto;
            font-size: .72rem;
            color: var(--muted);
            letter-spacing: .04em;
        }

        .snippet pre {
            padding: 20px 24px;
            font-family: 'SF Mono', 'Fira Code', monospace;
            font-size: .8rem;
            line-height: 1.7;
            overflow-x: auto;
            color: #c9d1d9;
        }

        .kw  { color: #ff7b72; }
        .fn  { color: #d2a8ff; }
        .st  { color: #a5d6ff; }
        .cm  { color: #6b6b75; font-style: italic; }
        .va  { color: #ffa657; }

        /* ── Footer ── */
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: .8rem;
            color: var(--muted);
            animation: fadeUp .6s .3s ease both;
        }

        .footer a { color: var(--muted); text-decoration: underline; }
        .footer a:hover { color: var(--text); }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
<div class="wrap">

    <div class="hero">
        <div class="logo">
            <svg width="160" height="130" viewBox="0 0 160 130" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><path d="M32.663 100.32c1.876-10.346 5.272-17.294 10.19-20.842 7.376-5.324 14.523-8.323 20.344-11.26 5.822-2.936 16.91-9.044 19.252-12.971 2.342-3.927 6.627-10.46-1.845-19.22-8.472-8.761-12.203-11.984-12.203-18.7 0-6.714 3.434-7.997 5.06-7.997 1.625 0 7.788 2.208 12.038 5.103 4.25 2.895 11.27 7.654 13.33 15.507 2.059 7.853.873 14.062-.732 17.103-1.604 3.041-4.196 7.123-7.817 10.087-2.414 1.976-6.321 4.562-11.722 7.756 6.402-2.495 11.041-5.08 13.916-7.756 4.312-4.012 8.837-10.09 9.579-15.274.742-5.184 1.914-15.07-5.605-23.124-7.52-8.054-17.7-12.38-19.098-12.939a44.56 44.56 0 0 0-2.914-1.046C77.63 2.012 81.317.497 85.499.204c6.272-.44 16.87-.513 27.579 3.488 10.709 4 24.005 10.962 32.883 22.146 8.879 11.183 15.21 27.088 13.857 42.38-1.353 15.293-3.998 24.359-15.462 38.075-11.464 13.716-30.531 21.023-42.303 23.1-11.773 2.075-31.206-1.513-40.953-5.515-9.747-4.002-15.24-9.49-18.247-12.136a80.448 80.448 0 0 1-5.63-5.45c5.773-2.417 11.545-3.625 17.317-3.625 8.657 0 18.395 1 22.81 1.838 4.415.838 12.217 3.163 16.847 3.163 4.631 0 17.757.181 28.304-5.001 10.547-5.183 20.672-13.617 24.117-24.342 2.296-7.15 3.232-13.252 2.805-18.307-.926 11.148-3.96 19.638-9.1 25.469-7.711 8.746-17.122 14.575-25.04 15.877-7.917 1.303-14.611 2.817-27.73 0-13.117-2.817-29.793-6.797-40.062-4.807-6.845 1.327-11.788 2.581-14.828 3.764Z" fill="#48B585"/><g fill="#297252"><path d="M40.756 51.509c3.065 5.201 5.74 8.46 8.027 9.776 3.429 1.973 8.393 2.898 11.567 1.945 2.116-.636 3.761-1.95 4.936-3.942-6.022-.562-10.277-1.3-12.764-2.216-2.488-.916-4.414-1.738-5.779-2.467 4.704 1.32 8.147 2.142 10.331 2.467 2.184.324 4.921.324 8.212 0 .214-1.82-.647-3.486-2.584-5.001-2.906-2.273-5.95-2.01-7.935-2.01-1.984 0-7.825 1.448-9.26 1.448h-4.75ZM2.58 57.666c3.247 7.082 6.355 11.709 9.324 13.88 4.453 3.257 11.313 5.627 16.028 5.267 3.143-.24 5.762-1.42 7.857-3.543-8.44-2.195-14.33-4.16-17.671-5.897-3.34-1.736-5.903-3.218-7.688-4.445 6.404 2.783 11.124 4.646 14.16 5.59 3.034.942 6.925 1.63 11.672 2.063.693-2.153-.176-4.392-2.606-6.718-3.646-3.488-8.03-3.934-10.85-4.433-2.82-.498-11.433-.21-13.472-.57L2.58 57.666ZM7.873 24.18c.068 6.806.848 11.497 2.34 14.072 2.236 3.862 6.56 7.569 10.192 8.431 2.422.575 4.765.222 7.03-1.058-5.56-3.95-9.295-7.074-11.208-9.372-1.913-2.298-3.33-4.188-4.252-5.67 3.847 3.944 6.745 6.691 8.694 8.241 1.948 1.55 4.619 3.097 8.012 4.639 1.23-1.654 1.326-3.766.287-6.339-1.558-3.858-4.676-5.321-6.612-6.442-1.936-1.12-8.448-3.008-9.848-3.818l-4.635-2.683ZM26.102 19.874c8.15-3.043 14.103-4.261 17.86-3.655 5.634.91 12.04 4.394 14.737 8.355 1.798 2.64 2.453 5.612 1.966 8.918-7.264-4.855-12.706-7.902-16.326-9.14-3.62-1.24-6.525-2.072-8.716-2.497 6.471 2.803 11.079 5.018 13.823 6.643 2.743 1.625 5.814 4.118 9.211 7.48-1.407 2.235-3.883 3.32-7.429 3.255-5.318-.097-8.495-3.165-10.72-4.972-2.226-1.808-7.467-8.752-9.077-10.06l-5.329-4.327Z"/></g></g></svg>
            <span class="logo-name">MaplePHP</span>
        </div>

        <h1>Your code.<br><span>Your framework.</span></h1>

        <p>High-performance PHP 8.2+ built on PSR standards. Every component is swappable - shape it around your stack.</p>

        <div class="hero-actions">
            <a href="<?= htmlspecialchars($twigLink, ENT_QUOTES) ?>" class="btn btn-primary">
                &#9670; Twig example
            </a>
            <a href="https://github.com/maplephp/maplephp" class="btn btn-ghost" target="_blank" rel="noopener">
                GitHub &nearr;
            </a>
        </div>
    </div>

    <div class="features">
        <div class="feature">
            <div class="feature-icon">&#9670;</div>
            <h3>MVC + DI Container</h3>
            <p>PSR-11 container with reflection-based autowiring. Controllers, services, and providers out of the box.</p>
        </div>
        <div class="feature">
            <div class="feature-icon">&#9671;</div>
            <h3>PSR-7 HTTP</h3>
            <p>Immutable request and response objects. FastRoute URL matching. PSR-15 middleware pipeline.</p>
        </div>
        <div class="feature">
            <div class="feature-icon">&#9670;</div>
            <h3>Twig Templates</h3>
            <p>First-class Twig integration with layout inheritance, block overrides, and a one-call render helper.</p>
        </div>
        <div class="feature">
            <div class="feature-icon">&#9671;</div>
            <h3>Database + Migrations</h3>
            <p>Doctrine DBAL query builder. Timestamped migration classes with up/down support.</p>
        </div>
        <div class="feature">
            <div class="feature-icon">&#9670;</div>
            <h3>PSR-3 Logging</h3>
            <p>File, error_log, and database handlers with automatic size-based log rotation.</p>
        </div>
        <div class="feature">
            <div class="feature-icon">&#9671;</div>
            <h3>CLI + Testing</h3>
            <p>Built-in CLI kernel, interactive prompts, and Unitary — a zero-dependency test runner.</p>
        </div>
    </div>

    <div class="snippet">
        <div class="snippet-bar">
            <span class="dot dot-r"></span>
            <span class="dot dot-y"></span>
            <span class="dot dot-g"></span>
            <span class="snippet-label">app/Controllers/HelloController.php</span>
        </div>
        <pre><span class="kw">class</span> <span class="fn">HelloController</span> <span class="kw">extends</span> <span class="fn">DefaultController</span>
{
    <span class="kw">public function</span> <span class="fn">show</span>(<span class="fn">Twig</span> <span class="va">$twig</span>, <span class="fn">PathInterface</span> <span class="va">$path</span>): <span class="fn">void</span>
    {
        <span class="va">$twig</span>-><span class="fn">render</span>(<span class="st">'views/hello.twig'</span>, [
            <span class="st">'title'</span> => <span class="st">'Hello'</span>,
            <span class="st">'name'</span>  => <span class="va">$path</span>-><span class="fn">select</span>(<span class="st">'name'</span>)-><span class="fn">last</span>(),
        ]);
    }
}</pre>
    </div>

    <div class="footer">
        <p>MaplePHP &mdash; <a href="https://github.com/maplephp/maplephp" target="_blank" rel="noopener">Github</a> &mdash; PHP 8.2+ </p>
    </div>

</div>
</body>
</html>

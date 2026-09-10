<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Consulta pública | Municipalidad de Coatepeque</title>
        <script>
            try {
                document.documentElement.classList.toggle(
                    'light-mode',
                    localStorage.getItem('municipal_portal_theme') === 'light',
                );
            } catch (error) {}
        </script>
        <style>
            :root {
                color-scheme: dark;
                --page: #050707;
                --header: #0b0e0f;
                --nav: #14191a;
                --line: rgba(49, 169, 214, 0.25);
                --text: #f7f4ea;
                --muted: #aaa99f;
                --cyan: #2aa5d3;
                --cyan-deep: #116b90;
                --gold: #d5b26d;
            }

            * { box-sizing: border-box; }
            html, body { width: 100%; min-height: 100%; margin: 0; }
            body { overflow: hidden; color: var(--text); background: var(--page); font-family: "Segoe UI", Arial, Helvetica, sans-serif; }
            button, a { font: inherit; -webkit-tap-highlight-color: transparent; }
            [hidden] { display: none !important; }

            .portal-header { position: relative; z-index: 10; border-bottom: 1px solid var(--line); background: var(--header); box-shadow: 0 12px 34px rgba(0,0,0,.28); }
            .header-top { display: flex; width: min(1500px, calc(100% - 44px)); min-height: 88px; margin: 0 auto; align-items: center; justify-content: space-between; gap: 24px; }
            .brand-cluster { display: flex; min-width: 0; align-items: center; gap: 20px; }
            .brand { display: flex; min-width: 0; align-items: center; }
            .brand__logo { display: block; width: auto; height: 80px; border-radius: 5px; object-fit: contain; }
            .public-badge { padding: 7px 10px; border: 1px solid rgba(213,178,109,.34); border-radius: 999px; color: var(--gold); background: rgba(213,178,109,.08); font-size: .66rem; font-weight: 800; text-transform: uppercase; white-space: nowrap; }

            .header-actions { display: flex; align-items: center; gap: 14px; }
            .theme-toggle { display: grid; width: 42px; height: 42px; padding: 0; place-items: center; border: 0; color: #98a4ad; background: transparent; cursor: pointer; }
            .theme-toggle:hover { color: var(--cyan); }
            .theme-toggle svg { width: 25px; height: 25px; fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
            .theme-toggle__moon { display: none; }
            .login-link { display: inline-flex; min-height: 40px; padding: 0 15px; align-items: center; border: 1px solid rgba(42,165,211,.42); border-radius: 9px; color: #fff; background: var(--cyan-deep); font-size: .72rem; font-weight: 800; text-decoration: none; }

            .nav-shell { border-top: 1px solid rgba(255,255,255,.06); background: var(--nav); }
            .nav-list { display: flex; width: min(1500px, calc(100% - 44px)); min-height: 55px; margin: 0 auto; padding: 0; align-items: stretch; list-style: none; overflow-x: auto; }
            .nav-link { position: relative; display: flex; height: 100%; min-height: 55px; padding: 0 24px; align-items: center; color: var(--muted); font-size: .78rem; font-weight: 800; text-decoration: none; text-transform: uppercase; white-space: nowrap; }
            .nav-link:hover, .nav-link:focus-visible { color: var(--text); background: rgba(42,165,211,.09); }
            .nav-link.is-active { color: #fff; background: linear-gradient(180deg, #237fa5, #166985); }
            .nav-link.is-active::after { content: ""; position: absolute; right: 24px; bottom: 8px; left: 24px; height: 3px; border-radius: 99px; background: #9cdef6; }

            .module-panel { width: 100%; height: calc(100vh - 144px); height: calc(100svh - 144px); background: var(--page); }
            .module-panel iframe { display: block; width: 100%; height: 100%; border: 0; background: var(--page); }

            html.light-mode { color-scheme: light; --page: #eaf1f5; --header: #fff; --nav: #f7fbfd; --line: rgba(31,57,79,.16); --text: #172033; --muted: #66717e; }
            html.light-mode .nav-shell { border-top-color: rgba(31,57,79,.1); box-shadow: 0 8px 22px rgba(31,57,79,.08); }
            html.light-mode .nav-link.is-active { color: #fff; }
            html.light-mode .theme-toggle__sun { display: none; }
            html.light-mode .theme-toggle__moon { display: block; }

            @media (max-width: 720px) {
                .header-top { width: calc(100% - 24px); min-height: 78px; }
                .brand__logo { height: 69px; }
                .public-badge { display: none; }
                .login-link { padding: 0 10px; font-size: .65rem; }
                .nav-list { width: 100%; }
                .nav-link { padding-inline: 16px; font-size: .68rem; }
                .module-panel { height: calc(100vh - 134px); height: calc(100svh - 134px); }
            }
        </style>
    </head>
    <body>
        <header class="portal-header">
            <div class="header-top">
                <div class="brand-cluster">
                    <div class="brand" aria-label="Gobierno Municipal de Coatepeque, Quetzaltenango">
                        <img
                            class="brand__logo"
                            src="{{ asset('images/municipal-coatepeque-login-logo.png') }}"
                            alt="Gobierno Municipal de Coatepeque, Administración 2024-2028"
                        >
                    </div>
                    <span class="public-badge">Consulta pública</span>
                </div>
                <div class="header-actions">
                    <button class="theme-toggle" id="theme-toggle" type="button" aria-label="Cambiar entre modo claro y oscuro">
                        <svg class="theme-toggle__sun" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.42-1.42M17.66 6.34l1.41-1.41"></path></svg>
                        <svg class="theme-toggle__moon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.5 14.3A8.5 8.5 0 0 1 9.7 3.5a8.5 8.5 0 1 0 10.8 10.8Z"></path></svg>
                    </button>
                    <a class="login-link" href="{{ route('login') }}">Acceso administrativo</a>
                </div>
            </div>
            <nav class="nav-shell" aria-label="Consulta pública municipal">
                <ul class="nav-list">
                    <li><a class="nav-link is-active" href="{{ route('public.project-locations') }}" data-module-target="locations" aria-current="page">Ubicación de proyectos</a></li>
                    <li><a class="nav-link" href="{{ route('public.project-monitoring.index') }}" data-module-target="monitoring">Monitoreo</a></li>
                    <li><a class="nav-link" href="{{ route('public.project-finance.index') }}" data-module-target="finance">Control financiero</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <section class="module-panel" data-module-panel="locations">
                <iframe title="Ubicación de proyectos" src="{{ route('public.project-locations', ['embed' => 1]) }}" allow="geolocation"></iframe>
            </section>
            <section class="module-panel" data-module-panel="monitoring" hidden>
                <iframe title="Monitoreo de proyectos" data-src="{{ route('public.project-monitoring.index', ['embed' => 1]) }}"></iframe>
            </section>
            <section class="module-panel" data-module-panel="finance" hidden>
                <iframe title="Control financiero" data-src="{{ route('public.project-finance.index', ['embed' => 1]) }}"></iframe>
            </section>
        </main>

        <script>
            (() => {
                const links = Array.from(document.querySelectorAll('[data-module-target]'));
                const panels = Array.from(document.querySelectorAll('[data-module-panel]'));
                const themeToggle = document.getElementById('theme-toggle');

                const currentTheme = () => document.documentElement.classList.contains('light-mode') ? 'light' : 'dark';
                const notifyTheme = () => {
                    panels.forEach((panel) => panel.querySelector('iframe')?.contentWindow?.postMessage(
                        { type: 'theme-changed', theme: currentTheme() },
                        window.location.origin,
                    ));
                };

                const showModule = (name) => {
                    panels.forEach((panel) => {
                        const active = panel.dataset.modulePanel === name;
                        panel.hidden = !active;
                        if (active) {
                            const frame = panel.querySelector('iframe');
                            if (!frame.src && frame.dataset.src) frame.src = frame.dataset.src;
                        }
                    });
                    links.forEach((link) => {
                        const active = link.dataset.moduleTarget === name;
                        link.classList.toggle('is-active', active);
                        if (active) link.setAttribute('aria-current', 'page');
                        else link.removeAttribute('aria-current');
                    });
                    window.setTimeout(notifyTheme, 80);
                };

                links.forEach((link) => link.addEventListener('click', (event) => {
                    event.preventDefault();
                    showModule(link.dataset.moduleTarget);
                }));

                themeToggle.addEventListener('click', () => {
                    const light = !document.documentElement.classList.contains('light-mode');
                    document.documentElement.classList.toggle('light-mode', light);
                    try { localStorage.setItem('municipal_portal_theme', light ? 'light' : 'dark'); } catch (error) {}
                    notifyTheme();
                });

                panels.forEach((panel) => panel.querySelector('iframe')?.addEventListener('load', notifyTheme));
            })();
        </script>
    </body>
</html>

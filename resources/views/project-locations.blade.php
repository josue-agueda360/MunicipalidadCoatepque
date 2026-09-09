<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Ubicación de Proyectos | Municipalidad de Coatepeque</title>
        <script>
            const publicReadOnly = @json($readOnly);
            const protectedNavigation = performance.getEntriesByType(
                'navigation',
            )[0];
            const protectedReloadKey = 'protected-reload-pending';
            const protectedInternalKey = 'protected-internal-navigation';
            const protectedReloadAllowed =
                protectedNavigation?.type === 'reload'
                && sessionStorage.getItem(protectedReloadKey) === 'true';
            const protectedInternalAllowed =
                protectedNavigation?.type === 'navigate'
                && sessionStorage.getItem(protectedInternalKey) === 'true';

            if (protectedReloadAllowed || protectedInternalAllowed) {
                sessionStorage.setItem('protected-tab-authorized', 'true');
                sessionStorage.removeItem('protected-session-closed');
            }

            sessionStorage.removeItem(protectedReloadKey);
            sessionStorage.removeItem(protectedInternalKey);
            window.__protectedTabAuthorized =
                sessionStorage.getItem('protected-tab-authorized') === 'true';

            if (!publicReadOnly && !window.__protectedTabAuthorized) {
                document.documentElement.style.visibility = 'hidden';
            }

            try {
                document.documentElement.classList.toggle(
                    'light-mode',
                    localStorage.getItem('municipal_portal_theme') === 'light',
                );
            } catch (error) {
                document.documentElement.classList.remove('light-mode');
            }
        </script>
        <link
            rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/cesium@1.143.0/Build/Cesium/Widgets/widgets.css"
        >

        <style>
            :root {
                color-scheme: dark;
                --page: #050606;
                --surface: rgba(15, 20, 22, 0.96);
                --surface-soft: rgba(255, 255, 255, 0.055);
                --line: rgba(255, 255, 255, 0.12);
                --text: #f6f2e7;
                --muted: #bcb7aa;
                --gold: #d5b26d;
                --blue: #136b91;
                --blue-bright: #31a9d6;
            }

            * {
                box-sizing: border-box;
            }

            html,
            body {
                width: 100%;
                min-height: 100%;
                margin: 0;
            }

            body {
                min-height: 100vh;
                color: var(--text);
                background:
                    radial-gradient(
                        circle at 50% 10%,
                        rgba(19, 107, 145, 0.12),
                        transparent 36rem
                    ),
                    var(--page);
                font-family: "Segoe UI", Arial, Helvetica, sans-serif;
            }

            button,
            input {
                font: inherit;
            }

            button,
            a {
                -webkit-tap-highlight-color: transparent;
            }

            [hidden] {
                display: none !important;
            }

            .portal-header {
                position: relative;
                z-index: 20;
                border-bottom: 1px solid rgba(49, 169, 214, 0.28);
                background: linear-gradient(
                    180deg,
                    rgba(10, 12, 12, 0.98),
                    rgba(13, 15, 15, 0.96)
                );
                box-shadow: 0 18px 50px rgba(0, 0, 0, 0.38);
            }

            .header-top {
                display: grid;
                grid-template-columns:
                    minmax(270px, 1fr)
                    minmax(260px, 420px)
                    minmax(360px, 1fr);
                width: min(1440px, calc(100% - 48px));
                min-height: 94px;
                margin: 0 auto;
                align-items: center;
                gap: 34px;
            }

            .brand {
                position: relative;
                display: block;
                width: 310px;
                height: 74px;
                overflow: hidden;
                text-decoration: none;
            }

            .brand__art {
                position: absolute;
                top: 0;
                left: 0;
                width: 310px;
                height: 80px;
                background-image: url('{{ asset('images/municipal-coatepeque-header.png') }}');
                background-repeat: no-repeat;
                background-position: -229px -7px;
                background-size: 1917px 152px;
                transform: scale(0.92);
                transform-origin: top left;
            }

            .brand__wordmark {
                position: absolute;
                z-index: 2;
                top: 8px;
                right: 0;
                bottom: 8px;
                left: 88px;
                display: flex;
                padding-left: 7px;
                flex-direction: column;
                justify-content: center;
                color: #fff;
                background: #0c0e0e;
                line-height: 1;
                white-space: nowrap;
            }

            .brand__wordmark strong {
                font-family: Georgia, "Times New Roman", serif;
                font-size: 1.05rem;
                font-weight: 800;
                letter-spacing: -0.035em;
                text-transform: uppercase;
            }

            .brand__wordmark small {
                margin-top: 3px;
                font-family: Georgia, "Times New Roman", serif;
                font-size: 0.56rem;
                font-weight: 700;
                letter-spacing: 0.015em;
                text-transform: uppercase;
            }

            .header-actions {
                display: flex;
                min-width: 0;
                align-items: center;
                justify-content: flex-end;
                gap: 22px;
                grid-column: 3;
            }

            .contact {
                margin: 0;
                color: var(--muted);
                font-size: 0.72rem;
                letter-spacing: 0.055em;
                text-transform: uppercase;
                white-space: nowrap;
            }

            .contact strong {
                color: var(--text);
                font-size: 0.82rem;
            }

            .logout-form {
                margin: 0;
            }

            .logout-button {
                display: inline-flex;
                min-height: 40px;
                padding: 0 16px;
                align-items: center;
                justify-content: center;
                gap: 9px;
                border: 1px solid rgba(49, 169, 214, 0.5);
                border-radius: 9px;
                color: #f7fbfd;
                background: linear-gradient(
                    135deg,
                    rgba(19, 107, 145, 0.78),
                    rgba(15, 67, 88, 0.72)
                );
                cursor: pointer;
                font-size: 0.76rem;
                font-weight: 800;
                letter-spacing: 0.035em;
                text-transform: uppercase;
            }

            .logout-button::before {
                content: "↗";
                font-size: 0.94rem;
                transform: rotate(45deg);
            }

            .logout-button:hover,
            .logout-button:focus-visible {
                border-color: rgba(86, 207, 248, 0.85);
                background: linear-gradient(
                    135deg,
                    rgba(24, 128, 170, 0.94),
                    rgba(15, 76, 101, 0.9)
                );
                outline: none;
            }

            .nav-shell {
                border-top: 1px solid rgba(255, 255, 255, 0.055);
                background:
                    linear-gradient(
                        90deg,
                        rgba(25, 79, 101, 0.24),
                        rgba(255, 255, 255, 0.035) 30%,
                        rgba(213, 178, 109, 0.1)
                    ),
                    rgba(255, 255, 255, 0.025);
            }

            .nav-list {
                display: flex;
                width: min(1440px, calc(100% - 48px));
                min-height: 44px;
                margin: 0 auto;
                padding: 0;
                align-items: stretch;
                gap: 4px;
                overflow-x: auto;
                list-style: none;
                scrollbar-width: none;
            }

            .nav-list::-webkit-scrollbar {
                display: none;
            }

            .nav-list li {
                display: flex;
                flex: 0 0 auto;
            }

            .nav-link {
                position: relative;
                display: inline-flex;
                min-height: 42px;
                padding: 0 18px;
                align-items: center;
                color: rgba(246, 242, 231, 0.76);
                background: transparent;
                font-size: 0.79rem;
                font-weight: 750;
                letter-spacing: 0.045em;
                text-decoration: none;
                text-transform: uppercase;
                white-space: nowrap;
            }

            .nav-link::after {
                position: absolute;
                right: 18px;
                bottom: 8px;
                left: 18px;
                height: 2px;
                border-radius: 999px;
                background: var(--blue-bright);
                content: "";
                opacity: 0;
            }

            .nav-link:hover,
            .nav-link:focus-visible {
                color: #fff;
                background: rgba(255, 255, 255, 0.055);
                outline: none;
            }

            .nav-link.is-active {
                color: #fff;
                background: linear-gradient(
                    180deg,
                    rgba(35, 125, 162, 0.82),
                    rgba(22, 87, 115, 0.88)
                );
                box-shadow:
                    inset 0 1px 0 rgba(255, 255, 255, 0.16),
                    0 8px 24px rgba(0, 0, 0, 0.22);
            }

            .nav-link.is-active::after {
                opacity: 0.9;
            }

            .location-module {
                padding: 24px;
            }

            .location-workspace {
                display: grid;
                grid-template-columns: minmax(290px, 360px) minmax(0, 1fr);
                width: min(1540px, 100%);
                min-height: 690px;
                margin: 0 auto;
                overflow: hidden;
                border: 1px solid rgba(49, 169, 214, 0.2);
                border-radius: 18px;
                background: rgba(7, 10, 11, 0.96);
                box-shadow:
                    0 24px 70px rgba(0, 0, 0, 0.44),
                    inset 0 1px 0 rgba(255, 255, 255, 0.04);
            }

            .project-finder {
                position: relative;
                z-index: 4;
                display: flex;
                min-width: 0;
                min-height: 690px;
                flex-direction: column;
                border-right: 1px solid rgba(255, 255, 255, 0.1);
                background:
                    linear-gradient(
                        180deg,
                        rgba(17, 27, 30, 0.98),
                        rgba(8, 12, 13, 0.98)
                    );
            }

            .finder-head {
                padding: 22px 20px 16px;
                border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            }

            .finder-eyebrow {
                margin: 0 0 6px;
                color: var(--gold);
                font-size: 0.68rem;
                font-weight: 850;
                letter-spacing: 0.12em;
                text-transform: uppercase;
            }

            .finder-head h1 {
                margin: 0;
                color: #fff;
                font-size: 1.15rem;
                line-height: 1.25;
            }

            .finder-head p:last-child {
                margin: 7px 0 0;
                color: rgba(246, 242, 231, 0.55);
                font-size: 0.74rem;
                line-height: 1.5;
            }

            .finder-search-wrap {
                padding: 16px 18px 12px;
            }

            #finder-mode {
                display: flex;
                min-height: 0;
                flex: 1;
                flex-direction: column;
            }

            .finder-search {
                position: relative;
            }

            .finder-search::before {
                position: absolute;
                z-index: 1;
                top: 50%;
                left: 14px;
                color: var(--gold);
                content: "⌕";
                font-size: 1.15rem;
                pointer-events: none;
                transform: translateY(-53%);
            }

            .finder-search input {
                width: 100%;
                min-height: 46px;
                padding: 0 42px;
                border: 1px solid rgba(49, 169, 214, 0.28);
                border-radius: 10px;
                color: #fff;
                background: rgba(2, 7, 8, 0.88);
                outline: none;
                font-size: 0.78rem;
                font-weight: 650;
            }

            .finder-search input:focus {
                border-color: rgba(49, 169, 214, 0.8);
                box-shadow: 0 0 0 4px rgba(49, 169, 214, 0.1);
            }

            .finder-search input::placeholder {
                color: rgba(246, 242, 231, 0.38);
            }

            .search-clear {
                position: absolute;
                top: 50%;
                right: 8px;
                display: grid;
                width: 30px;
                height: 30px;
                padding: 0;
                place-items: center;
                border: 0;
                border-radius: 50%;
                color: rgba(255, 255, 255, 0.65);
                background: transparent;
                cursor: pointer;
                transform: translateY(-50%);
            }

            .search-clear:hover {
                color: #fff;
                background: rgba(255, 255, 255, 0.08);
            }

            .result-count {
                margin: 9px 2px 0;
                color: rgba(246, 242, 231, 0.48);
                font-size: 0.68rem;
            }

            .project-results {
                display: grid;
                min-height: 0;
                margin: 0;
                padding: 0 12px 18px;
                flex: 1;
                align-content: start;
                gap: 8px;
                overflow-y: auto;
                list-style: none;
                scrollbar-color: rgba(49, 169, 214, 0.7) transparent;
                scrollbar-width: thin;
            }

            .project-result {
                display: grid;
                width: 100%;
                padding: 12px;
                grid-template-columns: 36px minmax(0, 1fr) 18px;
                align-items: center;
                gap: 10px;
                border: 1px solid rgba(255, 255, 255, 0.09);
                border-radius: 11px;
                color: var(--text);
                background: rgba(255, 255, 255, 0.035);
                cursor: pointer;
                text-align: left;
                transition:
                    border-color 150ms ease,
                    background-color 150ms ease,
                    transform 150ms ease;
            }

            .project-result:hover,
            .project-result:focus-visible {
                border-color: rgba(49, 169, 214, 0.58);
                background: rgba(49, 169, 214, 0.11);
                outline: none;
                transform: translateX(2px);
            }

            .result-marker {
                display: grid;
                width: 34px;
                height: 34px;
                place-items: center;
                border: 2px solid rgba(255, 255, 255, 0.72);
                border-radius: 50% 50% 50% 0;
                color: #081014;
                background: var(--marker-color, #e4a52c);
                font-size: 0.67rem;
                font-weight: 900;
                transform: rotate(-45deg);
            }

            .result-marker span {
                transform: rotate(45deg);
            }

            .result-copy {
                min-width: 0;
            }

            .result-copy strong,
            .result-copy small {
                display: block;
            }

            .result-copy strong {
                overflow: hidden;
                color: #fff;
                font-size: 0.75rem;
                line-height: 1.3;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .result-copy small {
                margin-top: 4px;
                overflow: hidden;
                color: rgba(246, 242, 231, 0.48);
                font-size: 0.66rem;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .result-arrow {
                color: var(--blue-bright);
                font-size: 1.05rem;
            }

            .empty-results {
                margin: 16px 18px;
                padding: 22px 16px;
                border: 1px dashed rgba(255, 255, 255, 0.14);
                border-radius: 12px;
                color: rgba(246, 242, 231, 0.5);
                font-size: 0.76rem;
                line-height: 1.55;
                text-align: center;
            }

            .route-detail {
                display: flex;
                min-height: 0;
                padding: 20px 18px;
                flex: 1;
                flex-direction: column;
                gap: 16px;
                overflow-y: auto;
            }

            .back-to-search {
                display: inline-flex;
                width: fit-content;
                min-height: 36px;
                padding: 0 12px;
                align-items: center;
                gap: 7px;
                border: 1px solid rgba(49, 169, 214, 0.45);
                border-radius: 8px;
                color: #d9f5ff;
                background: rgba(19, 107, 145, 0.14);
                cursor: pointer;
                font-size: 0.7rem;
                font-weight: 800;
            }

            .route-project {
                padding: 16px;
                border: 1px solid rgba(49, 169, 214, 0.24);
                border-radius: 13px;
                background: rgba(49, 169, 214, 0.07);
            }

            .route-project span,
            .route-project strong,
            .route-project small {
                display: block;
            }

            .route-project span {
                color: var(--gold);
                font-size: 0.64rem;
                font-weight: 850;
                letter-spacing: 0.1em;
                text-transform: uppercase;
            }

            .route-project strong {
                margin-top: 7px;
                color: #fff;
                font-size: 0.88rem;
                line-height: 1.45;
            }

            .route-project small {
                margin-top: 5px;
                color: rgba(246, 242, 231, 0.52);
                font-size: 0.7rem;
            }

            .route-message {
                display: flex;
                min-height: 44px;
                margin: 0;
                padding: 10px 12px;
                align-items: center;
                gap: 9px;
                border: 1px solid rgba(255, 255, 255, 0.09);
                border-radius: 10px;
                color: rgba(246, 242, 231, 0.7);
                background: rgba(255, 255, 255, 0.035);
                font-size: 0.7rem;
                line-height: 1.45;
            }

            .route-message::before {
                color: var(--blue-bright);
                content: "●";
                font-size: 0.6rem;
            }

            .route-message.is-error {
                border-color: rgba(223, 106, 106, 0.3);
                color: #f0b5b5;
            }

            .route-message.is-error::before {
                color: #df6a6a;
            }

            .route-stats {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 8px;
            }

            .route-stat {
                padding: 12px;
                border: 1px solid rgba(255, 255, 255, 0.09);
                border-radius: 10px;
                background: rgba(255, 255, 255, 0.035);
            }

            .route-stat span,
            .route-stat strong {
                display: block;
            }

            .route-stat span {
                color: rgba(246, 242, 231, 0.45);
                font-size: 0.62rem;
                text-transform: uppercase;
            }

            .route-stat strong {
                margin-top: 5px;
                color: #fff;
                font-size: 0.9rem;
            }

            .request-location,
            .google-navigation {
                display: inline-flex;
                min-height: 42px;
                padding: 0 14px;
                align-items: center;
                justify-content: center;
                gap: 8px;
                border: 1px solid rgba(49, 169, 214, 0.5);
                border-radius: 9px;
                color: #fff;
                background: linear-gradient(
                    135deg,
                    rgba(19, 107, 145, 0.9),
                    rgba(18, 83, 108, 0.9)
                );
                cursor: pointer;
                font-size: 0.72rem;
                font-weight: 850;
                text-align: center;
                text-decoration: none;
            }

            .google-navigation {
                margin-top: auto;
                border-color: rgba(213, 178, 109, 0.5);
                background: linear-gradient(
                    135deg,
                    rgba(145, 109, 33, 0.8),
                    rgba(96, 72, 24, 0.88)
                );
            }

            .map-panel {
                position: relative;
                min-width: 0;
                min-height: 690px;
                overflow: hidden;
                background: #020303;
            }

            #project-location-map {
                position: absolute;
                inset: 0;
            }

            .map-toolbar {
                position: absolute;
                z-index: 8;
                top: 18px;
                right: 18px;
                left: 18px;
                display: flex;
                min-height: 52px;
                padding: 7px 8px 7px 14px;
                align-items: center;
                gap: 8px;
                border: 1px solid rgba(255, 255, 255, 0.11);
                border-radius: 12px;
                background: rgba(8, 13, 14, 0.88);
                box-shadow: 0 12px 28px rgba(0, 0, 0, 0.32);
                backdrop-filter: blur(12px);
            }

            .map-toolbar__copy {
                min-width: 0;
                margin-right: auto;
            }

            .map-toolbar__copy strong,
            .map-toolbar__copy span {
                display: block;
            }

            .map-toolbar__copy strong {
                color: #fff;
                font-size: 0.78rem;
            }

            .map-toolbar__copy span {
                margin-top: 3px;
                overflow: hidden;
                color: rgba(255, 255, 255, 0.5);
                font-size: 0.68rem;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .map-action {
                display: grid;
                width: 38px;
                height: 38px;
                padding: 0;
                flex: 0 0 auto;
                place-items: center;
                border: 1px solid rgba(49, 169, 214, 0.22);
                border-radius: 9px;
                color: #d8f4ff;
                background: rgba(19, 107, 145, 0.14);
                cursor: pointer;
                font-weight: 800;
            }

            .map-action:hover,
            .map-action:focus-visible {
                border-color: rgba(49, 169, 214, 0.7);
                background: rgba(19, 107, 145, 0.28);
                outline: none;
            }

            .globe-navigation {
                position: absolute;
                z-index: 8;
                top: 92px;
                right: 18px;
                display: grid;
                gap: 8px;
            }

            .globe-navigation .map-action {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                background: rgba(8, 13, 14, 0.9);
            }

            .map-status {
                position: absolute;
                z-index: 8;
                right: 18px;
                bottom: 16px;
                padding: 8px 11px;
                border: 1px solid rgba(255, 255, 255, 0.1);
                border-radius: 8px;
                color: rgba(255, 255, 255, 0.68);
                background: rgba(6, 10, 11, 0.86);
                font-family: Consolas, monospace;
                font-size: 0.63rem;
            }

            .map-legend {
                position: absolute;
                z-index: 8;
                bottom: 16px;
                left: 16px;
                display: flex;
                padding: 8px 11px;
                align-items: center;
                gap: 8px;
                border: 1px solid rgba(255, 255, 255, 0.1);
                border-radius: 8px;
                color: rgba(255, 255, 255, 0.68);
                background: rgba(6, 10, 11, 0.86);
                font-size: 0.64rem;
            }

            .map-legend::before {
                width: 10px;
                height: 10px;
                border: 2px solid #fff;
                border-radius: 50%;
                background: var(--blue-bright);
                content: "";
            }

            .map-credits {
                position: absolute;
                z-index: 8;
                bottom: 54px;
                left: 16px;
                max-width: 280px;
                color: rgba(255, 255, 255, 0.68);
                font-size: 0.58rem;
            }

            .map-credits summary {
                width: fit-content;
                padding: 5px 8px;
                border-radius: 7px;
                background: rgba(6, 10, 11, 0.86);
                cursor: pointer;
            }

            .map-credits__content {
                margin-top: 4px;
                padding: 6px;
                border-radius: 7px;
                background: rgba(6, 10, 11, 0.9);
            }

            .cesium-viewer-bottom,
            .cesium-viewer-toolbar {
                display: none !important;
            }

            html.light-mode {
                color-scheme: light;
                --page: #eef3f7;
                --surface: rgba(255, 255, 255, 0.97);
                --surface-soft: rgba(31, 57, 79, 0.05);
                --line: rgba(31, 57, 79, 0.16);
                --text: #172033;
                --muted: #66717e;
                --gold: #a36d13;
            }

            html.light-mode body,
            html.light-mode .location-module {
                color: var(--text);
                background:
                    radial-gradient(
                        circle at 72% 10%,
                        rgba(49, 169, 214, 0.13),
                        transparent 34rem
                    ),
                    var(--page);
            }

            html.light-mode .portal-header {
                border-bottom-color: rgba(19, 107, 145, 0.22);
                background: rgba(255, 255, 255, 0.97);
                box-shadow: 0 14px 38px rgba(31, 57, 79, 0.13);
            }

            html.light-mode .brand__art {
                background-image: url('{{ asset('images/municipal-coatepeque-brand-light.png') }}');
                background-position: 0 0;
                background-size: 310px 80px;
            }

            html.light-mode .brand__wordmark {
                position: absolute;
                z-index: 2;
                top: 8px;
                right: 0;
                bottom: 8px;
                left: 88px;
                display: flex;
                padding-left: 7px;
                flex-direction: column;
                justify-content: center;
                color: #101820;
                background: #fff;
                line-height: 1;
                white-space: nowrap;
            }

            html.light-mode .brand__wordmark strong {
                font-family: Georgia, "Times New Roman", serif;
                font-size: 1rem;
                font-weight: 800;
                letter-spacing: -0.035em;
                text-transform: uppercase;
            }

            html.light-mode .brand__wordmark small {
                margin-top: 3px;
                font-family: Georgia, "Times New Roman", serif;
                font-size: 0.56rem;
                font-weight: 700;
                letter-spacing: 0.015em;
                text-transform: uppercase;
            }

            html.light-mode .contact,
            html.light-mode .finder-head p:last-child,
            html.light-mode .result-count,
            html.light-mode .result-copy small,
            html.light-mode .route-project small {
                color: #66717e;
            }

            html.light-mode .contact strong,
            html.light-mode .finder-head h1,
            html.light-mode .result-copy strong,
            html.light-mode .route-project strong,
            html.light-mode .route-stat strong {
                color: #172033;
            }

            html.light-mode .nav-shell {
                border-top-color: rgba(31, 57, 79, 0.08);
                background: linear-gradient(
                    90deg,
                    rgba(49, 169, 214, 0.1),
                    rgba(255, 255, 255, 0.92) 42%,
                    rgba(213, 178, 109, 0.1)
                );
            }

            html.light-mode .nav-link {
                color: #52606d;
            }

            html.light-mode .nav-link:hover,
            html.light-mode .nav-link:focus-visible {
                color: #172033;
                background: rgba(19, 107, 145, 0.08);
            }

            html.light-mode .nav-link.is-active {
                color: #fff;
            }

            html.light-mode .location-workspace {
                border-color: rgba(31, 57, 79, 0.17);
                background: #fff;
                box-shadow: 0 24px 60px rgba(31, 57, 79, 0.16);
            }

            html.light-mode .project-finder {
                border-right-color: rgba(31, 57, 79, 0.13);
                background: linear-gradient(180deg, #fff, #f4f7f9);
            }

            html.light-mode .finder-search {
                background: transparent;
            }

            html.light-mode .finder-search input {
                border-color: #d4e0e8;
                color: #172033;
                background: #f8fafb;
            }

            html.light-mode .finder-search input::placeholder {
                color: #88939d;
            }

            html.light-mode .finder-search input:focus {
                border-color: rgba(19, 107, 145, 0.62);
                background: #fff;
            }

            html.light-mode .search-clear {
                color: #66717e;
            }

            html.light-mode .search-clear:hover {
                color: #172033;
                background: rgba(19, 107, 145, 0.08);
            }

            html.light-mode .project-result,
            html.light-mode .route-project,
            html.light-mode .route-message,
            html.light-mode .route-stat {
                border-color: rgba(31, 57, 79, 0.13);
                color: #172033;
                background: rgba(31, 57, 79, 0.035);
            }

            html.light-mode .project-result:hover,
            html.light-mode .project-result:focus-visible {
                border-color: rgba(19, 107, 145, 0.28);
                background: rgba(49, 169, 214, 0.09);
            }

            html.light-mode .back-to-search,
            html.light-mode .request-location {
                color: #0e668a;
                background: rgba(49, 169, 214, 0.09);
            }

            body.is-embedded {
                min-height: 100vh;
                overflow: hidden;
            }

            .is-embedded .location-module {
                min-height: 100vh;
                padding: 0;
            }

            .is-embedded .location-workspace {
                grid-template-columns: minmax(300px, 350px) minmax(0, 1fr);
                width: 100%;
                min-height: 100vh;
                border: 0;
                border-radius: 0;
            }

            .is-embedded .project-finder,
            .is-embedded .map-panel {
                min-height: 100vh;
            }

            @media (max-width: 1100px) {
                .header-top {
                    grid-template-columns: minmax(250px, 1fr) minmax(250px, 1fr);
                }

                .header-actions {
                    grid-column: 2;
                }

                .location-workspace {
                    grid-template-columns: minmax(270px, 330px) minmax(0, 1fr);
                }
            }

            @media (max-width: 820px) {
                .header-top {
                    display: flex;
                    width: calc(100% - 28px);
                    min-height: 78px;
                    justify-content: space-between;
                    gap: 12px;
                }

                .brand {
                    width: 230px;
                    transform: scale(0.82);
                    transform-origin: left center;
                }

                .contact {
                    display: none;
                }

                .nav-list {
                    width: calc(100% - 28px);
                }

                .location-module {
                    padding: 14px;
                }

                .location-workspace {
                    grid-template-columns: 1fr;
                }

                .project-finder {
                    min-height: auto;
                    max-height: 390px;
                    border-right: 0;
                    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                }

                .map-panel {
                    min-height: 560px;
                }
            }

            @media (max-width: 540px) {
                .brand {
                    width: 160px;
                }

                .brand__wordmark {
                    left: 58px;
                    padding-left: 4px;
                }

                .brand__wordmark strong {
                    font-size: 0.55rem;
                }

                .brand__wordmark small {
                    font-size: 0.3rem;
                }

                .logout-button {
                    padding: 0 11px;
                    font-size: 0.65rem;
                }

                .location-module {
                    padding: 8px;
                }

                .location-workspace {
                    border-radius: 12px;
                }

                .map-panel {
                    min-height: 500px;
                }

                .map-toolbar {
                    top: 10px;
                    right: 10px;
                    left: 10px;
                }

                .globe-navigation {
                    top: 80px;
                    right: 10px;
                }

                .map-status {
                    display: none;
                }
            }
        </style>
    </head>
    <body
        @class(['is-embedded' => request()->boolean('embed')])
        data-public-read-only="{{ $readOnly ? 'true' : 'false' }}"
        data-session-activity-url="{{ route('session.activity') }}"
        data-login-url="{{ route('login') }}"
        data-logout-url="{{ route('logout') }}"
        data-idle-timeout-ms="{{ config('session.lifetime') * 60 * 1000 }}"
    >
        @unless (request()->boolean('embed'))
        <header class="portal-header">
            <div class="header-top">
                <div class="brand">
                    <span class="brand__art" aria-hidden="true"></span>
                    <span class="brand__wordmark" aria-hidden="true">
                        <strong>Gobierno Municipal</strong>
                        <small>Coatepeque, Quetzaltenango</small>
                    </span>
                </div>

                <div class="header-actions">
                    <p class="contact">
                        Comunícate con nosotros:
                        <strong>7957-2525</strong>
                    </p>

                    <form
                        class="logout-form"
                        method="post"
                        action="{{ route('logout') }}"
                    >
                        @csrf
                        <button class="logout-button" type="submit">
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>

            <div class="nav-shell">
                <nav aria-label="Navegación principal">
                    <ul class="nav-list">
                        <li>
                            <a
                                class="nav-link"
                                href="{{ route('home') }}"
                                data-protected-navigation
                            >Inicio</a>
                        </li>
                        <li>
                            <a
                                class="nav-link is-active"
                                href="{{ route('project-locations') }}"
                                aria-current="page"
                            >Ubicación de Proyectos</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </header>
        @endunless

        <main class="location-module">
            <section
                class="location-workspace"
                aria-label="Ubicación y navegación de proyectos"
            >
                <aside class="project-finder">
                    <div class="finder-head">
                        <p class="finder-eyebrow">Mapa municipal</p>
                        <h1>Buscar un proyecto</h1>
                        <p>
                            Busca por código SNIP o nombre y selecciónalo para
                            calcular la ruta desde tu ubicación.
                        </p>
                    </div>

                    <div id="finder-mode">
                        <div class="finder-search-wrap">
                            <div class="finder-search">
                                <input
                                    id="project-location-search"
                                    type="search"
                                    autocomplete="off"
                                    placeholder="SNIP o nombre del proyecto"
                                    aria-label="Buscar por SNIP o nombre"
                                >
                                <button
                                    class="search-clear"
                                    id="clear-project-search"
                                    type="button"
                                    aria-label="Limpiar búsqueda"
                                    hidden
                                >×</button>
                            </div>
                            <p class="result-count" id="project-result-count"></p>
                        </div>

                        <ul class="project-results" id="project-location-results">
                            @foreach ($projects as $project)
                                <li>
                                    <button
                                        class="project-result"
                                        type="button"
                                        data-location-project="{{ $project['id'] }}"
                                        data-project-search="{{ mb_strtolower(($project['snip'] ? $project['snip'].' ' : '').$project['name']) }}"
                                        style="--marker-color: {{ $project['color'] }}"
                                    >
                                        <span class="result-marker" aria-hidden="true">
                                            <span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                        </span>
                                        <span class="result-copy">
                                            <strong>@if ($project['snip']){{ $project['snip'] }}-@endif{{ $project['name'] }}</strong>
                                            <small>{{ $project['place'] }}</small>
                                        </span>
                                        <span class="result-arrow" aria-hidden="true">›</span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>

                        <p class="empty-results" id="empty-project-results" hidden>
                            No se encontraron proyectos con ese SNIP o nombre.
                        </p>
                    </div>

                    <section
                        class="route-detail"
                        id="route-detail"
                        aria-live="polite"
                        hidden
                    >
                        <button
                            class="back-to-search"
                            id="back-to-project-search"
                            type="button"
                        >← Regresar para buscar otro</button>

                        <div class="route-project">
                            <span>Proyecto seleccionado</span>
                            <strong id="selected-project-name"></strong>
                            <small id="selected-project-place"></small>
                        </div>

                        <p class="route-message" id="route-message">
                            Solicitando acceso a tu ubicación…
                        </p>

                        <div class="route-stats" id="route-stats" hidden>
                            <div class="route-stat">
                                <span>Distancia</span>
                                <strong id="route-distance">—</strong>
                            </div>
                            <div class="route-stat">
                                <span>Tiempo estimado</span>
                                <strong id="route-duration">—</strong>
                            </div>
                        </div>

                        <button
                            class="request-location"
                            id="request-location-again"
                            type="button"
                            hidden
                        >⌖ Permitir mi ubicación</button>

                        <a
                            class="google-navigation"
                            id="google-navigation"
                            href="#"
                            target="_blank"
                            rel="noopener"
                        >↗ Abrir navegación en Google Maps</a>
                    </section>
                </aside>

                <div class="map-panel">
                    <div class="map-toolbar">
                        <div class="map-toolbar__copy">
                            <strong>Mapa de ubicación de proyectos</strong>
                            <span id="map-selection">
                                Selecciona un proyecto para calcular cómo llegar
                            </span>
                        </div>
                        <button
                            class="map-action"
                            id="map-home"
                            type="button"
                            title="Vista general"
                            aria-label="Volver a la vista general"
                        >◎</button>
                        <button
                            class="map-action"
                            id="map-fullscreen"
                            type="button"
                            title="Pantalla completa"
                            aria-label="Mostrar mapa en pantalla completa"
                        >⛶</button>
                    </div>

                    <div class="globe-navigation" aria-label="Controles del mapa">
                        <button
                            class="map-action"
                            id="globe-north"
                            type="button"
                            aria-label="Orientar al norte"
                        >N</button>
                        <button
                            class="map-action"
                            id="globe-zoom-in"
                            type="button"
                            aria-label="Acercar"
                        >+</button>
                        <button
                            class="map-action"
                            id="globe-zoom-out"
                            type="button"
                            aria-label="Alejar"
                        >−</button>
                    </div>

                    <div
                        id="project-location-map"
                        aria-label="Mapa interactivo de ubicación de proyectos"
                    ></div>

                    <details class="map-credits">
                        <summary>Fuentes</summary>
                        <div
                            class="map-credits__content"
                            id="map-credits-content"
                        ></div>
                    </details>

                    <div class="map-legend">Tu ubicación y ruta seleccionada</div>
                    <div class="map-status" id="map-status">
                        Coatepeque · Vista satelital
                    </div>
                </div>
            </section>
        </main>

        <script src="https://cdn.jsdelivr.net/npm/cesium@1.143.0/Build/Cesium/Cesium.js"></script>
        <script src="https://unpkg.com/@esri/arcgis-rest-request@4/dist/bundled/request.umd.js"></script>
        <script src="https://unpkg.com/@esri/arcgis-rest-basemap-sessions@1/dist/bundled/basemap-sessions.umd.js"></script>

        <script>
            (async () => {
                const isPublicReadOnly = document.body.dataset.publicReadOnly === 'true';
                const activityUrl = document.body.dataset.sessionActivityUrl;
                const loginUrl = document.body.dataset.loginUrl;
                const logoutUrl = document.body.dataset.logoutUrl;
                const idleTimeout = Number(
                    document.body.dataset.idleTimeoutMs,
                );
                const csrfToken = document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute('content');
                const historySessionKey = 'protected-session-closed';
                const tabAuthorizationKey = 'protected-tab-authorized';
                const reloadAuthorizationKey = 'protected-reload-pending';
                const internalNavigationKey = 'protected-internal-navigation';
                const logoutForm = document.querySelector('.logout-form');
                let explicitLogoutStarted = false;
                let logoutInProgress = false;

                const logoutRequestOptions = {
                    method: 'POST',
                    credentials: 'same-origin',
                    redirect: 'follow',
                    keepalive: true,
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                };

                const markSessionClosed = () => {
                    sessionStorage.setItem(historySessionKey, 'true');
                    sessionStorage.removeItem(tabAuthorizationKey);
                };

                const returnToLogin = async () => {
                    if (logoutInProgress) {
                        return;
                    }

                    logoutInProgress = true;
                    explicitLogoutStarted = true;
                    markSessionClosed();

                    try {
                        await fetch(logoutUrl, logoutRequestOptions);
                    } catch {
                        // La redirección continúa aunque la red falle.
                    } finally {
                        window.location.replace(loginUrl);
                    }
                };

                if (!isPublicReadOnly && !window.__protectedTabAuthorized) {
                    await returnToLogin();

                    return;
                }

                document.documentElement.style.visibility = '';

                if (!isPublicReadOnly) {
                document.querySelectorAll('[data-protected-navigation]')
                    .forEach((link) => {
                        link.addEventListener('click', () => {
                            sessionStorage.setItem(
                                internalNavigationKey,
                                'true',
                            );
                        });
                    });

                logoutForm?.addEventListener('submit', () => {
                    explicitLogoutStarted = true;
                    markSessionClosed();
                    document.documentElement.style.visibility = 'hidden';
                });

                window.addEventListener('pagehide', () => {
                    if (!explicitLogoutStarted) {
                        sessionStorage.setItem(
                            reloadAuthorizationKey,
                            'true',
                        );
                    }

                    markSessionClosed();
                    document.documentElement.style.visibility = 'hidden';
                });

                window.addEventListener('pageshow', (event) => {
                    const navigation = performance.getEntriesByType(
                        'navigation',
                    )[0];
                    const restored = event.persisted
                        || navigation?.type === 'back_forward';

                    if (
                        restored
                        && sessionStorage.getItem(historySessionKey) === 'true'
                    ) {
                        returnToLogin();
                    }
                });

                let lastActivity = Date.now();
                let expirationTimer;

                const scheduleExpiration = () => {
                    window.clearTimeout(expirationTimer);
                    const remaining =
                        idleTimeout - (Date.now() - lastActivity);

                    if (remaining <= 0) {
                        returnToLogin();

                        return;
                    }

                    expirationTimer = window.setTimeout(
                        returnToLogin,
                        remaining,
                    );
                };

                [
                    'pointerdown',
                    'keydown',
                    'wheel',
                    'touchstart',
                ].forEach((eventName) => {
                    window.addEventListener(eventName, () => {
                        lastActivity = Date.now();
                        scheduleExpiration();

                        if (window.parent !== window) {
                            window.parent.postMessage(
                                { type: 'module-activity' },
                                window.location.origin,
                            );
                        }
                    }, { passive: true });
                });
                scheduleExpiration();

                window.setInterval(async () => {
                    if (Date.now() - lastActivity > 60_000) {
                        return;
                    }

                    try {
                        const response = await fetch(activityUrl, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                Accept: 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                        });

                        if (
                            response.redirected
                            || response.status === 401
                            || response.status === 419
                        ) {
                            returnToLogin();
                        }
                    } catch {
                        // El temporizador local continúa protegiendo la sesión.
                    }
                }, 30_000);
                }

                const projects = {{ Illuminate\Support\Js::from($projects) }};
                const projectById = Object.fromEntries(
                    projects.map((project) => [
                        String(project.id),
                        project,
                    ]),
                );
                const searchInput = document.getElementById(
                    'project-location-search',
                );
                const clearSearch = document.getElementById(
                    'clear-project-search',
                );
                const resultCount = document.getElementById(
                    'project-result-count',
                );
                const resultList = document.getElementById(
                    'project-location-results',
                );
                const resultButtons = Array.from(
                    document.querySelectorAll('[data-location-project]'),
                );
                const emptyResults = document.getElementById(
                    'empty-project-results',
                );
                const finderMode = document.getElementById('finder-mode');
                const routeDetail = document.getElementById('route-detail');
                const selectedProjectName = document.getElementById(
                    'selected-project-name',
                );
                const selectedProjectPlace = document.getElementById(
                    'selected-project-place',
                );
                const routeMessage = document.getElementById('route-message');
                const routeStats = document.getElementById('route-stats');
                const routeDistance = document.getElementById(
                    'route-distance',
                );
                const routeDuration = document.getElementById(
                    'route-duration',
                );
                const requestLocationAgain = document.getElementById(
                    'request-location-again',
                );
                const googleNavigation = document.getElementById(
                    'google-navigation',
                );
                const mapSelection = document.getElementById('map-selection');
                const mapStatus = document.getElementById('map-status');
                const arcGisApiKey = @js(config('services.arcgis.api_key'));
                const arcGisServicesUrl =
                    'https://server.arcgisonline.com/ArcGIS/rest/services/';
                const publicSatelliteProvider = () =>
                    Cesium.ArcGisMapServerImageryProvider.fromUrl(
                        arcGisServicesUrl + 'World_Imagery/MapServer',
                    );
                let basemapSession = null;
                let satelliteProvider;

                if (
                    arcGisApiKey
                    && window.arcgisRest?.BasemapStyleSession
                ) {
                    try {
                        basemapSession =
                            await window.arcgisRest.BasemapStyleSession.start({
                                authentication: arcGisApiKey,
                                styleFamily: 'arcgis',
                                duration: 43_200,
                                autoRefresh: false,
                            });
                        const sessionToken = await basemapSession.getToken();
                        Cesium.ArcGisMapService.defaultAccessToken =
                            sessionToken;
                        satelliteProvider =
                            Cesium.ArcGisMapServerImageryProvider.fromBasemapType(
                                Cesium.ArcGisBaseMapType.SATELLITE,
                            );
                    } catch {
                        basemapSession = null;
                        satelliteProvider = publicSatelliteProvider();
                    }
                } else {
                    satelliteProvider = publicSatelliteProvider();
                }

                const viewer = new Cesium.Viewer(
                    'project-location-map',
                    {
                        animation: false,
                        baseLayerPicker: false,
                        creditContainer: document.getElementById(
                            'map-credits-content',
                        ),
                        fullscreenButton: false,
                        geocoder: false,
                        homeButton: false,
                        infoBox: false,
                        navigationHelpButton: false,
                        sceneModePicker: false,
                        selectionIndicator: false,
                        timeline: false,
                        baseLayer: Cesium.ImageryLayer.fromProviderAsync(
                            satelliteProvider,
                        ),
                        terrainProvider:
                            new Cesium.EllipsoidTerrainProvider(),
                    },
                );
                const roadsLayer = Cesium.ImageryLayer.fromProviderAsync(
                    Cesium.ArcGisMapServerImageryProvider.fromUrl(
                        arcGisServicesUrl
                        + 'Reference/World_Transportation/MapServer',
                    ),
                );
                const boundariesLayer = Cesium.ImageryLayer.fromProviderAsync(
                    Cesium.ArcGisMapServerImageryProvider.fromUrl(
                        arcGisServicesUrl
                        + 'Reference/World_Boundaries_and_Places/MapServer',
                    ),
                );
                viewer.imageryLayers.add(roadsLayer);
                viewer.imageryLayers.add(boundariesLayer);
                roadsLayer.alpha = 0.92;
                boundariesLayer.alpha = 1;

                const scene = viewer.scene;
                const camera = viewer.camera;
                const cameraController = scene.screenSpaceCameraController;
                const projectEntities = {};
                let selectedProjectId = null;
                let userEntity = null;
                let routeEntity = null;
                let routeRequest = null;
                let currentOrigin = null;
                const maximumGlobeAltitude = 20_000_000;
                const globeCenteringStartAltitude = 250_000;
                const globeCenteringAltitude = 4_000_000;
                const minimumGlobeAlignment = Math.cos(
                    Cesium.Math.toRadians(0.1),
                );
                const globeDirection = new Cesium.Cartesian3();
                const globeRight = new Cesium.Cartesian3();
                const globeUp = new Cesium.Cartesian3();
                const correctedDirection = new Cesium.Cartesian3();
                const correctedRight = new Cesium.Cartesian3();
                const correctedUp = new Cesium.Cartesian3();
                let correctingGlobeView = false;
                let cameraFlightActive = false;
                let lastGlobeCorrectionTime = performance.now();

                const keepGlobeVisible = () => {
                    const now = performance.now();
                    const elapsedSeconds = Math.min(
                        (now - lastGlobeCorrectionTime) / 1000,
                        0.1,
                    );
                    lastGlobeCorrectionTime = now;
                    const altitude = camera.positionCartographic.height;
                    const rawCenteringProgress = Cesium.Math.clamp(
                        (altitude - globeCenteringStartAltitude)
                        / (
                            globeCenteringAltitude
                            - globeCenteringStartAltitude
                        ),
                        0,
                        1,
                    );
                    const centeringProgress =
                        rawCenteringProgress
                        * rawCenteringProgress
                        * (3 - (2 * rawCenteringProgress));
                    cameraController.enableTilt = centeringProgress < 0.98;

                    if (
                        correctingGlobeView
                        || cameraFlightActive
                        || centeringProgress === 0
                    ) {
                        return;
                    }

                    Cesium.Cartesian3.normalize(
                        Cesium.Cartesian3.negate(
                            camera.positionWC,
                            globeDirection,
                        ),
                        globeDirection,
                    );
                    const alignment = Cesium.Cartesian3.dot(
                        camera.directionWC,
                        globeDirection,
                    );

                    if (
                        centeringProgress >= 1
                        && alignment >= minimumGlobeAlignment
                    ) {
                        return;
                    }

                    Cesium.Cartesian3.cross(
                        globeDirection,
                        Cesium.Cartesian3.UNIT_Z,
                        globeRight,
                    );

                    if (
                        Cesium.Cartesian3.magnitudeSquared(globeRight)
                        < Cesium.Math.EPSILON10
                    ) {
                        Cesium.Cartesian3.clone(camera.rightWC, globeRight);
                    } else {
                        Cesium.Cartesian3.normalize(globeRight, globeRight);
                    }

                    Cesium.Cartesian3.normalize(
                        Cesium.Cartesian3.cross(
                            globeRight,
                            globeDirection,
                            globeUp,
                        ),
                        globeUp,
                    );

                    const correctionRate = 0.6 + (2.4 * centeringProgress);
                    const correctionAmount = Math.min(
                        0.12,
                        1 - Math.exp(-correctionRate * elapsedSeconds),
                    );
                    Cesium.Cartesian3.lerp(
                        camera.directionWC,
                        globeDirection,
                        correctionAmount,
                        correctedDirection,
                    );
                    Cesium.Cartesian3.normalize(
                        correctedDirection,
                        correctedDirection,
                    );
                    Cesium.Cartesian3.lerp(
                        camera.upWC,
                        globeUp,
                        correctionAmount,
                        correctedUp,
                    );
                    Cesium.Cartesian3.normalize(correctedUp, correctedUp);
                    Cesium.Cartesian3.normalize(
                        Cesium.Cartesian3.cross(
                            correctedDirection,
                            correctedUp,
                            correctedRight,
                        ),
                        correctedRight,
                    );
                    Cesium.Cartesian3.normalize(
                        Cesium.Cartesian3.cross(
                            correctedRight,
                            correctedDirection,
                            correctedUp,
                        ),
                        correctedUp,
                    );

                    correctingGlobeView = true;
                    camera.setView({
                        destination: camera.positionWC,
                        orientation: {
                            direction: correctedDirection,
                            up: correctedUp,
                        },
                    });
                    window.requestAnimationFrame(() => {
                        correctingGlobeView = false;
                    });
                };

                const finishCameraFlight = () => {
                    cameraFlightActive = false;
                    keepGlobeVisible();
                };

                const flyCamera = (options) => {
                    cameraFlightActive = true;
                    camera.flyTo({
                        ...options,
                        complete: () => {
                            options.complete?.();
                            finishCameraFlight();
                        },
                        cancel: () => {
                            options.cancel?.();
                            finishCameraFlight();
                        },
                    });
                };

                scene.backgroundColor = Cesium.Color.BLACK;
                scene.globe.baseColor =
                    Cesium.Color.fromCssColorString('#08131c');
                scene.globe.showGroundAtmosphere = true;
                scene.globe.maximumScreenSpaceError = 1.25;
                scene.globe.preloadAncestors = true;
                scene.globe.preloadSiblings = true;
                scene.globe.tileCacheSize = 500;
                scene.skyAtmosphere.show = true;
                scene.fog.enabled = false;
                scene.postProcessStages.fxaa.enabled = true;
                cameraController.minimumZoomDistance = 80;
                cameraController.maximumZoomDistance = maximumGlobeAltitude;
                cameraController.maximumTiltAngle =
                    Cesium.Math.toRadians(80);
                cameraController.enableLook = false;
                cameraController.enableRotate = true;
                cameraController.enableTilt = true;
                cameraController.inertiaSpin = 0.72;
                cameraController.inertiaZoom = 0.72;
                viewer.resolutionScale = Math.min(
                    Math.max(window.devicePixelRatio || 1, 1.25),
                    1.75,
                );
                viewer.clock.shouldAnimate = false;

                if (basemapSession) {
                    basemapSession.on('refreshed', (event) => {
                        Cesium.ArcGisMapService.defaultAccessToken =
                            event.current.token;
                    });
                    viewer.creditDisplay.addStaticCredit(
                        new Cesium.Credit(
                            'Powered by <a href="https://www.esri.com/" '
                            + 'target="_blank" rel="noopener">Esri</a>',
                            true,
                        ),
                    );
                }

                const generalView = {
                    destination: Cesium.Cartesian3.fromDegrees(
                        -91.2,
                        11.5,
                        17_500_000,
                    ),
                    orientation: {
                        heading: 0,
                        pitch: Cesium.Math.toRadians(-90),
                        roll: 0,
                    },
                };
                camera.setView(generalView);

                projects.forEach((project, index) => {
                    const id = String(project.id);
                    const label = project.snip
                        ? project.snip + '-' + project.name
                        : project.name;

                    projectEntities[id] = viewer.entities.add({
                        id: 'location-project-' + id,
                        name: label,
                        position: Cesium.Cartesian3.fromDegrees(
                            project.longitude,
                            project.latitude,
                            0,
                        ),
                        point: {
                            pixelSize: 15,
                            heightReference:
                                Cesium.HeightReference.CLAMP_TO_GROUND,
                            color:
                                Cesium.Color.fromCssColorString(project.color),
                            outlineColor: Cesium.Color.WHITE,
                            outlineWidth: 3,
                            scaleByDistance: new Cesium.NearFarScalar(
                                1_000,
                                1.4,
                                2_500_000,
                                0.35,
                            ),
                            disableDepthTestDistance:
                                Number.POSITIVE_INFINITY,
                        },
                        label: {
                            text: label,
                            heightReference:
                                Cesium.HeightReference.CLAMP_TO_GROUND,
                            font: '600 14px Segoe UI',
                            fillColor: Cesium.Color.WHITE,
                            outlineColor: Cesium.Color.BLACK,
                            outlineWidth: 4,
                            style: Cesium.LabelStyle.FILL_AND_OUTLINE,
                            pixelOffset: new Cesium.Cartesian2(0, -27),
                            distanceDisplayCondition:
                                new Cesium.DistanceDisplayCondition(
                                    0,
                                    120_000,
                                ),
                            disableDepthTestDistance:
                                Number.POSITIVE_INFINITY,
                        },
                        properties: {
                            projectId: id,
                            order: index + 1,
                        },
                    });
                });

                const normalizeSearch = (value) => value
                    .toLocaleLowerCase('es')
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '');

                const visibleProjectIds = () => resultButtons
                    .filter((button) => !button.closest('li').hidden)
                    .map((button) => button.dataset.locationProject);

                const updateEntityVisibility = () => {
                    const visibleIds = new Set(visibleProjectIds());

                    Object.entries(projectEntities).forEach(([id, entity]) => {
                        entity.show = selectedProjectId !== null
                            ? id === selectedProjectId
                            : visibleIds.has(id);
                    });
                    scene.requestRender();
                };

                const filterProjects = () => {
                    const query = normalizeSearch(searchInput.value.trim());
                    let matches = 0;

                    resultButtons.forEach((button) => {
                        const searchValue = normalizeSearch(
                            button.dataset.projectSearch,
                        );
                        const match =
                            query === '' || searchValue.includes(query);

                        button.closest('li').hidden = !match;

                        if (match) {
                            matches += 1;
                        }
                    });

                    resultCount.textContent = matches === 1
                        ? '1 proyecto encontrado'
                        : matches + ' proyectos encontrados';
                    emptyResults.hidden = matches !== 0;
                    resultList.hidden = matches === 0;
                    clearSearch.hidden = query === '';
                    updateEntityVisibility();
                };

                const formatDistance = (meters) => meters >= 1000
                    ? (meters / 1000).toFixed(1) + ' km'
                    : Math.round(meters) + ' m';
                const formatDuration = (seconds) => {
                    const minutes = Math.max(1, Math.round(seconds / 60));

                    if (minutes < 60) {
                        return minutes + ' min';
                    }

                    const hours = Math.floor(minutes / 60);
                    const remainingMinutes = minutes % 60;

                    return hours + ' h '
                        + (remainingMinutes > 0
                            ? remainingMinutes + ' min'
                            : '');
                };

                const googleDirectionsUrl = (project, origin = null) => {
                    const url = new URL(
                        'https://www.google.com/maps/dir/',
                    );
                    url.searchParams.set('api', '1');

                    if (origin) {
                        url.searchParams.set(
                            'origin',
                            origin.latitude + ',' + origin.longitude,
                        );
                    }

                    url.searchParams.set(
                        'destination',
                        project.latitude + ',' + project.longitude,
                    );
                    url.searchParams.set('travelmode', 'driving');

                    return url.toString();
                };

                const clearRouteGraphics = () => {
                    if (routeRequest) {
                        routeRequest.abort();
                        routeRequest = null;
                    }

                    if (routeEntity) {
                        viewer.entities.remove(routeEntity);
                        routeEntity = null;
                    }

                    if (userEntity) {
                        viewer.entities.remove(userEntity);
                        userEntity = null;
                    }

                    currentOrigin = null;
                };

                const fitPositions = (positions) => {
                    if (positions.length < 2) {
                        return;
                    }

                    flyCamera({
                        destination:
                            Cesium.Rectangle.fromCartesianArray(positions),
                        orientation: {
                            heading: 0,
                            pitch: Cesium.Math.toRadians(-90),
                            roll: 0,
                        },
                        duration: 2,
                    });
                };

                const centerSelectedProject = (project) => {
                    flyCamera({
                        destination: Cesium.Cartesian3.fromDegrees(
                            project.longitude,
                            project.latitude,
                            5_000,
                        ),
                        orientation: {
                            heading: 0,
                            pitch: Cesium.Math.toRadians(-90),
                            roll: 0,
                        },
                        duration: 1.6,
                    });
                };

                const showDirectRoute = (origin, project) => {
                    const positions = [
                        Cesium.Cartesian3.fromDegrees(
                            origin.longitude,
                            origin.latitude,
                        ),
                        Cesium.Cartesian3.fromDegrees(
                            project.longitude,
                            project.latitude,
                        ),
                    ];

                    routeEntity = viewer.entities.add({
                        id: 'selected-direct-route',
                        polyline: {
                            positions,
                            width: 4,
                            clampToGround: true,
                            material:
                                Cesium.Color.fromCssColorString('#31a9d6')
                                    .withAlpha(0.82),
                        },
                    });
                    fitPositions(positions);
                };

                const calculateRoadRoute = async (origin, project) => {
                    routeRequest = new AbortController();
                    const coordinates =
                        origin.longitude + ',' + origin.latitude + ';'
                        + project.longitude + ',' + project.latitude;
                    const routeUrl =
                        'https://router.project-osrm.org/route/v1/driving/'
                        + coordinates
                        + '?overview=full&geometries=geojson&steps=true';

                    try {
                        const response = await fetch(routeUrl, {
                            signal: routeRequest.signal,
                        });
                        const payload = await response.json();

                        if (
                            !response.ok
                            || payload.code !== 'Ok'
                            || !payload.routes?.[0]
                        ) {
                            throw new Error('Ruta no disponible');
                        }

                        const route = payload.routes[0];
                        const flatCoordinates =
                            route.geometry.coordinates.flatMap(
                                (coordinate) => [
                                    coordinate[0],
                                    coordinate[1],
                                ],
                            );
                        const positions =
                            Cesium.Cartesian3.fromDegreesArray(
                                flatCoordinates,
                            );

                        routeEntity = viewer.entities.add({
                            id: 'selected-road-route',
                            polyline: {
                                positions,
                                width: 6,
                                clampToGround: true,
                                material:
                                    new Cesium.PolylineGlowMaterialProperty({
                                        color:
                                            Cesium.Color.fromCssColorString(
                                                '#31a9d6',
                                            ),
                                        glowPower: 0.2,
                                    }),
                            },
                        });
                        routeDistance.textContent =
                            formatDistance(route.distance);
                        routeDuration.textContent =
                            formatDuration(route.duration);
                        routeStats.hidden = false;
                        routeMessage.classList.remove('is-error');
                        routeMessage.textContent =
                            'Ruta por carretera calculada desde tu ubicación.';
                        fitPositions(positions);
                    } catch (error) {
                        if (error.name === 'AbortError') {
                            return;
                        }

                        routeMessage.classList.add('is-error');
                        routeMessage.textContent =
                            'No se pudo cargar la ruta por carretera. '
                            + 'Puedes abrir la navegación en Google Maps.';
                        showDirectRoute(origin, project);
                    } finally {
                        routeRequest = null;
                    }
                };

                const requestCurrentLocation = (project) => {
                    routeMessage.classList.remove('is-error');
                    routeMessage.textContent =
                        'Solicitando permiso para usar tu ubicación…';
                    requestLocationAgain.hidden = true;
                    routeStats.hidden = true;
                    googleNavigation.href =
                        googleDirectionsUrl(project);

                    if (!navigator.geolocation) {
                        routeMessage.classList.add('is-error');
                        routeMessage.textContent =
                            'Este navegador no permite obtener tu ubicación.';

                        return;
                    }

                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            currentOrigin = {
                                latitude: position.coords.latitude,
                                longitude: position.coords.longitude,
                            };
                            userEntity = viewer.entities.add({
                                id: 'current-user-location',
                                name: 'Tu ubicación',
                                position: Cesium.Cartesian3.fromDegrees(
                                    currentOrigin.longitude,
                                    currentOrigin.latitude,
                                ),
                                point: {
                                    pixelSize: 15,
                                    color:
                                        Cesium.Color.fromCssColorString(
                                            '#31a9d6',
                                        ),
                                    outlineColor: Cesium.Color.WHITE,
                                    outlineWidth: 4,
                                    disableDepthTestDistance:
                                        Number.POSITIVE_INFINITY,
                                },
                                label: {
                                    text: 'Tu ubicación',
                                    font: '700 14px Segoe UI',
                                    fillColor: Cesium.Color.WHITE,
                                    outlineColor: Cesium.Color.BLACK,
                                    outlineWidth: 4,
                                    style:
                                        Cesium.LabelStyle.FILL_AND_OUTLINE,
                                    pixelOffset:
                                        new Cesium.Cartesian2(0, -27),
                                    disableDepthTestDistance:
                                        Number.POSITIVE_INFINITY,
                                },
                            });
                            googleNavigation.href =
                                googleDirectionsUrl(
                                    project,
                                    currentOrigin,
                                );
                            routeMessage.textContent =
                                'Ubicación obtenida. Calculando la mejor ruta…';
                            calculateRoadRoute(currentOrigin, project);
                        },
                        (error) => {
                            routeMessage.classList.add('is-error');
                            routeMessage.textContent = error.code === 1
                                ? 'Debes permitir tu ubicación para calcular '
                                    + 'la ruta desde donde estás.'
                                : 'No fue posible obtener tu ubicación. '
                                    + 'Inténtalo nuevamente.';
                            requestLocationAgain.hidden = false;
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 15_000,
                            maximumAge: 30_000,
                        },
                    );
                };

                const selectProject = (projectId) => {
                    const normalizedId = String(projectId);
                    const project = projectById[normalizedId];

                    if (!project) {
                        return;
                    }

                    clearRouteGraphics();
                    selectedProjectId = normalizedId;
                    finderMode.hidden = true;
                    routeDetail.hidden = false;
                    selectedProjectName.textContent = project.snip
                        ? project.snip + '-' + project.name
                        : project.name;
                    selectedProjectPlace.textContent = project.place;
                    mapSelection.textContent =
                        'Calculando ruta hacia ' + project.name;
                    googleNavigation.href =
                        googleDirectionsUrl(project);
                    updateEntityVisibility();
                    centerSelectedProject(project);
                    requestCurrentLocation(project);
                };

                const returnToSearch = () => {
                    clearRouteGraphics();
                    selectedProjectId = null;
                    routeDetail.hidden = true;
                    finderMode.hidden = false;
                    routeStats.hidden = true;
                    requestLocationAgain.hidden = true;
                    routeMessage.classList.remove('is-error');
                    mapSelection.textContent =
                        'Selecciona un proyecto para calcular cómo llegar';
                    updateEntityVisibility();
                    flyCamera({
                        ...generalView,
                        duration: 1.8,
                    });
                    searchInput.focus();
                };

                resultButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        selectProject(button.dataset.locationProject);
                    });
                });

                searchInput.addEventListener('input', filterProjects);
                clearSearch.addEventListener('click', () => {
                    searchInput.value = '';
                    filterProjects();
                    searchInput.focus();
                });
                document.getElementById('back-to-project-search')
                    .addEventListener('click', returnToSearch);
                requestLocationAgain.addEventListener('click', () => {
                    const project = projectById[selectedProjectId];

                    if (project) {
                        clearRouteGraphics();
                        centerSelectedProject(project);
                        requestCurrentLocation(project);
                    }
                });

                const mapClickHandler =
                    new Cesium.ScreenSpaceEventHandler(scene.canvas);
                mapClickHandler.setInputAction((movement) => {
                    const picked = scene.pick(movement.position);
                    const projectId =
                        picked?.id?.properties?.projectId?.getValue();

                    if (projectId) {
                        selectProject(projectId);
                    }
                }, Cesium.ScreenSpaceEventType.LEFT_CLICK);

                document.getElementById('map-home')
                    .addEventListener('click', returnToSearch);
                document.getElementById('map-fullscreen')
                    .addEventListener('click', async () => {
                        const panel = document.querySelector('.map-panel');

                        if (document.fullscreenElement) {
                            await document.exitFullscreen();
                        } else if (panel.requestFullscreen) {
                            await panel.requestFullscreen();
                        }
                    });
                document.getElementById('globe-north')
                    .addEventListener('click', () => {
                        flyCamera({
                            destination: camera.positionWC,
                            orientation: {
                                heading: 0,
                                pitch: camera.pitch,
                                roll: 0,
                            },
                            duration: 0.8,
                        });
                    });
                document.getElementById('globe-zoom-in')
                    .addEventListener('click', () => {
                        camera.zoomIn(
                            camera.positionCartographic.height * 0.35,
                        );
                        keepGlobeVisible();
                    });
                document.getElementById('globe-zoom-out')
                    .addEventListener('click', () => {
                        camera.zoomOut(
                            camera.positionCartographic.height * 0.55,
                        );
                        keepGlobeVisible();
                    });

                scene.postRender.addEventListener(keepGlobeVisible);
                window.addEventListener('message', (event) => {
                    if (event.origin !== window.location.origin) {
                        return;
                    }

                    if (event.data?.type === 'theme-changed') {
                        document.documentElement.classList.toggle(
                            'light-mode',
                            event.data.theme === 'light',
                        );

                        return;
                    }

                    if (event.data?.type !== 'module-shown') {
                        return;
                    }

                    window.requestAnimationFrame(() => {
                        viewer.resize();
                        keepGlobeVisible();
                        scene.requestRender();
                    });
                });

                camera.changed.addEventListener(() => {
                    const position = camera.positionCartographic;
                    const latitude =
                        Cesium.Math.toDegrees(position.latitude);
                    const longitude =
                        Cesium.Math.toDegrees(position.longitude);
                    const altitude = position.height;
                    const altitudeLabel = altitude >= 1_000_000
                        ? (altitude / 1_000_000).toFixed(1) + ' mil km'
                        : Math.round(altitude / 1000) + ' km';

                    mapStatus.textContent =
                        latitude.toFixed(3) + ', '
                        + longitude.toFixed(3)
                        + ' · Alt. ' + altitudeLabel;
                });
                keepGlobeVisible();

                filterProjects();
            })();
        </script>
    </body>
</html>

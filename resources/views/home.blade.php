<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Inicio | Municipalidad de Coatepeque</title>
        <script>
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

            if (!window.__protectedTabAuthorized) {
                document.documentElement.style.visibility = 'hidden';
            }

            try {
                window.__municipalPortalTheme =
                    localStorage.getItem('municipal_portal_theme') === 'light'
                        ? 'light'
                        : 'dark';
            } catch (error) {
                window.__municipalPortalTheme = 'dark';
            }

            document.documentElement.classList.toggle(
                'light-mode',
                window.__municipalPortalTheme === 'light',
            );
        </script>
        <link
            rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/cesium@1.143.0/Build/Cesium/Widgets/widgets.css"
        >

        <style>
            :root {
                color-scheme: dark;
                --page: #050606;
                --surface: rgba(17, 20, 21, 0.94);
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
                min-height: 100svh;
                overflow-x: hidden;
                color: var(--text);
                background:
                    radial-gradient(
                        circle at 50% 12%,
                        rgba(19, 107, 145, 0.1),
                        transparent 34rem
                    ),
                    var(--page);
                font-family:
                    "Segoe UI",
                    Arial,
                    Helvetica,
                    sans-serif;
            }

            button,
            input {
                font: inherit;
            }

            button {
                -webkit-tap-highlight-color: transparent;
            }

            .portal-header {
                position: relative;
                z-index: 10;
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
                grid-template-columns: minmax(270px, 1fr) minmax(260px, 420px) minmax(360px, 1fr);
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

            .brand-cluster {
                display: flex;
                min-width: 0;
                align-items: center;
                gap: 14px;
            }

            .theme-toggle {
                display: inline-flex;
                padding: 0;
                flex: 0 0 auto;
                margin-inline: auto;
                align-items: center;
                justify-content: center;
                width: 46px;
                height: 46px;
                border: 0;
                border-radius: 0;
                color: #89939e;
                background: transparent;
                cursor: pointer;
                transition:
                    color 180ms ease,
                    background-color 180ms ease;
            }

            .theme-toggle:hover {
                color: #dce7ed;
                background: transparent;
            }

            .theme-toggle__icon {
                display: block;
                width: 27px;
                height: 27px;
                fill: none;
                stroke: currentColor;
                stroke-width: 1.8;
                stroke-linecap: round;
                stroke-linejoin: round;
            }

            .theme-toggle__icon--moon {
                display: none;
            }

            .theme-toggle:focus-visible {
                outline: 3px solid rgba(49, 169, 214, 0.34);
                outline-offset: 4px;
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

            .search {
                position: relative;
                display: flex;
                height: 42px;
                overflow: hidden;
                border: 1px solid rgba(255, 255, 255, 0.1);
                border-radius: 8px;
                background: rgba(255, 255, 255, 0.1);
                transition:
                    border-color 160ms ease,
                    background-color 160ms ease,
                    box-shadow 160ms ease;
            }

            .header-center {
                min-width: 0;
            }

            .search:focus-within {
                border-color: rgba(49, 169, 214, 0.72);
                background: rgba(255, 255, 255, 0.13);
                box-shadow: 0 0 0 4px rgba(49, 169, 214, 0.1);
            }

            .search__input {
                width: 100%;
                min-width: 0;
                padding: 0 50px 0 16px;
                border: 0;
                outline: 0;
                color: var(--text);
                background: transparent;
                font-size: 0.86rem;
                font-weight: 600;
            }

            .search__input::placeholder {
                color: rgba(246, 242, 231, 0.48);
                font-style: italic;
                text-transform: uppercase;
            }

            .search__button {
                position: absolute;
                top: 0;
                right: 0;
                display: grid;
                width: 46px;
                height: 100%;
                padding: 0;
                place-items: center;
                border: 0;
                color: var(--gold);
                background: transparent;
                cursor: pointer;
                font-size: 1.3rem;
            }

            .header-actions {
                display: flex;
                min-width: 0;
                align-items: center;
                justify-content: flex-end;
                gap: 22px;
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
                box-shadow:
                    0 10px 24px rgba(0, 0, 0, 0.24),
                    inset 0 1px 0 rgba(255, 255, 255, 0.14);
                cursor: pointer;
                font-size: 0.76rem;
                font-weight: 800;
                letter-spacing: 0.035em;
                text-transform: uppercase;
                transition:
                    border-color 160ms ease,
                    background-color 160ms ease,
                    box-shadow 160ms ease,
                    transform 160ms ease;
            }

            .logout-button::before {
                content: "↗";
                font-size: 0.94rem;
                transform: rotate(45deg);
            }

            .logout-button:hover {
                border-color: rgba(86, 207, 248, 0.85);
                background: linear-gradient(
                    135deg,
                    rgba(24, 128, 170, 0.94),
                    rgba(15, 76, 101, 0.9)
                );
                box-shadow:
                    0 13px 30px rgba(0, 0, 0, 0.3),
                    0 0 20px rgba(49, 169, 214, 0.13);
                transform: translateY(-1px);
            }

            .logout-button:active {
                transform: translateY(0);
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
                border: 0;
                color: rgba(246, 242, 231, 0.76);
                background: transparent;
                cursor: pointer;
                font-size: 0.79rem;
                font-weight: 750;
                letter-spacing: 0.045em;
                text-decoration: none;
                text-transform: uppercase;
                white-space: nowrap;
                transition:
                    color 160ms ease,
                    background-color 160ms ease;
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
                transform: scaleX(0.35);
                transition:
                    opacity 160ms ease,
                    transform 160ms ease;
            }

            .nav-link:hover,
            .nav-link:focus-visible {
                color: #fff;
                background: rgba(255, 255, 255, 0.055);
            }

            .nav-link:hover::after,
            .nav-link:focus-visible::after {
                opacity: 0.7;
                transform: scaleX(1);
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
                right: 22px;
                bottom: 7px;
                left: 22px;
                background: #fff;
                opacity: 0.58;
                transform: scaleX(1);
            }

            .logout-button:focus-visible,
            .search__button:focus-visible,
            .nav-link:focus-visible,
            .brand:focus-visible {
                outline: 3px solid rgba(49, 169, 214, 0.82);
                outline-offset: 3px;
            }

            .module-content {
                min-height: calc(100svh - 161px);
                padding: 24px;
                background:
                    radial-gradient(
                        circle at 78% 15%,
                        rgba(49, 169, 214, 0.09),
                        transparent 34rem
                    ),
                    #050606;
            }

            .embedded-module {
                width: 100%;
                min-height: calc(100svh - 161px);
                padding: 24px;
                background:
                    radial-gradient(
                        circle at 78% 15%,
                        rgba(49, 169, 214, 0.09),
                        transparent 34rem
                    ),
                    #050606;
            }

            .embedded-module[hidden] {
                display: none;
            }

            .embedded-module iframe {
                display: block;
                width: min(1540px, 100%);
                height: clamp(620px, calc(100svh - 210px), 920px);
                margin-inline: auto;
                overflow: hidden;
                border: 1px solid rgba(255, 255, 255, 0.11);
                border-radius: 18px;
                background: #0b0e0f;
                box-shadow:
                    0 28px 80px rgba(0, 0, 0, 0.48),
                    inset 0 1px 0 rgba(255, 255, 255, 0.04);
            }

            .geo-workspace {
                display: grid;
                grid-template-columns: minmax(300px, 350px) minmax(0, 1fr);
                width: min(1540px, 100%);
                height: clamp(620px, calc(100svh - 210px), 920px);
                margin: 0;
                margin-inline: auto;
                overflow: hidden;
                border: 1px solid rgba(255, 255, 255, 0.11);
                border-radius: 18px;
                background: #0b0e0f;
                box-shadow:
                    0 28px 80px rgba(0, 0, 0, 0.48),
                    inset 0 1px 0 rgba(255, 255, 255, 0.04);
            }

            .places-panel {
                position: relative;
                z-index: 3;
                display: flex;
                min-width: 0;
                min-height: 0;
                flex-direction: column;
                border-right: 1px solid rgba(255, 255, 255, 0.1);
                background: linear-gradient(180deg, #121617, #0d1011);
            }

            .places-tree {
                flex: 1;
                min-height: 0;
                padding: 13px;
                overflow-y: auto;
                scrollbar-color: rgba(49, 169, 214, 0.42) transparent;
                scrollbar-width: thin;
            }

            .tree-folder {
                margin: 0 0 8px;
                border: 1px solid rgba(255, 255, 255, 0.075);
                border-radius: 11px;
                background: rgba(255, 255, 255, 0.025);
            }

            .tree-folder[open] {
                border-color: rgba(49, 169, 214, 0.22);
                background: rgba(49, 169, 214, 0.045);
            }

            .tree-folder summary {
                display: flex;
                min-height: 44px;
                padding: 0 13px;
                align-items: center;
                gap: 10px;
                color: rgba(255, 255, 255, 0.88);
                cursor: pointer;
                font-size: 0.81rem;
                font-weight: 760;
                list-style: none;
                user-select: none;
            }

            .tree-folder summary::-webkit-details-marker {
                display: none;
            }

            .tree-folder summary::before {
                width: 16px;
                color: var(--gold);
                content: "▸";
                font-size: 0.78rem;
                transition: transform 150ms ease;
            }

            .tree-folder[open] > summary::before {
                transform: rotate(90deg);
            }

            .folder-icon {
                position: relative;
                width: 25px;
                height: 18px;
                flex: 0 0 auto;
                margin-top: 3px;
                border-radius: 4px;
                background: linear-gradient(135deg, #ffd86b, #e4ac30);
                box-shadow:
                    inset 0 1px 0 rgba(255, 255, 255, 0.42),
                    0 3px 8px rgba(0, 0, 0, 0.2);
            }

            .folder-icon::before {
                position: absolute;
                bottom: calc(100% - 3px);
                left: 3px;
                width: 11px;
                height: 5px;
                border-radius: 3px 3px 0 0;
                background: #ffd86b;
                content: "";
            }

            .folder-year {
                margin-left: auto;
                padding: 3px 7px;
                border: 1px solid rgba(49, 169, 214, 0.25);
                border-radius: 999px;
                color: #9edff5;
                background: rgba(49, 169, 214, 0.09);
                font-size: 0.62rem;
                letter-spacing: 0.06em;
            }

            .folder-title,
            .folder-name {
                min-width: 0;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .folder-add {
                display: grid;
                width: 28px;
                height: 28px;
                margin-left: auto;
                padding: 0;
                flex: 0 0 auto;
                place-items: center;
                border: 1px solid rgba(49, 169, 214, 0.34);
                border-radius: 8px;
                color: #d8f4ff;
                background: rgba(49, 169, 214, 0.12);
                cursor: pointer;
                font-size: 1.2rem;
                font-weight: 500;
                line-height: 1;
                transition:
                    border-color 150ms ease,
                    background-color 150ms ease,
                    transform 150ms ease;
            }

            .folder-add:hover,
            .folder-add:focus-visible {
                border-color: rgba(49, 169, 214, 0.72);
                background: rgba(49, 169, 214, 0.24);
                outline: none;
                transform: translateY(-1px);
            }

            .folder-add--new-folder {
                position: relative;
                width: 35px;
                height: 29px;
                border: 0;
                border-radius: 8px;
                color: transparent;
                background: transparent;
                overflow: visible;
            }

            .folder-add--new-folder::before {
                position: absolute;
                top: 4px;
                left: 2px;
                width: 28px;
                height: 20px;
                border-radius: 4px;
                background: linear-gradient(135deg, #ffb62e, #ff9f1c);
                clip-path: polygon(
                    0 18%,
                    28% 18%,
                    34% 0,
                    58% 0,
                    65% 18%,
                    100% 18%,
                    100% 100%,
                    0 100%
                );
                box-shadow: 0 4px 9px rgba(0, 0, 0, 0.24);
                content: "";
            }

            .folder-add--new-folder::after {
                position: absolute;
                right: 0;
                bottom: 0;
                display: grid;
                width: 18px;
                height: 18px;
                place-items: center;
                border: 2px solid #10232b;
                border-radius: 50%;
                color: #fff;
                background: #0b64a8;
                box-shadow: 0 3px 7px rgba(0, 0, 0, 0.28);
                content: "+";
                font-size: 0.9rem;
                font-weight: 500;
                line-height: 1;
            }

            .folder-add--new-folder:hover,
            .folder-add--new-folder:focus-visible {
                border-color: transparent;
                background: transparent;
                filter: brightness(1.08);
            }

            .folder-name-input {
                width: 100%;
                min-width: 0;
                padding: 5px 7px;
                border: 1px solid #31a9d6;
                border-radius: 5px;
                color: #fff;
                background: #14262c;
                box-shadow: 0 0 0 2px rgba(49, 169, 214, 0.13);
                font: inherit;
                outline: none;
            }

            .folder-name-input::selection {
                color: #fff;
                background: #167da4;
            }

            .empty-folder {
                margin: 4px 0 2px 29px;
                color: rgba(246, 242, 231, 0.4);
                font-size: 0.69rem;
            }

            .folder-feedback {
                min-height: 17px;
                margin: 0 12px 8px 46px;
                color: #ef9a9a;
                font-size: 0.67rem;
                line-height: 1.35;
            }

            .folder-feedback:empty {
                display: none;
            }

            .folder-context-menu {
                position: fixed;
                z-index: 2200;
                display: grid;
                width: 180px;
                padding: 6px;
                gap: 3px;
                border: 1px solid rgba(255, 255, 255, 0.14);
                border-radius: 10px;
                background: rgba(15, 21, 23, 0.97);
                box-shadow: 0 18px 45px rgba(0, 0, 0, 0.46);
                backdrop-filter: blur(14px);
            }

            .folder-context-menu[hidden] {
                display: none;
            }

            .folder-context-menu button {
                display: flex;
                min-height: 36px;
                padding: 0 10px;
                align-items: center;
                gap: 9px;
                border: 0;
                border-radius: 7px;
                color: rgba(255, 255, 255, 0.84);
                background: transparent;
                cursor: pointer;
                font: inherit;
                font-size: 0.75rem;
                text-align: left;
            }

            .folder-context-menu button:hover,
            .folder-context-menu button:focus-visible {
                color: #fff;
                background: rgba(49, 169, 214, 0.14);
                outline: none;
            }

            .folder-context-menu button[data-folder-action="delete"],
            .folder-context-menu button[data-project-action="delete"],
            .folder-context-menu button[data-project-document-action="delete"] {
                color: #f0a0a0;
            }

            .folder-context-menu__icon {
                width: 17px;
                color: var(--blue-bright);
                font-size: 0.9rem;
                text-align: center;
            }

            .folder-context-menu button[data-folder-action="delete"]
                .folder-context-menu__icon,
            .folder-context-menu button[data-project-action="delete"]
                .folder-context-menu__icon,
            .folder-context-menu button[data-project-document-action="delete"]
                .folder-context-menu__icon {
                color: #e67676;
            }

            .folder-dialog {
                width: min(410px, calc(100% - 32px));
                padding: 0;
                border: 1px solid rgba(255, 255, 255, 0.15);
                border-radius: 16px;
                color: #f5f1e7;
                background: linear-gradient(150deg, #1a2325, #0e1213);
                box-shadow: 0 28px 80px rgba(0, 0, 0, 0.58);
            }

            .folder-dialog::backdrop {
                background: rgba(0, 0, 0, 0.7);
                backdrop-filter: blur(5px);
            }

            .folder-dialog__form {
                display: grid;
                padding: 24px;
                gap: 15px;
            }

            .folder-dialog__head {
                display: grid;
                grid-template-columns: minmax(0, 1fr) auto;
                align-items: start;
                gap: 14px;
            }

            .folder-dialog__head h2,
            .folder-dialog__head p {
                margin: 0;
            }

            .folder-dialog__head h2 {
                color: #fff;
                font-size: 1rem;
            }

            .folder-dialog__head p,
            .folder-dialog__copy {
                margin: 5px 0 0;
                color: rgba(246, 242, 231, 0.58);
                font-size: 0.76rem;
                line-height: 1.5;
            }

            .folder-dialog__close {
                width: 30px;
                height: 30px;
                padding: 0;
                border: 1px solid rgba(255, 255, 255, 0.14);
                border-radius: 50%;
                color: rgba(255, 255, 255, 0.8);
                background: rgba(255, 255, 255, 0.05);
                cursor: pointer;
                font-size: 1rem;
            }

            .folder-dialog__label {
                display: grid;
                gap: 7px;
                color: rgba(255, 255, 255, 0.78);
                font-size: 0.73rem;
                font-weight: 700;
            }

            .folder-dialog__label[hidden] {
                display: none;
            }

            .coordinate-fields {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 12px;
            }

            .folder-dialog__input {
                width: 100%;
                padding: 11px 12px;
                border: 1px solid rgba(255, 255, 255, 0.14);
                border-radius: 9px;
                color: #fff;
                background: rgba(4, 7, 8, 0.56);
                font: inherit;
                outline: none;
            }

            .folder-dialog__input:focus {
                border-color: var(--blue-bright);
                box-shadow: 0 0 0 3px rgba(49, 169, 214, 0.13);
            }

            .folder-dialog__input:disabled {
                color: rgba(255, 255, 255, 0.42);
                background: rgba(4, 7, 8, 0.34);
                cursor: not-allowed;
                opacity: 0.75;
            }

            .coordinate-fallback {
                display: flex;
                width: fit-content;
                align-items: center;
                gap: 9px;
                color: rgba(255, 255, 255, 0.76);
                cursor: pointer;
                font-size: 0.73rem;
                font-weight: 650;
                user-select: none;
            }

            .coordinate-fallback input {
                width: 16px;
                height: 16px;
                margin: 0;
                accent-color: var(--blue-bright);
                cursor: pointer;
            }

            .folder-dialog__error {
                min-height: 18px;
                margin: -5px 0 0;
                color: #f09a9a;
                font-size: 0.7rem;
            }

            .folder-dialog__error:empty {
                display: none;
            }

            .folder-dialog__actions {
                display: flex;
                justify-content: flex-end;
                gap: 9px;
            }

            .folder-dialog__button {
                min-height: 38px;
                padding: 0 15px;
                border: 1px solid rgba(255, 255, 255, 0.14);
                border-radius: 9px;
                color: rgba(255, 255, 255, 0.84);
                background: rgba(255, 255, 255, 0.06);
                cursor: pointer;
                font: inherit;
                font-size: 0.75rem;
                font-weight: 700;
            }

            .folder-dialog__button--primary {
                border-color: rgba(49, 169, 214, 0.56);
                color: #fff;
                background: rgba(20, 120, 158, 0.72);
            }

            .folder-dialog__button--danger {
                border-color: rgba(218, 82, 82, 0.58);
                color: #fff;
                background: rgba(145, 43, 43, 0.78);
            }

            .folder-dialog__button:disabled {
                cursor: wait;
                opacity: 0.55;
            }

            .tree-children {
                display: grid;
                padding: 2px 8px 10px 20px;
                gap: 4px;
            }

            .project-item {
                display: grid;
                grid-template-columns: 26px auto minmax(0, 1fr);
                width: 100%;
                min-height: 47px;
                padding: 7px 9px;
                align-items: center;
                gap: 8px;
                border: 1px solid transparent;
                border-radius: 8px;
                color: rgba(246, 242, 231, 0.75);
                background: transparent;
                cursor: pointer;
                text-align: left;
                transition:
                    color 150ms ease,
                    border-color 150ms ease,
                    background-color 150ms ease,
                    transform 150ms ease;
            }

            .project-item:hover,
            .project-item:focus-visible,
            .project-item.is-active {
                color: #fff;
                border-color: rgba(49, 169, 214, 0.28);
                background: rgba(49, 169, 214, 0.1);
                outline: none;
                transform: translateX(2px);
            }

            .project-item.is-hidden {
                display: none;
            }

            .project-pin {
                display: grid;
                width: 24px;
                height: 24px;
                place-items: center;
                border: 1px solid rgba(255, 255, 255, 0.15);
                border-radius: 7px;
                color: #fff;
                background: var(--pin-color, #2086ae);
                box-shadow: 0 5px 12px rgba(0, 0, 0, 0.24);
                font-size: 0.68rem;
                font-weight: 800;
            }

            .project-completion-status {
                display: flex;
                height: 18px;
                flex: 0 0 auto;
                align-items: center;
                gap: 3px;
            }

            .project-completion-status__count {
                min-width: 7px;
                color: #ffc329;
                font-size: 0.66rem;
                font-weight: 900;
                line-height: 1;
                text-align: right;
            }

            .project-completion-status__icon {
                display: grid;
                width: 18px;
                height: 18px;
                place-items: center;
                font-size: 0.62rem;
                font-weight: 900;
                line-height: 1;
            }

            .project-completion-status--missing .project-completion-status__icon {
                padding-top: 4px;
                color: #343434;
                background: #ffc329;
                clip-path: polygon(50% 0, 100% 100%, 0 100%);
                text-shadow: 0 1px 0 rgba(255, 255, 255, 0.3);
            }

            .project-completion-status--complete .project-completion-status__count {
                display: none;
            }

            .project-completion-status--complete .project-completion-status__icon {
                border: 1px solid rgba(167, 242, 132, 0.72);
                border-radius: 50%;
                color: #102a17;
                background: #91df6c;
                box-shadow:
                    inset 0 1px 0 rgba(255, 255, 255, 0.38),
                    0 2px 6px rgba(0, 0, 0, 0.24);
                font-size: 0.72rem;
            }

            .project-copy {
                display: grid;
                min-width: 0;
                gap: 3px;
            }

            .project-copy strong {
                overflow: hidden;
                font-size: 0.73rem;
                font-weight: 700;
                line-height: 1.25;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .project-copy small {
                color: rgba(246, 242, 231, 0.42);
                font-size: 0.64rem;
            }

            .project-files-panel[hidden],
            #projects-tree-view[hidden] {
                display: none;
            }

            .project-files-panel {
                display: grid;
                grid-template-rows: auto auto minmax(0, 1fr) auto auto auto;
                height: 100%;
                min-height: 0;
                gap: 13px;
                overflow: hidden;
            }

            .project-files-back {
                display: inline-flex;
                width: fit-content;
                min-height: 32px;
                padding: 0 10px;
                align-items: center;
                gap: 7px;
                border: 1px solid rgba(49, 169, 214, 0.28);
                border-radius: 8px;
                color: #c9effc;
                background: rgba(49, 169, 214, 0.08);
                cursor: pointer;
                font: inherit;
                font-size: 0.7rem;
                font-weight: 700;
            }

            .project-files-back:hover,
            .project-files-back:focus-visible {
                border-color: rgba(49, 169, 214, 0.62);
                background: rgba(49, 169, 214, 0.16);
                outline: none;
            }

            .project-files-head {
                padding: 13px;
                border: 1px solid rgba(49, 169, 214, 0.2);
                border-radius: 11px;
                background: rgba(49, 169, 214, 0.055);
            }

            .project-files-head span,
            .project-files-head h2,
            .project-files-head p {
                margin: 0;
            }

            .project-files-head span {
                color: var(--gold);
                font-size: 0.61rem;
                font-weight: 800;
                letter-spacing: 0.1em;
                text-transform: uppercase;
            }

            .project-files-head h2 {
                margin-top: 5px;
                overflow-wrap: anywhere;
                color: #fff;
                font-size: 0.83rem;
                line-height: 1.35;
            }

            .project-files-head p {
                margin-top: 4px;
                color: rgba(246, 242, 231, 0.48);
                font-size: 0.66rem;
            }

            .project-file-list {
                display: flex;
                min-height: 0;
                padding-right: 4px;
                flex-direction: column;
                gap: 8px;
                overflow-y: auto;
                scrollbar-color: rgba(49, 169, 214, 0.42) transparent;
                scrollbar-width: thin;
            }

            .project-file-slot {
                display: block;
                flex: 0 0 auto;
                overflow: hidden;
                border: 1px solid rgba(255, 255, 255, 0.085);
                border-radius: 10px;
                background: rgba(255, 255, 255, 0.025);
            }

            .project-file-slot.is-open {
                border-color: rgba(49, 169, 214, 0.2);
                background: rgba(49, 169, 214, 0.045);
            }

            .project-file-slot.is-loading {
                pointer-events: none;
                opacity: 0.58;
            }

            .project-file-slot__head,
            .project-file-slot__actions {
                display: flex;
                align-items: center;
                gap: 7px;
            }

            .project-file-slot__head {
                width: 100%;
                min-height: 42px;
                padding: 7px 10px;
                border: 0;
                cursor: pointer;
                background: transparent;
                text-align: left;
                user-select: none;
            }

            .project-file-slot__head::after {
                content: "▸";
                margin-left: 3px;
                color: rgba(122, 213, 245, 0.72);
                font-size: 0.68rem;
                transition: transform 160ms ease;
            }

            .project-file-slot.is-open > .project-file-slot__head::after {
                transform: rotate(90deg);
            }

            .project-file-slot__head:hover,
            .project-file-slot__head:focus-visible {
                background: rgba(49, 169, 214, 0.075);
                outline: none;
            }

            .project-file-slot__head strong {
                color: rgba(255, 255, 255, 0.82);
                font-size: 0.71rem;
            }

            .project-file-slot__folder {
                position: relative;
                width: 25px;
                height: 18px;
                flex: 0 0 auto;
                margin-top: 3px;
                border-radius: 4px;
                background: linear-gradient(135deg, #ffd86b, #e4ac30);
                box-shadow:
                    inset 0 1px 0 rgba(255, 255, 255, 0.42),
                    0 3px 8px rgba(0, 0, 0, 0.2);
            }

            .project-file-slot__folder::before {
                position: absolute;
                bottom: calc(100% - 3px);
                left: 3px;
                width: 11px;
                height: 5px;
                border-radius: 3px 3px 0 0;
                background: #ffd86b;
                content: "";
            }

            .project-file-slot__status {
                display: grid;
                width: 22px;
                height: 22px;
                margin-left: auto;
                flex: 0 0 auto;
                place-items: center;
                font-size: 0.78rem;
                font-weight: 900;
                line-height: 1;
            }

            .project-file-slot__status--missing {
                padding-top: 5px;
                color: #343434;
                background: #ffc329;
                clip-path: polygon(50% 0, 100% 100%, 0 100%);
                text-shadow: 0 1px 0 rgba(255, 255, 255, 0.3);
            }

            .project-file-slot__status--complete {
                border: 1px solid rgba(167, 242, 132, 0.72);
                border-radius: 50%;
                color: #102a17;
                background: #91df6c;
                box-shadow:
                    inset 0 1px 0 rgba(255, 255, 255, 0.38),
                    0 2px 6px rgba(0, 0, 0, 0.24);
                font-size: 0.9rem;
            }

            .project-file-slot__body {
                display: grid;
                padding: 8px 10px 10px;
                gap: 8px;
                border-top: 1px solid rgba(255, 255, 255, 0.065);
            }

            .project-file-slot__body[hidden] {
                display: none;
            }

            .project-file-slot__file,
            .project-file-slot__empty {
                margin: 0;
                overflow: hidden;
                color: rgba(246, 242, 231, 0.52);
                font-size: 0.65rem;
                line-height: 1.35;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .project-file-slot__file {
                color: rgba(246, 242, 231, 0.72);
            }

            .project-file-slot__actions {
                flex-wrap: wrap;
                justify-content: flex-end;
            }

            .project-file-action {
                display: inline-flex;
                min-height: 29px;
                padding: 0 9px;
                align-items: center;
                justify-content: center;
                border: 1px solid rgba(49, 169, 214, 0.28);
                border-radius: 7px;
                color: #d6f4ff;
                background: rgba(49, 169, 214, 0.1);
                cursor: pointer;
                font: inherit;
                font-size: 0.64rem;
                font-weight: 700;
                text-decoration: none;
            }

            .project-file-action:hover,
            .project-file-action:focus-visible {
                border-color: rgba(49, 169, 214, 0.65);
                background: rgba(49, 169, 214, 0.2);
                outline: none;
            }

            .project-file-action--danger {
                border-color: rgba(223, 106, 106, 0.34);
                color: #f2b0b0;
                background: rgba(145, 43, 43, 0.12);
            }

            .project-file-action--view {
                border-color: rgba(221, 183, 83, 0.34);
                color: #f5e5af;
                background: rgba(221, 183, 83, 0.1);
            }

            .project-file-action--link {
                min-height: 34px;
                border-color: rgba(99, 189, 255, 0.4);
                color: #dff5ff;
                background: rgba(29, 125, 177, 0.15);
                line-height: 1.25;
                text-align: left;
                white-space: normal;
            }

            .project-file-input {
                position: absolute;
                width: 1px;
                height: 1px;
                overflow: hidden;
                clip: rect(0 0 0 0);
                clip-path: inset(50%);
                white-space: nowrap;
            }

            .project-files-feedback {
                min-height: 16px;
                margin: 0;
                color: #f09a9a;
                font-size: 0.67rem;
                line-height: 1.4;
            }

            .project-files-feedback.is-success {
                color: #92d9aa;
            }

            .project-document-add {
                display: flex;
                width: 100%;
                min-height: 40px;
                padding: 8px 12px;
                align-items: center;
                justify-content: center;
                gap: 8px;
                border: 1px dashed rgba(49, 169, 214, 0.42);
                border-radius: 9px;
                color: #d6f4ff;
                background: rgba(49, 169, 214, 0.075);
                cursor: pointer;
                font: inherit;
                font-size: 0.7rem;
                font-weight: 800;
            }

            .project-document-add::before {
                display: grid;
                width: 20px;
                height: 20px;
                place-items: center;
                border-radius: 6px;
                color: #08212b;
                background: #6dd2f5;
                content: "+";
                font-size: 1rem;
                line-height: 1;
            }

            .project-document-add:hover,
            .project-document-add:focus-visible {
                border-color: rgba(49, 169, 214, 0.8);
                background: rgba(49, 169, 214, 0.16);
                outline: none;
            }

            .project-files-note {
                margin: 0;
                color: rgba(246, 242, 231, 0.36);
                font-size: 0.61rem;
                line-height: 1.45;
            }

            .layer-panel {
                padding: 16px 18px 18px;
                border-top: 1px solid rgba(255, 255, 255, 0.08);
                background: rgba(0, 0, 0, 0.14);
            }

            .layer-panel__title {
                margin: 0 0 12px;
                color: rgba(255, 255, 255, 0.62);
                font-size: 0.68rem;
                font-weight: 800;
                letter-spacing: 0.1em;
                text-transform: uppercase;
            }

            .layer-options {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 9px;
            }

            .layer-option {
                display: flex;
                align-items: center;
                gap: 8px;
                color: rgba(246, 242, 231, 0.64);
                font-size: 0.69rem;
                cursor: pointer;
            }

            .layer-option input {
                width: 14px;
                height: 14px;
                margin: 0;
                accent-color: var(--blue-bright);
            }

            .map-panel {
                position: relative;
                min-width: 0;
                min-height: 620px;
                overflow: hidden;
                background: #070a0c;
            }

            #project-map {
                position: absolute;
                inset: 0;
                z-index: 1;
                background: #081015;
            }

            .cesium-widget,
            .cesium-widget canvas {
                width: 100%;
                height: 100%;
            }

            .cesium-widget-credits {
                position: static !important;
                display: flex !important;
                max-width: 280px;
                padding: 8px 10px !important;
                flex-wrap: wrap;
                gap: 4px 8px;
                color: rgba(255, 255, 255, 0.7) !important;
                background: transparent !important;
                font-size: 0.62rem !important;
            }

            .cesium-viewer-bottom {
                display: none;
            }

            .map-credits {
                position: absolute;
                z-index: 500;
                left: 12px;
                bottom: 12px;
                color: rgba(255, 255, 255, 0.76);
                font-size: 0.65rem;
            }

            .map-credits summary {
                width: max-content;
                padding: 6px 9px;
                border: 1px solid rgba(255, 255, 255, 0.14);
                border-radius: 7px;
                background: rgba(8, 12, 14, 0.8);
                box-shadow: 0 6px 16px rgba(0, 0, 0, 0.24);
                cursor: pointer;
                list-style: none;
                user-select: none;
                backdrop-filter: blur(8px);
            }

            .map-credits summary::-webkit-details-marker {
                display: none;
            }

            .map-credits summary::before {
                margin-right: 5px;
                color: var(--blue-bright);
                content: "ⓘ";
            }

            .map-credits[open] summary {
                border-radius: 7px 7px 0 0;
            }

            .map-credits__content {
                max-width: min(300px, calc(100vw - 48px));
                border: 1px solid rgba(255, 255, 255, 0.14);
                border-top: 0;
                border-radius: 0 7px 7px 7px;
                background: rgba(8, 12, 14, 0.92);
                box-shadow: 0 10px 26px rgba(0, 0, 0, 0.32);
                backdrop-filter: blur(10px);
            }

            .cesium-selection-wrapper,
            .cesium-viewer-selectionIndicatorContainer {
                display: none;
            }

            .map-toolbar {
                position: absolute;
                z-index: 500;
                top: 18px;
                left: 18px;
                display: flex;
                max-width: calc(100% - 92px);
                padding: 8px;
                align-items: center;
                gap: 8px;
                border: 1px solid rgba(255, 255, 255, 0.15);
                border-radius: 12px;
                background: rgba(10, 14, 16, 0.86);
                box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35);
                backdrop-filter: blur(12px);
            }

            .map-toolbar__copy {
                min-width: 0;
                padding: 0 8px;
            }

            .map-toolbar__copy strong,
            .map-toolbar__copy span {
                display: block;
            }

            .map-toolbar__copy strong {
                color: #fff;
                font-size: 0.77rem;
            }

            .map-toolbar__copy span {
                margin-top: 2px;
                overflow: hidden;
                color: rgba(255, 255, 255, 0.5);
                font-size: 0.65rem;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .map-action {
                display: grid;
                width: 36px;
                height: 36px;
                flex: 0 0 auto;
                padding: 0;
                place-items: center;
                border: 1px solid rgba(255, 255, 255, 0.12);
                border-radius: 8px;
                color: #d7f3fc;
                background: rgba(49, 169, 214, 0.12);
                cursor: pointer;
                transition:
                    border-color 150ms ease,
                    background-color 150ms ease;
            }

            .map-action:hover,
            .map-action:focus-visible {
                border-color: rgba(49, 169, 214, 0.7);
                background: rgba(49, 169, 214, 0.28);
                outline: none;
            }

            .map-status {
                position: absolute;
                z-index: 500;
                right: 16px;
                bottom: 28px;
                padding: 7px 10px;
                border: 1px solid rgba(255, 255, 255, 0.12);
                border-radius: 8px;
                color: rgba(255, 255, 255, 0.68);
                background: rgba(8, 12, 14, 0.78);
                box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
                font-family: Consolas, monospace;
                font-size: 0.64rem;
                backdrop-filter: blur(9px);
                pointer-events: none;
            }

            .globe-navigation {
                position: absolute;
                z-index: 500;
                top: 78px;
                right: 16px;
                display: grid;
                gap: 8px;
            }

            .globe-navigation .map-action {
                border-radius: 50%;
                background: rgba(15, 20, 23, 0.86);
                backdrop-filter: blur(10px);
            }

            html.light-mode {
                color-scheme: light;
                --page: #eef3f7;
                --surface: rgba(255, 255, 255, 0.97);
                --surface-soft: rgba(23, 49, 72, 0.055);
                --line: rgba(31, 57, 79, 0.16);
                --text: #172033;
                --muted: #66717e;
                --gold: #a36d13;
            }

            html.light-mode body,
            html.light-mode .module-content,
            html.light-mode .embedded-module {
                color: var(--text);
                background:
                    radial-gradient(
                        circle at 78% 12%,
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
                font-size: 1.05rem;
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

            html.light-mode .search {
                border-color: #d8e2eb;
                background: #f2f6f9;
            }

            html.light-mode .search__input {
                color: #172033;
            }

            html.light-mode .search__input::placeholder,
            html.light-mode .contact,
            html.light-mode .project-copy small,
            html.light-mode .project-files-note,
            html.light-mode .project-file-slot__file,
            html.light-mode .project-file-slot__empty,
            html.light-mode .empty-folder {
                color: #6b7682;
            }

            html.light-mode .contact strong,
            html.light-mode .tree-folder summary,
            html.light-mode .project-item,
            html.light-mode .project-file-slot__head strong,
            html.light-mode .project-files-head h2 {
                color: #172033;
            }

            html.light-mode .nav-shell {
                border-top-color: rgba(31, 57, 79, 0.08);
                background: linear-gradient(
                    90deg,
                    rgba(49, 169, 214, 0.1),
                    rgba(255, 255, 255, 0.9) 38%,
                    rgba(213, 178, 109, 0.11)
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

            html.light-mode .theme-toggle {
                color: #118fc8;
                background: transparent;
            }

            html.light-mode .theme-toggle:hover {
                color: #087eb4;
                background: transparent;
            }

            html.light-mode .theme-toggle__icon--sun {
                display: none;
            }

            html.light-mode .theme-toggle__icon--moon {
                display: block;
            }

            html.light-mode .geo-workspace,
            html.light-mode .embedded-module iframe {
                border-color: rgba(31, 57, 79, 0.17);
                background: #fff;
                box-shadow:
                    0 24px 60px rgba(31, 57, 79, 0.16),
                    inset 0 1px 0 #fff;
            }

            html.light-mode .places-panel {
                border-color: rgba(31, 57, 79, 0.13);
                background: linear-gradient(180deg, #fff, #f4f7f9);
            }

            html.light-mode .tree-folder,
            html.light-mode .project-file-slot,
            html.light-mode .project-files-head {
                border-color: rgba(31, 57, 79, 0.13);
                background: rgba(31, 57, 79, 0.035);
            }

            html.light-mode .tree-folder[open],
            html.light-mode .project-file-slot.is-open,
            html.light-mode .project-item:hover,
            html.light-mode .project-item:focus-visible,
            html.light-mode .project-item.is-active {
                color: #172033;
                border-color: rgba(19, 107, 145, 0.24);
                background: rgba(49, 169, 214, 0.09);
            }

            html.light-mode .project-files-panel {
                background: #f4f8fa;
            }

            html.light-mode .layer-panel {
                border-top-color: rgba(31, 57, 79, 0.12);
                background: #edf3f6;
            }

            html.light-mode .layer-panel__title,
            html.light-mode .layer-option {
                color: #53616e;
            }

            html.light-mode .folder-name-input,
            html.light-mode .folder-dialog__input {
                color: #172033;
                background: #f8fafb;
            }

            html.light-mode .folder-context-menu,
            html.light-mode .folder-dialog {
                border-color: rgba(31, 57, 79, 0.18);
                color: #172033;
                background: #fff;
                box-shadow: 0 24px 60px rgba(31, 57, 79, 0.22);
            }

            html.light-mode .folder-context-menu button,
            html.light-mode .folder-dialog__head h2,
            html.light-mode .folder-dialog__label {
                color: #172033;
            }

            html.light-mode .folder-dialog__head p,
            html.light-mode .folder-dialog__copy {
                color: #66717e;
            }

            html.light-mode .folder-dialog__close {
                border-color: rgba(31, 57, 79, 0.17);
                color: #53616e;
                background: #eef3f6;
            }

            html.light-mode .project-file-slot__body {
                border-top-color: rgba(31, 57, 79, 0.1);
            }

            html.light-mode .project-file-action,
            html.light-mode .project-files-back,
            html.light-mode .project-document-add {
                color: #0e668a;
                background: rgba(49, 169, 214, 0.09);
            }

            html.light-mode .logout-button {
                color: #fff;
            }

            @media (max-width: 1120px) {
                .header-top {
                    grid-template-columns: 1fr minmax(240px, 340px);
                    gap: 18px 28px;
                    padding: 20px 0;
                }

                .header-center {
                    grid-column: 1 / -1;
                    grid-row: 2;
                }

                .header-actions {
                    grid-column: 2;
                    grid-row: 1;
                }

                .module-content {
                    min-height: calc(100svh - 227px);
                }
            }

            @media (max-width: 900px) {
                .geo-workspace {
                    grid-template-columns: 1fr;
                    height: auto;
                    min-height: auto;
                    overflow: visible;
                }

                .places-panel {
                    max-height: 470px;
                    border-right: 0;
                    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                }

                .places-tree {
                    max-height: 260px;
                }

                .map-panel {
                    min-height: 580px;
                }
            }

            @media (max-width: 720px) {
                .header-top,
                .nav-list {
                    width: min(100% - 28px, 1440px);
                }

                .header-top {
                    grid-template-columns: 1fr;
                    gap: 16px;
                }

                .brand,
                .header-actions,
                .header-center {
                    grid-column: 1;
                }

                .brand-cluster {
                    grid-column: 1;
                    grid-row: 1;
                    justify-content: space-between;
                }

                .theme-toggle {
                    margin-right: 0;
                }

                .brand {
                    grid-row: 1;
                }

                .header-actions {
                    grid-row: 2;
                    justify-content: space-between;
                }

                .header-center {
                    grid-row: 3;
                }

                .nav-link {
                    padding-right: 15px;
                    padding-left: 15px;
                    font-size: 0.73rem;
                }

                .contact {
                    white-space: normal;
                }

                .module-content {
                    min-height: calc(100svh - 293px);
                    padding: 14px;
                }

                .embedded-module {
                    min-height: calc(100svh - 293px);
                    padding: 14px;
                }

                .map-panel {
                    min-height: 500px;
                }

                .map-toolbar {
                    top: 10px;
                    left: 10px;
                    max-width: calc(100% - 64px);
                }

                .map-status {
                    display: none;
                }
            }

            @media (max-width: 460px) {
                .coordinate-fields {
                    grid-template-columns: 1fr;
                }

                .brand {
                    width: 235px;
                    height: 72px;
                }

                .brand__art {
                    transform: scale(0.9);
                    transform-origin: top left;
                }

                .brand-cluster {
                    gap: 6px;
                }

                .brand__wordmark {
                    left: 73px;
                }

                .brand__wordmark strong {
                    font-size: 0.76rem;
                }

                .brand__wordmark small {
                    font-size: 0.41rem;
                }

                .contact {
                    display: none;
                }

                .header-actions {
                    justify-content: flex-end;
                }

                .logout-button {
                    width: 100%;
                }
            }

            @media (prefers-reduced-motion: reduce) {
                *,
                *::before,
                *::after {
                    scroll-behavior: auto !important;
                    transition-duration: 0.01ms !important;
                }
            }
        </style>
    </head>
    <body
        data-session-activity-url="{{ route('session.activity') }}"
        data-project-folders-url="{{ route('project-folders.store') }}"
        data-projects-url="{{ url('/proyectos') }}"
        data-login-url="{{ route('login') }}"
        data-logout-url="{{ route('logout') }}"
        data-idle-timeout-ms="{{ config('session.lifetime') * 60 * 1000 }}"
    >
        <header class="portal-header">
            <div class="header-top">
                <div class="brand-cluster">
                    <div class="brand">
                        <span class="brand__art" aria-hidden="true"></span>
                        <span class="brand__wordmark" aria-hidden="true">
                            <strong>Gobierno Municipal</strong>
                            <small>Coatepeque, Quetzaltenango</small>
                        </span>
                    </div>
                    <button
                        class="theme-toggle"
                        id="theme-toggle"
                        type="button"
                        aria-label="Cambiar entre modo claro y oscuro"
                        aria-pressed="false"
                    >
                        <svg class="theme-toggle__icon theme-toggle__icon--sun" viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="12" r="4"></circle>
                            <path d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.42-1.42M17.66 6.34l1.41-1.41"></path>
                        </svg>
                        <svg class="theme-toggle__icon theme-toggle__icon--moon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M20.5 14.3A8.5 8.5 0 0 1 9.7 3.5a8.5 8.5 0 1 0 10.8 10.8Z"></path>
                        </svg>
                    </button>
                </div>

                <div class="header-center">
                    <div class="search" id="portal-search" role="search">
                        <input
                            class="search__input"
                            type="search"
                            placeholder="Buscar..."
                            aria-label="Buscar en el portal"
                        >
                        <button
                            class="search__button"
                            type="button"
                            aria-label="Buscar"
                        >
                            <span aria-hidden="true">⌕</span>
                        </button>
                    </div>

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
                                class="nav-link is-active"
                                href="{{ route('home') }}"
                                data-module-target="home"
                                aria-current="page"
                            >
                                Inicio
                            </a>
                        </li>
                        <li>
                            <a
                                class="nav-link"
                                href="{{ route('project-locations') }}"
                                data-module-target="locations"
                            >Ubicación de Proyectos</a>
                        </li>
                        <li>
                            <a
                                class="nav-link"
                                href="{{ route('project-monitoring.index') }}"
                                data-module-target="monitoring"
                            >Monitoreo</a>
                        </li>
                        <li>
                            <a
                                class="nav-link"
                                href="{{ route('project-finance.index') }}"
                                data-module-target="finance"
                            >Control financiero</a>
                        </li>
                        @if (mb_strtolower(auth()->user()->email) === mb_strtolower(config('security.user_management_admin_email')))
                            <li>
                                <a
                                    class="nav-link"
                                    href="{{ route('user-management.index') }}"
                                    data-module-target="users"
                                >Usuarios</a>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
        </header>

        <main
            class="module-content"
            id="home-module"
            data-module-panel="home"
        >
            <section class="geo-workspace" aria-label="Mapa de proyectos municipales">
                <aside class="places-panel">
                    <div class="places-tree" id="places-tree">
                        <div id="projects-tree-view">
                        <details class="tree-folder" open>
                            <summary>
                                <span class="folder-icon" aria-hidden="true"></span>
                                <span class="folder-title">Proyectos</span>
                                <button
                                    class="folder-add folder-add--new-folder"
                                    id="add-project-folder"
                                    type="button"
                                    title="Crear carpeta nueva"
                                    aria-label="Crear carpeta nueva"
                                ></button>
                            </summary>
                            <div class="tree-children" id="project-folders">
                                @foreach ($projectFolders as $folder)
                                    <details
                                        class="tree-folder user-folder"
                                        data-folder-id="{{ $folder->id }}"
                                        data-folder-name="{{ $folder->name }}"
                                        data-folder-color="{{ $folder->color }}"
                                        @if ($folder->is_primary) open @endif
                                    >
                                        <summary>
                                            <span class="folder-icon" aria-hidden="true"></span>
                                            <span class="folder-name">{{ $folder->name }}</span>
                                            <button
                                                class="folder-add"
                                                type="button"
                                                data-add-project
                                                title="Crear proyecto en {{ $folder->name }}"
                                                aria-label="Crear proyecto en {{ $folder->name }}"
                                            >+</button>
                                        </summary>
                                        <div class="tree-children" data-project-list>
                                            @forelse ($folder->projects as $project)
                                                <button
                                                    class="project-item"
                                                    type="button"
                                                    data-project="{{ $project->id }}"
                                                    data-search="{{ mb_strtolower($project->name.' '.$project->place.' '.($project->snip ?? '')) }}"
                                                >
                                                    <span
                                                        class="project-pin"
                                                        style="--pin-color: {{ $project->color }}"
                                                    >{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                                    @php
                                                        $projectMissingFiles = $project->documentFolders
                                                            ->reject(fn ($documentFolder) => $project->files->contains('slot', $documentFolder->position))
                                                            ->count();
                                                        $projectFilesComplete = $projectMissingFiles === 0;
                                                        $projectFileStatusLabel = $projectFilesComplete
                                                            ? 'Documentación completa'
                                                            : ($projectMissingFiles === 1
                                                                ? 'Falta 1 archivo por subir'
                                                                : 'Faltan '.$projectMissingFiles.' archivos por subir');
                                                    @endphp
                                                    <span
                                                        class="project-completion-status {{ $projectFilesComplete ? 'project-completion-status--complete' : 'project-completion-status--missing' }}"
                                                        data-project-completion-status
                                                        title="{{ $projectFileStatusLabel }}"
                                                        aria-label="{{ $projectFileStatusLabel }}"
                                                    >
                                                        <span class="project-completion-status__count" aria-hidden="true">{{ $projectFilesComplete ? '' : $projectMissingFiles }}</span>
                                                        <span class="project-completion-status__icon" aria-hidden="true">{{ $projectFilesComplete ? '✓' : '!' }}</span>
                                                    </span>
                                                    <span class="project-copy">
                                                        <strong>@if ($project->snip){{ $project->snip }}-@endif{{ $project->name }}</strong>
                                                        <small>{{ $project->place }}</small>
                                                    </span>
                                                </button>
                                            @empty
                                                <p class="empty-folder">Carpeta vacía</p>
                                            @endforelse
                                        </div>
                                    </details>
                                @endforeach
                            </div>
                            <p class="folder-feedback" id="folder-feedback" role="alert"></p>
                        </details>
                        </div>

                        <section
                            class="project-files-panel"
                            id="project-files-panel"
                            aria-labelledby="project-files-title"
                            hidden
                        >
                            <button
                                class="project-files-back"
                                id="project-files-back"
                                type="button"
                            >← Volver a proyectos</button>
                            <div class="project-files-head">
                                <span>Listado del proyecto</span>
                                <h2 id="project-files-title"></h2>
                                <p id="project-files-place"></p>
                            </div>
                            <div class="project-file-list" id="project-file-list"></div>
                            <button
                                class="project-document-add"
                                id="add-project-document"
                                type="button"
                            >Añadir documento</button>
                            <p
                                class="project-files-feedback"
                                id="project-files-feedback"
                                role="alert"
                            ></p>
                            <p class="project-files-note">
                                Un archivo por carpeta · máximo 50 MB.
                            </p>
                        </section>
                    </div>

                    <div class="layer-panel">
                        <p class="layer-panel__title">Uso de capas</p>
                        <div class="layer-options">
                            <label class="layer-option">
                                <input id="projects-layer-toggle" type="checkbox" checked>
                                Proyectos
                            </label>
                            <label class="layer-option">
                                <input id="coverage-layer-toggle" type="checkbox" checked>
                                Cobertura municipal
                            </label>
                            <label class="layer-option">
                                <input id="boundaries-layer-toggle" type="checkbox" checked>
                                Límites y nombres
                            </label>
                            <label class="layer-option">
                                <input id="roads-layer-toggle" type="checkbox" checked>
                                Carreteras
                            </label>
                        </div>
                    </div>
                </aside>

                <div class="map-panel">
                    <div class="map-toolbar">
                        <div class="map-toolbar__copy">
                            <strong>Globo geográfico municipal 3D</strong>
                            <span id="map-selection">Vista satelital HD · límites, nombres y carreteras</span>
                        </div>
                        <button
                            class="map-action"
                            id="map-home"
                            type="button"
                            title="Volver a Coatepeque"
                            aria-label="Volver a la vista general de Coatepeque"
                        >
                            ◎
                        </button>
                        <button
                            class="map-action"
                            id="map-fullscreen"
                            type="button"
                            title="Pantalla completa"
                            aria-label="Mostrar mapa en pantalla completa"
                        >
                            ⛶
                        </button>
                    </div>
                    <div class="globe-navigation" aria-label="Controles del globo">
                        <button
                            class="map-action"
                            id="globe-north"
                            type="button"
                            title="Orientar al norte"
                            aria-label="Orientar el globo al norte"
                        >
                            N
                        </button>
                        <button
                            class="map-action"
                            id="globe-zoom-in"
                            type="button"
                            title="Acercar"
                            aria-label="Acercar el globo"
                        >
                            +
                        </button>
                        <button
                            class="map-action"
                            id="globe-zoom-out"
                            type="button"
                            title="Alejar"
                            aria-label="Alejar el globo"
                        >
                            −
                        </button>
                    </div>
                    <div id="project-map" aria-label="Mapa interactivo de Coatepeque"></div>
                    <details class="map-credits">
                        <summary>Fuentes</summary>
                        <div class="map-credits__content" id="map-credits-content"></div>
                    </details>
                    <div class="map-status" id="map-status">
                        América · Satélite HD con divisiones territoriales
                    </div>
                </div>
            </section>
        </main>

        <section
            class="embedded-module"
            id="project-locations-module"
            data-module-panel="locations"
            aria-label="Ubicación de Proyectos"
            hidden
        >
            <iframe
                id="project-locations-frame"
                title="Ubicación de Proyectos"
                data-src="{{ route('project-locations', ['embed' => 1]) }}"
                allow="geolocation"
            ></iframe>
        </section>

        <section
            class="embedded-module"
            id="project-monitoring-module"
            data-module-panel="monitoring"
            aria-label="Monitoreo"
            hidden
        >
            <iframe
                id="project-monitoring-frame"
                title="Monitoreo"
                data-src="{{ route('project-monitoring.index', ['embed' => 1]) }}"
            ></iframe>
        </section>

        <section
            class="embedded-module"
            id="project-finance-module"
            data-module-panel="finance"
            aria-label="Control financiero"
            hidden
        >
            <iframe
                id="project-finance-frame"
                title="Control financiero"
                data-src="{{ route('project-finance.index', ['embed' => 1]) }}"
            ></iframe>
        </section>

        @if (mb_strtolower(auth()->user()->email) === mb_strtolower(config('security.user_management_admin_email')))
            <section
                class="embedded-module"
                id="user-management-module"
                data-module-panel="users"
                aria-label="Administración de usuarios"
                hidden
            >
                <iframe
                    id="user-management-frame"
                    title="Administración de usuarios"
                    data-src="{{ route('user-management.index', ['embed' => 1]) }}"
                ></iframe>
            </section>
        @endif

        <div
            class="folder-context-menu"
            id="folder-context-menu"
            role="menu"
            aria-label="Opciones de carpeta"
            hidden
        >
            <button type="button" role="menuitem" data-folder-action="rename">
                <span class="folder-context-menu__icon" aria-hidden="true">✎</span>
                Cambiar nombre
            </button>
            <button type="button" role="menuitem" data-folder-action="delete">
                <span class="folder-context-menu__icon" aria-hidden="true">×</span>
                Eliminar
            </button>
        </div>

        <div
            class="folder-context-menu"
            id="project-context-menu"
            role="menu"
            aria-label="Opciones de proyecto"
            hidden
        >
            <button type="button" role="menuitem" data-project-action="edit">
                <span class="folder-context-menu__icon" aria-hidden="true">✎</span>
                Editar proyecto
            </button>
            <button type="button" role="menuitem" data-project-action="delete">
                <span class="folder-context-menu__icon" aria-hidden="true">×</span>
                Eliminar proyecto
            </button>
        </div>

        <div
            class="folder-context-menu"
            id="project-document-context-menu"
            role="menu"
            aria-label="Opciones del documento"
            hidden
        >
            <button
                type="button"
                role="menuitem"
                data-project-document-action="delete"
            >
                <span class="folder-context-menu__icon" aria-hidden="true">×</span>
                Eliminar documento
            </button>
        </div>

        <dialog class="folder-dialog" id="delete-folder-dialog">
            <form class="folder-dialog__form" id="delete-folder-form" novalidate>
                <div class="folder-dialog__head">
                    <div>
                        <h2>Eliminar carpeta</h2>
                        <p class="folder-dialog__copy">
                            Se eliminará “<strong id="delete-folder-name"></strong>”.
                            También se eliminarán sus proyectos. Esta acción no se puede deshacer.
                        </p>
                    </div>
                    <button
                        class="folder-dialog__close"
                        type="button"
                        data-close-dialog="delete-folder-dialog"
                        aria-label="Cerrar"
                    >×</button>
                </div>
                <label class="folder-dialog__label">
                    Código de seguridad
                    <input
                        class="folder-dialog__input"
                        id="delete-folder-code"
                        name="code"
                        type="password"
                        inputmode="numeric"
                        pattern="[0-9]{8}"
                        maxlength="8"
                        autocomplete="off"
                        data-lpignore="true"
                        data-1p-ignore="true"
                        required
                    >
                </label>
                <p class="folder-dialog__error" id="delete-folder-error" role="alert"></p>
                <div class="folder-dialog__actions">
                    <button
                        class="folder-dialog__button"
                        type="button"
                        data-close-dialog="delete-folder-dialog"
                    >Cancelar</button>
                    <button
                        class="folder-dialog__button folder-dialog__button--danger"
                        id="confirm-folder-delete"
                        type="submit"
                    >Eliminar carpeta</button>
                </div>
            </form>
        </dialog>

        <dialog class="folder-dialog" id="delete-project-dialog">
            <form class="folder-dialog__form" id="delete-project-form" novalidate>
                <div class="folder-dialog__head">
                    <div>
                        <h2>Eliminar proyecto</h2>
                        <p class="folder-dialog__copy">
                            Se eliminará únicamente
                            “<strong id="delete-project-name"></strong>”.
                            La carpeta y los demás proyectos no se modificarán.
                        </p>
                    </div>
                    <button
                        class="folder-dialog__close"
                        type="button"
                        data-close-dialog="delete-project-dialog"
                        aria-label="Cerrar"
                    >×</button>
                </div>
                <label class="folder-dialog__label">
                    Código de seguridad
                    <input
                        class="folder-dialog__input"
                        id="delete-project-code"
                        name="code"
                        type="password"
                        inputmode="numeric"
                        pattern="[0-9]{8}"
                        maxlength="8"
                        autocomplete="off"
                        data-lpignore="true"
                        data-1p-ignore="true"
                        required
                    >
                </label>
                <p class="folder-dialog__error" id="delete-project-error" role="alert"></p>
                <div class="folder-dialog__actions">
                    <button
                        class="folder-dialog__button"
                        type="button"
                        data-close-dialog="delete-project-dialog"
                    >Cancelar</button>
                    <button
                        class="folder-dialog__button folder-dialog__button--danger"
                        id="confirm-project-delete"
                        type="submit"
                    >Eliminar proyecto</button>
                </div>
            </form>
        </dialog>

        <dialog class="folder-dialog" id="create-project-document-dialog">
            <form
                class="folder-dialog__form"
                id="create-project-document-form"
                novalidate
            >
                <div class="folder-dialog__head">
                    <div>
                        <h2>Añadir documento</h2>
                        <p>Escribe el nombre que tendrá el nuevo espacio para subir su archivo.</p>
                    </div>
                    <button
                        class="folder-dialog__close"
                        type="button"
                        data-close-dialog="create-project-document-dialog"
                        aria-label="Cerrar"
                    >×</button>
                </div>
                <label class="folder-dialog__label">
                    Nombre del documento
                    <input
                        class="folder-dialog__input"
                        id="project-document-name"
                        name="name"
                        type="text"
                        maxlength="100"
                        autocomplete="off"
                        required
                    >
                </label>
                <p
                    class="folder-dialog__error"
                    id="create-project-document-error"
                    role="alert"
                ></p>
                <div class="folder-dialog__actions">
                    <button
                        class="folder-dialog__button"
                        type="button"
                        data-close-dialog="create-project-document-dialog"
                    >Cancelar</button>
                    <button
                        class="folder-dialog__button folder-dialog__button--primary"
                        id="confirm-project-document-create"
                        type="submit"
                    >Guardar</button>
                </div>
            </form>
        </dialog>

        <dialog class="folder-dialog" id="delete-project-document-dialog">
            <form
                class="folder-dialog__form"
                id="delete-project-document-form"
                novalidate
            >
                <div class="folder-dialog__head">
                    <div>
                        <h2>Eliminar documento</h2>
                        <p class="folder-dialog__copy">
                            Se eliminará “<strong id="delete-project-document-name"></strong>”.
                            Si contiene un archivo, también se eliminará. Esta acción no se puede deshacer.
                        </p>
                    </div>
                    <button
                        class="folder-dialog__close"
                        type="button"
                        data-close-dialog="delete-project-document-dialog"
                        aria-label="Cerrar"
                    >×</button>
                </div>
                <label class="folder-dialog__label">
                    Código de seguridad
                    <input
                        class="folder-dialog__input"
                        id="delete-project-document-code"
                        name="code"
                        type="password"
                        inputmode="numeric"
                        pattern="[0-9]{8}"
                        maxlength="8"
                        autocomplete="off"
                        data-lpignore="true"
                        data-1p-ignore="true"
                        required
                    >
                </label>
                <p
                    class="folder-dialog__error"
                    id="delete-project-document-error"
                    role="alert"
                ></p>
                <div class="folder-dialog__actions">
                    <button
                        class="folder-dialog__button"
                        type="button"
                        data-close-dialog="delete-project-document-dialog"
                    >Cancelar</button>
                    <button
                        class="folder-dialog__button folder-dialog__button--danger"
                        id="confirm-project-document-delete"
                        type="submit"
                    >Eliminar documento</button>
                </div>
            </form>
        </dialog>

        <dialog class="folder-dialog" id="create-project-link-dialog">
            <form class="folder-dialog__form" id="create-project-link-form" novalidate>
                <div class="folder-dialog__head">
                    <div>
                        <h2>Guardar link de Google Drive</h2>
                        <p class="folder-dialog__copy">
                            Úsalo para un documento mayor a 50 MB. Asegúrate de que
                            el enlace tenga permiso para que pueda verlo quien lo necesite.
                        </p>
                    </div>
                    <button
                        class="folder-dialog__close"
                        type="button"
                        data-close-dialog="create-project-link-dialog"
                        aria-label="Cerrar"
                    >×</button>
                </div>
                <label class="folder-dialog__label">
                    Link de Google Drive
                    <input
                        class="folder-dialog__input"
                        id="project-link-url"
                        name="url"
                        type="url"
                        maxlength="2048"
                        autocomplete="off"
                        placeholder="https://drive.google.com/file/d/..."
                        required
                    >
                </label>
                <p
                    class="folder-dialog__error"
                    id="create-project-link-error"
                    role="alert"
                ></p>
                <div class="folder-dialog__actions">
                    <button
                        class="folder-dialog__button"
                        type="button"
                        data-close-dialog="create-project-link-dialog"
                    >Cancelar</button>
                    <button
                        class="folder-dialog__button folder-dialog__button--primary"
                        id="confirm-project-link-create"
                        type="submit"
                    >Guardar link</button>
                </div>
            </form>
        </dialog>

        <dialog class="folder-dialog" id="delete-project-file-dialog">
            <form class="folder-dialog__form" id="delete-project-file-form" novalidate>
                <div class="folder-dialog__head">
                    <div>
                        <h2>Eliminar documento</h2>
                        <p class="folder-dialog__copy">
                            Se eliminará “<strong id="delete-project-file-name"></strong>”.
                            Esta acción no se puede deshacer.
                        </p>
                    </div>
                    <button
                        class="folder-dialog__close"
                        type="button"
                        data-close-dialog="delete-project-file-dialog"
                        aria-label="Cerrar"
                    >×</button>
                </div>
                <label class="folder-dialog__label">
                    Código de seguridad
                    <input
                        class="folder-dialog__input"
                        id="delete-project-file-code"
                        name="code"
                        type="password"
                        inputmode="numeric"
                        pattern="[0-9]{8}"
                        maxlength="8"
                        autocomplete="off"
                        data-lpignore="true"
                        data-1p-ignore="true"
                        required
                    >
                </label>
                <p
                    class="folder-dialog__error"
                    id="delete-project-file-error"
                    role="alert"
                ></p>
                <div class="folder-dialog__actions">
                    <button
                        class="folder-dialog__button"
                        type="button"
                        data-close-dialog="delete-project-file-dialog"
                    >Cancelar</button>
                    <button
                        class="folder-dialog__button folder-dialog__button--danger"
                        id="confirm-project-file-delete"
                        type="submit"
                    >Eliminar documento</button>
                </div>
            </form>
        </dialog>

        <dialog class="folder-dialog" id="create-project-dialog">
            <form class="folder-dialog__form" id="create-project-form" novalidate>
                <div class="folder-dialog__head">
                    <div>
                        <h2 id="project-dialog-title">Crear proyecto</h2>
                        <p id="project-dialog-copy"></p>
                    </div>
                    <button
                        class="folder-dialog__close"
                        type="button"
                        data-close-dialog="create-project-dialog"
                        aria-label="Cerrar"
                    >×</button>
                </div>
                <label class="folder-dialog__label">
                    Nombre del proyecto
                    <input
                        class="folder-dialog__input"
                        id="project-name"
                        name="name"
                        type="text"
                        maxlength="150"
                        autocomplete="off"
                        required
                    >
                </label>
                <label class="folder-dialog__label">
                    SNIP
                    <input
                        class="folder-dialog__input"
                        id="project-snip"
                        name="snip"
                        type="text"
                        inputmode="numeric"
                        pattern="[0-9]{1,6}"
                        maxlength="6"
                        autocomplete="off"
                        placeholder="Máximo 6 dígitos"
                        required
                    >
                </label>
                <label class="folder-dialog__label">
                    Lugar o sector
                    <input
                        class="folder-dialog__input"
                        id="project-place"
                        name="place"
                        type="text"
                        maxlength="100"
                        autocomplete="off"
                        placeholder="Ejemplo: Barrio La Esperanza"
                    >
                </label>
                <div class="coordinate-fields">
                    <label class="folder-dialog__label">
                        Latitud
                        <input
                            class="folder-dialog__input"
                            id="project-latitude"
                            name="latitude"
                            type="text"
                            inputmode="decimal"
                            maxlength="20"
                            autocomplete="off"
                            spellcheck="false"
                            placeholder="Ejemplo: 14°42'25.49&quot;N"
                            required
                        >
                    </label>
                    <label class="folder-dialog__label">
                        Longitud
                        <input
                            class="folder-dialog__input"
                            id="project-longitude"
                            name="longitude"
                            type="text"
                            inputmode="decimal"
                            maxlength="20"
                            autocomplete="off"
                            spellcheck="false"
                            placeholder="Ejemplo: 91°59'43.17&quot;O"
                            required
                        >
                    </label>
                </div>
                <label class="coordinate-fallback">
                    <input id="project-no-coordinates" type="checkbox">
                    No tengo coordenadas
                </label>
                <p class="folder-dialog__error" id="create-project-error" role="alert"></p>
                <div class="folder-dialog__actions">
                    <button
                        class="folder-dialog__button"
                        type="button"
                        data-close-dialog="create-project-dialog"
                    >Cancelar</button>
                    <button
                        class="folder-dialog__button folder-dialog__button--primary"
                        id="confirm-project-create"
                        type="submit"
                    >Crear proyecto</button>
                </div>
            </form>
        </dialog>

        <script src="https://cdn.jsdelivr.net/npm/cesium@1.143.0/Build/Cesium/Cesium.js"></script>
        <script src="https://unpkg.com/@esri/arcgis-rest-request@4/dist/bundled/request.umd.js"></script>
        <script src="https://unpkg.com/@esri/arcgis-rest-basemap-sessions@1/dist/bundled/basemap-sessions.umd.js"></script>

        <script>
            (async () => {
                const activityUrl = document.body.dataset.sessionActivityUrl;
                const projectFoldersUrl =
                    document.body.dataset.projectFoldersUrl;
                const projectsUrl = document.body.dataset.projectsUrl;
                const loginUrl = document.body.dataset.loginUrl;
                const logoutUrl = document.body.dataset.logoutUrl;
                const historySessionKey = 'protected-session-closed';
                const tabAuthorizationKey = 'protected-tab-authorized';
                const reloadAuthorizationKey = 'protected-reload-pending';
                const internalNavigationKey = 'protected-internal-navigation';
                const logoutForm = document.querySelector('.logout-form');
                const idleTimeout = Number(
                    document.body.dataset.idleTimeoutMs,
                );
                const csrfToken = document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute('content');
                const pingInterval = 30_000;
                const activityThrottle = 1_000;
                let lastActivity = Date.now();
                let lastRecordedActivity = 0;
                let lastPing = 0;
                let expirationTimer;
                let pingInProgress = false;
                let explicitLogoutStarted = false;

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

                if (!window.__protectedTabAuthorized) {
                    markSessionClosed();

                    try {
                        await fetch(logoutUrl, logoutRequestOptions);
                    } catch {
                        // La redirección local continúa aunque la red falle.
                    } finally {
                        window.location.replace(loginUrl);
                    }

                    return;
                }

                document.documentElement.style.visibility = '';

                const moduleLinks = Array.from(
                    document.querySelectorAll('[data-module-target]'),
                );
                const modulePanels = Array.from(
                    document.querySelectorAll('[data-module-panel]'),
                );
                const projectLocationsFrame = document.getElementById(
                    'project-locations-frame',
                );
                const projectMonitoringFrame = document.getElementById(
                    'project-monitoring-frame',
                );
                const projectFinanceFrame = document.getElementById(
                    'project-finance-frame',
                );
                const userManagementFrame = document.getElementById(
                    'user-management-frame',
                );
                const moduleFrames = new Map([
                    ['locations', projectLocationsFrame],
                    ['monitoring', projectMonitoringFrame],
                    ['finance', projectFinanceFrame],
                    ['users', userManagementFrame],
                ].filter(([, moduleFrame]) => moduleFrame));
                const themeToggle = document.getElementById('theme-toggle');
                const themeStorageKey = 'municipal_portal_theme';

                const sendThemeToModules = (theme) => {
                    moduleFrames.forEach((moduleFrame) => {
                        if (moduleFrame.hasAttribute('src')) {
                            moduleFrame.contentWindow?.postMessage(
                                { type: 'theme-changed', theme },
                                window.location.origin,
                            );
                        }
                    });
                };

                const applyTheme = (theme, persist = false) => {
                    const isLight = theme === 'light';
                    const resolvedTheme = isLight ? 'light' : 'dark';

                    document.documentElement.classList.toggle(
                        'light-mode',
                        isLight,
                    );
                    window.__municipalPortalTheme = resolvedTheme;
                    themeToggle.setAttribute(
                        'aria-pressed',
                        isLight ? 'true' : 'false',
                    );
                    themeToggle.setAttribute(
                        'aria-label',
                        isLight
                            ? 'Cambiar a modo oscuro'
                            : 'Cambiar a modo claro',
                    );
                    themeToggle.title = isLight
                        ? 'Cambiar a modo oscuro'
                        : 'Cambiar a modo claro';

                    if (persist) {
                        try {
                            localStorage.setItem(
                                themeStorageKey,
                                resolvedTheme,
                            );
                        } catch (error) {
                            // El tema sigue funcionando aunque no pueda persistirse.
                        }
                    }

                    sendThemeToModules(resolvedTheme);
                };

                applyTheme(window.__municipalPortalTheme);
                themeToggle.addEventListener('click', () => {
                    applyTheme(
                        document.documentElement.classList.contains(
                            'light-mode',
                        )
                            ? 'dark'
                            : 'light',
                        true,
                    );
                });

                const notifyVisibleModule = (moduleName) => {
                    const moduleFrame = moduleFrames.get(moduleName);

                    if (moduleFrame) {
                        if (!moduleFrame.hasAttribute('src')) {
                            moduleFrame.setAttribute(
                                'src',
                                moduleFrame.dataset.src,
                            );
                        } else {
                            moduleFrame.contentWindow?.postMessage(
                                { type: 'module-shown' },
                                window.location.origin,
                            );
                        }

                        return;
                    }

                    window.dispatchEvent(new Event('home-module-shown'));
                };

                const showModule = (moduleName) => {
                    modulePanels.forEach((panel) => {
                        panel.hidden = panel.dataset.modulePanel !== moduleName;
                    });
                    moduleLinks.forEach((link) => {
                        const active = link.dataset.moduleTarget === moduleName;

                        link.classList.toggle('is-active', active);

                        if (active) {
                            link.setAttribute('aria-current', 'page');
                        } else {
                            link.removeAttribute('aria-current');
                        }
                    });

                    notifyVisibleModule(moduleName);
                };

                moduleLinks.forEach((link) => {
                    link.addEventListener('click', (event) => {
                        event.preventDefault();
                        showModule(link.dataset.moduleTarget);
                    });
                });

                moduleFrames.forEach((moduleFrame) => {
                    moduleFrame.addEventListener('load', () => {
                        moduleFrame.contentWindow?.postMessage(
                            {
                                type: 'theme-changed',
                                theme: window.__municipalPortalTheme,
                            },
                            window.location.origin,
                        );

                        if (!moduleFrame.closest('[hidden]')) {
                            moduleFrame.contentWindow?.postMessage(
                                { type: 'module-shown' },
                                window.location.origin,
                            );
                        }
                    });
                });

                document.querySelectorAll('[data-protected-navigation]')
                    .forEach((link) => {
                        link.addEventListener('click', () => {
                            sessionStorage.setItem(
                                internalNavigationKey,
                                'true',
                            );
                        });
                    });

                const folderList = document.getElementById('project-folders');
                const projectsTreeView = document.getElementById(
                    'projects-tree-view',
                );
                const projectFilesPanel = document.getElementById(
                    'project-files-panel',
                );
                const projectFilesBack = document.getElementById(
                    'project-files-back',
                );
                const projectFilesTitle = document.getElementById(
                    'project-files-title',
                );
                const projectFilesPlace = document.getElementById(
                    'project-files-place',
                );
                const projectFileList = document.getElementById(
                    'project-file-list',
                );
                const projectFilesFeedback = document.getElementById(
                    'project-files-feedback',
                );
                const createProjectLinkDialog = document.getElementById(
                    'create-project-link-dialog',
                );
                const createProjectLinkForm = document.getElementById(
                    'create-project-link-form',
                );
                const projectLinkUrlInput = document.getElementById(
                    'project-link-url',
                );
                const createProjectLinkError = document.getElementById(
                    'create-project-link-error',
                );
                const createProjectLinkSubmit = document.getElementById(
                    'confirm-project-link-create',
                );
                const addProjectDocumentButton = document.getElementById(
                    'add-project-document',
                );
                const createProjectDocumentDialog = document.getElementById(
                    'create-project-document-dialog',
                );
                const createProjectDocumentForm = document.getElementById(
                    'create-project-document-form',
                );
                const projectDocumentNameInput = document.getElementById(
                    'project-document-name',
                );
                const createProjectDocumentError = document.getElementById(
                    'create-project-document-error',
                );
                const createProjectDocumentSubmit = document.getElementById(
                    'confirm-project-document-create',
                );
                const addFolderButton = document.getElementById(
                    'add-project-folder',
                );
                const folderFeedback = document.getElementById(
                    'folder-feedback',
                );
                const projectDialog = document.getElementById(
                    'create-project-dialog',
                );
                const projectForm = document.getElementById(
                    'create-project-form',
                );
                const projectDialogTitle = document.getElementById(
                    'project-dialog-title',
                );
                const projectDialogCopy = document.getElementById(
                    'project-dialog-copy',
                );
                const projectNameInput = document.getElementById('project-name');
                const projectSnipInput = document.getElementById('project-snip');
                const projectPlaceInput = document.getElementById('project-place');
                const projectLatitudeInput = document.getElementById(
                    'project-latitude',
                );
                const projectLongitudeInput = document.getElementById(
                    'project-longitude',
                );
                const projectNoCoordinates = document.getElementById(
                    'project-no-coordinates',
                );
                const projectCreateError = document.getElementById(
                    'create-project-error',
                );
                const projectCreateSubmit = document.getElementById(
                    'confirm-project-create',
                );
                let folderDraft = null;
                let selectedProjectFolder = null;
                let selectedProjectItem = null;
                let editingProjectId = null;
                const coatepequeProjectCenter = [14.7000, -91.8667];
                const latitudeExample = 'Ejemplo: 14°42\'25.49"N';
                const longitudeExample = 'Ejemplo: 91°59\'43.17"O';

                const syncCoordinateFallback = () => {
                    const useCoatepequeCenter = projectNoCoordinates.checked;

                    projectLatitudeInput.disabled = useCoatepequeCenter;
                    projectLongitudeInput.disabled = useCoatepequeCenter;
                    projectLatitudeInput.required = !useCoatepequeCenter;
                    projectLongitudeInput.required = !useCoatepequeCenter;

                    if (useCoatepequeCenter) {
                        projectLatitudeInput.value = '';
                        projectLongitudeInput.value = '';
                        projectLatitudeInput.placeholder =
                            'Centro de Coatepeque';
                        projectLongitudeInput.placeholder =
                            'Centro de Coatepeque';

                        return;
                    }

                    projectLatitudeInput.placeholder = latitudeExample;
                    projectLongitudeInput.placeholder = longitudeExample;
                };

                projectNoCoordinates.addEventListener(
                    'change',
                    syncCoordinateFallback,
                );

                projectSnipInput.addEventListener('input', () => {
                    projectSnipInput.value = projectSnipInput.value
                        .replace(/\D/g, '')
                        .slice(0, 6);
                });

                const folderToOpen = sessionStorage.getItem(
                    'project-folder-to-open',
                );

                if (folderToOpen) {
                    const savedFolder = Array.from(
                        folderList.querySelectorAll('[data-folder-id]'),
                    ).find((folder) => folder.dataset.folderId === folderToOpen);

                    if (savedFolder) {
                        savedFolder.open = true;
                    }

                    sessionStorage.removeItem('project-folder-to-open');
                }

                const showFolderFeedback = (message = '') => {
                    folderFeedback.textContent = message;
                };

                const nextFolderName = () => {
                    const names = new Set(
                        Array.from(
                            folderList.querySelectorAll('[data-folder-name]'),
                        ).map((folder) =>
                            folder.dataset.folderName.toLocaleLowerCase('es'),
                        ),
                    );
                    let candidate = 'Nueva carpeta';
                    let number = 2;

                    while (names.has(candidate.toLocaleLowerCase('es'))) {
                        candidate = `Nueva carpeta (${number})`;
                        number += 1;
                    }

                    return candidate;
                };

                const createProjectAddButton = (folderName) => {
                    const button = document.createElement('button');

                    button.className = 'folder-add';
                    button.type = 'button';
                    button.dataset.addProject = '';
                    button.title = `Crear proyecto en ${folderName}`;
                    button.setAttribute(
                        'aria-label',
                        `Crear proyecto en ${folderName}`,
                    );
                    button.textContent = '+';

                    return button;
                };

                const minimizeOtherProjectFolders = (openedFolder) => {
                    Array.from(folderList.children).forEach((folder) => {
                        if (
                            folder !== openedFolder
                            && folder.classList.contains('user-folder')
                        ) {
                            folder.open = false;
                        }
                    });
                };

                folderList.addEventListener('toggle', (event) => {
                    const openedFolder = event.target;

                    if (
                        !openedFolder.open
                        || !openedFolder.classList.contains('user-folder')
                        || openedFolder.parentElement !== folderList
                    ) {
                        return;
                    }

                    minimizeOtherProjectFolders(openedFolder);
                }, true);

                const beginFolderCreation = () => {
                    if (folderDraft) {
                        const currentInput = folderDraft.querySelector('input');
                        currentInput.focus();
                        currentInput.select();

                        return;
                    }

                    showFolderFeedback();

                    const folder = document.createElement('details');
                    const summary = document.createElement('summary');
                    const icon = document.createElement('span');
                    const input = document.createElement('input');
                    const children = document.createElement('div');
                    const emptyMessage = document.createElement('p');
                    let saving = false;

                    folder.className = 'tree-folder user-folder';
                    icon.className = 'folder-icon';
                    icon.setAttribute('aria-hidden', 'true');
                    input.className = 'folder-name-input';
                    input.type = 'text';
                    input.maxLength = 80;
                    input.value = nextFolderName();
                    input.setAttribute('aria-label', 'Nombre de la carpeta nueva');
                    children.className = 'tree-children';
                    emptyMessage.className = 'empty-folder';
                    emptyMessage.textContent = 'Carpeta vacía';

                    summary.append(icon, input);
                    children.append(emptyMessage);
                    folder.append(summary, children);
                    folderList.append(folder);
                    folder.open = true;
                    folderDraft = folder;

                    const cancel = () => {
                        if (folderDraft !== folder) {
                            return;
                        }

                        folderDraft = null;
                        folder.remove();
                        showFolderFeedback();
                        addFolderButton.focus();
                    };

                    const save = async () => {
                        if (saving || folderDraft !== folder) {
                            return;
                        }

                        const name = input.value.trim();

                        if (name === '') {
                            showFolderFeedback(
                                'Escribe un nombre para la carpeta.',
                            );
                            input.focus();

                            return;
                        }

                        saving = true;
                        input.disabled = true;

                        try {
                            const response = await fetch(projectFoldersUrl, {
                                method: 'POST',
                                headers: {
                                    Accept: 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                },
                                body: JSON.stringify({ name }),
                            });
                            const payload = await response.json();

                            if (!response.ok) {
                                throw new Error(
                                    payload.errors?.name?.[0]
                                    ?? payload.message
                                    ?? 'No se pudo crear la carpeta.',
                                );
                            }

                            const nameElement = document.createElement('span');
                            nameElement.className = 'folder-name';
                            nameElement.textContent = payload.folder.name;
                            folder.dataset.folderId = payload.folder.id;
                            folder.dataset.folderName = payload.folder.name;
                            folder.dataset.folderColor = payload.folder.color;
                            input.replaceWith(nameElement);
                            summary.append(
                                createProjectAddButton(payload.folder.name),
                            );
                            folderDraft = null;
                            showFolderFeedback();
                        } catch (error) {
                            saving = false;
                            input.disabled = false;
                            showFolderFeedback(error.message);
                            input.focus();
                            input.select();
                        }
                    };

                    input.addEventListener('click', (event) => {
                        event.stopPropagation();
                    });
                    input.addEventListener('keydown', (event) => {
                        event.stopPropagation();

                        if (event.key === 'Enter') {
                            event.preventDefault();
                            save();
                        } else if (event.key === 'Escape') {
                            event.preventDefault();
                            cancel();
                        }
                    });
                    input.addEventListener('blur', () => {
                        window.setTimeout(() => {
                            if (folderDraft === folder) {
                                save();
                            }
                        }, 0);
                    });

                    input.focus();
                    input.select();
                };

                addFolderButton.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    beginFolderCreation();
                });

                folderList.addEventListener('click', (event) => {
                    const addProjectButton = event.target.closest(
                        '[data-add-project]',
                    );

                    if (!addProjectButton || !folderList.contains(addProjectButton)) {
                        return;
                    }

                    const folder = addProjectButton.closest(
                        '.user-folder[data-folder-id]',
                    );

                    if (!folder) {
                        return;
                    }

                    event.preventDefault();
                    event.stopPropagation();
                    selectedProjectFolder = folder;
                    selectedProjectItem = null;
                    editingProjectId = null;
                    projectForm.reset();
                    projectNoCoordinates.checked = false;
                    syncCoordinateFallback();
                    projectCreateError.textContent = '';
                    projectDialogTitle.textContent = 'Crear proyecto';
                    projectDialogCopy.textContent =
                        `Se guardará dentro de “${folder.dataset.folderName}”. ` +
                        'Ingresa las coordenadas o marca “No tengo ' +
                        'coordenadas” para usar el centro de Coatepeque.';
                    projectCreateSubmit.textContent = 'Crear proyecto';
                    projectDialog.showModal();
                    projectNameInput.focus();
                });

                const folderContextMenu = document.getElementById(
                    'folder-context-menu',
                );
                const projectContextMenu = document.getElementById(
                    'project-context-menu',
                );
                const projectDocumentContextMenu = document.getElementById(
                    'project-document-context-menu',
                );
                const deleteDialog = document.getElementById(
                    'delete-folder-dialog',
                );
                const deleteForm = document.getElementById('delete-folder-form');
                const deleteFolderName = document.getElementById(
                    'delete-folder-name',
                );
                const deleteFolderCode = document.getElementById(
                    'delete-folder-code',
                );
                const deleteError = document.getElementById(
                    'delete-folder-error',
                );
                const deleteSubmit = document.getElementById(
                    'confirm-folder-delete',
                );
                const deleteProjectDialog = document.getElementById(
                    'delete-project-dialog',
                );
                const deleteProjectForm = document.getElementById(
                    'delete-project-form',
                );
                const deleteProjectName = document.getElementById(
                    'delete-project-name',
                );
                const deleteProjectCode = document.getElementById(
                    'delete-project-code',
                );
                const deleteProjectError = document.getElementById(
                    'delete-project-error',
                );
                const deleteProjectSubmit = document.getElementById(
                    'confirm-project-delete',
                );
                const deleteProjectDocumentDialog = document.getElementById(
                    'delete-project-document-dialog',
                );
                const deleteProjectDocumentForm = document.getElementById(
                    'delete-project-document-form',
                );
                const deleteProjectDocumentName = document.getElementById(
                    'delete-project-document-name',
                );
                const deleteProjectDocumentCode = document.getElementById(
                    'delete-project-document-code',
                );
                const deleteProjectDocumentError = document.getElementById(
                    'delete-project-document-error',
                );
                const deleteProjectDocumentSubmit = document.getElementById(
                    'confirm-project-document-delete',
                );
                const deleteProjectFileDialog = document.getElementById(
                    'delete-project-file-dialog',
                );
                const deleteProjectFileForm = document.getElementById(
                    'delete-project-file-form',
                );
                const deleteProjectFileName = document.getElementById(
                    'delete-project-file-name',
                );
                const deleteProjectFileCode = document.getElementById(
                    'delete-project-file-code',
                );
                const deleteProjectFileError = document.getElementById(
                    'delete-project-file-error',
                );
                const deleteProjectFileSubmit = document.getElementById(
                    'confirm-project-file-delete',
                );
                let selectedFolder = null;
                let folderRenameInput = null;
                let activeProjectFilesId = null;
                let selectedProjectDocumentFolder = null;
                let selectedProjectFile = null;
                let selectedProjectLinkSlot = null;
                let showProjectLocations = () => {};
                let removeProjectLocation = (projectId) => {
                    delete projects[String(projectId)];
                };

                [
                    deleteFolderCode,
                    deleteProjectCode,
                    deleteProjectDocumentCode,
                    deleteProjectFileCode,
                ].forEach(
                    (input) => {
                        input.addEventListener('input', () => {
                            input.value = input.value
                                .replace(/\D/g, '')
                                .slice(0, 8);
                        });
                    },
                );

                const folderEndpoint = (folder) =>
                    `${projectFoldersUrl}/${folder.dataset.folderId}`;

                const projectEndpoint = (projectId) =>
                    `${projectsUrl}/${projectId}`;

                const hideFolderContextMenu = () => {
                    folderContextMenu.hidden = true;
                };

                const hideProjectContextMenu = () => {
                    projectContextMenu.hidden = true;
                };

                const hideProjectDocumentContextMenu = () => {
                    projectDocumentContextMenu.hidden = true;
                };

                const placeContextMenu = (menu, event) => {
                    menu.hidden = false;
                    const menuRect = menu.getBoundingClientRect();
                    const left = Math.min(
                        event.clientX,
                        window.innerWidth - menuRect.width - 8,
                    );
                    const top = Math.min(
                        event.clientY,
                        window.innerHeight - menuRect.height - 8,
                    );

                    menu.style.left = `${Math.max(8, left)}px`;
                    menu.style.top = `${Math.max(8, top)}px`;
                    menu.querySelector('button').focus();
                };

                const responsePayload = async (response) => {
                    const payload = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        const firstError = payload.errors
                            ? Object.values(payload.errors).flat()[0]
                            : null;

                        throw new Error(
                            firstError
                            ?? payload.message
                            ?? 'No se pudo completar la acción.',
                        );
                    }

                    return payload;
                };

                const closeDialog = (dialog) => {
                    if (dialog.open) {
                        dialog.close();
                    }
                };

                const cancelFolderRename = () => {
                    if (!folderRenameInput) {
                        return;
                    }

                    const { folder, input, nameElement, originalName } =
                        folderRenameInput;
                    nameElement.textContent = originalName;
                    input.replaceWith(nameElement);
                    folderRenameInput = null;
                    folder.focus?.();
                };

                const beginFolderRename = (folder) => {
                    cancelFolderRename();

                    const summary = folder.querySelector(':scope > summary');
                    const nameElement = summary.querySelector('.folder-name');
                    const originalName = folder.dataset.folderName;
                    const input = document.createElement('input');
                    let saving = false;

                    input.className = 'folder-name-input';
                    input.type = 'text';
                    input.maxLength = 80;
                    input.value = originalName;
                    input.setAttribute('aria-label', 'Nuevo nombre de la carpeta');
                    nameElement.replaceWith(input);
                    folderRenameInput = {
                        folder,
                        input,
                        nameElement,
                        originalName,
                    };

                    const save = async () => {
                        if (saving || folderRenameInput?.input !== input) {
                            return;
                        }

                        const name = input.value.trim();

                        if (name === originalName) {
                            nameElement.textContent = originalName;
                            input.replaceWith(nameElement);
                            folderRenameInput = null;

                            return;
                        }

                        if (name === '') {
                            showFolderFeedback(
                                'Escribe un nombre para la carpeta.',
                            );
                            input.focus();

                            return;
                        }

                        saving = true;
                        input.disabled = true;
                        showFolderFeedback();

                        try {
                            const response = await fetch(folderEndpoint(folder), {
                                method: 'PATCH',
                                headers: {
                                    Accept: 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                },
                                body: JSON.stringify({ name }),
                            });
                            const payload = await responsePayload(response);

                            nameElement.textContent = payload.folder.name;
                            folder.dataset.folderName = payload.folder.name;
                            const addProjectButton = summary.querySelector(
                                '[data-add-project]',
                            );

                            if (addProjectButton) {
                                addProjectButton.title =
                                    `Crear proyecto en ${payload.folder.name}`;
                                addProjectButton.setAttribute(
                                    'aria-label',
                                    `Crear proyecto en ${payload.folder.name}`,
                                );
                            }
                            input.replaceWith(nameElement);
                            folderRenameInput = null;
                        } catch (error) {
                            saving = false;
                            input.disabled = false;
                            showFolderFeedback(error.message);
                            input.focus();
                            input.select();
                        }
                    };

                    input.addEventListener('click', (event) => {
                        event.stopPropagation();
                    });
                    input.addEventListener('keydown', (event) => {
                        event.stopPropagation();

                        if (event.key === 'Enter') {
                            event.preventDefault();
                            save();
                        } else if (event.key === 'Escape') {
                            event.preventDefault();
                            cancelFolderRename();
                            showFolderFeedback();
                        }
                    });
                    input.addEventListener('blur', () => {
                        window.setTimeout(() => {
                            if (folderRenameInput?.input === input) {
                                save();
                            }
                        }, 0);
                    });

                    input.focus();
                    input.select();
                };

                folderList.addEventListener('contextmenu', (event) => {
                    if (event.target.closest('.project-item')) {
                        return;
                    }

                    const folder = event.target.closest(
                        '.user-folder[data-folder-id]',
                    );

                    if (!folder || !folderList.contains(folder)) {
                        return;
                    }

                    event.preventDefault();
                    event.stopPropagation();
                    hideProjectContextMenu();
                    selectedFolder = folder;
                    placeContextMenu(folderContextMenu, event);
                });

                folderContextMenu.addEventListener('click', (event) => {
                    const actionButton = event.target.closest(
                        '[data-folder-action]',
                    );

                    if (!actionButton || !selectedFolder) {
                        return;
                    }

                    hideFolderContextMenu();

                    if (actionButton.dataset.folderAction === 'rename') {
                        beginFolderRename(selectedFolder);
                    } else {
                        deleteForm.reset();
                        deleteFolderName.textContent =
                            selectedFolder.dataset.folderName;
                        deleteError.textContent = '';
                        deleteDialog.showModal();
                        deleteFolderCode.focus();
                    }
                });

                deleteForm.addEventListener('submit', async (event) => {
                    event.preventDefault();

                    if (!selectedFolder || deleteSubmit.disabled) {
                        return;
                    }

                    const folder = selectedFolder;
                    const projectIds = Array.from(
                        folder.querySelectorAll('.project-item[data-project]'),
                    ).map((projectItem) => projectItem.dataset.project);
                    deleteSubmit.disabled = true;
                    deleteError.textContent = '';

                    try {
                        const response = await fetch(folderEndpoint(folder), {
                            method: 'DELETE',
                            headers: {
                                Accept: 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({
                                code: deleteFolderCode.value,
                            }),
                        });

                        await responsePayload(response);
                        projectIds.forEach((projectId) => {
                            removeProjectLocation(projectId);
                        });
                        closeDialog(deleteDialog);
                        folder.remove();
                        selectedFolder = null;
                        showFolderFeedback();
                    } catch (error) {
                        deleteError.textContent = error.message;
                        deleteFolderCode.focus();
                        deleteFolderCode.select();
                    } finally {
                        deleteSubmit.disabled = false;
                    }
                });

                document.querySelectorAll('[data-close-dialog]').forEach(
                    (button) => {
                        button.addEventListener('click', () => {
                            closeDialog(
                                document.getElementById(
                                    button.dataset.closeDialog,
                                ),
                            );
                        });
                    },
                );

                document.addEventListener('click', (event) => {
                    if (!folderContextMenu.contains(event.target)) {
                        hideFolderContextMenu();
                    }

                    if (!projectContextMenu.contains(event.target)) {
                        hideProjectContextMenu();
                    }

                    if (!projectDocumentContextMenu.contains(event.target)) {
                        hideProjectDocumentContextMenu();
                    }
                });
                window.addEventListener('blur', () => {
                    hideFolderContextMenu();
                    hideProjectContextMenu();
                    hideProjectDocumentContextMenu();
                });
                window.addEventListener('resize', () => {
                    hideFolderContextMenu();
                    hideProjectContextMenu();
                    hideProjectDocumentContextMenu();
                });
                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        hideFolderContextMenu();
                        hideProjectContextMenu();
                        hideProjectDocumentContextMenu();
                    }
                });

                const projects = {{ Illuminate\Support\Js::from($mapProjects) }};

                const formatFileSize = (bytes) => {
                    if (bytes < 1024) {
                        return `${bytes} B`;
                    }

                    if (bytes < 1024 * 1024) {
                        return `${(bytes / 1024).toFixed(1)} KB`;
                    }

                    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
                };

                const showProjectFilesFeedback = (
                    message = '',
                    success = false,
                ) => {
                    projectFilesFeedback.textContent = message;
                    projectFilesFeedback.classList.toggle(
                        'is-success',
                        success,
                    );
                };

                const createProjectFileAction = (
                    label,
                    className = '',
                ) => {
                    const element = document.createElement('button');

                    element.className = `project-file-action ${className}`.trim();
                    element.type = 'button';
                    element.textContent = label;

                    return element;
                };

                const updateProjectCompletionStatus = (projectId) => {
                    const project = projects[projectId];
                    const projectItem = folderList.querySelector(
                        `.project-item[data-project="${projectId}"]`,
                    );
                    const status = projectItem?.querySelector(
                        '[data-project-completion-status]',
                    );
                    const count = status?.querySelector(
                        '.project-completion-status__count',
                    );
                    const icon = status?.querySelector(
                        '.project-completion-status__icon',
                    );

                    if (!project || !status || !count || !icon) {
                        return;
                    }

                    const occupiedSlots = new Set(
                        (project.files ?? []).map((file) => Number(file.slot)),
                    );
                    const missingFiles = (project.document_folders ?? [])
                        .filter(
                            (documentFolder) => !occupiedSlots.has(
                                Number(documentFolder.position),
                            ),
                        ).length;
                    const complete = missingFiles === 0;
                    const label = complete
                        ? 'Documentación completa'
                        : missingFiles === 1
                          ? 'Falta 1 archivo por subir'
                          : `Faltan ${missingFiles} archivos por subir`;

                    status.classList.toggle(
                        'project-completion-status--complete',
                        complete,
                    );
                    status.classList.toggle(
                        'project-completion-status--missing',
                        !complete,
                    );
                    count.textContent = complete ? '' : String(missingFiles);
                    icon.textContent = complete ? '✓' : '!';
                    status.title = label;
                    status.setAttribute('aria-label', label);
                };

                const renderProjectFiles = (openSlot = null) => {
                    const project = projects[activeProjectFilesId];

                    if (!project) {
                        return;
                    }

                    projectFileList.replaceChildren();
                    project.files ??= [];
                    project.document_folders ??= [];
                    updateProjectCompletionStatus(activeProjectFilesId);

                    const documentFolders = [...project.document_folders]
                        .sort(
                            (first, second) =>
                                Number(first.position) - Number(second.position),
                        );

                    for (const documentFolder of documentFolders) {
                        const slot = Number(documentFolder.position);
                        const storedFile = project.files.find(
                            (file) => Number(file.slot) === slot,
                        );
                        const row = document.createElement('article');
                        const head = document.createElement('button');
                        const folderIcon = document.createElement('span');
                        const title = document.createElement('strong');
                        const status = document.createElement('span');
                        const body = document.createElement('div');
                        const actions = document.createElement('div');

                        row.className = 'project-file-slot';
                        row.dataset.projectFileSlot = String(slot);
                        row.dataset.projectDocumentFolder = String(
                            documentFolder.id,
                        );
                        head.className = 'project-file-slot__head';
                        head.type = 'button';
                        folderIcon.className = 'project-file-slot__folder';
                        folderIcon.setAttribute('aria-hidden', 'true');
                        title.textContent = documentFolder.name;
                        status.className = storedFile
                            ? 'project-file-slot__status project-file-slot__status--complete'
                            : 'project-file-slot__status project-file-slot__status--missing';
                        status.textContent = storedFile ? '✓' : '!';
                        status.title = storedFile
                            ? 'Archivo cargado'
                            : 'Archivo faltante';
                        status.setAttribute('aria-label', status.title);
                        body.className = 'project-file-slot__body';
                        body.id =
                            `project-file-body-${activeProjectFilesId}-${slot}`;
                        actions.className = 'project-file-slot__actions';
                        head.append(folderIcon, title, status);
                        body.hidden = Number(openSlot) !== slot;
                        row.classList.toggle('is-open', !body.hidden);
                        head.setAttribute('aria-controls', body.id);
                        head.setAttribute(
                            'aria-expanded',
                            String(!body.hidden),
                        );

                        head.addEventListener('click', () => {
                            const shouldOpen = body.hidden;

                            projectFileList
                                .querySelectorAll('.project-file-slot')
                                .forEach((otherRow) => {
                                    const otherHead = otherRow.querySelector(
                                        '.project-file-slot__head',
                                    );
                                    const otherBody = otherRow.querySelector(
                                        '.project-file-slot__body',
                                    );

                                    otherRow.classList.remove('is-open');
                                    otherHead.setAttribute(
                                        'aria-expanded',
                                        'false',
                                    );
                                    otherBody.hidden = true;
                                });

                            if (shouldOpen) {
                                row.classList.add('is-open');
                                head.setAttribute('aria-expanded', 'true');
                                body.hidden = false;
                            }
                        });

                        if (storedFile) {
                            const fileName = document.createElement('p');
                            const preview = document.createElement('a');
                            const download = document.createElement('a');
                            const remove = createProjectFileAction(
                                'Eliminar',
                                'project-file-action--danger',
                            );

                            fileName.className = 'project-file-slot__file';
                            fileName.textContent = storedFile.is_link
                                ? '🔗 Link de Google Drive · Documento mayor a 50 MB'
                                : `${storedFile.name} · ${formatFileSize(storedFile.size)}`;
                            fileName.title = storedFile.name;
                            preview.className =
                                'project-file-action project-file-action--view';
                            preview.href = storedFile.preview_url;
                            preview.target = '_blank';
                            preview.rel = 'noopener';
                            preview.textContent = '👁 Ver';
                            preview.title = `Ver ${storedFile.name}`;
                            preview.setAttribute(
                                'aria-label',
                                `Ver archivo ${storedFile.name}`,
                            );
                            download.className = 'project-file-action';
                            download.href = storedFile.download_url;
                            download.target = storedFile.is_link ? '_blank' : '_self';
                            download.rel = storedFile.is_link ? 'noopener' : '';
                            download.textContent = 'Descargar';
                            remove.dataset.deleteProjectFile = String(
                                storedFile.id,
                            );
                            actions.append(preview, download, remove);
                            body.append(fileName, actions);
                        } else {
                            const empty = document.createElement('p');
                            const uploadLabel = document.createElement('label');
                            const input = document.createElement('input');
                            const linkButton = createProjectFileAction(
                                '🔗 Link ',
                                'project-file-action--link',
                            );
                            const inputId =
                                `project-file-${activeProjectFilesId}-${slot}`;

                            empty.className = 'project-file-slot__empty';
                            empty.textContent = 'Sin archivo';
                            uploadLabel.className = 'project-file-action';
                            uploadLabel.htmlFor = inputId;
                            uploadLabel.textContent = 'Subir archivo';
                            input.className = 'project-file-input';
                            input.id = inputId;
                            input.type = 'file';
                            input.accept =
                                '.pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.zip';
                            input.dataset.projectFileInput = String(slot);
                            linkButton.dataset.projectLinkSlot = String(slot);
                            actions.append(linkButton, uploadLabel, input);
                            body.append(empty, actions);
                        }

                        row.append(head, body);
                        projectFileList.append(row);
                    }
                };

                addProjectDocumentButton.addEventListener('click', () => {
                    if (!activeProjectFilesId) {
                        return;
                    }

                    createProjectDocumentForm.reset();
                    createProjectDocumentError.textContent = '';
                    createProjectDocumentDialog.showModal();
                    projectDocumentNameInput.focus();
                });

                createProjectDocumentForm.addEventListener(
                    'submit',
                    async (event) => {
                        event.preventDefault();

                        if (
                            !activeProjectFilesId
                            || createProjectDocumentSubmit.disabled
                        ) {
                            return;
                        }

                        const name = projectDocumentNameInput.value.trim();

                        if (name === '') {
                            createProjectDocumentError.textContent =
                                'Escribe un nombre para el documento.';
                            projectDocumentNameInput.focus();

                            return;
                        }

                        const projectId = activeProjectFilesId;
                        createProjectDocumentSubmit.disabled = true;
                        createProjectDocumentError.textContent = '';

                        try {
                            const response = await fetch(
                                `${projectEndpoint(projectId)}/documentos`,
                                {
                                    method: 'POST',
                                    headers: {
                                        Accept: 'application/json',
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken,
                                    },
                                    body: JSON.stringify({ name }),
                                },
                            );
                            const payload = await responsePayload(response);
                            const project = projects[projectId];

                            project.document_folders ??= [];
                            project.document_folders.push(
                                payload.document_folder,
                            );
                            closeDialog(createProjectDocumentDialog);
                            renderProjectFiles(
                                Number(payload.document_folder.position),
                            );
                            showProjectFilesFeedback(
                                'Documento añadido correctamente.',
                                true,
                            );
                        } catch (error) {
                            createProjectDocumentError.textContent =
                                error.message;
                            projectDocumentNameInput.focus();
                            projectDocumentNameInput.select();
                        } finally {
                            createProjectDocumentSubmit.disabled = false;
                        }
                    },
                );

                projectFileList.addEventListener('contextmenu', (event) => {
                    const row = event.target.closest(
                        '.project-file-slot[data-project-document-folder]',
                    );

                    if (!row || !activeProjectFilesId) {
                        return;
                    }

                    const project = projects[activeProjectFilesId];
                    const documentFolder = (project.document_folders ?? [])
                        .find(
                            (folder) => String(folder.id) ===
                                row.dataset.projectDocumentFolder,
                        );

                    if (!documentFolder) {
                        return;
                    }

                    event.preventDefault();
                    event.stopPropagation();
                    hideFolderContextMenu();
                    hideProjectContextMenu();
                    selectedProjectDocumentFolder = {
                        projectId: activeProjectFilesId,
                        folder: documentFolder,
                    };
                    placeContextMenu(projectDocumentContextMenu, event);
                });

                projectDocumentContextMenu.addEventListener(
                    'click',
                    (event) => {
                        const action = event.target.closest(
                            '[data-project-document-action="delete"]',
                        );

                        if (!action || !selectedProjectDocumentFolder) {
                            return;
                        }

                        hideProjectDocumentContextMenu();
                        deleteProjectDocumentForm.reset();
                        deleteProjectDocumentError.textContent = '';
                        deleteProjectDocumentName.textContent =
                            selectedProjectDocumentFolder.folder.name;
                        deleteProjectDocumentDialog.showModal();
                        deleteProjectDocumentCode.focus();
                    },
                );

                deleteProjectDocumentForm.addEventListener(
                    'submit',
                    async (event) => {
                        event.preventDefault();

                        if (
                            !selectedProjectDocumentFolder
                            || deleteProjectDocumentSubmit.disabled
                        ) {
                            return;
                        }

                        const { projectId, folder } =
                            selectedProjectDocumentFolder;
                        deleteProjectDocumentSubmit.disabled = true;
                        deleteProjectDocumentError.textContent = '';

                        try {
                            const response = await fetch(
                                `${projectEndpoint(projectId)}/documentos/${folder.id}`,
                                {
                                    method: 'DELETE',
                                    headers: {
                                        Accept: 'application/json',
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken,
                                    },
                                    body: JSON.stringify({
                                        code: deleteProjectDocumentCode.value,
                                    }),
                                },
                            );

                            await responsePayload(response);
                            const project = projects[projectId];

                            project.document_folders = (
                                project.document_folders ?? []
                            ).filter(
                                (item) => String(item.id) !== String(folder.id),
                            );
                            project.files = (project.files ?? []).filter(
                                (file) => Number(file.slot) !==
                                    Number(folder.position),
                            );
                            selectedProjectDocumentFolder = null;
                            closeDialog(deleteProjectDocumentDialog);
                            renderProjectFiles();
                            showProjectFilesFeedback(
                                'Documento eliminado correctamente.',
                                true,
                            );
                        } catch (error) {
                            deleteProjectDocumentError.textContent =
                                error.message;
                            deleteProjectDocumentCode.focus();
                            deleteProjectDocumentCode.select();
                        } finally {
                            deleteProjectDocumentSubmit.disabled = false;
                        }
                    },
                );

                const openProjectFiles = (projectId) => {
                    const project = projects[projectId];

                    if (!project) {
                        return;
                    }

                    activeProjectFilesId = projectId;
                    projectFilesTitle.textContent = project.snip
                        ? `${project.snip}-${project.title}`
                        : project.title;
                    projectFilesPlace.textContent = project.place;
                    showProjectFilesFeedback();
                    renderProjectFiles();
                    projectsTreeView.hidden = true;
                    projectFilesPanel.hidden = false;
                    showProjectLocations(projectId);
                    projectFilesBack.focus({ preventScroll: true });
                };

                projectFilesBack.addEventListener('click', () => {
                    projectFilesPanel.hidden = true;
                    projectsTreeView.hidden = false;
                    activeProjectFilesId = null;
                    showProjectLocations();
                });

                projectFileList.addEventListener('change', async (event) => {
                    const input = event.target.closest(
                        '[data-project-file-input]',
                    );

                    if (!input || !activeProjectFilesId || !input.files[0]) {
                        return;
                    }

                    const projectId = activeProjectFilesId;
                    const slot = input.dataset.projectFileInput;
                    const row = input.closest('.project-file-slot');
                    const formData = new FormData();

                    if (input.files[0].size > 50 * 1024 * 1024) {
                        input.value = '';
                        showProjectFilesFeedback(
                            'El archivo no puede superar 50 MB.',
                        );

                        return;
                    }

                    formData.append('file', input.files[0]);
                    input.disabled = true;
                    row.classList.add('is-loading');
                    showProjectFilesFeedback('Guardando archivo…');

                    try {
                        const response = await fetch(
                            `${projectEndpoint(projectId)}/archivos/${slot}`,
                            {
                                method: 'POST',
                                headers: {
                                    Accept: 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                },
                                body: formData,
                            },
                        );
                        const payload = await responsePayload(response);
                        const project = projects[projectId];

                        project.files = (project.files ?? []).filter(
                            (file) => Number(file.slot) !== Number(slot),
                        );
                        project.files.push(payload.file);
                        renderProjectFiles(Number(slot));
                        showProjectFilesFeedback(
                            'Archivo guardado correctamente.',
                            true,
                        );
                    } catch (error) {
                        input.disabled = false;
                        input.value = '';
                        row.classList.remove('is-loading');
                        showProjectFilesFeedback(error.message);
                    }
                });

                projectFileList.addEventListener('click', (event) => {
                    const linkButton = event.target.closest(
                        '[data-project-link-slot]',
                    );

                    if (!linkButton || !activeProjectFilesId) {
                        return;
                    }

                    selectedProjectLinkSlot = Number(
                        linkButton.dataset.projectLinkSlot,
                    );
                    createProjectLinkForm.reset();
                    createProjectLinkError.textContent = '';
                    createProjectLinkDialog.showModal();
                    projectLinkUrlInput.focus();
                });

                createProjectLinkForm.addEventListener(
                    'submit',
                    async (event) => {
                        event.preventDefault();

                        if (
                            !activeProjectFilesId
                            || !selectedProjectLinkSlot
                            || createProjectLinkSubmit.disabled
                        ) {
                            return;
                        }

                        const projectId = activeProjectFilesId;
                        const slot = selectedProjectLinkSlot;
                        createProjectLinkSubmit.disabled = true;
                        createProjectLinkError.textContent = '';

                        try {
                            const response = await fetch(
                                `${projectEndpoint(projectId)}/enlaces/${slot}`,
                                {
                                    method: 'POST',
                                    headers: {
                                        Accept: 'application/json',
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken,
                                    },
                                    body: JSON.stringify({
                                        url: projectLinkUrlInput.value.trim(),
                                    }),
                                },
                            );
                            const payload = await responsePayload(response);
                            const project = projects[projectId];

                            project.files = (project.files ?? []).filter(
                                (file) => Number(file.slot) !== Number(slot),
                            );
                            project.files.push(payload.file);
                            selectedProjectLinkSlot = null;
                            closeDialog(createProjectLinkDialog);
                            createProjectLinkForm.reset();
                            renderProjectFiles(Number(slot));
                            showProjectFilesFeedback(
                                'Link de Google Drive guardado correctamente.',
                                true,
                            );
                        } catch (error) {
                            createProjectLinkError.textContent = error.message;
                            projectLinkUrlInput.focus();
                            projectLinkUrlInput.select();
                        } finally {
                            createProjectLinkSubmit.disabled = false;
                        }
                    },
                );

                projectFileList.addEventListener('click', (event) => {
                    const deleteButton = event.target.closest(
                        '[data-delete-project-file]',
                    );

                    if (!deleteButton || !activeProjectFilesId) {
                        return;
                    }

                    const project = projects[activeProjectFilesId];
                    const file = (project.files ?? []).find(
                        (item) => String(item.id) ===
                            deleteButton.dataset.deleteProjectFile,
                    );

                    if (!file) {
                        return;
                    }

                    selectedProjectFile = {
                        projectId: activeProjectFilesId,
                        file,
                    };
                    deleteProjectFileForm.reset();
                    deleteProjectFileError.textContent = '';
                    deleteProjectFileName.textContent = file.name;
                    deleteProjectFileDialog.showModal();
                    deleteProjectFileCode.focus();
                });

                deleteProjectFileForm.addEventListener(
                    'submit',
                    async (event) => {
                        event.preventDefault();

                        if (
                            !selectedProjectFile
                            || deleteProjectFileSubmit.disabled
                        ) {
                            return;
                        }

                        const { projectId, file } = selectedProjectFile;
                        deleteProjectFileSubmit.disabled = true;
                        deleteProjectFileError.textContent = '';

                        try {
                            const response = await fetch(
                                `${projectEndpoint(projectId)}/archivos/${file.id}`,
                                {
                                    method: 'DELETE',
                                    headers: {
                                        Accept: 'application/json',
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken,
                                    },
                                    body: JSON.stringify({
                                        code: deleteProjectFileCode.value,
                                    }),
                                },
                            );

                            await responsePayload(response);
                            projects[projectId].files = (
                                projects[projectId].files ?? []
                            ).filter(
                                (item) => String(item.id) !== String(file.id),
                            );
                            selectedProjectFile = null;
                            closeDialog(deleteProjectFileDialog);
                            renderProjectFiles(Number(file.slot));
                            showProjectFilesFeedback(
                                'Documento eliminado correctamente.',
                                true,
                            );
                        } catch (error) {
                            deleteProjectFileError.textContent = error.message;
                            deleteProjectFileCode.focus();
                            deleteProjectFileCode.select();
                        } finally {
                            deleteProjectFileSubmit.disabled = false;
                        }
                    },
                );

                folderList.addEventListener('contextmenu', (event) => {
                    const projectItem = event.target.closest(
                        '.project-item[data-project]',
                    );

                    if (!projectItem || !folderList.contains(projectItem)) {
                        return;
                    }

                    event.preventDefault();
                    event.stopPropagation();
                    hideFolderContextMenu();
                    selectedFolder = null;
                    selectedProjectItem = projectItem;
                    selectedProjectFolder = projectItem.closest(
                        '.user-folder[data-folder-id]',
                    );
                    placeContextMenu(projectContextMenu, event);
                });

                projectContextMenu.addEventListener('click', (event) => {
                    const actionButton = event.target.closest(
                        '[data-project-action]',
                    );

                    if (!actionButton || !selectedProjectItem) {
                        return;
                    }

                    const projectId = selectedProjectItem.dataset.project;
                    const project = projects[projectId];
                    hideProjectContextMenu();

                    if (!project) {
                        return;
                    }

                    if (actionButton.dataset.projectAction === 'edit') {
                        editingProjectId = projectId;
                        projectForm.reset();
                        projectNoCoordinates.checked = false;
                        syncCoordinateFallback();
                        projectCreateError.textContent = '';
                        projectDialogTitle.textContent = 'Editar proyecto';
                        projectDialogCopy.textContent =
                            `Los cambios se aplicarán únicamente a “${project.title}”.`;
                        projectCreateSubmit.textContent = 'Guardar cambios';
                        projectNameInput.value = project.title;
                        projectSnipInput.value = project.snip ?? '';
                        projectPlaceInput.value = project.place;
                        projectLatitudeInput.value = formatCoordinate(
                            project.coordinates[0],
                            'latitude',
                        );
                        projectLongitudeInput.value = formatCoordinate(
                            project.coordinates[1],
                            'longitude',
                        );
                        projectDialog.showModal();
                        projectNameInput.focus();
                        projectNameInput.select();
                    } else {
                        deleteProjectForm.reset();
                        deleteProjectError.textContent = '';
                        deleteProjectName.textContent = project.title;
                        deleteProjectDialog.showModal();
                        deleteProjectCode.focus();
                    }
                });

                deleteProjectForm.addEventListener('submit', async (event) => {
                    event.preventDefault();

                    if (!selectedProjectItem || deleteProjectSubmit.disabled) {
                        return;
                    }

                    const projectId = selectedProjectItem.dataset.project;
                    const folderId = selectedProjectFolder?.dataset.folderId;
                    deleteProjectSubmit.disabled = true;
                    deleteProjectError.textContent = '';

                    try {
                        const response = await fetch(projectEndpoint(projectId), {
                            method: 'DELETE',
                            headers: {
                                Accept: 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({
                                code: deleteProjectCode.value,
                            }),
                        });

                        await responsePayload(response);
                        removeProjectLocation(projectId);

                        if (folderId) {
                            sessionStorage.setItem(
                                'project-folder-to-open',
                                folderId,
                            );
                        }

                        window.location.reload();
                    } catch (error) {
                        deleteProjectError.textContent = error.message;
                        deleteProjectCode.focus();
                        deleteProjectCode.select();
                        deleteProjectSubmit.disabled = false;
                    }
                });

                const coatepeque = [14.7000, -91.8667];
                const mapSelection = document.getElementById('map-selection');
                const mapStatus = document.getElementById('map-status');
                const projectItems = document.querySelectorAll('[data-project]');
                const projectsToggle = document.getElementById(
                    'projects-layer-toggle',
                );
                const coverageToggle = document.getElementById(
                    'coverage-layer-toggle',
                );
                const boundariesToggle = document.getElementById(
                    'boundaries-layer-toggle',
                );
                const roadsToggle = document.getElementById(
                    'roads-layer-toggle',
                );
                const portalSearch = document.querySelector('.search__input');
                const portalSearchButton = document.querySelector('.search__button');
                const arcGisApiKey = @js(config('services.arcgis.api_key'));

                const arcGisServicesUrl =
                    'https://server.arcgisonline.com/ArcGIS/rest/services/';
                const publicSatelliteProvider = () =>
                    Cesium.ArcGisMapServerImageryProvider.fromUrl(
                        `${arcGisServicesUrl}World_Imagery/MapServer`,
                    );
                let basemapSession = null;
                let satelliteProvider;

                if (
                    arcGisApiKey &&
                    window.arcgisRest?.BasemapStyleSession
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
                const roadsProvider =
                    Cesium.ArcGisMapServerImageryProvider.fromUrl(
                        `${arcGisServicesUrl}` +
                        'Reference/World_Transportation/MapServer',
                    );
                const boundariesProvider =
                    Cesium.ArcGisMapServerImageryProvider.fromUrl(
                        `${arcGisServicesUrl}` +
                        'Reference/World_Boundaries_and_Places/MapServer',
                    );
                const viewer = new Cesium.Viewer('project-map', {
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
                    terrainProvider: new Cesium.EllipsoidTerrainProvider(),
                });
                const scene = viewer.scene;
                const camera = viewer.camera;
                const cameraController = scene.screenSpaceCameraController;
                const entities = {};

                if (basemapSession) {
                    const applySessionToken = (sessionToken) => {
                        Cesium.ArcGisMapService.defaultAccessToken =
                            sessionToken;
                        const baseLayer = viewer.imageryLayers.get(0);
                        viewer.imageryLayers.remove(baseLayer, true);
                        satelliteProvider =
                            Cesium.ArcGisMapServerImageryProvider.fromBasemapType(
                                Cesium.ArcGisBaseMapType.SATELLITE,
                            );
                        viewer.imageryLayers.add(
                            Cesium.ImageryLayer.fromProviderAsync(
                                satelliteProvider,
                            ),
                            0,
                        );
                    };

                    basemapSession.on('refreshed', (event) => {
                        applySessionToken(event.current.token);
                    });
                    basemapSession.on('expired', () => {
                        basemapSession.refreshCredentials().catch(() => {
                            // Conserva la última vista si ArcGIS no responde.
                        });
                    });

                    viewer.creditDisplay.addStaticCredit(
                        new Cesium.Credit(
                            'Powered by <a href="https://www.esri.com/" target="_blank" rel="noopener">Esri</a>',
                            true,
                        ),
                    );
                }

                const formatCoordinate = (value, axis) => {
                    const absolute = Math.abs(value);
                    let degrees = Math.floor(absolute);
                    const minutesWithFraction = (absolute - degrees) * 60;
                    let minutes = Math.floor(minutesWithFraction);
                    let seconds = (minutesWithFraction - minutes) * 60;

                    if (seconds >= 59.995) {
                        seconds = 0;
                        minutes += 1;
                    }

                    if (minutes === 60) {
                        minutes = 0;
                        degrees += 1;
                    }

                    const hemisphere = axis === 'latitude'
                        ? (value >= 0 ? 'N' : 'S')
                        : (value >= 0 ? 'E' : 'O');

                    return `${degrees}°${String(minutes).padStart(2, '0')}'` +
                        `${seconds.toFixed(2).padStart(5, '0')}"${hemisphere}`;
                };

                const parseCoordinate = (rawValue, axis) => {
                    const value = rawValue
                        .trim()
                        .toUpperCase()
                        .replace(',', '.')
                        .replace(/[’′]/g, "'")
                        .replace(/[“”″]/g, '"');
                    const maximum = axis === 'latitude' ? 90 : 180;
                    const decimalMatch = value.match(
                        /^([+-]?\d{1,3}(?:\.\d+)?)°?$/,
                    );

                    if (decimalMatch) {
                        const decimal = Number(decimalMatch[1]);

                        return Number.isFinite(decimal) && Math.abs(decimal) <= maximum
                            ? decimal
                            : null;
                    }

                    const dmsMatch = value.match(
                        /^(\d{1,3})\s*°\s*(\d{1,2})\s*'\s*(\d{1,2}(?:\.\d+)?)\s*"?\s*([NSEOW])$/,
                    );

                    if (!dmsMatch) {
                        return null;
                    }

                    const degrees = Number(dmsMatch[1]);
                    const minutes = Number(dmsMatch[2]);
                    const seconds = Number(dmsMatch[3]);
                    const hemisphere = dmsMatch[4];
                    const validHemisphere = axis === 'latitude'
                        ? ['N', 'S'].includes(hemisphere)
                        : ['E', 'O', 'W'].includes(hemisphere);

                    if (
                        !validHemisphere
                        || degrees > maximum
                        || minutes >= 60
                        || seconds >= 60
                        || (degrees === maximum && (minutes > 0 || seconds > 0))
                    ) {
                        return null;
                    }

                    const decimal = degrees + (minutes / 60) + (seconds / 3600);

                    return ['S', 'O', 'W'].includes(hemisphere)
                        ? -decimal
                        : decimal;
                };

                projectForm.addEventListener('submit', async (event) => {
                    event.preventDefault();

                    if (!selectedProjectFolder || projectCreateSubmit.disabled) {
                        return;
                    }

                    const name = projectNameInput.value.trim();
                    const snip = projectSnipInput.value;

                    if (name === '') {
                        projectCreateError.textContent =
                            'Escribe un nombre para el proyecto.';
                        projectNameInput.focus();

                        return;
                    }

                    if (!/^\d{1,6}$/.test(snip)) {
                        projectCreateError.textContent =
                            'El SNIP solo puede contener números y un máximo de 6 dígitos.';
                        projectSnipInput.focus();

                        return;
                    }

                    const useCoatepequeCenter =
                        projectNoCoordinates.checked;
                    const latitude = useCoatepequeCenter
                        ? coatepequeProjectCenter[0]
                        : parseCoordinate(
                            projectLatitudeInput.value,
                            'latitude',
                        );
                    const longitude = useCoatepequeCenter
                        ? coatepequeProjectCenter[1]
                        : parseCoordinate(
                            projectLongitudeInput.value,
                            'longitude',
                        );

                    if (!useCoatepequeCenter && latitude === null) {
                        projectCreateError.textContent =
                            'Escribe una latitud válida, por ejemplo 14°42\'25.49"N.';
                        projectLatitudeInput.focus();
                        projectLatitudeInput.select();

                        return;
                    }

                    if (!useCoatepequeCenter && longitude === null) {
                        projectCreateError.textContent =
                            'Escribe una longitud válida, por ejemplo 91°59\'43.17"O.';
                        projectLongitudeInput.focus();
                        projectLongitudeInput.select();

                        return;
                    }

                    projectCreateSubmit.disabled = true;
                    projectCreateError.textContent = '';

                    try {
                        const editing = editingProjectId !== null;
                        const response = await fetch(
                            editing
                                ? projectEndpoint(editingProjectId)
                                : `${folderEndpoint(selectedProjectFolder)}/proyectos`,
                            {
                                method: editing ? 'PATCH' : 'POST',
                                headers: {
                                    Accept: 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                },
                                body: JSON.stringify({
                                    name,
                                    snip,
                                    place: projectPlaceInput.value.trim(),
                                    latitude,
                                    longitude,
                                }),
                            },
                        );

                        await responsePayload(response);
                        sessionStorage.setItem(
                            'project-folder-to-open',
                            selectedProjectFolder.dataset.folderId,
                        );
                        window.location.reload();
                    } catch (error) {
                        projectCreateError.textContent = error.message;
                        const errorInput = /SNIP/i.test(error.message)
                            ? projectSnipInput
                            : projectNameInput;
                        errorInput.focus();
                        errorInput.select();
                        projectCreateSubmit.disabled = false;
                    }
                });
                const roadsLayer =
                    Cesium.ImageryLayer.fromProviderAsync(roadsProvider);
                const boundariesLayer =
                    Cesium.ImageryLayer.fromProviderAsync(boundariesProvider);
                viewer.imageryLayers.add(roadsLayer);
                viewer.imageryLayers.add(boundariesLayer);
                const americaView = {
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
                const projectCameraRange = 3_800;
                const projectArrivalTolerance = 4_800;
                let correctingGlobeView = false;
                let cameraFlightActive = false;
                let activeProjectId = null;
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

                scene.backgroundColor = Cesium.Color.BLACK;
                scene.globe.baseColor = Cesium.Color.fromCssColorString('#08131c');
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
                cameraController.maximumTiltAngle = Cesium.Math.toRadians(80);
                cameraController.minimumTrackBallHeight = Number.POSITIVE_INFINITY;
                cameraController.enableLook = false;
                cameraController.enableRotate = true;
                cameraController.enableTilt = true;
                cameraController.zoomEventTypes = [
                    Cesium.CameraEventType.WHEEL,
                    Cesium.CameraEventType.PINCH,
                ];
                cameraController.tiltEventTypes = [
                    Cesium.CameraEventType.RIGHT_DRAG,
                    Cesium.CameraEventType.MIDDLE_DRAG,
                    Cesium.CameraEventType.PINCH,
                    {
                        eventType: Cesium.CameraEventType.LEFT_DRAG,
                        modifier: Cesium.KeyboardEventModifier.CTRL,
                    },
                ];
                cameraController.inertiaSpin = 0.72;
                cameraController.inertiaZoom = 0.72;
                viewer.resolutionScale = Math.min(
                    Math.max(window.devicePixelRatio || 1, 1.25),
                    1.75,
                );
                viewer.clock.shouldAnimate = false;

                roadsLayer.alpha = 0.92;
                boundariesLayer.alpha = 1;

                camera.setView(americaView);

                const coverageEntity = viewer.entities.add({
                    id: 'municipal-coverage',
                    position: Cesium.Cartesian3.fromDegrees(
                        coatepeque[1],
                        coatepeque[0],
                    ),
                    ellipse: {
                        semiMajorAxis: 2500,
                        semiMinorAxis: 2500,
                        height: 40,
                        material: Cesium.Color.fromCssColorString('#126b91')
                            .withAlpha(0.15),
                        outline: true,
                        outlineColor: Cesium.Color.fromCssColorString('#31a9d6')
                            .withAlpha(0.9),
                    },
                    show: true,
                });

                Object.entries(projects).forEach(([id, project]) => {
                    entities[id] = viewer.entities.add({
                        id: `project-${id}`,
                        name: project.title,
                        position: Cesium.Cartesian3.fromDegrees(
                            project.coordinates[1],
                            project.coordinates[0],
                            0,
                        ),
                        point: {
                            pixelSize: 14,
                            heightReference:
                                Cesium.HeightReference.CLAMP_TO_GROUND,
                            color: Cesium.Color.fromCssColorString(project.color),
                            outlineColor: Cesium.Color.WHITE,
                            outlineWidth: 3,
                            scaleByDistance: new Cesium.NearFarScalar(
                                1_000,
                                1.35,
                                2_500_000,
                                0.35,
                            ),
                            disableDepthTestDistance: Number.POSITIVE_INFINITY,
                        },
                        label: {
                            text: `${project.code} · ${project.title}`,
                            heightReference:
                                Cesium.HeightReference.CLAMP_TO_GROUND,
                            font: '600 14px Segoe UI',
                            fillColor: Cesium.Color.WHITE,
                            outlineColor: Cesium.Color.BLACK,
                            outlineWidth: 4,
                            style: Cesium.LabelStyle.FILL_AND_OUTLINE,
                            pixelOffset: new Cesium.Cartesian2(0, -27),
                            distanceDisplayCondition:
                                new Cesium.DistanceDisplayCondition(0, 120_000),
                            disableDepthTestDistance: Number.POSITIVE_INFINITY,
                        },
                        description:
                            `<p><strong>Proyecto</strong></p>` +
                            (project.snip
                                ? `<p><strong>SNIP:</strong> ${project.snip}</p>`
                                : '') +
                            `<p>${project.place}</p>` +
                            `<p>${project.description}</p>`,
                        show: true,
                    });
                });

                showProjectLocations = (selectedProjectId = null) => {
                    Object.entries(entities).forEach(([entityId, entity]) => {
                        entity.show = projectsToggle.checked
                            && (
                                selectedProjectId === null
                                || entityId === selectedProjectId
                            );
                    });
                    scene.requestRender();
                };

                removeProjectLocation = (projectId) => {
                    const normalizedProjectId = String(projectId);
                    const entity = entities[normalizedProjectId];

                    if (entity) {
                        viewer.entities.remove(entity);
                        delete entities[normalizedProjectId];
                    }

                    delete projects[normalizedProjectId];

                    if (
                        String(activeProjectFilesId) === normalizedProjectId
                    ) {
                        activeProjectFilesId = null;
                    }

                    showProjectLocations(activeProjectFilesId);
                };

                showProjectLocations(activeProjectFilesId);

                const selectProject = (id) => {
                    const project = projects[id];

                    if (!project) {
                        return;
                    }

                    const entityPosition = entities[id].position.getValue(
                        viewer.clock.currentTime,
                    );
                    const targetPosition = Cesium.Cartesian3.clone(
                        entityPosition,
                    );
                    const distanceToProject = Cesium.Cartesian3.distance(
                        camera.positionWC,
                        targetPosition,
                    );
                    const isCurrentProject = activeProjectId === id;

                    if (
                        isCurrentProject
                        && (
                            cameraFlightActive
                            || distanceToProject <= projectArrivalTolerance
                        )
                    ) {
                        return;
                    }

                    activeProjectId = id;

                    projectsToggle.checked = true;
                    showProjectLocations(activeProjectFilesId);

                    projectItems.forEach((item) => {
                        item.classList.toggle(
                            'is-active',
                            item.dataset.project === id,
                        );
                    });
                    mapSelection.textContent =
                        `${project.title} · ${project.place}`;

                    viewer.selectedEntity = entities[id];
                    cameraFlightActive = true;
                    camera.flyToBoundingSphere(
                        new Cesium.BoundingSphere(targetPosition, 40),
                        {
                            offset: new Cesium.HeadingPitchRange(
                                Cesium.Math.toRadians(12),
                                Cesium.Math.toRadians(-62),
                                projectCameraRange,
                            ),
                            duration: 2.2,
                            complete: () => {
                                viewer.selectedEntity = entities[id];
                                finishCameraFlight();
                            },
                            cancel: finishCameraFlight,
                        },
                    );
                };

                projectItems.forEach((item) => {
                    item.addEventListener('click', () => {
                        const projectId = item.dataset.project;

                        openProjectFiles(projectId);
                        window.requestAnimationFrame(() => {
                            viewer.resize();
                            scene.requestRender();
                            selectProject(projectId);
                        });
                    });
                });

                projectsToggle.addEventListener('change', () => {
                    showProjectLocations(activeProjectFilesId);
                });

                coverageToggle.addEventListener('change', () => {
                    coverageEntity.show = coverageToggle.checked;
                });

                boundariesToggle.addEventListener('change', () => {
                    boundariesLayer.show = boundariesToggle.checked;
                    scene.requestRender();
                });

                roadsToggle.addEventListener('change', () => {
                    roadsLayer.show = roadsToggle.checked;
                    scene.requestRender();
                });

                const filterProjects = () => {
                    const query = portalSearch.value
                        .trim()
                        .toLocaleLowerCase('es');
                    let firstMatch = null;

                    projectItems.forEach((item) => {
                        const matches =
                            query === '' ||
                            item.dataset.search.includes(query);
                        item.classList.toggle('is-hidden', !matches);

                        if (matches && query !== '' && firstMatch === null) {
                            firstMatch = item.dataset.project;
                        }
                    });

                    if (firstMatch) {
                        selectProject(firstMatch);
                    } else if (query === '') {
                        mapSelection.textContent =
                            'Vista general de proyectos';
                    } else {
                        mapSelection.textContent =
                            'No se encontraron proyectos';
                    }
                };

                portalSearch.addEventListener('input', filterProjects);
                portalSearch.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        filterProjects();
                    }
                });
                portalSearchButton.addEventListener('click', filterProjects);

                document.getElementById('map-home').addEventListener(
                    'click',
                    () => {
                        projectItems.forEach((item) => {
                            item.classList.remove('is-active');
                        });
                        mapSelection.textContent =
                            'Vista general de proyectos';
                        viewer.selectedEntity = undefined;
                        cameraFlightActive = true;
                        camera.flyTo({
                            ...americaView,
                            duration: 2.8,
                            complete: finishCameraFlight,
                            cancel: finishCameraFlight,
                        });
                    },
                );

                document.getElementById('map-fullscreen').addEventListener(
                    'click',
                    async () => {
                        const panel = document.querySelector('.map-panel');

                        if (document.fullscreenElement) {
                            await document.exitFullscreen();
                        } else if (panel.requestFullscreen) {
                            await panel.requestFullscreen();
                        }
                    },
                );

                document.getElementById('globe-north').addEventListener(
                    'click',
                    () => {
                        cameraFlightActive = true;
                        camera.flyTo({
                            destination: camera.positionWC,
                            orientation: {
                                heading: 0,
                                pitch: camera.pitch,
                                roll: 0,
                            },
                            duration: 0.8,
                            complete: finishCameraFlight,
                            cancel: finishCameraFlight,
                        });
                    },
                );
                document.getElementById('globe-zoom-in').addEventListener(
                    'click',
                    () => camera.zoomIn(camera.positionCartographic.height * 0.35),
                );
                document.getElementById('globe-zoom-out').addEventListener(
                    'click',
                    () => {
                        const altitude = camera.positionCartographic.height;
                        const availableDistance = Math.max(
                            0,
                            maximumGlobeAltitude - altitude,
                        );

                        camera.zoomOut(
                            Math.min(altitude * 0.55, availableDistance),
                        );
                        keepGlobeVisible();
                    },
                );

                scene.postRender.addEventListener(keepGlobeVisible);
                window.addEventListener('home-module-shown', () => {
                    window.requestAnimationFrame(() => {
                        viewer.resize();
                        keepGlobeVisible();
                        scene.requestRender();
                    });
                });
                camera.changed.addEventListener(() => {
                    const position = camera.positionCartographic;
                    const latitude = Cesium.Math.toDegrees(position.latitude);
                    const longitude = Cesium.Math.toDegrees(position.longitude);
                    const altitude = position.height;
                    const altitudeLabel = altitude >= 1_000_000
                        ? `${(altitude / 1_000_000).toFixed(1)} mil km`
                        : `${Math.round(altitude / 1000)} km`;
                    mapStatus.textContent =
                        `${latitude.toFixed(3)}, ${longitude.toFixed(3)} · ` +
                        `Alt. ${altitudeLabel}`;
                });
                keepGlobeVisible();

                let logoutInProgress = false;

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
                        // La redirección local se ejecuta aun si la red falla.
                    } finally {
                        window.location.replace(loginUrl);
                    }
                };

                const validateRestoredSession = async (event) => {
                    const navigation = performance.getEntriesByType(
                        'navigation',
                    )[0];
                    const restored = event.persisted
                        || navigation?.type === 'back_forward';

                    if (!restored) {
                        document.documentElement.style.visibility = '';
                        return;
                    }

                    if (sessionStorage.getItem(historySessionKey) === 'true') {
                        await returnToLogin();
                        return;
                    }

                    try {
                        const response = await fetch(activityUrl, {
                            method: 'POST',
                            credentials: 'same-origin',
                            cache: 'no-store',
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
                            await returnToLogin();
                            return;
                        }

                        document.documentElement.style.visibility = '';
                    } catch {
                        await returnToLogin();
                    }
                };

                logoutForm.addEventListener('submit', () => {
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
                window.addEventListener('pageshow', validateRestoredSession);

                const scheduleExpiration = () => {
                    window.clearTimeout(expirationTimer);

                    const elapsed = Date.now() - lastActivity;
                    const remaining = idleTimeout - elapsed;

                    if (remaining <= 0) {
                        returnToLogin();
                        return;
                    }

                    expirationTimer = window.setTimeout(() => {
                        if (Date.now() - lastActivity >= idleTimeout) {
                            returnToLogin();
                            return;
                        }

                        scheduleExpiration();
                    }, remaining + 100);
                };

                const keepSessionAlive = async () => {
                    const now = Date.now();

                    if (
                        pingInProgress ||
                        now - lastPing < pingInterval ||
                        document.hidden
                    ) {
                        return;
                    }

                    pingInProgress = true;
                    lastPing = now;

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
                            response.redirected ||
                            response.status === 401 ||
                            response.status === 419
                        ) {
                            returnToLogin();
                        }
                    } catch {
                        // La siguiente actividad volverá a intentar la conexión.
                    } finally {
                        pingInProgress = false;
                    }
                };

                const recordActivity = () => {
                    const now = Date.now();

                    if (now - lastRecordedActivity < activityThrottle) {
                        return;
                    }

                    lastRecordedActivity = now;
                    lastActivity = now;
                    scheduleExpiration();
                    keepSessionAlive();
                };

                window.addEventListener('message', (event) => {
                    const trustedModuleFrame = Array.from(
                        moduleFrames.values(),
                    ).some(
                        (moduleFrame) => event.source
                            === moduleFrame.contentWindow,
                    );

                    if (
                        event.origin !== window.location.origin
                        || !trustedModuleFrame
                        || event.data?.type !== 'module-activity'
                    ) {
                        return;
                    }

                    recordActivity();
                });

                [
                    'pointerdown',
                    'pointermove',
                    'keydown',
                    'scroll',
                    'touchstart',
                ].forEach((eventName) => {
                    document.addEventListener(eventName, recordActivity, {
                        passive: true,
                    });
                });

                window.addEventListener('focus', () => {
                    if (Date.now() - lastActivity >= idleTimeout) {
                        returnToLogin();
                        return;
                    }

                    recordActivity();
                });

                document.addEventListener('visibilitychange', () => {
                    if (document.hidden) {
                        return;
                    }

                    if (Date.now() - lastActivity >= idleTimeout) {
                        returnToLogin();
                        return;
                    }

                    recordActivity();
                });

                scheduleExpiration();
            })();
        </script>
    </body>
</html>

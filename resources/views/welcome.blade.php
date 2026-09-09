<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Construyendo un mejor Coatepeque</title>

        <style>
            :root {
                color-scheme: light;
                background: #102b43;
            }

            * {
                box-sizing: border-box;
            }

            html,
            body {
                width: 100%;
                height: 100%;
                margin: 0;
            }

            body {
                position: relative;
                min-height: 100vh;
                min-height: 100svh;
                overflow: hidden;
                background-color: #102b43;
                font-family: Arial, Helvetica, sans-serif;
            }

            body::before {
                content: "";
                position: fixed;
                inset: -4vmax;
                background-image: url("{{ asset('images/hero-coatepeque-enhanced.png') }}");
                background-repeat: no-repeat;
                background-position: center;
                background-size: cover;
                filter: blur(clamp(18px, 3vw, 36px)) brightness(0.54) saturate(1.15);
                transform: scale(1.08);
            }

            body::after {
                content: "";
                position: fixed;
                inset: 0;
                background:
                    linear-gradient(
                        180deg,
                        rgba(5, 20, 34, 0.2),
                        rgba(5, 20, 34, 0.08) 42%,
                        rgba(5, 20, 34, 0.26)
                    );
            }

            .hero {
                position: relative;
                z-index: 1;
                display: grid;
                place-items: center;
                width: 100%;
                min-height: 100vh;
                min-height: 100svh;
            }

            .hero__image {
                display: block;
                width: 100vw;
                height: 100vh;
                height: 100svh;
                object-fit: contain;
                object-position: center;
                filter: drop-shadow(0 16px 42px rgba(0, 0, 0, 0.34));
                pointer-events: none;
                user-select: none;
            }

            .access {
                position: absolute;
                z-index: 2;
                top: 50%;
                left: 50%;
                display: grid;
                gap: 16px;
                width: min(92vw, 430px);
                transform: translate(-50%, -50%);
                transition: gap 260ms ease;
            }

            .access__actions {
                display: flex;
                width: min(100%, 230px);
                max-height: 140px;
                margin-inline: auto;
                flex-direction: column;
                justify-content: center;
                gap: 12px;
                overflow: hidden;
                visibility: visible;
                opacity: 1;
                transform: translateY(0) scale(1);
                transition:
                    max-height 320ms ease,
                    opacity 200ms ease,
                    transform 260ms ease,
                    visibility 0s;
            }

            .access.is-login-open {
                gap: 0;
            }

            .access.is-login-open .access__actions {
                max-height: 0;
                visibility: hidden;
                opacity: 0;
                transform: translateY(-14px) scale(0.96);
                pointer-events: none;
                transition:
                    max-height 320ms ease,
                    opacity 180ms ease,
                    transform 260ms ease,
                    visibility 0s linear 260ms;
            }

            .glass-button {
                display: block;
                min-width: 150px;
                padding: 14px 28px;
                border: 1px solid rgba(255, 255, 255, 0.62);
                border-radius: 999px;
                color: #fff;
                background: rgba(7, 35, 60, 0.3);
                box-shadow:
                    0 12px 34px rgba(0, 0, 0, 0.28),
                    inset 0 1px 0 rgba(255, 255, 255, 0.24);
                backdrop-filter: blur(16px) saturate(145%);
                -webkit-backdrop-filter: blur(16px) saturate(145%);
                font-size: 1rem;
                font-weight: 700;
                letter-spacing: 0.04em;
                text-align: center;
                text-decoration: none;
                text-shadow: 0 1px 8px rgba(0, 0, 0, 0.5);
                cursor: pointer;
                transition:
                    transform 180ms ease,
                    background-color 180ms ease,
                    border-color 180ms ease,
                    box-shadow 180ms ease;
            }

            .glass-button:hover {
                transform: translateY(-2px);
                border-color: rgba(255, 255, 255, 0.9);
                background: rgba(12, 68, 108, 0.5);
                box-shadow:
                    0 16px 40px rgba(0, 0, 0, 0.34),
                    inset 0 1px 0 rgba(255, 255, 255, 0.3);
            }

            .glass-button:active {
                transform: translateY(0);
            }

            .glass-button:focus-visible,
            .glass-input:focus-visible,
            .password-peek:focus-visible,
            .forgot-password:focus-visible,
            .panel-link:focus-visible,
            .login-card__close:focus-visible {
                outline: 3px solid rgba(81, 207, 255, 0.92);
                outline-offset: 3px;
            }

            .login-panel {
                max-height: 0;
                overflow: hidden;
                visibility: hidden;
                opacity: 0;
                transform: translateY(-12px) scale(0.98);
                transition:
                    max-height 420ms ease,
                    opacity 220ms ease,
                    transform 320ms ease,
                    visibility 0s linear 420ms;
            }

            .login-panel.is-open {
                max-height: 700px;
                visibility: visible;
                opacity: 1;
                transform: translateY(0) scale(1);
                transition:
                    max-height 420ms ease,
                    opacity 260ms ease 60ms,
                    transform 320ms ease,
                    visibility 0s;
            }

            .login-card {
                position: relative;
                padding: 26px;
                border: 1px solid rgba(255, 255, 255, 0.48);
                border-radius: 24px;
                color: #fff;
                background:
                    linear-gradient(
                        145deg,
                        rgba(8, 44, 74, 0.48),
                        rgba(4, 24, 42, 0.3)
                    );
                box-shadow:
                    0 22px 60px rgba(0, 0, 0, 0.38),
                    inset 0 1px 0 rgba(255, 255, 255, 0.2);
                backdrop-filter: blur(24px) saturate(145%);
                -webkit-backdrop-filter: blur(24px) saturate(145%);
            }

            .login-card__title {
                margin: 0 42px 6px 0;
                font-size: 1.45rem;
                line-height: 1.15;
            }

            .login-card__subtitle {
                margin: 0 0 22px;
                color: rgba(255, 255, 255, 0.78);
                font-size: 0.9rem;
            }

            .login-card__close {
                position: absolute;
                top: 17px;
                right: 17px;
                display: grid;
                width: 34px;
                height: 34px;
                place-items: center;
                border: 1px solid rgba(255, 255, 255, 0.42);
                border-radius: 50%;
                color: #fff;
                background: rgba(255, 255, 255, 0.1);
                font-size: 1.2rem;
                cursor: pointer;
            }

            .panel-view[hidden] {
                display: none;
            }

            .panel-view.is-active {
                animation: panel-view-in 300ms ease both;
            }

            .login-form {
                display: grid;
                gap: 15px;
            }

            .field {
                display: grid;
                gap: 7px;
            }

            .field label {
                font-size: 0.82rem;
                font-weight: 700;
                letter-spacing: 0.025em;
            }

            .glass-input {
                width: 100%;
                padding: 13px 15px;
                border: 1px solid rgba(255, 255, 255, 0.42);
                border-radius: 13px;
                color: #fff;
                background: rgba(255, 255, 255, 0.11);
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.1);
                font: inherit;
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
            }

            .glass-input::placeholder {
                color: rgba(255, 255, 255, 0.62);
            }

            .glass-input:disabled,
            .glass-input[readonly] {
                opacity: 0.92;
            }

            .recovery-code-input {
                font-size: 1.2rem;
                font-weight: 800;
                letter-spacing: 0.34em;
                text-align: center;
            }

            .password-rules {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 7px 12px;
                padding: 0;
                margin: -2px 0 2px;
                color: rgba(255, 255, 255, 0.68);
                font-size: 0.73rem;
                list-style: none;
            }

            .password-rules li {
                display: flex;
                align-items: center;
                gap: 6px;
                transition: color 160ms ease;
            }

            .password-rules li::before {
                content: "○";
                color: rgba(255, 255, 255, 0.48);
                font-size: 0.85rem;
            }

            .password-rules li.is-met {
                color: #d8ffe9;
            }

            .password-rules li.is-met::before {
                content: "✓";
                color: #71efad;
                font-weight: 800;
            }

            .password-control {
                position: relative;
            }

            .password-control .glass-input {
                padding-right: 58px;
            }

            .password-entry {
                -webkit-text-security: disc !important;
            }

            .password-entry.is-visible {
                -webkit-text-security: none !important;
            }

            .password-peek {
                position: absolute;
                top: 50%;
                right: 7px;
                display: grid;
                width: 42px;
                height: 42px;
                padding: 0;
                place-items: center;
                transform: translateY(-50%);
                border: 1px solid rgba(255, 255, 255, 0.36);
                border-radius: 11px;
                color: rgba(255, 255, 255, 0.88);
                background: rgba(255, 255, 255, 0.1);
                font-size: 1.05rem;
                line-height: 1;
                cursor: pointer;
                touch-action: none;
                user-select: none;
                transition:
                    color 160ms ease,
                    background-color 160ms ease,
                    transform 160ms ease;
            }

            .password-peek:hover,
            .password-peek.is-revealing {
                color: #fff;
                background: rgba(32, 184, 237, 0.28);
            }

            .password-peek.is-revealing {
                transform: translateY(-50%) scale(0.94);
            }

            .password-peek:disabled {
                opacity: 0.5;
                cursor: not-allowed;
            }

            .login-form__options {
                display: flex;
                align-items: center;
                justify-content: flex-start;
                gap: 12px;
                color: rgba(255, 255, 255, 0.82);
                font-size: 0.8rem;
            }

            .forgot-password {
                padding: 2px 0;
                border: 0;
                color: rgba(255, 255, 255, 0.86);
                background: transparent;
                font: inherit;
                text-decoration: underline;
                text-decoration-color: rgba(255, 255, 255, 0.4);
                text-underline-offset: 4px;
                cursor: pointer;
            }

            .panel-links {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
            }

            .panel-link {
                padding: 3px 5px;
                border: 0;
                color: rgba(255, 255, 255, 0.84);
                background: transparent;
                font: inherit;
                font-size: 0.8rem;
                text-decoration: underline;
                text-decoration-color: rgba(255, 255, 255, 0.38);
                text-underline-offset: 4px;
                cursor: pointer;
            }

            .login-submit {
                width: 100%;
                margin-top: 2px;
            }

            .glass-button:disabled {
                opacity: 0.68;
                cursor: wait;
                transform: none;
            }

            .login-status {
                min-height: 1.2em;
                margin: 0;
                color: rgba(255, 255, 255, 0.86);
                font-size: 0.78rem;
                text-align: center;
            }

            .login-status.is-error {
                color: #ffe1df;
            }

            .login-status.is-success {
                color: #d8ffe9;
            }

            .session-takeover-notice {
                display: grid;
                justify-items: center;
                gap: 8px;
                padding: 18px 16px;
                border: 1px solid rgba(76, 198, 239, 0.42);
                border-radius: 15px;
                color: rgba(255, 255, 255, 0.82);
                background: rgba(6, 25, 40, 0.42);
                font-size: 0.8rem;
                line-height: 1.45;
                text-align: center;
            }

            .session-takeover-notice__icon {
                display: grid;
                width: 42px;
                height: 42px;
                place-items: center;
                border: 1px solid rgba(255, 255, 255, 0.34);
                border-radius: 50%;
                color: #d7f6ff;
                background: rgba(32, 184, 237, 0.2);
                font-size: 1.25rem;
                font-weight: 800;
            }

            .sr-only {
                position: absolute;
                width: 1px;
                height: 1px;
                padding: 0;
                margin: -1px;
                overflow: hidden;
                clip: rect(0, 0, 0, 0);
                white-space: nowrap;
                border: 0;
            }

            @media (max-width: 520px) {
                .access {
                    width: min(90vw, 390px);
                }

                .glass-button {
                    width: 100%;
                }

                .login-card {
                    padding: 22px 18px;
                }

                .password-rules {
                    grid-template-columns: 1fr;
                }
            }

            @media (max-height: 650px) {
                .hero {
                    min-height: 650px;
                    overflow-y: auto;
                }

                .access {
                    top: 24px;
                    padding-bottom: 24px;
                    transform: translateX(-50%);
                }
            }

            @media (prefers-reduced-motion: no-preference) {
                .hero__image {
                    animation: reveal 650ms ease-out both;
                }
            }

            @keyframes reveal {
                from {
                    opacity: 0;
                    transform: scale(1.012);
                }

                to {
                    opacity: 1;
                    transform: scale(1);
                }
            }

            @keyframes panel-view-in {
                from {
                    opacity: 0;
                    transform: translateY(12px) scale(0.985);
                }

                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }
        </style>
    </head>
    <body>
        <main class="hero">
            <h1 class="sr-only">Construyendo un mejor Coatepeque</h1>
            <img
                class="hero__image"
                src="{{ asset('images/hero-coatepeque-enhanced.png') }}"
                alt="Collage del Gobierno Municipal de Coatepeque con maquinaria, obras, espacios públicos y vistas de la ciudad"
                width="1918"
                height="820"
                fetchpriority="high"
            >

            <section
                class="access"
                id="access"
                aria-label="Acceso al sistema"
                data-login-url="{{ route('login.store') }}"
                data-logout-url="{{ route('logout') }}"
                data-session-replaced="{{ request('sesion') === 'reemplazada' ? 'true' : 'false' }}"
                data-recovery-code-url="{{ route('password.recovery.code') }}"
                data-recovery-verify-url="{{ route('password.recovery.verify') }}"
                data-recovery-reset-url="{{ route('password.recovery.reset') }}"
            >
                <div class="access__actions" id="access-actions">
                    <a
                        class="glass-button"
                        id="enter-button"
                        href="{{ route('public.portal') }}"
                    >
                        Ingresa
                    </a>
                    <button
                        class="glass-button"
                        id="login-toggle"
                        type="button"
                        aria-controls="login-panel"
                        aria-expanded="false"
                    >
                        Login
                    </button>
                </div>

                <div
                    class="login-panel"
                    id="login-panel"
                    aria-hidden="true"
                    inert
                >
                    <div class="login-card">
                        <button
                            class="login-card__close"
                            id="login-close"
                            type="button"
                            aria-label="Cerrar ventana de acceso"
                        >
                            ×
                        </button>

                        <div
                            class="panel-view is-active"
                            id="login-view"
                        >
                            <h2 class="login-card__title">Bienvenido</h2>
                            <p class="login-card__subtitle">
                                Ingresa tus datos para continuar.
                            </p>

                            <div
                                class="login-form"
                                id="login-form"
                                role="group"
                                aria-label="Formulario de acceso"
                            >
                                <div class="field">
                                    <label for="email">Correo electrónico</label>
                                    <input
                                        class="glass-input"
                                        id="email"
                                        name="manual_identity"
                                        type="text"
                                        placeholder="nombre@correo.com"
                                        inputmode="email"
                                        autocomplete="one-time-code"
                                        autocapitalize="none"
                                        spellcheck="false"
                                        data-lpignore="true"
                                        data-1p-ignore
                                        data-bwignore="true"
                                        readonly
                                        required
                                    >
                                </div>

                                <div class="field">
                                    <label for="password">Contraseña</label>
                                    <div class="password-control">
                                        <input
                                            class="glass-input password-entry"
                                            id="password"
                                            name="manual_access_key"
                                            type="text"
                                            placeholder="Tu contraseña"
                                            autocomplete="one-time-code"
                                            autocapitalize="none"
                                            spellcheck="false"
                                            data-lpignore="true"
                                            data-1p-ignore
                                            data-bwignore="true"
                                            readonly
                                            required
                                        >
                                        <button
                                            class="password-peek"
                                            id="password-peek"
                                            type="button"
                                            aria-label="Mantener presionado para mostrar la contraseña"
                                            aria-pressed="false"
                                        >
                                            <span aria-hidden="true">👁</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="login-form__options">
                                    <button
                                        class="forgot-password"
                                        id="forgot-password"
                                        type="button"
                                    >
                                        Olvidé mi contraseña
                                    </button>
                                </div>

                                <button
                                    class="glass-button login-submit"
                                    id="login-submit"
                                    type="button"
                                >
                                    Entrar
                                </button>

                                <p
                                    class="login-status"
                                    id="login-status"
                                    role="status"
                                    aria-live="polite"
                                ></p>
                            </div>
                        </div>

                        <div
                            class="panel-view"
                            id="session-takeover-view"
                            hidden
                            inert
                        >
                            <h2 class="login-card__title">Sesión abierta</h2>
                            <p class="login-card__subtitle">
                                Esta cuenta ya está abierta en otro lugar.
                            </p>

                            <div
                                class="login-form"
                                role="group"
                                aria-label="Confirmar traslado de sesión"
                            >
                                <div class="session-takeover-notice">
                                    <span
                                        class="session-takeover-notice__icon"
                                        aria-hidden="true"
                                    >↔</span>
                                    <strong>¿Quieres abrirla aquí?</strong>
                                    <span>
                                        Al continuar, la sesión anterior se
                                        cerrará automáticamente.
                                    </span>
                                </div>

                                <button
                                    class="glass-button login-submit"
                                    id="accept-session-takeover"
                                    type="button"
                                >
                                    Sí, abrir aquí
                                </button>

                                <div class="panel-links">
                                    <button
                                        class="panel-link"
                                        id="cancel-session-takeover"
                                        type="button"
                                    >
                                        No, mantener la otra sesión
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div
                            class="panel-view"
                            id="session-takeover-password-view"
                            hidden
                            inert
                        >
                            <h2 class="login-card__title">
                                Confirma tu identidad
                            </h2>
                            <p class="login-card__subtitle">
                                Escribe nuevamente tu contraseña para abrir la
                                cuenta aquí.
                            </p>

                            <div
                                class="login-form"
                                role="group"
                                aria-label="Confirmar contraseña para trasladar la sesión"
                            >
                                <div class="field">
                                    <label for="session-takeover-password">
                                        Contraseña
                                    </label>
                                    <div class="password-control">
                                        <input
                                            class="glass-input password-entry"
                                            id="session-takeover-password"
                                            name="manual_takeover_key"
                                            type="text"
                                            placeholder="Escribe tu contraseña"
                                            autocomplete="one-time-code"
                                            autocapitalize="none"
                                            spellcheck="false"
                                            data-lpignore="true"
                                            data-1p-ignore
                                            data-bwignore="true"
                                            maxlength="128"
                                            readonly
                                            required
                                        >
                                        <button
                                            class="password-peek"
                                            id="session-takeover-password-peek"
                                            type="button"
                                            aria-label="Mantener presionado para mostrar la contraseña"
                                            aria-pressed="false"
                                        >
                                            <span aria-hidden="true">👁</span>
                                        </button>
                                    </div>
                                </div>

                                <button
                                    class="glass-button login-submit"
                                    id="confirm-session-takeover"
                                    type="button"
                                >
                                    Abrir cuenta aquí
                                </button>

                                <div class="panel-links">
                                    <button
                                        class="panel-link"
                                        id="back-to-session-takeover"
                                        type="button"
                                    >
                                        Volver
                                    </button>
                                </div>

                                <p
                                    class="login-status"
                                    id="session-takeover-status"
                                    role="status"
                                    aria-live="polite"
                                ></p>
                            </div>
                        </div>

                        <div
                            class="panel-view"
                            id="recovery-email-view"
                            hidden
                            inert
                        >
                            <h2 class="login-card__title">
                                Recuperar contraseña
                            </h2>
                            <p class="login-card__subtitle">
                                Escribe el correo registrado en tu cuenta.
                            </p>

                            <div
                                class="login-form"
                                role="group"
                                aria-label="Solicitar código de recuperación"
                            >
                                <div class="field">
                                    <label for="recovery-email">
                                        Correo electrónico
                                    </label>
                                    <input
                                        class="glass-input"
                                        id="recovery-email"
                                        name="account_lookup"
                                        type="text"
                                        placeholder="nombre@correo.com"
                                        inputmode="email"
                                        autocomplete="one-time-code"
                                        autocapitalize="none"
                                        spellcheck="false"
                                    >
                                </div>

                                <button
                                    class="glass-button login-submit"
                                    id="send-recovery-code"
                                    type="button"
                                >
                                    Enviar código
                                </button>

                                <div class="panel-links">
                                    <button
                                        class="panel-link"
                                        id="back-to-login"
                                        type="button"
                                    >
                                        Volver al login
                                    </button>
                                </div>

                                <p
                                    class="login-status"
                                    id="recovery-email-status"
                                    role="status"
                                    aria-live="polite"
                                ></p>
                            </div>
                        </div>

                        <div
                            class="panel-view"
                            id="recovery-code-view"
                            hidden
                            inert
                        >
                            <h2 class="login-card__title">
                                Revisa tu correo
                            </h2>
                            <p
                                class="login-card__subtitle"
                                id="recovery-code-description"
                            >
                                Escribe o pega el código de 6 números; se
                                verificará automáticamente.
                            </p>

                            <div
                                class="login-form"
                                role="group"
                                aria-label="Verificar código de recuperación"
                            >
                                <div class="field">
                                    <label for="recovery-code">
                                        Código de verificación
                                    </label>
                                    <input
                                        class="glass-input recovery-code-input"
                                        id="recovery-code"
                                        name="recovery_code"
                                        type="text"
                                        placeholder="000000"
                                        inputmode="numeric"
                                        autocomplete="one-time-code"
                                        maxlength="6"
                                        pattern="[0-9]{6}"
                                    >
                                </div>

                                <button
                                    class="glass-button login-submit"
                                    id="verify-recovery-code"
                                    type="button"
                                >
                                    Verificar código
                                </button>

                                <div class="panel-links">
                                    <button
                                        class="panel-link"
                                        id="change-recovery-email"
                                        type="button"
                                    >
                                        Usar otro correo
                                    </button>
                                </div>

                                <p
                                    class="login-status"
                                    id="recovery-code-status"
                                    role="status"
                                    aria-live="polite"
                                ></p>
                            </div>
                        </div>

                        <div
                            class="panel-view"
                            id="recovery-password-view"
                            hidden
                            inert
                        >
                            <h2 class="login-card__title">
                                Nueva contraseña
                            </h2>
                            <p class="login-card__subtitle">
                                Crea una contraseña segura y repítela.
                            </p>

                            <div
                                class="login-form"
                                role="group"
                                aria-label="Crear una nueva contraseña"
                            >
                                <div class="field">
                                    <label for="new-password">
                                        Contraseña nueva
                                    </label>
                                    <div class="password-control">
                                        <input
                                            class="glass-input password-entry"
                                            id="new-password"
                                            name="manual_new_key"
                                            type="text"
                                            placeholder="Escribe la contraseña"
                                            autocomplete="one-time-code"
                                            autocapitalize="none"
                                            spellcheck="false"
                                            data-lpignore="true"
                                            data-1p-ignore
                                            data-bwignore="true"
                                            maxlength="128"
                                            readonly
                                        >
                                        <button
                                            class="password-peek"
                                            id="new-password-peek"
                                            type="button"
                                            aria-label="Mantener presionado para mostrar la contraseña nueva"
                                            aria-pressed="false"
                                        >
                                            <span aria-hidden="true">👁</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="field">
                                    <label for="new-password-confirmation">
                                        Repite la contraseña
                                    </label>
                                    <div class="password-control">
                                        <input
                                            class="glass-input password-entry"
                                            id="new-password-confirmation"
                                            name="manual_new_key_confirmation"
                                            type="text"
                                            placeholder="Repite la contraseña"
                                            autocomplete="one-time-code"
                                            autocapitalize="none"
                                            spellcheck="false"
                                            data-lpignore="true"
                                            data-1p-ignore
                                            data-bwignore="true"
                                            maxlength="128"
                                            readonly
                                        >
                                        <button
                                            class="password-peek"
                                            id="new-password-confirmation-peek"
                                            type="button"
                                            aria-label="Mantener presionado para mostrar la confirmación"
                                            aria-pressed="false"
                                        >
                                            <span aria-hidden="true">👁</span>
                                        </button>
                                    </div>
                                </div>

                                <ul
                                    class="password-rules"
                                    id="password-rules"
                                    aria-label="Requisitos de la contraseña"
                                >
                                    <li data-password-rule="noSpaces">
                                        Sin espacios
                                    </li>
                                    <li data-password-rule="lowercase">
                                        Una minúscula
                                    </li>
                                    <li data-password-rule="uppercase">
                                        Una mayúscula
                                    </li>
                                    <li data-password-rule="number">
                                        Un número
                                    </li>
                                    <li data-password-rule="symbol">
                                        Un símbolo
                                    </li>
                                    <li data-password-rule="matches">
                                        Ambas coinciden
                                    </li>
                                </ul>

                                <button
                                    class="glass-button login-submit"
                                    id="reset-password"
                                    type="button"
                                >
                                    Guardar contraseña
                                </button>

                                <div class="panel-links">
                                    <button
                                        class="panel-link"
                                        id="reset-back-to-login"
                                        type="button"
                                        hidden
                                    >
                                        Volver al login
                                    </button>
                                </div>

                                <p
                                    class="login-status"
                                    id="reset-password-status"
                                    role="status"
                                    aria-live="polite"
                                ></p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <script>
            const access = document.getElementById('access');
            const accessActions = document.getElementById('access-actions');
            const panel = document.getElementById('login-panel');
            const loginToggle = document.getElementById('login-toggle');
            const closeButton = document.getElementById('login-close');
            const loginView = document.getElementById('login-view');
            const sessionTakeoverView = document.getElementById(
                'session-takeover-view',
            );
            const sessionTakeoverPasswordView = document.getElementById(
                'session-takeover-password-view',
            );
            const recoveryEmailView =
                document.getElementById('recovery-email-view');
            const recoveryCodeView =
                document.getElementById('recovery-code-view');
            const recoveryPasswordView =
                document.getElementById('recovery-password-view');
            const panelViews = [
                loginView,
                sessionTakeoverView,
                sessionTakeoverPasswordView,
                recoveryEmailView,
                recoveryCodeView,
                recoveryPasswordView,
            ];
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const passwordPeek = document.getElementById('password-peek');
            const loginSubmit = document.getElementById('login-submit');
            const loginStatus = document.getElementById('login-status');
            const acceptSessionTakeover = document.getElementById(
                'accept-session-takeover',
            );
            const cancelSessionTakeover = document.getElementById(
                'cancel-session-takeover',
            );
            const sessionTakeoverPassword = document.getElementById(
                'session-takeover-password',
            );
            const sessionTakeoverPasswordPeek = document.getElementById(
                'session-takeover-password-peek',
            );
            const confirmSessionTakeover = document.getElementById(
                'confirm-session-takeover',
            );
            const backToSessionTakeover = document.getElementById(
                'back-to-session-takeover',
            );
            const sessionTakeoverStatus = document.getElementById(
                'session-takeover-status',
            );
            const forgotPassword =
                document.getElementById('forgot-password');
            const recoveryEmailInput =
                document.getElementById('recovery-email');
            const sendRecoveryCode =
                document.getElementById('send-recovery-code');
            const recoveryEmailStatus =
                document.getElementById('recovery-email-status');
            const backToLogin = document.getElementById('back-to-login');
            const recoveryCodeInput =
                document.getElementById('recovery-code');
            const verifyRecoveryCode =
                document.getElementById('verify-recovery-code');
            const recoveryCodeStatus =
                document.getElementById('recovery-code-status');
            const recoveryCodeDescription =
                document.getElementById('recovery-code-description');
            const changeRecoveryEmail =
                document.getElementById('change-recovery-email');
            const newPasswordInput =
                document.getElementById('new-password');
            const newPasswordConfirmationInput =
                document.getElementById('new-password-confirmation');
            const newPasswordPeek =
                document.getElementById('new-password-peek');
            const newPasswordConfirmationPeek = document.getElementById(
                'new-password-confirmation-peek',
            );
            const newPasswordRevealControls = [
                {
                    input: newPasswordInput,
                    button: newPasswordPeek,
                },
                {
                    input: newPasswordConfirmationInput,
                    button: newPasswordConfirmationPeek,
                },
            ];
            const passwordRuleItems = document.querySelectorAll(
                '[data-password-rule]',
            );
            const resetPasswordButton =
                document.getElementById('reset-password');
            const resetPasswordStatus =
                document.getElementById('reset-password-status');
            const resetBackToLogin =
                document.getElementById('reset-back-to-login');
            const csrfToken = document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute('content');
            const historySessionKey = 'protected-session-closed';
            const tabAuthorizationKey = 'protected-tab-authorized';
            const reloadAuthorizationKey = 'protected-reload-pending';
            const internalNavigationKey = 'protected-internal-navigation';
            let historyLogoutPromise = Promise.resolve();
            let pendingTakeoverEmail = '';

            sessionStorage.removeItem(tabAuthorizationKey);
            sessionStorage.removeItem(reloadAuthorizationKey);
            sessionStorage.removeItem(internalNavigationKey);

            const setStatus = (element, message = '', type = '') => {
                element.textContent = message;
                element.classList.toggle('is-error', type === 'error');
                element.classList.toggle('is-success', type === 'success');
            };

            const setButtonBusy = (button, busy, busyLabel) => {
                if (!button.dataset.defaultLabel) {
                    button.dataset.defaultLabel = button.textContent.trim();
                }

                button.disabled = busy;
                button.textContent = busy
                    ? busyLabel
                    : button.dataset.defaultLabel;
            };

            const firstErrorMessage = (data) => {
                if (data?.message) {
                    return data.message;
                }

                const errors = Object.values(data?.errors ?? {}).flat();
                return errors[0] ?? 'No pudimos completar la solicitud.';
            };

            const postJson = async (url, payload) => {
                const response = await fetch(url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(payload),
                });
                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const error = new Error(firstErrorMessage(data));
                    error.data = data;
                    error.status = response.status;

                    throw error;
                }

                return data;
            };

            const showPanelView = (activeView, focusTarget = null) => {
                panelViews.forEach((view) => {
                    const isActive = view === activeView;
                    view.hidden = !isActive;
                    view.inert = !isActive;
                    view.classList.toggle('is-active', isActive);
                });

                if (focusTarget) {
                    window.setTimeout(() => focusTarget.focus(), 80);
                }
            };

            const showPassword = () => {
                passwordInput.classList.add('is-visible');
                passwordInput.style.webkitTextSecurity = 'none';
                passwordPeek.classList.add('is-revealing');
                passwordPeek.setAttribute('aria-pressed', 'true');
            };

            const hidePassword = () => {
                passwordInput.classList.remove('is-visible');
                passwordInput.style.webkitTextSecurity = 'disc';
                passwordPeek.classList.remove('is-revealing');
                passwordPeek.setAttribute('aria-pressed', 'false');
            };

            const hideSessionTakeoverPassword = () => {
                hideNewPassword(
                    sessionTakeoverPassword,
                    sessionTakeoverPasswordPeek,
                );
            };

            const showNewPassword = (input, button) => {
                input.classList.add('is-visible');
                input.style.webkitTextSecurity = 'none';
                button.classList.add('is-revealing');
                button.setAttribute('aria-pressed', 'true');
            };

            const hideNewPassword = (input, button) => {
                input.classList.remove('is-visible');
                input.style.webkitTextSecurity = 'disc';
                button.classList.remove('is-revealing');
                button.setAttribute('aria-pressed', 'false');
            };

            const hideNewPasswords = () => {
                newPasswordRevealControls.forEach(({ input, button }) => {
                    hideNewPassword(input, button);
                });
            };

            const lockEmailField = () => {
                emailInput.setAttribute('readonly', '');
            };

            const lockPasswordField = () => {
                passwordInput.setAttribute('readonly', '');
            };

            const lockNewPasswordFields = () => {
                newPasswordInput.setAttribute('readonly', '');
                newPasswordConfirmationInput.setAttribute('readonly', '');
            };

            const enableNewPasswordFields = () => {
                newPasswordRevealControls.forEach(({ input, button }) => {
                    input.disabled = false;
                    button.disabled = false;
                });
            };

            const disableNewPasswordFields = () => {
                hideNewPasswords();
                lockNewPasswordFields();

                newPasswordRevealControls.forEach(({ input, button }) => {
                    input.disabled = true;
                    button.disabled = true;
                });
            };

            const updatePasswordRules = () => {
                const password = newPasswordInput.value;
                const confirmation = newPasswordConfirmationInput.value;
                const rules = {
                    noSpaces:
                        password.length > 0 &&
                        !/\s/.test(password),
                    lowercase: /[a-z]/.test(password),
                    uppercase: /[A-Z]/.test(password),
                    number: /\d/.test(password),
                    symbol: /[^A-Za-z0-9\s]/.test(password),
                    matches:
                        password.length > 0 &&
                        password === confirmation,
                };

                passwordRuleItems.forEach((item) => {
                    item.classList.toggle(
                        'is-met',
                        rules[item.dataset.passwordRule] === true,
                    );
                });

                return Object.values(rules).every(Boolean);
            };

            const clearManualFields = () => {
                hidePassword();
                emailInput.value = '';
                passwordInput.value = '';
                passwordInput.dataset.lastManualValue = '';
                setStatus(loginStatus);
                lockEmailField();
                lockPasswordField();
            };

            const clearSessionTakeoverFields = () => {
                pendingTakeoverEmail = '';
                hideSessionTakeoverPassword();
                sessionTakeoverPassword.value = '';
                sessionTakeoverPassword.dataset.lastManualValue = '';
                sessionTakeoverPassword.setAttribute('readonly', '');
                setStatus(sessionTakeoverStatus);
                setButtonBusy(
                    confirmSessionTakeover,
                    false,
                    'Abriendo aquí…',
                );
            };

            const clearRecoveryFields = () => {
                recoveryEmailInput.value = '';
                recoveryCodeInput.value = '';
                recoveryCodeInput.readOnly = false;
                enableNewPasswordFields();
                hideNewPasswords();
                newPasswordInput.value = '';
                newPasswordConfirmationInput.value = '';
                newPasswordInput.dataset.lastManualValue = '';
                newPasswordConfirmationInput.dataset.lastManualValue = '';
                lockNewPasswordFields();
                recoveryCodeDescription.textContent =
                    'Escribe o pega el código de 6 números; se ' +
                    'verificará automáticamente.';
                setStatus(recoveryEmailStatus);
                setStatus(recoveryCodeStatus);
                setStatus(resetPasswordStatus);
                resetBackToLogin.hidden = true;
                setButtonBusy(
                    sendRecoveryCode,
                    false,
                    'Enviando código…',
                );
                setButtonBusy(
                    verifyRecoveryCode,
                    false,
                    'Verificando…',
                );
                setButtonBusy(
                    resetPasswordButton,
                    false,
                    'Guardando…',
                );
                updatePasswordRules();
            };

            const resetPageFields = () => {
                clearManualFields();
                clearSessionTakeoverFields();
                clearRecoveryFields();
                showPanelView(loginView);
            };

            const isHistoryNavigation = (event) => {
                const navigation = performance.getEntriesByType('navigation')[0];

                return event.persisted || navigation?.type === 'back_forward';
            };

            const resetPageFromHistory = (event) => {
                resetPageFields();

                if (!isHistoryNavigation(event)) {
                    return;
                }

                sessionStorage.setItem(historySessionKey, 'true');
                sessionStorage.removeItem(tabAuthorizationKey);
                sessionStorage.removeItem(reloadAuthorizationKey);
                sessionStorage.removeItem(internalNavigationKey);
                historyLogoutPromise = fetch(access.dataset.logoutUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    redirect: 'follow',
                    keepalive: true,
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                }).catch(() => {});
            };

            resetPageFields();
            window.addEventListener('pageshow', resetPageFromHistory);

            emailInput.addEventListener('focus', () => {
                emailInput.removeAttribute('readonly');
            });

            emailInput.addEventListener('blur', lockEmailField);

            passwordInput.addEventListener('focus', () => {
                passwordInput.removeAttribute('readonly');
            });

            passwordInput.addEventListener('blur', lockPasswordField);

            sessionTakeoverPassword.addEventListener('focus', () => {
                sessionTakeoverPassword.removeAttribute('readonly');
            });

            sessionTakeoverPassword.addEventListener('blur', () => {
                sessionTakeoverPassword.setAttribute('readonly', '');
            });

            [newPasswordInput, newPasswordConfirmationInput].forEach(
                (input) => {
                    input.addEventListener('focus', () => {
                        if (!input.disabled) {
                            input.removeAttribute('readonly');
                        }
                    });

                    input.addEventListener('blur', () => {
                        input.setAttribute('readonly', '');
                    });
                },
            );

            const setLoginOpen = (open, focusField = false) => {
                access.classList.toggle('is-login-open', open);
                accessActions.setAttribute('aria-hidden', String(open));
                accessActions.inert = open;
                panel.classList.toggle('is-open', open);
                panel.setAttribute('aria-hidden', String(!open));
                panel.inert = !open;
                loginToggle.setAttribute('aria-expanded', String(open));

                if (!open) {
                    resetPageFields();
                } else {
                    showPanelView(
                        loginView,
                        focusField ? emailInput : null,
                    );
                }
            };

            loginToggle.addEventListener('click', () => {
                const willOpen = !panel.classList.contains('is-open');
                setLoginOpen(willOpen, willOpen);
            });

            closeButton.addEventListener('click', () => {
                setLoginOpen(false);
                loginToggle.focus();
            });

            document.addEventListener('keydown', (event) => {
                if (
                    event.key === 'Escape' &&
                    panel.classList.contains('is-open')
                ) {
                    setLoginOpen(false);
                    loginToggle.focus();
                }
            });

            forgotPassword.addEventListener('click', () => {
                recoveryEmailInput.value = emailInput.value.trim();
                setStatus(recoveryEmailStatus);
                showPanelView(recoveryEmailView, recoveryEmailInput);
            });

            backToLogin.addEventListener('click', () => {
                showPanelView(loginView, emailInput);
            });

            changeRecoveryEmail.addEventListener('click', () => {
                recoveryCodeInput.value = '';
                recoveryCodeInput.readOnly = false;
                setStatus(recoveryCodeStatus);
                showPanelView(recoveryEmailView, recoveryEmailInput);
            });

            acceptSessionTakeover.addEventListener('click', () => {
                sessionTakeoverPassword.value = '';
                sessionTakeoverPassword.dataset.lastManualValue = '';
                setStatus(sessionTakeoverStatus);
                showPanelView(
                    sessionTakeoverPasswordView,
                    sessionTakeoverPassword,
                );
            });

            cancelSessionTakeover.addEventListener('click', () => {
                clearSessionTakeoverFields();
                passwordInput.value = '';
                passwordInput.dataset.lastManualValue = '';
                showPanelView(loginView, passwordInput);
            });

            backToSessionTakeover.addEventListener('click', () => {
                hideSessionTakeoverPassword();
                sessionTakeoverPassword.value = '';
                sessionTakeoverPassword.dataset.lastManualValue = '';
                setStatus(sessionTakeoverStatus);
                showPanelView(sessionTakeoverView, acceptSessionTakeover);
            });

            const requestRecoveryCode = async () => {
                const email = recoveryEmailInput.value.trim();

                if (!email) {
                    setStatus(
                        recoveryEmailStatus,
                        'Escribe tu correo electrónico.',
                        'error',
                    );
                    recoveryEmailInput.focus();
                    return;
                }

                setStatus(recoveryEmailStatus);
                setButtonBusy(
                    sendRecoveryCode,
                    true,
                    'Enviando código…',
                );

                try {
                    await historyLogoutPromise;
                    const data = await postJson(
                        access.dataset.recoveryCodeUrl,
                        { email },
                    );

                    if (
                        data.registered !== true ||
                        data.code_sent !== true
                    ) {
                        throw new Error(
                            'El correo no fue confirmado. Inténtalo nuevamente.',
                        );
                    }

                    recoveryCodeDescription.textContent =
                        `Enviamos un código a ${data.masked_email}. ` +
                        'Se verificará automáticamente y vence en 10 minutos.';
                    recoveryCodeInput.value = '';
                    recoveryCodeInput.readOnly = false;
                    showPanelView(
                        recoveryCodeView,
                        recoveryCodeInput,
                    );
                } catch (error) {
                    setStatus(
                        recoveryEmailStatus,
                        error.message,
                        'error',
                    );
                } finally {
                    setButtonBusy(
                        sendRecoveryCode,
                        false,
                        'Enviando código…',
                    );
                }
            };

            const submitRecoveryCode = async () => {
                const code = recoveryCodeInput.value.trim();

                if (!/^\d{6}$/.test(code)) {
                    setStatus(
                        recoveryCodeStatus,
                        'Escribe los 6 números del código.',
                        'error',
                    );
                    recoveryCodeInput.focus();
                    return;
                }

                setStatus(recoveryCodeStatus);
                setButtonBusy(
                    verifyRecoveryCode,
                    true,
                    'Verificando…',
                );

                try {
                    await historyLogoutPromise;
                    const data = await postJson(
                        access.dataset.recoveryVerifyUrl,
                        { code },
                    );
                    recoveryCodeInput.readOnly = true;
                    enableNewPasswordFields();
                    newPasswordInput.value = '';
                    newPasswordConfirmationInput.value = '';
                    newPasswordInput.dataset.lastManualValue = '';
                    newPasswordConfirmationInput.dataset.lastManualValue = '';
                    lockNewPasswordFields();
                    updatePasswordRules();
                    setStatus(resetPasswordStatus);
                    showPanelView(
                        recoveryPasswordView,
                        newPasswordInput,
                    );
                } catch (error) {
                    setStatus(
                        recoveryCodeStatus,
                        error.message,
                        'error',
                    );
                    setButtonBusy(
                        verifyRecoveryCode,
                        false,
                        'Verificando…',
                    );
                }
            };

            sendRecoveryCode.addEventListener(
                'click',
                requestRecoveryCode,
            );
            verifyRecoveryCode.addEventListener(
                'click',
                submitRecoveryCode,
            );

            recoveryEmailInput.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    requestRecoveryCode();
                }
            });

            recoveryCodeInput.addEventListener('input', () => {
                recoveryCodeInput.value = recoveryCodeInput.value
                    .replace(/\D/g, '')
                    .slice(0, 6);
                setStatus(recoveryCodeStatus);

                if (
                    recoveryCodeInput.value.length === 6 &&
                    !verifyRecoveryCode.disabled
                ) {
                    submitRecoveryCode();
                }
            });

            recoveryCodeInput.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    submitRecoveryCode();
                }
            });

            const submitNewPassword = async () => {
                if (!updatePasswordRules()) {
                    setStatus(
                        resetPasswordStatus,
                        'Cumple todos los requisitos y confirma la contraseña.',
                        'error',
                    );
                    newPasswordInput.focus();
                    return;
                }

                setStatus(resetPasswordStatus);
                setButtonBusy(
                    resetPasswordButton,
                    true,
                    'Guardando…',
                );

                try {
                    const data = await postJson(
                        access.dataset.recoveryResetUrl,
                        {
                            password: newPasswordInput.value,
                            password_confirmation:
                                newPasswordConfirmationInput.value,
                        },
                    );
                    disableNewPasswordFields();
                    setStatus(
                        resetPasswordStatus,
                        data.message,
                        'success',
                    );
                    resetPasswordButton.textContent =
                        'Contraseña actualizada';
                    resetPasswordButton.disabled = true;
                    resetBackToLogin.hidden = false;
                } catch (error) {
                    setStatus(
                        resetPasswordStatus,
                        error.message,
                        'error',
                    );
                    setButtonBusy(
                        resetPasswordButton,
                        false,
                        'Guardando…',
                    );
                }
            };

            [newPasswordInput, newPasswordConfirmationInput].forEach(
                (input) => {
                    input.addEventListener('input', (event) => {
                        if (
                            [
                                'insertFromPaste',
                                'insertFromDrop',
                                'insertReplacementText',
                                'insertFromYank',
                            ].includes(event.inputType)
                        ) {
                            input.value =
                                input.dataset.lastManualValue ?? '';
                            blockNewPasswordPaste(event);
                            updatePasswordRules();
                            return;
                        }

                        input.dataset.lastManualValue = input.value;
                        updatePasswordRules();
                        setStatus(resetPasswordStatus);
                    });

                    input.addEventListener('keydown', (event) => {
                        if (
                            ((event.ctrlKey || event.metaKey) &&
                                event.key.toLowerCase() === 'v') ||
                            (event.shiftKey && event.key === 'Insert')
                        ) {
                            blockNewPasswordPaste(event);
                            return;
                        }

                        if (event.key === 'Enter') {
                            event.preventDefault();
                            submitNewPassword();
                        }
                    });
                },
            );

            const blockNewPasswordPaste = (event) => {
                event.preventDefault();
                setStatus(
                    resetPasswordStatus,
                    'Por seguridad, escribe la contraseña manualmente.',
                    'error',
                );
                event.currentTarget.focus();
            };

            [newPasswordInput, newPasswordConfirmationInput].forEach(
                (input) => {
                    input.addEventListener(
                        'paste',
                        blockNewPasswordPaste,
                    );
                    input.addEventListener(
                        'drop',
                        blockNewPasswordPaste,
                    );
                    input.addEventListener('beforeinput', (event) => {
                        if (
                            [
                                'insertFromPaste',
                                'insertFromDrop',
                                'insertReplacementText',
                                'insertFromYank',
                            ].includes(event.inputType)
                        ) {
                            blockNewPasswordPaste(event);
                        }
                    });
                },
            );

            resetPasswordButton.addEventListener(
                'click',
                submitNewPassword,
            );

            resetBackToLogin.addEventListener('click', () => {
                clearRecoveryFields();
                clearManualFields();
                showPanelView(loginView, emailInput);
            });

            newPasswordRevealControls.forEach(({ input, button }) => {
                button.addEventListener('pointerdown', (event) => {
                    event.preventDefault();

                    if (button.setPointerCapture) {
                        try {
                            button.setPointerCapture(event.pointerId);
                        } catch {
                            // El puntero puede haber terminado antes.
                        }
                    }

                    showNewPassword(input, button);
                });

                button.addEventListener('pointerup', (event) => {
                    hideNewPassword(input, button);

                    if (
                        button.hasPointerCapture?.(event.pointerId)
                    ) {
                        button.releasePointerCapture(event.pointerId);
                    }
                });
                button.addEventListener('pointercancel', () => {
                    hideNewPassword(input, button);
                });
                button.addEventListener('lostpointercapture', () => {
                    hideNewPassword(input, button);
                });
                button.addEventListener('blur', () => {
                    hideNewPassword(input, button);
                });
                button.addEventListener('contextmenu', (event) => {
                    event.preventDefault();
                });

                button.addEventListener('keydown', (event) => {
                    if (event.key === ' ' || event.key === 'Enter') {
                        event.preventDefault();
                        showNewPassword(input, button);
                    }
                });

                button.addEventListener('keyup', (event) => {
                    if (event.key === ' ' || event.key === 'Enter') {
                        hideNewPassword(input, button);
                    }
                });
            });

            sessionTakeoverPasswordPeek.addEventListener(
                'pointerdown',
                (event) => {
                    event.preventDefault();

                    if (sessionTakeoverPasswordPeek.setPointerCapture) {
                        try {
                            sessionTakeoverPasswordPeek.setPointerCapture(
                                event.pointerId,
                            );
                        } catch {
                            // El puntero puede haber terminado antes.
                        }
                    }

                    showNewPassword(
                        sessionTakeoverPassword,
                        sessionTakeoverPasswordPeek,
                    );
                },
            );
            sessionTakeoverPasswordPeek.addEventListener(
                'pointerup',
                (event) => {
                    hideSessionTakeoverPassword();

                    if (
                        sessionTakeoverPasswordPeek.hasPointerCapture?.(
                            event.pointerId,
                        )
                    ) {
                        sessionTakeoverPasswordPeek.releasePointerCapture(
                            event.pointerId,
                        );
                    }
                },
            );
            sessionTakeoverPasswordPeek.addEventListener(
                'pointercancel',
                hideSessionTakeoverPassword,
            );
            sessionTakeoverPasswordPeek.addEventListener(
                'lostpointercapture',
                hideSessionTakeoverPassword,
            );
            sessionTakeoverPasswordPeek.addEventListener(
                'blur',
                hideSessionTakeoverPassword,
            );
            sessionTakeoverPasswordPeek.addEventListener(
                'contextmenu',
                (event) => {
                    event.preventDefault();
                },
            );
            sessionTakeoverPasswordPeek.addEventListener(
                'keydown',
                (event) => {
                    if (event.key === ' ' || event.key === 'Enter') {
                        event.preventDefault();
                        showNewPassword(
                            sessionTakeoverPassword,
                            sessionTakeoverPasswordPeek,
                        );
                    }
                },
            );
            sessionTakeoverPasswordPeek.addEventListener(
                'keyup',
                (event) => {
                    if (event.key === ' ' || event.key === 'Enter') {
                        hideSessionTakeoverPassword();
                    }
                },
            );

            passwordPeek.addEventListener('pointerdown', (event) => {
                event.preventDefault();

                if (passwordPeek.setPointerCapture) {
                    try {
                        passwordPeek.setPointerCapture(event.pointerId);
                    } catch {
                        // El puntero puede haber terminado antes.
                    }
                }

                showPassword();
            });

            document.addEventListener('pointerup', hidePassword);
            document.addEventListener('pointerup', hideNewPasswords);
            document.addEventListener(
                'pointerup',
                hideSessionTakeoverPassword,
            );
            passwordPeek.addEventListener('pointerup', (event) => {
                hidePassword();

                if (
                    passwordPeek.hasPointerCapture?.(event.pointerId)
                ) {
                    passwordPeek.releasePointerCapture(event.pointerId);
                }
            });
            passwordPeek.addEventListener('pointercancel', hidePassword);
            passwordPeek.addEventListener('lostpointercapture', hidePassword);
            passwordPeek.addEventListener('blur', hidePassword);
            passwordPeek.addEventListener('contextmenu', (event) => {
                event.preventDefault();
            });

            passwordPeek.addEventListener('keydown', (event) => {
                if (event.key === ' ' || event.key === 'Enter') {
                    event.preventDefault();
                    showPassword();
                }
            });

            passwordPeek.addEventListener('keyup', (event) => {
                if (event.key === ' ' || event.key === 'Enter') {
                    hidePassword();
                }
            });

            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    hidePassword();
                    hideNewPasswords();
                    hideSessionTakeoverPassword();
                }
            });

            const blockPasswordPaste = (event) => {
                event.preventDefault();
                setStatus(
                    loginStatus,
                    'Por seguridad, escribe la contraseña manualmente.',
                    'error',
                );
                passwordInput.focus();
            };

            passwordInput.addEventListener('paste', blockPasswordPaste);
            passwordInput.addEventListener('drop', blockPasswordPaste);
            passwordInput.addEventListener('keydown', (event) => {
                if (
                    ((event.ctrlKey || event.metaKey) &&
                        event.key.toLowerCase() === 'v') ||
                    (event.shiftKey && event.key === 'Insert')
                ) {
                    blockPasswordPaste(event);
                }
            });
            passwordInput.addEventListener('beforeinput', (event) => {
                if (
                    [
                        'insertFromPaste',
                        'insertFromDrop',
                        'insertReplacementText',
                        'insertFromYank',
                    ].includes(event.inputType)
                ) {
                    blockPasswordPaste(event);
                }
            });

            passwordInput.addEventListener('input', (event) => {
                if (
                    [
                        'insertFromPaste',
                        'insertFromDrop',
                        'insertReplacementText',
                        'insertFromYank',
                    ].includes(event.inputType)
                ) {
                    passwordInput.value =
                        passwordInput.dataset.lastManualValue ?? '';
                    blockPasswordPaste(event);
                    return;
                }

                passwordInput.dataset.lastManualValue =
                    passwordInput.value;

                if (
                    loginStatus.textContent ===
                    'Por seguridad, escribe la contraseña manualmente.'
                ) {
                    setStatus(loginStatus);
                }
            });

            const blockSessionTakeoverPasswordPaste = (event) => {
                event.preventDefault();
                setStatus(
                    sessionTakeoverStatus,
                    'Por seguridad, escribe la contraseña manualmente.',
                    'error',
                );
                sessionTakeoverPassword.focus();
            };

            sessionTakeoverPassword.addEventListener(
                'paste',
                blockSessionTakeoverPasswordPaste,
            );
            sessionTakeoverPassword.addEventListener(
                'drop',
                blockSessionTakeoverPasswordPaste,
            );
            sessionTakeoverPassword.addEventListener('keydown', (event) => {
                if (
                    ((event.ctrlKey || event.metaKey) &&
                        event.key.toLowerCase() === 'v') ||
                    (event.shiftKey && event.key === 'Insert')
                ) {
                    blockSessionTakeoverPasswordPaste(event);
                }
            });
            sessionTakeoverPassword.addEventListener(
                'beforeinput',
                (event) => {
                    if (
                        [
                            'insertFromPaste',
                            'insertFromDrop',
                            'insertReplacementText',
                            'insertFromYank',
                        ].includes(event.inputType)
                    ) {
                        blockSessionTakeoverPasswordPaste(event);
                    }
                },
            );
            sessionTakeoverPassword.addEventListener('input', (event) => {
                if (
                    [
                        'insertFromPaste',
                        'insertFromDrop',
                        'insertReplacementText',
                        'insertFromYank',
                    ].includes(event.inputType)
                ) {
                    sessionTakeoverPassword.value =
                        sessionTakeoverPassword.dataset.lastManualValue ?? '';
                    blockSessionTakeoverPasswordPaste(event);
                    return;
                }

                sessionTakeoverPassword.dataset.lastManualValue =
                    sessionTakeoverPassword.value;

                if (
                    sessionTakeoverStatus.textContent ===
                    'Por seguridad, escribe la contraseña manualmente.'
                ) {
                    setStatus(sessionTakeoverStatus);
                }
            });

            const submitLogin = async () => {
                const email = emailInput.value.trim();

                if (!email || !passwordInput.value) {
                    setStatus(
                        loginStatus,
                        'Completa el correo y la contraseña.',
                        'error',
                    );
                    return;
                }

                setStatus(loginStatus);
                setButtonBusy(
                    loginSubmit,
                    true,
                    'Ingresando…',
                );

                try {
                    await historyLogoutPromise;
                    const data = await postJson(
                        access.dataset.loginUrl,
                        {
                            email,
                            password: passwordInput.value,
                        },
                    );
                    setStatus(
                        loginStatus,
                        data.message,
                        'success',
                    );
                    sessionStorage.removeItem(historySessionKey);
                    sessionStorage.setItem(tabAuthorizationKey, 'true');
                    window.location.assign(data.redirect_url);
                } catch (error) {
                    if (error.data?.requires_takeover === true) {
                        pendingTakeoverEmail = email;
                        hidePassword();
                        passwordInput.value = '';
                        passwordInput.dataset.lastManualValue = '';
                        setButtonBusy(
                            loginSubmit,
                            false,
                            'Ingresando…',
                        );
                        showPanelView(
                            sessionTakeoverView,
                            acceptSessionTakeover,
                        );
                        return;
                    }

                    setStatus(
                        loginStatus,
                        error.message,
                        'error',
                    );
                    setButtonBusy(
                        loginSubmit,
                        false,
                        'Ingresando…',
                    );
                }
            };

            const submitSessionTakeover = async () => {
                if (!pendingTakeoverEmail) {
                    clearSessionTakeoverFields();
                    showPanelView(loginView, emailInput);
                    return;
                }

                if (!sessionTakeoverPassword.value) {
                    setStatus(
                        sessionTakeoverStatus,
                        'Escribe nuevamente tu contraseña.',
                        'error',
                    );
                    sessionTakeoverPassword.focus();
                    return;
                }

                setStatus(sessionTakeoverStatus);
                setButtonBusy(
                    confirmSessionTakeover,
                    true,
                    'Abriendo aquí…',
                );

                try {
                    await historyLogoutPromise;
                    const data = await postJson(
                        access.dataset.loginUrl,
                        {
                            email: pendingTakeoverEmail,
                            password: sessionTakeoverPassword.value,
                            takeover: true,
                        },
                    );
                    setStatus(
                        sessionTakeoverStatus,
                        'Sesión trasladada correctamente.',
                        'success',
                    );
                    sessionStorage.removeItem(historySessionKey);
                    sessionStorage.setItem(tabAuthorizationKey, 'true');
                    window.location.assign(data.redirect_url);
                } catch (error) {
                    setStatus(
                        sessionTakeoverStatus,
                        error.message,
                        'error',
                    );
                    setButtonBusy(
                        confirmSessionTakeover,
                        false,
                        'Abriendo aquí…',
                    );
                    sessionTakeoverPassword.focus();
                    sessionTakeoverPassword.select();
                }
            };

            loginSubmit.addEventListener('click', submitLogin);
            confirmSessionTakeover.addEventListener(
                'click',
                submitSessionTakeover,
            );

            [emailInput, passwordInput].forEach((input) => {
                input.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        submitLogin();
                    }
                });
            });

            sessionTakeoverPassword.addEventListener(
                'keydown',
                (event) => {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        submitSessionTakeover();
                    }
                },
            );

            const showReplacedSessionMessage = () => {
                if (access.dataset.sessionReplaced !== 'true') {
                    return;
                }

                setLoginOpen(true);
                setStatus(
                    loginStatus,
                    'La sesión anterior se cerró porque la cuenta se abrió en otro lugar.',
                    'error',
                );
            };

            showReplacedSessionMessage();
            window.addEventListener('pageshow', showReplacedSessionMessage);
        </script>
    </body>
</html>

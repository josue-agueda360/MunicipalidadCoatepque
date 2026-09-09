<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Monitoreo | Municipalidad de Coatepeque</title>
        <script>
            try {
                document.documentElement.classList.toggle(
                    'light-mode',
                    localStorage.getItem('municipal_portal_theme') === 'light',
                );
            } catch (error) {
                document.documentElement.classList.remove('light-mode');
            }
        </script>

        <style>
            :root {
                color-scheme: dark;
                --page: #050606;
                --surface: #101719;
                --surface-strong: #0b1012;
                --surface-soft: rgba(255, 255, 255, 0.045);
                --line: rgba(255, 255, 255, 0.12);
                --line-blue: rgba(49, 169, 214, 0.34);
                --text: #f6f2e7;
                --muted: #aaa99f;
                --gold: #d5b26d;
                --blue: #136b91;
                --blue-bright: #31a9d6;
                --green: #71d59b;
                --danger: #ee8585;
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
                        circle at 76% 8%,
                        rgba(49, 169, 214, 0.1),
                        transparent 34rem
                    ),
                    var(--page);
                font-family: "Segoe UI", Arial, Helvetica, sans-serif;
            }

            button,
            input,
            select,
            textarea {
                font: inherit;
            }

            button,
            a {
                -webkit-tap-highlight-color: transparent;
            }

            [hidden] {
                display: none !important;
            }

            .standalone-header {
                display: flex;
                min-height: 68px;
                padding: 12px clamp(18px, 4vw, 52px);
                align-items: center;
                justify-content: space-between;
                border-bottom: 1px solid var(--line-blue);
                background: rgba(9, 12, 13, 0.96);
            }

            .standalone-header strong {
                font-size: 0.92rem;
                letter-spacing: 0.06em;
                text-transform: uppercase;
            }

            .standalone-header a {
                padding: 10px 14px;
                border: 1px solid var(--line-blue);
                border-radius: 9px;
                color: var(--text);
                text-decoration: none;
            }

            .is-embedded .standalone-header {
                display: none;
            }

            .monitoring-module {
                width: min(1540px, 100%);
                min-height: calc(100vh - 68px);
                margin: 0 auto;
                padding: clamp(18px, 2.4vw, 34px);
            }

            .is-embedded .monitoring-module {
                width: 100%;
                min-height: 100vh;
                padding: clamp(16px, 2.1vw, 28px);
            }

            .module-intro {
                display: flex;
                margin-bottom: 20px;
                align-items: flex-end;
                justify-content: space-between;
                gap: 24px;
            }

            .eyebrow {
                margin: 0 0 7px;
                color: var(--gold);
                font-size: 0.72rem;
                font-weight: 850;
                letter-spacing: 0.13em;
                text-transform: uppercase;
            }

            h1,
            h2,
            p {
                margin-top: 0;
            }

            h1 {
                margin-bottom: 7px;
                font-size: clamp(1.45rem, 2.3vw, 2.15rem);
                line-height: 1.08;
            }

            .module-intro__copy {
                max-width: 760px;
                margin-bottom: 0;
                color: var(--muted);
                font-size: 0.92rem;
                line-height: 1.55;
            }

            .records-total {
                flex: 0 0 auto;
                min-width: 126px;
                padding: 12px 16px;
                border: 1px solid var(--line);
                border-radius: 12px;
                background: rgba(255, 255, 255, 0.035);
                color: var(--muted);
                font-size: 0.75rem;
                text-align: center;
            }

            .records-total strong {
                display: block;
                margin-bottom: 1px;
                color: var(--text);
                font-size: 1.35rem;
            }

            .monitoring-layout {
                display: grid;
                grid-template-columns: minmax(340px, 0.78fr) minmax(440px, 1.22fr);
                align-items: start;
                gap: 20px;
            }

            .is-read-only .monitoring-layout { grid-template-columns: minmax(0, 1fr); }
            .is-read-only .form-panel { display: none; }

            .panel {
                border: 1px solid var(--line);
                border-radius: 17px;
                background:
                    linear-gradient(
                        160deg,
                        rgba(49, 169, 214, 0.045),
                        transparent 42%
                    ),
                    rgba(14, 19, 21, 0.97);
                box-shadow:
                    0 22px 58px rgba(0, 0, 0, 0.3),
                    inset 0 1px 0 rgba(255, 255, 255, 0.035);
            }

            .form-panel {
                padding: clamp(18px, 2vw, 26px);
            }

            .panel-heading {
                display: flex;
                margin-bottom: 19px;
                align-items: center;
                gap: 12px;
            }

            .panel-heading__icon {
                display: inline-grid;
                width: 39px;
                height: 39px;
                flex: 0 0 auto;
                place-items: center;
                border: 1px solid rgba(49, 169, 214, 0.34);
                border-radius: 11px;
                color: #bdeeff;
                background: rgba(19, 107, 145, 0.22);
                font-size: 1.2rem;
                font-weight: 900;
            }

            .panel-heading h2 {
                margin: 0 0 3px;
                font-size: 1.08rem;
            }

            .panel-heading p {
                margin: 0;
                color: var(--muted);
                font-size: 0.78rem;
            }

            .field-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 15px;
            }

            .field {
                min-width: 0;
            }

            .field--wide {
                grid-column: 1 / -1;
            }

            label {
                display: block;
                margin-bottom: 7px;
                color: #e8e5dc;
                font-size: 0.77rem;
                font-weight: 760;
            }

            .required-mark {
                color: var(--blue-bright);
            }

            input,
            select,
            textarea {
                width: 100%;
                border: 1px solid rgba(255, 255, 255, 0.15);
                border-radius: 10px;
                outline: none;
                color: var(--text);
                background: rgba(5, 8, 9, 0.82);
                transition:
                    border-color 150ms ease,
                    box-shadow 150ms ease,
                    background-color 150ms ease;
            }

            input,
            select {
                min-height: 44px;
                padding: 0 12px;
            }

            textarea {
                min-height: 104px;
                padding: 11px 12px;
                line-height: 1.45;
                resize: vertical;
            }

            input::placeholder,
            textarea::placeholder {
                color: rgba(188, 183, 170, 0.55);
            }

            input:focus,
            select:focus,
            textarea:focus {
                border-color: rgba(49, 169, 214, 0.78);
                background: rgba(5, 10, 12, 0.96);
                box-shadow: 0 0 0 3px rgba(49, 169, 214, 0.13);
            }

            .is-invalid {
                border-color: rgba(238, 133, 133, 0.84) !important;
                box-shadow: 0 0 0 3px rgba(238, 133, 133, 0.11) !important;
            }

            select {
                cursor: pointer;
            }

            .autocomplete {
                position: relative;
            }

            .autocomplete-results {
                position: absolute;
                z-index: 30;
                top: calc(100% + 7px);
                right: 0;
                left: 0;
                max-height: 245px;
                padding: 6px;
                overflow-y: auto;
                border: 1px solid rgba(49, 169, 214, 0.38);
                border-radius: 11px;
                background: #0c1214;
                box-shadow: 0 22px 48px rgba(0, 0, 0, 0.58);
            }

            .autocomplete-option {
                display: grid;
                width: 100%;
                padding: 10px 11px;
                border: 0;
                border-radius: 8px;
                color: var(--text);
                background: transparent;
                cursor: pointer;
                text-align: left;
                gap: 3px;
            }

            .autocomplete-option:hover,
            .autocomplete-option.is-active,
            .autocomplete-option:focus-visible {
                outline: none;
                background: rgba(49, 169, 214, 0.13);
            }

            .autocomplete-option strong {
                overflow: hidden;
                font-size: 0.8rem;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .autocomplete-option span {
                color: var(--muted);
                font-size: 0.71rem;
            }

            .autocomplete-empty {
                padding: 11px;
                color: var(--muted);
                font-size: 0.76rem;
                line-height: 1.45;
            }

            .project-mode {
                min-height: 20px;
                margin: 6px 1px 0;
                color: var(--muted);
                font-size: 0.7rem;
                line-height: 1.4;
            }

            .project-mode.is-linked {
                color: var(--green);
            }

            .project-mode.is-standalone {
                color: var(--gold);
            }

            .form-footer {
                display: flex;
                margin-top: 19px;
                align-items: center;
                justify-content: space-between;
                gap: 14px;
            }

            .form-message {
                min-height: 20px;
                margin: 0;
                color: var(--muted);
                font-size: 0.73rem;
                line-height: 1.4;
            }

            .form-message.is-error {
                color: var(--danger);
            }

            .form-message.is-success {
                color: var(--green);
            }

            .save-button {
                display: inline-flex;
                min-width: 154px;
                min-height: 44px;
                padding: 0 18px;
                align-items: center;
                justify-content: center;
                gap: 8px;
                border: 1px solid rgba(49, 169, 214, 0.72);
                border-radius: 10px;
                color: #fff;
                background: linear-gradient(135deg, #197ca3, #10536f);
                cursor: pointer;
                font-weight: 800;
                box-shadow: 0 12px 24px rgba(4, 55, 75, 0.27);
            }

            .save-button:hover,
            .save-button:focus-visible {
                outline: none;
                filter: brightness(1.1);
                box-shadow:
                    0 0 0 3px rgba(49, 169, 214, 0.13),
                    0 14px 28px rgba(4, 55, 75, 0.34);
            }

            .save-button:disabled {
                cursor: wait;
                filter: grayscale(0.35);
                opacity: 0.7;
            }

            .records-panel {
                min-height: 430px;
                padding: clamp(18px, 2vw, 25px);
            }

            .records-panel__header {
                display: flex;
                margin-bottom: 15px;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
            }

            .records-panel__header h2 {
                margin: 0 0 4px;
                font-size: 1.08rem;
            }

            .records-panel__header p {
                margin: 0;
                color: var(--muted);
                font-size: 0.76rem;
            }

            .records-filter {
                display: flex;
                min-width: min(100%, 270px);
                align-items: flex-end;
                justify-content: flex-end;
                gap: 10px;
            }

            .records-filter__field {
                min-width: 190px;
            }

            .records-filter label {
                margin-bottom: 5px;
                color: var(--muted);
                font-size: 0.65rem;
                letter-spacing: 0.055em;
                text-transform: uppercase;
            }

            .records-filter select {
                min-height: 38px;
                border-color: rgba(49, 169, 214, 0.32);
                background: rgba(5, 11, 13, 0.9);
                font-size: 0.75rem;
                font-weight: 750;
            }

            .filtered-count {
                min-width: 33px;
                min-height: 33px;
                display: inline-grid;
                place-items: center;
                border: 1px solid var(--line);
                border-radius: 9px;
                color: var(--text);
                background: rgba(255, 255, 255, 0.035);
                font-size: 0.72rem;
                font-weight: 850;
            }

            .records-message {
                min-height: 18px;
                margin: -5px 0 10px !important;
                color: var(--green) !important;
                font-size: 0.7rem !important;
                text-align: right;
            }

            .records-message.is-error {
                color: var(--danger) !important;
            }

            .records-list {
                display: grid;
                max-height: min(640px, calc(100vh - 240px));
                padding-right: 5px;
                overflow-y: auto;
                gap: 10px;
                scrollbar-color: rgba(49, 169, 214, 0.45) transparent;
                scrollbar-width: thin;
            }

            .record-card {
                border: 1px solid rgba(255, 255, 255, 0.105);
                border-radius: 12px;
                background: rgba(255, 255, 255, 0.025);
                overflow: clip;
                transition:
                    border-color 150ms ease,
                    background-color 150ms ease;
            }

            .record-card[open] {
                border-color: rgba(49, 169, 214, 0.3);
                background: rgba(49, 169, 214, 0.045);
            }

            .record-card summary {
                display: grid;
                grid-template-columns: minmax(0, 1fr) auto;
                padding: 14px;
                align-items: center;
                cursor: pointer;
                list-style: none;
                gap: 14px;
            }

            .record-card summary::-webkit-details-marker {
                display: none;
            }

            .record-title {
                min-width: 0;
            }

            .record-title strong {
                display: block;
                overflow: hidden;
                font-size: 0.87rem;
                line-height: 1.35;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .record-card[open] summary {
                align-items: start;
            }

            .record-card[open] .record-title strong {
                overflow: visible;
                text-overflow: clip;
                white-space: normal;
                overflow-wrap: anywhere;
            }

            .record-title span {
                display: block;
                margin-top: 4px;
                overflow: hidden;
                color: var(--muted);
                font-size: 0.71rem;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .record-summary-meta {
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .status-badge,
            .status-select,
            .kind-badge,
            .link-badge {
                display: inline-flex;
                min-height: 25px;
                padding: 0 9px;
                align-items: center;
                border: 1px solid var(--line);
                border-radius: 999px;
                font-size: 0.65rem;
                font-weight: 800;
                white-space: nowrap;
            }

            .status-select {
                width: auto;
                max-width: 150px;
                min-height: 31px;
                padding: 0 27px 0 10px;
                border-radius: 999px;
                cursor: pointer;
                font-size: 0.68rem;
                font-weight: 850;
            }

            .status-select:focus {
                border-color: rgba(49, 169, 214, 0.72);
                box-shadow: 0 0 0 3px rgba(49, 169, 214, 0.12);
            }

            .status-select:disabled {
                cursor: wait;
                opacity: 0.58;
            }

            .status-planning {
                border-color: rgba(213, 178, 109, 0.34);
                color: #f2cd84;
                background: rgba(213, 178, 109, 0.09);
            }

            .status-in_progress {
                border-color: rgba(49, 169, 214, 0.38);
                color: #aee8ff;
                background: rgba(49, 169, 214, 0.1);
            }

            .status-finished {
                border-color: rgba(113, 213, 155, 0.38);
                color: #aef0c9;
                background: rgba(113, 213, 155, 0.09);
            }

            .status-closed {
                color: #ccc8be;
                background: rgba(255, 255, 255, 0.04);
            }

            .record-chevron {
                color: var(--blue-bright);
                font-size: 0.82rem;
                transition: transform 150ms ease;
            }

            .record-card[open] .record-chevron {
                transform: rotate(90deg);
            }

            .record-details {
                padding: 0 14px 15px;
                border-top: 1px solid rgba(255, 255, 255, 0.075);
            }

            .record-facts {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                margin: 13px 0;
                gap: 10px;
            }

            .record-fact {
                padding: 10px;
                border-radius: 9px;
                background: rgba(255, 255, 255, 0.035);
            }

            .record-fact span {
                display: block;
                margin-bottom: 4px;
                color: var(--muted);
                font-size: 0.62rem;
                letter-spacing: 0.055em;
                text-transform: uppercase;
            }

            .record-fact strong {
                font-size: 0.75rem;
                line-height: 1.35;
            }

            .record-description {
                margin: 0;
                color: #cfccc3;
                font-size: 0.76rem;
                line-height: 1.55;
                white-space: pre-wrap;
            }

            .record-badges {
                display: flex;
                margin-top: 12px;
                flex-wrap: wrap;
                gap: 7px;
            }

            .kind-badge {
                color: #e9e5dc;
            }

            .link-badge.is-linked {
                border-color: rgba(113, 213, 155, 0.32);
                color: var(--green);
            }

            .link-badge.is-standalone {
                border-color: rgba(213, 178, 109, 0.3);
                color: var(--gold);
            }

            .empty-state {
                display: grid;
                min-height: 280px;
                padding: 28px;
                place-items: center;
                border: 1px dashed rgba(49, 169, 214, 0.25);
                border-radius: 13px;
                color: var(--muted);
                text-align: center;
            }

            .empty-state span {
                display: block;
                margin-bottom: 10px;
                color: var(--blue-bright);
                font-size: 1.7rem;
            }

            .empty-state strong {
                display: block;
                margin-bottom: 5px;
                color: var(--text);
                font-size: 0.88rem;
            }

            .empty-state p {
                max-width: 320px;
                margin: 0;
                font-size: 0.76rem;
                line-height: 1.5;
            }

            html.light-mode {
                color-scheme: light;
                --page: #eef3f7;
                --surface: #ffffff;
                --surface-strong: #f5f8fa;
                --surface-soft: rgba(31, 57, 79, 0.05);
                --line: rgba(31, 57, 79, 0.16);
                --line-blue: rgba(19, 107, 145, 0.28);
                --text: #172033;
                --muted: #66717e;
                --gold: #a36d13;
            }

            html.light-mode body {
                color: var(--text);
                background:
                    radial-gradient(
                        circle at 74% 8%,
                        rgba(49, 169, 214, 0.13),
                        transparent 34rem
                    ),
                    var(--page);
            }

            html.light-mode .standalone-header,
            html.light-mode .panel {
                border-color: rgba(31, 57, 79, 0.16);
                color: #172033;
                background: rgba(255, 255, 255, 0.97);
                box-shadow:
                    0 22px 55px rgba(31, 57, 79, 0.14),
                    inset 0 1px 0 #fff;
            }

            html.light-mode input,
            html.light-mode select,
            html.light-mode textarea {
                border-color: #d6e0e8;
                color: #172033;
                background: #f8fafb;
            }

            html.light-mode input::placeholder,
            html.light-mode textarea::placeholder {
                color: #89939d;
            }

            html.light-mode label,
            html.light-mode .panel-heading h2,
            html.light-mode .records-panel__header h2,
            html.light-mode .record-title strong,
            html.light-mode .record-fact strong,
            html.light-mode .empty-state strong {
                color: #172033;
            }

            html.light-mode .autocomplete-results {
                border-color: rgba(19, 107, 145, 0.25);
                background: #fff;
                box-shadow: 0 20px 42px rgba(31, 57, 79, 0.2);
            }

            html.light-mode .autocomplete-option {
                color: #172033;
            }

            html.light-mode .record-card,
            html.light-mode .record-fact,
            html.light-mode .empty-state {
                border-color: rgba(31, 57, 79, 0.13);
                background: rgba(31, 57, 79, 0.035);
            }

            html.light-mode .record-card[open] {
                border-color: rgba(19, 107, 145, 0.24);
                background: rgba(49, 169, 214, 0.065);
            }

            html.light-mode .record-details {
                border-top-color: rgba(31, 57, 79, 0.1);
            }

            html.light-mode .record-description {
                color: #4f5c68;
            }

            html.light-mode .kind-badge,
            html.light-mode .status-closed {
                color: #46535f;
            }

            html.light-mode .records-total,
            html.light-mode .filtered-count {
                border-color: rgba(31, 57, 79, 0.14);
                background: rgba(255, 255, 255, 0.74);
            }

            @media (max-width: 980px) {
                .monitoring-layout {
                    grid-template-columns: 1fr;
                }

                .records-list {
                    max-height: 560px;
                }
            }

            @media (max-width: 620px) {
                .monitoring-module,
                .is-embedded .monitoring-module {
                    padding: 14px;
                }

                .module-intro {
                    align-items: flex-start;
                }

                .records-total {
                    min-width: 84px;
                }

                .field-grid,
                .record-facts {
                    grid-template-columns: 1fr;
                }

                .form-footer {
                    align-items: stretch;
                    flex-direction: column;
                }

                .records-panel__header {
                    align-items: stretch;
                    flex-direction: column;
                }

                .records-filter {
                    width: 100%;
                    justify-content: stretch;
                }

                .records-filter__field {
                    width: 100%;
                }

                .save-button {
                    width: 100%;
                }

                .status-select {
                    max-width: 128px;
                }
            }
        </style>
    </head>
    <body @class(['is-embedded' => request()->boolean('embed'), 'is-read-only' => $readOnly])>
        <header class="standalone-header">
            <strong>Monitoreo de proyectos</strong>
            <a href="{{ $readOnly ? route('public.portal') : route('home') }}" @unless($readOnly) data-protected-navigation @endunless>
                ← Volver al inicio
            </a>
        </header>

        <main class="monitoring-module">
            <section class="module-intro" aria-labelledby="monitoring-title">
                <div>
                    <p class="eyebrow">Control municipal</p>
                    <h1 id="monitoring-title">Monitoreo de proyectos</h1>
                </div>
                <div class="records-total" aria-live="polite">
                    <strong id="monitoring-count">{{ $records->count() }}</strong>
                    <span>registros</span>
                </div>
            </section>

            <div class="monitoring-layout">
                <section class="panel form-panel" aria-labelledby="form-title">
                    <div class="panel-heading">
                        <span class="panel-heading__icon" aria-hidden="true">＋</span>
                        <div>
                            <h2 id="form-title">Registro de proyecto</h2>
                            <p>Completa la información para agregarlo al monitoreo.</p>
                        </div>
                    </div>

                    <form id="monitoring-form" novalidate>
                        <div class="field-grid">
                            <div class="field field--wide">
                                <label for="project-name">
                                    Nombre del proyecto
                                    <span class="required-mark">*</span>
                                </label>
                                <div class="autocomplete">
                                    <input
                                        id="project-name"
                                        name="project_name"
                                        type="text"
                                        maxlength="150"
                                        placeholder="Escribe SNIP o nombre del proyecto"
                                        autocomplete="off"
                                        aria-autocomplete="list"
                                        aria-controls="project-suggestions"
                                        aria-expanded="false"
                                        required
                                    >
                                    <input id="project-id" name="project_id" type="hidden">
                                    <div
                                        class="autocomplete-results"
                                        id="project-suggestions"
                                        role="listbox"
                                        hidden
                                    ></div>
                                </div>
                                <p class="project-mode" id="project-mode">
                                    Selecciona una sugerencia para vincular un proyecto existente.
                                </p>
                            </div>

                            <div class="field">
                                <label for="project-status">
                                    Estado del proyecto
                                    <span class="required-mark">*</span>
                                </label>
                                <select id="project-status" name="status" required>
                                    <option value="">Selecciona un estado</option>
                                    @foreach ($statuses as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="field">
                                <label for="project-category">
                                    Categoría
                                    <span class="required-mark">*</span>
                                </label>
                                <select id="project-category" name="category" required>
                                    <option value="">Selecciona una categoría</option>
                                    @foreach ($categories as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="field">
                                <label for="starts-on">
                                    Fecha de inicio
                                    <span class="required-mark">*</span>
                                </label>
                                <input id="starts-on" name="starts_on" type="date" required>
                            </div>

                            <div class="field">
                                <label for="ends-on">
                                    Fecha de finalización
                                    <span class="required-mark">*</span>
                                </label>
                                <input id="ends-on" name="ends_on" type="date" required>
                            </div>

                            <div class="field field--wide">
                                <label for="project-location">
                                    Ubicación
                                    <span class="required-mark">*</span>
                                </label>
                                <input
                                    id="project-location"
                                    name="location"
                                    type="text"
                                    maxlength="150"
                                    placeholder="Ejemplo: Barrio La Esperanza"
                                    required
                                >
                            </div>

                            <div class="field field--wide">
                                <label for="project-description">
                                    Descripción del proyecto
                                </label>
                                <textarea
                                    id="project-description"
                                    name="description"
                                    maxlength="3000"
                                    placeholder="Escribe una descripción breve del proceso"
                                ></textarea>
                            </div>
                        </div>

                        <div class="form-footer">
                            <p
                                class="form-message"
                                id="form-message"
                                role="status"
                                aria-live="polite"
                            ></p>
                            <button class="save-button" id="save-monitoring" type="submit">
                                <span aria-hidden="true">✓</span>
                                Guardar registro
                            </button>
                        </div>
                    </form>
                </section>

                <section class="panel records-panel" aria-labelledby="records-title">
                    <div class="records-panel__header">
                        <div>
                            <h2 id="records-title">Registros guardados</h2>
                            <p>Abre un registro para consultar toda su información.</p>
                        </div>
                        <div class="records-filter">
                            <div class="records-filter__field">
                                <label for="monitoring-status-filter">
                                    Mostrar por estado
                                </label>
                                <select id="monitoring-status-filter">
                                    <option value="all">Todos los estados</option>
                                    @foreach ($statuses as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <span
                                class="filtered-count"
                                id="filtered-records-count"
                                title="Registros mostrados"
                            >{{ $records->count() }}</span>
                        </div>
                    </div>
                    <p
                        class="records-message"
                        id="records-message"
                        role="status"
                        aria-live="polite"
                    ></p>

                    <div class="records-list" id="monitoring-records">
                        @foreach ($records as $record)
                            <details
                                class="record-card"
                                data-record-id="{{ $record['id'] }}"
                                data-status="{{ $record['status'] }}"
                            >
                                <summary>
                                    <div class="record-title">
                                        <strong>{{ $record['project_name'] }}</strong>
                                        <span>
                                            @if ($record['snip'])
                                                SNIP {{ $record['snip'] }} ·
                                            @endif
                                            {{ $record['location'] }}
                                        </span>
                                    </div>
                                    <div class="record-summary-meta">
                                        <select
                                            class="status-select status-{{ $record['status'] }}"
                                            data-monitoring-status
                                            aria-label="Estado de {{ $record['project_name'] }}"
                                            @disabled($readOnly)
                                        >
                                            @foreach ($statuses as $value => $label)
                                                <option
                                                    value="{{ $value }}"
                                                    @selected($record['status'] === $value)
                                                >{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <span class="record-chevron" aria-hidden="true">›</span>
                                    </div>
                                </summary>
                                <div class="record-details">
                                    <div class="record-facts">
                                        <div class="record-fact">
                                            <span>Fecha de inicio</span>
                                            <strong>{{ $record['starts_on_label'] }}</strong>
                                        </div>
                                        <div class="record-fact">
                                            <span>Fecha de finalización</span>
                                            <strong>{{ $record['ends_on_label'] }}</strong>
                                        </div>
                                        <div class="record-fact">
                                            <span>Categoría</span>
                                            <strong>{{ $record['category_label'] }}</strong>
                                        </div>
                                        <div class="record-fact">
                                            <span>Ubicación</span>
                                            <strong>{{ $record['location'] }}</strong>
                                        </div>
                                    </div>
                                    <p class="record-description">{{ $record['description'] ?: 'Sin descripción registrada.' }}</p>
                                    <div class="record-badges">
                                        <span class="kind-badge">{{ $record['category_label'] }}</span>
                                        <span
                                            @class([
                                                'link-badge',
                                                'is-linked' => $record['linked'],
                                                'is-standalone' => ! $record['linked'],
                                            ])
                                        >
                                            {{ $record['linked']
                                                ? 'Vinculado a proyecto existente'
                                                : 'Solo en Monitoreo' }}
                                        </span>
                                    </div>
                                </div>
                            </details>
                        @endforeach

                        <div
                            class="empty-state"
                            id="monitoring-empty"
                            @if ($records->isNotEmpty()) hidden @endif
                        >
                            <div>
                                <span aria-hidden="true">◎</span>
                                <strong id="monitoring-empty-title">Aún no hay registros</strong>
                                 <p id="monitoring-empty-copy">
                                    {{ $readOnly
                                        ? 'Todavía no hay proyectos publicados en monitoreo.'
                                        : 'Completa el formulario y guarda el primer proyecto que deseas monitorear.' }}
                                 </p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main>

        <script>
            (() => {
                let availableProjects = @json($projects);
                const readOnly = @json($readOnly);
                const statusLabels = @json($statuses);
                const storeUrl = @json(route('project-monitoring.store'));
                const statusUpdateUrlTemplate = @json(route(
                    'project-monitoring.status',
                    ['projectMonitoring' => '__MONITORING_ID__'],
                ));
                const csrfToken = document.querySelector(
                    'meta[name="csrf-token"]',
                ).content;
                const form = document.getElementById('monitoring-form');
                const projectName = document.getElementById('project-name');
                const projectId = document.getElementById('project-id');
                const projectLocation = document.getElementById(
                    'project-location',
                );
                const suggestions = document.getElementById(
                    'project-suggestions',
                );
                const projectMode = document.getElementById('project-mode');
                const formMessage = document.getElementById('form-message');
                const saveButton = document.getElementById('save-monitoring');
                const recordsList = document.getElementById(
                    'monitoring-records',
                );
                const emptyState = document.getElementById('monitoring-empty');
                const emptyStateTitle = document.getElementById(
                    'monitoring-empty-title',
                );
                const emptyStateCopy = document.getElementById(
                    'monitoring-empty-copy',
                );
                const countElement = document.getElementById(
                    'monitoring-count',
                );
                const statusFilter = document.getElementById(
                    'monitoring-status-filter',
                );
                const filteredCount = document.getElementById(
                    'filtered-records-count',
                );
                const recordsMessage = document.getElementById(
                    'records-message',
                );
                let activeSuggestion = -1;
                let visibleSuggestions = [];

                const normalizeText = (value) => String(value ?? '')
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .toLocaleLowerCase('es')
                    .trim();

                const projectDisplayName = (project) => project.snip
                    ? `${project.snip}-${project.name}`
                    : project.name;

                const setProjectMode = (mode, text) => {
                    projectMode.className = 'project-mode';

                    if (mode) {
                        projectMode.classList.add(mode);
                    }

                    projectMode.textContent = text;
                };

                const closeSuggestions = () => {
                    suggestions.hidden = true;
                    projectName.setAttribute('aria-expanded', 'false');
                    activeSuggestion = -1;
                };

                const selectProject = (project) => {
                    projectId.value = String(project.id);
                    projectName.value = projectDisplayName(project);

                    if (!projectLocation.value.trim() && project.place) {
                        projectLocation.value = project.place;
                    }

                    setProjectMode(
                        'is-linked',
                        `Vinculado al proyecto existente${project.snip
                            ? ` SNIP ${project.snip}`
                            : ''}.`,
                    );
                    closeSuggestions();
                    projectName.focus();
                };

                const highlightSuggestion = (index) => {
                    const options = Array.from(
                        suggestions.querySelectorAll('.autocomplete-option'),
                    );

                    options.forEach((option, optionIndex) => {
                        option.classList.toggle(
                            'is-active',
                            optionIndex === index,
                        );
                    });
                };

                const renderSuggestions = () => {
                    const query = normalizeText(projectName.value);
                    const matches = availableProjects.filter((project) => {
                        if (query === '') {
                            return true;
                        }

                        return normalizeText(
                            `${project.snip ?? ''} ${project.name} ${project.place ?? ''}`,
                        ).includes(query);
                    }).slice(0, 8);

                    visibleSuggestions = matches;
                    activeSuggestion = -1;
                    suggestions.replaceChildren();

                    if (matches.length === 0) {
                        if (projectName.value.trim() === '') {
                            closeSuggestions();

                            return;
                        }

                        const empty = document.createElement('div');
                        empty.className = 'autocomplete-empty';
                        empty.textContent =
                            'No existe una coincidencia. Puedes guardar este nombre y quedará únicamente en Monitoreo.';
                        suggestions.append(empty);
                    } else {
                        matches.forEach((project) => {
                            const option = document.createElement('button');
                            const title = document.createElement('strong');
                            const place = document.createElement('span');

                            option.type = 'button';
                            option.className = 'autocomplete-option';
                            option.setAttribute('role', 'option');
                            title.textContent = projectDisplayName(project);
                            place.textContent = project.place
                                || 'Sin ubicación registrada';
                            option.append(title, place);
                            option.addEventListener('pointerdown', (event) => {
                                event.preventDefault();
                                selectProject(project);
                            });
                            suggestions.append(option);
                        });
                    }

                    suggestions.hidden = false;
                    projectName.setAttribute('aria-expanded', 'true');
                };

                projectName.addEventListener('focus', renderSuggestions);
                projectName.addEventListener('input', () => {
                    projectId.value = '';
                    const typedName = projectName.value.trim();

                    if (typedName === '') {
                        setProjectMode(
                            '',
                            'Selecciona una sugerencia para vincular un proyecto existente.',
                        );
                    } else {
                        setProjectMode(
                            'is-standalone',
                            'Si no seleccionas una sugerencia, este nombre se guardará solo en Monitoreo.',
                        );
                    }

                    renderSuggestions();
                });

                projectName.addEventListener('keydown', (event) => {
                    if (suggestions.hidden || visibleSuggestions.length === 0) {
                        return;
                    }

                    if (event.key === 'ArrowDown') {
                        event.preventDefault();
                        activeSuggestion = Math.min(
                            activeSuggestion + 1,
                            visibleSuggestions.length - 1,
                        );
                        highlightSuggestion(activeSuggestion);
                    } else if (event.key === 'ArrowUp') {
                        event.preventDefault();
                        activeSuggestion = Math.max(activeSuggestion - 1, 0);
                        highlightSuggestion(activeSuggestion);
                    } else if (event.key === 'Enter' && activeSuggestion >= 0) {
                        event.preventDefault();
                        selectProject(visibleSuggestions[activeSuggestion]);
                    } else if (event.key === 'Escape') {
                        closeSuggestions();
                    }
                });

                document.addEventListener('pointerdown', (event) => {
                    if (!event.target.closest('.autocomplete')) {
                        closeSuggestions();
                    }
                });

                const setStatusClass = (element, status) => {
                    Object.keys(statusLabels).forEach((statusValue) => {
                        element.classList.remove(`status-${statusValue}`);
                    });
                    element.classList.add(`status-${status}`);
                };

                const showRecordsMessage = (message, isError = false) => {
                    recordsMessage.className = 'records-message';

                    if (isError) {
                        recordsMessage.classList.add('is-error');
                    }

                    recordsMessage.textContent = message;
                };

                const applyStatusFilter = () => {
                    const selectedStatus = statusFilter.value;
                    const cards = Array.from(
                        recordsList.querySelectorAll('.record-card'),
                    );
                    let visibleCount = 0;

                    cards.forEach((card) => {
                        const visible = selectedStatus === 'all'
                            || card.dataset.status === selectedStatus;

                        card.hidden = !visible;

                        if (visible) {
                            visibleCount += 1;
                        }
                    });

                    filteredCount.textContent = String(visibleCount);
                    emptyState.hidden = visibleCount !== 0;

                    if (cards.length === 0) {
                        emptyStateTitle.textContent = 'Aún no hay registros';
                        emptyStateCopy.textContent = readOnly
                            ? 'Todavía no hay proyectos publicados en monitoreo.'
                            : 'Completa el formulario y guarda el primer proyecto que deseas monitorear.';
                    } else if (visibleCount === 0) {
                        emptyStateTitle.textContent =
                            'No hay registros con este estado';
                        emptyStateCopy.textContent =
                            'Selecciona otro estado para consultar los demás proyectos.';
                    }
                };

                const createRecordCard = (record) => {
                    const card = document.createElement('details');
                    const summary = document.createElement('summary');
                    const titleBox = document.createElement('div');
                    const title = document.createElement('strong');
                    const subtitle = document.createElement('span');
                    const summaryMeta = document.createElement('div');
                    const status = document.createElement('select');
                    const chevron = document.createElement('span');
                    const details = document.createElement('div');
                    const facts = document.createElement('div');

                    card.className = 'record-card';
                    card.dataset.recordId = String(record.id);
                    card.dataset.status = record.status;
                    titleBox.className = 'record-title';
                    title.textContent = record.project_name;
                    subtitle.textContent = `${record.snip
                        ? `SNIP ${record.snip} · `
                        : ''}${record.location}`;
                    titleBox.append(title, subtitle);
                    summaryMeta.className = 'record-summary-meta';
                    status.className =
                        `status-select status-${record.status}`;
                    status.dataset.monitoringStatus = '';
                    status.disabled = readOnly;
                    status.setAttribute(
                        'aria-label',
                        `Cambiar estado de ${record.project_name}`,
                    );
                    Object.entries(statusLabels).forEach(([value, label]) => {
                        const option = document.createElement('option');

                        option.value = value;
                        option.textContent = label;
                        option.selected = value === record.status;
                        status.append(option);
                    });
                    chevron.className = 'record-chevron';
                    chevron.setAttribute('aria-hidden', 'true');
                    chevron.textContent = '›';
                    summaryMeta.append(status, chevron);
                    summary.append(titleBox, summaryMeta);

                    details.className = 'record-details';
                    facts.className = 'record-facts';
                    [
                        ['Fecha de inicio', record.starts_on_label],
                        ['Fecha de finalización', record.ends_on_label],
                        ['Categoría', record.category_label],
                        ['Ubicación', record.location],
                    ].forEach(([label, value]) => {
                        const fact = document.createElement('div');
                        const factLabel = document.createElement('span');
                        const factValue = document.createElement('strong');

                        fact.className = 'record-fact';
                        factLabel.textContent = label;
                        factValue.textContent = value;
                        fact.append(factLabel, factValue);
                        facts.append(fact);
                    });

                    const description = document.createElement('p');
                    const badges = document.createElement('div');
                    const category = document.createElement('span');
                    const link = document.createElement('span');

                    description.className = 'record-description';
                    description.textContent = record.description
                        || 'Sin descripción registrada.';
                    badges.className = 'record-badges';
                    category.className = 'kind-badge';
                    category.textContent = record.category_label;
                    link.className = `link-badge ${record.linked
                        ? 'is-linked'
                        : 'is-standalone'}`;
                    link.textContent = record.linked
                        ? 'Vinculado a proyecto existente'
                        : 'Solo en Monitoreo';
                    badges.append(category, link);
                    details.append(facts, description, badges);
                    card.append(summary, details);

                    return card;
                };

                statusFilter.addEventListener('change', () => {
                    applyStatusFilter();
                    showRecordsMessage('');
                });

                ['pointerdown', 'click'].forEach((eventName) => {
                    recordsList.addEventListener(eventName, (event) => {
                        if (event.target.matches('[data-monitoring-status]')) {
                            event.stopPropagation();
                        }
                    });
                });

                recordsList.addEventListener('change', async (event) => {
                    if (readOnly) return;
                    const statusSelect = event.target.closest(
                        '[data-monitoring-status]',
                    );

                    if (!statusSelect) {
                        return;
                    }

                    const card = statusSelect.closest('.record-card');
                    const previousStatus = card.dataset.status;
                    const selectedStatus = statusSelect.value;

                    if (selectedStatus === previousStatus) {
                        return;
                    }

                    statusSelect.disabled = true;
                    showRecordsMessage('Guardando el nuevo estado…');

                    try {
                        const response = await fetch(
                            statusUpdateUrlTemplate.replace(
                                '__MONITORING_ID__',
                                card.dataset.recordId,
                            ),
                            {
                                method: 'PATCH',
                                credentials: 'same-origin',
                                headers: {
                                    Accept: 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                },
                                body: JSON.stringify({
                                    status: selectedStatus,
                                }),
                            },
                        );
                        const result = await response.json().catch(() => ({}));

                        if (!response.ok) {
                            const validationMessage = result.errors?.status?.[0];

                            throw new Error(
                                validationMessage
                                    || result.message
                                    || 'No fue posible cambiar el estado.',
                            );
                        }

                        card.dataset.status = result.record.status;
                        statusSelect.value = result.record.status;
                        setStatusClass(statusSelect, result.record.status);
                        applyStatusFilter();
                        showRecordsMessage(result.message);
                    } catch (error) {
                        statusSelect.value = previousStatus;
                        setStatusClass(statusSelect, previousStatus);
                        showRecordsMessage(
                            error.message || 'No fue posible cambiar el estado.',
                            true,
                        );
                    } finally {
                        statusSelect.disabled = false;
                    }
                });

                const showMessage = (message, type = '') => {
                    formMessage.className = 'form-message';

                    if (type) {
                        formMessage.classList.add(`is-${type}`);
                    }

                    formMessage.textContent = message;
                };

                const clearInvalidFields = () => {
                    form.querySelectorAll('.is-invalid').forEach((field) => {
                        field.classList.remove('is-invalid');
                    });
                };

                form.addEventListener('input', (event) => {
                    event.target.classList?.remove('is-invalid');
                });

                form.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    if (readOnly) return;
                    clearInvalidFields();
                    closeSuggestions();
                    showMessage('Guardando registro…');
                    saveButton.disabled = true;

                    const payload = Object.fromEntries(new FormData(form));
                    payload.project_id = payload.project_id || null;

                    try {
                        const response = await fetch(storeUrl, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                Accept: 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify(payload),
                        });
                        const result = await response.json().catch(() => ({}));

                        if (!response.ok) {
                            if (response.status === 422 && result.errors) {
                                Object.keys(result.errors).forEach((fieldName) => {
                                    form.elements[fieldName]?.classList.add(
                                        'is-invalid',
                                    );
                                });

                                const firstError = Object.values(
                                    result.errors,
                                ).flat()[0];
                                throw new Error(firstError);
                            }

                            throw new Error(
                                result.message
                                    || 'No fue posible guardar el registro.',
                            );
                        }

                        const card = createRecordCard(result.record);
                        recordsList.insertBefore(card, recordsList.firstChild);
                        if (result.record.project_id) {
                            availableProjects = availableProjects.filter(
                                (project) => project.id !== result.record.project_id,
                            );
                        }
                        countElement.textContent = String(
                            Number(countElement.textContent) + 1,
                        );
                        applyStatusFilter();
                        form.reset();
                        projectId.value = '';
                        setProjectMode(
                            '',
                            'Selecciona una sugerencia para vincular un proyecto existente.',
                        );
                        showMessage(result.message, 'success');
                    } catch (error) {
                        showMessage(
                            error.message || 'No fue posible guardar el registro.',
                            'error',
                        );
                    } finally {
                        saveButton.disabled = false;
                    }
                });

                const notifyActivity = () => {
                    if (window.parent !== window) {
                        window.parent.postMessage(
                            { type: 'module-activity' },
                            window.location.origin,
                        );
                    }
                };

                ['pointerdown', 'keydown', 'wheel', 'touchstart'].forEach(
                    (eventName) => {
                        window.addEventListener(eventName, notifyActivity, {
                            passive: true,
                        });
                    },
                );

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

                    if (event.data?.type === 'module-shown') {
                        window.requestAnimationFrame(() => {
                            projectName.blur();
                        });
                    }
                });

                applyStatusFilter();
            })();
        </script>
    </body>
</html>

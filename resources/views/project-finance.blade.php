<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Control financiero | Municipalidad de Coatepeque</title>
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
                --panel: #0c1112;
                --card: #12191b;
                --card-2: #182124;
                --line: rgba(255, 255, 255, 0.1);
                --text: #f7f4ea;
                --muted: #9ba4a3;
                --cyan: #2aa5d3;
                --cyan-deep: #0d6588;
                --gold: #e4a52c;
                --green: #50c878;
                --red: #ef6a6a;
                --orange: #f2b84b;
            }

            * { box-sizing: border-box; }

            html, body { margin: 0; min-height: 100%; }

            body {
                color: var(--text);
                background:
                    radial-gradient(circle at 85% 0, rgba(42, 165, 211, 0.1), transparent 32rem),
                    var(--page);
                font-family: "Segoe UI", Arial, Helvetica, sans-serif;
            }

            button, input, select, textarea { font: inherit; }
            button { cursor: pointer; }

            .finance-shell {
                display: grid;
                min-height: 100vh;
                grid-template-columns: 310px minmax(0, 1fr);
                overflow: hidden;
                border: 1px solid var(--line);
                border-radius: 18px;
                background: rgba(7, 10, 10, 0.94);
            }

            .project-picker {
                min-height: 0;
                padding: 24px 18px;
                border-right: 1px solid var(--line);
                background: linear-gradient(180deg, #0d1517, #080b0c);
            }

            .eyebrow {
                margin: 0 0 7px;
                color: var(--gold);
                font-family: Georgia, "Times New Roman", serif;
                font-size: 0.74rem;
                font-weight: 800;
                letter-spacing: 0.12em;
                text-transform: uppercase;
            }

            h1, h2, h3, p { margin-top: 0; }
            h1 { margin-bottom: 7px; font-size: 1.35rem; }
            h2 { margin-bottom: 5px; font-size: 1.12rem; }
            h3 { margin-bottom: 4px; font-size: 0.92rem; }

            .lead, .section-copy { color: var(--muted); font-size: 0.78rem; line-height: 1.5; }

            .project-search {
                width: 100%;
                height: 46px;
                margin: 16px 0 10px;
                padding: 0 14px;
                border: 1px solid rgba(42, 165, 211, 0.36);
                border-radius: 11px;
                outline: none;
                color: var(--text);
                background: #030708;
            }

            .project-search:focus { border-color: var(--cyan); box-shadow: 0 0 0 4px rgba(42, 165, 211, 0.1); }

            .project-count { margin: 0 2px 10px; color: var(--muted); font-size: 0.7rem; }
            .project-list { display: grid; margin: 0; padding: 0; gap: 8px; list-style: none; }

            .project-option {
                display: grid;
                width: 100%;
                padding: 12px;
                grid-template-columns: 42px minmax(0, 1fr);
                gap: 10px;
                align-items: center;
                border: 1px solid var(--line);
                border-radius: 12px;
                color: var(--text);
                text-align: left;
                background: var(--card);
            }

            .project-option:hover, .project-option.is-active {
                border-color: rgba(42, 165, 211, 0.55);
                background: rgba(42, 165, 211, 0.12);
            }

            .project-pin {
                display: grid;
                width: 38px;
                height: 38px;
                place-items: center;
                border-radius: 50% 50% 50% 8px;
                color: #071012;
                background: var(--gold);
                font-size: 0.68rem;
                font-weight: 900;
                transform: rotate(-45deg);
            }

            .project-pin span { transform: rotate(45deg); }
            .project-option strong, .project-option small { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .project-option strong { font-size: 0.77rem; }
            .project-option small { margin-top: 4px; color: var(--muted); font-size: 0.67rem; }

            .finance-content {
                min-width: 0;
                height: 100vh;
                padding: 24px;
                overflow-y: auto;
                scrollbar-color: rgba(42, 165, 211, 0.7) transparent;
                scrollbar-width: thin;
            }

            .empty-state {
                display: grid;
                min-height: calc(100vh - 48px);
                place-items: center;
                color: var(--muted);
                text-align: center;
            }

            .empty-state strong { display: block; margin-bottom: 7px; color: var(--text); font-size: 1.1rem; }

            .project-finance[hidden], .empty-state[hidden] { display: none; }

            .finance-head {
                display: flex;
                margin-bottom: 16px;
                align-items: flex-start;
                justify-content: space-between;
                gap: 18px;
            }

            .finance-head h1 { margin-bottom: 4px; }
            .finance-head p { margin-bottom: 0; color: var(--muted); font-size: 0.75rem; }

            .finance-status {
                flex: 0 0 auto;
                padding: 8px 12px;
                border: 1px solid rgba(80, 200, 120, 0.35);
                border-radius: 999px;
                color: var(--green);
                background: rgba(80, 200, 120, 0.08);
                font-size: 0.68rem;
                font-weight: 850;
            }

            .finance-status--near_limit { border-color: rgba(242, 184, 75, 0.4); color: var(--orange); background: rgba(242, 184, 75, 0.09); }
            .finance-status--exceeded { border-color: rgba(239, 106, 106, 0.42); color: var(--red); background: rgba(239, 106, 106, 0.09); }

            .section-nav {
                position: sticky;
                z-index: 5;
                top: -24px;
                display: flex;
                margin: 0 -24px 18px;
                padding: 10px 24px;
                gap: 6px;
                overflow-x: auto;
                border-block: 1px solid var(--line);
                background: rgba(7, 10, 10, 0.94);
                backdrop-filter: blur(12px);
            }

            .section-nav button {
                padding: 8px 12px;
                border: 0;
                border-radius: 8px;
                color: var(--muted);
                background: transparent;
                font-size: 0.7rem;
                font-weight: 750;
                white-space: nowrap;
            }

            .section-nav button:hover { color: #fff; background: rgba(42, 165, 211, 0.12); }

            .summary-grid {
                display: grid;
                margin-bottom: 14px;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 10px;
            }

            .metric-card, .content-card {
                border: 1px solid var(--line);
                border-radius: 14px;
                background: var(--card);
                box-shadow: inset 0 1px 0 rgba(255,255,255,0.025);
            }

            .metric-card { padding: 15px; }
            .metric-card span { display: block; margin-bottom: 8px; color: var(--muted); font-size: 0.64rem; letter-spacing: 0.06em; text-transform: uppercase; }
            .metric-card strong { font-size: 1.08rem; }
            .metric-card--balance strong { color: var(--green); }
            .metric-card--negative strong { color: var(--red); }

            .content-card { margin-bottom: 14px; padding: 18px; scroll-margin-top: 62px; }
            .card-head { display: flex; margin-bottom: 14px; align-items: flex-start; justify-content: space-between; gap: 16px; }
            .card-head p { margin-bottom: 0; }

            .progress-track { height: 12px; overflow: hidden; border-radius: 999px; background: rgba(255,255,255,0.08); }
            .progress-bar { display: block; width: 0; height: 100%; border-radius: inherit; background: linear-gradient(90deg, var(--cyan-deep), var(--cyan)); transition: width 240ms ease; }
            .progress-bar.is-near { background: linear-gradient(90deg, #b87a12, var(--orange)); }
            .progress-bar.is-exceeded { background: linear-gradient(90deg, #a32f38, var(--red)); }
            .progress-labels { display: flex; margin-top: 7px; justify-content: space-between; color: var(--muted); font-size: 0.67rem; }

            .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
            .field { display: grid; gap: 6px; }
            .field--wide { grid-column: 1 / -1; }
            .field label { color: var(--text); font-size: 0.69rem; font-weight: 750; }

            .field input, .field select, .field textarea {
                width: 100%;
                min-height: 42px;
                padding: 9px 11px;
                border: 1px solid var(--line);
                border-radius: 9px;
                outline: 0;
                color: var(--text);
                background: #060a0b;
            }

            .field textarea { min-height: 76px; resize: vertical; }
            .field input:focus, .field select:focus, .field textarea:focus { border-color: var(--cyan); box-shadow: 0 0 0 3px rgba(42,165,211,.1); }
            .field-note { color: var(--muted); font-size: 0.62rem; }

            .form-actions { display: flex; margin-top: 13px; justify-content: flex-end; gap: 8px; }
            .primary-button, .secondary-button, .danger-button, .icon-button {
                min-height: 38px;
                padding: 0 14px;
                border: 1px solid rgba(42,165,211,.4);
                border-radius: 9px;
                color: #fff;
                background: var(--cyan-deep);
                font-size: 0.69rem;
                font-weight: 800;
            }

            .secondary-button { color: var(--text); border-color: var(--line); background: var(--card-2); }
            .danger-button { border-color: rgba(239,106,106,.45); background: #7f282c; }
            .icon-button { min-height: 30px; padding: 0 9px; color: var(--red); border-color: rgba(239,106,106,.28); background: rgba(239,106,106,.07); }

            .funding-form { display: grid; margin-bottom: 14px; grid-template-columns: 1.1fr 1fr 1fr auto; gap: 8px; align-items: end; }
            .funding-form[hidden] { display: none; }
            .funding-list { display: grid; margin: 0; padding: 0; gap: 8px; list-style: none; }
            .funding-item { display: flex; padding: 10px 12px; align-items: center; justify-content: space-between; gap: 12px; border: 1px solid var(--line); border-radius: 10px; background: var(--card-2); }
            .funding-item strong { display: block; font-size: .75rem; }
            .funding-item small { color: var(--muted); font-size: .64rem; }
            .funding-total { color: var(--gold); font-weight: 850; }

            .required-documents-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
            .required-document-card { display: flex; min-width: 0; min-height: 220px; padding: 16px; flex-direction: column; gap: 13px; border: 1px solid var(--line); border-radius: 12px; background: var(--card-2); }
            .required-document-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
            .required-document-head h3 { margin-bottom: 5px; }
            .required-document-head p { margin: 0; color: var(--muted); font-size: .66rem; line-height: 1.45; }
            .document-status { flex: 0 0 auto; padding: 5px 9px; border: 1px solid rgba(242,184,75,.35); border-radius: 999px; color: var(--orange); background: rgba(242,184,75,.08); font-size: .62rem; font-weight: 850; }
            .document-status.is-ready { border-color: rgba(80,200,120,.35); color: var(--green); background: rgba(80,200,120,.08); }
            .document-status.is-waived { border-color: var(--line); color: var(--muted); background: rgba(255,255,255,.03); }
            .waiver-option { display: flex; width: fit-content; align-items: center; gap: 8px; color: var(--text); font-size: .7rem; font-weight: 700; cursor: pointer; }
            .waiver-option input { width: 17px; height: 17px; margin: 0; accent-color: var(--cyan-deep); }
            .required-document-form { display: grid; margin-top: auto; gap: 9px; }
            .required-document-form[hidden] { display: none; }
            .required-document-form input[type="file"] { width: 100%; padding: 9px; border: 1px dashed rgba(42,165,211,.38); border-radius: 9px; color: var(--muted); background: #060a0b; font-size: .68rem; }
            .required-document-current { display: grid; margin-top: auto; padding: 12px; gap: 9px; border: 1px solid rgba(80,200,120,.25); border-radius: 10px; background: rgba(80,200,120,.055); }
            .required-document-current[hidden] { display: none; }
            .required-document-name { overflow-wrap: anywhere; color: var(--text); font-size: .72rem; font-weight: 750; }
            .required-document-meta { color: var(--muted); font-size: .62rem; }

            .is-read-only .required-document-card { min-height: 0; }
            .is-read-only .required-document-current { margin-top: 0; padding: 0; border: 0; background: transparent; }

            .table-wrap { overflow-x: auto; border: 1px solid var(--line); border-radius: 11px; }
            table { width: 100%; border-collapse: collapse; min-width: 920px; }
            th, td { padding: 10px; border-bottom: 1px solid var(--line); text-align: left; font-size: .67rem; vertical-align: top; }
            th { color: var(--muted); background: rgba(255,255,255,.025); letter-spacing: .045em; text-transform: uppercase; }
            tbody tr:last-child td { border-bottom: 0; }
            td strong { font-size: .71rem; }
            .amount-cell { color: var(--gold); font-weight: 850; white-space: nowrap; }
            .document-actions { display: flex; gap: 5px; flex-wrap: wrap; }
            .document-actions a { padding: 5px 7px; border: 1px solid rgba(42,165,211,.3); border-radius: 7px; color: #8edaff; text-decoration: none; font-weight: 750; }
            .empty-row { padding: 22px; color: var(--muted); text-align: center; }

            .analysis-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
            .chart-box { padding: 15px; border: 1px solid var(--line); border-radius: 12px; background: var(--card-2); }
            .chart-box h3 { margin-bottom: 13px; }
            .compare-row { display: grid; margin-bottom: 11px; grid-template-columns: 110px minmax(0,1fr) 46px; align-items: center; gap: 8px; color: var(--muted); font-size: .68rem; }
            .mini-track { height: 10px; overflow: hidden; border-radius: 999px; background: rgba(255,255,255,.08); }
            .mini-bar { display: block; height: 100%; border-radius: inherit; background: var(--cyan); }
            .mini-bar--gold { background: var(--gold); }
            .comparison-alert { margin: 13px 0 0; padding: 10px 12px; border-left: 3px solid var(--green); color: var(--muted); background: rgba(80,200,120,.07); font-size: .7rem; line-height: 1.45; }
            .comparison-alert.is-warning { border-color: var(--orange); background: rgba(242,184,75,.07); }

            .monthly-chart { display: flex; min-height: 180px; padding-top: 16px; align-items: flex-end; gap: 9px; overflow-x: auto; }
            .month-column { display: grid; min-width: 58px; height: 165px; grid-template-rows: 1fr auto auto; align-items: end; gap: 5px; text-align: center; }
            .month-bar-wrap { display: flex; height: 115px; align-items: flex-end; justify-content: center; }
            .month-bar { width: 25px; min-height: 3px; border-radius: 5px 5px 2px 2px; background: linear-gradient(180deg, var(--cyan), var(--cyan-deep)); }
            .month-column strong { color: var(--text); font-size: .56rem; }
            .month-column small { color: var(--muted); font-size: .54rem; text-transform: capitalize; }

            dialog { width: min(430px, calc(100% - 28px)); padding: 0; border: 1px solid var(--line); border-radius: 15px; color: var(--text); background: #11191b; box-shadow: 0 28px 90px rgba(0,0,0,.65); }
            dialog::backdrop { background: rgba(0,0,0,.72); backdrop-filter: blur(4px); }
            .dialog-body { padding: 22px; }
            .dialog-body p { color: var(--muted); font-size: .74rem; line-height: 1.5; }
            .dialog-error { min-height: 18px; margin: 7px 0 0; color: var(--red); font-size: .67rem; }

            .toast { position: fixed; z-index: 20; right: 22px; bottom: 22px; max-width: 390px; padding: 12px 15px; border: 1px solid rgba(80,200,120,.35); border-radius: 10px; color: #dff9e7; background: #173523; box-shadow: 0 15px 45px rgba(0,0,0,.4); font-size: .72rem; }
            .toast.is-error { border-color: rgba(239,106,106,.4); color: #ffe4e4; background: #4b2023; }
            .toast[hidden] { display: none; }

            html.light-mode { color-scheme: light; --page: #eaf1f5; --panel: #fff; --card: #fff; --card-2: #f3f7f9; --line: rgba(31,57,79,.15); --text: #172033; --muted: #66717e; --cyan-deep: #0e6f96; }
            html.light-mode body { background: radial-gradient(circle at 85% 0, rgba(42,165,211,.14), transparent 32rem), var(--page); }
            html.light-mode .finance-shell { background: rgba(255,255,255,.96); box-shadow: 0 24px 60px rgba(31,57,79,.14); }
            html.light-mode .project-picker { background: linear-gradient(180deg, #fff, #f4f7f9); }
            html.light-mode .project-search, html.light-mode .field input, html.light-mode .field select, html.light-mode .field textarea, html.light-mode .required-document-form input[type="file"] { color: var(--text); background: #f8fafb; }
            html.light-mode .section-nav { background: rgba(255,255,255,.94); }
            html.light-mode .section-nav button:hover { color: var(--text); }
            html.light-mode .progress-track, html.light-mode .mini-track { background: rgba(31,57,79,.1); }
            html.light-mode dialog { background: #fff; }

            @media (max-width: 1050px) {
                .finance-shell { grid-template-columns: 260px minmax(0,1fr); }
                .summary-grid { grid-template-columns: repeat(2, minmax(0,1fr)); }
                .funding-form { grid-template-columns: 1fr 1fr; }
                .required-documents-grid { grid-template-columns: 1fr; }
                .analysis-grid { grid-template-columns: 1fr; }
            }

            @media (max-width: 720px) {
                .finance-shell { display: block; overflow: visible; border-radius: 12px; }
                .project-picker { border-right: 0; border-bottom: 1px solid var(--line); }
                .project-list { max-height: 230px; overflow-y: auto; }
                .finance-content { height: auto; min-height: 700px; padding: 16px; overflow: visible; }
                .section-nav { top: 0; margin-inline: -16px; padding-inline: 16px; }
                .form-grid, .funding-form { grid-template-columns: 1fr; }
                .field--wide { grid-column: auto; }
                .finance-head { display: block; }
                .finance-status { display: inline-block; margin-top: 10px; }
            }
        </style>
    </head>
    <body @class(['is-read-only' => $readOnly])>
        <main class="finance-shell">
            <aside class="project-picker">
                <p class="eyebrow">Control municipal</p>
                <h1>Finanzas</h1>
                <p class="lead">{{ $readOnly ? 'Consulta el presupuesto, la ejecución y los respaldos publicados.' : 'Selecciona un proyecto para administrar su presupuesto, ejecución y respaldos.' }}</p>
                <input class="project-search" id="finance-project-search" type="search" autocomplete="off" placeholder="SNIP o nombre del proyecto" aria-label="Buscar proyecto">
                <p class="project-count" id="finance-project-count"></p>
                <ul class="project-list" id="finance-project-list"></ul>
            </aside>

            <section class="finance-content">
                <div class="empty-state" id="finance-empty">
                    <p><strong>Selecciona un proyecto</strong>Su información financiera aparecerá aquí.</p>
                </div>

                <article class="project-finance" id="project-finance" hidden>
                    <header class="finance-head">
                        <div>
                            <p class="eyebrow">Ficha financiera del proyecto</p>
                            <h1 id="finance-project-name"></h1>
                            <p id="finance-project-place"></p>
                        </div>
                        <span class="finance-status" id="finance-status"></span>
                    </header>

                    <nav class="section-nav" aria-label="Secciones financieras">
                        <button type="button" data-scroll-to="financial-summary">Resumen</button>
                        <button type="button" data-scroll-to="funding-section">Fuentes</button>
                        <button type="button" data-scroll-to="required-documents-section">Documentos</button>
                        <button type="button" data-scroll-to="analysis-section">Análisis</button>
                    </nav>

                    <section id="financial-summary">
                        <div class="summary-grid">
                            <div class="metric-card"><span>Presupuesto asignado</span><strong id="metric-budget"></strong></div>
                            <div class="metric-card"><span>Monto ejecutado</span><strong id="metric-executed"></strong></div>
                            <div class="metric-card metric-card--balance" id="balance-card"><span>Saldo disponible</span><strong id="metric-balance"></strong></div>
                            <div class="metric-card"><span>Ejecución financiera</span><strong id="metric-progress"></strong></div>
                        </div>

                        <div class="content-card">
                            <div class="card-head"><div><h2>Resumen financiero</h2><p class="section-copy">{{ $readOnly ? 'Presupuesto y avance físico reportado.' : 'Actualiza el presupuesto y el avance físico reportado.' }}</p></div></div>
                            <div class="progress-track"><span class="progress-bar" id="financial-progress-bar"></span></div>
                            <div class="progress-labels"><span>0%</span><strong id="financial-progress-label">0%</strong><span>100%</span></div>
                            <form id="financial-profile-form" @if($readOnly) hidden @endif>
                                <div class="form-grid" style="margin-top:16px">
                                    <div class="field"><label for="allocated-budget">Presupuesto asignado (Q)</label><input id="allocated-budget" name="allocated_budget" type="number" min="0" max="999999999999.99" step="0.01" required></div>
                                    <div class="field"><label for="physical-progress">Avance físico (%)</label><input id="physical-progress" name="physical_progress" type="number" min="0" max="100" step="0.01" required></div>
                                </div>
                                <div class="form-actions"><button class="primary-button" type="submit">Guardar resumen</button></div>
                            </form>
                        </div>
                    </section>

                    <section class="content-card" id="funding-section">
                        <div class="card-head"><div><h2>Fuentes de financiamiento</h2><p class="section-copy">{{ $readOnly ? 'Montos aportados por cada institución o fuente.' : 'Registra el monto aportado por cada institución o fuente.' }}</p></div><strong class="funding-total" id="funding-total"></strong></div>
                        @unless($readOnly)
                        <form class="funding-form" id="funding-form">
                            <div class="field"><label for="funding-type">Fuente</label><select id="funding-type" name="source_type" required>@foreach ($fundingTypes as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
                            <div class="field"><label for="funding-custom-name">Nombre si es “Otro”</label><input id="funding-custom-name" name="custom_name" maxlength="120" placeholder="Nombre de la fuente" disabled></div>
                            <div class="field"><label for="funding-amount">Monto aportado (Q)</label><input id="funding-amount" name="amount" type="number" min="0.01" max="999999999999.99" step="0.01" required></div>
                            <button class="primary-button" type="submit">Añadir fuente</button>
                        </form>
                        @endunless
                        <ul class="funding-list" id="funding-list"></ul>
                    </section>

                    <section class="content-card" id="required-documents-section">
                        <div class="card-head"><div><h2>Documentos financieros</h2><p class="section-copy">{{ $readOnly ? 'Contrato y presupuesto publicados para el proyecto.' : 'Adjunta el contrato y el presupuesto del proyecto. Los archivos se guardan en IDrive e2.' }}</p></div></div>
                        <div class="required-documents-grid">
                            <article class="required-document-card">
                                <div class="required-document-head">
                                    <div><h3>Contrato</h3>@unless($readOnly)<p>Sube el contrato correspondiente al proyecto.</p>@endunless</div>
                                    @unless($readOnly)
                                    <span class="document-status" id="contract-status">Pendiente</span>
                                    @endunless
                                </div>
                                @unless($readOnly)
                                <label class="waiver-option" for="contract-waiver"><input id="contract-waiver" type="checkbox"> No tengo contrato</label>
                                <form class="required-document-form" data-required-document-form="contract" enctype="multipart/form-data">
                                    <div class="field"><label for="contract-file">Archivo del contrato</label><input id="contract-file" name="file" type="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.zip" required><span class="field-note">Máximo 50 MB · PDF, Word, Excel, JPG, PNG o ZIP.</span></div>
                                    <div class="form-actions"><button class="primary-button" type="submit">Subir contrato</button></div>
                                </form>
                                @endunless
                                <div class="required-document-current" id="contract-current" hidden></div>
                            </article>

                            <article class="required-document-card">
                                <div class="required-document-head">
                                    <div><h3>Presupuesto</h3>@unless($readOnly)<p>Este documento es obligatorio y no puede omitirse.</p>@endunless</div>
                                    @unless($readOnly)
                                    <span class="document-status" id="budget-status">Pendiente</span>
                                    @endunless
                                </div>
                                @unless($readOnly)
                                <form class="required-document-form" data-required-document-form="budget" enctype="multipart/form-data">
                                    <div class="field"><label for="budget-file">Archivo del presupuesto</label><input id="budget-file" name="file" type="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.zip" required><span class="field-note">Obligatorio · máximo 50 MB · PDF, Word, Excel, JPG, PNG o ZIP.</span></div>
                                    <div class="form-actions"><button class="primary-button" type="submit">Subir presupuesto</button></div>
                                </form>
                                @endunless
                                <div class="required-document-current" id="budget-current" hidden></div>
                            </article>
                        </div>
                    </section>

                    <section class="content-card" id="analysis-section">
                        <div class="card-head"><div><h2>Análisis financiero</h2><p class="section-copy">Comparación de presupuesto, ejecución y avance por período.</p></div></div>
                        <div class="analysis-grid">
                            <div class="chart-box">
                                <h3>Presupuesto vs. ejecutado</h3>
                                <div class="compare-row"><span>Presupuesto</span><div class="mini-track"><span class="mini-bar mini-bar--gold" id="budget-compare-bar"></span></div><strong>100%</strong></div>
                                <div class="compare-row"><span>Ejecutado</span><div class="mini-track"><span class="mini-bar" id="executed-compare-bar"></span></div><strong id="executed-compare-label"></strong></div>
                                <h3 style="margin-top:20px">Avance físico vs. financiero</h3>
                                <div class="compare-row"><span>Físico</span><div class="mini-track"><span class="mini-bar mini-bar--gold" id="physical-compare-bar"></span></div><strong id="physical-compare-label"></strong></div>
                                <div class="compare-row"><span>Financiero</span><div class="mini-track"><span class="mini-bar" id="financial-compare-bar"></span></div><strong id="financial-compare-label"></strong></div>
                                <p class="comparison-alert" id="comparison-alert"></p>
                            </div>
                            <div class="chart-box">
                                <h3>Ejecución financiera por período</h3>
                                <div class="monthly-chart" id="monthly-chart"></div>
                            </div>
                        </div>
                    </section>
                </article>
            </section>
        </main>

        <dialog id="delete-dialog">
            <form class="dialog-body" id="delete-form" method="dialog">
                <h2>Confirmar eliminación</h2>
                <p id="delete-message">Esta acción eliminará únicamente el registro seleccionado. Escribe el código de seguridad.</p>
                <div class="field"><label for="delete-code">Código de seguridad</label><input id="delete-code" name="code" type="password" inputmode="numeric" pattern="[0-9]{8}" maxlength="8" autocomplete="off" required></div>
                <p class="dialog-error" id="delete-error"></p>
                <div class="form-actions"><button class="secondary-button" type="button" id="cancel-delete">Cancelar</button><button class="danger-button" type="submit">Eliminar</button></div>
            </form>
        </dialog>

        <div class="toast" id="finance-toast" role="status" hidden></div>

        <script>
            (() => {
                let projects = {{ Illuminate\Support\Js::from($projects) }};
                const readOnly = @json($readOnly);
                let selectedProjectId = projects[0]?.id ?? null;
                let pendingDelete = null;
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                const money = new Intl.NumberFormat('es-GT', { style: 'currency', currency: 'GTQ', minimumFractionDigits: 2 });
                const percent = (value) => `${Number(value || 0).toLocaleString('es-GT', { maximumFractionDigits: 2 })}%`;
                const fileSize = (bytes) => {
                    const size = Number(bytes || 0);
                    if (size < 1024) return `${size} B`;
                    if (size < 1048576) return `${(size / 1024).toLocaleString('es-GT', { maximumFractionDigits: 1 })} KB`;
                    return `${(size / 1048576).toLocaleString('es-GT', { maximumFractionDigits: 1 })} MB`;
                };
                const $ = (selector) => document.querySelector(selector);
                const list = $('#finance-project-list');
                const search = $('#finance-project-search');
                const count = $('#finance-project-count');
                const toast = $('#finance-toast');
                const empty = $('#finance-empty');
                const panel = $('#project-finance');
                const deleteDialog = $('#delete-dialog');

                const showToast = (message, isError = false) => {
                    toast.textContent = message;
                    toast.classList.toggle('is-error', isError);
                    toast.hidden = false;
                    window.clearTimeout(showToast.timer);
                    showToast.timer = window.setTimeout(() => { toast.hidden = true; }, 4200);
                };

                const firstError = (result, fallback) => Object.values(result.errors ?? {}).flat()[0] ?? result.message ?? fallback;

                const replaceProject = (project) => {
                    projects = projects.map((item) => item.id === project.id ? project : item);
                    renderProjectList();
                    renderSelectedProject();
                };

                const createText = (tag, text, className = '') => {
                    const element = document.createElement(tag);
                    element.textContent = text;
                    if (className) element.className = className;
                    return element;
                };

                const renderProjectList = () => {
                    const query = search.value.trim().toLocaleLowerCase('es');
                    const filtered = projects.filter((project) => `${project.snip ?? ''} ${project.name} ${project.place ?? ''}`.toLocaleLowerCase('es').includes(query));
                    count.textContent = `${filtered.length} ${filtered.length === 1 ? 'proyecto encontrado' : 'proyectos encontrados'}`;
                    list.replaceChildren();

                    filtered.forEach((project, index) => {
                        const item = document.createElement('li');
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = `project-option${project.id === selectedProjectId ? ' is-active' : ''}`;
                        const pin = createText('span', '', 'project-pin');
                        pin.append(createText('span', String(index + 1).padStart(2, '0')));
                        const copy = document.createElement('span');
                        copy.append(createText('strong', `${project.snip ? `${project.snip}-` : ''}${project.name}`));
                        copy.append(createText('small', project.place || 'Sin ubicación'));
                        button.append(pin, copy);
                        button.addEventListener('click', () => {
                            selectedProjectId = project.id;
                            renderProjectList();
                            renderSelectedProject();
                        });
                        item.append(button);
                        list.append(item);
                    });
                };

                const selectedProject = () => projects.find((project) => project.id === selectedProjectId) ?? null;

                const renderFunding = (project) => {
                    $('#funding-total').textContent = `Total: ${money.format(project.funding_total)}`;
                    const fundingList = $('#funding-list');
                    fundingList.replaceChildren();

                    if (!project.funding_sources.length) {
                        fundingList.append(createText('li', 'Aún no hay fuentes de financiamiento registradas.', 'empty-row'));
                        return;
                    }

                    project.funding_sources.forEach((source) => {
                        const item = document.createElement('li');
                        item.className = 'funding-item';
                        const copy = document.createElement('span');
                        copy.append(createText('strong', source.label));
                        copy.append(createText('small', money.format(source.amount)));
                        item.append(copy);
                        if (!readOnly) {
                            const remove = createText('button', 'Eliminar', 'icon-button');
                            remove.type = 'button';
                            remove.addEventListener('click', () => openDelete(source.delete_url, `Se eliminará la fuente “${source.label}”.`));
                            item.append(remove);
                        }
                        fundingList.append(item);
                    });
                };

                const renderRequiredDocuments = (project) => {
                    ['contract', 'budget'].forEach((type) => {
                        const documentData = project.required_documents[type];
                        const status = $(`#${type}-status`);
                        const form = document.querySelector(`[data-required-document-form="${type}"]`);
                        const fileInput = form?.querySelector('input[type="file"]');
                        const current = $(`#${type}-current`);

                        if (status) {
                            status.className = 'document-status';
                            status.textContent = 'Pendiente';
                            if (documentData.has_file) {
                                status.classList.add('is-ready');
                                status.textContent = 'Guardado';
                            } else if (type === 'contract' && documentData.waived) {
                                status.classList.add('is-waived');
                                status.textContent = 'No aplica';
                            }
                        }

                        const hideUpload = readOnly || documentData.has_file || (type === 'contract' && documentData.waived);
                        if (form) form.hidden = hideUpload;
                        if (fileInput) fileInput.disabled = hideUpload;
                        current.replaceChildren();
                        current.hidden = !readOnly && !(documentData.has_file || (type === 'contract' && documentData.waived));

                        if (documentData.has_file) {
                            current.append(createText('strong', documentData.name, 'required-document-name'));
                            current.append(createText('span', fileSize(documentData.size), 'required-document-meta'));
                            const actions = document.createElement('div');
                            actions.className = 'document-actions';
                            const documentLabel = type === 'contract' ? 'contrato' : 'presupuesto';
                            const preview = createText('a', readOnly ? `Ver ${documentLabel}` : 'Ver');
                            preview.href = documentData.preview_url;
                            preview.target = '_blank';
                            preview.rel = 'noopener';
                            const download = createText('a', 'Descargar');
                            download.href = documentData.download_url;
                            actions.append(preview, download);
                            if (!readOnly) {
                                const remove = createText('button', 'Eliminar', 'icon-button');
                                remove.type = 'button';
                                remove.addEventListener('click', () => openDelete(
                                    documentData.delete_url,
                                    `Se eliminará el archivo “${documentData.name}”.`,
                                ));
                                actions.append(remove);
                            }
                            current.append(actions);
                        } else if (readOnly) {
                            current.append(createText('span', type === 'contract' ? 'No tiene contrato.' : 'No tiene presupuesto.', 'required-document-name'));
                        } else if (type === 'contract' && documentData.waived) {
                            current.append(createText('span', 'Marcado como proyecto sin contrato.', 'required-document-name'));
                        }
                    });

                    const waiver = $('#contract-waiver');
                    const contract = project.required_documents.contract;
                    if (waiver) {
                        waiver.checked = Boolean(contract.waived);
                        waiver.disabled = readOnly || Boolean(contract.has_file);
                    }
                };

                const renderAnalysis = (project) => {
                    const cappedFinancial = Math.min(100, Number(project.financial_progress));
                    const cappedPhysical = Math.min(100, Number(project.physical_progress));
                    $('#budget-compare-bar').style.width = project.allocated_budget > 0 ? '100%' : '0%';
                    $('#executed-compare-bar').style.width = `${cappedFinancial}%`;
                    $('#executed-compare-label').textContent = percent(project.financial_progress);
                    $('#physical-compare-bar').style.width = `${cappedPhysical}%`;
                    $('#physical-compare-label').textContent = percent(project.physical_progress);
                    $('#financial-compare-bar').style.width = `${cappedFinancial}%`;
                    $('#financial-compare-label').textContent = percent(project.financial_progress);

                    const alert = $('#comparison-alert');
                    const messages = {
                        balanced: 'Ejecución equilibrada: el avance físico y financiero mantienen una diferencia aceptable.',
                        financial_ahead: `⚠ La ejecución financiera supera al avance físico por ${percent(Math.abs(project.progress_difference))}. Revisa el uso de los recursos.`,
                        physical_ahead: `El avance físico supera a la ejecución financiera por ${percent(Math.abs(project.progress_difference))}.`,
                    };
                    alert.textContent = messages[project.comparison_status];
                    alert.classList.toggle('is-warning', project.comparison_status === 'financial_ahead');

                    const monthly = $('#monthly-chart');
                    monthly.replaceChildren();
                    if (!project.monthly_execution.length) {
                        monthly.append(createText('p', 'Aún no hay ejecución financiera para mostrar por período.', 'section-copy'));
                        return;
                    }
                    const maximum = Math.max(...project.monthly_execution.map((item) => Number(item.amount)), 1);
                    project.monthly_execution.forEach((item) => {
                        const column = document.createElement('div');
                        column.className = 'month-column';
                        const wrap = document.createElement('div');
                        wrap.className = 'month-bar-wrap';
                        const bar = document.createElement('span');
                        bar.className = 'month-bar';
                        bar.style.height = `${Math.max(3, (Number(item.amount) / maximum) * 100)}%`;
                        bar.title = money.format(item.amount);
                        wrap.append(bar);
                        column.append(wrap, createText('strong', money.format(item.amount)), createText('small', item.label));
                        monthly.append(column);
                    });
                };

                const renderSelectedProject = () => {
                    const project = selectedProject();
                    empty.hidden = Boolean(project);
                    panel.hidden = !project;
                    if (!project) return;

                    $('#finance-project-name').textContent = `${project.snip ? `${project.snip}-` : ''}${project.name}`;
                    $('#finance-project-place').textContent = project.place || 'Sin ubicación registrada';
                    $('#metric-budget').textContent = money.format(project.allocated_budget);
                    $('#metric-executed').textContent = money.format(project.executed_amount);
                    $('#metric-balance').textContent = money.format(project.available_balance);
                    $('#metric-progress').textContent = percent(project.financial_progress);
                    $('#balance-card').classList.toggle('metric-card--negative', project.available_balance < 0);
                    const statusLabels = { normal: 'Estado financiero: normal', near_limit: 'Cercano al límite', exceeded: 'Presupuesto excedido' };
                    const status = $('#finance-status');
                    status.textContent = statusLabels[project.financial_status];
                    status.className = `finance-status finance-status--${project.financial_status}`;
                    const progressBar = $('#financial-progress-bar');
                    progressBar.style.width = `${Math.min(100, Number(project.financial_progress))}%`;
                    progressBar.className = `progress-bar${project.financial_status === 'near_limit' ? ' is-near' : ''}${project.financial_status === 'exceeded' ? ' is-exceeded' : ''}`;
                    $('#financial-progress-label').textContent = percent(project.financial_progress);
                    $('#allocated-budget').value = Number(project.allocated_budget).toFixed(2);
                    $('#physical-progress').value = Number(project.physical_progress).toFixed(2);
                    renderFunding(project);
                    renderRequiredDocuments(project);
                    renderAnalysis(project);
                };

                const jsonRequest = async (url, method, payload) => {
                    const response = await fetch(url, {
                        method,
                        credentials: 'same-origin',
                        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify(payload),
                    });
                    const result = await response.json().catch(() => ({}));
                    if (!response.ok) throw new Error(firstError(result, 'No se pudo completar la operación.'));
                    return result;
                };

                $('#financial-profile-form').addEventListener('submit', async (event) => {
                    event.preventDefault();
                    if (readOnly) return;
                    const project = selectedProject();
                    if (!project) return;
                    try {
                        const result = await jsonRequest(project.profile_url, 'PUT', Object.fromEntries(new FormData(event.currentTarget)));
                        replaceProject(result.project);
                        showToast(result.message);
                    } catch (error) { showToast(error.message, true); }
                });

                $('#funding-type')?.addEventListener('change', (event) => {
                    const custom = $('#funding-custom-name');
                    custom.disabled = event.target.value !== 'other';
                    custom.required = event.target.value === 'other';
                    if (custom.disabled) custom.value = '';
                });

                $('#funding-form')?.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    if (readOnly) return;
                    const project = selectedProject();
                    if (!project) return;
                    const submittedForm = event.currentTarget;
                    try {
                        const result = await jsonRequest(project.funding_url, 'POST', Object.fromEntries(new FormData(submittedForm)));
                        submittedForm.reset();
                        $('#funding-custom-name').disabled = true;
                        replaceProject(result.project);
                        showToast(result.message);
                    } catch (error) { showToast(error.message, true); }
                });

                document.querySelectorAll('[data-required-document-form]').forEach((form) => {
                    form.addEventListener('submit', async (event) => {
                        event.preventDefault();
                        if (readOnly) return;
                        const project = selectedProject();
                        if (!project) return;
                        const submittedForm = event.currentTarget;
                        const type = submittedForm.dataset.requiredDocumentForm;
                        const button = submittedForm.querySelector('[type="submit"]');
                        const originalLabel = button.textContent;
                        button.disabled = true;
                        button.textContent = 'Subiendo…';
                        try {
                            const response = await fetch(project.required_document_upload_urls[type], {
                                method: 'POST',
                                credentials: 'same-origin',
                                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
                                body: new FormData(submittedForm),
                            });
                            const result = await response.json().catch(() => ({}));
                            if (!response.ok) throw new Error(firstError(result, 'No se pudo guardar el documento.'));
                            submittedForm.reset();
                            replaceProject(result.project);
                            showToast(result.message);
                        } catch (error) { showToast(error.message, true); }
                        finally { button.disabled = false; button.textContent = originalLabel; }
                    });
                });

                $('#contract-waiver')?.addEventListener('change', async (event) => {
                    if (readOnly) return;
                    const project = selectedProject();
                    if (!project) return;
                    const checkbox = event.currentTarget;
                    const waived = checkbox.checked;
                    checkbox.disabled = true;
                    try {
                        const result = await jsonRequest(project.contract_waiver_url, 'PATCH', { waived });
                        replaceProject(result.project);
                        showToast(result.message);
                    } catch (error) {
                        checkbox.checked = !waived;
                        checkbox.disabled = false;
                        showToast(error.message, true);
                    }
                });

                const openDelete = (url, message) => {
                    pendingDelete = url;
                    $('#delete-message').textContent = message;
                    $('#delete-code').value = '';
                    $('#delete-error').textContent = '';
                    deleteDialog.showModal();
                    $('#delete-code').focus();
                };

                $('#cancel-delete').addEventListener('click', () => deleteDialog.close());
                $('#delete-form').addEventListener('submit', async (event) => {
                    event.preventDefault();
                    if (!pendingDelete) return;
                    try {
                        const result = await jsonRequest(pendingDelete, 'DELETE', { code: $('#delete-code').value });
                        deleteDialog.close();
                        pendingDelete = null;
                        replaceProject(result.project);
                        showToast(result.message);
                    } catch (error) { $('#delete-error').textContent = error.message; }
                });

                document.querySelectorAll('[data-scroll-to]').forEach((button) => {
                    button.addEventListener('click', () => document.getElementById(button.dataset.scrollTo)?.scrollIntoView({ behavior: 'smooth', block: 'start' }));
                });

                search.addEventListener('input', renderProjectList);
                renderProjectList();
                renderSelectedProject();

                const notifyActivity = () => {
                    if (window.parent !== window) window.parent.postMessage({ type: 'module-activity' }, window.location.origin);
                };
                ['pointerdown', 'keydown'].forEach((eventName) => window.addEventListener(eventName, notifyActivity, { passive: true }));
                window.addEventListener('message', (event) => {
                    if (event.origin !== window.location.origin) return;
                    if (event.data?.type === 'theme-changed') {
                        document.documentElement.classList.toggle('light-mode', event.data.theme === 'light');
                    }
                });
            })();
        </script>
    </body>
</html>

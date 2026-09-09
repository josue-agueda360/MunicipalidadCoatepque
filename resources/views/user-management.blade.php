<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Usuarios | Municipalidad de Coatepeque</title>
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
                --panel: #0e1415;
                --card: #151d1f;
                --line: rgba(255, 255, 255, 0.1);
                --text: #f7f4ea;
                --muted: #9ba4a3;
                --cyan: #2aa5d3;
                --cyan-deep: #0d6588;
                --gold: #e4a52c;
                --green: #61d892;
                --red: #ef7777;
            }

            * { box-sizing: border-box; }
            html, body { margin: 0; min-height: 100%; }

            body {
                color: var(--text);
                background:
                    radial-gradient(circle at 84% 4%, rgba(42,165,211,.11), transparent 32rem),
                    var(--page);
                font-family: "Segoe UI", Arial, Helvetica, sans-serif;
            }

            button, input { font: inherit; }

            .users-shell {
                display: grid;
                min-height: 100vh;
                padding: 28px;
                grid-template-columns: minmax(330px, .82fr) minmax(430px, 1.18fr);
                gap: 20px;
                border: 1px solid var(--line);
                border-radius: 18px;
                background: rgba(7,10,10,.92);
            }

            .panel {
                min-width: 0;
                padding: 24px;
                border: 1px solid var(--line);
                border-radius: 16px;
                background: var(--panel);
                box-shadow: inset 0 1px 0 rgba(255,255,255,.025);
            }

            .eyebrow {
                margin: 0 0 8px;
                color: var(--gold);
                font-family: Georgia, "Times New Roman", serif;
                font-size: .74rem;
                font-weight: 850;
                letter-spacing: .12em;
                text-transform: uppercase;
            }

            h1, h2, p { margin-top: 0; }
            h1 { margin-bottom: 7px; font-size: 1.45rem; }
            h2 { margin-bottom: 6px; font-size: 1.15rem; }
            .lead { color: var(--muted); font-size: .78rem; line-height: 1.5; }

            .form-grid { display: grid; margin-top: 20px; gap: 14px; }
            .field { display: grid; gap: 7px; }
            .field label { font-size: .72rem; font-weight: 800; }
            .field input {
                width: 100%;
                height: 46px;
                padding: 0 13px;
                border: 1px solid var(--line);
                border-radius: 10px;
                outline: none;
                color: var(--text);
                background: #050909;
            }
            .field input:focus { border-color: var(--cyan); box-shadow: 0 0 0 4px rgba(42,165,211,.1); }
            .field input.is-invalid { border-color: var(--red); box-shadow: 0 0 0 4px rgba(239,119,119,.08); }

            .password-wrap { position: relative; }
            .password-wrap input { padding-right: 54px; }
            .password-peek {
                position: absolute;
                top: 5px;
                right: 5px;
                width: 37px;
                height: 36px;
                padding: 0;
                border: 0;
                border-radius: 8px;
                color: var(--muted);
                background: transparent;
                cursor: pointer;
            }
            .password-peek:hover { color: #fff; background: rgba(42,165,211,.1); }

            .password-rules {
                display: grid;
                margin: 0;
                padding: 0;
                grid-template-columns: repeat(2, minmax(0,1fr));
                gap: 7px 12px;
                color: var(--muted);
                font-size: .66rem;
                list-style: none;
            }
            .password-rules li::before { margin-right: 6px; color: #707a7d; content: "○"; }
            .password-rules li.is-met { color: var(--green); }
            .password-rules li.is-met::before { color: var(--green); content: "✓"; font-weight: 900; }

            .form-message { min-height: 20px; margin: 10px 0 0; color: var(--muted); font-size: .7rem; }
            .form-message.is-error { color: var(--red); }
            .form-message.is-success { color: var(--green); }

            .primary-button {
                width: 100%;
                min-height: 44px;
                margin-top: 4px;
                border: 1px solid rgba(42,165,211,.55);
                border-radius: 10px;
                color: #fff;
                background: linear-gradient(180deg, #137aa3, var(--cyan-deep));
                cursor: pointer;
                font-size: .74rem;
                font-weight: 850;
            }
            .primary-button:disabled { opacity: .55; cursor: wait; }

            .recovery-note {
                display: flex;
                margin-top: 18px;
                padding: 12px;
                align-items: flex-start;
                gap: 10px;
                border: 1px solid rgba(42,165,211,.2);
                border-radius: 11px;
                color: var(--muted);
                background: rgba(42,165,211,.06);
                font-size: .69rem;
                line-height: 1.48;
            }
            .recovery-note strong { display: block; margin-bottom: 2px; color: var(--text); }
            .recovery-icon { color: var(--cyan); font-size: 1.05rem; }

            .users-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; }
            .users-count { min-width: 36px; padding: 7px 10px; border: 1px solid var(--line); border-radius: 9px; color: var(--gold); text-align: center; font-weight: 900; }
            .user-search {
                width: 100%;
                height: 44px;
                margin: 12px 0 14px;
                padding: 0 13px;
                border: 1px solid rgba(42,165,211,.28);
                border-radius: 10px;
                outline: 0;
                color: var(--text);
                background: #050909;
            }
            .user-search:focus { border-color: var(--cyan); }

            .user-list {
                display: grid;
                max-height: calc(100vh - 225px);
                margin: 0;
                padding: 0 5px 0 0;
                gap: 9px;
                overflow-y: auto;
                list-style: none;
                scrollbar-color: rgba(42,165,211,.55) transparent;
                scrollbar-width: thin;
            }
            .user-card {
                display: grid;
                padding: 13px;
                grid-template-columns: 42px minmax(0,1fr) auto;
                align-items: center;
                gap: 11px;
                border: 1px solid var(--line);
                border-radius: 12px;
                background: var(--card);
            }
            .user-avatar { display: grid; width: 40px; height: 40px; place-items: center; border-radius: 50%; color: #071012; background: var(--gold); font-size: .72rem; font-weight: 900; }
            .user-copy { min-width: 0; }
            .user-copy strong, .user-copy span { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .user-copy strong { font-size: .78rem; }
            .user-copy span { margin-top: 3px; color: var(--muted); font-size: .67rem; }
            .user-meta { display: flex; align-items: flex-end; flex-direction: column; gap: 5px; }
            .user-meta small { color: var(--muted); font-size: .6rem; }
            .badge { padding: 5px 8px; border: 1px solid rgba(97,216,146,.3); border-radius: 999px; color: var(--green); background: rgba(97,216,146,.06); font-size: .58rem; font-weight: 800; white-space: nowrap; }
            .badge.is-locked { border-color: rgba(239,119,119,.35); color: var(--red); background: rgba(239,119,119,.06); }
            .delete-user-button { padding: 5px 8px; border: 1px solid rgba(239,119,119,.36); border-radius: 8px; color: var(--red); background: rgba(239,119,119,.06); cursor: pointer; font-size: .6rem; font-weight: 800; }
            .delete-user-button:hover { color: #fff; background: rgba(190,45,53,.72); }
            .delete-user-button:disabled { opacity: .55; cursor: wait; }
            .user-list-message { min-height: 18px; margin: -4px 0 8px; color: var(--muted); font-size: .68rem; }
            .user-list-message.is-error { color: var(--red); }
            .user-list-message.is-success { color: var(--green); }
            .empty-users { padding: 30px 12px; color: var(--muted); text-align: center; font-size: .75rem; }

            html.light-mode {
                color-scheme: light;
                --page: #eaf1f5;
                --panel: #fff;
                --card: #f4f7f9;
                --line: rgba(31,57,79,.15);
                --text: #172033;
                --muted: #66717e;
                --cyan-deep: #0e6f96;
            }
            html.light-mode body { background: radial-gradient(circle at 84% 4%, rgba(42,165,211,.14), transparent 32rem), var(--page); }
            html.light-mode .users-shell { background: rgba(255,255,255,.7); }
            html.light-mode .panel { box-shadow: 0 18px 50px rgba(31,57,79,.09); }
            html.light-mode .field input, html.light-mode .user-search { color: var(--text); background: #f8fafb; }
            html.light-mode .password-peek:hover { color: var(--text); }

            @media (max-width: 900px) {
                .users-shell { grid-template-columns: 1fr; padding: 16px; }
                .user-list { max-height: none; }
            }
            @media (max-width: 520px) {
                .users-shell { padding: 8px; gap: 10px; border-radius: 12px; }
                .panel { padding: 17px; }
                .password-rules { grid-template-columns: 1fr; }
                .user-card { grid-template-columns: 40px minmax(0,1fr); }
                .user-meta { grid-column: 1 / -1; align-items: flex-start; flex-direction: row; }
            }
        </style>
    </head>
    <body>
        <main class="users-shell">
            <section class="panel">
                <p class="eyebrow">Administración restringida</p>
                <h1>Crear usuario</h1>
                <p class="lead">Registra una cuenta nueva para que pueda ingresar al sistema municipal.</p>

                <form id="create-user-form" novalidate>
                    <div class="form-grid">
                        <div class="field"><label for="user-name">Nombre del usuario</label><input id="user-name" name="name" maxlength="120" autocomplete="name" required></div>
                        <div class="field"><label for="user-email">Correo electrónico</label><input id="user-email" name="email" type="email" maxlength="255" autocomplete="email" required></div>
                        <div class="field">
                            <label for="user-password">Contraseña</label>
                            <div class="password-wrap"><input id="user-password" name="password" type="password" minlength="8" maxlength="128" autocomplete="new-password" required><button class="password-peek" type="button" data-password-peek="user-password" aria-label="Mostrar contraseña">◉</button></div>
                        </div>
                        <div class="field">
                            <label for="user-password-confirmation">Confirmar contraseña</label>
                            <div class="password-wrap"><input id="user-password-confirmation" name="password_confirmation" type="password" minlength="8" maxlength="128" autocomplete="new-password" required><button class="password-peek" type="button" data-password-peek="user-password-confirmation" aria-label="Mostrar confirmación">◉</button></div>
                        </div>
                        <ul class="password-rules" id="password-rules">
                            <li data-rule="length">Mínimo 8 caracteres</li><li data-rule="lower">Una minúscula</li><li data-rule="upper">Una mayúscula</li><li data-rule="number">Un número</li><li data-rule="symbol">Un símbolo</li><li data-rule="spaces">Sin espacios</li><li data-rule="match">Las contraseñas coinciden</li>
                        </ul>
                    </div>
                    <p class="form-message" id="create-user-message" role="status"></p>
                    <button class="primary-button" id="create-user-button" type="submit">Crear usuario</button>
                </form>

                <div class="recovery-note">
                    <span class="recovery-icon" aria-hidden="true">↻</span>
                    <div><strong>Recuperación de contraseña disponible</strong>Cada usuario puede pulsar “Olvidé mi contraseña” en el inicio de sesión. Recibirá un código seguro en el correo registrado.</div>
                </div>
            </section>

            <section class="panel">
                <div class="users-head"><div><p class="eyebrow">Cuentas registradas</p><h2>Usuarios del sistema</h2><p class="lead">Consulta las cuentas que pueden acceder.</p></div><span class="users-count" id="users-count">{{ $users->count() }}</span></div>
                <input class="user-search" id="user-search" type="search" autocomplete="off" placeholder="Buscar por nombre o correo" aria-label="Buscar usuarios">
                <p class="user-list-message" id="user-list-message" role="status"></p>
                <ul class="user-list" id="user-list"></ul>
            </section>
        </main>

        <script>
            (() => {
                let users = {{ Illuminate\Support\Js::from($users) }};
                const adminEmail = @json(config('security.user_management_admin_email'));
                const storeUrl = @json(route('user-management.store'));
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                const form = document.getElementById('create-user-form');
                const password = document.getElementById('user-password');
                const confirmation = document.getElementById('user-password-confirmation');
                const message = document.getElementById('create-user-message');
                const button = document.getElementById('create-user-button');
                const search = document.getElementById('user-search');
                const userList = document.getElementById('user-list');
                const count = document.getElementById('users-count');
                const listMessage = document.getElementById('user-list-message');

                const initials = (name) => name.trim().split(/\s+/).slice(0, 2).map((part) => part[0]?.toUpperCase() ?? '').join('') || 'U';
                const normalize = (value) => String(value ?? '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('es');

                const renderUsers = () => {
                    const query = normalize(search.value.trim());
                    const visible = users.filter((user) => normalize(`${user.name} ${user.email}`).includes(query));
                    count.textContent = String(users.length);
                    userList.replaceChildren();
                    if (!visible.length) {
                        const empty = document.createElement('li');
                        empty.className = 'empty-users';
                        empty.textContent = 'No se encontraron usuarios.';
                        userList.append(empty);
                        return;
                    }
                    visible.forEach((user) => {
                        const item = document.createElement('li');
                        item.className = 'user-card';
                        const avatar = document.createElement('span');
                        avatar.className = 'user-avatar';
                        avatar.textContent = initials(user.name);
                        const copy = document.createElement('div');
                        copy.className = 'user-copy';
                        const name = document.createElement('strong');
                        name.textContent = user.name;
                        const email = document.createElement('span');
                        email.textContent = user.email;
                        copy.append(name, email);
                        const meta = document.createElement('div');
                        meta.className = 'user-meta';
                        const badge = document.createElement('span');
                        badge.className = `badge${user.is_locked ? ' is-locked' : ''}`;
                        badge.textContent = user.is_locked ? 'Acceso bloqueado' : (user.email.toLowerCase() === adminEmail.toLowerCase() ? 'Administrador' : 'Recuperación disponible');
                        const date = document.createElement('small');
                        date.textContent = `Creado: ${user.created_at_label}`;
                        meta.append(badge, date);
                        if (!user.is_admin && user.delete_url) {
                            const remove = document.createElement('button');
                            remove.type = 'button';
                            remove.className = 'delete-user-button';
                            remove.textContent = 'Eliminar';
                            remove.setAttribute('aria-label', `Eliminar usuario ${user.name}`);
                            remove.addEventListener('click', async () => {
                                if (!window.confirm(`¿Deseas eliminar al usuario “${user.name}”?\n\nLos proyectos y registros municipales se conservarán.`)) return;
                                remove.disabled = true;
                                listMessage.className = 'user-list-message';
                                listMessage.textContent = 'Eliminando usuario…';
                                try {
                                    const response = await fetch(user.delete_url, {
                                        method: 'DELETE',
                                        credentials: 'same-origin',
                                        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
                                    });
                                    const result = await response.json().catch(() => ({}));
                                    if (!response.ok) throw new Error(result.message ?? 'No se pudo eliminar el usuario.');
                                    users = users.filter((current) => current.id !== user.id);
                                    renderUsers();
                                    listMessage.className = 'user-list-message is-success';
                                    listMessage.textContent = result.message;
                                } catch (error) {
                                    remove.disabled = false;
                                    listMessage.className = 'user-list-message is-error';
                                    listMessage.textContent = error.message;
                                }
                            });
                            meta.append(remove);
                        }
                        item.append(avatar, copy, meta);
                        userList.append(item);
                    });
                };

                const updateRules = () => {
                    const value = password.value;
                    const checks = {
                        length: value.length >= 8,
                        lower: /[a-z]/.test(value),
                        upper: /[A-Z]/.test(value),
                        number: /[0-9]/.test(value),
                        symbol: /[^A-Za-z0-9]/.test(value),
                        spaces: value.length > 0 && !/\s/.test(value),
                        match: value.length > 0 && value === confirmation.value,
                    };
                    Object.entries(checks).forEach(([rule, met]) => document.querySelector(`[data-rule="${rule}"]`)?.classList.toggle('is-met', met));
                };

                [password, confirmation].forEach((input) => input.addEventListener('input', updateRules));
                document.querySelectorAll('[data-password-peek]').forEach((peek) => {
                    peek.addEventListener('click', () => {
                        const input = document.getElementById(peek.dataset.passwordPeek);
                        const reveal = input.type === 'password';
                        input.type = reveal ? 'text' : 'password';
                        peek.textContent = reveal ? '◌' : '◉';
                    });
                });

                form.addEventListener('input', (event) => event.target.classList?.remove('is-invalid'));
                form.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    form.querySelectorAll('.is-invalid').forEach((field) => field.classList.remove('is-invalid'));
                    message.className = 'form-message';
                    message.textContent = 'Creando usuario…';
                    button.disabled = true;
                    try {
                        const response = await fetch(storeUrl, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                            body: JSON.stringify(Object.fromEntries(new FormData(form))),
                        });
                        const result = await response.json().catch(() => ({}));
                        if (!response.ok) {
                            Object.keys(result.errors ?? {}).forEach((field) => form.elements[field]?.classList.add('is-invalid'));
                            throw new Error(Object.values(result.errors ?? {}).flat()[0] ?? result.message ?? 'No se pudo crear el usuario.');
                        }
                        users.push(result.user);
                        users.sort((first, second) => first.name.localeCompare(second.name, 'es'));
                        form.reset();
                        updateRules();
                        renderUsers();
                        message.className = 'form-message is-success';
                        message.textContent = result.message;
                    } catch (error) {
                        message.className = 'form-message is-error';
                        message.textContent = error.message;
                    } finally {
                        button.disabled = false;
                    }
                });

                search.addEventListener('input', renderUsers);
                renderUsers();

                const notifyActivity = () => {
                    if (window.parent !== window) window.parent.postMessage({ type: 'module-activity' }, window.location.origin);
                };
                ['pointerdown', 'keydown'].forEach((eventName) => window.addEventListener(eventName, notifyActivity, { passive: true }));
                window.addEventListener('message', (event) => {
                    if (event.origin !== window.location.origin) return;
                    if (event.data?.type === 'theme-changed') document.documentElement.classList.toggle('light-mode', event.data.theme === 'light');
                });
            })();
        </script>
    </body>
</html>

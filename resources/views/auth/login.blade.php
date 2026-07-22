<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — SiManS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
            display: flex;
            background: #f0f4f8;
        }

        /* ── PANEL KIRI ── */
        .left-panel {
            display: none;
            flex: 1;
            background: linear-gradient(135deg, #1e40af 0%, #1d4ed8 40%, #2563eb 70%, #3b82f6 100%);
            position: relative;
            overflow: hidden;
            align-items: center;
            justify-content: center;
            padding: 60px;
        }
        @media (min-width: 1024px) { .left-panel { display: flex; } }

        /* Dekorasi lingkaran */
        .left-panel::before {
            content: '';
            position: absolute;
            top: -120px; right: -120px;
            width: 500px; height: 500px;
            background: rgba(255,255,255,0.06);
            border-radius: 50%;
        }
        .left-panel::after {
            content: '';
            position: absolute;
            bottom: -80px; left: -80px;
            width: 350px; height: 350px;
            background: rgba(255,255,255,0.04);
            border-radius: 50%;
        }
        .left-circle-mid {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%,-50%);
            width: 600px; height: 600px;
            background: rgba(255,255,255,0.03);
            border-radius: 50%;
        }

        .left-content {
            position: relative;
            z-index: 1;
            color: white;
            max-width: 420px;
        }
        .left-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.20);
            border-radius: 20px;
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 600;
            color: rgba(255,255,255,0.9);
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 32px;
        }
        .left-badge span {
            width: 6px; height: 6px;
            background: #34d399;
            border-radius: 50%;
            animation: blink 2s infinite;
        }
        @keyframes blink { 0%,100%{opacity:1} 50%{opacity:.3} }

        .left-title {
            font-size: 38px;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -1px;
            margin-bottom: 16px;
        }
        .left-title em {
            font-style: normal;
            color: #bfdbfe;
        }
        .left-desc {
            font-size: 15px;
            color: rgba(255,255,255,0.65);
            line-height: 1.7;
            margin-bottom: 40px;
        }

        /* Feature list */
        .feature-list { display: flex; flex-direction: column; gap: 14px; }
        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .feature-icon {
            width: 36px; height: 36px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .feature-text { font-size: 13.5px; color: rgba(255,255,255,0.8); font-weight: 500; }

        /* Stats row */
        .stats-row {
            display: flex;
            gap: 24px;
            margin-top: 48px;
            padding-top: 32px;
            border-top: 1px solid rgba(255,255,255,0.12);
        }
        .stat-item {}
        .stat-val { font-size: 26px; font-weight: 800; color: white; letter-spacing: -1px; }
        .stat-lbl { font-size: 11.5px; color: rgba(255,255,255,0.5); margin-top: 2px; }

        /* ── PANEL KANAN (form) ── */
        .right-panel {
            width: 100%;
            max-width: 480px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 32px;
            background: #ffffff;
            box-shadow: -8px 0 40px rgba(15,23,42,0.08);
        }
        @media (min-width: 1024px) { .right-panel { min-height: 100vh; } }

        .form-wrap { width: 100%; max-width: 380px; }

        /* Logo */
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 40px;
        }
        .brand-icon {
            width: 44px; height: 44px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(59,130,246,0.35);
        }
        .brand-icon img {
            width: 44px; height: 44px;
            object-fit: contain;
            border-radius: 12px;
        }
        .brand-name { font-size: 17px; font-weight: 800; color: #0f172a; letter-spacing: -0.3px; }
        .brand-sub  { font-size: 11.5px; color: #94a3b8; margin-top: 1px; }

        /* Heading */
        .form-heading { margin-bottom: 28px; }
        .form-heading h1 {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
            margin-bottom: 6px;
        }
        .form-heading p { font-size: 13.5px; color: #64748b; }

        /* Alerts */
        .alert {
            padding: 12px 14px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 9px;
            font-weight: 500;
        }
        .alert-error {
            background: #fff1f2;
            border: 1px solid #fecaca;
            color: #dc2626;
        }
        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #15803d;
        }
        .alert svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; flex-shrink: 0; }

        /* Form field */
        .field { margin-bottom: 16px; }
        .field label {
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 7px;
            letter-spacing: 0.01em;
        }
        .field-wrap { position: relative; }
        .field input {
            width: 100%;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 11px 14px;
            font-size: 14px;
            color: #1e293b;
            font-family: 'Inter', sans-serif;
            outline: none;
            transition: border-color 0.15s, background 0.15s, box-shadow 0.15s;
        }
        .field input.has-toggle { padding-right: 44px; }
        .field input::placeholder { color: #cbd5e1; }
        .field input:focus {
            border-color: #3b82f6;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.10);
        }

        /* Toggle password */
        .toggle-pw {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #94a3b8;
            display: flex;
            padding: 4px;
            border-radius: 6px;
            transition: color 0.15s, background 0.15s;
        }
        .toggle-pw:hover { color: #475569; background: #f1f5f9; }
        .toggle-pw svg { width: 16px; height: 16px; stroke: currentColor; fill: none; stroke-width: 2; }

        /* Options row */
        .opts-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 4px 0 22px;
        }
        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            user-select: none;
            font-size: 13px;
            color: #64748b;
        }
        .remember input[type=checkbox] {
            width: 15px; height: 15px;
            accent-color: #3b82f6;
            cursor: pointer;
            border-radius: 4px;
        }
        .forgot {
            font-size: 13px;
            color: #3b82f6;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.15s;
        }
        .forgot:hover { color: #1d4ed8; }

        /* Submit button */
        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            border-radius: 10px;
            padding: 12.5px;
            font-size: 14.5px;
            font-weight: 700;
            color: #ffffff;
            cursor: pointer;
            letter-spacing: 0.01em;
            box-shadow: 0 4px 14px rgba(59,130,246,0.35);
            transition: all 0.2s ease;
            font-family: 'Inter', sans-serif;
        }
        .btn-submit:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            box-shadow: 0 6px 20px rgba(59,130,246,0.45);
            transform: translateY(-1px);
        }
        .btn-submit:active { transform: translateY(0); }

        /* Footer */
        .form-footer {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #f1f5f9;
            text-align: center;
        }
        .form-footer p {
            font-size: 11.5px;
            color: #cbd5e1;
        }
        .form-footer strong { color: #94a3b8; font-weight: 600; }
    </style>
</head>
<body>

    {{-- Panel kiri: branding --}}
    <div class="left-panel">
        <div class="left-circle-mid"></div>
        <div class="left-content">
            <div class="left-badge">
                <span></span>
                Sistem Aktif
            </div>

            <h1 class="left-title">
                Kelola sekolah<br>lebih <em>cerdas & efisien</em>
            </h1>
            <p class="left-desc">
                SiManS mengintegrasikan absensi, nilai, keuangan, dan pelaporan dalam satu platform yang mudah digunakan.
            </p>

            <div class="feature-list">
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg width="17" height="17" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/>
                        </svg>
                    </div>
                    <span class="feature-text">Absensi GPS + QR Code real-time</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg width="17" height="17" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75"/>
                        </svg>
                    </div>
                    <span class="feature-text">Manajemen keuangan & SPP otomatis</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg width="17" height="17" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                        </svg>
                    </div>
                    <span class="feature-text">Laporan & rekap otomatis per peran</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg width="17" height="17" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                        </svg>
                    </div>
                    <span class="feature-text">Multi-role: Admin, Guru, Siswa & Bendahara</span>
                </div>
            </div>

            @php $loginSchool = \App\Models\School::first(); @endphp
            <div class="stats-row">
                <div class="stat-item">
                    <div class="stat-val">{{ \App\Models\User::where('role','siswa')->count() }}+</div>
                    <div class="stat-lbl">Siswa aktif</div>
                </div>
                <div class="stat-item">
                    <div class="stat-val">{{ \App\Models\User::whereIn('role',['guru','wali_kelas'])->count() }}</div>
                    <div class="stat-lbl">Guru & staf</div>
                </div>
                <div class="stat-item">
                    <div class="stat-val">100%</div>
                    <div class="stat-lbl">Digital</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Panel kanan: form login --}}
    <div class="right-panel">
        <div class="form-wrap">

            {{-- Brand --}}
            @php $loginSchool = \App\Models\School::first(); @endphp
            <div class="brand">
                @if($loginSchool?->logo_path)
                    <img src="{{ Storage::url($loginSchool->logo_path) }}" alt="{{ $loginSchool->name }}" class="brand-icon" style="border-radius:12px;object-fit:contain;background:#eff6ff;padding:4px">
                @else
                    <div class="brand-icon">
                        <svg width="22" height="22" fill="none" stroke="white" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342"/>
                        </svg>
                    </div>
                @endif
                <div>
                    <div class="brand-name">{{ $loginSchool?->name ?? 'SiManS' }}</div>
                    <div class="brand-sub">EduWeb by Padiweb</div>
                </div>
            </div>

            {{-- Heading --}}
            <div class="form-heading">
                <h1>Selamat datang kembali</h1>
                <p>Masuk untuk mengakses dashboard Anda</p>
            </div>

            {{-- Alert --}}
            @if(session('status'))
                <div class="alert alert-success">
                    <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('status') }}
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-error">
                    <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9.303 3.376c.866 1.5-.217 3.374-1.948 3.374H4.645c-1.73 0-2.813-1.874-1.948-3.374L10.053 3.378c.866-1.5 3.032-1.5 3.898 0l6.352 12.748zM12 15.75h.007v.008H12v-.008z"/></svg>
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Form --}}
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="field">
                    <label for="login">Email / Username / NIS</label>
                    <input
                        type="text"
                        id="login"
                        name="login"
                        value="{{ old('login') }}"
                        placeholder="Masukkan identitas login"
                        required autofocus autocomplete="username">
                </div>

                <div class="field">
                    <label for="pw">Password</label>
                    <div class="field-wrap">
                        <input
                            type="password"
                            id="pw"
                            name="password"
                            placeholder="Masukkan password"
                            required autocomplete="current-password"
                            class="has-toggle">
                        <button type="button" class="toggle-pw" id="togglePw" aria-label="Tampilkan password">
                            <svg id="eye-on" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <svg id="eye-off" style="display:none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="opts-row">
                    <label class="remember">
                        <input type="checkbox" name="remember"> Ingat saya
                    </label>
                    @if(Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot">Lupa password?</a>
                    @endif
                </div>

                <button type="submit" class="btn-submit">
                    Masuk ke Dashboard
                </button>
            </form>

            <div class="form-footer">
                <p>EduWeb &copy; {{ date('Y') }} &middot; <strong>Padiweb Developer</strong></p>
            </div>
        </div>
    </div>

    <script>
        const pw  = document.getElementById('pw');
        const btn = document.getElementById('togglePw');
        const on  = document.getElementById('eye-on');
        const off = document.getElementById('eye-off');
        btn.addEventListener('click', () => {
            const show = pw.type === 'password';
            pw.type = show ? 'text' : 'password';
            on.style.display  = show ? 'none'  : 'block';
            off.style.display = show ? 'block' : 'none';
        });
    </script>
</body>
</html>

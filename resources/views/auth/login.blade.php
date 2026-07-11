<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — EduWeb</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{min-height:100vh;background:#0b0f14;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;display:flex;align-items:center;justify-content:center;padding:24px}
        body::after{content:'';position:fixed;bottom:-100px;right:-100px;width:400px;height:400px;background:radial-gradient(circle,rgba(16,185,129,.06) 0%,transparent 65%);pointer-events:none;z-index:0}
        .card{position:relative;z-index:1;width:100%;max-width:380px}
        .logo{display:flex;align-items:center;gap:12px;margin-bottom:36px}
        .logo-img{width:40px;height:40px;object-fit:contain;border-radius:8px}
        .logo-icon{width:40px;height:40px;background:#10b981;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
        .logo-icon svg{width:20px;height:20px;stroke:#fff;fill:none;stroke-width:2.2}
        .logo-name{font-size:16px;font-weight:700;color:#fff;letter-spacing:-.3px;line-height:1.2}
        .logo-sub{font-size:11px;color:#4b5563;margin-top:2px}
        .heading{margin-bottom:28px}
        .heading h1{font-size:20px;font-weight:600;color:#f9fafb;letter-spacing:-.3px;margin-bottom:5px}
        .heading p{font-size:13px;color:#6b7280}
        .alert{padding:11px 14px;border-radius:10px;font-size:13px;margin-bottom:20px;display:flex;align-items:center;gap:8px}
        .alert-e{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.15);color:#fca5a5}
        .alert-e svg{width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;flex-shrink:0}
        .alert-s{background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.15);color:#6ee7b7}
        .field{margin-bottom:14px}
        .field label{display:block;font-size:12.5px;font-weight:500;color:#9ca3af;margin-bottom:7px}
        .fw{position:relative}
        .field input{width:100%;background:#131920;border:1px solid #1f2937;border-radius:10px;padding:11px 14px;font-size:14px;color:#f3f4f6;outline:none;transition:border-color .15s;-webkit-appearance:none}
        .field input::placeholder{color:#374151}
        .field input:focus{border-color:#10b981}
        .field input.pr{padding-right:42px}
        .tog{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#4b5563;display:flex;padding:4px;transition:color .15s}
        .tog:hover{color:#9ca3af}
        .tog svg{width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2}
        .opts{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;margin-top:4px}
        .rem{display:flex;align-items:center;gap:7px;font-size:13px;color:#6b7280;cursor:pointer;user-select:none}
        .rem input{width:14px;height:14px;accent-color:#10b981;cursor:pointer}
        .frgt{font-size:13px;color:#10b981;text-decoration:none;transition:color .15s}
        .frgt:hover{color:#34d399}
        .btn{width:100%;background:#10b981;border:none;border-radius:10px;padding:12px;font-size:14px;font-weight:600;color:#fff;cursor:pointer;transition:background .15s,transform .1s;letter-spacing:.01em}
        .btn:hover{background:#0ea572}
        .btn:active{transform:scale(.99)}
        .divider{height:1px;background:#111827;margin:24px 0}
        .foot{text-align:center}
        .foot-brand{display:flex;align-items:center;justify-content:center;gap:8px;margin-bottom:6px}
        .foot-brand img{height:18px;opacity:.4;filter:brightness(10)}
        .foot p{font-size:11.5px;color:#1f2937}
        .foot a{color:#374151;text-decoration:none}
        .foot a:hover{color:#6b7280}
    </style>
</head>
<body>
<div class="card">

    {{-- Logo: pakai logo sekolah jika ada, fallback EduWeb --}}
    @php
        $loginSchool = \App\Models\School::first(); // ambil sekolah pertama untuk login page
    @endphp
    <div class="logo">
        @if($loginSchool?->logo_path)
            <img src="{{ Storage::url($loginSchool->logo_path) }}" alt="{{ $loginSchool->name }}" class="logo-img">
            <div>
                <div class="logo-name">{{ $loginSchool->name }}</div>
                <div class="logo-sub">EduWeb by Padiweb</div>
            </div>
        @else
            <div class="logo-icon">
                <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/></svg>
            </div>
            <div>
                <div class="logo-name">EduWeb</div>
                <div class="logo-sub">by Padiweb Developer</div>
            </div>
        @endif
    </div>

    <div class="heading">
        <h1>Masuk ke akun Anda</h1>
        <p>{{ $loginSchool?->name ?? 'Sistem Manajemen Sekolah' }}</p>
    </div>

    @if(session('status'))
        <div class="alert alert-s">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-e">
            <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9.303 3.376c.866 1.5-.217 3.374-1.948 3.374H4.645c-1.73 0-2.813-1.874-1.948-3.374L10.053 3.378c.866-1.5 3.032-1.5 3.898 0l6.352 12.748zM12 15.75h.007v.008H12v-.008z"/></svg>
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="field">
            <label>Email / Username / NIS</label>
            <input type="text" name="login" value="{{ old('login') }}" placeholder="Masukkan identitas login" required autofocus autocomplete="username">
        </div>
        <div class="field">
            <label>Password</label>
            <div class="fw">
                <input type="password" name="password" id="pw" placeholder="Masukkan password" required autocomplete="current-password" class="pr">
                <button type="button" class="tog" onclick="var i=document.getElementById('pw'),s1=document.getElementById('s1'),s2=document.getElementById('s2');if(i.type==='password'){i.type='text';s1.style.display='none';s2.style.display='block'}else{i.type='password';s1.style.display='block';s2.style.display='none'}">
                    <svg id="s1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <svg id="s2" style="display:none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                </button>
            </div>
        </div>
        <div class="opts">
            <label class="rem"><input type="checkbox" name="remember"> Ingat saya</label>
            @if(Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="frgt">Lupa password?</a>
            @endif
        </div>
        <button type="submit" class="btn">Masuk</button>
    </form>

    <div class="divider"></div>
    <div class="foot">
        <div class="foot-brand">
            {{-- Logo Padiweb — akan ditambahkan setelah logo dikirim --}}
            <p style="font-size:11px;color:#374151;font-weight:600;letter-spacing:.05em">PADIWEB DEVELOPER</p>
        </div>
        <p>EduWeb &copy; {{ date('Y') }} &middot; <a href="#">Sistem Manajemen Sekolah</a></p>
    </div>
</div>
</body>
</html>

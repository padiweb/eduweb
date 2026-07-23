<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} — SiManS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ── Design Tokens ── */
        :root {
            --blue-50:  #eff6ff;
            --blue-100: #dbeafe;
            --blue-200: #bfdbfe;
            --blue-500: #3b82f6;
            --blue-600: #2563eb;
            --blue-700: #1d4ed8;
            --gray-50:  #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-700: #334155;
            --gray-900: #0f172a;
            --shadow-sm: 0 1px 3px rgba(15,23,42,0.06), 0 1px 2px rgba(15,23,42,0.04);
            --shadow-md: 0 4px 16px rgba(15,23,42,0.10);
            --shadow-blue: 0 4px 14px rgba(59,130,246,0.35);
            --radius-card: 14px;
            --radius-btn:  8px;
            --radius-input: 8px;
        }

        /* ── Reset & Base ── */
        *, *::before, *::after { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; }
        body {
            background-color: #f0f4f8;
            color: #1e293b;
            font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, sans-serif;
            -webkit-font-smoothing: antialiased;
            font-size: 14px;
        }

        /* ── Layout wrapper ── */
        .simans-wrap { display: flex; min-height: 100vh; }

        /* ── SIDEBAR ── */
        .simans-sidebar {
            position: fixed;
            top: 0; left: 0; bottom: 0;
            width: 240px;
            background: #ffffff;
            border-right: 1px solid #e2e8f0;
            box-shadow: 4px 0 16px rgba(15,23,42,0.06);
            display: flex;
            flex-direction: column;
            z-index: 50;
            transition: transform 0.3s ease;
        }

        /* Brand area */
        .simans-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 18px 20px 16px;
            border-bottom: 1px solid #f1f5f9;
        }
        .simans-brand-icon {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(59,130,246,0.35);
        }
        .simans-brand-name {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.2;
            letter-spacing: -0.3px;
        }
        .simans-brand-sub {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 1px;
        }

        /* Nav scroll area */
        .simans-nav {
            flex: 1;
            overflow-y: auto;
            padding: 12px 12px 8px;
        }
        .simans-nav::-webkit-scrollbar { width: 4px; }
        .simans-nav::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 99px; }

        /* Section label */
        .simans-section {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #94a3b8;
            padding: 14px 10px 6px;
        }

        /* Nav link */
        .simans-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 500;
            text-decoration: none;
            color: #64748b;
            border-left: 3px solid transparent;
            margin-left: -1px;
            transition: all 0.15s ease;
            margin-bottom: 2px;
        }
        .simans-link:hover {
            background: #f1f5f9;
            color: #1e293b;
        }
        .simans-link.active {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            color: #1d4ed8;
            border-left-color: #3b82f6;
            font-weight: 600;
        }
        .simans-link svg { flex-shrink: 0; }

        /* User footer */
        .simans-user {
            padding: 12px;
            border-top: 1px solid #f1f5f9;
        }
        .simans-user-inner {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 10px;
            transition: background 0.15s;
        }
        .simans-user-inner:hover { background: #f8fafc; }
        .simans-avatar {
            width: 34px; height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            display: flex; align-items: center; justify-content: center;
            font-size: 12px;
            font-weight: 700;
            color: white;
            flex-shrink: 0;
        }
        .simans-user-name { font-size: 13px; font-weight: 600; color: #0f172a; }
        .simans-user-role { font-size: 11px; color: #94a3b8; margin-top: 1px; }
        .simans-logout {
            margin-left: auto;
            background: none; border: none; cursor: pointer;
            color: #94a3b8;
            padding: 4px;
            border-radius: 6px;
            transition: color 0.15s, background 0.15s;
            display: flex;
        }
        .simans-logout:hover { color: #ef4444; background: #fef2f2; }

        /* ── MAIN AREA ── */
        .simans-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            padding-left: 240px;
        }

        /* ── TOPBAR ── */
        .simans-topbar {
            position: sticky;
            top: 0;
            z-index: 40;
            height: 58px;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(15,23,42,0.05);
            display: flex;
            align-items: center;
            padding: 0 24px;
            gap: 14px;
        }
        .simans-topbar-title {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.2px;
        }
        .simans-topbar-date {
            font-size: 12px;
            color: #64748b;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 4px 12px;
        }
        .simans-notif {
            position: relative;
            background: none; border: none; cursor: pointer;
            color: #64748b;
            padding: 6px;
            border-radius: 8px;
            transition: background 0.15s, color 0.15s;
            display: flex;
        }
        .simans-notif:hover { background: #f1f5f9; color: #1e293b; }
        .simans-notif-dot {
            position: absolute;
            top: 4px; right: 4px;
            width: 8px; height: 8px;
            background: #ef4444;
            border-radius: 50%;
            border: 2px solid white;
        }
        .simans-hamburger {
            display: none;
            background: none; border: none; cursor: pointer;
            color: #64748b; padding: 6px;
            border-radius: 8px;
        }
        .simans-hamburger:hover { background: #f1f5f9; color: #1e293b; }

        /* ── PAGE CONTENT ── */
        .simans-content {
            flex: 1;
            padding: 22px 24px;
        }

        /* ── ALERTS ── */
        .alert-success {
            display: flex; align-items: center; gap: 10px;
            background: #f0fdf4;
            border: 1px solid #86efac;
            color: #15803d;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13.5px;
            margin-bottom: 20px;
        }
        .alert-error {
            display: flex; align-items: center; gap: 10px;
            background: #fef2f2;
            border: 1px solid #fca5a5;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13.5px;
            margin-bottom: 20px;
        }

        /* ── CARD global ── */
        .card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 1px 4px rgba(15,23,42,0.06);
            transition: box-shadow 0.2s;
        }
        .card:hover { box-shadow: 0 4px 16px rgba(15,23,42,0.10); }

        /* ── BUTTON primary global ── */
        .btn-primary, button.bg-blue-600, a.bg-blue-600 {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
            color: white !important;
            border-radius: 8px;
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(59,130,246,0.30);
            transition: all 0.2s ease;
        }
        .btn-primary:hover, button.bg-blue-600:hover, a.bg-blue-600:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
            box-shadow: 0 4px 12px rgba(59,130,246,0.45) !important;
            transform: translateY(-1px);
        }

        /* ── TABLE ── */
        table tr:hover td { background: #f8fafc; }

        /* ── INPUT focus ── */
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.12) !important;
        }

        /* ── SCROLLBAR global ── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* ── MOBILE ── */
        @media (max-width: 1023px) {
            .simans-sidebar { transform: translateX(-100%); }
            .simans-sidebar.open { transform: translateX(0); box-shadow: 8px 0 32px rgba(15,23,42,.18); }
            .simans-main { padding-left: 0; }
            .simans-hamburger { display: flex !important; }
        }
        @media (max-width: 767px) {
            .simans-content { padding: 16px 14px; }
            .simans-topbar  { padding: 0 14px; }
        }

        /* Alpine cloak */
        [x-cloak] { display: none !important; }

        /* ════════════════════════════════════════════════════
           1. TAB NAV — scroll horizontal, TIDAK flex-wrap
        ════════════════════════════════════════════════════ */
        .tab-nav-scroll {
            display: flex;
            flex-wrap: nowrap !important;   /* tab TIDAK turun baris */
            gap: 8px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            -ms-overflow-style: none;
            padding-bottom: 4px;
            margin-bottom: 20px;
        }
        .tab-nav-scroll::-webkit-scrollbar { display: none; }
        /* Extend ke tepi di mobile agar tidak terasa ada padding kanan */
        @media (max-width: 1023px) {
            .tab-nav-scroll {
                margin-left: -14px;
                margin-right: -14px;
                padding-left: 14px;
                padding-right: 14px;
            }
        }
        .tab-nav-scroll > a,
        .tab-nav-scroll > button {
            flex-shrink: 0 !important;  /* tab tidak boleh menyempit */
            white-space: nowrap;
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            border: 1.5px solid #e2e8f0;
            color: #64748b;
            background: #fff;
            display: inline-flex;
            align-items: center;
        }
        .tab-nav-scroll > a.bg-blue-600,
        .tab-nav-scroll > a[class*="bg-blue-6"] {
            background: linear-gradient(135deg,#3b82f6,#2563eb) !important;
            color: #fff !important; border-color: transparent !important;
            box-shadow: 0 2px 6px rgba(59,130,246,.3);
        }
        .tab-nav-scroll > a.bg-orange-600 {
            background: linear-gradient(135deg,#f97316,#ea580c) !important;
            color: #fff !important; border-color: transparent !important;
        }
        .tab-nav-scroll > a.bg-amber-600 {
            background: linear-gradient(135deg,#f59e0b,#d97706) !important;
            color: #fff !important; border-color: transparent !important;
        }
        .tab-nav-scroll > a:not([class*="bg-blue-6"]):not(.bg-orange-600):not(.bg-amber-600):hover {
            border-color: #bfdbfe; color: #2563eb; background: #eff6ff;
        }

        /* ════════════════════════════════════════════════════
           2. TABEL SCROLL — HANYA tabel yang geser, bukan halaman
           
           KUNCI: .tbl-card pakai overflow:visible (bukan hidden)
           Rounded corner ada di .tbl-wrap, bukan di .tbl-card
           Sehingga overflow-x:auto di .tbl-wrap bisa bekerja bebas
        ════════════════════════════════════════════════════ */
        .tbl-card {
            /* Card container - tidak punya overflow apapun */
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 1px 4px rgba(15,23,42,.06);
            overflow: visible !important;  /* KRITIS: jangan hidden */
        }
        .tbl-wrap {
            /* Yang punya scroll */
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            /* Rounded corner ada di sini supaya tidak perlu parent overflow:hidden */
            border-radius: 14px;
            width: 100%;
        }
        .tbl-wrap::-webkit-scrollbar { height: 3px; }
        .tbl-wrap::-webkit-scrollbar-thumb {
            background: #cbd5e1; border-radius: 99px;
        }

        /* ════════════════════════════════════════════════════
           3. MOBILE RESPONSIVE
        ════════════════════════════════════════════════════ */
        @media (max-width: 767px) {
            #stat-grid  { grid-template-columns: 1fr 1fr !important; gap: 10px !important; }
            #panel-grid { grid-template-columns: 1fr !important; gap: 12px !important; }
            #stat-row-siswa { grid-template-columns: 1fr 1fr !important; gap: 8px !important; }
        }

        /* ════════════════════════════════════════════════════
           4. DUAL MODE: Tabel desktop / Card mobile
        ════════════════════════════════════════════════════ */
        .mobile-hidden { display: block; }
        .mobile-cards  { display: none; }
        @media (max-width: 767px) {
            .mobile-hidden { display: none !important; }
            .mobile-cards  { display: block !important; }
        }
        .m-card {
            background: #fff; border: 1px solid #e2e8f0;
            border-radius: 12px; padding: 14px 16px;
            margin-bottom: 10px; box-shadow: 0 1px 3px rgba(15,23,42,.05);
        }
        .m-card-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; margin-bottom: 8px; }
        .m-card-title  { font-size: 14px; font-weight: 700; color: #1e293b; }
        .m-card-meta   { display: flex; flex-wrap: wrap; gap: 4px 12px; }
        .m-card-meta-item { display: flex; align-items: center; gap: 4px; font-size: 12px; color: #64748b; }
        .m-card-footer { display: flex; justify-content: flex-end; gap: 8px; margin-top: 10px; padding-top: 10px; border-top: 1px solid #f1f5f9; }

        /* ─────────────────────────────────────────────
           GLOBAL DESIGN SYSTEM — override Tailwind
        ───────────────────────────────────────────── */

        /* ── PAGE BACKGROUND ── */
        body { background: #f0f4f8; }

        /* ── CARDS ── */
        .bg-white {
            background: #ffffff !important;
        }
        .rounded-xl, .rounded-2xl {
            border-radius: var(--radius-card) !important;
        }
        /* Card otomatis dapat shadow */
        .bg-white.border {
            box-shadow: var(--shadow-sm);
            transition: box-shadow 0.2s ease;
        }
        .bg-white.border:hover {
            box-shadow: var(--shadow-md);
        }

        /* ── TOMBOL PRIMER ── */
        .bg-blue-600, .bg-blue-500, .bg-blue-700 {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
            color: #ffffff !important;
            border-radius: var(--radius-btn) !important;
            font-weight: 600 !important;
            box-shadow: 0 2px 6px rgba(59,130,246,0.30) !important;
            transition: all 0.18s ease !important;
            border: none !important;
        }
        .bg-blue-600:hover, .bg-blue-500:hover, .bg-blue-700:hover,
        .hover\:bg-blue-700:hover, .hover\:bg-blue-600:hover, .hover\:bg-blue-500:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
            box-shadow: 0 4px 14px rgba(59,130,246,0.45) !important;
            transform: translateY(-1px) !important;
            color: #ffffff !important;
        }
        button.bg-blue-600, a.bg-blue-600,
        button.bg-blue-700, a.bg-blue-700 { cursor: pointer; }

        /* amber / orange tombol */
        .bg-amber-600, .bg-amber-700 {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
            color: #ffffff !important;
            border-radius: var(--radius-btn) !important;
            font-weight: 600 !important;
            box-shadow: 0 2px 6px rgba(245,158,11,0.28) !important;
            transition: all 0.18s ease !important;
        }
        .bg-amber-600:hover, .bg-amber-700:hover,
        .hover\:bg-amber-500:hover, .hover\:bg-amber-600:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%) !important;
            box-shadow: 0 4px 12px rgba(245,158,11,0.40) !important;
            transform: translateY(-1px) !important;
            color: #ffffff !important;
        }

        .bg-orange-600, .bg-orange-700 {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%) !important;
            color: #ffffff !important;
            border-radius: var(--radius-btn) !important;
            font-weight: 600 !important;
            box-shadow: 0 2px 6px rgba(249,115,22,0.28) !important;
            transition: all 0.18s ease !important;
        }
        .bg-orange-600:hover, .bg-orange-700:hover,
        .hover\:bg-orange-500:hover, .hover\:bg-orange-600:hover {
            background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%) !important;
            color: #ffffff !important;
            transform: translateY(-1px) !important;
        }

        /* ── TOMBOL SEKUNDER / OUTLINE ── */
        .border-gray-200.bg-white,
        button.border.border-gray-200 {
            color: var(--gray-700) !important;
            font-weight: 500 !important;
        }
        button:not([class*="bg-"]) {
            border-radius: var(--radius-btn) !important;
        }

        /* ── TOMBOL DANGER (red) ── */
        .bg-red-600, .bg-red-500 {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
            color: #ffffff !important;
            border-radius: var(--radius-btn) !important;
            font-weight: 600 !important;
            box-shadow: 0 2px 6px rgba(239,68,68,0.28) !important;
            transition: all 0.18s ease !important;
        }
        .bg-red-600:hover { box-shadow: 0 4px 12px rgba(239,68,68,0.40) !important; transform: translateY(-1px) !important; }

        /* ── TOMBOL SUCCESS (emerald/green) ── */
        .bg-emerald-500, .bg-emerald-600, .bg-green-500 {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
            color: #ffffff !important;
            border-radius: var(--radius-btn) !important;
            font-weight: 600 !important;
            box-shadow: 0 2px 6px rgba(16,185,129,0.28) !important;
        }
        .bg-emerald-500:hover, .bg-emerald-600:hover {
            box-shadow: 0 4px 12px rgba(16,185,129,0.40) !important;
            transform: translateY(-1px) !important;
        }

        /* ── TOMBOL AMBER/ORANGE ── */
        .bg-amber-500, .bg-yellow-500 {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
            color: #ffffff !important;
            border-radius: var(--radius-btn) !important;
            font-weight: 600 !important;
        }

        /* ── INPUT & SELECT ── */
        input[type="text"], input[type="email"], input[type="number"],
        input[type="password"], input[type="date"], input[type="time"],
        input[type="search"], textarea, select {
            background: #ffffff !important;
            border: 1.5px solid #e2e8f0 !important;
            border-radius: var(--radius-input) !important;
            color: #1e293b !important;
            font-family: 'Inter', sans-serif !important;
            font-size: 13.5px !important;
            transition: border-color 0.15s, box-shadow 0.15s !important;
        }
        input:focus, textarea:focus, select:focus {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.12) !important;
            outline: none !important;
        }
        input::placeholder, textarea::placeholder { color: #94a3b8 !important; }

        /* ── LABEL ── */
        label {
            font-size: 12.5px !important;
            font-weight: 600 !important;
            color: #475569 !important;
            letter-spacing: 0.01em;
        }

        /* ── TEKS ABU → LEBIH HIDUP ── */
        .text-gray-500 { color: #64748b !important; }
        .text-gray-400 { color: #94a3b8 !important; }
        .text-gray-600 { color: #475569 !important; }

        /* ── TABLE ── */
        table { border-collapse: collapse; width: 100%; }
        thead th {
            font-size: 11px !important;
            font-weight: 700 !important;
            letter-spacing: 0.07em !important;
            text-transform: uppercase !important;
            color: #64748b !important;
            background: #f8fafc !important;
            padding: 10px 16px !important;
            border-bottom: 1px solid #e2e8f0 !important;
        }
        tbody td {
            padding: 11px 16px !important;
            font-size: 13.5px !important;
            color: #334155 !important;
            border-bottom: 1px solid #f1f5f9 !important;
            vertical-align: middle !important;
        }
        tbody tr:hover td { background: #f8fafc !important; }
        tbody tr:last-child td { border-bottom: none !important; }

        /* ── BADGE / PILL ── */
        .badge, [class*="px-2"][class*="rounded"] {
            font-size: 11px !important;
            font-weight: 600 !important;
        }

        /* ── SECTION HEADER (judul halaman) ── */
        h1 { font-weight: 800; letter-spacing: -0.5px; color: #0f172a; }
        h2 { font-weight: 700; color: #1e293b; }
        h3 { font-weight: 600; color: #334155; }

        /* ── LINK ── */
        a.text-blue-600, a.text-blue-500 {
            color: #2563eb !important;
            font-weight: 500;
        }
        a.text-blue-600:hover { color: #1d4ed8 !important; }

        /* ── ABU LATAR SEKSI ── */
        .bg-gray-50 { background: #f8fafc !important; }
        .bg-gray-100 { background: #f1f5f9 !important; }

        /* ── BORDER ── */
        .border-gray-200 { border-color: #e2e8f0 !important; }
        .border-gray-100 { border-color: #f1f5f9 !important; }
        .divide-gray-100 > * + * { border-color: #f1f5f9 !important; }

        /* ── PAGINATION ── */
        nav[aria-label] a, nav[aria-label] span {
            border-radius: 8px !important;
            font-size: 13px !important;
            font-weight: 500 !important;
        }

        /* ── SCROLLBAR ── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* ── ANIMASI PULSE ── */
        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }

        /* ── FAILSAFE: semua tombol/link berwarna → teks putih ── */
        /* Cover bg-500 sampai bg-900 semua warna */
        a[class*="bg-blue-5"], a[class*="bg-blue-6"], a[class*="bg-blue-7"], a[class*="bg-blue-8"],
        a[class*="bg-red-5"], a[class*="bg-red-6"], a[class*="bg-red-7"],
        a[class*="bg-emerald-5"], a[class*="bg-emerald-6"], a[class*="bg-emerald-7"],
        a[class*="bg-green-5"], a[class*="bg-green-6"], a[class*="bg-green-7"],
        a[class*="bg-amber-5"], a[class*="bg-amber-6"], a[class*="bg-amber-7"],
        a[class*="bg-orange-5"], a[class*="bg-orange-6"], a[class*="bg-orange-7"],
        a[class*="bg-yellow-5"], a[class*="bg-yellow-6"],
        a[class*="bg-indigo-5"], a[class*="bg-indigo-6"], a[class*="bg-indigo-7"],
        a[class*="bg-violet-5"], a[class*="bg-violet-6"], a[class*="bg-violet-7"],
        a[class*="bg-teal-5"], a[class*="bg-teal-6"],
        a[class*="bg-cyan-5"], a[class*="bg-cyan-6"],
        a[class*="bg-sky-5"], a[class*="bg-sky-6"],
        button[class*="bg-blue-5"], button[class*="bg-blue-6"], button[class*="bg-blue-7"], button[class*="bg-blue-8"],
        button[class*="bg-red-5"], button[class*="bg-red-6"], button[class*="bg-red-7"],
        button[class*="bg-emerald-5"], button[class*="bg-emerald-6"], button[class*="bg-emerald-7"],
        button[class*="bg-green-5"], button[class*="bg-green-6"], button[class*="bg-green-7"],
        button[class*="bg-amber-5"], button[class*="bg-amber-6"], button[class*="bg-amber-7"],
        button[class*="bg-orange-5"], button[class*="bg-orange-6"], button[class*="bg-orange-7"],
        button[class*="bg-yellow-5"], button[class*="bg-yellow-6"],
        button[class*="bg-indigo-5"], button[class*="bg-indigo-6"], button[class*="bg-indigo-7"],
        button[class*="bg-violet-5"], button[class*="bg-violet-6"], button[class*="bg-violet-7"],
        button[class*="bg-teal-5"], button[class*="bg-teal-6"],
        button[class*="bg-cyan-5"], button[class*="bg-cyan-6"],
        button[class*="bg-sky-5"], button[class*="bg-sky-6"] {
            color: #ffffff !important;
        }

        /* ── FAILSAFE: bg gradient → bg biru juga teks putih ── */
        .bg-blue-700, .bg-blue-800,
        .bg-amber-700, .bg-orange-700,
        .bg-red-700, .bg-emerald-700, .bg-green-700,
        .bg-indigo-700, .bg-violet-700 {
            color: #ffffff !important;
        }

        /* ── FAILSAFE: badge/pill /10 opacity → solid ── */
        [class*="bg-blue-50"] { background-color: #eff6ff !important; }
        [class*="bg-emerald-50"] { background-color: #ecfdf5 !important; }
        [class*="bg-green-50"] { background-color: #f0fdf4 !important; }
        [class*="bg-red-50"] { background-color: #fff1f2 !important; }
        [class*="bg-amber-50"] { background-color: #fffbeb !important; }
        [class*="bg-orange-50"] { background-color: #fff7ed !important; }
        [class*="bg-yellow-50"] { background-color: #fefce8 !important; }

        /* ── TAB AKTIF: jelas terlihat ── */
        a.bg-blue-50, button.bg-blue-50 { 
            background-color: #eff6ff !important; 
            color: #1d4ed8 !important;
            border-color: #bfdbfe !important;
        }

        /* ── TEKS WARNA: cukup gelap ── */
        .text-blue-600   { color: #2563eb !important; }
        .text-emerald-600 { color: #059669 !important; }
        .text-green-600  { color: #16a34a !important; }
        .text-red-600    { color: #dc2626 !important; }
        .text-amber-600  { color: #d97706 !important; }
        .text-orange-600 { color: #ea580c !important; }

        /* ── TEKS 700: untuk dalam badge/bg berwarna ── */
        .text-blue-700    { color: #1d4ed8 !important; }
        .text-emerald-700 { color: #047857 !important; }
        .text-green-700   { color: #15803d !important; }
        .text-red-700     { color: #b91c1c !important; }
        .text-amber-700   { color: #b45309 !important; }
        .text-orange-700  { color: #c2410c !important; }
    </style>
</head>
<body>

<div class="simans-wrap">

    {{-- ===== SIDEBAR ===== --}}
    <aside id="simans-sidebar" class="simans-sidebar">

        {{-- Brand --}}
        <div class="simans-brand">
            @php $sSchool = auth()->user()->school; @endphp
            @if($sSchool?->logo_path)
                <img src="{{ Storage::url($sSchool->logo_path) }}" alt="{{ $sSchool->name }}"
                     style="width:36px;height:36px;object-fit:contain;border-radius:8px;flex-shrink:0;background:#eff6ff;padding:4px;border:1px solid #dbeafe">
            @else
                <div class="simans-brand-icon">
                    <svg width="18" height="18" fill="none" stroke="white" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342"/>
                    </svg>
                </div>
            @endif
            <div>
                <div class="simans-brand-name">{{ $sSchool?->name ?? 'SiManS' }}</div>
                <div class="simans-brand-sub">by Padiweb</div>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="simans-nav">
            @php $role = auth()->user()->role; @endphp

            <a href="{{ route('dashboard') }}"
               class="simans-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
                </svg>
                Beranda
            </a>

            {{-- ── SISWA ── --}}
            @if($role === 'siswa')
                <div class="simans-section">Akademik</div>
                <a href="{{ route('siswa.siswa.dashboard') }}" class="simans-link {{ request()->routeIs('siswa.siswa.dashboard') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('siswa.attendance.absensi') }}" class="simans-link {{ request()->routeIs('siswa.attendance.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z M6.75 6.75h.75v.75h-.75v-.75zM6.75 17.25h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75z"/></svg>
                    Absensi
                </a>
                <a href="{{ route('siswa.assignments.index') }}" class="simans-link {{ request()->routeIs('siswa.assignments.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                    Tugas & Nilai
                </a>
                <div class="simans-section">Keuangan</div>
                <a href="{{ route('siswa.payment.index') }}" class="simans-link {{ request()->routeIs('siswa.payment.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
                    Status Pembayaran
                </a>
                <div class="simans-section">Informasi</div>
                <a href="{{ route('siswa.violations') }}" class="simans-link {{ request()->routeIs('siswa.violations') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                    Pelanggaran
                </a>
                @if(auth()->user()?->school?->feature_prakerin)
                    <div class="simans-section">Prakerin</div>
                    <a href="{{ route('siswa.prakerin.index') }}" class="simans-link {{ request()->routeIs('siswa.prakerin.*') ? 'active' : '' }}">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                        Absen & Jurnal
                    </a>
                @endif
            @endif

            {{-- ── GURU / WALI KELAS ── --}}
            @if(in_array($role, ['guru', 'wali_kelas']))
                <div class="simans-section">Kelas</div>
                <a href="{{ route('guru.dashboard') }}" class="simans-link {{ request()->routeIs('guru.dashboard') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('guru.attendance.index') }}" class="simans-link {{ request()->routeIs('guru.attendance.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
                    Absensi Siswa
                </a>
                <a href="{{ route('guru.assignments.index') }}" class="simans-link {{ request()->routeIs('guru.assignments.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                    Tugas & Nilai
                </a>
                <a href="{{ route('guru.journal.index') }}" class="simans-link {{ request()->routeIs('guru.journal.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                    Jurnal Mengajar
                </a>
                <div class="simans-section">Kehadiran</div>
                <a href="{{ route('guru.teacher-attendance.index') }}" class="simans-link {{ request()->routeIs('guru.teacher-attendance.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Absensi Saya
                </a>
                @if(auth()->user()?->school?->feature_prakerin && \App\Models\PrakerinLocation::whereHas('supervisors', fn($q) => $q->where('teacher_id', auth()->id()))->exists())
                    <div class="simans-section">Prakerin</div>
                    <a href="{{ route('guru.prakerin.index') }}" class="simans-link {{ request()->routeIs('guru.prakerin.*') ? 'active' : '' }}">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                        Koordinator Prakerin
                    </a>
                @endif
            @endif

            {{-- ── KESISWAAN ── --}}
            @if($role === 'kesiswaan')
                <div class="simans-section">Kesiswaan</div>
                <a href="{{ route('kesiswaan.dashboard') }}" class="simans-link {{ request()->routeIs('kesiswaan.dashboard') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('kesiswaan.violations.index') }}" class="simans-link {{ request()->routeIs('kesiswaan.violations.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                    Pelanggaran
                </a>
                <a href="#" class="simans-link">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
                    Rekap Absensi
                </a>
            @endif

            {{-- ── BENDAHARA ── --}}
            @if($role === 'bendahara')
                <div class="simans-section">Pembayaran Siswa</div>
                <a href="{{ route('bendahara.dashboard') }}" class="simans-link {{ request()->routeIs('bendahara.dashboard') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('bendahara.bills.index') }}" class="simans-link {{ request()->routeIs('bendahara.bills.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
                    Kelola Tagihan
                </a>
                <a href="{{ route('bendahara.transactions.index') }}" class="simans-link {{ request()->routeIs('bendahara.transactions.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
                    Konfirmasi Transfer
                </a>
                <a href="{{ route('bendahara.payment-types.index') }}" class="simans-link {{ request()->routeIs('bendahara.payment-types.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Jenis & Tarif
                </a>
                <a href="{{ route('bendahara.discount-programs.index') }}" class="simans-link {{ request()->routeIs('bendahara.discount-programs.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                    Beasiswa & Keringanan
                </a>
                <a href="{{ route('bendahara.bills.tunggakan') }}" class="simans-link {{ request()->routeIs('bendahara.bills.tunggakan') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                    Daftar Tunggakan
                </a>
                <div class="simans-section">Keuangan Sekolah</div>
                <a href="{{ route('bendahara.finance.index') }}" class="simans-link {{ request()->routeIs('bendahara.finance.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                    Dashboard Keuangan
                </a>
                <a href="{{ route('bendahara.fund-sources.index') }}" class="simans-link {{ request()->routeIs('bendahara.fund-sources.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
                    Sumber Dana
                </a>
                <a href="{{ route('bendahara.setoran.index') }}" class="simans-link {{ request()->routeIs('bendahara.setoran.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z"/></svg>
                    Setoran Kas
                </a>
                <a href="{{ route('bendahara.expenses.index') }}" class="simans-link {{ request()->routeIs('bendahara.expenses.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181"/></svg>
                    Pengeluaran
                </a>
                <a href="{{ route('bendahara.payroll.index') }}" class="simans-link {{ request()->routeIs('bendahara.payroll.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                    Penggajian
                </a>
            @endif

            {{-- ── KEPALA SEKOLAH ── --}}
            @if($role === 'kepala_sekolah')
                <div class="simans-section">Monitoring</div>
                <a href="{{ route('kepala.dashboard') }}" class="simans-link {{ request()->routeIs('kepala.dashboard') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('bendahara.bills.index') }}" class="simans-link {{ request()->routeIs('bendahara.bills.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
                    Data Tagihan
                </a>
                <div class="simans-section">Keuangan</div>
                <a href="{{ route('bendahara.finance.index') }}" class="simans-link {{ request()->routeIs('bendahara.finance.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                    Dashboard Keuangan
                </a>
                <a href="{{ route('bendahara.expenses.pending') }}" class="simans-link {{ request()->routeIs('bendahara.expenses.pending') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                    Approval Pengeluaran
                </a>
            @endif

            {{-- ── ADMIN ── --}}
            @if($role === 'admin')
                <div class="simans-section">Manajemen</div>
                <a href="{{ route('admin.dashboard') }}" class="simans-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('admin.users.index') }}" class="simans-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                    Manajemen User
                </a>
                <a href="{{ route('admin.classrooms.index') }}" class="simans-link {{ request()->routeIs('admin.classrooms.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/></svg>
                    Manajemen Kelas
                </a>
                <a href="{{ route('admin.subjects.index') }}" class="simans-link {{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                    Mata Pelajaran
                </a>
                <a href="{{ route('admin.schedules.index') }}" class="simans-link {{ request()->routeIs('admin.schedules.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                    Jadwal Mengajar
                </a>
                <a href="{{ route('admin.promotions.index') }}" class="simans-link {{ request()->routeIs('admin.promotions.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"/></svg>
                    Promosi Siswa
                </a>
                <div class="simans-section">Monitoring</div>
                <a href="{{ route('admin.teacher-attendance.index') }}" class="simans-link {{ request()->routeIs('admin.teacher-attendance.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                    Absensi Guru
                </a>
                @if(auth()->user()?->school?->feature_prakerin)
                    <div class="simans-section">Prakerin</div>
                    <a href="{{ route('admin.prakerin.periods.index') }}" class="simans-link {{ request()->routeIs('admin.prakerin.*') ? 'active' : '' }}">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                        Manajemen Prakerin
                    </a>
                @endif
                <div class="simans-section">Sistem</div>
                <a href="{{ route('admin.settings.school') }}" class="simans-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Pengaturan Sekolah
                </a>
                <a href="{{ route('admin.qr.index') }}" class="simans-link {{ request()->routeIs('admin.qr.*') ? 'active' : '' }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z M6.75 6.75h.75v.75h-.75v-.75zM6.75 17.25h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75z"/></svg>
                    Kelola QR Kelas
                </a>
            @endif
        </nav>

        {{-- User footer --}}
        <div class="simans-user">
            <div class="simans-user-inner">
                <div class="simans-avatar">{{ auth()->user()->initials }}</div>
                <div style="flex:1;min-width:0;">
                    <div class="simans-user-name truncate">{{ auth()->user()->name }}</div>
                    <div class="simans-user-role">{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="simans-logout" title="Keluar">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ===== MAIN AREA ===== --}}
    <div class="simans-main">

        {{-- Topbar --}}
        <header class="simans-topbar">
            <button id="simans-hamburger" class="simans-hamburger" aria-label="Menu">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                </svg>
            </button>

            <span class="simans-topbar-title">{{ $title ?? 'Dashboard' }}</span>

            <div style="flex:1"></div>

            <span class="simans-topbar-date hidden sm:block">
                {{ now()->translatedFormat('l, d F Y') }}
            </span>

            <button class="simans-notif" aria-label="Notifikasi">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                </svg>
                <span class="simans-notif-dot"></span>
            </button>
        </header>

        {{-- Page Content --}}
        <main class="simans-content">

            @if(session('success'))
                <div class="alert-success">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert-error">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                    </svg>
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert-error" style="align-items:flex-start">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                    </svg>
                    <ul style="margin:0;padding:0;list-style:none;display:flex;flex-direction:column;gap:4px">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>
</div>

{{-- Sidebar overlay mobile --}}
<div id="simans-overlay" style="display:none;position:fixed;inset:0;z-index:40;background:rgba(15,23,42,0.5)"></div>

<script>
    const sb  = document.getElementById('simans-sidebar');
    const ov  = document.getElementById('simans-overlay');
    const hbg = document.getElementById('simans-hamburger');

    function openSidebar()  { sb.classList.add('open');  ov.style.display = 'block'; }
    function closeSidebar() { sb.classList.remove('open'); ov.style.display = 'none'; }

    hbg?.addEventListener('click', openSidebar);
    ov?.addEventListener('click', closeSidebar);
</script>

@stack('scripts')
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INA AI Form - @yield('title', 'Dashboard')</title>
    <!-- Google Fonts: Inter and Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@300;400;500;600;700;800&family=Courier+Prime:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1A2A3A;
            --primary-light: #2C3E50;
            --primary-hover: #243447;
            --accent: #E6A100;
            --accent-light: #F1B434;
            --accent-soft: #FFF8E7;
            --bg-main: #F0F2F5;
            --bg-paper: #FEFDF9;
            --bg-card: #FFFFFF;
            --text-dark: #1A2A3A;
            --text-muted: #64748B;
            --text-light: #94A3B8;
            --border-color: #333333;
            --border-soft: #E2E8F0;
            --border-input: #CBD5E1;
            --success: #059669;
            --success-bg: #ECFDF5;
            --danger: #DC2626;
            --danger-bg: #FEF2F2;
            --warning: #D97706;
            --warning-bg: #FFFBEB;
            --info: #2563EB;
            --info-bg: #EFF6FF;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.06);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.08), 0 2px 4px rgba(0,0,0,0.05);
            --shadow-lg: 0 10px 25px rgba(0,0,0,0.10), 0 4px 10px rgba(0,0,0,0.06);
            --radius: 8px;
            --radius-sm: 5px;
            --font-main: 'Inter', sans-serif;
            --font-heading: 'Outfit', sans-serif;
            --font-paper: 'Courier Prime', 'Courier New', monospace;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-main);
            background-color: var(--bg-main);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            line-height: 1.6;
            font-size: 15px;
            -webkit-font-smoothing: antialiased;
        }

        /* Navbar Styling */
        .navbar {
            background: linear-gradient(135deg, var(--primary) 0%, #243447 100%);
            color: #FFFFFF;
            padding: 0 2rem;
            height: 56px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar-brand {
            font-family: var(--font-heading);
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.5px;
            color: #FFFFFF;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .navbar-brand span {
            color: var(--accent-light);
        }

        .navbar-menu {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .navbar-link {
            color: rgba(255,255,255,0.80);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.2s ease;
            padding: 0.25rem 0;
        }

        .navbar-link:hover {
            color: var(--accent-light);
        }

        .navbar-link.active {
            color: #FFFFFF;
            border-bottom: 2px solid var(--accent-light);
            padding-bottom: 2px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background-color: var(--primary-light);
            padding: 0.4rem 1rem;
            border-radius: 50px;
            font-size: 0.9rem;
        }

        .user-role {
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 700;
            background-color: var(--accent);
            color: #000000;
            padding: 0.15rem 0.5rem;
            border-radius: 3px;
        }

        .btn-logout {
            background: none;
            border: none;
            color: #ECF0F1;
            cursor: pointer;
            font-family: var(--font-main);
            font-size: 0.95rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            transition: color 0.2s ease;
        }

        .btn-logout:hover {
            color: #E74C3C;
        }

        /* Container & Grid */
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1.5rem;
            flex-grow: 1;
        }

        /* Alerts */
        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .alert-success {
            background-color: #D4EDDA;
            color: #155724;
            border: 1px solid #C3E6CB;
        }

        .alert-danger {
            background-color: #F8D7DA;
            color: #721C24;
            border: 1px solid #F5C6CB;
        }

        .alert-close {
            cursor: pointer;
            font-weight: bold;
            background: none;
            border: none;
            font-size: 1.2rem;
            color: inherit;
        }

        /* Dashboard Styles */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.75rem;
        }

        .dashboard-title {
            font-family: var(--font-heading);
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--text-dark);
            letter-spacing: -0.3px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.55rem 1.25rem;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.18s ease;
            text-decoration: none;
            font-family: var(--font-main);
            gap: 0.4rem;
            letter-spacing: 0.01em;
            white-space: nowrap;
        }

        .btn-primary {
            background-color: var(--primary);
            color: #FFFFFF;
            border: 1px solid var(--primary);
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
            box-shadow: 0 2px 6px rgba(26,42,58,0.25);
        }

        .btn-accent {
            background-color: var(--accent);
            color: #1A2A3A;
            border: 1px solid var(--accent);
            font-weight: 700;
        }

        .btn-accent:hover {
            background-color: var(--accent-light);
            border-color: var(--accent-light);
            box-shadow: 0 2px 6px rgba(230,161,0,0.3);
        }

        .btn-secondary {
            background-color: #FFFFFF;
            color: var(--text-dark);
            border: 1px solid var(--border-input);
        }

        .btn-secondary:hover {
            background-color: #F8FAFC;
            border-color: #94A3B8;
        }

        .btn-success {
            background-color: var(--success);
            color: #FFFFFF;
            border: 1px solid var(--success);
        }

        .btn-success:hover {
            background-color: #047857;
            box-shadow: 0 2px 6px rgba(5,150,105,0.3);
        }

        .btn-danger {
            background-color: var(--danger);
            color: #FFFFFF;
            border: 1px solid var(--danger);
        }

        .btn-danger:hover {
            background-color: #B91C1C;
            box-shadow: 0 2px 6px rgba(220,38,38,0.3);
        }

        .btn-group {
            display: flex;
            gap: 0.5rem;
        }

        /* Document Wrapper (Realistic Paper Sheet) */
        .paper-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 3rem;
        }

        .paper-sheet {
            background-color: var(--bg-paper);
            width: 100%;
            max-width: 850px;
            min-height: 1100px;
            padding: 2.5rem 3rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid #D8D8D0;
            position: relative;
            color: #000000;
        }

        /* Physical Form Aesthetics */
        .form-header-block {
            background-color: #4A4A4A;
            color: #FFFFFF;
            padding: 0.8rem 1.5rem;
            text-align: right;
            font-family: var(--font-heading);
            font-size: 1.8rem;
            font-weight: 800;
            letter-spacing: 1px;
            margin-bottom: 2rem;
            text-transform: uppercase;
        }

        .form-row {
            display: flex;
            margin-bottom: 1.25rem;
            gap: 1.5rem;
        }

        .form-group-custom {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-label-custom {
            font-weight: 700;
            font-size: 0.95rem;
            white-space: nowrap;
        }

        .form-value-box {
            border: 1px solid var(--border-color);
            padding: 0.4rem 0.75rem;
            min-width: 150px;
            font-family: var(--font-paper);
            font-size: 1.05rem;
            background-color: transparent;
            color: #000000;
        }

        .form-value-box-yellow {
            background-color: var(--accent-light);
            border: 1px solid var(--border-color);
            padding: 0.4rem 0.75rem;
            min-width: 150px;
            font-family: var(--font-paper);
            font-size: 1.05rem;
            color: #000000;
        }

        .w-full {
            width: 100%;
        }

        .flex-1 {
            flex: 1;
        }

        /* Tables matching physical sheet */
        .paper-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            margin-bottom: 1rem;
        }

        .paper-table th {
            background-color: #C8C8C8;
            color: #1A2A3A;
            font-weight: 700;
            font-size: 0.85rem;
            padding: 0.6rem 0.75rem;
            border: 1px solid var(--border-color);
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .paper-table td {
            padding: 0.55rem 0.75rem;
            border: 1px solid #AAAAAA;
            font-family: var(--font-paper);
            font-size: 0.95rem;
            background-color: transparent;
            vertical-align: middle;
        }

        .paper-table tr.dashed-row td {
            border-bottom: 1px dashed #9E9E9E;
            border-top: none;
            border-left: 1px solid #AAAAAA;
            border-right: 1px solid #AAAAAA;
        }

        /* Large input area */
        .paper-textarea-section {
            margin-bottom: 1.5rem;
        }

        .paper-textarea-label {
            font-weight: 700;
            font-size: 0.95rem;
            margin-bottom: 0.4rem;
            display: block;
        }

        .paper-textarea-box {
            width: 100%;
            min-height: 80px;
            border: 1px solid var(--border-color);
            padding: 0.75rem;
            font-family: var(--font-paper);
            font-size: 1rem;
            line-height: 1.4;
            background-color: transparent;
            resize: vertical;
        }

        /* Checkbox list */
        .supporting-section {
            margin-bottom: 2rem;
        }

        .supporting-title {
            font-weight: 700;
            font-size: 0.95rem;
            margin-bottom: 0.75rem;
        }

        .supporting-row {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
            gap: 1rem;
        }

        .supporting-label {
            width: 120px;
            font-family: var(--font-main);
            font-size: 0.95rem;
        }

        .supporting-line {
            flex-grow: 1;
            border-bottom: 1px dotted #000000;
            margin: 0 0.5rem;
        }

        .checkbox-box {
            width: 25px;
            height: 25px;
            border: 1px solid var(--border-color);
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: var(--font-paper);
            font-weight: bold;
            font-size: 1.1rem;
            cursor: pointer;
            user-select: none;
        }

        /* Signature block grid layout */
        .signature-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            margin-top: 3rem;
        }

        .signature-grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1.5rem;
            margin-top: 3rem;
        }

        .signature-card {
            border: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
        }

        .signature-title {
            background-color: #D6D6D6;
            color: #000000;
            text-align: center;
            font-weight: 700;
            font-size: 0.9rem;
            padding: 0.3rem;
            border-bottom: 1px solid var(--border-color);
        }

        .signature-body {
            height: 90px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: var(--font-paper);
            font-size: 1.1rem;
            position: relative;
            background-color: transparent;
        }

        .signature-body.highlight {
            background-color: var(--accent-light);
        }

        .signature-name-field {
            border-top: 1px solid var(--border-color);
            padding: 0.3rem;
            text-align: center;
            font-family: var(--font-paper);
            font-size: 0.95rem;
            font-weight: 700;
        }

        .signature-date {
            border-top: 1px solid var(--border-color);
            padding: 0.3rem 0.5rem;
            font-size: 0.8rem;
            font-family: var(--font-paper);
        }

        /* Dashboard elements */
        .dashboard-card {
            background: var(--bg-card);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-soft);
        }

        .dashboard-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0.5rem;
        }

        .dashboard-table th {
            text-align: left;
            padding: 0.65rem 1rem;
            border-bottom: 2px solid var(--border-soft);
            color: var(--text-muted);
            font-size: 0.78rem;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.06em;
            background-color: #F8FAFC;
        }

        .dashboard-table td {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid var(--border-soft);
            font-size: 0.9rem;
            color: var(--text-dark);
            vertical-align: middle;
        }

        .dashboard-table tbody tr:hover {
            background-color: #F8FAFC;
            transition: background-color 0.15s;
        }

        .dashboard-table tbody tr:last-child td {
            border-bottom: none;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .badge-draft { background-color: #E2E8F0; color: #475569; }
        .badge-pending { background-color: #FEF3C7; color: #92400E; }
        .badge-approved { background-color: #D1FAE5; color: #065F46; }
        .badge-verified { background-color: #DBEAFE; color: #1E40AF; }
        .badge-rejected { background-color: #FEE2E2; color: #991B1B; }

        /* Login styles */
        .login-wrapper {
            max-width: 400px;
            width: 100%;
            margin: auto;
            background: #FFFFFF;
            border-radius: 8px;
            padding: 2.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        .login-title {
            font-family: var(--font-heading);
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .input-group {
            margin-bottom: 1.25rem;
        }

        .input-group label {
            display: block;
            font-weight: 600;
            font-size: 0.85rem;
            margin-bottom: 0.4rem;
            color: var(--primary-light);
        }

        .input-control {
            width: 100%;
            padding: 0.6rem 0.85rem;
            border: 1px solid var(--border-input);
            border-radius: var(--radius-sm);
            font-family: var(--font-main);
            font-size: 0.9rem;
            transition: border-color 0.15s, box-shadow 0.15s;
            background-color: #fff;
            color: var(--text-dark);
        }

        .input-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(26,42,58,0.10);
        }

        .text-error {
            color: #E74C3C;
            font-size: 0.8rem;
            margin-top: 0.25rem;
        }

        /* Printable sheets controls */
        .document-actions-bar {
            background-color: var(--bg-card);
            padding: 0.85rem 1.5rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-soft);
            margin-bottom: 1.25rem;
            width: 100%;
            max-width: 850px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Input overrides for form fields on the actual document */
        .paper-sheet input[type="text"], 
        .paper-sheet input[type="date"],
        .paper-sheet select,
        .paper-sheet textarea {
            border: none;
            background: transparent;
            font-family: var(--font-paper);
            font-size: 1.05rem;
            width: 100%;
            padding: 0.2rem 0;
            outline: none;
            color: #000000;
        }

        .paper-sheet select {
            cursor: pointer;
        }

        .paper-sheet input[type="text"]:focus,
        .paper-sheet input[type="date"]:focus,
        .paper-sheet select:focus,
        .paper-sheet textarea:focus {
            background-color: rgba(230, 161, 0, 0.05);
            border-bottom: 1px solid var(--accent);
        }

        /* Hide interface items on print */
        @media print {
            .navbar, .document-actions-bar, .btn, footer {
                display: none !important;
            }
            body {
                background-color: #FFFFFF;
            }
            .container {
                margin: 0;
                padding: 0;
            }
            .paper-sheet {
                box-shadow: none;
                border: none;
                padding: 1.5cm;
                margin: 0;
                width: 100%;
                min-height: auto;
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar { padding: 0 1rem; }
            .container { padding: 0 1rem; margin: 1rem auto; }
            .paper-sheet { padding: 1.5rem 1.25rem; }
            .signature-grid-2, .signature-grid-3 { grid-template-columns: 1fr; gap: 1rem; }
            .document-actions-bar { flex-direction: column; gap: 0.75rem; padding: 1rem; }
            .dashboard-table { font-size: 0.82rem; }
            .dashboard-table th, .dashboard-table td { padding: 0.6rem 0.65rem; }
            .btn { font-size: 0.82rem; padding: 0.5rem 1rem; }
        }
    </style>
</head>
<body>

    <!-- Global Rupiah Formatter Utility -->
    <script>
        /**
         * Format angka menjadi format Rupiah Indonesia (titik sebagai pemisah ribuan)
         * contoh: 1000000 → "1.000.000"
         */
        function rupiahFormat(value) {
            const num = parseInt(String(value).replace(/\D/g, ''), 10);
            if (isNaN(num) || num === 0) return '';
            return num.toLocaleString('id-ID');
        }

        /**
         * Parse string format Rupiah ke raw integer
         * contoh: "1.000.000" → 1000000
         */
        function rupiahParse(str) {
            return parseInt(String(str).replace(/\./g, '').replace(/\D/g, ''), 10) || 0;
        }

        /**
         * Inisialisasi input nominal Rupiah.
         * @param {HTMLInputElement} displayInput - input yang terlihat user (text)
         * @param {HTMLInputElement} hiddenInput  - input hidden yang dikirim ke backend (raw number)
         * @param {Function|null} onChangeCb      - callback opsional setelah nilai berubah
         */
        function initRupiahInput(displayInput, hiddenInput, onChangeCb) {
            // Set nilai awal jika sudah ada di hiddenInput
            if (hiddenInput && hiddenInput.value && hiddenInput.value !== '' && hiddenInput.value !== '0') {
                displayInput.value = rupiahFormat(hiddenInput.value);
            }

            displayInput.addEventListener('input', function() {
                const raw = rupiahParse(this.value);
                const formatted = raw > 0 ? rupiahFormat(raw) : '';
                this.value = formatted;
                if (hiddenInput) hiddenInput.value = raw > 0 ? raw : '';
                if (onChangeCb) onChangeCb();
            });

            displayInput.addEventListener('paste', function(e) {
                e.preventDefault();
                const pasted = (e.clipboardData || window.clipboardData).getData('text');
                const raw = rupiahParse(pasted);
                this.value = raw > 0 ? rupiahFormat(raw) : '';
                if (hiddenInput) hiddenInput.value = raw > 0 ? raw : '';
                if (onChangeCb) onChangeCb();
            });

            // Cegah karakter non-angka (kecuali keys navigasi dan ctrl)
            displayInput.addEventListener('keydown', function(e) {
                const allowedKeys = ['Backspace','Delete','ArrowLeft','ArrowRight','ArrowUp','ArrowDown','Tab','Home','End'];
                if (allowedKeys.includes(e.key)) return;
                if (e.ctrlKey || e.metaKey) return;
                if (!/^\d$/.test(e.key)) e.preventDefault();
            });
        }
    </script>

    @auth
    <header class="navbar">
        <a href="{{ route('dashboard') }}" class="navbar-brand">
            INA AI<span> Form</span>
        </a>
        <div class="navbar-menu">
            <a href="{{ route('dashboard') }}" class="navbar-link {{ Route::is('dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('reimbursement-choice') }}" class="navbar-link {{ (Route::is('reimbursement-choice') || Route::is('travel-requests.*') || Route::is('reimbursements.*')) ? 'active' : '' }}">Travel or Cash Reimbursement</a>
            
            <div class="user-profile">
                <span>{{ Auth::user()->name }}</span>
                <span class="user-role">{{ Auth::user()->role }}</span>
            </div>
            
            <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn-logout">
                    Logout
                </button>
            </form>
        </div>
    </header>
    @endauth

    <main class="container">
        @if (session('success'))
            <div class="alert alert-success">
                <span>{{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="alert-close">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                <span>{{ session('error') }}</span>
                <button onclick="this.parentElement.remove()" class="alert-close">&times;</button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <div>
                    <strong>Validation failed:</strong>
                    <ul style="margin-left: 1.5rem; margin-top: 0.25rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button onclick="this.parentElement.remove()" class="alert-close">&times;</button>
            </div>
        @endif

        @yield('content')
    </main>

    <footer style="text-align: center; padding: 2rem; color: var(--text-light); font-size: 0.85rem; border-top: 1px solid #E2E8F0; margin-top: auto;">
        &copy; 2026 PT Teknologi Cerdas Berdaulat Indonesia. All Rights Reserved.
    </footer>

</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Masuk — StockFlow</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        rel="stylesheet"
    >

    <style>
        :root {
            --emerald: #047857;
            --emerald-dark: #065f46;
            --ink: #172b26;
            --muted: #718078;
            --line: #e4ebe7;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f5f8f6;
            color: var(--ink);
            font-family: "Segoe UI", Arial, sans-serif;
        }

        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px;
        }

        .login-shell {
            width: 100%;
            max-width: 1080px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 24px 80px rgba(27, 57, 43, 0.07);
        }

        .brand-panel {
            padding: 48px;
            background: #edf6f0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 64px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 23px;
            font-weight: 750;
            letter-spacing: -0.8px;
        }

        .brand-icon {
            width: 43px;
            height: 43px;
            display: grid;
            place-items: center;
            background: var(--emerald);
            color: white;
            border-radius: 13px;
            font-size: 21px;
        }

        .eyebrow {
            color: var(--emerald);
            font-size: 11px;
            font-weight: 750;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .hero-title {
            margin: 20px 0;
            font-size: clamp(32px, 4vw, 46px);
            line-height: 1.15;
            font-weight: 750;
            letter-spacing: -1.8px;
        }

        .hero-description {
            max-width: 350px;
            color: #5d7165;
            font-size: 15px;
            line-height: 1.8;
        }

        .feature-list {
            display: grid;
            gap: 16px;
            margin-top: 32px;
        }

        .feature {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #365645;
            font-size: 14px;
        }

        .feature i {
            color: var(--emerald);
        }

        .brand-footer {
            color: #6e8376;
            font-size: 12px;
        }

        .form-panel {
            padding: 64px 52px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .welcome-icon {
            width: 50px;
            height: 50px;
            display: grid;
            place-items: center;
            margin-bottom: 24px;
            background: #f1f6f3;
            border: 1px solid var(--line);
            border-radius: 15px;
            color: var(--emerald);
            font-size: 20px;
        }

        .form-title {
            font-size: 30px;
            font-weight: 750;
            letter-spacing: -1px;
        }

        .form-description {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.7;
            margin-bottom: 30px;
        }

        .form-label {
            font-size: 13px;
            font-weight: 650;
            margin-bottom: 9px;
        }

        .form-control {
            min-height: 50px;
            padding: 12px 14px;
            border: 1px solid #dce5df;
            border-radius: 11px;
            font-size: 14px;
            background: #fcfdfc;
        }

        .form-control:focus {
            border-color: #34a881;
            box-shadow: 0 0 0 4px rgba(4, 120, 87, 0.08);
            background: white;
        }

        .password-field {
            position: relative;
        }

        .password-field .form-control {
            padding-right: 52px;
        }

        .password-toggle {
            position: absolute;
            top: 7px;
            right: 7px;
            width: 36px;
            height: 36px;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #7b8a81;
        }

        .password-toggle:hover {
            background: #edf6f0;
            color: var(--emerald);
        }

        .login-button {
            min-height: 50px;
            width: 100%;
            margin-top: 10px;
            border: 0;
            border-radius: 11px;
            background: var(--emerald);
            color: #fff;
            font-size: 14px;
            font-weight: 650;
            transition: background 0.2s, transform 0.2s;
        }

        .login-button:hover {
            background: var(--emerald-dark);
            transform: translateY(-1px);
        }

        .security-note {
            margin-top: 24px;
            text-align: center;
            color: var(--muted);
            font-size: 12px;
        }

        .alert {
            border-radius: 11px;
            font-size: 13px;
        }

        .field-error {
            margin-top: 7px;
            color: #b42318;
            font-size: 12px;
        }

        .form-footer {
            margin-top: 36px;
            padding-top: 22px;
            border-top: 1px solid var(--line);
            text-align: center;
            color: #89958d;
            font-size: 12px;
        }

        @media (max-width: 767px) {
            .login-page {
                padding: 18px;
            }

            .login-shell {
                grid-template-columns: 1fr;
                max-width: 480px;
                border-radius: 22px;
            }

            .brand-panel {
                padding: 28px;
                gap: 0;
            }

            .brand-story,
            .brand-footer {
                display: none;
            }

            .form-panel {
                padding: 32px 28px;
            }

            .form-title {
                font-size: 27px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .login-button {
                transition: none;
            }
        }
    </style>
</head>

<body>
    <main class="login-page">
        <div class="login-shell">
            <section class="brand-panel" aria-label="Tentang StockFlow">
                <div class="brand">
                    <span class="brand-icon">
                        <i class="fa-solid fa-cube" aria-hidden="true"></i>
                    </span>

                    <span>StockFlow<span style="color: #059669;">.</span></span>
                </div>

                <div class="brand-story">
                    <span class="eyebrow">Inventory & Sales Management</span>

                    <h1 class="hero-title">
                        Stok tertata.<br>
                        Bisnis terjaga.
                    </h1>

                    <p class="hero-description">
                        Satu ruang untuk mengelola persediaan, mencatat
                        transaksi, dan mengikuti aktivitas bisnis Anda.
                    </p>

                    <div class="feature-list">
                        <div class="feature">
                            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                            <span>Persediaan dalam satu tempat</span>
                        </div>

                        <div class="feature">
                            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                            <span>Riwayat pembelian dan penjualan</span>
                        </div>

                        <div class="feature">
                            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                            <span>Akses sesuai peran tim</span>
                        </div>
                    </div>
                </div>

                <div class="brand-footer">
                    Lebih rapi mengelola. Lebih mudah memantau.
                </div>
            </section>

            <section class="form-panel" aria-labelledby="login-title">
                <div class="welcome-icon">
                    <i class="fa-solid fa-arrow-right-to-bracket" aria-hidden="true"></i>
                </div>

                <h2 class="form-title" id="login-title">Selamat datang kembali</h2>

                <p class="form-description">
                    Masuk ke akun Anda untuk membuka ruang kerja StockFlow.
                </p>

                @if (session('success'))
                    <div class="alert alert-success" role="status">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('login.submit') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label for="username" class="form-label">
                            Username
                        </label>

                        <input
                            type="text"
                            id="username"
                            name="username"
                            class="form-control"
                            value="{{ old('username') }}"
                            placeholder="Masukkan username"
                            autocomplete="username"
                            autocapitalize="none"
                            spellcheck="false"
                            maxlength="50"
                            required
                            autofocus
                            @error('username')
                                aria-invalid="true"
                                aria-describedby="username-error"
                            @enderror
                        >

                        @error('username')
                            <div class="field-error" id="username-error" role="alert">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">
                            Password
                        </label>

                        <div class="password-field">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control"
                                placeholder="Masukkan password"
                                autocomplete="current-password"
                                maxlength="255"
                                required
                                @error('password')
                                    aria-invalid="true"
                                    aria-describedby="password-error"
                                @enderror
                            >

                            <button
                                type="button"
                                class="password-toggle"
                                id="toggle-password"
                                aria-label="Tampilkan password"
                                aria-controls="password"
                                aria-pressed="false"
                            >
                                <i class="fa-regular fa-eye" aria-hidden="true"></i>
                            </button>
                        </div>

                        @error('password')
                            <div class="field-error" id="password-error" role="alert">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <button type="submit" class="login-button">
                        Masuk ke akun
                        <i class="fa-solid fa-arrow-right ms-2" aria-hidden="true"></i>
                    </button>
                </form>

                <div class="security-note">
                    <i class="fa-solid fa-lock me-1" aria-hidden="true"></i>
                    Gunakan akun yang diberikan administrator.
                </div>

                <div class="form-footer">
                    &copy; {{ date('Y') }} StockFlow
                </div>
            </section>
        </div>
    </main>

    <script>
        const passwordInput = document.getElementById('password');
        const toggleButton = document.getElementById('toggle-password');

        toggleButton.addEventListener('click', function () {
            const shouldShow = passwordInput.type === 'password';

            passwordInput.type = shouldShow ? 'text' : 'password';

            toggleButton.setAttribute('aria-pressed', String(shouldShow));
            toggleButton.setAttribute(
                'aria-label',
                shouldShow ? 'Sembunyikan password' : 'Tampilkan password'
            );

            toggleButton.querySelector('i').className = shouldShow
                ? 'fa-regular fa-eye-slash'
                : 'fa-regular fa-eye';
        });
    </script>
</body>
</html>
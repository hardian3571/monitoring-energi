<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Manajemen Aset PT. Pupuk Kaltim</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');

        body, html {
            margin: 0;
            padding: 0;
            width: 100%;
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            background-image: url('{{ asset("images/banner.jpg") }}');
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden; 
        }

        .page-overlay {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.75);
            z-index: 0;
        }

        .login-wrapper {
            display: flex;
            flex-direction: column;
            width: 90%;
            max-width: 550px; 
            background: #ffffff;
            border-radius: 20px;
            overflow: hidden; 
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            z-index: 1;
        }

        /* PERBAIKAN FOTO: Dinaikkan lagi dan bayangan ditipiskan */
        .login-header-image {
            width: 100%;
            height: 160px; 
            background-image: url('{{ asset("images/bg-pkt.jpeg") }}');
            background-size: cover;
            /* 1. Fokus dinaikkan kembali ke atas (sebelumnya 70%) */
            background-position: center 20%; 
            position: relative;
        }

        .login-header-image::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; width: 100%; 
            /* 2. Tinggi bayangan putih dikurangi drastis (sebelumnya 35%) */
            height: 15%; 
            background: linear-gradient(to bottom, rgba(255,255,255,0) 0%, rgba(255,255,255,1) 100%);
        }

        .login-form-container {
            padding: 0px 40px 30px 40px; 
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            z-index: 2; 
        }

        .header-title-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            /* 3. Margin ditarik sedikit ke atas agar menyatu dengan sisa fade putih */
            margin-top: -15px; 
            margin-bottom: 25px;
            width: 100%;
        }

        .logo-kiri, .logo-kanan {
            flex: 1;
            display: flex;
            align-items: center;
        }

        .logo-kiri {
            justify-content: flex-start;
        }

        .logo-kanan {
            justify-content: flex-end;
        }

        .logo-kiri img {
            height: 45px; 
            width: auto;
            object-fit: contain;
        }

        .logo-kanan img {
            height: 28px; /* Tetap kecil sesuai permintaan sebelumnya */
            width: auto;
            object-fit: contain;
        }

        .title-text-group {
            flex: 3; 
            text-align: center;
            padding: 0 10px;
        }

        .login-title {
            margin: 0 0 2px 0;
            color: #1e293b;
            font-size: 1.3rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .login-subtitle {
            margin: 0;
            color: #64748b;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .input-group {
            margin-bottom: 18px;
            position: relative;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #334155;
            font-size: 0.85rem;
            font-weight: 700;
        }

        .input-group input {
            width: 100%;
            padding: 12px 15px 12px 40px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 0.95rem;
            box-sizing: border-box;
            transition: 0.3s;
            background: #f8fafc;
        }

        .input-group input:focus {
            outline: none;
            border-color: #f97316;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.2);
            background: #fff;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 38px;
            color: #94a3b8;
            font-size: 1.1rem;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 38px;
            color: #94a3b8;
            cursor: pointer;
            z-index: 10;
        }

        .password-toggle:hover { color: #f97316; }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #f97316, #ea580c);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 4px 6px rgba(234, 88, 12, 0.2);
            margin-top: 5px;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #ea580c, #c2410c);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(234, 88, 12, 0.3);
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 20px;
            border: 1px solid #f87171;
            font-weight: 600;
            text-align: center;
        }

        .login-footer {
            margin-top: 20px;
            font-size: 0.75rem;
            color: #94a3b8;
            text-align: center;
        }

        @media (max-width: 480px) {
            .login-form-container {
                padding: 0px 25px 25px 25px; 
            }
            .login-title {
                font-size: 1.1rem;
            }
            .logo-kiri img { height: 35px; }
            .logo-kanan img { height: 22px; }
        }
    </style>
</head>
<body>

    <div class="page-overlay"></div>

    <div class="login-wrapper">
        
        <div class="login-header-image"></div>

        <div class="login-form-container">
            
            <div class="header-title-wrapper">
                <div class="logo-kiri">
                    <img src="{{ asset('images/logo-pkt.png') }}" alt="Logo PKT">
                </div>
                
                <div class="title-text-group">
                    <h1 class="login-title">Manajemen Aset</h1>
                    <p class="login-subtitle">PT. Pupuk Kalimantan Timur</p>
                </div>

                <div class="logo-kanan">
                    <img src="{{ asset('images/logo-manset.png') }}" alt="Logo Manajemen Aset">
                </div>
            </div>

            @if($errors->any())
                <div class="alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i> Username atau Password salah!
                </div>
            @endif

            <form action="{{ route('login.process') }}" method="POST">
                @csrf
                
                <div class="input-group">
                    <label for="username">Username / Email</label>
                    <i class="fa-solid fa-user input-icon"></i>
                    <input type="text" id="username" name="username" placeholder="Masukkan username" required autocomplete="off">
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <i class="fa-solid fa-lock input-icon"></i>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                    <i class="fa-solid fa-eye password-toggle" onclick="togglePassword()" id="eye-icon"></i>
                </div>

                <button type="submit" class="btn-login"><i class="fa-solid fa-right-to-bracket"></i> MASUK</button>
            </form>

            <div class="login-footer">
                &copy; {{ date('Y') }} PT. Pupuk Kalimantan Timur.<br>All rights reserved.
            </div>

        </div>
    </div>

    <script>
        function togglePassword() {
            const pwd = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                pwd.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
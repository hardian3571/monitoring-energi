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
            
            /* BACKGROUND UTAMA: bg-pkt.jpg */
            background-image: url('{{ asset("images/bg-pkt.jpeg") }}');
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Overlay gelap untuk body agar Background Utama tidak terlalu mencolok */
        .page-overlay {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.7); /* Biru gelap transparan */
            z-index: 0;
        }

        /* CONTAINER KARTU LOGIN UTAMA */
        .login-wrapper {
            display: flex;
            flex-direction: row; /* Sejajar kiri-kanan di Desktop */
            width: 90%;
            max-width: 1000px; /* Lebar maksimal kartu */
            background: rgba(255, 255, 255, 0.98);
            border-radius: 20px;
            overflow: hidden; /* Biar gambar banner tidak keluar dari lengkungan */
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            z-index: 1;
        }

        /* SISI KIRI: BANNER (banner.jpg) */
        .login-banner {
            flex: 1.2; /* Proporsi lebar lebih besar sedikit dari form */
            background-image: url('{{ asset("images/banner.jpg") }}');
            background-size: cover;
            background-position: center center;
            position: relative;
        }

        /* Overlay opsional di atas banner kalau mau dikasih teks */
        .banner-overlay {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(to right, rgba(234, 88, 12, 0.4), rgba(15, 23, 42, 0.2));
        }

        /* SISI KANAN: FORM LOGIN */
        .login-form-container {
            flex: 1;
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* Logo Perusahaan */
        .login-logo {
            /* Pastikan nama file logo sesuai, misal logo.png */
            width: 130px;
            height: auto;
            margin-bottom: 20px;
        }

        .login-title {
            margin: 0 0 5px 0;
            color: #1e293b;
            font-size: 1.5rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .login-subtitle {
            margin: 0 0 30px 0;
            color: #64748b;
            font-size: 0.95rem;
            font-weight: 600;
        }

        /* Input Form */
        .input-group {
            margin-bottom: 20px;
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

        /* Tombol Login */
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
            margin-top: 10px;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #ea580c, #c2410c);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(234, 88, 12, 0.3);
        }

        /* Error Message */
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 20px;
            border: 1px solid #f87171;
            font-weight: 600;
        }

        /* Footer */
        .login-footer {
            margin-top: 30px;
            font-size: 0.75rem;
            color: #94a3b8;
            text-align: left;
        }

        /* ========================================= */
        /* RESPONSIVE UNTUK HP (MOBILE VIEW)         */
        /* ========================================= */
        @media (max-width: 768px) {
            .login-wrapper {
                flex-direction: column; /* Berubah jadi numpuk atas-bawah */
                width: 95%;
            }
            .login-banner {
                min-height: 200px; /* Di HP, banner jadi header kecil di atas */
                width: 100%;
            }
            .login-form-container {
                padding: 30px 20px; /* Padding dikecilkan untuk layar sempit */
            }
        }
    </style>
</head>
<body>

    <div class="page-overlay"></div>

    <div class="login-wrapper">
        
        <div class="login-banner">
            <div class="banner-overlay"></div>
        </div>

        <div class="login-form-container">
            
            <img src="{{ asset('images/logo-pkt.png') }}" alt="Logo PKT" class="login-logo">
            
            <h1 class="login-title">Manajemen Aset</h1>
            <p class="login-subtitle">PT. Pupuk Kalimantan Timur</p>

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
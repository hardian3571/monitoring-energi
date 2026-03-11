<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Energi PKT</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Nunito', sans-serif; background: #f1f5f9; margin: 0; overflow-x: hidden; }
        
        /*SIDEBAR STRUKTUR (FLEXBOX)*/
        .sidebar {
            width: 250px; 
            background: #1e293b; 
            color: white; 
            height: 100vh; /* Full Tinggi Layar */
            position: fixed; 
            top: 0; left: 0;
            transition: 0.3s; 
            z-index: 999;
            display: flex; 
            flex-direction: column; /* Wajib column biar bisa dibagi area-nya */
        }
        
        /* State Tertutup (Desktop) */
        .sidebar.closed { left: -250px; }

        /* 1. BAGIAN ATAS (Judul) - Tidak Ikut Scroll */
        .sidebar-header { 
            padding: 20px; 
            text-align: center; 
            background: #0f172a; 
            border-bottom: 1px solid #334155; 
            flex-shrink: 0; 
        }

        /* 2. BAGIAN TENGAH (Menu) - BISA SCROLL */
        .sidebar-content {
            flex: 1; /* Mengisi sisa ruang kosong */
            overflow-y: auto; /* Scroll aktif jika menu panjang */
            scrollbar-width: thin;
            scrollbar-color: #475569 #1e293b;
        }
        /* Styling Scrollbar Chrome */
        .sidebar-content::-webkit-scrollbar { width: 6px; }
        .sidebar-content::-webkit-scrollbar-track { background: #1e293b; }
        .sidebar-content::-webkit-scrollbar-thumb { background-color: #475569; border-radius: 10px; }

        .sidebar ul { list-style: none; padding: 0; margin: 0; }
        .sidebar ul li a {
            display: block; padding: 15px 20px; color: #cbd5e1; text-decoration: none; transition: 0.3s;
            border-left: 4px solid transparent; font-size: 0.95rem;
        }
        .sidebar ul li a:hover, .sidebar ul li.active a {
            background: #334155; color: #facc15; border-left: 4px solid #facc15;
        }
        .sidebar ul li a i { margin-right: 10px; width: 25px; text-align: center; }

        /* 3. BAGIAN BAWAH (Logout) - Tidak Ikut Scroll (Nempel Bawah) */
        .logout-area { 
            padding: 20px; 
            border-top: 1px solid #334155; 
            background: #0f172a; 
            flex-shrink: 0; /* Mencegah tombol gepeng/hilang */
        }
        .btn-logout {
            width: 100%; background: #ef4444; color: white; border: none; padding: 12px;
            border-radius: 6px; cursor: pointer; font-weight: bold; display: flex; 
            align-items: center; justify-content: center; gap: 10px; transition: 0.2s;
        }
        .btn-logout:hover { background: #dc2626; }

        /*KONTEN UTAMA*/
        .content { 
            margin-left: 250px; 
            transition: 0.3s; 
            min-height: 100vh;
            display: flex; flex-direction: column;
        }
        .content.full { margin-left: 0; }

        .top-navbar {
            background: white; padding: 15px 20px; border-bottom: 1px solid #e2e8f0;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 100;
        }

        /*RESPONSIVE TABLET & HP (< 992px) */
        @media (max-width: 992px) {
            .sidebar { left: -250px; } /* Default Sembunyi */
            .sidebar.active { left: 0; } /* Muncul jika class active */
            
            .content { margin-left: 0; } /* Konten selalu full width */
            /* .content.full tidak perlu diatur ulang karena margin sudah 0 */
            
            /* Overlay Gelap */
            .overlay {
                display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                background: rgba(0,0,0,0.5); z-index: 998;
            }
            .overlay.active { display: block; }
        }
    </style>
</head>
<body>

    <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3 style="margin:0; font-weight:800;"><i class="fa-solid fa-bolt" style="color:#facc15;"></i> ENERGI PKT</h3>
            <small style="color:#94a3b8;">Monitoring System</small>
        </div>

        <div class="sidebar-content">
            <ul>
                <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}"><i class="fa-solid fa-gauge"></i> Dashboard</a>
                </li>
                
                <li class="{{ request()->is('plts*') ? 'active' : '' }}">
                    <a href="{{ route('plts.index') }}"><i class="fa-solid fa-solar-panel"></i> PLTS</a>
                </li>
                
                <li class="{{ request()->is('ss4*') || request()->is('listrik-ss4*') ? 'active' : '' }}">
                    <a href="{{ route('ss4.index') }}"><i class="fa-solid fa-industry"></i> Listrik SS-4</a>
                </li>
                
                <li class="{{ request()->is('trend*') ? 'active' : '' }}">
                    <a href="{{ route('trend.index') }}"><i class="fa-solid fa-chart-line"></i> Trend Multi</a>
                </li>

                @if(auth()->check() && optional(auth()->user())->role == 'admin')
                <li style="margin-top: 20px; padding-left: 20px; font-size: 0.7rem; color: #64748b; font-weight: 800; text-transform: uppercase;">
                    Administrator
                </li>
                <li>
                    <a href="{{ route('users.index') }}"><i class="fa-solid fa-users-gear"></i> Manage Users</a>
                </li>
                @endif
            </ul>
        </div>

        <div class="logout-area">
            <div style="margin-bottom: 15px; font-size: 0.85rem; color: #cbd5e1; text-align: center;">
                Login sebagai: <br> 
                <b style="color: #facc15; font-size: 1rem;">{{ optional(auth()->user())->name ?? 'Guest' }}</b>
            </div>
            
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-logout">
                    <i class="fa-solid fa-right-from-bracket"></i> LOGOUT
                </button>
            </form>
        </div>
    </nav>

    <div class="content" id="content">
        <div class="top-navbar">
            <button onclick="toggleSidebar()" style="background: white; border: 1px solid #cbd5e1; padding: 8px 12px; border-radius: 6px; cursor: pointer; color: #334155;">
                <i class="fa-solid fa-bars" style="font-size: 1.2rem;"></i>
            </button>
            <div style="font-weight: bold; color: #334155;">
                {{ date('d F Y') }}
            </div>
        </div>

        <div style="padding: 20px; flex: 1;">
            @yield('content')
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const content = document.getElementById('content');
            const overlay = document.getElementById('overlay');
            
            // Cek Lebar Layar (992px adalah batas Tablet/Laptop Kecil)
            if (window.innerWidth <= 992) {
                // Mode Tablet/HP: Sidebar Muncul (Active)
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
            } else {
                // Mode Desktop: Sidebar Sembunyi (Closed) & Konten Melebar (Full)
                sidebar.classList.toggle('closed');
                content.classList.toggle('full');
            }
        }
    </script>
</body>
</html>
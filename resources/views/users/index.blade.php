@extends('layouts.app')

@section('content')

<style>
    /* Agar Input Password Rapi & Tidak Keluar Kotak */
    .password-group {
        position: relative;
        width: 100%;
    }
    
    .password-group input {
        width: 100%;
        /* Padding kanan 35px buat tempat ikon mata */
        padding: 8px 35px 8px 8px; 
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        margin-top: 5px;
        /* Agar padding tidak bikin input melebar keluar */
        box-sizing: border-box; 
    }

    .password-group .toggle-icon {
        position: absolute;
        right: 10px;
        top: 60%; /* Sesuaikan vertikal biar pas tengah input */
        transform: translateY(-50%);
        cursor: pointer;
        color: #64748b;
        font-size: 0.9rem;
        z-index: 5;
    }
    
    .password-group .toggle-icon:hover { color: #334155; }

    /* Perbaikan untuk SEMUA Input agar konsisten */
    input[type="text"], input[type="email"], input[type="password"], select {
        box-sizing: border-box; /* Wajib ada biar gak jebol */
    }

    /* --- TAMBAHAN CSS RESPONSIVE --- */
    /* Header Halaman (Judul & Tombol Tambah) */
    .page-header {
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        margin-bottom: 20px;
        flex-wrap: wrap; /* Agar tombol turun ke bawah di layar sangat kecil */
        gap: 15px;
    }

    /* Wrapper Tabel agar bisa di-scroll horizontal di HP */
    .table-responsive-wrapper {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch; /* Biar scrollnya mulus di iOS */
        width: 100%;
    }

    /* Desain Tabel Utama */
    .user-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 500px; /* Lebar minimum tabel agar tidak hancur saat di-compress */
    }

    /* Tombol Aksi CRUD di dalam Tabel */
    .action-buttons {
        display: flex; 
        gap: 5px; 
        justify-content: center;
        flex-wrap: nowrap; /* Pertahankan tombol berdampingan */
    }

    /* Responsif untuk Modal (Formulir) */
    .modal-content-box {
        background: white; 
        padding: 25px; 
        border-radius: 8px; 
        width: 90%; /* Gunakan persentase, bukan px fix */
        max-width: 400px; /* Batas maksimal lebar di layar besar */
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        max-height: 90vh; /* Agar tidak melewati tinggi layar */
        overflow-y: auto; /* Bisa di-scroll kalau isinya kepanjangan */
    }

</style>

<div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
    
    <div class="page-header">
        <h2 style="margin: 0; color: #1e293b;"><i class="fa-solid fa-users-gear"></i> Manajemen User</h2>
        <button onclick="document.getElementById('modalAdd').style.display='flex'" 
                style="background: #2563eb; color: white; border: none; padding: 10px 15px; border-radius: 6px; cursor: pointer; font-weight: bold; white-space: nowrap;">
            <i class="fa-solid fa-plus"></i> Tambah User
        </button>
    </div>

    @if(session('success'))
        <div style="background: #dcfce7; color: #166534; padding: 10px; border-radius: 6px; margin-bottom: 15px;">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 6px; margin-bottom: 15px;">
            {{ session('error') }}
        </div>
    @endif

    <div class="table-responsive-wrapper">
        <table class="user-table">
            <thead style="background: #f1f5f9;">
                <tr>
                    <th style="padding: 10px; text-align: left; min-width: 120px;">Nama</th>
                    <th style="padding: 10px; text-align: left; min-width: 150px;">Username & Email</th>
                    <th style="padding: 10px; text-align: center; min-width: 80px;">Role</th>
                    <th style="padding: 10px; text-align: center; min-width: 100px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 10px; vertical-align: middle;">{{ $u->name }}</td>
                    <td style="padding: 10px; vertical-align: middle;">
                        <b>{{ $u->username }}</b> <br>
                        <small style="color: #64748b;">{{ $u->email }}</small>
                    </td>
                    <td style="padding: 10px; text-align: center; vertical-align: middle;">
                        @if($u->role == 'admin')
                            <span style="background: #dbeafe; color: #1e40af; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold;">ADMIN</span>
                        @else
                            <span style="background: #f1f5f9; color: #475569; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem;">GUEST</span>
                        @endif
                    </td>
                    <td style="padding: 10px; vertical-align: middle;">
                        <div class="action-buttons">
                            <button onclick="editUser({{ $u }})" style="background: #f59e0b; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; transition: 0.2s;" title="Edit User">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            @if($u->id != auth()->user()->id)
                            <form action="{{ route('users.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Yakin hapus user ini?');" style="margin: 0;">
                                @csrf @method('DELETE')
                                <button type="submit" style="background: #ef4444; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; transition: 0.2s;" title="Hapus User">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div id="modalAdd" style="display: none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index: 1000; padding: 15px; box-sizing: border-box;">
    <div class="modal-content-box">
        <h3 style="margin-top: 0; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">Tambah User Baru</h3>
        <form action="{{ route('users.store') }}" method="POST">
            @csrf
            <div style="margin-bottom: 12px;">
                <label style="font-size: 0.9rem; font-weight: bold; color: #334155;">Nama Lengkap</label>
                <input type="text" name="name" required style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
            </div>
            <div style="margin-bottom: 12px;">
                <label style="font-size: 0.9rem; font-weight: bold; color: #334155;">Username</label>
                <input type="text" name="username" required style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
            </div>
            <div style="margin-bottom: 12px;">
                <label style="font-size: 0.9rem; font-weight: bold; color: #334155;">Email</label>
                <input type="email" name="email" required style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
            </div>
            
            <div style="margin-bottom: 12px;">
                <label style="font-size: 0.9rem; font-weight: bold; color: #334155;">Password</label>
                <div class="password-group">
                    <input type="password" name="password" id="add_password" required>
                    <i class="fa-solid fa-eye toggle-icon" onclick="togglePassword('add_password', this)"></i>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="font-size: 0.9rem; font-weight: bold; color: #334155;">Role</label>
                <select name="role" style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
                    <option value="guest">Guest (Hanya Lihat)</option>
                    <option value="admin">Admin (Full Akses)</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 25px;">
                <button type="button" onclick="document.getElementById('modalAdd').style.display='none'" style="flex:1; padding: 10px; cursor: pointer; border: 1px solid #cbd5e1; background: #f8fafc; color: #475569; border-radius: 4px; font-weight: bold;">Batal</button>
                <button type="submit" style="flex:2; background: #2563eb; color: white; border: none; padding: 10px; cursor: pointer; border-radius: 4px; font-weight: bold;">Simpan User</button>
            </div>
        </form>
    </div>
</div>

<div id="modalEdit" style="display: none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index: 1000; padding: 15px; box-sizing: border-box;">
    <div class="modal-content-box">
        <h3 style="margin-top: 0; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">Edit Data User</h3>
        <form id="formEdit" method="POST">
            @csrf @method('PUT')
            <div style="margin-bottom: 12px;">
                <label style="font-size: 0.9rem; font-weight: bold; color: #334155;">Nama Lengkap</label>
                <input type="text" name="name" id="edit_name" required style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
            </div>
            <div style="margin-bottom: 12px;">
                <label style="font-size: 0.9rem; font-weight: bold; color: #334155;">Username</label>
                <input type="text" name="username" id="edit_username" required style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
            </div>
            <div style="margin-bottom: 12px;">
                <label style="font-size: 0.9rem; font-weight: bold; color: #334155;">Email</label>
                <input type="email" name="email" id="edit_email" required style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
            </div>
            
            <div style="margin-bottom: 12px;">
                <label style="font-size: 0.9rem; font-weight: bold; color: #334155;">Password Baru</label>
                <div class="password-group">
                    <input type="password" name="password" id="edit_password" placeholder="Kosongkan jika tidak diganti">
                    <i class="fa-solid fa-eye toggle-icon" onclick="togglePassword('edit_password', this)"></i>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="font-size: 0.9rem; font-weight: bold; color: #334155;">Role</label>
                <select name="role" id="edit_role" style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
                    <option value="guest">Guest (Hanya Lihat)</option>
                    <option value="admin">Admin (Full Akses)</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 25px;">
                <button type="button" onclick="document.getElementById('modalEdit').style.display='none'" style="flex:1; padding: 10px; cursor: pointer; border: 1px solid #cbd5e1; background: #f8fafc; color: #475569; border-radius: 4px; font-weight: bold;">Batal</button>
                <button type="submit" style="flex:2; background: #f59e0b; color: white; border: none; padding: 10px; cursor: pointer; border-radius: 4px; font-weight: bold;">Update Data</button>
            </div>
        </form>
    </div>
</div>

<script>
// Fungsi Edit
function editUser(user) {
    document.getElementById('formEdit').action = '/users/' + user.id;
    document.getElementById('edit_name').value = user.name;
    document.getElementById('edit_username').value = user.username;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_role').value = user.role;
    document.getElementById('edit_password').value = ''; 
    document.getElementById('modalEdit').style.display = 'flex';
}

// Fungsi Toggle Password
function togglePassword(inputId, iconElement) {
    const input = document.getElementById(inputId);
    if (input.type === "password") {
        input.type = "text";
        iconElement.classList.remove('fa-eye');
        iconElement.classList.add('fa-eye-slash');
    } else {
        input.type = "password";
        iconElement.classList.remove('fa-eye-slash');
        iconElement.classList.add('fa-eye');
    }
}
</script>
@endsection
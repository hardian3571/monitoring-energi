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
</style>

<div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0; color: #1e293b;"><i class="fa-solid fa-users-gear"></i> Manajemen User</h2>
        <button onclick="document.getElementById('modalAdd').style.display='flex'" 
                style="background: #2563eb; color: white; border: none; padding: 10px 15px; border-radius: 6px; cursor: pointer; font-weight: bold;">
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

    <table style="width: 100%; border-collapse: collapse;">
        <thead style="background: #f1f5f9;">
            <tr>
                <th style="padding: 10px; text-align: left;">Nama</th>
                <th style="padding: 10px; text-align: left;">Username</th>
                <th style="padding: 10px; text-align: left;">Role</th>
                <th style="padding: 10px; text-align: center;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $u)
            <tr style="border-bottom: 1px solid #e2e8f0;">
                <td style="padding: 10px;">{{ $u->name }}</td>
                <td style="padding: 10px;">
                    <b>{{ $u->username }}</b> <br>
                    <small style="color: #64748b;">{{ $u->email }}</small>
                </td>
                <td style="padding: 10px;">
                    @if($u->role == 'admin')
                        <span style="background: #dbeafe; color: #1e40af; padding: 3px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: bold;">ADMIN</span>
                    @else
                        <span style="background: #f1f5f9; color: #475569; padding: 3px 8px; border-radius: 4px; font-size: 0.8rem;">GUEST</span>
                    @endif
                </td>
                <td style="padding: 10px; text-align: center;">
                    <div style="display: flex; gap: 5px; justify-content: center;">
                        <button onclick="editUser({{ $u }})" style="background: #f59e0b; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        @if($u->id != auth()->user()->id)
                        <form action="{{ route('users.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Yakin hapus user ini?');">
                            @csrf @method('DELETE')
                            <button type="submit" style="background: #ef4444; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">
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

<div id="modalAdd" style="display: none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index: 1000;">
    <div style="background: white; padding: 25px; border-radius: 8px; width: 400px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
        <h3 style="margin-top: 0; margin-bottom: 20px;">Tambah User Baru</h3>
        <form action="{{ route('users.store') }}" method="POST">
            @csrf
            <div style="margin-bottom: 10px;">
                <label>Nama Lengkap</label>
                <input type="text" name="name" required style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
            </div>
            <div style="margin-bottom: 10px;">
                <label>Username</label>
                <input type="text" name="username" required style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
            </div>
            <div style="margin-bottom: 10px;">
                <label>Email</label>
                <input type="email" name="email" required style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
            </div>
            
            <div style="margin-bottom: 10px;">
                <label>Password</label>
                <div class="password-group">
                    <input type="password" name="password" id="add_password" required>
                    <i class="fa-solid fa-eye toggle-icon" onclick="togglePassword('add_password', this)"></i>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label>Role</label>
                <select name="role" style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
                    <option value="guest">Guest (Hanya Lihat)</option>
                    <option value="admin">Admin (Full Akses)</option>
                </select>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="document.getElementById('modalAdd').style.display='none'" style="flex:1; padding: 10px; cursor: pointer; border: 1px solid #cbd5e1; background: #f1f5f9; border-radius: 4px;">Batal</button>
                <button type="submit" style="flex:2; background: #2563eb; color: white; border: none; padding: 10px; cursor: pointer; border-radius: 4px;">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div id="modalEdit" style="display: none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index: 1000;">
    <div style="background: white; padding: 25px; border-radius: 8px; width: 400px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
        <h3 style="margin-top: 0; margin-bottom: 20px;">Edit User</h3>
        <form id="formEdit" method="POST">
            @csrf @method('PUT')
            <div style="margin-bottom: 10px;">
                <label>Nama Lengkap</label>
                <input type="text" name="name" id="edit_name" required style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
            </div>
            <div style="margin-bottom: 10px;">
                <label>Username</label>
                <input type="text" name="username" id="edit_username" required style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
            </div>
            <div style="margin-bottom: 10px;">
                <label>Email</label>
                <input type="email" name="email" id="edit_email" required style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
            </div>
            
            <div style="margin-bottom: 10px;">
                <label>Password Baru (Kosongkan jika tidak diganti)</label>
                <div class="password-group">
                    <input type="password" name="password" id="edit_password">
                    <i class="fa-solid fa-eye toggle-icon" onclick="togglePassword('edit_password', this)"></i>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label>Role</label>
                <select name="role" id="edit_role" style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
                    <option value="guest">Guest</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="document.getElementById('modalEdit').style.display='none'" style="flex:1; padding: 10px; cursor: pointer; border: 1px solid #cbd5e1; background: #f1f5f9; border-radius: 4px;">Batal</button>
                <button type="submit" style="flex:2; background: #f59e0b; color: white; border: none; padding: 10px; cursor: pointer; border-radius: 4px;">Update</button>
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
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ config('app.name') }}</title>
    <style>
        body { background-color: black; color: white; font-family: 'Segoe UI', sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: #111; padding: 40px; border-radius: 15px; width: 350px; box-shadow: 0 0 30px rgba(135, 206, 235, 0.2); border: 1px solid #333; text-align: center; }
        input { width: 100%; padding: 12px; margin-bottom: 20px; background: #222; border: 1px solid #444; color: white; border-radius: 5px; box-sizing: border-box;}
        button { width: 100%; padding: 12px; background: linear-gradient(90deg, #87CEEB, #90EE90); border: none; border-radius: 5px; font-weight: bold; cursor: pointer; color: black; }
        button:hover { opacity: 0.9; }
        .error { color: #ff4d4d; font-size: 0.9rem; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2 style="margin-bottom: 30px; letter-spacing: 2px;">LOGIN SYSTEM</h2>
        <form method="POST" action="{{ route('login.process') }}">
            @csrf
            <input type="text" name="username" placeholder="Username (admin/guest)" required autofocus>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">MASUK DASHBOARD</button>
        </form>
        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif
    </div>
</body>
</html>
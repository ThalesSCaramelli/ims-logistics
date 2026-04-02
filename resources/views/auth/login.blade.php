<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMS Logistics — Login</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: system-ui, sans-serif; }
        body { background: #f5f4f0; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { background: #fff; border: 0.5px solid rgba(0,0,0,0.1); border-radius: 16px; padding: 40px; width: 100%; max-width: 400px; }
        .logo { text-align: center; margin-bottom: 32px; }
        .logo-mark { display: inline-flex; align-items: center; justify-content: center; width: 56px; height: 56px; background: #185FA5; border-radius: 14px; margin-bottom: 12px; }
        .logo-mark span { color: #fff; font-size: 20px; font-weight: 700; letter-spacing: -1px; }
        .logo h1 { font-size: 20px; font-weight: 600; color: #1a1a18; }
        .logo p { font-size: 13px; color: #73726c; margin-top: 4px; }
        .field { margin-bottom: 16px; }
        .field label { display: block; font-size: 12px; color: #73726c; margin-bottom: 6px; font-weight: 500; }
        .field input { width: 100%; font-size: 14px; padding: 10px 12px; border: 0.5px solid #c2c0b6; border-radius: 8px; background: #fff; color: #1a1a18; font-family: inherit; transition: border-color .15s; }
        .field input:focus { outline: none; border-color: #185FA5; }
        .field input.error { border-color: #F09595; background: #FFFAFA; }
        .error-msg { font-size: 12px; color: #A32D2D; margin-top: 4px; }
        .remember { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #73726c; margin-bottom: 20px; cursor: pointer; }
        .remember input { width: auto; accent-color: #185FA5; }
        .btn { width: 100%; padding: 11px; background: #185FA5; color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; font-family: inherit; transition: background .15s; }
        .btn:hover { background: #0C447C; }
        .divider { text-align: center; font-size: 12px; color: #73726c; margin-top: 24px; padding-top: 20px; border-top: 0.5px solid rgba(0,0,0,0.08); }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">
            <div class="logo-mark"><span>IMS</span></div>
            <h1>IMS Logistics</h1>
            <p>Sign in to your account</p>
        </div>

        @if ($errors->any())
            <div style="background:#FCEBEB;border:0.5px solid #F09595;border-radius:8px;padding:10px 12px;margin-bottom:16px;font-size:13px;color:#A32D2D;">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="field">
                <label>Email address</label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="admin@ims.com.au"
                    autocomplete="email"
                    class="{{ $errors->has('email') ? 'error' : '' }}"
                    required
                    autofocus
                >
            </div>

            <div class="field">
                <label>Password</label>
                <input
                    type="password"
                    name="password"
                    placeholder="••••••••"
                    autocomplete="current-password"
                    class="{{ $errors->has('password') ? 'error' : '' }}"
                    required
                >
            </div>

            <label class="remember">
                <input type="checkbox" name="remember"> Keep me signed in
            </label>

            <button type="submit" class="btn">Sign in</button>
        </form>

        <div class="divider">IMS Logistics System &copy; {{ date('Y') }}</div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión · VoltRider Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background:#0b1f1e" class="d-flex align-items-center min-vh-100">
<div class="container" style="max-width: 420px;">
    <div class="card shadow border-0">
        <div class="card-body p-4">
            <h1 class="h4 mb-1">⚡ VoltRider Motors</h1>
            <p class="text-muted small mb-4">Panel administrativo de inventario</p>

            <form method="POST" action="{{ route('login.post') }}" novalidate>
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="email">Correo</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                           class="form-control @error('email') is-invalid @enderror" autofocus required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Contraseña</label>
                    <input id="password" type="password" name="password"
                           class="form-control @error('password') is-invalid @enderror" required>
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember">Recordarme</label>
                </div>
                <button class="btn w-100 text-white" style="background:#0f766e">Ingresar</button>
            </form>

            <hr>
            <p class="small text-muted mb-0">
                Demo: <code>admin@voltrider.com</code> / <code>Admin12345</code>
            </p>
        </div>
    </div>
</div>
</body>
</html>

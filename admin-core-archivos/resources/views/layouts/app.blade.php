<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Panel') · VoltRider Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --vr: #0f766e; }
        .navbar-vr { background: #0b1f1e; }
        .btn-vr { background: var(--vr); color: #fff; border: 0; }
        .btn-vr:hover { background: #0d5f59; color: #fff; }
        .kpi { border-left: 4px solid var(--vr); }
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand navbar-dark navbar-vr mb-4">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="{{ route('productos.index') }}">⚡ VoltRider · Admin</a>
        @auth
        <div class="d-flex align-items-center gap-3 text-white">
            <span class="small d-none d-sm-inline">{{ auth()->user()->nombre }} ({{ auth()->user()->rol }})</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-sm btn-outline-light">Cerrar sesión</button>
            </form>
        </div>
        @endauth
    </div>
</nav>

<main class="container pb-5">
    @if (session('ok'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('ok') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

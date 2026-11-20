@extends('layouts.app')
@section('title', 'Editar producto')
@section('content')
<div class="card shadow-sm"><div class="card-body">
    <h1 class="h4 mb-4">Editar: {{ $producto->nombre }}</h1>
    <form method="POST" action="{{ route('productos.update', $producto) }}">
        @csrf @method('PUT')
        @include('productos._form')
    </form>
</div></div>
@endsection

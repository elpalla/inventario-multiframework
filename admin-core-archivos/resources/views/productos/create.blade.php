@extends('layouts.app')
@section('title', 'Nuevo producto')
@section('content')
<div class="card shadow-sm"><div class="card-body">
    <h1 class="h4 mb-4">Nuevo producto</h1>
    <form method="POST" action="{{ route('productos.store') }}">
        @csrf
        @include('productos._form')
    </form>
</div></div>
@endsection

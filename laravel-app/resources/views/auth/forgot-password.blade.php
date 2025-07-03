@extends('auth.layout')

@section('title', 'Recuperar Contraseña')
@section('subtitle', 'Te enviaremos un enlace de recuperación')

@section('content')
    {{-- Mostrar errores --}}
    @if ($errors->any())
        <div class="alert alert-error">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    {{-- Mostrar mensaje de éxito si existe --}}
    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="form-group">
            <label for="email">📧 Email</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                value="{{ old('email') }}" 
                required 
                autofocus
                placeholder="tu@email.com"
            >
        </div>

        <button type="submit" class="btn">
            📤 Enviar Enlace de Recuperación
        </button>
    </form>
@endsection

@section('footer')
    <p><a href="{{ route('login') }}">← Volver al login</a></p>
@endsection
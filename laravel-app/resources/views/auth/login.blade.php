@extends('auth.layout')

@section('title', 'Iniciar Sesión')
@section('subtitle', 'Accede a tu gestor de tareas')

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

    <form method="POST" action="{{ route('login') }}">
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

        <div class="form-group">
            <label for="password">🔒 Contraseña</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                required
                placeholder="Tu contraseña"
            >
        </div>

        <div class="checkbox-group">
            <input type="checkbox" id="remember" name="remember">
            <label for="remember">Recordarme</label>
        </div>

        <button type="submit" class="btn">
            🚀 Iniciar Sesión
        </button>
    </form>
@endsection

@section('footer')
    <p>Solo usuarios registrados pueden acceder</p>
    
    @if (Route::has('password.request'))
        <p style="margin-top: 8px;">
            <a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
        </p>
    @endif
@endsection
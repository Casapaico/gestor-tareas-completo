@extends('auth.layout')

@section('title', 'Nueva Contraseña')
@section('subtitle', 'Crea una nueva contraseña segura')

@section('content')
    {{-- Mostrar errores --}}
    @if ($errors->any())
        <div class="alert alert-error">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <!-- Token oculto -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="form-group">
            <label for="email">📧 Email</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                value="{{ old('email', $request->email) }}" 
                required 
                autofocus
                placeholder="tu@email.com"
            >
        </div>

        <div class="form-group">
            <label for="password">🔒 Nueva Contraseña</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                required
                placeholder="Mínimo 8 caracteres"
            >
        </div>

        <div class="form-group">
            <label for="password_confirmation">🔒 Confirmar Contraseña</label>
            <input 
                type="password" 
                id="password_confirmation" 
                name="password_confirmation" 
                required
                placeholder="Confirma tu nueva contraseña"
            >
        </div>

        <button type="submit" class="btn">
            💾 Actualizar Contraseña
        </button>
    </form>
@endsection

@section('footer')
    <p><a href="{{ route('login') }}">← Volver al login</a></p>
@endsection
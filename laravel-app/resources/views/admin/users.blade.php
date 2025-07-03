<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Usuarios - Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);
            color: white;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        .user-card {
            transition: all 0.2s ease;
            border-radius: 10px;
            margin-bottom: 15px;
        }
        .user-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.1rem;
        }
        .admin-badge {
            background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);
            color: white;
            border: none;
        }
        .device-status {
            font-size: 0.8rem;
            padding: 4px 8px;
            border-radius: 12px;
        }
        .back-nav {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        .btn-action {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            border-radius: 6px;
            margin: 2px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-3">
        
        {{-- Navegación --}}
        <div class="back-nav">
            <div class="d-flex align-items-center">
                <a href="{{ route('dashboard') }}" class="btn btn-outline-primary me-3">
                    <i class="fas fa-arrow-left"></i> Volver al Dashboard
                </a>
                <div>
                    <h6 class="mb-0">Panel de Administración</h6>
                    <small class="text-muted">Gestiona usuarios del sistema</small>
                </div>
            </div>
        </div>

        {{-- Header Admin --}}
        <div class="admin-header p-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 class="mb-1">
                        <i class="fas fa-users-cog me-2"></i>
                        Gestión de Usuarios
                    </h3>
                    <p class="mb-0 opacity-75">Administra las cuentas del sistema</p>
                </div>
                <div class="col-md-6 text-md-end mt-2 mt-md-0">
                    <a href="{{ route('admin.users.create') }}" class="btn btn-light">
                        <i class="fas fa-user-plus"></i> Crear Usuario
                    </a>
                </div>
            </div>
        </div>

        {{-- Alertas --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Estadísticas --}}
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h4 class="text-primary">{{ $users->count() }}</h4>
                        <small class="text-muted">Total Usuarios</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h4 class="text-success">{{ $users->where('active', true)->count() }}</h4>
                        <small class="text-muted">Usuarios Activos</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h4 class="text-danger">{{ $users->where('active', false)->count() }}</h4>
                        <small class="text-muted">Usuarios Inactivos</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h4 class="text-warning">{{ $users->where('is_admin', true)->count() }}</h4>
                        <small class="text-muted">Administradores</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lista de usuarios --}}
        @if($users->isEmpty())
            <div class="card text-center py-5">
                <div class="card-body">
                    <h5 class="text-muted mb-3">👥 No hay usuarios registrados</h5>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Crear primer usuario
                    </a>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-list"></i> Lista de Usuarios
                    </h6>
                </div>
                <div class="card-body p-0">
                    @foreach($users as $user)
                        <div class="user-card border-bottom p-3">
                            <div class="row align-items-center">
                                {{-- Avatar y información --}}
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar me-3">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0">
                                                {{ $user->name }}
                                                @if($user->is_admin)
                                                    <span class="badge admin-badge ms-1">Admin</span>
                                                @endif
                                                @if($user->id === auth()->id())
                                                    <span class="badge bg-info ms-1">Tú</span>
                                                @endif
                                            </h6>
                                            <small class="text-muted">{{ $user->email }}</small>
                                        </div>
                                    </div>
                                </div>

                                {{-- Estado del dispositivo --}}
                                <div class="col-md-3 mt-2 mt-md-0">
                                    @if($user->user_agent)
                                        <span class="device-status bg-success text-white">
                                            <i class="fas fa-mobile-alt"></i> Dispositivo Vinculado
                                        </span>
                                    @else
                                        <span class="device-status bg-secondary text-white">
                                            <i class="fas fa-times"></i> Sin Dispositivo
                                        </span>
                                    @endif
                                </div>

                                {{-- Información adicional --}}
                                <div class="col-md-2 mt-2 mt-md-0 text-center">
                                    <small class="text-muted d-block">Creado</small>
                                    <small class="text-muted">{{ $user->created_at->format('d/m/Y') }}</small>
                                </div>

                                {{-- Acciones --}}
                                <div class="col-md-3 mt-2 mt-md-0 text-md-end">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.users.edit', $user) }}" 
                                           class="btn btn-warning btn-action btn-sm">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                        
                                        @if($user->user_agent)
                                            <form action="{{ route('admin.users.reset-device', $user) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-info btn-action btn-sm"
                                                        onclick="return confirm('¿Reiniciar dispositivo de {{ $user->name }}?')">
                                                    <i class="fas fa-sync"></i> Reset
                                                </button>
                                            </form>
                                        @endif
                                        
                                        @if($user->id !== auth()->id() && $user->id !== 1)
                                            <form action="{{ route('admin.users.destroy', $user) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-action btn-sm"
                                                        onclick="return confirm('¿Eliminar usuario {{ $user->name }}?')">
                                                    <i class="fas fa-trash"></i> Eliminar
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Información adicional --}}
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle"></i> Información del Sistema
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li><strong>Registro público:</strong> Deshabilitado</li>
                            <li><strong>Dispositivos por usuario:</strong> 1 máximo</li>
                            <li><strong>Reset de dispositivo:</strong> Disponible para admin</li>
                            <li><strong>Usuarios admin:</strong> Pueden gestionar otros usuarios</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-shield-alt"></i> Seguridad
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li><i class="fas fa-check text-success"></i> Control de dispositivo único</li>
                            <li><i class="fas fa-check text-success"></i> Sesiones seguras</li>
                            <li><i class="fas fa-check text-success"></i> Logout automático</li>
                            <li><i class="fas fa-check text-success"></i> Validación de user agent</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-dismiss alerts
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>
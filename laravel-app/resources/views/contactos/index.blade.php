<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Contactos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        .contacto-card {
            transition: all 0.2s ease;
            border-radius: 10px;
            border: 1px solid #dee2e6;
            margin-bottom: 15px;
        }
        .contacto-card:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-color: #007bff;
        }
        .contacto-card.activo {
            border-left: 4px solid #28a745;
        }
        .contacto-card.inactivo {
            border-left: 4px solid #dc3545;
            opacity: 0.8;
        }
        .contacto-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: #007bff;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.1rem;
        }
        .btn-action {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            border-radius: 6px;
            margin: 2px;
        }
        .estado-toggle {
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 15px;
            font-size: 0.8rem;
            border: none;
            transition: all 0.2s ease;
        }
        .estado-toggle:hover {
            transform: scale(1.05);
        }
        .header-actions {
            background: white;
            padding: 1rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }
        .stats-compact {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 0.75rem;
            margin-bottom: 1rem;
        }
        .search-input {
            border-radius: 20px;
            border: 1px solid #dee2e6;
            padding: 0.5rem 1rem;
        }
        .search-input:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        .user-bar {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 1px solid #dee2e6;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .user-bar:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        @media (max-width: 768px) {
            .contacto-avatar {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }
            .btn-action {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-3">
        
        {{-- Barra de usuario autenticado --}}
        @auth
        <div class="row mb-3">
            <div class="col-12">
                <div class="user-bar p-3">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                        <div class="user-info mb-2 mb-md-0">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 1.2rem;">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                    </div>
                                </div>
                                <div>
                                    <span class="fw-bold text-dark">{{ auth()->user()->name }}</span>
                                    <small class="d-block text-muted">{{ auth()->user()->email }}</small>
                                </div>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm" 
                                    onclick="return confirm('¿Cerrar sesión y salir del sistema?')">
                                🚪 Cerrar Sesión
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endauth

        {{-- Header con acciones principales --}}
        <div class="header-actions">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="mb-1">📞 Contactos</h4>
                    <small class="text-muted">Gestiona tus destinatarios</small>
                </div>
                <div class="col-md-6 text-md-end mt-2 mt-md-0">
                    <a href="{{ route('contactos.create') }}" class="btn btn-success me-2">
                        ➕ Agregar
                    </a>
                    <a href="{{ route('tareas.index') }}" class="btn btn-primary">
                        📱 Mensajes
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

        {{-- Estadísticas compactas y buscador --}}
        @if($contactos->isNotEmpty())
            <div class="stats-compact">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="d-flex gap-3">
                            <span><strong>{{ $contactos->count() }}</strong> Total</span>
                            <span class="text-success"><strong>{{ $contactos->where('activo', true)->count() }}</strong> Activos</span>
                            <span class="text-danger"><strong>{{ $contactos->where('activo', false)->count() }}</strong> Inactivos</span>
                        </div>
                    </div>
                    <div class="col-md-6 mt-2 mt-md-0">
                        <input type="text" 
                               id="searchInput" 
                               class="form-control search-input" 
                               placeholder="🔍 Buscar contacto..."
                               onkeyup="filtrarContactos()">
                    </div>
                </div>
            </div>
        @endif

        {{-- Lista de contactos --}}
        @if($contactos->isEmpty())
            <div class="card text-center py-4">
                <div class="card-body">
                    <h5 class="text-muted mb-3">📱 No hay contactos registrados</h5>
                    <p class="text-muted mb-3">Agrega tu primer contacto para empezar a enviar mensajes.</p>
                    <a href="{{ route('contactos.create') }}" class="btn btn-primary btn-lg">
                        ➕ Crear primer contacto
                    </a>
                </div>
            </div>
        @else
            <div id="contactosContainer">
                @foreach($contactos as $contacto)
                    <div class="contacto-card {{ $contacto->activo ? 'activo' : 'inactivo' }} contacto-item" 
                         data-nombre="{{ strtolower($contacto->nombre) }}" 
                         data-numero="{{ $contacto->numero }}">
                        <div class="card-body p-3">
                            <div class="row align-items-center">
                                {{-- Avatar y nombre --}}
                                <div class="col-md-4 col-lg-3">
                                    <div class="d-flex align-items-center">
                                        <div class="contacto-avatar me-3">
                                            {{ strtoupper(substr($contacto->nombre, 0, 1)) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $contacto->nombre }}</h6>
                                            <small class="text-muted">+{{ $contacto->numero }}</small>
                                        </div>
                                    </div>
                                </div>

                                {{-- Descripción --}}
                                <div class="col-md-3 col-lg-4 mt-2 mt-md-0">
                                    @if($contacto->descripcion)
                                        <small class="text-muted">{{ Str::limit($contacto->descripcion, 50) }}</small>
                                    @else
                                        <small class="text-muted fst-italic">Sin descripción</small>
                                    @endif
                                </div>

                                {{-- Estado --}}
                                <div class="col-md-2 mt-2 mt-md-0 text-center">
                                    <button class="estado-toggle {{ $contacto->activo ? 'bg-success text-white' : 'bg-danger text-white' }}" 
                                            onclick="toggleEstado({{ $contacto->id }}, this)">
                                        {{ $contacto->activo ? '✓ Activo' : '✗ Inactivo' }}
                                    </button>
                                </div>

                                {{-- Acciones --}}
                                <div class="col-md-3 col-lg-3 mt-2 mt-md-0 text-md-end">
                                    <a href="{{ route('contactos.edit', $contacto->id) }}" 
                                       class="btn btn-warning btn-action">
                                        ✏️ Editar
                                    </a>
                                    <form action="{{ route('contactos.destroy', $contacto->id) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirm('¿Eliminar {{ $contacto->nombre }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-action">
                                            🗑️ Eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Sin resultados de búsqueda --}}
            <div class="card text-center py-4" id="noResults" style="display: none;">
                <div class="card-body">
                    <h6 class="text-muted">No se encontraron contactos</h6>
                    <small class="text-muted">Intenta con otros términos</small>
                </div>
            </div>
        @endif

        {{-- Acciones rápidas al final --}}
        @if($contactos->isNotEmpty())
            <div class="mt-4 text-center">
                <a href="{{ route('contactos.create') }}" class="btn btn-outline-success">
                    ➕ Agregar otro contacto
                </a>
                <a href="{{ route('tareas.create') }}" class="btn btn-outline-primary ms-2">
                    📝 Crear mensaje
                </a>
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Cambiar estado de contacto
        function toggleEstado(contactoId, button) {
            const originalText = button.textContent;
            button.textContent = 'Cambiando...';
            button.disabled = true;

            fetch(`/contactos/${contactoId}/toggle`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                const card = button.closest('.contacto-card');
                
                if (data.activo) {
                    button.textContent = '✓ Activo';
                    button.className = 'estado-toggle bg-success text-white';
                    card.classList.remove('inactivo');
                    card.classList.add('activo');
                } else {
                    button.textContent = '✗ Inactivo';
                    button.className = 'estado-toggle bg-danger text-white';
                    card.classList.remove('activo');
                    card.classList.add('inactivo');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                button.textContent = originalText;
                alert('Error al cambiar el estado');
            })
            .finally(() => {
                button.disabled = false;
            });
        }

        // Filtrar contactos
        function filtrarContactos() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const contactos = document.querySelectorAll('.contacto-item');
            let visibleCount = 0;

            contactos.forEach(contacto => {
                const nombre = contacto.dataset.nombre;
                const numero = contacto.dataset.numero;
                
                if (nombre.includes(searchTerm) || numero.includes(searchTerm)) {
                    contacto.style.display = 'block';
                    visibleCount++;
                } else {
                    contacto.style.display = 'none';
                }
            });

            // Mostrar/ocultar mensaje de sin resultados
            const noResults = document.getElementById('noResults');
            const container = document.getElementById('contactosContainer');
            
            if (visibleCount === 0 && searchTerm) {
                noResults.style.display = 'block';
                container.style.display = 'none';
            } else {
                noResults.style.display = 'none';
                container.style.display = 'block';
            }
        }

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
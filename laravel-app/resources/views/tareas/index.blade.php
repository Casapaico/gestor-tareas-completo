<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mensajes Programados</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        .mensaje-card {
            transition: all 0.2s ease;
            border-left: 4px solid #007bff;
        }
        .mensaje-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .mensaje-card.completado {
            border-left-color: #28a745;
            background-color: #f8fff9;
        }
        .mensaje-card.vencido {
            border-left-color: #dc3545;
            background-color: #fff8f8;
        }
        .mensaje-preview {
            font-size: 0.95rem;
            line-height: 1.4;
            max-height: 80px;
            overflow: hidden;
            position: relative;
        }
        .mensaje-preview::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 40px;
            height: 20px;
            background: linear-gradient(to right, transparent, white);
        }
        .contacto-badge {
            font-size: 0.7rem;
            margin: 2px;
            border-radius: 12px;
        }
        .fecha-badge {
            font-size: 0.8rem;
            padding: 4px 8px;
        }
        .imagen-thumbnail {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #dee2e6;
        }
        .estado-toggle {
            cursor: pointer;
            user-select: none;
            transition: transform 0.2s ease;
        }
        .estado-toggle:hover {
            transform: scale(1.05);
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }
        .btn-responsive {
            font-size: 0.8rem;
            padding: 0.375rem 0.75rem;
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
            .d-md-none {
                display: block !important;
            }
            .d-md-block {
                display: none !important;
            }
            .btn-responsive {
                font-size: 0.75rem;
                padding: 0.25rem 0.5rem;
                margin: 1px;
            }
            .contacto-badge {
                font-size: 0.65rem;
            }
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid mt-3">
        
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

        {{-- Header --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                    <h1 class="h3 mb-2 mb-md-0">📱 Mensajes Programados</h1>
                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <a href="{{ route('tareas.create') }}" class="btn btn-success">
                            <i class="fas fa-plus"></i> Programar Mensaje
                        </a>
                        <a href="{{ route('contactos.index') }}" class="btn btn-info">
                            <i class="fas fa-users"></i> Contactos
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Alertas --}}
        @if(session('success'))
            <div class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Estadísticas --}}
        @if($tareas->isNotEmpty())
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card stats-card">
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6 col-md-3">
                                    <div class="h4 mb-0">{{ $tareas->count() }}</div>
                                    <small>Total</small>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="h4 mb-0">{{ $tareas->where('completado', false)->where('fecha_hora', '>', now())->count() }}</div>
                                    <small>Programados</small>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="h4 mb-0">{{ $tareas->where('completado', true)->count() }}</div>
                                    <small>Enviados</small>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="h4 mb-0">{{ $tareas->where('completado', false)->where('fecha_hora', '<', now())->count() }}</div>
                                    <small>Vencidos</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Lista de tareas --}}
        @if($tareas->isEmpty())
            <div class="row">
                <div class="col-12">
                    <div class="card text-center py-5">
                        <div class="card-body">
                            <h5 class="text-muted mb-3">📝 No hay mensajes programados</h5>
                            <p class="text-muted mb-4">Programa tu primer mensaje para empezar a recibir notificaciones automáticas.</p>
                            <a href="{{ route('tareas.create') }}" class="btn btn-primary btn-lg">
                                ➕ Programar primer mensaje
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="row">
                @foreach($tareas as $tarea)
                    @php
                        $contactosMultiples = $tarea->contactos;
                        if ($contactosMultiples->isEmpty() && $tarea->contacto) {
                            $contactosMultiples = collect([$tarea->contacto]);
                        }
                        
                        $esVencido = $tarea->fecha_hora->isPast() && !$tarea->completado;
                        $esCompletado = $tarea->completado;
                        $esHoy = $tarea->fecha_hora->isToday();
                        
                        $cardClass = '';
                        if ($esCompletado) $cardClass = 'completado';
                        elseif ($esVencido) $cardClass = 'vencido';
                    @endphp
                    
                    <div class="col-12 col-lg-6 mb-3">
                        <div class="card mensaje-card {{ $cardClass }} h-100">
                            <div class="card-body">
                                {{-- Header del mensaje --}}
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="d-flex align-items-center">
                                        @if($tarea->tieneImagenAdjunta())
                                            <img src="{{ $tarea->url_imagen }}" 
                                                 alt="Imagen adjunta" 
                                                 class="imagen-thumbnail me-3"
                                                 title="Mensaje con imagen">
                                        @endif
                                        <div>
                                            <div class="fecha-badge badge bg-primary">
                                                {{ $tarea->fecha_hora->format('d/m/Y H:i') }}
                                            </div>
                                            @if($esHoy && !$esCompletado)
                                                <span class="badge bg-warning text-dark ms-1">📅 Hoy</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="estado-toggle" data-id="{{ $tarea->id }}">
                                        <span class="estado-text">
                                            @if($esCompletado)
                                                <span class="badge bg-success">✅ Enviado</span>
                                            @elseif($esVencido)
                                                <span class="badge bg-danger">⏰ Vencido</span>
                                            @else
                                                <span class="badge bg-warning text-dark">⏳ Programado</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>

                                {{-- Contenido del mensaje --}}
                                <div class="mensaje-preview mb-3">
                                    {{ $tarea->mensaje_para_enviar }}
                                </div>

                                {{-- Información adicional --}}
                                @if($tarea->tieneImagenAdjunta())
                                    <div class="mb-2">
                                        <small class="text-info">📷 Mensaje con imagen adjunta</small>
                                    </div>
                                @endif

                                {{-- Destinatarios --}}
                                <div class="mb-3">
                                    <small class="text-muted d-block mb-1">
                                        👥 Destinatarios ({{ $contactosMultiples->count() }}):
                                    </small>
                                    <div class="d-flex flex-wrap">
                                        @if($contactosMultiples->isNotEmpty())
                                            @foreach($contactosMultiples->take(4) as $contacto)
                                                <span class="badge bg-{{ $contacto->activo ? 'primary' : 'secondary' }} contacto-badge">
                                                    {{ $contacto->nombre }}
                                                </span>
                                            @endforeach
                                            @if($contactosMultiples->count() > 4)
                                                <span class="badge bg-light text-dark contacto-badge">
                                                    +{{ $contactosMultiples->count() - 4 }} más
                                                </span>
                                            @endif
                                        @else
                                            <span class="badge bg-warning">⚠️ Sin destinatarios</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Acciones --}}
                                <div class="d-flex flex-column flex-sm-row gap-2">
                                    <a href="{{ route('tareas.edit', $tarea->id) }}" 
                                       class="btn btn-warning btn-responsive flex-fill">
                                        ✏️ Editar
                                    </a>
                                    <form action="{{ route('tareas.destroy', $tarea->id) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('¿Estás seguro de eliminar este mensaje?');"
                                          class="flex-fill">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-responsive w-100">
                                            🗑️ Eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Paginación o botón de cargar más (si se implementa después) --}}
            @if($tareas->count() > 10)
                <div class="row mt-4">
                    <div class="col-12 text-center">
                        <small class="text-muted">Mostrando {{ $tareas->count() }} mensajes</small>
                    </div>
                </div>
            @endif
        @endif
    </div>

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Alternar estado de tareas
        document.querySelectorAll('.estado-toggle').forEach(function(element) {
            element.addEventListener('click', function() {
                const tareaId = this.dataset.id;
                fetch(`/tareas/${tareaId}/toggle`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const badge = data.completado 
                            ? '<span class="badge bg-success">✅ Enviado</span>'
                            : '<span class="badge bg-warning text-dark">⏳ Programado</span>';
                        this.querySelector('.estado-text').innerHTML = badge;
                        
                        // Actualizar clase de la card
                        const card = this.closest('.mensaje-card');
                        if (data.completado) {
                            card.classList.add('completado');
                            card.classList.remove('vencido');
                        } else {
                            card.classList.remove('completado');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        });

        // Auto-dismiss alerts
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Animación suave para cards
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Aplicar animación a las cards
        document.querySelectorAll('.mensaje-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });

        // Mostrar tooltip en imágenes
        document.querySelectorAll('.imagen-thumbnail').forEach(img => {
            img.addEventListener('click', function() {
                // Crear modal simple para mostrar imagen grande
                const modal = document.createElement('div');
                modal.className = 'modal fade';
                modal.innerHTML = `
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Imagen del mensaje</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-center">
                                <img src="${this.src}" class="img-fluid rounded" alt="Imagen del mensaje">
                            </div>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
                
                // Eliminar modal del DOM cuando se cierre
                modal.addEventListener('hidden.bs.modal', function() {
                    document.body.removeChild(modal);
                });
            });
        });
    </script>
</body>
</html>
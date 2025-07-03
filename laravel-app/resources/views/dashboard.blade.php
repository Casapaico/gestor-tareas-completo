<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - Gestor de Tareas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .dashboard-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        .dashboard-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
        }
        .bot-status-card {
            border-left: 4px solid #28a745;
        }
        .bot-status-card.disconnected {
            border-left-color: #dc3545;
        }
        .qr-container {
            background: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            border: 2px dashed #dee2e6;
        }
        .qr-code {
            max-width: 256px;
            margin: 0 auto;
        }
        .user-bar {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 1px solid #dee2e6;
            border-radius: 10px;
        }
        .quick-action {
            text-decoration: none;
            color: inherit;
        }
        .quick-action:hover {
            color: inherit;
        }
        .tarea-mini {
            border-left: 3px solid #007bff;
            padding: 8px 12px;
            margin-bottom: 8px;
            background: #f8f9fa;
            border-radius: 6px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid mt-3">
        
        {{-- Barra de usuario --}}
        @auth
        <div class="row mb-4">
            <div class="col-12">
                <div class="user-bar p-3">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                        <div class="user-info mb-2 mb-md-0">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 1.5rem;">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                    </div>
                                </div>
                                <div>
                                    <h5 class="mb-0 text-dark">¡Hola, {{ auth()->user()->name }}!</h5>
                                    <small class="text-muted">{{ auth()->user()->email }}</small>
                                    @if(auth()->user()->is_admin)
                                        <span class="badge bg-danger ms-2">Administrador</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            @if(auth()->user()->is_admin)
                                <a href="{{ route('admin.users') }}" class="btn btn-outline-primary btn-sm">
                                    👥 Gestionar Usuarios
                                </a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm" 
                                        onclick="return confirm('¿Cerrar sesión?')">
                                    🚪 Cerrar Sesión
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endauth

        {{-- Estadísticas principales --}}
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="dashboard-card stat-card p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $stats['tareas_total'] }}</h3>
                            <small class="opacity-75">Total Mensajes</small>
                        </div>
                        <i class="fas fa-envelope fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="dashboard-card bg-success text-white p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $stats['tareas_pendientes'] }}</h3>
                            <small class="opacity-75">Programados</small>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="dashboard-card bg-warning text-white p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $stats['tareas_hoy'] }}</h3>
                            <small class="opacity-75">Para Hoy</small>
                        </div>
                        <i class="fas fa-calendar-day fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="dashboard-card bg-info text-white p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $stats['contactos_activos'] }}</h3>
                            <small class="opacity-75">Contactos Activos</small>
                        </div>
                        <i class="fas fa-users fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Estado del Bot de WhatsApp --}}
            <div class="col-lg-6 mb-4">
                <div class="dashboard-card bot-status-card {{ !$botStatus['connected'] ? 'disconnected' : '' }} p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            <i class="fab fa-whatsapp text-success me-2"></i>
                            Estado del Bot WhatsApp
                        </h5>
                        @if(auth()->user()->is_admin)
                            <button class="btn btn-outline-secondary btn-sm" onclick="checkBotStatus()" id="refreshBot">
                                <i class="fas fa-sync"></i>
                            </button>
                        @endif
                    </div>
                    
                    <div id="botStatusContainer">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-{{ $botStatus['connected'] ? 'success' : 'danger' }} me-2">
                                {{ $botStatus['status'] }}
                            </span>
                            <small class="text-muted">{{ $botStatus['message'] }}</small>
                        </div>

                        @if(!$botStatus['connected'])
                            @if(auth()->user()->is_admin)
                                <div class="mt-3">
                                    <div class="alert alert-warning" role="alert">
                                        <strong>⚠️ Bot desconectado</strong><br>
                                        Como administrador, necesitas escanear el código QR para conectar WhatsApp.
                                    </div>
                                    
                                    <button class="btn btn-primary" onclick="showQR()" id="showQRBtn">
                                        📱 Mostrar Código QR
                                    </button>
                                    
                                    <div id="qrContainer" style="display: none;" class="mt-3">
                                        <div class="qr-container">
                                            <div class="spinner-border text-primary" role="status" id="qrSpinner">
                                                <span class="visually-hidden">Cargando...</span>
                                            </div>
                                            <div id="qrContent" style="display: none;">
                                                <h6>Escanea este código QR con WhatsApp</h6>
                                                <div id="qrCode" class="qr-code mb-3"></div>
                                                <small class="text-muted">
                                                    Abre WhatsApp → Configuración → Dispositivos vinculados → Vincular dispositivo
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-info" role="alert">
                                    <strong>ℹ️ Bot desconectado</strong><br>
                                    El administrador necesita conectar WhatsApp. Tus mensajes se enviarán automáticamente cuando esté disponible.
                                </div>
                            @endif
                        @else
                            <div class="alert alert-success" role="alert">
                                <strong>✅ Bot conectado</strong><br>
                                WhatsApp está listo para enviar tus mensajes automáticos.
                                @if(!auth()->user()->is_admin)
                                    <br><small>Puedes programar mensajes con confianza.</small>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Acciones rápidas --}}
            <div class="col-lg-6 mb-4">
                <div class="dashboard-card p-4">
                    <h5 class="mb-3">🚀 Acciones Rápidas</h5>
                    
                    <div class="row g-3">
                        <div class="col-6">
                            <a href="{{ route('tareas.create') }}" class="quick-action">
                                <div class="dashboard-card bg-primary text-white p-3 text-center">
                                    <i class="fas fa-plus fa-2x mb-2"></i>
                                    <div class="small">Crear Mensaje</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('contactos.create') }}" class="quick-action">
                                <div class="dashboard-card bg-success text-white p-3 text-center">
                                    <i class="fas fa-user-plus fa-2x mb-2"></i>
                                    <div class="small">Agregar Contacto</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('tareas.index') }}" class="quick-action">
                                <div class="dashboard-card bg-info text-white p-3 text-center">
                                    <i class="fas fa-list fa-2x mb-2"></i>
                                    <div class="small">Ver Mensajes</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('contactos.index') }}" class="quick-action">
                                <div class="dashboard-card bg-warning text-white p-3 text-center">
                                    <i class="fas fa-address-book fa-2x mb-2"></i>
                                    <div class="small">Ver Contactos</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Próximas tareas --}}
        @if($tareasProximas->isNotEmpty())
        <div class="row">
            <div class="col-12">
                <div class="dashboard-card p-4">
                    <h5 class="mb-3">⏰ Mensajes Próximos (24 horas)</h5>
                    
                    @foreach($tareasProximas as $tarea)
                        <div class="tarea-mini">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $tarea->fecha_hora->format('H:i') }}</strong>
                                    <span class="ms-2">{{ Str::limit($tarea->mensaje_para_enviar, 50) }}</span>
                                </div>
                                <div>
                                    @php
                                        $contactos = $tarea->contactos->isNotEmpty() ? $tarea->contactos : collect([$tarea->contacto])->filter();
                                    @endphp
                                    <small class="text-muted">{{ $contactos->count() }} destinatario(s)</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    
                    <div class="text-center mt-3">
                        <a href="{{ route('tareas.index') }}" class="btn btn-outline-primary btn-sm">
                            Ver todos los mensajes →
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    
    <script>
        // Verificar estado del bot (solo admin puede refrescar)
        async function checkBotStatus() {
            @if(auth()->user()->is_admin)
                const btn = document.getElementById('refreshBot');
                const icon = btn.querySelector('i');
                
                icon.className = 'fas fa-sync fa-spin';
                btn.disabled = true;
                
                try {
                    const response = await fetch('/dashboard/bot-status');
                    const data = await response.json();
                    location.reload();
                } catch (error) {
                    console.error('Error checking bot status:', error);
                } finally {
                    icon.className = 'fas fa-sync';
                    btn.disabled = false;
                }
            @endif
        }
        
        // Mostrar código QR (solo admin)
        async function showQR() {
            @if(auth()->user()->is_admin)
                const container = document.getElementById('qrContainer');
                const spinner = document.getElementById('qrSpinner');
                const content = document.getElementById('qrContent');
                const btn = document.getElementById('showQRBtn');
                
                container.style.display = 'block';
                spinner.style.display = 'block';
                content.style.display = 'none';
                btn.disabled = true;
                btn.textContent = 'Obteniendo QR...';
                
                try {
                    const response = await fetch('/dashboard/bot-qr');
                    const data = await response.json();
                    
                    if (data.success && data.qr) {
                        const qrElement = document.getElementById('qrCode');
                        qrElement.innerHTML = '';
                        
                        QRCode.toCanvas(qrElement, data.qr, {
                            width: 256,
                            height: 256,
                            colorDark: '#000000',
                            colorLight: '#ffffff'
                        }, function (error) {
                            if (error) {
                                console.error('Error generating QR:', error);
                                alert('Error generando código QR');
                            } else {
                                spinner.style.display = 'none';
                                content.style.display = 'block';
                            }
                        });
                    } else {
                        throw new Error(data.message || 'No se pudo obtener el QR');
                    }
                } catch (error) {
                    console.error('Error getting QR:', error);
                    alert('Error obteniendo código QR: ' + error.message);
                    container.style.display = 'none';
                } finally {
                    btn.disabled = false;
                    btn.textContent = '📱 Mostrar Código QR';
                }
            @endif
        }
        
        // Auto-refresh del estado del bot cada 30 segundos (solo para admin)
        @if(auth()->user()->is_admin)
            setInterval(checkBotStatus, 30000);
        @endif
    </script>
</body>
</html>
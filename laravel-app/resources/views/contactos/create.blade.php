<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agregar Contacto</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .main-card {
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header-gradient {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 2rem;
        }
        .contacto-avatar-placeholder {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
            margin: 0 auto 1rem;
            border: 4px solid rgba(255,255,255,0.3);
            transition: all 0.3s ease;
        }
        .form-floating {
            margin-bottom: 1.5rem;
        }
        .form-floating .form-control {
            border-radius: 15px;
            border: 2px solid #e9ecef;
            padding: 1rem 0.75rem;
            height: auto;
            transition: all 0.3s ease;
        }
        .form-floating .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }
        .form-floating label {
            color: #6c757d;
            padding: 1rem 0.75rem;
        }
        .btn-modern {
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            border: none;
        }
        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .btn-success-modern {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        .btn-success-modern:hover {
            background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
        }
        .btn-secondary-modern {
            background: #6c757d;
            color: white;
        }
        .country-select {
            border-radius: 15px;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        .country-select:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }
        .info-card {
            background: linear-gradient(135deg, #e3f2fd 0%, #f1f8e9 100%);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: none;
        }
        .status-toggle {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        .status-toggle input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        .numero-preview {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 10px 15px;
            border-radius: 20px;
            font-weight: bold;
            text-align: center;
            margin-top: 10px;
            min-height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0.6;
        }
        .validation-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .is-valid + .validation-icon.valid {
            opacity: 1;
            color: #28a745;
        }
        .is-invalid + .validation-icon.invalid {
            opacity: 1;
            color: #dc3545;
        }
        @media (max-width: 768px) {
            .header-gradient {
                padding: 1.5rem 1rem;
            }
            .contacto-avatar-placeholder {
                width: 80px;
                height: 80px;
                font-size: 2rem;
            }
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                {{-- Card principal --}}
                <div class="card main-card">
                    {{-- Header con gradiente --}}
                    <div class="header-gradient text-center">
                        <div class="contacto-avatar-placeholder" id="avatarPlaceholder">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h2 class="mb-0">Agregar Nuevo Contacto</h2>
                        <p class="mb-0 opacity-75">Agrega un contacto para enviar mensajes automáticos</p>
                    </div>

                    {{-- Formulario --}}
                    <div class="card-body p-4">
                        {{-- Botones de navegación --}}
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="d-flex flex-column flex-sm-row gap-2">
                                    <a href="{{ route('contactos.index') }}" class="btn btn-secondary-modern btn-modern">
                                        <i class="fas fa-arrow-left me-2"></i>Volver a Contactos
                                    </a>
                                    <a href="{{ route('tareas.index') }}" class="btn btn-outline-primary">
                                        <i class="fas fa-list me-2"></i>Ver Mensajes
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Errores de validación --}}
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <h6 class="alert-heading">
                                    <i class="fas fa-exclamation-circle me-2"></i>Ups... algo salió mal:
                                </h6>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        {{-- Información de ayuda --}}
                        <div class="info-card">
                            <h6 class="mb-3">
                                <i class="fas fa-lightbulb text-warning me-2"></i>Información Importante
                            </h6>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    <strong>Número válido:</strong> 987654321
                                </div>
                                <div class="col-md-6 mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    <strong>Con código:</strong> 51987654321
                                </div>
                                <div class="col-md-6 mb-2">
                                    <i class="fas fa-info-circle text-info me-2"></i>
                                    <strong>Mínimo:</strong> 9 dígitos
                                </div>
                                <div class="col-md-6 mb-2">
                                    <i class="fas fa-globe text-primary me-2"></i>
                                    <strong>Países:</strong> Perú, México, Colombia, etc.
                                </div>
                            </div>
                        </div>

                        {{-- Formulario --}}
                        <form action="{{ route('contactos.store') }}" method="POST" id="createForm">
                            @csrf

                            {{-- Nombre --}}
                            <div class="form-floating">
                                <input type="text" 
                                       name="nombre" 
                                       id="nombre" 
                                       class="form-control" 
                                       value="{{ old('nombre') }}" 
                                       placeholder="Nombre del contacto"
                                       required
                                       oninput="validateNombre(this)"
                                       maxlength="255">
                                <label for="nombre">
                                    <i class="fas fa-user me-2"></i>Nombre del Contacto
                                </label>
                                <i class="fas fa-check validation-icon valid"></i>
                                <i class="fas fa-times validation-icon invalid"></i>
                                <div class="form-text">
                                    Ej: Juan Pérez, Mamá, Trabajo, etc.
                                </div>
                            </div>

                            {{-- Número de teléfono --}}
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-phone me-2"></i>Número de WhatsApp
                                </label>
                                
                                {{-- Selector de país --}}
                                <select class="form-select country-select" id="countryCode" onchange="updateNumberPreview()">
                                    <option value="51" selected>🇵🇪 Perú (+51)</option>
                                    <option value="52">🇲🇽 México (+52)</option>
                                    <option value="57">🇨🇴 Colombia (+57)</option>
                                    <option value="54">🇦🇷 Argentina (+54)</option>
                                    <option value="34">🇪🇸 España (+34)</option>
                                    <option value="1">🇺🇸 Estados Unidos (+1)</option>
                                    <option value="55">🇧🇷 Brasil (+55)</option>
                                    <option value="56">🇨🇱 Chile (+56)</option>
                                </select>

                                <div class="form-floating">
                                    <input type="text" 
                                           name="numero" 
                                           id="numero" 
                                           class="form-control" 
                                           value="{{ old('numero') }}" 
                                           placeholder="987654321"
                                           required
                                           oninput="validateNumero(this)"
                                           maxlength="15">
                                    <label for="numero">Número (sin código de país)</label>
                                    <i class="fas fa-check validation-icon valid"></i>
                                    <i class="fas fa-times validation-icon invalid"></i>
                                </div>

                                {{-- Vista previa del número --}}
                                <div class="numero-preview" id="numeroPreview">
                                    Número aparecerá aquí...
                                </div>
                            </div>

                            {{-- Descripción --}}
                            <div class="form-floating">
                                <textarea name="descripcion" 
                                          id="descripcion" 
                                          class="form-control" 
                                          placeholder="Descripción del contacto..."
                                          style="height: 100px"
                                          oninput="updateCharCount(this)"
                                          maxlength="255">{{ old('descripcion') }}</textarea>
                                <label for="descripcion">
                                    <i class="fas fa-comment me-2"></i>Descripción (Opcional)
                                </label>
                                <div class="form-text">
                                    <span id="charCount">0</span>/255 caracteres
                                    • Ej: Número personal, Trabajo, Familiar, etc.
                                </div>
                            </div>

                            {{-- Estado activo/inactivo --}}
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <label class="form-label mb-1">
                                            <i class="fas fa-toggle-on me-2"></i>Estado del Contacto
                                        </label>
                                        <div class="form-text">
                                            Solo los contactos activos aparecerán al crear mensajes
                                        </div>
                                    </div>
                                    <label class="status-toggle">
                                        <input type="checkbox" 
                                               name="activo" 
                                               id="activo" 
                                               {{ old('activo', true) ? 'checked' : '' }}
                                               onchange="updateStatusText(this)">
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div class="text-center mt-2">
                                    <span id="statusText" class="badge bg-success fs-6">
                                        Contacto Activo
                                    </span>
                                </div>
                            </div>

                            {{-- Resumen de códigos de país --}}
                            <div class="card mb-4" style="border-radius: 15px; border: 2px solid #e9ecef;">
                                <div class="card-header bg-transparent">
                                    <h6 class="mb-0">
                                        <i class="fas fa-globe me-2"></i>Códigos de País Disponibles
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row small">
                                        <div class="col-md-6">
                                            <ul class="list-unstyled mb-0">
                                                <li class="mb-1"><strong>🇵🇪 Perú:</strong> +51</li>
                                                <li class="mb-1"><strong>🇲🇽 México:</strong> +52</li>
                                                <li class="mb-1"><strong>🇨🇴 Colombia:</strong> +57</li>
                                                <li class="mb-1"><strong>🇦🇷 Argentina:</strong> +54</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <ul class="list-unstyled mb-0">
                                                <li class="mb-1"><strong>🇪🇸 España:</strong> +34</li>
                                                <li class="mb-1"><strong>🇺🇸 Estados Unidos:</strong> +1</li>
                                                <li class="mb-1"><strong>🇧🇷 Brasil:</strong> +55</li>
                                                <li class="mb-1"><strong>🇨🇱 Chile:</strong> +56</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Botones de acción --}}
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="{{ route('contactos.index') }}" class="btn btn-outline-secondary btn-modern">
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-success-modern btn-modern" id="submitBtn">
                                    <i class="fas fa-save me-2"></i>Guardar Contacto
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Validar nombre y actualizar avatar
        function validateNombre(input) {
            const value = input.value.trim();
            const avatar = document.getElementById('avatarPlaceholder');
            
            if (value.length >= 2 && value.length <= 255) {
                input.classList.remove('is-invalid');
                input.classList.add('is-valid');
                // Actualizar avatar con primera letra
                avatar.textContent = value.charAt(0).toUpperCase();
            } else {
                input.classList.remove('is-valid');
                input.classList.add('is-invalid');
                avatar.innerHTML = '<i class="fas fa-user-plus"></i>';
            }
        }

        // Validar número
        function validateNumero(input) {
            const value = input.value.replace(/\D/g, ''); // Solo números
            input.value = value; // Actualizar el input solo con números
            
            if (value.length >= 9 && value.length <= 15) {
                input.classList.remove('is-invalid');
                input.classList.add('is-valid');
            } else {
                input.classList.remove('is-valid');
                input.classList.add('is-invalid');
            }
            
            updateNumberPreview();
        }

        // Actualizar vista previa del número
        function updateNumberPreview() {
            const countryCode = document.getElementById('countryCode').value;
            const numero = document.getElementById('numero').value.replace(/\D/g, '');
            const preview = document.getElementById('numeroPreview');
            
            if (numero) {
                preview.textContent = `+${countryCode}${numero}`;
                preview.style.opacity = '1';
            } else {
                preview.textContent = 'Número aparecerá aquí...';
                preview.style.opacity = '0.6';
            }
        }

        // Actualizar texto del estado
        function updateStatusText(checkbox) {
            const statusText = document.getElementById('statusText');
            if (checkbox.checked) {
                statusText.textContent = 'Contacto Activo';
                statusText.className = 'badge bg-success fs-6';
            } else {
                statusText.textContent = 'Contacto Inactivo';
                statusText.className = 'badge bg-danger fs-6';
            }
        }

        // Contador de caracteres
        function updateCharCount(textarea) {
            const count = textarea.value.length;
            const counter = document.getElementById('charCount');
            counter.textContent = count;
            
            if (count > 255) {
                counter.style.color = '#dc3545';
                textarea.classList.add('is-invalid');
            } else {
                counter.style.color = '#6c757d';
                textarea.classList.remove('is-invalid');
            }
        }

        // Validación del formulario
        document.getElementById('createForm').addEventListener('submit', function(e) {
            const nombre = document.getElementById('nombre').value.trim();
            const numero = document.getElementById('numero').value.replace(/\D/g, '');
            
            if (nombre.length < 2) {
                e.preventDefault();
                alert('El nombre debe tener al menos 2 caracteres.');
                document.getElementById('nombre').focus();
                return;
            }
            
            if (numero.length < 9) {
                e.preventDefault();
                alert('El número debe tener al menos 9 dígitos.');
                document.getElementById('numero').focus();
                return;
            }
            
            // Mostrar loading en el botón
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Guardando...';
            submitBtn.disabled = true;
        });

        // Auto-dismiss alerts
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 7000);

        // Inicializar
        document.addEventListener('DOMContentLoaded', function() {
            updateNumberPreview();
        });
    </script>
</body>
</html>
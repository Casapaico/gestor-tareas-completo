<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Tarea</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        .preview-imagen {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            border: 2px dashed #dee2e6;
        }
        .mensaje-counter {
            font-size: 0.875rem;
            color: #6c757d;
        }
    </style>
</head>
<body class="container mt-5">

    <h1 class="mb-4">✏️ Editar Tarea</h1>
    
    <a href="{{ route('tareas.index') }}" class="btn btn-secondary mb-4">⬅️ Volver a la lista</a>

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Ups... algo salió mal:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>🔸 {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('tareas.update', $tarea->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row">
            {{-- Columna principal --}}
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">📱 Mensaje de WhatsApp</h5>
                    </div>
                    <div class="card-body">
                        {{-- Campo único de mensaje --}}
                        <div class="mb-3">
                            <label for="mensaje_personalizado" class="form-label">
                                <strong>Mensaje a enviar</strong> 
                                <span class="text-danger">*</span>
                            </label>
                            <textarea 
                                name="mensaje_personalizado" 
                                id="mensaje_personalizado" 
                                class="form-control" 
                                rows="6" 
                                required
                                placeholder="Escribe aquí el mensaje que quieres enviar por WhatsApp..."
                                maxlength="1000"
                                oninput="actualizarContador()"
                            >{{ old('mensaje_personalizado', $tarea->mensaje_personalizado ?: ($tarea->titulo . "\n\n" . $tarea->descripcion)) }}</textarea>
                            <div class="d-flex justify-content-between">
                                <div class="form-text">
                                    Modifica el mensaje que se enviará por WhatsApp.
                                </div>
                                <div class="mensaje-counter">
                                    <span id="contador-chars">0</span>/1000 caracteres
                                </div>
                            </div>
                        </div>

                        {{-- Imagen actual y nueva --}}
                        <div class="mb-3">
                            <label class="form-label">
                                <strong>Imagen adjunta</strong> 
                                <small class="text-muted">(Opcional)</small>
                            </label>
                            
                            @if($tarea->tieneImagenAdjunta())
                                <div class="mb-3">
                                    <div class="alert alert-info">
                                        <strong>📷 Imagen actual:</strong>
                                        <br>
                                        <img src="{{ $tarea->url_imagen }}" alt="Imagen actual" class="preview-imagen mt-2">
                                        <br>
                                        <small class="text-muted">{{ basename($tarea->imagen_adjunta) }}</small>
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" name="eliminar_imagen" id="eliminar_imagen">
                                            <label class="form-check-label" for="eliminar_imagen">
                                                🗑️ Eliminar imagen actual
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            <input 
                                type="file" 
                                name="imagen" 
                                id="imagen" 
                                class="form-control"
                                accept="image/*"
                                onchange="previsualizarImagen(this)"
                            >
                            <div class="form-text">
                                @if($tarea->tieneImagenAdjunta())
                                    Selecciona una nueva imagen para reemplazar la actual.
                                @else
                                    Formatos admitidos: JPG, PNG, GIF (máximo 5MB)
                                @endif
                            </div>
                            <img id="preview" class="preview-imagen mt-2" alt="Vista previa" style="display: none;">
                        </div>

                        {{-- Fecha y Hora --}}
                        <div class="mb-3">
                            <label for="fecha_hora" class="form-label">
                                <strong>Fecha y Hora de Envío</strong> <span class="text-danger">*</span>
                            </label>
                            <input type="datetime-local" name="fecha_hora" id="fecha_hora" 
                                   class="form-control" 
                                   value="{{ old('fecha_hora', $tarea->fecha_hora->format('Y-m-d\TH:i')) }}" 
                                   required>
                        </div>

                        {{-- Completado --}}
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="completado" 
                                   id="completado" {{ old('completado', $tarea->completado) ? 'checked' : '' }}>
                            <label class="form-check-label" for="completado">
                                Marcar como completada
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Selección de contactos --}}
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">👥 Destinatarios</h6>
                        <span class="badge bg-primary" id="contador">0/5</span>
                    </div>
                    <div class="card-body">
                        @php
                            $contactosSeleccionados = collect();
                            if ($tarea->contactos->isNotEmpty()) {
                                $contactosSeleccionados = $tarea->contactos->pluck('id');
                            } elseif ($tarea->contacto_id) {
                                $contactosSeleccionados = collect([$tarea->contacto_id]);
                            }
                            $contactosSeleccionados = collect(old('contactos', $contactosSeleccionados->toArray()));
                        @endphp
                        
                        <p class="small text-muted mb-3">
                            Modifica los contactos que recibirán el mensaje.
                        </p>

                        <div class="row g-2" style="max-height: 400px; overflow-y: auto;">
                            @foreach($contactos as $contacto)
                                <div class="col-12">
                                    <div class="card border {{ $contactosSeleccionados->contains($contacto->id) ? 'border-primary bg-light' : '' }}" 
                                         onclick="toggleContacto({{ $contacto->id }})" style="cursor: pointer;">
                                        <div class="card-body p-2">
                                            <div class="form-check">
                                                <input 
                                                    class="form-check-input contacto-checkbox" 
                                                    type="checkbox" 
                                                    name="contactos[]" 
                                                    value="{{ $contacto->id }}"
                                                    id="contacto_{{ $contacto->id }}"
                                                    {{ $contactosSeleccionados->contains($contacto->id) ? 'checked' : '' }}
                                                    onchange="actualizarContadorContactos()"
                                                >
                                                <label class="form-check-label w-100" for="contacto_{{ $contacto->id }}">
                                                    <div>
                                                        <strong>{{ $contacto->nombre }}</strong>
                                                        @if($contactosSeleccionados->contains($contacto->id))
                                                            <span class="badge bg-success ms-1">✓</span>
                                                        @endif
                                                        <br>
                                                        <small class="text-muted">+{{ $contacto->numero }}</small>
                                                        @if($contacto->descripcion)
                                                            <br>
                                                            <small class="text-info">{{ Str::limit($contacto->descripcion, 20) }}</small>
                                                        @endif
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Información actual --}}
                        <div class="alert alert-light mt-3">
                            <small>
                                <strong>📋 Contactos actuales:</strong> {{ $tarea->contactos->count() + ($tarea->contacto ? 1 : 0) }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Botones de acción --}}
        <div class="row mt-4">
            <div class="col-12">
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="{{ route('tareas.index') }}" class="btn btn-outline-secondary">
                        ❌ Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary" id="btnGuardar">
                        💾 Actualizar Tarea
                    </button>
                </div>
            </div>
        </div>
    </form>

    <script>
        function toggleContacto(contactoId) {
            const checkbox = document.getElementById(`contacto_${contactoId}`);
            const card = checkbox.closest('.card');
            
            checkbox.checked = !checkbox.checked;
            
            if (checkbox.checked) {
                if (document.querySelectorAll('.contacto-checkbox:checked').length > 5) {
                    checkbox.checked = false;
                    alert('Solo puedes seleccionar máximo 5 contactos.');
                    return;
                }
                card.classList.add('border-primary', 'bg-light');
            } else {
                card.classList.remove('border-primary', 'bg-light');
            }
            
            actualizarContadorContactos();
        }

        function actualizarContadorContactos() {
            const checkboxes = document.querySelectorAll('.contacto-checkbox:checked');
            const count = checkboxes.length;
            const contador = document.getElementById('contador');
            
            contador.textContent = `${count}/5`;
            contador.className = `badge ${count > 0 ? 'bg-success' : 'bg-secondary'}`;
        }

        function actualizarContador() {
            const textarea = document.getElementById('mensaje_personalizado');
            const contador = document.getElementById('contador-chars');
            const length = textarea.value.length;
            
            contador.textContent = length;
            contador.style.color = length > 900 ? '#dc3545' : '#6c757d';
        }

        function previsualizarImagen(input) {
            const preview = document.getElementById('preview');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }

        // Inicializar al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            actualizarContador();
            actualizarContadorContactos();
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
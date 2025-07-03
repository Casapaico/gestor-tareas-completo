<?php

namespace App\Http\Controllers; // Controlador para gestionar tareas

use App\Models\Tarea;   // Modelo de Tarea
use App\Models\Contacto;    // Modelo de Contacto
use Illuminate\Http\Request;    // Importa la clase Request para manejar solicitudes HTTP
use Illuminate\Support\Facades\Storage; // Importa la clase Storage para manejar archivos

class TareaController extends Controller    // Controlador para gestionar tareas
{
    /**
     * Mostrar la lista de tareas
     */
    public function index() // Método para mostrar la lista de tareas
    {
        // Solo mostrar tareas del usuario autenticado
        $tareas = Tarea::with(['contacto', 'contactos'])
                      ->where('user_id', auth()->id())
                      ->orderBy('fecha_hora', 'asc')
                      ->get();
        return view('tareas.index', compact('tareas'));
    }

    /**
     * Mostrar el formulario de creación
     */
    public function create() // Método para mostrar el formulario de creación de una nueva tarea
    {
        // Solo mostrar contactos del usuario autenticado
        $contactos = Contacto::where('user_id', auth()->id())
                            ->where('activo', true)
                            ->orderBy('nombre')
                            ->get();
        return view('tareas.create', compact('contactos'));
    }

    /**
     * Guardar una nueva tarea
     */
    public function store(Request $request) // Método para guardar una nueva tarea
    {
        $request->validate([    // Validación de los datos de la tarea
            'mensaje_personalizado' => 'required|string|max:1000',  // Mensaje personalizado de la tarea
            'fecha_hora' => 'required|date',    // Fecha y hora de la tarea
            'contactos' => 'required|array|min:1|max:5',    // Debe seleccionar al menos un contacto, máximo 5
            'contactos.*' => 'exists:contactos,id', // Cada contacto debe existir en la tabla de contactos
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB máximo
        ], [
            'mensaje_personalizado.required' => 'Debes escribir un mensaje.',  // Mensaje de error si el mensaje personalizado es requerido
            'mensaje_personalizado.max' => 'El mensaje no puede exceder 1000 caracteres.',  // Mensaje de error si el mensaje personalizado excede el límite
            'contactos.required' => 'Debes seleccionar al menos un contacto.',  // Mensaje de error si no se selecciona ningún contacto
            'contactos.min' => 'Debes seleccionar al menos un contacto.',   // Mensaje de error si no se selecciona ningún contacto
            'contactos.max' => 'Puedes seleccionar máximo 5 contactos.',    // Mensaje de error si se seleccionan más de 5 contactos
            'contactos.*.exists' => 'Uno de los contactos seleccionados no es válido.', // Mensaje de error si alguno de los contactos no existe
            'imagen.image' => 'El archivo debe ser una imagen.',    // Mensaje de error si el archivo no es una imagen
            'imagen.mimes' => 'La imagen debe ser JPG, PNG o GIF.', // Mensaje de error si la imagen no es del tipo permitido
            'imagen.max' => 'La imagen no puede pesar más de 5MB.', // Mensaje de error si la imagen excede el tamaño máximo
        ]);

        // Verificar que todos los contactos seleccionados pertenecen al usuario autenticado
        $contactosValidos = Contacto::where('user_id', auth()->id())
                                  ->whereIn('id', $request->contactos)
                                  ->pluck('id')
                                  ->toArray();
        
        if (count($contactosValidos) !== count($request->contactos)) {
            return back()->withErrors(['contactos' => 'Algunos contactos seleccionados no son válidos.']);
        }

        try {
            // Manejar subida de imagen
            $nombreImagen = null;
            if ($request->hasFile('imagen')) {
                $imagen = $request->file('imagen');
                $nombreImagen = time() . '_' . uniqid() . '.' . $imagen->getClientOriginalExtension();
                $imagen->storeAs('public/tareas', $nombreImagen);
            }

            // Crear la tarea
            $tarea = Tarea::create([
 
                'user_id' => auth()->id() ?? null, // Temporal: permitir null si no hay usuario autenticado
                'titulo' => $this->extraerTitulo($request->mensaje_personalizado),
                'descripcion' => $request->mensaje_personalizado,
                'mensaje_personalizado' => $request->mensaje_personalizado,
                'imagen_adjunta' => $nombreImagen,
                'fecha_hora' => $request->fecha_hora,
                'completado' => $request->has('completado'),
    ]);

            // Asociar múltiples contactos (solo los válidos)
            $tarea->contactos()->attach($contactosValidos);

            $contactosCount = count($contactosValidos);
            $mensaje = $contactosCount === 1 
                ? '✅ Tarea creada exitosamente para 1 contacto.' 
                : "✅ Tarea creada exitosamente para {$contactosCount} contactos.";

            if ($nombreImagen) {
                $mensaje .= ' 📷 Con imagen adjunta.';
            }

            return redirect()->route('tareas.index')->with('success', $mensaje);

        } catch (\Exception $e) {
            \Log::error('Error al crear tarea:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['error' => 'Error al crear la tarea: ' . $e->getMessage()])
                        ->withInput();
        }
    }

    /**
     * Mostrar los detalles de una tarea
     */
    public function show($id)
    {
        $tarea = Tarea::with(['contacto', 'contactos'])
                     ->where('user_id', auth()->id())
                     ->findOrFail($id);
        return view('tareas.show', compact('tarea'));
    }

    /**
     * Mostrar el formulario de edición
     */
    public function edit($id)
    {
        $tarea = Tarea::with(['contacto', 'contactos'])
                     ->where('user_id', auth()->id())
                     ->findOrFail($id);
        $contactos = Contacto::where('user_id', auth()->id())
                            ->where('activo', true)
                            ->orderBy('nombre')
                            ->get();
        return view('tareas.edit', compact('tarea', 'contactos'));
    }

    /**
     * Actualizar una tarea existente
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'mensaje_personalizado' => 'required|string|max:1000',
            'fecha_hora' => 'required|date',
            'contactos' => 'required|array|min:1|max:5',
            'contactos.*' => 'exists:contactos,id',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ], [
            'mensaje_personalizado.required' => 'Debes escribir un mensaje.',
            'mensaje_personalizado.max' => 'El mensaje no puede exceder 1000 caracteres.',
            'contactos.required' => 'Debes seleccionar al menos un contacto.',
            'contactos.min' => 'Debes seleccionar al menos un contacto.',
            'contactos.max' => 'Puedes seleccionar máximo 5 contactos.',
            'imagen.max' => 'La imagen no puede pesar más de 5MB.',
        ]);

        $tarea = Tarea::where('user_id', auth()->id())->findOrFail($id);
        
        // Verificar que todos los contactos seleccionados pertenecen al usuario autenticado
        $contactosValidos = Contacto::where('user_id', auth()->id())
                                  ->whereIn('id', $request->contactos)
                                  ->pluck('id')
                                  ->toArray();
        
        if (count($contactosValidos) !== count($request->contactos)) {
            return back()->withErrors(['contactos' => 'Algunos contactos seleccionados no son válidos.']);
        }

        try {
            // Manejar imagen
            $nombreImagen = $tarea->imagen_adjunta;
            
            // Eliminar imagen actual si se solicita
            if ($request->has('eliminar_imagen') && $nombreImagen) {
                Storage::delete('public/tareas/' . $nombreImagen);
                $nombreImagen = null;
            }
            
            // Subir nueva imagen
            if ($request->hasFile('imagen')) {
                // Eliminar imagen anterior si existe
                if ($nombreImagen) {
                    Storage::delete('public/tareas/' . $nombreImagen);
                }
                
                $imagen = $request->file('imagen');
                $nombreImagen = time() . '_' . uniqid() . '.' . $imagen->getClientOriginalExtension();
                $imagen->storeAs('public/tareas', $nombreImagen);
            }
            
            // Actualizar datos de la tarea
            $tarea->update([
                'titulo' => $this->extraerTitulo($request->mensaje_personalizado),
                'descripcion' => $request->mensaje_personalizado,
                'mensaje_personalizado' => $request->mensaje_personalizado,
                'imagen_adjunta' => $nombreImagen,
                'fecha_hora' => $request->fecha_hora,
                'completado' => $request->has('completado'),
            ]);

            // Sincronizar contactos (solo los válidos)
            $tarea->contactos()->sync($contactosValidos);

            $contactosCount = count($contactosValidos);
            $mensaje = $contactosCount === 1 
                ? '✏️ Tarea actualizada exitosamente para 1 contacto.' 
                : "✏️ Tarea actualizada exitosamente para {$contactosCount} contactos.";

            if ($nombreImagen) {
                $mensaje .= ' 📷 Con imagen adjunta.';
            }

            return redirect()->route('tareas.index')->with('success', $mensaje);

        } catch (\Exception $e) {
            \Log::error('Error al actualizar tarea:', [
                'tarea_id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors(['error' => 'Error al actualizar la tarea: ' . $e->getMessage()])
                        ->withInput();
        }
    }

    /**
     * Eliminar una tarea
     */
    public function destroy($id)
    {
        try {
            $tarea = Tarea::where('user_id', auth()->id())->findOrFail($id);
            
            // Eliminar imagen si existe
            if ($tarea->imagen_adjunta) {
                Storage::delete('public/tareas/' . $tarea->imagen_adjunta);
            }
            
            // Eliminar relaciones con contactos
            $tarea->contactos()->detach();
            
            // Eliminar la tarea
            $tarea->delete();

            return redirect()->route('tareas.index')
                           ->with('success', '🗑️ Tarea eliminada exitosamente.');

        } catch (\Exception $e) {
            \Log::error('Error al eliminar tarea:', [
                'tarea_id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('tareas.index')
                           ->with('error', '❌ Error al eliminar la tarea.');
        }
    }

    /**
     * Alternar el estado de completado de una tarea (AJAX)
     */
    public function toggle(Tarea $tarea)
    {
        // Verificar que la tarea pertenece al usuario autenticado
        if ($tarea->user_id !== auth()->id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        try {
            $tarea->completado = !$tarea->completado;
            $tarea->save();

            return response()->json([
                'success' => true,
                'completado' => $tarea->completado,
                'html' => $tarea->completado ? '✅ Completado' : '⏳ Pendiente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al cambiar el estado'
            ], 500);
        }
    }

    /**
     * Extraer título del mensaje personalizado (para compatibilidad)
     */
    private function extraerTitulo($mensaje)
    {
        $lineas = explode("\n", $mensaje);
        $primeraLinea = trim($lineas[0]);
        
        // Límite de 100 caracteres para el título
        return strlen($primeraLinea) > 100 
            ? substr($primeraLinea, 0, 97) . '...'
            : $primeraLinea;
    }
}
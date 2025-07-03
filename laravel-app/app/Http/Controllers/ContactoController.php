<?php

namespace App\Http\Controllers; // Controlador para gestionar contactos

use App\Models\Contacto;    // Modelo de Contacto
use Illuminate\Http\Request;    // Importa la clase Request para manejar solicitudes HTTP

class ContactoController extends Controller // Controlador para gestionar contactos
{
    // Mostrar lista de contactos
    public function index() // Método para mostrar la lista de contactos
    {
        // Solo mostrar contactos del usuario autenticado
        $contactos = Contacto::where('user_id', auth()->id())
                            ->orderBy('nombre')
                            ->get();
        return view('contactos.index', compact('contactos'));
    }

    // Mostrar formulario de creación
    public function create()    // Método para mostrar el formulario de creación de un nuevo contacto
    {
        return view('contactos.create');    // Retorna la vista de creación de contacto
    }

    // Guardar nuevo contacto
    public function store(Request $request) // Método para guardar un nuevo contacto
    {
        $request->validate([    // 
            'nombre' => 'required|string|max:255',  // Validación del nombre
            'numero' => 'required|string|max:20',   // Validación del número
            'descripcion' => 'nullable|string', // Validación de la descripción
        ]);

        // Limpiar número (solo dígitos)
        $numero = preg_replace('/[^\d]/', '', $request->numero);    // Elimina todo lo que no sea un dígito del número
        
        // Validar que sea un número válido
        if (strlen($numero) < 9) {  // Verifica que el número tenga al menos 9 dígitos
            return back()->withErrors(['numero' => 'El número debe tener al menos 9 dígitos']); // Retorna un error si no cumple la validación
        }

        Contacto::create([
            'user_id' => auth()->id() ?? null, // Temporal: permitir null si no hay usuario autenticado
            'nombre' => $request->nombre,
            'numero' => $numero,
            'descripcion' => $request->descripcion,
            'activo' => $request->has('activo'),
        ]);

        return redirect()->route('contactos.index')->with('success', '📞 Contacto creado exitosamente.');
    }

    // Mostrar formulario de edición
    public function edit(Contacto $contacto)    // Método para mostrar el formulario de edición de un contacto
    {
        // Verificar que el contacto pertenece al usuario autenticado
        if ($contacto->user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para editar este contacto.');
        }
        
        return view('contactos.edit', compact('contacto')); // Retorna la vista de edición con el contacto a editar
    }

    // Actualizar contacto
    public function update(Request $request, Contacto $contacto)    // Método para actualizar un contacto existente
    {
        // Verificar que el contacto pertenece al usuario autenticado
        if ($contacto->user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para actualizar este contacto.');
        }

        $request->validate([    // Validación de los datos del contacto
            'nombre' => 'required|string|max:255',  // Nombre del contacto
            'numero' => 'required|string|max:20',   // Número del contacto
            'descripcion' => 'nullable|string', // Descripción del contacto
        ]);

        // Limpiar número
        $numero = preg_replace('/[^\d]/', '', $request->numero);    // Elimina todo lo que no sea un dígito del número
        
        if (strlen($numero) < 9) {  // Verifica que el número tenga al menos 9 dígitos
            return back()->withErrors(['numero' => 'El número debe tener al menos 9 dígitos']); // Retorna un error si no cumple la validación
        }

        $contacto->update([ // Actualiza el contacto con los datos validados
            'nombre' => $request->nombre,   // Nombre del contacto
            'numero' => $numero,    // Número del contacto (limpio)
            'descripcion' => $request->descripcion, // Descripción del contacto
            'activo' => $request->has('activo'),    //  Verifica si el contacto está activo
        ]);

        return redirect()->route('contactos.index')->with('success', '✏️ Contacto actualizado exitosamente.');  // Redirige a la lista de contactos con un mensaje de éxito
    }

    // Eliminar contacto
    public function destroy(Contacto $contacto)
    {
        // Verificar que el contacto pertenece al usuario autenticado
        if ($contacto->user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para eliminar este contacto.');
        }

        // Verificar si tiene tareas asociadas
        if ($contacto->tareas()->count() > 0) { // Verifica si el contacto tiene tareas asociadas
            return redirect()->route('contactos.index') // Redirige a la lista de contactos
                ->with('error', '❌ No se puede eliminar el contacto porque tiene tareas asociadas.');  // Muestra un mensaje de error si no se puede eliminar
        }

        $contacto->delete();    // Elimina el contacto
        return redirect()->route('contactos.index')->with('success', '🗑️ Contacto eliminado exitosamente.');    // Redirige a la lista de constactos con un mensaje de éxito
    }

    // Alternar estado activo/inactivo
    public function toggle(Contacto $contacto)  // Método para alternar el estado activo/inactivo de un contacto
    {
        // Verificar que el contacto pertenece al usuario autenticado
        if ($contacto->user_id !== auth()->id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $contacto->activo = !$contacto->activo; // Cambia el estado activo del contacto al contrario del actual
        $contacto->save();  // Guarda el cambio en la base de datos

        return response()->json([   // Retorna una respuesta JSON con el estado actualizado del contacto
            'activo' => $contacto->activo,  // Estado activo del contacto
            'html' => $contacto->activo ? '✅ Activo' : '❌ Inactivo'   // Texto que indica si el contacto está activo o inactivo
        ]);
    }
}
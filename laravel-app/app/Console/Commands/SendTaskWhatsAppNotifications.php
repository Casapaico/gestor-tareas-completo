<?php
namespace App\Console\Commands; // Asegúrate de que la ruta sea correcta
use Illuminate\Console\Command; // Importa la clase Command
use Illuminate\Support\Facades\Http; // Importa la clase Http para hacer peticiones HTTP
use App\Models\Tarea;   // Asegúrate de que la ruta del modelo Tarea sea correcta
use Carbon\Carbon;  // Importa Carbon para manejar fechas y horas
use Illuminate\Support\Str; // Importa Str para manipular cadenas de texto

class SendTaskWhatsAppNotifications extends Command // Define tu comando de consola
{
    protected $signature = 'tareas:whatsapp-notify {--include-overdue : Incluir tareas vencidas}';  // Define la firma del comando, incluyendo una opción para incluir tareas vencidas
    protected $description = 'Envía notificaciones personalizadas de tareas a WhatsApp si la hora coincide con la actual';  // Descripción del comando

    public function handle()    // Método que se ejecuta al llamar al comando
    {
        $this->info('🚀 Iniciando proceso de notificaciones WhatsApp...'); // Mensaje de incio de proceso
        
        // Forzar zona horaria de Lima
        $now = Carbon::now('America/Lima')->format('Y-m-d H:i:s'); // Obtener la hora actual en formato 'Y-m-d H:i:s', usar la zona horaria de Lima
        $this->info("⏰ Hora actual (Lima): {$now}");   // Mostrar la hora actual en Lima
        
        $previousMinute = Carbon::now('America/Lima')->subMinute()->format('Y-m-d H:i:s');  // Obtener el minuto anterior en formato 'Y-m-d H:i:s'
        $this->info("⏰ Minuto anterior (Lima): {$previousMinute}");    // Mostrar el minuto anterior en Lima
        
        // Mostrar todas las tareas pendientes
        $allPendingTasks = Tarea::where('completado', false)->with('contactos')->get(); // Obtener todas las tareas pendientes sin filtrar por hora
        $this->info("📋 Total de tareas pendientes: " . $allPendingTasks->count()); // Mostrar el total de tareas pendientes
        
        foreach ($allPendingTasks as $task) {   // Iterar sobre todas las tareas pendientes
            $contactosInfo = $this->getContactosInfo($task);    // Obtener información de contactos para la tarea
            $tieneImagen = $task->tieneImagenAdjunta() ? ' 📷' : '';    // Verificar si la tarea tiene una imagen adjunta
            $this->info("  - ID: {$task->id} | Mensaje: " . Str::limit($task->mensaje_para_enviar, 50) . "{$tieneImagen} | Fecha: {$task->fecha_hora} | Contactos: {$contactosInfo}");  // Mostrar información de la tarea
        }
        
        // Buscar tareas usando hora de Lima
        if ($this->option('include-overdue')) { // Si se incluye la opción para tareas vencidas, buscar tareas que no se han completado en la última semana
            $weekAgo = Carbon::now('America/Lima')->subDays(7)->format('Y-m-d H:i:s');  // Obtener la fecha de hace una semana en formato 'Y-m-d H:i:s'
            $tareas = Tarea::where('completado', false) // Obtener tareas no completadas
                ->where('fecha_hora', '>=', $weekAgo)   // Filtrar tareas que tienen fecha y hora mayor o igual a hace una semana
                ->where('fecha_hora', '<=', $now)   // Asegurarse de que la fecha y hora sea menor o igual a la hora actual
                ->with('contactos') // Cargar relaciones de contactos
                ->get();    // Obtener las tareas
            $this->info("🔍 Buscando tareas vencidas desde: {$weekAgo}");   // Mostrar mensaje de búsqueda de tareas vencidas
        } else {    // Si no se incluye la opción para tareas vencidas, buscar solo las tareas que coinciden con la hora actual o el minuto anterior
            // Solo tareas para este momento exacto (hora de Lima)
            $tareas = Tarea::where('completado', false) // Obtener tareas no completadas
                ->where(function($query) use ($now, $previousMinute) {  // Filtrar tareas que tienen fecha y hora igual a la hora actual o al minuto anterior
                    $query->where('fecha_hora', $now)   // Filtrar tareas que tienen fecha y hora igual a la hora actual
                          ->orWhere('fecha_hora', $previousMinute); // O filtrar tareas que tienen fecha y hora igual al minuto anterior
                })
                ->with('contactos')// Cargar relaciones de contactos
                ->get(); // Obtener las tareas
        }

        $this->info("🎯 Tareas encontradas para notificar: " . $tareas->count());   // Mostrar el total de tareas para modificar

        if ($tareas->isEmpty()) {   // Si no hay tareas para notificar, mostrar mensaje y salir
            $this->info("ℹ️  No hay tareas para notificar en este momento"); // Mostrar mensaje de que no hay tareas para notificar
            return; // Salir del comando
        }

        // Verificar si el bot está corriendo
        try {
            $testResponse = Http::timeout(5)->get('http://localhost:3000/test');    // Hacer una petición de prueba al bot de WhatsApp
            if ($testResponse->successful()) {  // Si la respuesta es exitosa, mostrar mensaje de éxito
                $this->info("✅ Bot de WhatsApp está activo");  // Mostrar mensaje de que el bot está activo
                $features = $testResponse->json()['features'] ?? [];    // Obtener las funciones disponibles del bot
                $this->info("🔧 Funciones disponibles: " . implode(', ', $features));   // Mostrar las funciones disponibles del bot
            } else {    // Si la respuesta no es exitosa, mostrar mensaje de error
                $this->error("❌ Bot no responde correctamente");   // Mostrar mensaje de que el bot no responde correctamente
                return; // Salir del comando
            }
        } catch (\Exception $e) {   // Si ocurre una excepción al intentar conectarse al bot, mostrar mensaje de error
            $this->error("❌ No se puede conectar al bot: " . $e->getMessage());    // Mostrar mensaje de error con el mensaje de la excepción
            $this->error("💡 Asegúrate de que el bot esté corriendo: node bot.js"); // Sugerir que se incie el bot
            return; // Salir del comando
        }

        foreach ($tareas as $tarea) {   // Iterar sobre cada tarea encontrada
            $this->info("📤 Procesando tarea ID: {$tarea->id}");    // Mostrar mensaje de que se está procesando la tarea
            
            // Obtener contactos de la tarea
            $contactos = $this->getContactosParaTarea($tarea);  // Obtener los contactos asignados a la tarea
            
            if ($contactos->isEmpty()) {    // Si no hay contactos asignados a la tarea, mostrar mensaje y continuar con la siguiente tarea
                $this->warn("⚠️ Tarea {$tarea->id} no tiene contactos asignados, saltando..."); // Mostrar mensaje de advertencia de que la tarea no tiene contactos asignados
                continue;   // Continuar con la siguiente tarea
            }

            $this->info("📱 Enviando a {$contactos->count()} contacto(s):");    // Mostrar mensaje de que se enviará a los contactos asignados a la tarea

            // Preparar mensaje personalizado
            $mensaje = $tarea->mensaje_para_enviar; // Obtener el mensaje a enviar de la tarea
            
            // Verificar si tiene imagen adjunta
            $rutaImagen = $tarea->ruta_imagen_completa; // Obtener la ruta completa de la imagen adjunta
            $tieneImagen = $tarea->tieneImagenAdjunta();    // Verificar si la tarea tiene una imagen adjunta
            
            if ($tieneImagen) { // Si la tarea tiene una imagen adjunta, mostrar mensaje de que se enviará una imagen
                $this->info("🖼️ Tarea incluye imagen: " . basename($tarea->imagen_adjunta));    // Mostrar el nombre de la imagen adjunta
            }

            $enviadoExitosamente = true;    // Variable para controlar si se envió exitosamente
            $enviadosCount = 0; // Contador de mensajes enviados exitosamente

            foreach ($contactos as $contacto) { // Iterar sobre cada contacto asignado a la tarea
                try {   // Intentar enviar el mensaje al contacto
                    $this->info("  📞 Enviando a: {$contacto->nombre} (+{$contacto->numero_formateado})");  // Mostrar mensaje de que se está enviando al contacto
                    
                    // Elegir endpoint según si tiene imagen o no
                    if ($tieneImagen) { // Si la tarea tiene una imagen adjunta, enviar imagen con mensaje
                        // Enviar imagen con mensaje
                        $response = Http::timeout(30)->post('http://localhost:3000/send-notification', [    // Hacer una petición POST al endpoint del bot de WhatsApp
                            'number' => $contacto->numero_formateado,   // Número de teléfono del contacto formateado
                            'message' => $mensaje,  // Mensaje a enviar
                            'imagePath' => $rutaImagen, // Ruta completa de la imagen adjunta
                        ]);
                    } else {    // Si la tarea no tiene una imagen adjunta, enviar solo el mensaje de texto
                        // Enviar solo mensaje de texto
                        $response = Http::timeout(30)->post('http://localhost:3000/send-notification', [    // Hacer una petición POST al endpoint del bot de WhatsApp
                            'number' => $contacto->numero_formateado,   // Número de teléfono del contacto formateado
                            'message' => $mensaje,  // Mensaje a enviar
                        ]);
                    }

                    if ($response->successful()) {  // Si la respuesta es exitosa, mostrar mensaje de éxito
                        $responseData = $response->json();  // Obtener los datos de la respuesta en formato JSON
                        $tipo = $responseData['type'] ?? 'unknown'; // Obtener el tipo de mensaje enviado (puede ser 'text', 'image', etc.)
                        $this->info("    ✅ Enviado exitosamente a {$contacto->nombre} (tipo: {$tipo})");   // Mostrar mensaje de que se envió exitosamente al contacto y el tipo de mensaje enviado
                        $enviadosCount++;   // Incrementar el contador de mensajes enviados exitosamente
                        
                        // Marcar como enviado en la tabla pivot (si es múltiple)
                        if ($tarea->contactos->contains($contacto->id)) {   // Si la tarea tiene contactos múltiples, marcar como enviado en la tabla pivot
                            $tarea->marcarEnviadoAContacto($contacto->id);  // Marcar como enviado al contacto en la tabla pivot
                        }
                    } else {    // Si la respuesta no es exitosa, mostrar mensaje de error
                        $this->error("    ❌ Error HTTP {$response->status()} para {$contacto->nombre}");   // Mostrar mensaje de error HTTP con el código de estado
                        $this->error("    📄 Respuesta: " . $response->body()); // Mostrar el cuerpo de la respuesta del error
                        $enviadoExitosamente = false;   // Marcar como no enviado exitosamente
                    }
                } catch (\Exception $e) {   // Si ocurre una excepción al intentar enviar el mensaje, mostrar mensaje de error
                    $this->error("    ❌ Excepción enviando a {$contacto->nombre}: " . $e->getMessage());   // Mostrar mensaje de error con el mensaje de la excepción
                    $enviadoExitosamente = false;   // Marcar como no enviado exitosamente
                }
            }

            // Marcar tarea como completada solo si se envió a todos los contactos exitosamente
            if ($enviadoExitosamente && $enviadosCount > 0) {   // Si se envió exitosamente a al menos un contacto, marcar la tarea como completada
                $tarea->completado = true;  // Marcar la tarea como completada
                $tarea->save(); // Guardar los cambios en la tarea
                $tipoMensaje = $tieneImagen ? 'mensaje con imagen' : 'mensaje'; // Determinar el tipo de mensaje enviado (con imagen o solo texto)
                $this->info("✅ Tarea marcada como completada ({$tipoMensaje} enviado a {$enviadosCount} contactos)");  // Mostrar mensaje de que la tarea se marcó como completada y el tipo de mensaje enviado
            } else {    // Si no se envió exitosamente a todos los contactos, mostrar mensaje de advertencia
                $this->warn("⚠️ Tarea no marcada como completada debido a errores en el envío");    // Mostrar mensaje de advertencia de que la tarea no se marcó como completada debido a errores en el envío
            }

            $this->info("---"); // Separador para mejorar la legibilidad en la salida del comando
        }

        $this->info('🏁 Proceso completado');   // Mensaje final indicando que el proceso a sido completado
    }

    private function getContactosParaTarea($tarea)  // Método para obtener los contactos asignados a una tarea
    {
        // Priorizar contactos múltiples (nuevo sistema)
        $contactosMultiples = $tarea->contactosActivos; // Obtener los contactos activos asignados a la tarea
        
        if ($contactosMultiples->isNotEmpty()) {    // Si hay contactos múltiples activos, devolverlos
            return $contactosMultiples; // Devolver los contactos múltiples activos
        }
        
        // Fallback al contacto original (sistema anterior)
        if ($tarea->contacto && $tarea->contacto->activo) { // Si no hay contactos múltiples, verificar si hay un contacto original activo
            return collect([$tarea->contacto]); // Devolver el contacto original como una colección
        }
        
        return collect();   // Si no hay contactos múltiples ni contacto original activo, devolver una colección vacía
    }

    private function getContactosInfo($tarea)   // Método para obtener información de los contactos asignados a una tarea
    {
        $contactos = $this->getContactosParaTarea($tarea);  // Obtener los contactos asignados a la tarea
        
        if ($contactos->isEmpty()) {    // Si no hay contactos asignados, devolver un mensaje indicando que no hay contactos
            return 'Sin contactos'; // Devolver mensaje indicando que no hay contactos asignados
        }
        
        $nombres = $contactos->pluck('nombre')->take(3)->toArray(); // Obtener los nombres de los primeros 3 contactos asignados a la tarea
        $count = $contactos->count();   // Contar el total de contactos asignados a la tarea
        
        if ($count > 3) {   // Si hay más de 3 contactos asignados, agregar un mensaje indicando cuántos más hay
            $nombres[] = "y " . ($count - 3) . " más";  // Agregar mensaje indicando cuántos más contactos hay
        }
        
        return implode(', ', $nombres); // Devolver los nombres de los contactos como una cadena separada por comas
    }
}
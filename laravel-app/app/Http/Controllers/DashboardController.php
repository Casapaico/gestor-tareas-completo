<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Tarea;
use App\Models\Contacto;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Estadísticas del usuario autenticado
        $stats = [
            'tareas_total' => Tarea::where('user_id', $user->id)->count(),
            'tareas_pendientes' => Tarea::where('user_id', $user->id)
                                       ->where('completado', false)
                                       ->where('fecha_hora', '>', now())
                                       ->count(),
            'tareas_hoy' => Tarea::where('user_id', $user->id)
                                ->whereDate('fecha_hora', today())
                                ->count(),
            'contactos_activos' => Contacto::where('user_id', $user->id)
                                          ->where('activo', true)
                                          ->count(),
        ];

        // Verificar estado del bot de WhatsApp
        $botStatus = $this->checkBotStatus();

        // Tareas próximas del usuario (siguiente 24 horas)
        $tareasProximas = Tarea::with(['contacto', 'contactos'])
            ->where('user_id', $user->id)
            ->where('completado', false)
            ->where('fecha_hora', '>=', now())
            ->where('fecha_hora', '<=', now()->addDay())
            ->orderBy('fecha_hora')
            ->limit(5)
            ->get();

        return view('dashboard', compact('user', 'stats', 'botStatus', 'tareasProximas'));
    }

    private function checkBotStatus()
    {
        try {
            // Verificar si el bot está funcionando
            $response = Http::timeout(5)->get('http://localhost:3000/test');
            
            if ($response->successful()) {
                return [
                    'connected' => true,
                    'status' => 'Conectado',
                    'message' => 'Bot de WhatsApp funcionando correctamente'
                ];
            }
            
            return [
                'connected' => false,
                'status' => 'Desconectado',
                'message' => 'Bot de WhatsApp no está funcionando'
            ];
            
        } catch (\Exception $e) {
            return [
                'connected' => false,
                'status' => 'Error',
                'message' => 'No se puede conectar con el bot. Asegúrate de que esté ejecutándose.'
            ];
        }
    }

    public function getBotQR()
    {
        try {
            // Endpoint para obtener QR del bot
            $response = Http::timeout(10)->get('http://localhost:3000/qr');
            
            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'qr' => $response->json('qr'),
                    'status' => $response->json('status')
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'No se pudo obtener el código QR'
            ], 500);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error conectando con el bot: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkBotStatusAjax()
    {
        $status = $this->checkBotStatus();
        return response()->json($status);
    }
}
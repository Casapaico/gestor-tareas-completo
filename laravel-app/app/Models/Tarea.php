<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarea extends Model
{
    use HasFactory;

    protected $table = 'tareas';

    protected $fillable = [
        'user_id',      // AÑADIDO: Campo para relación con usuario
        'titulo',
        'descripcion', 
        'mensaje_personalizado',
        'imagen_adjunta',
        'fecha_hora',
        'completado',
        'contacto_id' // Mantener por compatibilidad
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'completado' => 'boolean'
    ];

    // Relación con User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación original (un contacto) - MANTENER PARA COMPATIBILIDAD
    public function contacto()
    {
        return $this->belongsTo(Contacto::class);
    }

    // Nueva relación (múltiples contactos)
    public function contactos()
    {
        return $this->belongsToMany(Contacto::class, 'tarea_contacto')
                    ->withPivot('enviado', 'enviado_at')
                    ->withTimestamps();
    }

    // Scope para tareas del usuario autenticado
    public function scopeDelUsuario($query, $userId = null)
    {
        $userId = $userId ?? auth()->id();
        return $query->where('user_id', $userId);
    }

    // Obtener contactos activos de la tarea
    public function contactosActivos()
    {
        return $this->contactos()->where('contactos.activo', true);
    }

    // Obtener números de WhatsApp de todos los contactos
    public function getNumerosWhatsappAttribute()
    {
        $contactos = $this->contactos->where('activo', true);
        
        if ($contactos->isEmpty() && $this->contacto) {
            return [$this->contacto->numero_formateado];
        }
        
        return $contactos->pluck('numero_formateado')->toArray();
    }

    // Verificar si ya se envió a todos los contactos
    public function getEnviadoATodesAttribute()
    {
        $totalContactos = $this->contactos()->where('contactos.activo', true)->count();
        $enviadosCount = $this->contactos()->wherePivot('enviado', true)->count();
        
        return $totalContactos > 0 && $totalContactos === $enviadosCount;
    }

    // Marcar como enviado a un contacto específico
    public function marcarEnviadoAContacto($contactoId)
    {
        $this->contactos()->updateExistingPivot($contactoId, [
            'enviado' => true,
            'enviado_at' => now()
        ]);
    }

    // Método de compatibilidad para obtener el primer número
    public function getNumeroWhatsappAttribute()
    {
        $numeros = $this->numeros_whatsapp;
        
        if (!empty($numeros)) {
            return $numeros[0];
        }
        
        return '51937594193';
    }

    // Obtener el mensaje a enviar (personalizado o generado)
    public function getMensajeParaEnviarAttribute()
    {
        // Si tiene mensaje personalizado, usarlo
        if (!empty($this->mensaje_personalizado)) {
            return $this->mensaje_personalizado;
        }
        
        // Fallback al mensaje generado (compatibilidad)
        return $this->titulo . "\n\n" . $this->descripcion;
    }

    // Verificar si tiene imagen adjunta
    public function tieneImagenAdjunta()
    {
        return !empty($this->imagen_adjunta) && file_exists(public_path('storage/tareas/' . $this->imagen_adjunta));
    }

    // Obtener URL completa de la imagen
    public function getUrlImagenAttribute()
    {
        if ($this->tieneImagenAdjunta()) {
            return asset('storage/tareas/' . $this->imagen_adjunta);
        }
        return null;
    }

    // Obtener ruta completa de la imagen para el bot
    public function getRutaImagenCompletaAttribute()
    {
        if ($this->tieneImagenAdjunta()) {
            return public_path('storage/tareas/' . $this->imagen_adjunta);
        }
        return null;
    }
}
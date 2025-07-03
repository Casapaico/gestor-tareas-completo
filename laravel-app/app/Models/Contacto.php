<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contacto extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',      // AÑADIDO: Campo para relación con usuario
        'nombre',
        'numero', 
        'descripcion',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    // Relación con User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación original (una tarea) - DEPRECATED
    public function tareas()
    {
        return $this->hasMany(Tarea::class);
    }

    // Nueva relación (múltiples tareas)
    public function tareasMultiples()
    {
        return $this->belongsToMany(Tarea::class, 'tarea_contacto')
                    ->withPivot('enviado', 'enviado_at')
                    ->withTimestamps();
    }

    // Scope para contactos activos
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    // Scope para contactos del usuario autenticado
    public function scopeDelUsuario($query, $userId = null)
    {
        $userId = $userId ?? auth()->id();
        return $query->where('user_id', $userId);
    }

    // Formatear número para WhatsApp
    public function getNumeroFormateadoAttribute()
    {
        // Eliminar espacios, guiones y símbolos
        $numero = preg_replace('/[^\d]/', '', $this->numero);
        
        // Si no empieza con código de país, agregar Perú por defecto
        if (strlen($numero) === 9) {
            $numero = '51' . $numero;
        }
        
        return $numero;
    }

    // Contar total de tareas (original + múltiples)
    public function getTotalTareasAttribute()
    {
        return $this->tareas()->count() + $this->tareasMultiples()->count();
    }
}
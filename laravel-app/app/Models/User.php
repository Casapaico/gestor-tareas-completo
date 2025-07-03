<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_agent', // Campo para controlar dispositivos
        'is_admin', // Campo para permisos de administrador
        'active', // Campo para activar/desactivar usuarios
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'user_agent', // Ocultar user_agent en serialización
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
        'active' => 'boolean',
    ];

    /**
     * Método para cerrar sesión en otros dispositivos
     */
    public function logoutOtherDevices()
    {
        $this->update(['user_agent' => null]);
    }

    /**
     * Método para permitir acceso desde un nuevo dispositivo
     */
    public function allowNewDevice($userAgent)
    {
        $this->update(['user_agent' => $userAgent]);
    }

    /**
     * Verificar si el dispositivo actual está autorizado
     */
    public function isDeviceAuthorized($userAgent)
    {
        return empty($this->user_agent) || $this->user_agent === $userAgent;
    }

    // Agregar relaciones
    public function tareas()
    {
        return $this->hasMany(Tarea::class);
    }

    public function contactos()
    {
        return $this->hasMany(Contacto::class);
    }
}
# 🔧 Solución de Problemas

Guía para resolver los errores más comunes del Sistema de Gestión de Tareas.

## 🚨 Problemas Frecuentes

### 1. El Sistema No Inicia

#### Error: "No se encontró laravel-app"
```bash
❌ Error: No se encontró laravel-app en /home/alex/gestor-tareas-completo/laravel-app
```

**Solución:**
```bash
# Verificar la estructura de carpetas
ls -la ~/gestor-tareas-completo/

# Si las carpetas están mal ubicadas, moverlas:
mv ~/gestorHorarios ~/gestor-tareas-completo/laravel-app
mv ~/venom-bot-server ~/gestor-tareas-completo/whatsapp-bot
```

#### Error: "Node.js no está instalado"
**Solución:**
```bash
# Instalar Node.js en Ubuntu
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Verificar instalación
node --version
npm --version
```

#### Error: "PHP no está instalado"
**Solución:**
```bash
# Instalar PHP en Ubuntu
sudo apt update
sudo apt install php php-cli php-mbstring php-xml php-sqlite3

# Verificar instalación
php --version
```

### 2. Bot de WhatsApp No Funciona

#### Error: "Bot no responde en puerto 3000"
**Diagnóstico:**
```bash
# Ver logs específicos del bot
npm run logs:bot

# Verificar si el puerto está ocupado
lsof -i :3000

# Probar conexión manualmente
curl http://localhost:3000/test
```

**Soluciones:**
```bash
# Si el puerto está ocupado por otro proceso
sudo lsof -ti :3000 | xargs kill -9

# Reiniciar solo el bot
cd ~/gestor-tareas-completo/whatsapp-bot
node bot.js
```

#### Error: "QR Code requerido"
**Solución:**
1. El bot mostrará un QR en la terminal
2. Escanéalo con tu WhatsApp desde **Dispositivos Vinculados**
3. Espera a que diga "✅ Cliente conectado"

#### Error: "Cannot find module 'venom-bot'"
**Solución:**
```bash
cd ~/gestor-tareas-completo/whatsapp-bot
npm install venom-bot express body-parser
```

### 3. Laravel No Funciona

#### Error: "php artisan serve falló"
**Diagnóstico:**
```bash
# Ver logs del servidor
npm run logs:server

# Probar manualmente
cd ~/gestor-tareas-completo/laravel-app
php artisan serve
```

**Soluciones:**
```bash
# Si falta la key de Laravel
cd ~/gestor-tareas-completo/laravel-app
php artisan key:generate

# Si hay problemas de permisos
sudo chown -R $USER:$USER storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Si falta composer
cd ~/gestor-tareas-completo/laravel-app
composer install
```

#### Error: "Database not found"
**Solución:**
```bash
cd ~/gestor-tareas-completo/laravel-app

# Crear la base de datos
touch database/database.sqlite

# Ejecutar migraciones
php artisan migrate
```

### 4. Scheduler No Ejecuta Tareas

#### Error: "schedule:work no funciona"
**Diagnóstico:**
```bash
# Ver logs del scheduler
npm run logs:scheduler

# Probar comando manual
cd ~/gestor-tareas-completo/laravel-app
php artisan tareas:whatsapp-notify
```

**Soluciones:**
```bash
# Verificar zona horaria
php artisan tinker
>>> Carbon\Carbon::now()

# Si la zona horaria está mal, editar config/app.php:
'timezone' => 'America/Lima'

# Reiniciar scheduler
npm stop
npm start
```

### 5. No Llegan Notificaciones WhatsApp

#### Problema: Tareas no se detectan
**Diagnóstico:**
```bash
# Ejecutar comando manual con debug
cd ~/gestor-tareas-completo/laravel-app
php artisan tareas:whatsapp-notify

# Verificar tareas en la BD
php artisan tinker
>>> App\Models\Tarea::where('completado', false)->get()
```

**Soluciones:**

1. **Hora no coincide exactamente:**
```php
// Crear tarea para testing
use App\Models\Tarea;
use Carbon\Carbon;

Tarea::create([
    'titulo' => 'Prueba Inmediata',
    'descripcion' => 'Test del sistema',
    'fecha_hora' => Carbon::now()->addMinute(),
    'completado' => false
]);
```

2. **Número de WhatsApp incorrecto:**
```bash
# Editar el comando
nano ~/gestor-tareas-completo/laravel-app/app/Console/Commands/SendTaskWhatsAppNotifications.php

# Cambiar línea:
$number = '51937594193'; // Tu número correcto
```

## 🆘 Comandos de Emergencia

### Detener Todo Forzadamente
```bash
# Matar todos los procesos relacionados
pkill -f "node.*bot"
pkill -f "php.*artisan"
sudo lsof -ti :3000,:8000 | xargs kill -9
```

### Resetear Completamente
```bash
cd ~/gestor-tareas-completo

# Detener servicios
npm stop

# Limpiar logs
npm run clean-logs

# Reiniciar
npm start
```

### Verificar Estado del Sistema
```bash
# Ver todos los procesos activos
npm run status

# Probar conectividad
npm run test

# Ver logs en tiempo real
npm run logs
```

## 📞 Diagnóstico Completo

Si sigues teniendo problemas, ejecuta este script de diagnóstico:

```bash
#!/bin/bash
echo "=== DIAGNÓSTICO DEL SISTEMA ==="
echo "Fecha/Hora: $(date)"
echo ""

echo "1. Estructura de carpetas:"
ls -la ~/gestor-tareas-completo/
echo ""

echo "2. Procesos activos:"
ps aux | grep -E "(node.*bot|php.*artisan)" | grep -v grep
echo ""

echo "3. Puertos en uso:"
lsof -i :3000,:8000
echo ""

echo "4. Versiones:"
echo "Node: $(node --version 2>/dev/null || echo 'No instalado')"
echo "PHP: $(php --version 2>/dev/null | head -1 || echo 'No instalado')"
echo ""

echo "5. Últimos logs:"
echo "--- Bot WhatsApp ---"
tail -n 5 ~/gestor-tareas-completo/logs/whatsapp-bot.log 2>/dev/null || echo "Sin logs"
echo "--- Scheduler ---"  
tail -n 5 ~/gestor-tareas-completo/logs/scheduler.log 2>/dev/null || echo "Sin logs"
```

## 💡 Consejos de Prevención

1. **Siempre usa `npm start` y `npm stop`** para iniciar/detener
2. **Revisa los logs** regularmente con `npm run logs`
3. **Mantén respaldos** de tu base de datos
4. **Actualiza dependencias** periódicamente
5. **Configura la zona horaria** correctamente desde el inicio
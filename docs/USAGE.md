# 🎮 Guía de Uso Diario

Comandos y operaciones para el uso cotidiano del Sistema de Gestión de Tareas.

## 🚀 Comandos Principales

### Iniciar el Sistema

```bash
# Entrar al directorio principal
cd ~/gestor-tareas-completo

# Iniciar todo el sistema
npm start
# o
bash scripts/start.sh
```

### Detener el Sistema

```bash
# Detener todos los servicios
npm stop
# o  
bash scripts/stop.sh
```

### Reiniciar el Sistema

```bash
# Reiniciar completamente
npm restart
```

## 📊 Monitoreo y Logs

### Ver Estado de los Servicios

```bash
# Verificar qué servicios están corriendo
npm run status
```

### Ver Logs en Tiempo Real

```bash
# Ver todos los logs
npm run logs

# Ver solo logs del bot de WhatsApp
npm run logs:bot

# Ver solo logs del scheduler
npm run logs:scheduler

# Ver solo logs del servidor Laravel
npm run logs:server
```

### Limpiar Logs

```bash
# Eliminar todos los archivos de log
npm run clean-logs
```

## 🌐 Acceso a la Aplicación

Una vez iniciado el sistema, puedes acceder a:

- **🌐 Aplicación Web:** http://localhost:8000
- **📱 API del Bot:** http://localhost:3000/test

## 📝 Uso de la Aplicación Web

### Crear una Nueva Tarea

1. Ve a http://localhost:8000
2. Clic en "➕ Crear nueva tarea"
3. Completa los campos:
   - **Título:** Nombre de la tarea
   - **Descripción:** Detalles de la tarea
   - **Fecha y Hora:** Cuándo quieres recibir la notificación
4. Clic en "💾 Guardar tarea"

### Gestionar Tareas Existentes

- **✏️ Editar:** Modificar título, descripción o fecha
- **✅ Completar:** Marcar como realizada (click en el estado)
- **🗑️ Eliminar:** Borrar definitivamente la tarea

## 📱 Notificaciones WhatsApp

### Cómo Funcionan

1. El **scheduler** revisa las tareas cada minuto
2. Cuando la fecha/hora coincide con la actual, envía una notificación
3. El mensaje llega a tu WhatsApp automáticamente
4. La tarea se marca como completada

### Formato del Mensaje

```
🔔 Recordatorio de Tarea

📋 Título: [Tu título]
📝 Descripción: [Tu descripción]  
⏰ Programada para: dd/mm/yyyy HH:mm
🤖 Enviado por tu Gestor de Tareas
```

## 🔧 Comandos de Prueba

### Verificar Conectividad

```bash
# Probar que ambos servicios responden
npm run test
```

### Crear Tarea de Prueba

```bash
cd ~/gestor-tareas-completo/laravel-app
php artisan tinker
```

```php
use App\Models\Tarea;
use Carbon\Carbon;

// Crear tarea para dentro de 2 minutos
Tarea::create([
    'titulo' => 'Prueba del Sistema',
    'descripcion' => 'Esta es una prueba automática',
    'fecha_hora' => Carbon::now()->addMinutes(2),
    'completado' => false
]);
```

### Enviar Notificación Manual

```bash
cd ~/gestor-tareas-completo/laravel-app
php artisan tareas:whatsapp-notify
```

## ⚠️ Consideraciones Importantes

### Zona Horaria
- El sistema está configurado para **America/Lima**
- Las notificaciones se envían según la hora local de Perú

### Número de WhatsApp
- Por defecto configurado para: **+51 937594193**
- Para cambiar el número, edita: `laravel-app/app/Console/Commands/SendTaskWhatsAppNotifications.php`

### Precisión de Horarios
- Las notificaciones se envían cuando la hora coincide **exactamente**
- El sistema revisa cada minuto automáticamente

## 🚨 Qué Hacer Si...

### No Llegan las Notificaciones
1. Verificar que el bot esté conectado: `npm run logs:bot`
2. Revisar que el scheduler funcione: `npm run logs:scheduler`
3. Confirmar la hora de la tarea: debe coincidir exactamente

### El Sistema No Inicia
1. Verificar que ambas carpetas existan: `laravel-app/` y `whatsapp-bot/`
2. Revisar dependencias: Node.js y PHP instalados
3. Ver logs de error: `npm run logs`

### Problemas con WhatsApp
1. El bot puede pedir re-escanear el QR
2. Verificar conexión a internet
3. Revisar logs específicos: `npm run logs:bot`

## 🤖 Cambio del número del bot
1. Detener todo
```bash
cd ~/gestor-tareas-completo
npm stop
```
2. Eliminar sesión actual
```bash
cd whatsapp-bot
rm -rf tokens
```
3. Iniciar bot para ver QR
```bash
node bot.js
```
4. Iniciar la aplicación desde la raiz
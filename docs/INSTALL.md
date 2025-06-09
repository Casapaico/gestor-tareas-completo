# 📥 Guía de Instalación

Configuración inicial completa del Sistema de Gestión de Tareas.

## 🔧 Requisitos del Sistema

### Software Necesario
- **Ubuntu 20.04+** (o distribución compatible)
- **PHP 8.1+** con extensiones: mbstring, xml, sqlite3
- **Node.js 16+** y npm
- **Composer** (para Laravel)
- **Git** (opcional, para versioning)

### Hardware Mínimo
- **RAM:** 2GB
- **Disco:** 1GB libre
- **Red:** Conexión a internet para WhatsApp Web

## 🚀 Instalación Paso a Paso

### 1. Instalar Dependencias del Sistema

```bash
# Actualizar paquetes
sudo apt update && sudo apt upgrade -y

# Instalar PHP y extensiones
sudo apt install -y php php-cli php-mbstring php-xml php-sqlite3 php-curl php-zip

# Instalar Node.js (versión 18 LTS)
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Verificar instalaciones
php --version
node --version
npm --version
composer --version
```

### 2. Crear Estructura del Proyecto

```bash
# Crear directorio principal
mkdir ~/gestor-tareas-completo
cd ~/gestor-tareas-completo

# Crear subdirectorios
mkdir scripts logs docs

# Mover proyectos existentes (si ya los tienes)
# Si tienes gestorHorarios y venom-bot-server:
mv ~/gestorHorarios ./laravel-app
mv ~/venom-bot-server ./whatsapp-bot
```

### 3. Configurar Laravel App

```bash
cd ~/gestor-tareas-completo/laravel-app

# Instalar dependencias de Composer
composer install

# Crear archivo de configuración
cp .env.example .env

# Generar key de aplicación
php artisan key:generate

# Configurar base de datos (SQLite)
touch database/database.sqlite

# Configurar .env para SQLite
sed -i 's/DB_CONNECTION=mysql/DB_CONNECTION=sqlite/' .env
sed -i 's/DB_DATABASE=laravel/DB_DATABASE=database\/database.sqlite/' .env

# Ejecutar migraciones
php artisan migrate

# Configurar zona horaria
sed -i "s/'timezone' => 'UTC'/'timezone' => 'America\/Lima'/" config/app.php

# Dar permisos
chmod -R 775 storage bootstrap/cache
```

### 4. Configurar WhatsApp Bot

```bash
cd ~/gestor-tareas-completo/whatsapp-bot

# Instalar dependencias de Node.js
npm install venom-bot express body-parser

# Verificar que bot.js existe
ls -la bot.js
```

### 5. Crear Scripts de Gestión

Crea los siguientes archivos en `scripts/`:

#### scripts/start.sh
```bash
# Copia el contenido del artifact "scripts/start.sh"
nano ~/gestor-tareas-completo/scripts/start.sh
# [Pegar contenido del script de inicio]
```

#### scripts/stop.sh
```bash
# Copia el contenido del artifact "scripts/stop.sh"  
nano ~/gestor-tareas-completo/scripts/stop.sh
# [Pegar contenido del script de parada]
```

### 6. Configurar package.json

```bash
# Crear package.json en la raíz
nano ~/gestor-tareas-completo/package.json
# [Pegar contenido del artifact "package.json"]
```

### 7. Dar Permisos a Scripts

```bash
cd ~/gestor-tareas-completo
chmod +x scripts/*.sh
```

### 8. Configurar el Número de WhatsApp

```bash
# Editar el comando de notificaciones
nano laravel-app/app/Console/Commands/SendTaskWhatsAppNotifications.php

# Buscar y cambiar esta línea:
$number = '51937594193'; // Cambiar por tu número

# Formato: código de país + número (sin espacios ni símbolos)
# Ejemplo: '51987654321' para Perú
```

## ✅ Verificación de la Instalación

### 1. Probar Estructura
```bash
cd ~/gestor-tareas-completo
tree . -L 2
```

**Estructura esperada:**
```
gestor-tareas-completo/
├── laravel-app/
├── whatsapp-bot/
├── scripts/
├── logs/
├── docs/
└── package.json
```

### 2. Probar Laravel
```bash
cd ~/gestor-tareas-completo/laravel-app
php artisan --version
php artisan migrate:status
```

### 3. Probar Node.js
```bash
cd ~/gestor-tareas-completo/whatsapp-bot
node --version
ls -la node_modules/venom-bot
```

### 4. Primera Ejecución
```bash
cd ~/gestor-tareas-completo
npm start
```

## 📱 Configuración Inicial de WhatsApp

### Primera Conexión
1. Ejecuta `npm start`
2. El bot mostrará un **QR Code** en la terminal
3. Abre WhatsApp en tu teléfono
4. Ve a **Configuración > Dispositivos Vinculados**
5. Escanea el QR Code
6. Espera el mensaje "✅ Cliente conectado"

### Verificar Conexión
```bash
# En otra terminal
curl http://localhost:3000/test
```

**Respuesta esperada:**
```json
{"status":"Bot funcionando correctamente","timestamp":"..."}
```

## 🎯 Configuración del Crontab (Opcional)

Para que el sistema funcione automáticamente al reiniciar:

```bash
# Editar crontab
crontab -e

# Agregar esta línea:
@reboot cd /home/alex/gestor-tareas-completo && npm start

# O para scheduler manual:
* * * * * cd /home/alex/gestor-tareas-completo/laravel-app && php artisan schedule:run >> /dev/null 2>&1
```

## 🧪 Prueba Completa del Sistema

### 1. Crear Tarea de Prueba
```bash
cd ~/gestor-tareas-completo/laravel-app
php artisan tinker
```

```php
use App\Models\Tarea;
use Carbon\Carbon;

Tarea::create([
    'titulo' => 'Prueba de Instalación',
    'descripcion' => 'Si recibes este mensaje, ¡la instalación fue exitosa!',
    'fecha_hora' => Carbon::now()->addMinutes(2),
    'completado' => false
]);

echo "Tarea creada para: " . Carbon::now()->addMinutes(2) . "\n";
exit
```

### 2. Verificar Recepción
- Espera 2 minutos
- Deberías recibir la notificación en WhatsApp
- Verifica en http://localhost:8000 que la tarea se marcó como completada

## 🎉 ¡Instalación Completada!

Si llegaste hasta aquí y recibiste la notificación de prueba, ¡felicidades! Tu sistema está completamente instalado y funcionando.

### Próximos Pasos
1. Lee la **[Guía de Uso Diario](USAGE.md)**
2. Familiarízate con los **[Comandos Principales](USAGE.md#🚀-comandos-principales)**
3. Guarda esta documentación para referencia futura

### Soporte
Si encuentras problemas, consulta **[Solución de Problemas](TROUBLESHOOTING.md)**.
# 📋 Sistema de Gestión de Tareas con WhatsApp

Sistema completo de gestión de tareas desarrollado en Laravel con notificaciones automáticas a WhatsApp.

## 🎯 Características

- ✅ **CRUD completo** de tareas (crear, editar, eliminar, completar)
- 📱 **Notificaciones automáticas** a WhatsApp cuando llega la hora programada
- ⏰ **Scheduler automático** que revisa tareas cada minuto
- 🤖 **Bot de WhatsApp** integrado con Venom-bot
- 📊 **Sistema de logs** centralizado
- 🚀 **Scripts de inicio/parada** unificados

## 🏗️ Arquitectura

```
gestor-tareas-completo/
├── laravel-app/        # Aplicación Laravel (gestión de tareas)
├── whatsapp-bot/       # Bot de WhatsApp (venom-bot)
├── scripts/            # Scripts de gestión del sistema
├── logs/               # Logs centralizados
└── docs/               # Documentación
```

## ⚡ Inicio Rápido

```bash
# Entrar al directorio principal
cd ~/gestor-tareas-completo

# Iniciar todo el sistema
npm start

# Abrir la aplicación web
# http://localhost:8000
```

## 📖 Documentación Completa

- 📥 **[Instalación](INSTALL.md)** - Configuración inicial del sistema
- 🎮 **[Uso Diario](USAGE.md)** - Comandos y operaciones cotidianas  
- 🔧 **[Solución de Problemas](TROUBLESHOOTING.md)** - Errores comunes y soluciones
- 🏛️ **[Arquitectura](ARCHITECTURE.md)** - Detalles técnicos del sistema

## 🛠️ Tecnologías

- **Backend:** Laravel 10 + PHP 8.1
- **Bot:** Node.js + Venom-bot
- **Base de datos:** SQLite/MySQL
- **Frontend:** Bootstrap 5 + Blade templates
- **Notificaciones:** WhatsApp Web API

## 👨‍💻 Autor

**Alex** - Desarrollador del sistema

## 📄 Licencia

MIT License - Puedes usar este proyecto libremente.
#!/bin/bash

# Sistema de Gestión de Tareas - Script de Inicio
# Autor: Alex
# Descripción: Inicia todos los servicios necesarios

# Colores para output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
PURPLE='\033[0;35m'
NC='\033[0m'

# Directorio base (desde donde se ejecuta el script)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BASE_DIR="$(dirname "$SCRIPT_DIR")"
LARAVEL_DIR="$BASE_DIR/laravel-app"
BOT_DIR="$BASE_DIR/whatsapp-bot"
LOGS_DIR="$BASE_DIR/logs"

# Banner de inicio
echo -e "${PURPLE}"
echo "╔══════════════════════════════════════════════════╗"
echo "║           GESTOR DE TAREAS - INICIO              ║"
echo "║              WhatsApp + Laravel                  ║"
echo "╚══════════════════════════════════════════════════╝"
echo -e "${NC}"

# Verificar estructura de carpetas
echo -e "${BLUE}🔍 Verificando estructura del proyecto...${NC}"

if [ ! -d "$LARAVEL_DIR" ]; then
    echo -e "${RED}❌ Error: No se encontró laravel-app en $LARAVEL_DIR${NC}"
    exit 1
fi

if [ ! -d "$BOT_DIR" ]; then
    echo -e "${RED}❌ Error: No se encontró whatsapp-bot en $BOT_DIR${NC}"
    exit 1
fi

# Crear directorio de logs si no existe
mkdir -p "$LOGS_DIR"

echo -e "${GREEN}✅ Estructura verificada${NC}"

# Función de limpieza al salir
cleanup() {
    echo -e "\n${YELLOW}🛑 Deteniendo todos los servicios...${NC}"
    
    pkill -f "node.*bot\.js" 2>/dev/null
    pkill -f "php artisan schedule:work" 2>/dev/null  
    pkill -f "php artisan serve" 2>/dev/null
    
    echo -e "${GREEN}✅ Todos los servicios detenidos${NC}"
    echo -e "${PURPLE}👋 ¡Hasta luego!${NC}"
    exit 0
}

# Capturar señales para cleanup
trap cleanup SIGINT SIGTERM

# Verificar dependencias
echo -e "${BLUE}🔧 Verificando dependencias...${NC}"

if ! command -v node &> /dev/null; then
    echo -e "${RED}❌ Node.js no está instalado${NC}"
    exit 1
fi

if ! command -v php &> /dev/null; then
    echo -e "${RED}❌ PHP no está instalado${NC}"
    exit 1
fi

NODE_VERSION=$(node --version)
PHP_VERSION=$(php --version | head -n1)
echo -e "${GREEN}✅ Node.js: $NODE_VERSION${NC}"
echo -e "${GREEN}✅ PHP: $PHP_VERSION${NC}"

# 1. Iniciar Bot de WhatsApp
echo -e "\n${BLUE}1️⃣ Iniciando Bot de WhatsApp...${NC}"
cd "$BOT_DIR"

# Verificar que bot.js existe
if [ ! -f "bot.js" ]; then
    echo -e "${RED}❌ Error: bot.js no encontrado en $BOT_DIR${NC}"
    exit 1
fi

node bot.js > "$LOGS_DIR/whatsapp-bot.log" 2>&1 &
BOT_PID=$!

echo -e "${YELLOW}⏳ Esperando conexión del bot (15 segundos)...${NC}"
sleep 15

# Verificar si el bot está corriendo
if ! kill -0 $BOT_PID 2>/dev/null; then
    echo -e "${RED}❌ El bot de WhatsApp falló al iniciar${NC}"
    echo -e "${RED}📄 Revisa los logs: tail -f $LOGS_DIR/whatsapp-bot.log${NC}"
    exit 1
fi

# Verificar conectividad del bot
for i in {1..5}; do
    if curl -s http://localhost:3000/test > /dev/null 2>&1; then
        echo -e "${GREEN}✅ Bot de WhatsApp activo en puerto 3000${NC}"
        break
    else
        if [ $i -eq 5 ]; then
            echo -e "${RED}❌ Bot no responde después de 5 intentos${NC}"
            echo -e "${RED}📄 Revisa los logs: tail -f $LOGS_DIR/whatsapp-bot.log${NC}"
            exit 1
        fi
        echo -e "${YELLOW}⏳ Intento $i/5 - esperando respuesta del bot...${NC}"
        sleep 2
    fi
done

# 2. Iniciar Laravel Scheduler
echo -e "\n${BLUE}2️⃣ Iniciando Laravel Scheduler...${NC}"
cd "$LARAVEL_DIR"

php artisan schedule:work > "$LOGS_DIR/scheduler.log" 2>&1 &
SCHEDULER_PID=$!

sleep 2
if kill -0 $SCHEDULER_PID 2>/dev/null; then
    echo -e "${GREEN}✅ Scheduler iniciado correctamente${NC}"
else
    echo -e "${RED}❌ Error al iniciar el scheduler${NC}"
    exit 1
fi

# 3. Iniciar Laravel Server
echo -e "\n${BLUE}3️⃣ Iniciando Laravel Server...${NC}"

php artisan serve --host=127.0.0.1 --port=8000 > "$LOGS_DIR/laravel-server.log" 2>&1 &
SERVER_PID=$!

sleep 3
if kill -0 $SERVER_PID 2>/dev/null; then
    echo -e "${GREEN}✅ Servidor Laravel iniciado en http://localhost:8000${NC}"
else
    echo -e "${RED}❌ Error al iniciar el servidor Laravel${NC}"
    exit 1
fi

# Resumen final
echo -e "\n${GREEN}🎉 ¡SISTEMA INICIADO EXITOSAMENTE! 🎉${NC}"
echo -e "${PURPLE}══════════════════════════════════════════════════${NC}"
echo -e "${BLUE}📱 Bot WhatsApp:${NC}     http://localhost:3000/test"
echo -e "${BLUE}🌐 App Laravel:${NC}      http://localhost:8000"
echo -e "${BLUE}📂 Logs:${NC}             $LOGS_DIR/"
echo -e "${BLUE}⏰ Scheduler:${NC}        Activo (cada minuto)"
echo -e "${PURPLE}══════════════════════════════════════════════════${NC}"
echo -e "${YELLOW}💡 Presiona Ctrl+C para detener todos los servicios${NC}"
echo -e "${YELLOW}💡 Para ver logs: tail -f $LOGS_DIR/*.log${NC}"
echo ""

# Monitor principal
echo -e "${BLUE}📊 Monitoreando servicios...${NC}"
while true; do
    sleep 30
    
    # Verificar que todos los procesos siguen activos
    if ! kill -0 $BOT_PID 2>/dev/null; then
        echo -e "${RED}❌ Bot de WhatsApp se detuvo inesperadamente${NC}"
        cleanup
    fi
    
    if ! kill -0 $SCHEDULER_PID 2>/dev/null; then
        echo -e "${RED}❌ Scheduler se detuvo inesperadamente${NC}"
        cleanup  
    fi
    
    if ! kill -0 $SERVER_PID 2>/dev/null; then
        echo -e "${RED}❌ Servidor Laravel se detuvo inesperadamente${NC}"
        cleanup
    fi
    
    # Mostrar estado cada 2 minutos
    current_minute=$(date +%M)
        
    # Forzar base decimal para evitar errores con 08, 09
    current_minute=$((10#$current_minute))
    current_second=$(date +%S)
    current_second=$((10#$current_second))
    
    if [ $((current_minute % 2)) -eq 0 ] && [ $(date +%S) -lt 5 ]; then
        echo -e "${GREEN}✅ Todos los servicios funcionando correctamente - $(date '+%H:%M:%S')${NC}"
    fi
done

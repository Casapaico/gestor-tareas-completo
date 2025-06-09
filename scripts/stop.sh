#!/bin/bash

# Sistema de Gestión de Tareas - Script de Parada
# Autor: Alex

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
PURPLE='\033[0;35m'
NC='\033[0m'

echo -e "${PURPLE}"
echo "╔══════════════════════════════════════════════════╗"
echo "║           GESTOR DE TAREAS - PARADA             ║"
echo "╚══════════════════════════════════════════════════╝"
echo -e "${NC}"

echo -e "${YELLOW}🛑 Deteniendo Sistema de Gestión de Tareas...${NC}"

# Función para verificar y matar procesos
kill_process() {
    local process_name="$1"
    local display_name="$2"
    
    local pids=$(pgrep -f "$process_name" 2>/dev/null)
    
    if [ -n "$pids" ]; then
        echo -e "${YELLOW}🔄 Deteniendo $display_name (PIDs: $pids)...${NC}"
        pkill -f "$process_name"
        sleep 2
        
        # Verificar si se detuvo
        local remaining_pids=$(pgrep -f "$process_name" 2>/dev/null)
        if [ -z "$remaining_pids" ]; then
            echo -e "${GREEN}✅ $display_name detenido correctamente${NC}"
        else
            echo -e "${RED}⚠️ Forzando cierre de $display_name...${NC}"
            pkill -9 -f "$process_name"
            echo -e "${GREEN}✅ $display_name forzadamente detenido${NC}"
        fi
    else
        echo -e "${YELLOW}ℹ️ $display_name no estaba corriendo${NC}"
    fi
}

# Detener servicios en orden
kill_process "node.*bot\.js" "Bot de WhatsApp"
kill_process "php artisan schedule:work" "Laravel Scheduler"  
kill_process "php artisan serve" "Servidor Laravel"

# Verificar puertos
echo -e "\n${YELLOW}🔍 Verificando puertos...${NC}"

if lsof -i :3000 >/dev/null 2>&1; then
    echo -e "${RED}⚠️ Puerto 3000 aún en uso${NC}"
    lsof -ti :3000 | xargs kill -9 2>/dev/null
else
    echo -e "${GREEN}✅ Puerto 3000 liberado${NC}"
fi

if lsof -i :8000 >/dev/null 2>&1; then
    echo -e "${RED}⚠️ Puerto 8000 aún en uso${NC}"
    lsof -ti :8000 | xargs kill -9 2>/dev/null
else
    echo -e "${GREEN}✅ Puerto 8000 liberado${NC}"
fi

echo -e "\n${GREEN}🏁 Todos los servicios han sido detenidos correctamente${NC}"
echo -e "${PURPLE}💤 Sistema en reposo${NC}"
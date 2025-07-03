const venom = require('venom-bot');
const express = require('express');
const bodyParser = require('body-parser');
const fs = require('fs');
const path = require('path');

const app = express();
app.use(bodyParser.json());

let venomClient = null;
let qrCode = null;
let isCreatingClient = false;
let botStatus = {
    connected: false,
    status: 'Iniciando...',
    lastUpdate: new Date()
};

console.log('🚀 Iniciando bot de WhatsApp...');

// Configurar CORS para desarrollo local
app.use((req, res, next) => {
    res.header('Access-Control-Allow-Origin', '*');
    res.header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    res.header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization');
    
    if (req.method === 'OPTIONS') {
        res.sendStatus(200);
    } else {
        next();
    }
});

// 🔄 FUNCIÓN PARA LIMPIAR COMPLETAMENTE LA SESIÓN
function cleanSession(sessionName = 'my-session') {
    try {
        const pathsToClean = [
            `./tokens`,
            `./tokens/${sessionName}`,
            `./tokens/${sessionName}.data.json`,
            `./${sessionName}.data.json`,
            `./session`,
            `./session/${sessionName}`,
            // Limpiar posibles carpetas de Chrome/Chromium
            `./chrome-session`,
            `./chrome-data`,
            `./browser-data`
        ];
        
        pathsToClean.forEach(sessionPath => {
            if (fs.existsSync(sessionPath)) {
                if (fs.statSync(sessionPath).isDirectory()) {
                    fs.rmSync(sessionPath, { recursive: true, force: true });
                    console.log(`📁 Carpeta eliminada: ${sessionPath}`);
                } else {
                    fs.unlinkSync(sessionPath);
                    console.log(`🗃️ Archivo eliminado: ${sessionPath}`);
                }
            }
        });
        
        console.log('✅ Sesión limpiada completamente');
        return true;
    } catch (error) {
        console.error('❌ Error limpiando sesión:', error);
        return false;
    }
}

// 🔄 RUTA PARA LIMPIAR SESIÓN COMPLETAMENTE
app.post('/reset-session', async (req, res) => {
    console.log('🔄 Solicitud para resetear sesión completamente...');
    
    // Prevenir múltiples resets simultáneos
    if (isCreatingClient) {
        return res.status(429).json({
            success: false,
            message: 'Ya hay un reset en progreso, espera un momento...'
        });
    }
    
    try {
        // Cerrar cliente actual si existe
        if (venomClient) {
            try {
                await venomClient.close();
                console.log('🔌 Cliente existente cerrado');
            } catch (error) {
                console.error('Error cerrando cliente:', error);
            }
            venomClient = null;
        }
        
        // Resetear estado
        qrCode = null;
        botStatus = {
            connected: false,
            status: 'Reseteando...',
            message: 'Eliminando sesión y datos de navegador...',
            lastUpdate: new Date()
        };
        
        // Limpiar sesión completamente
        const cleanSuccess = cleanSession();
        
        // Esperar un momento antes de recrear
        setTimeout(() => {
            console.log('🔄 Recreando cliente después del reset...');
            createVenomClient();
        }, 3000);
        
        res.json({
            success: true,
            message: 'Sesión reseteada completamente. Nuevo cliente iniciándose...',
            status: botStatus
        });
        
    } catch (error) {
        console.error('❌ Error en reset:', error);
        res.status(500).json({
            success: false,
            message: 'Error al resetear: ' + error.message
        });
    }
});

// Crear cliente con configuración optimizada para Chrome Linux
function createVenomClient() {
    if (isCreatingClient) {
        console.log('⚠️ Ya hay un cliente creándose, saltando...');
        return;
    }
    
    isCreatingClient = true;
    console.log('🔧 Creando cliente de Venom con configuración optimizada...');
    
    // Configuración específica para solucionar problemas de Chrome en Linux
    const venomOptions = {
        session: 'my-session',
        multidevice: true,
        headless: true,
        logQR: false,
        disableSpins: true,
        autoClose: 60000, // 60 segundos de timeout
        
        // 🔧 CONFIGURACIÓN ESPECÍFICA PARA CHROME LINUX
        browserArgs: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-accelerated-2d-canvas',
            '--no-first-run',
            '--no-zygote',
            '--disable-gpu',
            '--disable-background-timer-throttling',
            '--disable-backgrounding-occluded-windows',
            '--disable-renderer-backgrounding',
            '--disable-features=TranslateUI',
            '--disable-ipc-flooding-protection',
            '--disable-extensions',
            '--disable-default-apps',
            '--disable-sync',
            '--disable-translate',
            '--hide-scrollbars',
            '--mute-audio',
            '--no-default-browser-check',
            '--no-pings',
            '--single-process', // Esto puede ayudar con problemas de memoria
            '--disable-web-security',
            '--disable-features=VizDisplayCompositor'
        ],
        
        // Configuraciones adicionales para estabilidad
        puppeteerOptions: {
            headless: true,
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-accelerated-2d-canvas',
                '--no-first-run',
                '--no-zygote',
                '--disable-gpu'
            ]
        },
        
        // Callbacks mejorados
        catchQR: (base64Qr, asciiQR, attempts, urlCode) => {
            console.log(`📱 Código QR generado (intento ${attempts})`);
            console.log('📱 QR ASCII en terminal:');
            console.log(asciiQR);
            
            qrCode = base64Qr;
            botStatus = {
                connected: false,
                status: 'Esperando QR',
                message: `Escanea el código QR para conectar WhatsApp (intento ${attempts}/5)`,
                lastUpdate: new Date(),
                attempts: attempts
            };
            
            // Si han fallado muchos intentos, sugerir reset
            if (attempts >= 3) {
                console.log('⚠️ Muchos intentos de QR, puede necesitar reset completo');
            }
        },
        
        statusFind: (statusSession, session) => {
            console.log(`📊 Estado de sesión: ${statusSession} | Sesión: ${session}`);
            
            switch(statusSession) {
                case 'notLogged':
                    botStatus = {
                        connected: false,
                        status: 'No conectado',
                        message: 'Necesitas escanear el código QR',
                        lastUpdate: new Date()
                    };
                    break;
                    
                case 'qrReadSuccess':
                    botStatus = {
                        connected: false,
                        status: 'QR Escaneado',
                        message: 'QR escaneado exitosamente, conectando...',
                        lastUpdate: new Date()
                    };
                    qrCode = null;
                    break;
                    
                case 'chatsAvailable':
                    botStatus = {
                        connected: true,
                        status: 'Conectado',
                        message: 'WhatsApp Web conectado correctamente',
                        lastUpdate: new Date()
                    };
                    isCreatingClient = false;
                    break;
                    
                case 'qrReadFail':
                    console.log('❌ Fallo al leer QR');
                    botStatus = {
                        connected: false,
                        status: 'Error QR',
                        message: 'Falló la lectura del QR. Intenta nuevamente o resetea la sesión.',
                        lastUpdate: new Date()
                    };
                    break;
                    
                case 'waitForLogin':
                    console.log('⏳ Esperando login...');
                    botStatus = {
                        connected: false,
                        status: 'Esperando Login',
                        message: 'Esperando que completes el login en WhatsApp...',
                        lastUpdate: new Date()
                    };
                    break;
                    
                case 'autocloseCalled':
                case 'browserClose':
                    console.log('🔌 Navegador cerrado');
                    botStatus = {
                        connected: false,
                        status: 'Navegador cerrado',
                        message: 'El navegador se cerró. Reinicia o resetea la sesión.',
                        lastUpdate: new Date()
                    };
                    isCreatingClient = false;
                    break;
                    
                case 'deleteToken':
                    console.log('🔄 Token eliminado, preparando nueva sesión...');
                    botStatus = {
                        connected: false,
                        status: 'Token eliminado',
                        message: 'Token eliminado, creando nueva sesión...',
                        lastUpdate: new Date()
                    };
                    break;
                    
                default:
                    console.log(`⚠️ Estado no manejado: ${statusSession}`);
                    botStatus = {
                        connected: false,
                        status: `Estado: ${statusSession}`,
                        message: `Estado del bot: ${statusSession}`,
                        lastUpdate: new Date()
                    };
                    break;
            }
        }
    };
    
    venom
        .create(venomOptions)
        .then((client) => {
            console.log('✅ Cliente de WhatsApp conectado exitosamente');
            venomClient = client;
            isCreatingClient = false;
            
            botStatus = {
                connected: true,
                status: 'Conectado',
                message: 'Bot funcionando correctamente',
                lastUpdate: new Date()
            };
            
            qrCode = null;
            start(client);
        })
        .catch((error) => {
            console.error('❌ Error conectando WhatsApp:', error);
            isCreatingClient = false;
            
            // Manejo específico de errores
            let errorMessage = error.message || error.toString();
            
            if (errorMessage.includes('Page Closed')) {
                botStatus = {
                    connected: false,
                    status: 'Error - Página cerrada',
                    message: 'La página de WhatsApp se cerró inesperadamente. Usa reset para reintentar.',
                    lastUpdate: new Date()
                };
            } else if (errorMessage.includes('Failed to authenticate')) {
                botStatus = {
                    connected: false,
                    status: 'Error - Autenticación fallida',
                    message: 'Falló la autenticación. Resetea la sesión e intenta nuevamente.',
                    lastUpdate: new Date()
                };
            } else if (errorMessage.includes('Checking is logged')) {
                botStatus = {
                    connected: false,
                    status: 'Error - Sesión bloqueada',
                    message: 'Sesión bloqueada en "Checking is logged". Resetea la sesión.',
                    lastUpdate: new Date()
                };
            } else {
                botStatus = {
                    connected: false,
                    status: 'Error de conexión',
                    message: 'Error al inicializar: ' + errorMessage.substring(0, 100),
                    lastUpdate: new Date()
                };
            }
            
            // Auto-retry después de 30 segundos si hay error
            console.log('🔄 Reintentando conexión en 30 segundos...');
            setTimeout(() => {
                if (!venomClient) {
                    console.log('🔄 Reintentando crear cliente...');
                    createVenomClient();
                }
            }, 30000);
        });
}

function start(client) {
    console.log('🌐 Configurando servidor Express...');
    
    // Detectar cambios de estado para manejar desconexiones
    client.onStateChange((state) => {
        console.log('🔄 Estado del cliente cambiado:', state);
        
        switch(state) {
            case 'CONFLICT':
                console.log('⚠️ Conflicto detectado - forzando tomar control');
                client.useHere();
                break;
                
            case 'UNPAIRED':
            case 'UNLAUNCHED':
                console.log('❌ Cliente desemparejado o no lanzado');
                botStatus = {
                    connected: false,
                    status: 'Desconectado',
                    message: `Estado: ${state}. Resetea la sesión si persiste.`,
                    lastUpdate: new Date()
                };
                break;
                
            case 'CONNECTED':
                console.log('✅ Cliente conectado correctamente');
                botStatus = {
                    connected: true,
                    status: 'Conectado',
                    message: 'WhatsApp conectado y funcionando',
                    lastUpdate: new Date()
                };
                break;
        }
    });
    
    // Monitorear cambios de stream
    client.onStreamChange((state) => {
        console.log('🌊 Estado de stream cambiado:', state);
        
        if (state === 'DISCONNECTED') {
            console.log('📱 Desconectado del teléfono');
            botStatus = {
                connected: false,
                status: 'Desconectado del teléfono',
                message: 'El teléfono se desconectó. Verifica la conexión.',
                lastUpdate: new Date()
            };
        } else if (state === 'CONNECTED') {
            console.log('📱 Reconectado al teléfono');
            botStatus = {
                connected: true,
                status: 'Conectado',
                message: 'Reconectado exitosamente',
                lastUpdate: new Date()
            };
        }
    });
    
    // TUS RUTAS ORIGINALES (sin cambios)
    app.post('/send-message', async (req, res) => {
        console.log('📨 Petición recibida para enviar mensaje');
        
        const { number, message } = req.body;
        
        if (!number || !message) {
            console.error('❌ Faltan datos: number o message');
            return res.status(400).json({ 
                status: 'error', 
                error: 'Faltan campos requeridos: number y message' 
            });
        }

        if (!botStatus.connected) {
            return res.status(503).json({
                status: 'error',
                error: 'Bot no está conectado. Verifica el estado en el dashboard.',
                botStatus: botStatus
            });
        }

        try {
            const formattedNumber = number.includes('@c.us') ? number : `${number}@c.us`;
            
            console.log(`📤 Enviando mensaje a: ${formattedNumber}`);
            console.log(`📝 Mensaje: ${message.substring(0, 100)}${message.length > 100 ? '...' : ''}`);
            
            await client.sendText(formattedNumber, message);
            
            console.log('✅ Mensaje enviado exitosamente');
            return res.status(200).json({ 
                status: 'enviado',
                number: formattedNumber,
                timestamp: new Date().toISOString()
            });
            
        } catch (err) {
            console.error('❌ Error enviando mensaje:', err);
            return res.status(500).json({ 
                status: 'error', 
                error: err.toString(),
                timestamp: new Date().toISOString()
            });
        }
    });

    app.post('/send-notification', async (req, res) => {
        console.log('📨 Petición recibida para enviar notificación');
        
        const { number, message, imagePath } = req.body;
        
        if (!number || !message) {
            console.error('❌ Faltan datos: number o message');
            return res.status(400).json({ 
                status: 'error', 
                error: 'Faltan campos requeridos: number y message' 
            });
        }

        if (!botStatus.connected) {
            return res.status(503).json({
                status: 'error',
                error: 'Bot no está conectado',
                botStatus: botStatus
            });
        }

        try {
            const formattedNumber = number.includes('@c.us') ? number : `${number}@c.us`;
            
            console.log(`📤 Enviando notificación a: ${formattedNumber}`);
            
            if (imagePath) {
                try {
                    if (fs.existsSync(imagePath)) {
                        console.log(`🖼️ Enviando imagen: ${path.basename(imagePath)}`);
                        await client.sendImage(
                            formattedNumber,
                            imagePath,
                            path.basename(imagePath),
                            message
                        );
                        console.log('✅ Imagen con mensaje enviada exitosamente');
                        return res.status(200).json({ 
                            status: 'enviado',
                            type: 'image_with_text',
                            number: formattedNumber,
                            timestamp: new Date().toISOString()
                        });
                    } else {
                        console.warn('⚠️ Imagen no encontrada, enviando solo texto');
                        await client.sendText(formattedNumber, message);
                    }
                } catch (imageError) {
                    console.error('❌ Error con imagen, enviando solo texto:', imageError);
                    await client.sendText(formattedNumber, message);
                }
            } else {
                console.log('📝 Enviando solo mensaje de texto');
                await client.sendText(formattedNumber, message);
            }
            
            console.log('✅ Notificación enviada exitosamente');
            return res.status(200).json({ 
                status: 'enviado',
                type: imagePath ? 'text_fallback' : 'text',
                number: formattedNumber,
                timestamp: new Date().toISOString()
            });
            
        } catch (err) {
            console.error('❌ Error enviando notificación:', err);
            return res.status(500).json({ 
                status: 'error', 
                error: err.toString(),
                timestamp: new Date().toISOString()
            });
        }
    });

    // Ruta para obtener el estado del bot
    app.get('/status', (req, res) => {
        res.json({
            ...botStatus,
            hasQR: qrCode !== null,
            uptime: process.uptime(),
            isCreatingClient: isCreatingClient
        });
    });

    // Ruta para obtener el código QR
    app.get('/qr', (req, res) => {
        if (qrCode) {
            res.json({
                success: true,
                qr: qrCode,
                status: 'QR disponible'
            });
        } else if (botStatus.connected) {
            res.json({
                success: false,
                message: 'Bot ya está conectado',
                status: 'connected'
            });
        } else {
            res.json({
                success: false,
                message: 'Código QR no disponible en este momento',
                status: botStatus.status
            });
        }
    });

    // Ruta de prueba mejorada
    app.get('/test', (req, res) => {
        res.json({ 
            status: 'Bot funcionando correctamente',
            features: ['text', 'image', 'notifications', 'qr', 'status', 'reset-session'],
            botStatus: botStatus,
            browserInfo: {
                platform: 'linux',
                chrome: '/usr/bin/google-chrome-stable'
            },
            timestamp: new Date().toISOString(),
            uptime: process.uptime()
        });
    });

    // Escuchar en puerto 3000
    app.listen(3000, () => {
        console.log('🚀 Servidor de VenomBot activo en puerto 3000');
        console.log('📱 Endpoints disponibles:');
        console.log('  - POST /send-message (mensaje de texto)');
        console.log('  - POST /send-notification (texto o imagen+texto)');
        console.log('  - POST /reset-session (limpiar sesión completamente) 🔄');
        console.log('  - GET /status (estado del bot)');
        console.log('  - GET /qr (código QR para conectar)');
        console.log('  - GET /test (verificar estado)');
        console.log('🌐 Listo para recibir peticiones de Laravel');
        console.log('');
        console.log('💡 SOLUCIÓN PARA ERRORES:');
        console.log('   1. Si ves "Page Closed" o "Failed to authenticate":');
        console.log('      curl -X POST http://localhost:3000/reset-session');
        console.log('   2. Espera 30 segundos y verifica: curl http://localhost:3000/status');
        console.log('   3. Si persiste, reinicia completamente: pkill node && node bot.js');
    });
}

// Inicializar el cliente
createVenomClient();

// Manejar errores globales
process.on('unhandledRejection', (reason, promise) => {
    console.error('❌ Unhandled Rejection at:', promise, 'reason:', reason);
    botStatus = {
        connected: false,
        status: 'Error interno',
        message: 'Error interno del bot: ' + reason,
        lastUpdate: new Date()
    };
});

process.on('uncaughtException', (error) => {
    console.error('❌ Uncaught Exception:', error);
    botStatus = {
        connected: false,
        status: 'Error crítico',
        message: 'Error crítico del bot: ' + error.message,
        lastUpdate: new Date()
    };
});

// Cerrar correctamente al terminar
process.on('SIGINT', function() {
    console.log('🛑 Cerrando bot correctamente...');
    if (venomClient) {
        venomClient.close().then(() => {
            console.log('✅ Cliente cerrado correctamente');
            process.exit(0);
        });
    } else {
        process.exit(0);
    }
});

process.on('SIGTERM', function() {
    console.log('🛑 Terminando bot...');
    if (venomClient) {
        venomClient.close().then(() => {
            process.exit(0);
        });
    } else {
        process.exit(0);
    }
});

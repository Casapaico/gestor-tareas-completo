<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create {--email=} {--name=} {--password=} {--admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crear un usuario regular para el sistema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('👤 Creando nuevo usuario...');
        $this->newLine();

        // Obtener datos del usuario
        $email = $this->option('email') ?: $this->ask('Email del usuario');
        $name = $this->option('name') ?: $this->ask('Nombre completo del usuario');
        $password = $this->option('password') ?: $this->secret('Contraseña del usuario');
        $isAdmin = $this->option('admin') || $this->confirm('¿Es administrador?', false);

        // Validar datos
        $validator = Validator::make([
            'email' => $email,
            'name' => $name,
            'password' => $password,
        ], [
            'email' => 'required|email|unique:users,email',
            'name' => 'required|string|max:255',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            $this->error('❌ Datos inválidos:');
            foreach ($validator->errors()->all() as $error) {
                $this->line("  • $error");
            }
            return Command::FAILURE;
        }

        try {
            // Crear el usuario
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'is_admin' => $isAdmin,
                'active' => true,
            ]);

            $this->newLine();
            $this->info('✅ Usuario creado exitosamente!');
            $this->newLine();
            
            $this->table(
                ['Campo', 'Valor'],
                [
                    ['ID', $user->id],
                    ['Nombre', $user->name],
                    ['Email', $user->email],
                    ['Tipo', $user->is_admin ? 'Administrador' : 'Usuario Regular'],
                    ['Estado', $user->active ? 'Activo' : 'Inactivo'],
                    ['Creado', $user->created_at->format('d/m/Y H:i:s')],
                ]
            );

            $this->newLine();
            
            if ($isAdmin) {
                $this->info('🔑 Usuario administrador creado. Puede:');
                $this->line('  • Gestionar otros usuarios');
                $this->line('  • Manejar el bot de WhatsApp');
                $this->line('  • Ver el código QR del bot');
            } else {
                $this->info('👤 Usuario regular creado. Puede:');
                $this->line('  • Crear y gestionar sus mensajes');
                $this->line('  • Gestionar sus contactos');
                $this->line('  • Ver el estado del bot (sin gestionarlo)');
            }
            
            $this->newLine();
            $this->info('📱 Instrucciones para el usuario:');
            $this->line("  1. Ir a: http://localhost:8000/login");
            $this->line("  2. Iniciar sesión con: {$email}");
            $this->line("  3. Usar la contraseña que acabas de crear");
            $this->line("  4. ¡Empezar a usar el sistema!");

            if (!$isAdmin) {
                $this->newLine();
                $this->warn('⚠️  Recuerda: El bot de WhatsApp debe estar conectado por un administrador');
                $this->line('   para que los mensajes se envíen correctamente.');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error creando usuario: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
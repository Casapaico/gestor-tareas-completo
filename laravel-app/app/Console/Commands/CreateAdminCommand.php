<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create {--email=} {--name=} {--password=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crear un usuario administrador para el sistema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Creando usuario administrador...');
        $this->newLine();

        // Obtener datos del admin
        $email = $this->option('email') ?: $this->ask('Email del administrador');
        $name = $this->option('name') ?: $this->ask('Nombre completo del administrador');
        $password = $this->option('password') ?: $this->secret('Contraseña del administrador');

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

        // Verificar si ya existe un admin
        $existingAdmin = User::where('is_admin', true)->first();
        if ($existingAdmin) {
            $this->warn("⚠️  Ya existe un administrador: {$existingAdmin->name} ({$existingAdmin->email})");
            
            if (!$this->confirm('¿Deseas crear otro administrador?')) {
                $this->info('Operación cancelada.');
                return Command::SUCCESS;
            }
        }

        try {
            // Crear el administrador
            $admin = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'is_admin' => true,
                'active' => true,
            ]);

            $this->newLine();
            $this->info('✅ Administrador creado exitosamente!');
            $this->newLine();
            
            $this->table(
                ['Campo', 'Valor'],
                [
                    ['ID', $admin->id],
                    ['Nombre', $admin->name],
                    ['Email', $admin->email],
                    ['Admin', $admin->is_admin ? 'Sí' : 'No'],
                    ['Creado', $admin->created_at->format('d/m/Y H:i:s')],
                ]
            );

            $this->newLine();
            $this->info('🎯 Próximos pasos:');
            $this->line('  1. Inicia el servidor: php artisan serve');
            $this->line('  2. Visita: http://localhost:8000/login');
            $this->line("  3. Inicia sesión con: {$email}");
            $this->line('  4. Accede al panel de admin desde el dashboard');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error creando administrador: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
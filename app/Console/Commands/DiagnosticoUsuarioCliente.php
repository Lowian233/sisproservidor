<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\User;
use Illuminate\Support\Facades\Hash;

class DiagnosticoUsuarioCliente extends Command
{
    protected $signature = 'usuario:diagnostico {email : Email del usuario a revisar}';
    protected $description = 'Diagnostica por qué un usuario cliente no puede iniciar sesión';

    public function handle()
    {
        $email = trim($this->argument('email'));
        $this->info("Buscando usuario con email: \"{$email}\"");

        $user = User::where('email', $email)->first();
        if (!$user) {
            $user = User::whereRaw('LOWER(email) = ?', [strtolower($email)])->first();
            if ($user) {
                $this->warn("Usuario encontrado pero con email diferente (posible diferencia de mayúsculas):");
                $this->line("  En BD: \"{$user->email}\"");
                $this->line("  Usted buscó: \"{$email}\"");
            }
        }

        if (!$user) {
            $this->error("No se encontró ningún usuario con ese email.");
            $this->line("Sugerencias:");
            $this->line("  - Verifique que el email esté escrito exactamente igual que en la base de datos");
            $this->line("  - Compruebe que no haya espacios antes o después del email");
            return 1;
        }

        $this->info("Usuario encontrado:");
        $this->table(
            ['Campo', 'Valor'],
            [
                ['ID', $user->id],
                ['Nombre', $user->name],
                ['Email', $user->email],
                ['UsRol', $user->UsRol ?? 'N/A'],
                ['email_verified_at', $user->email_verified_at ? Carbon::parse($user->email_verified_at)->format('Y-m-d H:i') : 'NO VERIFICADO'],
                ['FK_UserPers', $user->FK_UserPers ?? 'N/A'],
                ['password (hash)', substr($user->password, 0, 20) . '...'],
            ]
        );

        if (!$user->email_verified_at) {
            $this->warn("El correo NO está verificado. El usuario debe verificar su email para acceder a ciertas rutas.");
        }

        if ($this->confirm('¿Desea restablecer la contraseña de este usuario?', false)) {
            $nuevaClave = $this->secret('Ingrese la nueva contraseña (mín. 8 caracteres)');
            if (strlen($nuevaClave) < 8) {
                $this->error("La contraseña debe tener al menos 8 caracteres.");
                return 1;
            }
            $user->password = Hash::make($nuevaClave);
            $user->save();
            $this->info("Contraseña actualizada correctamente.");
        }

        return 0;
    }
}

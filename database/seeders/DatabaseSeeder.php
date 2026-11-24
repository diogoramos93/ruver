<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents; // Opcional, pode deixar comentado

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Cria o usuário Super Admin padrão do sistema
        User::updateOrCreate(
            ['email' => 'admin@ruver.com.br'], // ⚠️ Altere para o seu e-mail oficial
            [
                'name' => 'Super Admin',
                'password' => Hash::make('19931993'), // ⚠️ Altere para a senha que desejar
                'role' => 'admin', // Define o acesso total
                'email_verified_at' => now(), // Já valida o e-mail automaticamente
            ]
        );

        // Se quiser gerar usuários de teste depois, é só descomentar a linha abaixo:
        // User::factory(10)->create(['role' => 'cliente']);
    }
}
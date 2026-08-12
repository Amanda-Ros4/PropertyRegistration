<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'perfil') && ! Schema::hasColumn('users', 'profile')) {
            DB::table('users')->where('perfil', 'atendente')->update(['perfil' => 'A']);
            DB::table('users')->where('perfil', 'administrador_sistema')->update(['perfil' => 'S']);
            DB::table('users')->where('perfil', 'administrador_ti')->update(['perfil' => 'T']);
            DB::table('users')->whereNotIn('perfil', ['T', 'S', 'A'])->update(['perfil' => 'A']);

            DB::statement("ALTER TABLE users CHANGE perfil profile CHAR(1) NOT NULL DEFAULT 'A'");
        }

        if (Schema::hasColumn('users', 'ativo') && ! Schema::hasColumn('users', 'active')) {
            DB::statement("ALTER TABLE users CHANGE ativo active CHAR(1) NOT NULL DEFAULT 'S'");
        }

        if (! Schema::hasColumn('users', 'profile')) {
            Schema::table('users', function (Blueprint $table) {
                $table->char('profile', 1)->default('A')->after('cpf');
            });
        }

        if (! Schema::hasColumn('users', 'active')) {
            Schema::table('users', function (Blueprint $table) {
                $table->char('active', 1)->default('S')->after('profile');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'profile') && ! Schema::hasColumn('users', 'perfil')) {
            DB::statement("ALTER TABLE users CHANGE profile perfil VARCHAR(255) NOT NULL DEFAULT 'atendente'");
        }

        if (Schema::hasColumn('users', 'active') && ! Schema::hasColumn('users', 'ativo')) {
            DB::statement("ALTER TABLE users CHANGE active ativo CHAR(1) NOT NULL DEFAULT 'S'");
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * CPF e telefone passam a ser armazenados apenas com dígitos (sem pontuação).
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('users')->whereNotNull('cpf')->cursor() as $row) {
            $digits = preg_replace('/[^0-9]/', '', $row->cpf);
            if ($digits !== $row->cpf) {
                DB::table('users')->where('id', $row->id)->update(['cpf' => $digits]);
            }
        }

        foreach (DB::table('people')->cursor() as $row) {
            $cpf = preg_replace('/[^0-9]/', '', $row->cpf);
            $phoneRaw = $row->phone;
            $phone = $phoneRaw !== null && $phoneRaw !== ''
                ? preg_replace('/[^0-9]/', '', $phoneRaw)
                : null;
            $phone = $phone === '' ? null : $phone;

            if ($cpf !== $row->cpf || $phone !== $row->phone) {
                DB::table('people')->where('id', $row->id)->update([
                    'cpf' => $cpf,
                    'phone' => $phone,
                ]);
            }
        }
    }

    public function down(): void
    {
        //
    }
};

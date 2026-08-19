<?php

return [

    // Liga ou desliga a auditoria no sistema.
    'enabled' => env('AUDITING_ENABLED', true),

    /*
     * Classe que representa cada registro salvo na tabela audits.
     * Usamos App\Models\Audit para adaptar rótulos e consultas ao projeto.
     */
    'implementation' => App\Models\Audit::class,

    /*
     * Identifica quem executou a ação.
     * O morph_prefix define as colunas user_id e user_type na tabela audits.
     */
    'user' => [
        'morph_prefix' => 'user',
        'guards' => [
            'web',
            'api',
        ],
        'resolver' => OwenIt\Auditing\Resolvers\UserResolver::class,
    ],

    /*
     * Dados de contexto gravados junto com cada operação:
     * IP, navegador (user agent) e URL da requisição.
     */
    'resolvers' => [
        'ip_address' => OwenIt\Auditing\Resolvers\IpAddressResolver::class,
        'user_agent' => OwenIt\Auditing\Resolvers\UserAgentResolver::class,
        'url' => OwenIt\Auditing\Resolvers\UrlResolver::class,
    ],

    /*
     * Eventos do Eloquent que geram registro de auditoria.
     * created = inclusão, updated = alteração, deleted = exclusão, restored = restauração.
     */
    'events' => [
        'created',
        'updated',
        'deleted',
        'restored',
    ],

    // Em modo estrito, atributos ocultos também entram na auditoria.
    'strict' => false,

    // Campos ignorados em todos os modelos auditados (além do exclude de cada model).
    'exclude' => [],

    /*
     * Se old_values e new_values vierem vazios, o registro ainda é salvo?
     * Útil para eventos que não carregam diff, como retrieved.
     */
    'empty_values' => true,
    'allowed_empty_values' => [
        'retrieved',
    ],

    // Por padrão, valores do tipo array não são auditados (evita JSON muito grande).
    'allowed_array_values' => false,

    // Não auditamos created_at, updated_at e deleted_at automaticamente.
    'timestamps' => false,

    // Limite de registros por model; 0 = sem limite.
    'threshold' => 0,

    // Onde os logs são armazenados. Aqui usamos apenas o banco de dados.
    'driver' => 'database',

    'drivers' => [
        'database' => [
            'table' => 'audits',
            'connection' => null,
        ],
    ],

    /*
     * Fila para gravar auditoria de forma assíncrona.
     * Desligado: cada operação grava o log na hora (sync).
     */
    'queue' => [
        'enable' => false,
        'connection' => 'sync',
        'queue' => 'default',
        'delay' => 0,
    ],

    /*
     * Comandos Artisan (seed, migrate etc.) também geram auditoria?
     * false em produção; nos testes usamos AUDITING_CONSOLE=true no phpunit.xml.
     */
    'console' => env('AUDITING_CONSOLE', false),
];

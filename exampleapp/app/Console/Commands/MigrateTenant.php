<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class MigrateTenant extends Command
{
    /**
     * php artisan tenant:migrate {database}
     *   --path   : optional migration path (defaults to all pending migrations)
     *   --fresh  : drop all tables and re-migrate
     */
    protected $signature = 'tenant:migrate
                            {database : The tenant database name (e.g. maica_university)}
                            {--path= : Optional migration path}
                            {--fresh : Drop all tables and re-migrate (use with caution!)}';

    protected $description = 'Run migrations against a specific tenant database';

    public function handle(): int
    {
        $database = $this->argument('database');

        // Verify the database exists
        $exists = DB::select(
            'SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?',
            [$database]
        );

        if (empty($exists)) {
            $this->error("Database \"{$database}\" does not exist.");
            return self::FAILURE;
        }

        // Temporarily configure and switch to tenant connection
        Config::set('database.connections.tenant_migrate', [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST', '127.0.0.1'),
            'port'      => env('DB_PORT', '3306'),
            'database'  => $database,
            'username'  => env('DB_USERNAME', 'root'),
            'password'  => env('DB_PASSWORD', ''),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
        ]);

        $this->info("Switching to database: {$database}");

        $options = [
            '--database' => 'tenant_migrate',
            '--force'    => true,
        ];

        if ($this->option('path')) {
            $options['--path'] = $this->option('path');
        }

        if ($this->option('fresh')) {
            if (!$this->confirm("⚠️  This will DROP ALL TABLES in \"{$database}\". Are you sure?")) {
                $this->info('Aborted.');
                return self::SUCCESS;
            }
            $this->call('migrate:fresh', $options);
        } else {
            $this->call('migrate', $options);
        }

        $this->info("Done migrating \"{$database}\".");
        return self::SUCCESS;
    }
}

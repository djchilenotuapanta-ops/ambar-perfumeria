<?php

namespace Tests\Feature;

use Tests\TestCase;

class AppConfigTest extends TestCase
{
    public function test_app_config_registers_core_service_providers()
    {
        $providers = config('app.providers', []);

        $this->assertNotEmpty($providers);
        $this->assertContains(\App\Providers\AppServiceProvider::class, $providers);
    }

    public function test_sqlite_database_path_is_resolved_to_an_absolute_path_when_relative()
    {
        $database = config('database.connections.sqlite.database');

        $this->assertNotSame('database/database.sqlite', $database);
        $this->assertTrue(
            $database === ':memory:' || str_starts_with($database, base_path()),
            "La ruta de sqlite debe ser ':memory:' (entorno de test) o una ruta absoluta, se obtuvo: {$database}"
        );
    }
}

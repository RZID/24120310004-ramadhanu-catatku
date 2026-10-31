<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApplicationBootTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_is_served(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_test_suite_runs_against_mysql(): void
    {
        $this->assertSame('mysql', DB::connection()->getDriverName());
        $this->assertTrue(DB::getSchemaBuilder()->hasTable('users'));
    }
}

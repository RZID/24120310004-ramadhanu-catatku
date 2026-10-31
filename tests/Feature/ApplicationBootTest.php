<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApplicationBootTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_the_login_page(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_test_suite_runs_against_mysql(): void
    {
        $this->assertSame('mysql', DB::connection()->getDriverName());
        $this->assertTrue(DB::getSchemaBuilder()->hasTable('users'));
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Tamu yang membuka halaman utama diarahkan ke halaman login.
     */
    public function test_tamu_di_halaman_utama_diarahkan_ke_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }
}
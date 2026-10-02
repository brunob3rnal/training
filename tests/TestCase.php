<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Los tests de PHP no necesitan los assets compilados (public/build no está en git).
        $this->withoutVite();
    }
}

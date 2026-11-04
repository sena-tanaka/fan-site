<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // テストでは Vite(CSS・JS)を読み込まない。GitHub Actions には npm run build の結果がないため
        $this->withoutVite();
    }
}
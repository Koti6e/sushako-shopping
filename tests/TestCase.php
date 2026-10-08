<?php

namespace Tests;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        fwrite(STDERR, "RUNNING_TESTS=" . (app()->runningUnitTests() ? 'yes' : 'no') . PHP_EOL);

        if (app()->runningUnitTests()) {
            $this->withoutMiddleware(ValidateCsrfToken::class);
        }
    }
}

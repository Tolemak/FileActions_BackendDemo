<?php

namespace App\Tests\Config;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Yaml\Yaml;

class SentryConfigTest extends TestCase
{
    public function testClientErrorsFromScannersAreNotReported(): void
    {
        $config = Yaml::parseFile(__DIR__.'/../../config/packages/sentry.yaml');

        $ignored = $config['when@prod']['sentry']['options']['ignore_exceptions'];

        $this->assertContains(NotFoundHttpException::class, $ignored);
        $this->assertContains(MethodNotAllowedHttpException::class, $ignored);
    }
}

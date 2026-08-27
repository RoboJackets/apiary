<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class NovaFrontendBootstrapTest extends TestCase
{
    public function test_nova_layout_keeps_safe_javascript_bootstrap(): void
    {
        $this->withoutMix();

        $admin = $this->getTestUser(['admin']);

        $response = $this->actingAs($admin, 'web')->get('/nova/dashboards/main');

        $response->assertOk();

        $content = $response->getContent();

        $this->assertIsString($content);
        $this->assertStringContainsString('createNovaApp(config)', $content);
        $this->assertStringNotContainsString('createNovaApp(config) Nova.countdown()', $content);
        $this->assertStringNotContainsString('createNovaApp(config)Nova.countdown()', $content);
        $this->assertTrue(
            str_contains($content, "createNovaApp(config)\n")
                || str_contains($content, 'createNovaApp(config);'),
            'Nova bootstrap must keep a newline or semicolon before Nova.countdown()'
        );
    }
}

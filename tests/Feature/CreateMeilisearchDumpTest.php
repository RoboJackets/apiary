<?php

declare(strict_types=1);

namespace Tests\Feature;

use Meilisearch\Client;
use Mockery;
use Tests\TestCase;

final class CreateMeilisearchDumpTest extends TestCase
{
    public function test_dump_command_creates_dump_and_prunes_old_dumps(): void
    {
        $dumpPath = storage_path('app/testing/meilisearch-dumps-'.uniqid());
        mkdir($dumpPath, 0o755, true);
        config(['scout.meilisearch.dump-path' => $dumpPath]);

        $files = [];
        foreach (range(1, 4) as $index) {
            $file = $dumpPath.'/dump'.$index.'.dump';
            file_put_contents($file, 'dump');
            touch($file, time() + $index);
            $files[$index] = $file;
        }

        $client = Mockery::mock(Client::class);
        $client->shouldReceive('createDump')
            ->once()
            ->andReturn(['taskUid' => 42]);
        $client->shouldReceive('waitForTask')
            ->once()
            ->with(42, Mockery::type('int'), Mockery::type('int'))
            ->andReturn(['status' => 'succeeded']);

        $this->instance(Client::class, $client);

        $this->artisan('meilisearch:dump')->assertExitCode(0);

        $this->assertFileDoesNotExist($files[1]);
        $this->assertFileDoesNotExist($files[2]);
        $this->assertFileExists($files[3]);
        $this->assertFileExists($files[4]);

        array_map('unlink', glob($dumpPath.'/*') ?: []);
        rmdir($dumpPath);
    }

    public function test_dump_command_fails_when_task_does_not_succeed(): void
    {
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('createDump')
            ->once()
            ->andReturn(['taskUid' => 7]);
        $client->shouldReceive('waitForTask')
            ->once()
            ->andReturn(['status' => 'failed']);

        $this->instance(Client::class, $client);

        $this->artisan('meilisearch:dump')->assertExitCode(1);
    }
}

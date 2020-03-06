<?php

namespace Tests;

use Blue32a\Laravel\Filesystem\AzureBlobStorage\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class AzureBlobStorageTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public const STORAGE_DISK = 'azure-blob';

    /**
     * @test
     */
    public function testBoot()
    {
        $storageSpy = Storage::spy();

        $appMock = Mockery::mock('Application');
        $provider = new ServiceProvider($appMock);
        $provider->boot();

        $storageSpy->shouldHaveReceived('extend')
            ->once()
            ->with(self::STORAGE_DISK, Mockery::type('callable'));
    }
}

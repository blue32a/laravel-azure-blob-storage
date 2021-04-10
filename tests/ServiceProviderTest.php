<?php

namespace Tests;

use Blue32a\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter;
use Blue32a\Laravel\Filesystem\AzureBlobStorage\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class ServiceProviderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public const STORAGE_DISK = 'azure-blob';

    /**
     * @return \Mockery\MockInterface&\Mockery\LegacyMockInterface&ServiceProvider
     */
    protected function createTargetMock()
    {
        return Mockery::mock(ServiceProvider::class);
    }

    /**
     * @return \ReflectionClass
     */
    protected function createTargetReflection()
    {
        return new \ReflectionClass(ServiceProvider::class);
    }

    /**
     * @test
     */
    public function testBoot(): void
    {
        $storageSpy = Storage::spy();

        $appMock  = Mockery::mock('Application');
        $provider = new ServiceProvider($appMock);
        $provider->boot();

        $storageSpy->shouldHaveReceived('extend')
            ->once()
            ->with(self::STORAGE_DISK, Mockery::type('callable'));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @test
     */
    public function testCreateAdapter(): void
    {
        $publicEndpoint = 'https://storage.example.com';
        $config         = [
            'container' => 'example',
            'public_endpoint' => $publicEndpoint,
        ];
        $connectionStr  = 'example_connection_string';

        $targetMock = $this->createTargetMock();
        $targetMock->shouldAllowMockingProtectedMethods();
        $targetMock
            ->shouldReceive('createConnectionString')
            ->with($config)
            ->andReturn($connectionStr);

        $blobRestProxyMock = $this->createBlobRestProxyAliasMock();
        $blobRestProxyMock
            ->shouldReceive('createBlobService')
            ->with($connectionStr)
            ->andReturn($blobRestProxyMock);

        $targetRef = $this->createTargetReflection();

        $createAdapterRef = $targetRef->getMethod('createAdapter');
        $createAdapterRef->setAccessible(true);

        $result = $createAdapterRef->invoke($targetMock, $config);
        $this->assertInstanceOf(AzureBlobStorageAdapter::class, $result);

        $resultRef         = new \ReflectionClass($result);
        $publicEndpointRef = $resultRef->getProperty('publicEndpoint');
        $publicEndpointRef->setAccessible(true);
        $this->assertEquals($publicEndpoint, $publicEndpointRef->getValue($result));
    }

    /**
     * @return \Mockery\MockInterface&\Mockery\LegacyMockInterface&BlobRestProxy
     */
    protected function createBlobRestProxyAliasMock()
    {
        return Mockery::mock('alias:' . BlobRestProxy::class);
    }

    /**
     * @test
     */
    public function testCreateConnectionString(): void
    {
        $targetRef = $this->createTargetReflection();

        $createConnectionStringRef = $targetRef->getMethod('createConnectionString');
        $createConnectionStringRef->setAccessible(true);

        $targetMock = $this->createTargetMock();

        $secure       = true;
        $name         = 'example_name';
        $key          = 'example_key';
        $blobEndpoint = null;

        $resulFirst = $createConnectionStringRef->invoke($targetMock, [
            'secure' => $secure,
            'name' => $name,
            'key' => $key,
            'blob_endpoint' => $blobEndpoint,
        ]);

        $this->assertStringContainsString('DefaultEndpointsProtocol=https;', $resulFirst);
        $this->assertStringContainsString(sprintf('AccountName=%s;', $name), $resulFirst);
        $this->assertStringContainsString(sprintf('AccountKey=%s;', $key), $resulFirst);
        $this->assertStringNotContainsString('BlobEndpoint', $resulFirst);

        $secure       = false;
        $blobEndpoint = 'https://blob.example.com';

        $resulSecond = $createConnectionStringRef->invoke($targetMock, [
            'secure' => $secure,
            'name' => $name,
            'key' => $key,
            'blob_endpoint' => $blobEndpoint,
        ]);
        $this->assertStringContainsString('DefaultEndpointsProtocol=http;', $resulSecond);
        $this->assertStringContainsString(sprintf('BlobEndpoint=%s;', $blobEndpoint), $resulSecond);
    }
}

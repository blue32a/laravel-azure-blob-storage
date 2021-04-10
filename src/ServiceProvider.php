<?php

namespace Blue32a\Laravel\Filesystem\AzureBlobStorage;

use Blue32a\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use League\Flysystem\Filesystem;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Storage::extend('azure-blob', function ($app, $config) {
            $adapter = $this->createAdapter($config);

            return new Filesystem($adapter);
        });
    }

    protected function createAdapter(array $config): AzureBlobStorageAdapter
    {
        $connectionStr = $this->createConnectionString($config);
        $client        = BlobRestProxy::createBlobService($connectionStr);

        $adapter = new AzureBlobStorageAdapter($client, $config['container']);
        $adapter->setPublicEndpoint($config['public_endpoint']);

        return $adapter;
    }

    protected function createConnectionString(array $config): string
    {
        $connectionStr = sprintf(
            'DefaultEndpointsProtocol=%s;AccountName=%s;AccountKey=%s;',
            $config['secure'] ? 'https' : 'http',
            $config['name'],
            $config['key']
        );

        if ($config['blob_endpoint']) {
            $connectionStr .= sprintf('BlobEndpoint=%s;', $config['blob_endpoint']);
        }

        return $connectionStr;
    }
}

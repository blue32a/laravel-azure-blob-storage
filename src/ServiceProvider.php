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
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Storage::extend('azure-blob', function ($app, $config) {
            $connection = sprintf(
                'DefaultEndpointsProtocol=%s;AccountName=%s;AccountKey=%s;',
                $config['secure'] ? 'https' : 'http',
                $config['name'],
                $config['key']
            );
            $client = BlobRestProxy::createBlobService($connection);

            return new Filesystem(
                new AzureBlobStorageAdapter($client, $config['container'])
            );
        });
    }
}

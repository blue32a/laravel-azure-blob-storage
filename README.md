# laravel-azure-blob-storage

![](https://github.com/blue32a/laravel-azure-blob-storage/workflows/Test/badge.svg)

## About

Use Azure Blob Storage as file storage for Laravel.

Flysystem Adapter: [blue32a/flysystem-azure-blob-storage](https://github.com/blue32a/flysystem-azure-blob-storage)

## Installation

```console
$ composer require blue32a/laravel-azure-blob-storage
```

## Usage

Configure your disk in `config/filesystems.php`.

The driver is `azure-blob`.

```php
    'disks' => [

        'azure-blob' => [
            'driver' => 'azure-blob',
            'secure' => true,
            'name' => env('AZULE_STORAGE_NAME'),
            'key' => env('AZULE_STORAGE_KEY'),
            'container' => 'example',
        ],

    ],
```

You can use `url()`. Requires public read access to the Blob.

```php
Storage::disk('azure-blob')->url($path);
```

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        /*
         * Cloudflare R2 — S3-compatible, dos buckets separados a propósito:
         * `r2_private` (backups, archivos privados) y `r2_public` (archivos
         * servibles públicamente). Credenciales independientes por bucket
         * (principio de menor privilegio: un token de R2 puede limitarse a
         * un solo bucket). No se define `visibility` aquí: R2 no maneja ACLs
         * por objeto como S3 — lo público/privado se controla a nivel de
         * bucket desde el dashboard de Cloudflare, no por archivo.
         */
        'r2_private' => [
            'driver' => 's3',
            'key' => env('R2_PRIVATE_ACCESS_KEY_ID'),
            'secret' => env('R2_PRIVATE_SECRET_ACCESS_KEY'),
            'region' => env('R2_DEFAULT_REGION', 'auto'),
            'bucket' => env('R2_PRIVATE_BUCKET'),
            'endpoint' => env('R2_PRIVATE_ENDPOINT'),
            'use_path_style_endpoint' => true,
            'throw' => false,
            'report' => false,
        ],

        'r2_public' => [
            'driver' => 's3',
            'key' => env('R2_PUBLIC_ACCESS_KEY_ID'),
            'secret' => env('R2_PUBLIC_SECRET_ACCESS_KEY'),
            'region' => env('R2_DEFAULT_REGION', 'auto'),
            'bucket' => env('R2_PUBLIC_BUCKET'),
            // Dominio público del bucket (r2.dev o un dominio propio conectado
            // en Cloudflare) — necesario para construir URLs servibles con
            // Storage::disk('r2_public')->url($path).
            'url' => env('R2_PUBLIC_URL'),
            'endpoint' => env('R2_PUBLIC_ENDPOINT'),
            'use_path_style_endpoint' => true,
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];

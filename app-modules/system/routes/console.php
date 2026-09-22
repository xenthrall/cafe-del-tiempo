<?php

use Illuminate\Support\Facades\Schedule;

// Solo la base de datos (--only-db): el código ya está en git, y un
// backup de archivos incluiría el .env con credenciales/APP_KEY.
Schedule::command('backup:run --only-db')
    ->daily()
    ->at('02:00')
    ->onOneServer();

Schedule::command('backup:clean')
    ->daily()
    ->at('01:30')
    ->onOneServer();

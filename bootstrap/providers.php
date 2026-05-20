<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\FortifyServiceProvider::class,
    // App\Providers\TelescopeServiceProvider lo registra AppServiceProvider
    // condicionalmente (solo local + paquete instalado) para que producción
    // (composer install --no-dev) no rompa.
];

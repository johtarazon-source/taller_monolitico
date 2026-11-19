<?php

/**
 * Carga automatica de clases, sin Composer.
 *
 * Convencion: el namespace "app\carpeta\subcarpeta" es la ruta de carpetas y el nombre de la
 * clase en CamelCase es el archivo en snake_case. Ejemplo:
 *   app\controllers\vehiculos\VehiculosController -> controllers/vehiculos/vehiculos_controller.php
 */

// Las excepciones comparten un solo archivo, asi que se cargan de una vez.
require_once __DIR__ . '/controllers/exceptions/exceptions.php';

spl_autoload_register(function (string $clase): void {
    $prefijo = 'app\\';

    if (strncmp($clase, $prefijo, strlen($prefijo)) !== 0) {
        return;
    }

    $partes = explode('\\', substr($clase, strlen($prefijo)));
    $nombre_clase = array_pop($partes);
    $archivo = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $nombre_clase));
    $ruta = __DIR__ . '/' . implode('/', $partes) . '/' . $archivo . '.php';

    if (is_file($ruta)) {
        require $ruta;
    }
});

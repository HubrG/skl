<?php
ini_set("extension", "php_mbstring.dll");
ini_set("extension", "php_exif.dll");

use App\Kernel;

require_once dirname(__DIR__) . '/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};

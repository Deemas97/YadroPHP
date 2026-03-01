<?php
define('YADRO_PHP__ROOT_DIR',      realpath(__DIR__ . '/../')) ?: exit('[Fatal error]: Incorrect root dir');

define('YADRO_PHP__SRC_DIR',       YADRO_PHP__ROOT_DIR . '/src');
define('YADRO_PHP__CONFIGS_DIR',   YADRO_PHP__ROOT_DIR . '/configs');
define('YADRO_PHP__TEMPLATES_DIR', YADRO_PHP__ROOT_DIR . '/templates');

define('YADRO_PHP__LOGS_DIR',      YADRO_PHP__ROOT_DIR . '/var/log');
define('YADRO_PHP__CACHE_DIR',     YADRO_PHP__ROOT_DIR . '/var/cache');

define('YADRO_PHP__ENV_FILE',      YADRO_PHP__ROOT_DIR . '/.env');
define('YADRO_PHP__KERNEL_FILE',   YADRO_PHP__SRC_DIR  . '/Kernel.php');

define('YADRO_PHP__ASSETS_DIR',    '/assets');
define('YADRO_PHP__STORAGE_DIR',   '/storage');

$autoloader = require (YADRO_PHP__SRC_DIR . '/Bootstrap/Autoloader.php');

use YadroPHP\Kernel;

return (new Kernel($autoloader))->handle();
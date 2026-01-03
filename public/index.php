<?php
define('YADRO_PHP__ROOT_DIR', realpath(__DIR__ . '/../'));

$autoloader = require (YADRO_PHP__ROOT_DIR . '/src/Bootstrap/Autoloader.php');

use YadroPHP\Kernel;

return $response = (new Kernel())->handle();

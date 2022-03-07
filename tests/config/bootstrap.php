<?php

/**
 * @author Junior Grossi <juniorgro@gmail.com>
 */

use Corcel\Database;

require __DIR__.'/../../vendor/autoload.php';

$capsule = Database::connect($params = [
    'database' => 'corcel_acf',
    'username' => 'corcel_acf',
    'password' => 'corcel_acf',
    'host' => '127.0.0.1',
]);

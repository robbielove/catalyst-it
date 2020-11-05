<?php
/**
 * User: robbielove
 * Date: 5/11/20
 * Time: 10:03 pm
 */
echo "Hello";
require __DIR__.'/vendor/autoload.php';
//require __DIR__.'/bootstrap/app.php';
$app = require_once __DIR__.'/bootstrap/app.php';
dd($app);

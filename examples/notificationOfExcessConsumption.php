<?php

require_once '../vendor/autoload.php';
require_once './config.php';

$iberdrola = new ZoiloMora\Iberdrola\Iberdrola($email, $password);
$iberdrola->selectContract($contract);

$reading = $iberdrola->getReading();
$floatValue = floatval($reading->valMagnitud);

if (($floatValue/1000) >= $limit) {
    // Send notification
    echo "Excess consumption reached!" . PHP_EOL;
}

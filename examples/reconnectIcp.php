<?php

require_once '../vendor/autoload.php';
require_once './config.php';

$iberdrola = new ZoiloMora\Iberdrola\Iberdrola($email, $password);
$iberdrola->selectContract($contract);

if ($iberdrola->getIcpStatus() === false) {
    $iberdrola->reconnectIcp();
}

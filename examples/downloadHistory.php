<?php

require_once '../vendor/autoload.php';
require_once './config.php';

$iberdrola = new ZoiloMora\Iberdrola\Iberdrola($email, $password);
$iberdrola->selectContract($contract);

$limits = $iberdrola->getLimitsConsumptionDates();
$current = $limits['max'];

do {
    $readings = $iberdrola->getReadingsOfTheDay($current);

    // Use of readings
    var_dump($readings);

    try {
        $interval = new \DateInterval('P1D');
        $current->sub($interval);
    } catch (\Exception $ex) {
        return false;
    }

    sleep(5);
} while ($current !== $limits['min']);

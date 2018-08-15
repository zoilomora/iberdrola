# Iberdrola

This library aims to help make integrations with the *Iberdrola API*.

## Installation
1) Install via composer
```
composer require zoilomora/iberdrola
```

## Example
```php
<?php
use ZoiloMora\Iberdrola\Iberdrola;

$limit = 3.4; // Hired potency (kW)

$iberdrola = new Iberdrola('test@test.com', '123456');
$reading = $iberdrola->getReading();
$floatValue = floatval($reading->valMagnitud);

if (($floatValue/1000) >= $limit) {
    // Send notification
}
```

## License
Licensed under the [MIT license](http://opensource.org/licenses/MIT)

Read [LICENSE](LICENSE) for more information

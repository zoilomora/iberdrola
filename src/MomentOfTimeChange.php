<?php

namespace ZoiloMora\Iberdrola;

/**
 * Class MomentOfTimeChange
 *
 * @category Model
 * @package  ZoiloMora\Iberdrola
 * @author   Zoilo Mora <zoilo.mora@hotmail.com>
 * @license  https://opensource.org/licenses/MIT The MIT License
 * @link     https://github.com/zoilomora/iberdrola/blob/master/src/MomentOfTimeChange.php
 */
class MomentOfTimeChange
{
    private $dateTime;
    private $dateTimeZone;

    public function __construct(
        \DateTime $dateTime,
        \DateTimeZone $dateTimeZone
    ) {
        $this->dateTime = $dateTime;
        $this->dateTimeZone = $dateTimeZone;
    }

    public function dateTime(): \DateTime
    {
        return $this->dateTime;
    }

    public function dateTimeZone(): \DateTimeZone
    {
        return $this->dateTimeZone;
    }
}

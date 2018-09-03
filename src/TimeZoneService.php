<?php

namespace ZoiloMora\Iberdrola;

/**
 * Class TimeZoneService
 *
 * @category Model
 * @package  ZoiloMora\Iberdrola
 * @author   Zoilo Mora <zoilo.mora@hotmail.com>
 * @license  https://opensource.org/licenses/MIT The MIT License
 * @link     https://github.com/zoilomora/iberdrola/blob/master/src/TimeZoneService.php
 */
class TimeZoneService
{
    private $daysOfDst;

    public function __construct()
    {
        $this->daysOfDst = [];
    }

    /**
     * Assign the correct time zone to the day
     * @param \DateTime $dateTime
     * @return \DateTime
     * @throws \Exception
     */
    public function assignTimeZone(\DateTime $dateTime): \DateTime
    {
        $months = $this->getMonthsOfDst($dateTime->format('Y'));

        if ($dateTime->getTimestamp() < $months['summer']->dateTime()->getTimestamp() ||
            $dateTime->getTimestamp() > $months['winter']->dateTime()->getTimestamp()
        ) {
            return $this->generateDateTimeWithTimeZone($dateTime, $months['winter']);
        }

        return $this->generateDateTimeWithTimeZone($dateTime, $months['summer']);
    }

    /**
     * Generates a datetime with the given time zone
     * @param \DateTime $dateTime
     * @param MomentOfTimeChange $momentOfTimeChange
     * @return bool|\DateTime
     */
    private function generateDateTimeWithTimeZone(
        \DateTime $dateTime,
        MomentOfTimeChange $momentOfTimeChange
    ): \DateTime {
        return \DateTime::createFromFormat(
            'Y-m-d H:i:s',
            $dateTime->format('Y-m-d') . ' 00:00:00',
            $momentOfTimeChange->dateTimeZone()
        );
    }

    /**
     * Change time zone
     * @param \DateTime $date
     * @return \DateTime
     * @throws \Exception
     */
    public function changeTimeZone(\DateTime $date): \DateTime
    {
        $months = $this->getMonthsOfDst($date->format('Y'));
        foreach ($months as $month) {
            if ($month->dateTime()->format('Y-m-d H') !== $date->format('Y-m-d H')) {
                continue;
            }

            $date->setTimezone($month->dateTimeZone());
        }
        return $date;
    }

    /**
     * Get the months when the time is changed
     * @param int $year
     * @return MomentOfTimeChange[]
     * @throws \Exception
     */
    public function getMonthsOfDst(int $year): array
    {
        if (array_key_exists($year, $this->daysOfDst)) {
            return $this->daysOfDst[$year];
        }

        $this->daysOfDst[$year] = [
            'summer' => new MomentOfTimeChange(
                new \DateTime("last sunday of March {$year} 2:00"),
                new \DateTimeZone('Etc/GMT-2')
            ),
            'winter' => new MomentOfTimeChange(
                new \DateTime("last sunday of October {$year} 3:00"),
                new \DateTimeZone('Etc/GMT-1')
            )
        ];

        return $this->daysOfDst[$year];
    }
}

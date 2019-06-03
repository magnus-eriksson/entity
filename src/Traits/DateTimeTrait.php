<?php namespace Maer\Entity\Traits;

use DateTime;

trait DateTimeTrait
{
    /**
     * Get a date/timestamp from a property and return it
     * as a formatted date string
     *
     * @param  string $propertyName
     * @param  string $format       Defaults to: "F j, Y"
     * @param  string $tz           Defaults to the scripts timezone
     *
     * @return string
     */
    public function date(string $propertyName, string $format = "F j, Y", string $tz = null) : string
    {
        $date = $this->dateTime($propertyName);

        return $date->format($format);
    }


    /**
     * Get a date string/timestamp from a property and return it
     * as a DateTime object
     *
     * @param  string $propertyName
     * @param  string $tz           Defaults to the scripts timezone
     *
     * @return \DateTime
     */
    public function dateTime(string $propertyName, string $tz = null) : \DateTime
    {
        $value = $this->{$propertyName};
        $tz    = $tz ? new DateTimeZone($tz) : null;

        if (is_numeric($value)) {
            // It's numeric, assume it's a timestamp
            $dateTime = new DateTime();
            if ($tz) {
                $dateTime->setTimezone($tz);
            }

            $dateTime->setTimestamp($value);

            return $dateTime;
        }

        return new DateTime($value, $tz);
    }


    /**
     * Get a date as timestamp
     *
     * @param  string $propertyName
     *
     * @return int
     */
    public function timestamp(string $propertyName, string $tz = null) : int
    {
        $date = $this->dateTime($propertyName, $tz);

        return $date->getTimestamp();
    }
}

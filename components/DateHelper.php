<?php
/**
 * Created: Ákos Kiszely
 * Date: 2014.12.02.
 * Time: 11:38
 */

namespace app\components;


class DateHelper
{
    private static $MONTH_NAMES = ['Január', 'Február', 'Március', 'Április', 'Május', 'Június', 'Július',
        'Augusztus', 'Szeptember', 'Október', 'November', 'December'];

    public static function getMonthName($i)
    {
        return self::$MONTH_NAMES[$i - 1];
    }
} 
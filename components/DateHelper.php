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

    private static $DAY_NAMES = ['Vasárnap','Hétfő', 'Kedd', 'Szerda', 'Csütörtök', 'Péntek', 'Szombat'];

    public static function getMonthName($i)
    {
        return self::$MONTH_NAMES[$i - 1];
    }

    public static function getDayWithDayName($date) {
        $dw = date( "w", strtotime($date));

        $txtNum = substr($date, -2);
        return $txtNum.". ".self::$DAY_NAMES[intval($dw)%7];
    }

    public static function isWeekEnd($date) {
        $dw = date( "w", strtotime($date));
        return $dw==0 || $dw==6;
    }
} 
<?php

use Carbon\Carbon;



/**
 * find vechicle age
 */
if (!function_exists('getVehicleAge')) {
    function getVehicleAge($year)
    {
        $years = Carbon::parse("01-01-" . $year);
        $diffYears = Carbon::now()->diffInYears($years);

        return $diffYears;
    }
}


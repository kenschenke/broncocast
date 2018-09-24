<?php

namespace App\Model;

class TimezonesModel
{
    public function GetTimezones()
    {
        try {
            $timezones = \DateTimeZone::listIdentifiers();
            return ['Success' => true, 'Timezones' => $timezones];
        } catch (\Exception $e) {
            return ['Success' => false, 'Error' => $e->getMessage()];
        }
    }
}

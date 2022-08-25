<?php

namespace App\Services;

use App\Models\UsersSchedule;
use Carbon\Carbon;

class MeetingService
{

    // general slot format
    protected string $format = 'Y-m-d H:i:s';

    /**
     * @param $workStartsAt
     * @param $workEndsAt
     * @param Carbon $earliest
     * @param Carbon $latest
     * @param int $minutes
     * @return array
     */
    public function generateNeededSlots(
        int    $workStartsAt,
        int    $workEndsAt,
        Carbon $earliest,
        Carbon $latest,
        int    $minutes = 30
    ): array
    {
        // for results return
        $slots = [];

        // check if the earliest hour is less than the day work start
        if ($earliest->hour < $workStartsAt) {
            $earliest->hour = $workStartsAt; // set the start hour
            $earliest->minute = 0;  // set the beginning of the hour
        }

        // casting the earliest minutes field to the beginning or the half of the hour
        if ($earliest->minute > 0 && $earliest->minute <= 30) // more than 0 and less than 30
        {
            // move to the next half of the hour
            $earliest->minute = 30;

            // the needed time after the half of the hour
        } elseif ($earliest->minute > 30 && $earliest->minute <= 59) // more than 30 and less than 59
        {
            //move to next hour
            $earliest->addHour();
            // set minutes field to 0
            $earliest->minute = 0;
        }

        // set initial value
        $current = \Carbon\Carbon::create($earliest);

        //retrieve the time until reach the latest
        while ($latest > $current) {
            // set the end of working day
            // using the current because it will be updated through the changes
            $last_day_slot = \Carbon\Carbon::create($current);
            $last_day_slot->hour = $workEndsAt; // work end hour
            $last_day_slot->minute = 0; // assuming that work from 8-17 so no need for minutes

            // change last time the meeting can be done before the work day end
            $last_day_slot->subMinutes($minutes);

            // $needed is flag to be checked to break the daily loop
            $needed = \Carbon\Carbon::create($latest)->subMinutes($minutes);

            // loop through one day
            while ($last_day_slot >= $current && $current <= $needed) {
                // generate slot string
                $slots [] = \Carbon\Carbon::create($current)->format($this->format);

                // move the current to 30 minutes slot, the default slots period of the system
                $current->addMinutes(30);
            }

            // move to new day
            $current->addDay();

            // set the work starts at hour
            $current->hour = $workStartsAt;
            $current->minute = 0; // 0 the beginning of the hour
        }

        // list of slots as strings
        return $slots;
    }

    /**
     * @param string $slot
     * @param array $ids
     * @param int $needed_minutes
     * @return bool
     */
    public function validateSlot(
        string $slot,
        array  $ids,
        int    $needed_minutes
    ): bool
    {
        // create between time array for the sql query
        $slot_dates_array = [
            // slot start at
            Carbon::parse($slot, 'UTC'),

            // slot end at
            //add the needed period sub 1 minute to reach 59 format or 29 minute in the database slots
            Carbon::parse($slot, 'UTC')->addMinutes($needed_minutes - 1)
        ];

        // check if there is no reserved slots related with in the needed period
        return !UsersSchedule::query()
            ->whereIn('user_id', $ids)
            ->whereBetween('booked_schedule_slot', $slot_dates_array)
            ->exists();
    }

    /** this function when the logged-in user with another time zone
     * @param string $slot
     * @param string $tz
     * @return string
     *
     */
    public function convertSlotDateTime(
        string $slot, // string of the needed slot to be converted
        string $tz = 'UTC' // the needed time zone to convert the slot date to
    ): string
    {
        // convert and format
        return Carbon::parse($slot, 'UTC')->tz($tz)->format($this->format);
    }
}

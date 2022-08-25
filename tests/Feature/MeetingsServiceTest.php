<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UsersSchedule;
use App\Services\MeetingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeetingsServiceTest extends TestCase
{
    use RefreshDatabase;

    private MeetingService $meetingService;

    /** constract and set service object
     * @param string|null $name
     * @param array $data
     * @param $dataName
     */
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        $this->meetingService = new MeetingService();
        parent::__construct($name, $data, $dataName);
    }

    /** test generated slots
     * when good time selected
     * @return void
     */
    public function test_generate_needed_slots_needed_time_in_between_work_time(): void
    {
        $start = 8;
        $end = 17;
        $earliest = Carbon::parse('2022-12-12 10:00:00');
        $latest = Carbon::parse('2022-12-12 11:00:00');
        $minutes = 60;

        $slots = $this->meetingService->generateNeededSlots($start, $end, $earliest, $latest, $minutes);
        $this->assertEquals(count($slots), 1);

        $minutes = 30;
        $slots = $this->meetingService->generateNeededSlots($start, $end, $earliest, $latest, $minutes);
        $this->assertEquals(count($slots), 2);
    }

    /**
     * generated slots before work time
     * @return void
     */
    public function test_generate_needed_slots_needed_time_before_between_work_time(): void
    {
        $start = 8;
        $end = 17;
        $earliest = Carbon::parse('2022-12-12 7:00:00');
        $latest = Carbon::parse('2022-12-12 8:00:00');
        $minutes = 60;

        $slots = $this->meetingService->generateNeededSlots($start, $end, $earliest, $latest, $minutes);

        $this->assertEquals(count($slots), 0);
    }

    /** generated slots after work time
     * @return void
     */
    public function test_generate_needed_slots_needed_time_after_between_work_time(): void
    {
        $start = 8;
        $end = 17;
        $earliest = Carbon::parse('2022-12-12 7:00:00');
        $latest = Carbon::parse('2022-12-12 8:00:00');
        $minutes = 60;

        $slots = $this->meetingService->generateNeededSlots($start, $end, $earliest, $latest, $minutes);

        $this->assertEquals(count($slots), 0);
    }

    /** the needed time not suitable with the required dates
     * @return void
     */
    public function test_generate_needed_slots_needed_time_more_than_the_available_time(): void
    {
        $start = 8;
        $end = 17;
        $earliest = Carbon::parse('2022-12-12 16:30:00');
        $latest = Carbon::parse('2022-12-12 18:00:00');
        $minutes = 60;

        $slots = $this->meetingService->generateNeededSlots($start, $end, $earliest, $latest, $minutes);

        $this->assertEquals(0, count($slots));
    }

    /** check for users
     * @return void
     */
    public function test_validate_specific_slot_in_the_same_time_create_for_user(): void
    {
        $user = User::factory()->create();

        $slot_time = Carbon::now();

        UsersSchedule::factory()->create(
            [
                'user_id' => $user->id,
                'booked_schedule_slot' => $slot_time,
            ]
        );

        $need_period = 30;

        $this->assertFalse($this->meetingService->validateSlot($slot_time->format('Y-m-d H:i:s'), [$user->id], $need_period));
    }

    /**  no enough time
     * @return void
     */
    public function test_validate_specific_slot_before_time_created_but_no_time_enough_between_them(): void
    {
        $user = User::factory()->create();

        $slot_time = Carbon::now();

        UsersSchedule::factory()->create(
            [
                'user_id' => $user->id,
                'booked_schedule_slot' => $slot_time,
            ]
        );

        $need_period = 120;

        // the slot will be before the created one by 60 minutes but the needed is 120
        $check = $slot_time->subHour()->format('Y-m-d H:i:s');
        $this->assertFalse($this->meetingService->validateSlot($check, [$user->id], $need_period));
    }

    /** enough time to validate slot
     * @return void
     */
    public function test_validate_specific_slot_before_time_created_suitable_time_enough_between_them(): void
    {
        $user = User::factory()->create();

        $slot_time = Carbon::now();

        UsersSchedule::factory()->create(
            [
                'user_id' => $user->id,
                'booked_schedule_slot' => $slot_time,
            ]
        );

        $need_period = 60;

        // the slot will be before the created one by 60 minutes but the needed is 60
        $check = $slot_time->subHour()->format('Y-m-d H:i:s');
        $this->assertTrue($this->meetingService->validateSlot($check, [$user->id], $need_period));

        $need_period = 30;

        // the slot will be before the created one by 60 minutes but the needed is 60
        $this->assertTrue($this->meetingService->validateSlot($check, [$user->id], $need_period));
    }

    /** test converted time zone value
     * @return void
     */
    public function test_convert_slot_date_time_to_time_zone(): void
    {
        $slot_time = '2022-12-20 11:00:00';
        $expect = '2022-12-20 13:00:00'; // for cairo

        $ac = $this->meetingService->convertSlotDateTime($slot_time, 'Africa/Cairo');
        $this->assertEquals($expect, $ac);

    }

    /** the same time zone
     * @return void
     */
    public function test_convert_slot_date_time_to_default_time_zone_utc(): void
    {
        $slot_time = '2022-12-20 11:00:00';

        // default the same
        $ac = $this->meetingService->convertSlotDateTime($slot_time);
        $this->assertEquals($slot_time, $ac);

    }
}

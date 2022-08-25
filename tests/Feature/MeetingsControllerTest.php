<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UsersSchedule;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeetingsControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * assert inputs names exits
     * @return void
     */
    public function test_index_view(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        // form assertion
        $response->assertSee('length');
        $response->assertSee('ids');
        $response->assertSee('earliest');
        $response->assertSee('latest');
        $response->assertSee('startAt');
        $response->assertSee('endAt');
    }

    /** assert that all users in db represented
     * @return void
     */
    public function test_all_users_viewed(): void
    {
        $users = User::factory(100)->create();

        $response = $this->get('/');

        $response->assertStatus(200);

        // check ids
        $response->assertSee($users->pluck(['id'])->toArray());

        //check names
        $response->assertSee($users->pluck(['name'])->toArray());

    }

    /** users ids can not be empty
     * @return void
     */
    public function test_error_empty_ids(): void
    {
        $request_date = [
            "ids" => [],
            "length" => 60,
            "earliest" => Carbon::parse('2022-08-25 10:00:00'),
            "latest" => Carbon::parse('2022-08-25 12:00:00'),
            "startAt" => 8,
            "endAt" => 17,
        ];

        $response = $this->post('results', $request_date);

        $response->assertSessionHasErrors('ids');
    }

    /** users ids required
     * @return void
     */
    public function test_error_required_ids(): void
    {
        $request_date = [
            "length" => 60,
            "earliest" => Carbon::parse('2022-08-25 10:00:00'),
            "latest" => Carbon::parse('2022-08-25 12:00:00'),
            "startAt" => 8,
            "endAt" => 17,
        ];

        $response = $this->post('results', $request_date);

        $response->assertSessionHasErrors('ids');
    }

    /** meeting period must be sent
     * @return void
     */
    public function test_error_required_length(): void
    {
        $user = User::factory()->create();

        $request_date = [
            "ids" => [$user->id],
            "earliest" => Carbon::parse('2022-08-25 10:00:00'),
            "latest" => Carbon::parse('2022-08-25 12:00:00'),
            "startAt" => 8,
            "endAt" => 17,
        ];

        $response = $this->post('results', $request_date);

        $response->assertSessionHasErrors('length');
    }

    /** meeting period can not be 0
     * @return void
     */
    public function test_error_min_length(): void
    {
        $user = User::factory()->create();

        $request_date = [
            "ids" => [$user->id],
            'length' => 0,
            "earliest" => Carbon::parse('2022-08-25 10:00:00'),
            "latest" => Carbon::parse('2022-08-25 12:00:00'),
            "startAt" => 8,
            "endAt" => 17,
        ];

        $response = $this->post('results', $request_date);

        $response->assertSessionHasErrors('length');
    }

    /** not dates sent
     * @return void
     */
    public function test_error_earliest_latest_required(): void
    {
        $user = User::factory()->create();

        $request_date = [
            "ids" => [$user->id],
            'length' => 0,
            "startAt" => 8,
            "endAt" => 17,
        ];

        $response = $this->post('results', $request_date);
        $response->assertSessionHasErrors('earliest');
        $response->assertSessionHasErrors('latest');
    }

    /** time of earliest is after time of the latest and vise versa
     * @return void
     */
    public function test_error_earliest_latest(): void
    {
        $user = User::factory()->create();

        $request_date = [
            "ids" => [$user->id],
            'length' => 0,
            "earliest" => Carbon::parse('2022-08-25 10:00:00'),
            "latest" => Carbon::parse('2022-08-25 09:00:00'),
            "startAt" => 8,
            "endAt" => 17,
        ];

        $response = $this->post('results', $request_date);

        $response->assertSessionHasErrors('earliest');
        $response->assertSessionHasErrors('latest');
    }

    /** not earliest or latest time
     * @return void
     */
    public function test_error_start_at_end_at(): void
    {
        $user = User::factory()->create();

        $request_date = [
            "ids" => [$user->id],
            'length' => 0,
            "earliest" => Carbon::parse('2022-08-25 10:00:00'),
            "latest" => Carbon::parse('2022-08-25 09:00:00'),
            "startAt" => 18,
            "endAt" => 8,
        ];

        $response = $this->post('results', $request_date);

        $response->assertSessionHasErrors('startAt');
        $response->assertSessionHasErrors('endAt');
    }

    /** no work time sent
     * @return void
     */
    public function test_error_start_at_end_at_required(): void
    {
        $user = User::factory()->create();

        $request_date = [
            "ids" => [$user->id],
            'length' => 0,
            "earliest" => Carbon::parse('2022-08-25 10:00:00'),
            "latest" => Carbon::parse('2022-08-25 09:00:00'),
        ];

        $response = $this->post('results', $request_date);

        $response->assertSessionHasErrors('startAt');

        $response->assertSessionHasErrors('endAt');
    }

    /** validate empty date request
     * @return void
     */
    public function test_errors(): void
    {
        $user = User::factory()->create();

        $request_date = [];

        $response = $this->post('results', $request_date);
        $response->assertSessionHasErrors('length');
        $response->assertSessionHasErrors('ids');
        $response->assertSessionHasErrors('earliest');
        $response->assertSessionHasErrors('latest');
        $response->assertSessionHasErrors('startAt');
        $response->assertSessionHasErrors('endAt');
    }

    /** the user has tight time
     * @return void
     */
    public function test_no_results_tight_time(): void
    {
        $user = User::factory()->create();

        UsersSchedule::factory()->create([
            'user_id' => $user->id,
            'booked_schedule_slot' => Carbon::parse('2022-08-25 10:00:00')
        ]);

        $request_date = [
            "length" => 60,
            "ids" => [$user->id],
            "earliest" => Carbon::parse('2022-08-25 10:00:00'),
            "latest" => Carbon::parse('2022-08-25 10:30:00'),
            "startAt" => 8,
            "endAt" => 17,
        ];

        $response = $this->post('results', $request_date);

        $response->assertStatus(200);
        $response->assertDontSee('button');
    }

    /**no time before work
     * @return void
     */
    public function test_no_results_time_needed_before_work_time(): void
    {
        $user = User::factory()->create();

        UsersSchedule::factory()->create([
            'user_id' => $user->id,
            'booked_schedule_slot' => Carbon::parse('2022-08-25 10:00:00')
        ]);

        $request_date = [
            "length" => 60,
            "ids" => [$user->id],
            "earliest" => Carbon::parse('2022-08-25 10:00:00'),
            "latest" => Carbon::parse('2022-08-25 10:30:00'),
            "startAt" => 17,
            "endAt" => 18,
        ];

        $response = $this->post('results', $request_date);

        $response->assertStatus(200);

        $response->assertDontSee('button');
    }

    /**no time after work time
     * @return void
     */
    public function test_no_results_time_needed_after_work_time(): void
    {
        $user = User::factory()->create();

        UsersSchedule::factory()->create([
            'user_id' => $user->id,
            'booked_schedule_slot' => Carbon::parse('2022-08-25 10:00:00')
        ]);

        $request_date = [
            "length" => 60,
            "ids" => [$user->id],
            "earliest" => Carbon::parse('2022-08-25 18:00:00'),
            "latest" => Carbon::parse('2022-08-25 20:30:00'),
            "startAt" => 8,
            "endAt" => 17,
        ];

        $response = $this->post('results', $request_date);

        $response->assertStatus(200);

        $response->assertDontSee('button');
    }

    /** find one slot for user
     * @return void
     */
    public function test_no_results_time_needed(): void
    {
        $user = User::factory()->create();

        UsersSchedule::factory()->create([
            'user_id' => $user->id,
            'booked_schedule_slot' => Carbon::parse('2022-08-25 10:00:00')
        ]);

        UsersSchedule::factory()->create([
            'user_id' => $user->id,
            'booked_schedule_slot' => Carbon::parse('2022-08-25 10:30:00')
        ]);

        $request_date = [
            "length" => 60,
            "ids" => [$user->id],
            "earliest" => Carbon::parse('2022-08-25 09:00:00'),
            "latest" => Carbon::parse('2022-08-25 11:30:00'),
            "startAt" => 8,
            "endAt" => 17,
        ];

        $response = $this->post('results', $request_date);

        $response->assertStatus(200);

        $response->assertSee('button');

        $dom = $response->getContent();

        $count = substr_count($dom, 'button');

        $this->assertSame(1, $count / 2);
    }

    /** check results if 30 minutes needed before a meeting
     * @return void
     */
    public function test_results_time_needed_30_minutes(): void
    {
        $user = User::factory()->create();

        UsersSchedule::factory()->create([
            'user_id' => $user->id,
            'booked_schedule_slot' => Carbon::parse('2022-08-25 10:00:00')
        ]);

        UsersSchedule::factory()->create([
            'user_id' => $user->id,
            'booked_schedule_slot' => Carbon::parse('2022-08-25 10:30:00')
        ]);

        $request_date = [
            "length" => 30,
            "ids" => [$user->id],
            "earliest" => Carbon::parse('2022-08-25 09:00:00'),
            "latest" => Carbon::parse('2022-08-25 11:30:00'),
            "startAt" => 8,
            "endAt" => 17,
        ];

        $response = $this->post('results', $request_date);

        $response->assertStatus(200);

        $response->assertSee('button');

        $dom = $response->getContent();

        $count = substr_count($dom, 'button');

        $this->assertSame(3, $count / 2);
    }


    /** no slots
     * @return void
     */
    public function test_no_results_time_needed_two_users(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        UsersSchedule::factory()->create([
            'user_id' => $user1->id,
            'booked_schedule_slot' => Carbon::parse('2022-08-25 10:00:00')
        ]);

        UsersSchedule::factory()->create([
            'user_id' => $user2->id,
            'booked_schedule_slot' => Carbon::parse('2022-08-25 10:30:00')
        ]);

        $request_date = [
            "length" => 60,
            "ids" => [$user1->id, $user2->id],
            "earliest" => Carbon::parse('2022-08-25 09:30:00'),
            "latest" => Carbon::parse('2022-08-25 11:30:00'),
            "startAt" => 8,
            "endAt" => 17,
        ];

        $response = $this->post('results', $request_date);

        $response->assertStatus(200);

        $response->assertDontSee('button');
    }

    /** can see one slot
     * @return void
     */
    public function test_one_results_time_needed_two_users(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        UsersSchedule::factory()->create([
            'user_id' => $user1->id,
            'booked_schedule_slot' => Carbon::parse('2022-08-25 10:00:00')
        ]);

        UsersSchedule::factory()->create([
            'user_id' => $user2->id,
            'booked_schedule_slot' => Carbon::parse('2022-08-25 10:30:00')
        ]);

        $request_date = [
            "length" => 30,
            "ids" => [$user1->id, $user2->id],
            "earliest" => Carbon::parse('2022-08-25 09:30:00'),
            "latest" => Carbon::parse('2022-08-25 11:00:00'),
            "startAt" => 8,
            "endAt" => 17,
        ];

        $response = $this->post('results', $request_date);

        $response->assertStatus(200);

        $response->assertSee('button');

        $dom = $response->getContent();

        $count = substr_count($dom, 'button');

        $this->assertSame(1, $count / 2);
    }

}

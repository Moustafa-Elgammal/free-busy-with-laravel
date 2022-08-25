<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetTimeSlotsAPIRequest;
use App\Http\Requests\GetTimeSlotsRequest;
use App\Models\User;
use App\Services\MeetingService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;

class MeetingsController extends Controller
{
    /**
     * @param MeetingService $meetingService
     */
    public function __construct(protected MeetingService $meetingService){}

    /** home page form
     * @return View
     */
    public function index(): View
    {
        $employees = User::query()->orderBy('name')->get();
        return view('welcome')->with('employees', $employees);
    }

    /** slots results
     * @param GetTimeSlotsRequest $request
     * @return View
     */
    public function getTimeSlots(GetTimeSlotsRequest $request): View
    {
        // init results response
        $result = [];

        // this line should set to convert the needed times when processing
        // and return the results converted from the system time zone to the user time zone
        $currentUserTZ = 'UTC';

        // the needed employees ids list
        $ids = $request->ids;

        // needed meeting can be after
        $needed_earliest = Carbon::parse($request->earliest, 'UTC');

        // needed meeting must be before
        $needed_latest = Carbon::parse($request->latest, 'UTC');

        //meeting needed period
        $period = $request->length;

        // work start hour
        $workStartsAt = (int) $request->startAt;

        //work end hour
        $workEndsAt = (int) $request->endAt;

        // generate slots of the needed period between earliest and latest
        $slots =  $this->meetingService->generateNeededSlots(
            $workStartsAt,
            $workEndsAt,
            $needed_earliest,
            $needed_latest,
            $period
        );

        // slots validation
        foreach ($slots as $slot)
        {
            // check availability of each slot compared with the slots in db
            if ($this->meetingService->validateSlot($slot, $ids, $period))
            {
                $result [] = $this->meetingService->convertSlotDateTime($slot, $currentUserTZ);
            }
        }

        // render results page
        return  view('results')->with('slots', $result);
    }



    /** get results using api end point
     * @param GetTimeSlotsRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTimeSlotsApi(GetTimeSlotsAPIRequest $request): \Illuminate\Http\JsonResponse
    {
        // init results response
        $result = [];

        // this line should set to convert the needed times when processing
        // and return the results converted from the system time zone to the user time zone
        $currentUserTZ = 'UTC';

        // the needed employees ids list
        $ids = $request->ids;

        // needed meeting can be after
        $needed_earliest = Carbon::parse($request->earliest, 'UTC');

        // needed meeting must be before
        $needed_latest = Carbon::parse($request->latest, 'UTC');

        //meeting needed period
        $period = $request->length;

        // work start hour
        $workStartsAt = (int) $request->startAt;

        //work end hour
        $workEndsAt = (int) $request->endAt;

        // generate slots of the needed period between earliest and latest
        $slots =  $this->meetingService->generateNeededSlots(
            $workStartsAt,
            $workEndsAt,
            $needed_earliest,
            $needed_latest,
            $period
        );

        // slots validation
        foreach ($slots as $slot)
        {
            // check availability of each slot compared with the slots in db
            if ($this->meetingService->validateSlot($slot, $ids, $period))
            {
                $result [] = $this->meetingService->convertSlotDateTime($slot, $currentUserTZ);
            }
        }

        // render results page
        return  response()->json(['data' => $result]);
    }
}

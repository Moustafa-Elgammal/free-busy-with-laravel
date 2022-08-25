<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UsersSchedule;
use Illuminate\Database\Seeder;

class UsersDataSeeds extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // reset the schedules table
        UsersSchedule::truncate();

        // init dates to contain the user scheduled slots
        $dates = [];

        try {
            // make a file as streamer as handler
            $file = new \SplFileObject(base_path('data/freebusy.txt'));

            // seek through the file line by line
            while (!$file->eof()) { // break at the end

                // read next line
                $line = $file->fgets();

                // the string line to array
                $lineArr = explode(';', $line);

                // this means that the line is name line
                if (count($lineArr) == 2) {
                    $id = $lineArr[0];
                    $name = trim($lineArr[1]);
                    // user not found
                    if (!User::find($id)) {
                        // create and save the user
                        $user = new User();
                        $user->id = $id;
                        $user->name = $name;

                        try {
                            // save the user date
                            $user->save();

                        } catch (\Exception $e) {
                            // error messages
                            logger('creating user ' . $e->getMessage());
                            echo('creating user ' . $e->getMessage() . "\n");
                        }
                    }

                    // line of dates reserved slots
                } else if (count($lineArr) == 4) {

                    $id = $lineArr[0]; // user id
                    $start = $lineArr[1]; // date of start
                    $end = $lineArr[2]; // date of end

                    // generate old scheduled slots
                    $schedules = $this->generateOldSlots($id, $start, $end);

                    //set or merge the generated old schedules
                    $dates [$lineArr[0]] = isset($dates[$lineArr[0]]) && is_array($dates [$lineArr[0]]) ? // user set index id
                        // merge
                        array_merge($schedules, $dates [$lineArr[0]]) : // else
                        // just set
                        $schedules;

                }
            }
            // file handler error exception
        } catch (\Exception  $e) {
            logger($e->getMessage());
            echo $e->getMessage() . "\n";
        }

        // get current users id once to be checked if user is existed
        $current_users_id_array = \App\Models\User::all()->pluck(['id'])->toArray();

        // insert reserved of slots
        foreach ($dates as $key => $slots) {

            // check if the slot user is already excited
            if (in_array($key, $current_users_id_array))
                // user slots
                UsersSchedule::insert($slots);

            else { // skip inserting
                // User not found to add his/her slots
                echo "$key not found\n";
                logger("$key not found");
            }
        }
    }

    /** get set of slots between two datetime
     * @param $uid
     * @param $start
     * @param $end
     * @return array
     */
    private function generateOldSlots($uid, $start, $end): array
    {
        // init slots data
        $slots = [];

        // start date object
        $startDate = \Carbon\Carbon::parse($start);

        // end  date object
        $endDate = \Carbon\Carbon::parse($end);


        // retrieve slots from time
        while ($endDate > $startDate) {

            // slot record data
            $slots [] = [
                'user_id' => $uid,
                'booked_schedule_slot' => \Carbon\Carbon::create($startDate),
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ];

            // move to new slot
            $startDate->addMinutes(30);
        }

        // slots list between start and end dates
        return $slots;
    }
}

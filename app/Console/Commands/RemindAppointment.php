<?php

namespace App\Console\Commands;

use App\Appointment;
use App\Jobs\SendAppointmentReminderJob;
use App\Notification;
use App\UserApp;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RemindAppointment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remind:appointment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remind Every User Before 15 Minute and one Day of The Appointment';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(Notification $notification)
    {
        try {
            /*START::SEND REMINDER 1 DAY BEFORE APPOINTMENT START*/
            $records = [];
            $oneDay = Appointment::with(['patient' => function ($obj) {
                $obj->select(['id', 'name', 'email']);
            }])->where('start_time', Carbon::now()->addDay()->setTimezone('Asia/Kolkata')->format('Y-m-d H:i'))->get();
            if ($oneDay->count() > 0) {
                $records['oneDay'] = $oneDay;
            }
            
            /*END::SEND REMINDER 1 DAY BEFORE APPOINTMENT START*/
            /*START::SEND REMINDER 15 FIFTEEN MINUTES BEFORE APPOINTMENT START*/
            $fifteenMinutes = Appointment::with(['patient' => function ($obj) {
                $obj->select(['id', 'name', 'email']);
            }])->where('start_time', Carbon::now()->addMinutes(15)->setTimezone('Asia/Kolkata')->format('Y-m-d H:i'))->get(); //'2020-10-14 09:30'
            if ($fifteenMinutes->count() > 0) {
                $records['fifteenMinutes'] = $fifteenMinutes;
            }
           
            /*END::SEND REMINDER FIFTEEN MINUTES BEFORE APPOINTMENT START*/
            if (!empty($records)) {
                foreach ($records as $key => $record) {
                    switch ($key) {
                        case "oneDay":
                            $reminder_before = "<strong>1</strong> Day";
                            $type = "oneDay";
                            break;
                        case "fifteenMinutes":
                            $reminder_before = "<strong>15</strong> Minutes";
                            $type = "fifteenMinutes";
                            break;
                    }
                    foreach ($record as $user) {
                       
                        $record = [
                            'subject' => 'NC Health Hub | Reminder For Appointment',
                            'recipient_name' => $user->patient_name,
                            'recipient_email' => $user->patient_email,
                            'reminder_before' => $reminder_before,
                            'type' => $type,

                        ];
                        
                        dispatch(new SendAppointmentReminderJob($record));

                        /* start notification*/
                        //send notification to app 
                        $androidToken = UserApp::where('user_id', $user->patient_id)->where('device_type', 'Android')->pluck('token')->toArray();
                        if (!empty($androidToken)) {
                            $subject = 'NC Health Hub | Reminder For Appointment';
                            $extra = ['id' => $user->patient_id, 'type' => 'appointment_remainder'];
                            $sms_push_text = 'Reminder For Appointment';
                            $notification->sendPushNotification("android", $androidToken, $subject, $sms_push_text, $extra);
                        }
                        /* end notification */
                    }
                }
            }

            $this->info('Completed.');
            \Log::info("Cron is working fineeee!");
        } catch (Exception $e) {
            $this->info('Getting Error');
        }
    }
}

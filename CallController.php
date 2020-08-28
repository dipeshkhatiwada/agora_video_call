<?php

namespace App\Http\Controllers\employee;

use App\CounsellingAreas;
use App\CounsellingBooking;
use App\CounsellingCategory;
use App\CounsellingFees;
use App\CounsellingMedium;
use App\CounsellingMeeting;
use App\CounsellingShift;
use App\CounsellorLeave;
use App\EducationLevel;
use App\Employee;
use App\EmployeeCounselling;
use App\EmployeeEducation;
use App\Employees;
use App\Employers;
use App\EnrollEmployerStream;
use App\Faculty;
use App\Imagetool;
use App\JobApply;
use App\JobCategory;
use App\JobEducation;
use App\JobLocation;
use App\Jobs;
use App\library\myFunctions;
use App\library\Settings;
use App\OrganizationType;
use App\Project;
use App\ProjectApply;
use App\Saluation;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Rchalange;
use App\RchalangeQuestion;
use App\RchalangeParticipation;
use Carbon\Carbon;
use App\TestCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Pusher\Pusher;
use Validator;

class CallController extends Controller
{
    public function __construct()
    {
        $this->middleware('employee');
    }
    public function onlineSession(Request $request,$id){
        $data['counselling_booking'] = CounsellingBooking::find($id);
        $data['channel']=$data['counselling_booking']->session_date.''.date('Y-m-d_H:i_',strtotime($data['counselling_booking']->session_start_time)).''.$data['counselling_booking']->counselling_id;
        return view('employee.counselling.video_session', compact('data'));

    }

    public function liveStreaming(Request $request){
        $data['counselling_booking'] = CounsellingBooking::find($request->input('counselling_booking'));

//        dd($data);

        $stream_data = EnrollEmployerStream::where('channel', $request['channel'])->first();
        $employee = auth()->guard('employee')->user();
//        $data = EnrollReservation::where('seo_url', $request['channel'])->first();
        $employer_id = $data['counselling_booking']->counsellor_id;
        $reservation_id = $data['counselling_booking']->id;
        $camera_profile = '720p_6';
        $message = '';
        $viewer_count = '';
        $active_user = [];
        $counter = '';
        $html='';

        if($stream_data != '' && $stream_data->channel == $request['channel']){
//if entry channel is equal to database only update employee_id and count
            $array_data = json_decode($stream_data->employee_id);
            $active_user = json_decode($stream_data->active_user);
            if(in_array($employee->id, $array_data)) {
                if(!in_array($employee->id, $active_user)){
                    $counter = $stream_data->counter + 1;
                    array_push($active_user, $employee->id);
                    EnrollEmployerStream::where('channel', $request['channel'])->update([
                        'counter' => $stream_data->counter + 1,
                        'active_user' => $active_user,
                    ]);
                }else{
                    $counter = $stream_data->counter;
                }
                $message = 'old_user';

            }else{
                array_push($array_data, $employee->id);
                array_push($active_user, $employee->id);
                $viewer_count = $stream_data->total_count + 1;
                $counter = $stream_data->counter + 1;
                EnrollEmployerStream::where('channel', $request['channel'])->update([
                    'employee_id' => json_encode($array_data),
                    'active_user' => json_encode($active_user),
                    'total_count' => $viewer_count,
                    'counter' => $counter,
                ]);
                $message = 'new_user';


            }
        }else{
            $viewer_count = 1;
            $counter = 1;

            $new_stream = EnrollEmployerStream::create([
                'employee_id' =>  json_encode([$employee->id]),
                'active_user' =>  json_encode([$employee->id]),
                'employer_id' =>  $employer_id,
                'reservation_id' => $reservation_id,
                'channel' => $request['channel'],
                'camera_profile'=> $camera_profile,
                'total_count' => $viewer_count,
                'counter' => $counter,
                'start_time' => now(),
            ]);
            $message = 'new_user';
            $array_data = json_decode($new_stream->active_user);
        }

//   pusher
        $options = array(
            'cluster' => 'ap2',
            'useTLS' => true
        );
        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            $options
        );
//        dd($pusher);
        $html = '';
        foreach ($active_user as $user){
            $html .= '<li class="list-group-item" id="user_viewer_'.$user.'">'.Employees::getName($user).'</li>';
        }
//        dd($html);
        $temp = ['user_id' => $employee->id, 'viewer_count' => $viewer_count, 'counter' => $counter, 'html' => $html, 'type' => 'joinstream', 'message' => $message];
        $pusher->trigger('my-audience', 'my-broadcast', $temp);
//        $pusher->trigger('my-channel', 'my-event', $temp);
        return $message;


    }
    public function endliveStreaming(Request $request){
        $data['counselling_booking'] = CounsellingBooking::find($request->input('counselling_booking'));

        $user = auth()->guard('employee')->user();

        $enroll_stream = EnrollEmployerStream::where('channel', $request['channel'])->first();
        if($enroll_stream->counter > 1)
        {
            $counter = $enroll_stream->counter - 1 ;
//            dd(json_encode(array_diff(json_decode($enroll_stream->active_user),[$user->id])));
            $enroll_stream->active_user = json_encode(array_diff(json_decode($enroll_stream->active_user),[$user->id]));
        }else{
            $counter = 0;
        }
        $enroll_stream->counter = $counter;
        $enroll_stream->end_time = now();
        $enroll_stream->save();

// pusher
        $options = array(
            'cluster' => 'ap2',
            'useTLS' => true
        );

        $pusher = new Pusher(
            'e82a1bf369a704f763a5',
            'ff9ab85e05c2e3703cff',
            '1010881',
            $options
        );
        $temp = ['user_id' => $user->id,'type' => 'leavestream', 'count' => $counter];
        $pusher->trigger('my-audience', 'my-broadcast', $temp);
    }

}
?>
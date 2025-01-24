<?php

namespace App\Http\Controllers;

use App\Mail\AdminRegistrationNotification;
use App\Mail\ManualNotification;
use App\Mail\PaymentNotification;
use App\Mail\SendExportFile;
use App\Models\Learner;
use App\Models\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;

class NotificationController extends Controller
{
    public $params;
    public $scope;
    public $type;
    public $subject;
    public $content;
    public $notified;
    public $data;
    public $file_name;
    public function __construct($params, $type, $scope, $subject, $content, $file_name)
    {
        $this->data = [];
        $this->notified = [];
        $this->params = $params;
        $this->type = $type;
        $this->scope = $scope;
        $this->subject = $subject;
        $this->content = $content;
        $this->file_name = $file_name;
        $this->notifications();
    }

    public function notifications()
    {

        if (!empty($this->scope)) {

            $this->data['email'] = null;
            $this->data['push'] = null;
            $this->data['web'] = null;
            // $this->data['whatsapp']=NULL;

            if (in_array('email', $this->scope)) {
                $this->email();
            }

            if (in_array('web', $this->scope)) {
                $this->web();
            }

            if (in_array('push', $this->scope)) {

                $this->push_notify();
            }

            // if(in_array('whatsapp',$this->scope)) $this->whatsapp();
        }
        return true;
    }

    public function email()
    {

        $inst = null;
        $to = isset($this->params['email']['to']) && !empty($this->params['email']['to']) ? $this->params['email']['to'] : null;

        if (!empty($to) && !empty($this->content) && !empty($this->subject)) {
            if ($this->type == 'registration') {
                $learnerId = isset($this->params['email']['learnerId']) && !empty($this->params['email']['learnerId']) ? $this->params['email']['learnerId'] : null;

                $learnerData = [];
                if ($learnerId > 0) {
                    $learnerData = get_learner_data($learnerId);
                }

                $inst = new AdminRegistrationNotification($this->content, $this->subject, $learnerData);

            } elseif ($this->type == 'payment' || $this->type == 'enroll') {
                $userCourseId = isset($this->params['email']['user_course_id']) && !empty($this->params['email']['user_course_id']) ? $this->params['email']['user_course_id'] : null;

                 $device_type = isset($this->params['common']['devcetype']) && !empty($this->params['common']['devcetype']) ? $this->params['common']['devcetype'] : 0;
                // dd($this->params['email']['user_course_id']);

                $invoiceData = [];
                // if($device_type=!2) {
                    if ($userCourseId > 0) {

                        $invoiceData = get_invoice_data($userCourseId);
                    }
                // }
                    // dd($device_type);
                $inst = new PaymentNotification($this->content, $this->subject, $invoiceData,"",$device_type);
            } elseif ($this->type == 'manual_notification') {

                $slug = isset($this->params['common']['slug']) && !empty($this->params['common']['slug']) ? $this->params['common']['slug'] : 0;
                $type = isset($this->params['common']['type']) && !empty($this->params['common']['type']) ? $this->params['common']['type'] : "";

                $image = isset($this->params['common']['image_name']) && !empty($this->params['common']['image_name']) ? $this->params['common']['image_name'] : "";
                $learner_manual_id = isset($this->params['common']['learner_id']) && !empty($this->params['common']['learner_id']) ? $this->params['common']['learner_id'] : "";

                $external_link = isset($this->params['common']['external_link']) && !empty($this->params['common']['external_link']) ? $this->params['common']['external_link'] : "";
                $message = isset($this->params['common']['content']) && !empty($this->params['common']['content']) ? $this->params['common']['content'] : "";
                $title = isset($this->params['common']['message']) && !empty($this->params['common']['message']) ? $this->params['common']['message'] : "";

                $redirect_type = isset($this->params['common']['redirect_type']) && !empty($this->params['common']['redirect_type']) ? $this->params['common']['redirect_type'] : 0;

                $name = isset($this->params['common']['name']) && !empty($this->params['common']['name']) ? $this->params['common']['name'] : '';
                $manual_notify_id = isset($this->params['common']['manual_notify_id']) && !empty($this->params['common']['manual_notify_id']) ? $this->params['common']['manual_notify_id'] : '';


                    if ((in_array('web', $this->scope) == false) || (in_array('web', $this->scope) == false)) {

                    $noti_id = Notification::create([
                        'title' => $title,
                        'text' => $message,
                        'learnerId' => $learner_manual_id,
                        'target_link' => $redirect_type,
                        'external_link' => $external_link,
                        'type' => $type,
                        'manual_notify_id' => $manual_notify_id,
                    ]);

                }

                $inst = new ManualNotification($this->content, $this->subject, $slug, $redirect_type, $name, $image, $external_link);

            } elseif ($this->type == 'export_email') {

                $inst = new SendExportFile($this->content, $this->subject, $this->file_name);
            }
            if (!empty($inst) && !empty($to)) {
                Mail::to($to)->send($inst);
                $this->notified[] = true;
                $ids = Learner::where('email', $to)->pluck('id')->toArray();
                if (!empty($ids)) {
                    $this->data['email'] = $ids;
                }
            }
        }
        return true;
    }

    public function push_notify()
    {

        $to = isset($this->params['push']['to']) && !empty($this->params['push']['to']) ? $this->params['push']['to'] : null;

        $type = isset($this->params['common']['type']) && !empty($this->params['common']['type']) ? $this->params['common']['type'] : 0;
        $course_id = isset($this->params['common']['course_id']) && !empty($this->params['common']['course_id']) ? $this->params['common']['course_id'] : 0;

        $learner_manual_id = isset($this->params['common']['learner_id']) && !empty($this->params['common']['learner_id']) ? $this->params['common']['learner_id'] : 0;
        $manual_notify_id = isset($this->params['common']['manual_notify_id']) && !empty($this->params['common']['manual_notify_id']) ? $this->params['common']['manual_notify_id'] : 0;

        $course_purchase_id = isset($this->params['common']['course_purchase_id']) && !empty($this->params['common']['course_purchase_id']) ? $this->params['common']['course_purchase_id'] : 0;

        $redirect_type = isset($this->params['common']['redirect_type']) && !empty($this->params['common']['redirect_type']) ? $this->params['common']['redirect_type'] : 0;

        $external_link = isset($this->params['common']['external_link']) && !empty($this->params['common']['external_link']) ? $this->params['common']['external_link'] : 0;

        $image_name = isset($this->params['common']['image_name']) && !empty($this->params['common']['image_name']) ? $this->params['common']['image_name'] : 0;

        $to_bck = [];
        if (!empty($this->subject) && !empty($this->content) && !empty($to)) {
            $to = explode(",", $to);
            $learner = Learner::whereIn('id', $to)->get();
            if (!empty($learner)) {
                $ids = $learner->pluck('id')->values()->toArray();
                $this->data['push'] = $ids;
                $to_bck = $ids;
            }

            notification($this->subject, $this->content, $to_bck, $type, $course_id, $course_purchase_id, $learner_manual_id, $this->scope, $this->type, $redirect_type, $external_link, $manual_notify_id, $image_name);
            $this->notified[] = true;
        }
        return true;
    }

    // public function whatsapp()
    // {

    //     $to = $this->params['whatsapp']['to'];
    //     $title = $this->params['whatsapp']['message'] ?? '';
    //     $template = $this->params['whatsapp']['template'] ?? '';
    //     $contact = env('MAIL_FROM_ADDRESS', "info@lifegurukul.com");
    //     $token = !empty(config('settings.whats_app_token')) ? config('settings.whats_app_token') : "";

    //     if (!empty($token)) {
    //         $phoneid = !empty(config('settings.whatsappphoneid')) ? config('settings.whatsappphoneid') : "175989102154600";

    //         if(!empty($phoneid)){
    //             $curl = curl_init();
    //             curl_setopt_array($curl, array(
    //             CURLOPT_URL => 'https://graph.facebook.com/v17.0/'.$phoneid.'/messages',
    //             CURLOPT_RETURNTRANSFER => true,
    //             CURLOPT_ENCODING => '',
    //             CURLOPT_MAXREDIRS => 10,
    //             CURLOPT_TIMEOUT => 0,
    //             CURLOPT_FOLLOWLOCATION => true,
    //             CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //             CURLOPT_CUSTOMREQUEST => 'POST',
    //             CURLOPT_POSTFIELDS =>'{
    //                 "messaging_product": "whatsapp",
    //                 "recipient_type": "individual",
    //                 "to": "'.$to.'",
    //                 "type": "template",
    //                 "template": {
    //                     "name": "'.$template.'",
    //                     "language": {
    //                         "code": "en"
    //                     },
    //                     "components": [
    //                         {
    //                             "type": "body",
    //                             "parameters": [
    //                                 {
    //                                     "type": "text",
    //                                     "text": "'.$title.'"
    //                                 },
    //                                 {
    //                                     "type": "text",
    //                                     "text": "'.$contact.'"
    //                                 }
    //                             ]
    //                         }
    //                     ]
    //                 }
    //             }',
    //             CURLOPT_HTTPHEADER => array(
    //                 'Content-Type: application/json',
    //                 'Accept: application/json',
    //                 'Authorization: Bearer '.$token
    //             ),
    //             ));

    //             $response = curl_exec($curl);
    //             $err = curl_error($curl);
    //             curl_close($curl);
    //             if ($err) {
    //                 return false;
    //             } else {
    //                 $learner = Learner::where('mobile', $to)->first();
    //                 if (!empty($learner)) {
    //                     dump("");
    //                     $this->data['whatsapp'] = $learner->id;
    //                 }
    //                 $this->notified[] = true;
    //                 return true;
    //             }
    //         }
    //     }
    // }

    public function web()
    {

        $to = $this->params['web']['to'];

        $this->data['web'] = $to;
        if (!empty($this->scope)) {
            $this->notified[] = true;
            $this->notify();
        }
        $this->notified[] = true;
    }

    public function notify()
    {
        // $type = isset($this->params['common']['type']) && !empty($this->params['common']['type']) ? $this->params['common']['type'] : '';
        // if($type==2) {
        //     return false;
        // }
        if (in_array('push', $this->scope) && in_array('web', $this->scope) && $this->type == 'manual_notification') {
            return false;
        }
        if (!empty($this->notified) && in_array(true, $this->notified)) {
            $external_link = isset($this->params['common']['external_link']) && !empty($this->params['common']['external_link']) ? $this->params['common']['external_link'] : 0;
            Notification::create([
                'title' => $this->subject,
                'text' => $this->content,
                'courseId' => $this->params['common']['course_id'] ?? null,
                'learnerId' => $this->params['common']['learner_id'] ?? null,
                'target_link' => $this->params['common']['redirect_type'] ?? null,
                'external_link' => $external_link,
                'type' => $this->params['common']['type'] ?? null,
                'manual_notify_id' => $this->params['common']['manual_notify_id'] ?? null,
            ]);
        }
        return true;
    }

    public function get_ids()
    {
        $ids = Arr::collapse($this->data);
        $ids = array_unique($ids);
        $ids = implode(",", $ids);
        return $ids;
    }
}

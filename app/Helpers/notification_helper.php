<?php
// legacy FCM APIs
function send_notification_FCM_old($notification_id, $title, $message, $id, $type)
{
    $accesstoken = env('FCM_KEY');
    $URL = 'https://fcm.googleapis.com/fcm/send';

    $post_data = '{
        "to" : "' . $notification_id . '",
        "data" : {
            "title" : "' . $title . '",
            "type" : "' . $type . '",
            "id" : "' . $id . '",
            "message" : "' . $message . '"
        },
        "notification" : {
            "body" : "' . $message . '",
            "title" : "' . $title . '",
            "type" : "' . $type . '",
            "id" : "' . $id . '",
            "message" : "' . $message . '",
            "icon" : "new",
            "sound" : "default"
        }
    }';

    $crl = curl_init();

    $headr = array();
    $headr[] = 'Content-type: application/json';
    $headr[] = 'Authorization: key=' . $accesstoken;

    curl_setopt($crl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($crl, CURLOPT_URL, $URL);
    curl_setopt($crl, CURLOPT_HTTPHEADER, $headr);
    curl_setopt($crl, CURLOPT_POST, true);
    curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);

    $rest = curl_exec($crl);

    /*   if ($rest === false) {

           return 0;
       } else {

           return 1;
       }*/

    curl_close($crl);
    return $rest;
}
// the HTTP v1 API

use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\OAuth2;
use Illuminate\Support\Facades\Http;

function getAccessToken()
{
    $client = new \Google\Client();
    $client->setAuthConfig(storage_path('rashaktekapp-firebase-adminsdk-dcz11-5055e6ab69.json'));
    $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
    $client->fetchAccessTokenWithAssertion();

    return $client->getAccessToken()['access_token'];
}

function send_notification_FCM($notification_id, $title, $message, $id, $type)
{
    $accessToken = getAccessToken();
    $projectID = 'rashaktekapp';
    $url = 'https://fcm.googleapis.com/v1/projects/' . $projectID . '/messages:send';
    $post_data = [
        'message' => [
            'token' => $notification_id,
            'data' => [
                'title' => (string) $title,
                'type' => (string) $type,
                'id' => (string) $id,
                'message' => (string) $message
            ],
            'notification' => [
                'body' => (string) $message,
                'title' => (string) $title
            ]
        ]
    ];

    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));

    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}


/*-------------------------------------------------------*/
function add_notifications($to_user_id, $to_user_name, $from_user_id, $from_user_name, $content, $title, $action, $status, $type)
{
    $notification = new \App\Models\Notifications;
    $notification->to_user_id = $to_user_id;
    $notification->to_user_name = $to_user_name;
    $notification->from_user_id = $from_user_id;
    $notification->from_user_name = $from_user_name;
    $notification->content = json_encode($content);
    $notification->title = json_encode($title);
    // $notification->action = $action;
    $notification->status = $status;
    $notification->type = $type;
    $notification->add_at_day = now()->format('Y-m-d');
    $notification->add_at_time = now()->format('H:i:s');
//dd($notification);
    $notification->save();
    return $notification;
}

/*---------------------------------------------------------------------------*/
function send_notifications($to_user_id, $to_user_name, $from_user_id, $from_user_name, $data, $extra_data, $status, $type, $token, $code)
{
    switch ($code) {
        case 1:
            $title_ar = 'تم تحديث نظامك الغذائي';
            $title_en = 'Your diet plan has been updated.';
            $title = ['ar' => $title_ar, 'en' => $title_en];
            $content_en = $from_user_name . '  has updated your diet  ' ;
            $content_ar = $from_user_name . ' قام بتحديث نظامك الغذائي  ';
            $content = ['ar' => $content_ar, 'en' => $content_en];
            if ($data[0] == 'en') {
                $title_fcm = $title_en;
                $message = $content_en;
            } elseif ($data[0] == 'ar') {
                $title_fcm = $title_ar;
                $message = $content_ar;
            }
            break;
        /*---------------------------------------------*/

        case 2:
            $title_ar = ' تم تحديث الinbody الخاص بك';
            $title_en = 'Your inbody  has been updated. ';
            $title = ['ar' => $title_ar, 'en' => $title_en];
            $content_en = $from_user_name . '  has updated your inbody ';
            $content_ar = $from_user_name . ' قام بتحديث inbody الخاص بك '  ;
            $content = ['ar' => $content_ar, 'en' => $content_en];
            if ($data[0] == 'en') {
                $title_fcm = $title_en;
                $message = $content_en;
            } elseif ($data[0] == 'ar') {
                $title_fcm = $title_ar;
                $message = $content_ar;
            }

            /*---------------------------------------------*/
            break;
        case 3:
            $title_ar = 'استخدام كود الدعوة الخاص بك';
            $title_en = 'Use your invitation code';
            $title = ['ar' => $title_ar, 'en' => $title_en];
            $content_en = $from_user_name . '  has used your  Invitation Code To Subscription';
            $content_ar = $from_user_name .'استخدم كود الدعوة الخاص بك للإشتراك ';
            $content = ['ar' => $content_ar, 'en' => $content_en];
            if ($data[0] == 'en') {
                $title_fcm = $title_en;
                $message = $content_en;
            } elseif ($data[0] == 'ar') {
                $title_fcm = $title_ar;
                $message = $content_ar;
            }

            /*---------------------------------------------*/
            break;
        case 4:
            $title_ar = 'إضافة class جديد';
            $title_en = 'Add new Class ';
            $title = ['ar' => $title_ar, 'en' => $title_en];
            $content_en = $from_user_name . ' A class has been created';
            $content_ar = 'تم إضافة class جديد' . $from_user_name;
            $content = ['ar' => $content_ar, 'en' => $content_en];
            if ($data[0] == 'en') {
                $title_fcm = $title_en;
                $message = $content_en;
            } elseif ($data[0] == 'ar') {
                $title_fcm = $title_ar;
                $message = $content_ar;
            }

            /*---------------------------------------------*/
            break;

        /*---------------------------------------------*/

    }
    add_notifications($to_user_id, $to_user_name, $from_user_id, $from_user_name, $content, $title, $extra_data, $status, $type);
   // dd($extra_data);
    send_notification_FCM($token, $title_fcm, $message, $extra_data['0'], $type);
}

/***********************************************************************************************************/
function send_notifications2($to_user_id, $to_user_name, $from_user_id, $from_user_name, $data, $extra_data, $status, $type, $token, $code)
{

    $notificationData = [
        1 => [
            'title' => ['ar' => 'انضمام للتمرين', 'en' => 'exercise join request'],
            'content' => [
                'en' => "$from_user_name has sent a request to join exercise {$data[1]} at day {$data[2]} ",
                'ar' => "$from_user_name ارسل لك طلب انظمام للتمرين {$data[1]} ليوم {$data[2]}"
            ]
        ],
        2 => [
            'title' => ['ar' => 'رفض انضمام للتمرين', 'en' => 'exercise join accept'],
            'content' => [
                'en' => "$from_user_name refused request to join exercise {$data[1]} at day {$data[2]}",
                'ar' => "$data[2] ليوم {$data[1]} رفض على طلب الانضمام على رحلة $from_user_name"
            ]
        ],
        4 => [
            'title' => ['ar' => 'بداية التمرين', 'en' => 'Start of exercise'],
            'content' => [
                'en' => "$from_user_name  has started exercising. {$data[1]}",
                'ar' => "$data[1]  بدأ التمرين $from_user_name"
            ]
        ],
        5 => [
            'title' => ['ar' => 'عمل تعليق', 'en' => 'Make a comment'],
            'content' => [
                'en' => "$from_user_name commented {$data[1]}",
                'ar' => "$data[1] نشر تعليق $from_user_name"
            ]
        ]
    ];

    if (isset($notificationData[$code])) {
        $titleData = $notificationData[$code]['title'];
        $contentData = $notificationData[$code]['content'];

        $title = ['ar' => $titleData['ar'], 'en' => $titleData['en']];
        $content = ['ar' => $contentData['ar'], 'en' => $contentData['en']];
        $title_fcm = $data[0] == 'en' ? $titleData['en'] : $titleData['ar'];
        $message = $data[0] == 'en' ? $contentData['en'] : $contentData['ar'];

        add_notifications($to_user_id, $to_user_name, $from_user_id, $from_user_name, $content, $title, $extra_data, $status, $code);
        return   send_notification_FCM($token, $title_fcm, $message, $extra_data[0], $type);
    }
}


//////////////////////////for oneSginal //////////////////////
if (!function_exists('sendOneSignalNotification')) {
    function sendOneSignalNotification($playerIds, $message, $data = null, $url = null)
    {
        $appId = config('onesignal.app_id');
        $restApiKey = config('onesignal.rest_api_key');

        if (!$appId || !$restApiKey) {
            throw new \Exception('OneSignal configuration not found');
        }

        $fields = [
            'app_id' => $appId,
            'include_player_ids' => is_array($playerIds) ? $playerIds : [$playerIds],
            'contents' => [
                'en' => $message
            ],
            'data' => $data,
            'url' => $url
        ];

        $fields = json_encode($fields);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Basic ' . $restApiKey
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            \Log::error('OneSignal API Error: ' . $response);
            return false;
        }

        return json_decode($response, true);
    }
}


if (!function_exists('sendOneSignalNotification1')) {
    function sendOneSignalNotification1($users = null, $message, $data = null, $url = null, $filters = null)
    {
        // $playerIds = [];
        // if ($users) {
        //     foreach ($users as $user) {
        //         if (!empty($user->onesignal_id)) {
        //             $playerIds[] = $user->onesignal_id;
        //         }
        //     }
        // }

        // if (empty($playerIds) && !$filters) {
        //     logger()->warning('No valid OneSignal player IDs found for notification');
        //     return false;
        // }

        // $payload = [
        //     'app_id' => config('onesignal.app_id'),
        //     'contents' => ['en' => $message],
        //     'data' => $data
        // ];

        // if ($url) {
        //     $payload['url'] = $url;
        // }

        // if (!empty($playerIds)) {
        //     $payload['include_player_ids'] = $playerIds;
        // } elseif ($filters) {
        //     $payload['filters'] = $filters;
        // } else {
        //     $payload['included_segments'] = ['All'];
        // }

        // logger()->info('OneSignal payload: ' . json_encode($payload));

        // $apiKey = config('onesignal.rest_api_key');
        // logger()->info('Using OneSignal API Key: ' . substr($apiKey, 0, 5) . '...');

        // $response = Http::withHeaders([
        //     'Authorization' => 'Basic ' . $apiKey,
        //     'Content-Type' => 'application/json'
        // ])->post('https://onesignal.com/api/v1/notifications', $payload);

        // if ($response->failed()) {
        //     logger()->error('OneSignal error: ' . $response->body());
        //     logger()->error('OneSignal status code: ' . $response->status());
        //     return false;
        // }

        // return $response->json();
    }
    // sendOneSignalNotification1(
    //     null,
    //     'إعلان عام لجميع المستخدمين',
    //     null,
    //     'https://example.com/announcement'
    // );
}

if (!function_exists('sendOneSignalNotification2')) {
    if (!function_exists('sendOneSignalNotification')) {/**
        * إرسال إشعار OneSignal متكامل بجميع الخيارات
        *
        * @param array|string $playerIds - مصفوفة أو نص لـ Player IDs
        * @param string $message - نص الإشعار الرئيسي
        * @param array $options - مصفوفة تحتوي على جميع الخيارات المتقدمة:
        *    - 'title' => string - عنوان الإشعار
        *    - 'data' => array - بيانات مخصصة
        *    - 'url' => string - رابط عند النقر
        *    - 'image' => string - صورة للإشعار
        *    - 'buttons' => array - أزرار (array of ['id'=>string, 'text'=>string, 'icon'=>string])
        *    - 'schedule' => string - وقت الجدولة (تنسيق ISO 8601)
        *    - 'android_channel_id' => string - قناة أندرويد المخصصة
        *    - 'ios_badgeType' => string - نوع البادج (Increase/SetTo/None)
        *    - 'ios_badgeCount' => int - عدد البادج
        *    - 'ttl' => int - وقت الحياة بالثواني
        *    - 'priority' => int - الأولوية (10 عاجل، 5 عادي)
        * @return array|false - استجابة OneSignal أو false عند الفشل
        */
        function sendOneSignalNotification2($playerIds, $message, array $options = [])
        {
            $defaultParams = [
                'app_id' => config('onesignal.app_id'),
                'include_player_ids' => (array) $playerIds,
                'contents' => ['en' => $message],
            ];

            // معالجة الخيارات الأساسية
            $optionalParams = [
                'headings' => isset($options['title']) ? ['en' => $options['title']] : null,
                'data' => $options['data'] ?? null,
                'url' => $options['url'] ?? null,
                'ios_attachments' => isset($options['image']) ? ['id1' => $options['image']] : null,
                'big_picture' => $options['image'] ?? null,
                'buttons' => $options['buttons'] ?? null,
                'send_after' => $options['schedule'] ?? null,
                'android_channel_id' => $options['android_channel_id'] ?? null,
                'ios_badgeType' => $options['ios_badgeType'] ?? null,
                'ios_badgeCount' => $options['ios_badgeCount'] ?? null,
                'ttl' => $options['ttl'] ?? null,
                'priority' => $options['priority'] ?? null,
                'content_available' => $options['content_available'] ?? false,
                'mutable_content' => $options['mutable_content'] ?? false,
            ];

            // إزالة القيم الفارغة
            $optionalParams = array_filter($optionalParams, function ($value) {
                return $value !== null;
            });

            $requestParams = array_merge($defaultParams, $optionalParams);

            try {
                $response = Http::timeout(15)
                    ->retry(3, 500)
                    ->withHeaders([
                        'Authorization' => 'Basic ' . config('onesignal.rest_api_key'),
                        'Content-Type' => 'application/json',
                    ])
                    ->post('https://onesignal.com/api/v1/notifications', $requestParams);

                if ($response->failed()) {
                    logger()->error('OneSignal API Error', [
                        'status' => $response->status(),
                        'response' => $response->body(),
                        'request' => $requestParams
                    ]);
                    return false;
                }

                return $response->json();

            } catch (\Exception $e) {
                logger()->error('OneSignal Connection Error: ' . $e->getMessage());
                return false;
            }
        }
    }
}

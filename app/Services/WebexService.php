<?php

namespace App\Services;

use App\Models\MasterClass;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Offlineagency\LaravelWebex\LaravelWebex;


class WebexService
{
    /**
     * @throws Exception
     */
    public static function getAccessToken($grantType, $codeKey, $code, $needUrl = false): array
    {
        $request = [
            'grant_type' => $grantType,
            'client_id' => Config::get('webex.client.id'),
            'client_secret' => Config::get('webex.client.secret'),
            $codeKey => $code
        ];
        if ($needUrl) {
            $request['redirect_uri'] = Config::get('webex.redirect_uri');
        }

        $response = Http::post('https://webexapis.com/v1/access_token', $request);

        $result = json_decode($response->body());
        $data = [];
        if ($response->status() !== 200) {
            throw new Exception('Invalid webex token, please try again later.');
        }
        $data['access_token'] = $result->access_token;
        if (isset($result->refresh_token)) {
            $data['refresh_token'] = $result->refresh_token;
        }
        return $data;
    }

    /**
     * @throws Exception
     */
    public static function createMeeting($webexAuthCode, $data): array
    {
        $tokens = self::getAccessToken('authorization_code', 'code', $webexAuthCode, true);
        self::setAccessToken($tokens['access_token']);
        $startDateTime = Carbon::parse($data['start_date'])->setTimeFromTimeString($data['start_time']);
        $startDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $startDateTime->toDateTimeString(), 'Asia/Kolkata')->setTimezone('UTC');
        $endDateTime = (clone $startDateTime)->addMinutes($data['duration']);
        $webex = new LaravelWebex();
        return [
            'meeting' => $webex->meeting()->create($data['title'], $startDateTime->toAtomString(), $endDateTime->toAtomString(), ['sendEmail' => false]),
            'refresh_token' => $tokens['refresh_token'] ?? null
        ];
    }

    public static function setAccessToken($webexToken)
    {
        Config::set('webex.bearer', $webexToken);
    }


    /**
     * @throws Exception
     */
    public static function addAttendee($refreshToken, MasterClass $masterClass, $user){
        $tokens = self::getAccessToken('refresh_token', 'refresh_token', $refreshToken);
        self::setAccessToken($tokens['access_token']);
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $tokens['access_token']
        ])->post('https://webexapis.com/v1/meetingInvitees', [
            'meetingId' => $masterClass->meeting_id,
            'email' => $user->vEmail,
            'displayName' => $user->vFirstName. ' '. $user->vLastName,
            'coHost' => false,
            'sendEmail' => false
        ]);
        $result = json_decode($response->body());
        if ($response->status() !== 200) {
            throw new Exception($result->message ?? 'Invalid webex response, please try again later.');
        }
        $masterClass->update(['refresh_token' => $tokens['refresh_token']]);
    }


    /**
     * @throws Exception
     */
    public static function createMeetingWithAttendee($refreshToken, $data, $attendee): array
    {
        $tokens = self::getAccessToken('refresh_token', 'refresh_token', $refreshToken);
        self::setAccessToken($tokens['access_token']);
        $webex = new LaravelWebex();
        return [
            'meeting' => $webex->meeting()->create($data['title'], $data['start_date_time']->toAtomString(), $data['end_date_time']->toAtomString(),
                [   'sendEmail' => false,
                    'invitees' => [
                        [
                            'email' => $attendee->vEmail,
                            'displayName' => $attendee->vFirstName. ' '. $attendee->vLastName,
                            'coHost' => false,
                            'sendEmail' => false
                        ]
                    ]
                ]),
            'refresh_token' => $tokens['refresh_token'] ?? null
        ];
    }


}

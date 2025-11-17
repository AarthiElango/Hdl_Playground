<?php
namespace App\Controllers\Guest;

use App\Controllers\Controller;
use Carbon\Carbon;
use function _\get;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RegisterController extends Controller
{
    /**
     * Handle guest login request.
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */

    public function index(Request $request, Response $response, array $args): Response
    {
        $validator = new \App\Helpers\Validator();
        $data      = $request->getParsedBody();
        $rules     = [
            'email'    => 'required|email|unique:users,email',
            'username'    => 'required|unique:users,username|regex:/^[0-9a-z]*$/',
            'mobile'   => 'required|regex:/^[6-9][0-9]{9}$/|unique:users,mobile',
            'fullname' => 'required|min:3|max:30',
        ];
        $messages = [

        ];
        $validationResult = $validator->make($data, $rules, $messages);
        if ($validationResult !== true) {
            return $this->json(['errors' => $validationResult], 422);
        }
        $validData = $validator->validData;

        $random = new \App\Helpers\Random;
        $otp    = $random->otp();

        $dbhelper = new \App\Helpers\DB;

        $args = [
            'username'           => $validData->username,
            'password'       => password_hash($random->string(), PASSWORD_DEFAULT),
            'fullname'       => $validData->fullname,
            'email'          => $validData->email,
            'mobile'         => $validData->mobile,
            'role_id'        => ! empty($_ENV['APP_DEFAULT_ROLE']) ? $_ENV['APP_DEFAULT_ROLE'] : -1,
            'otp'            => $otp,
            'otp_created_at' => Carbon::now()->toDateTimeString(),
        ];

        try {
            $this->db->insert('users', $args);

        } catch (\Exception $e) {

            return $this->json(['error' => $e->getMessage()], 422);
        }

        $ENV = get($_ENV, 'APP_ENV', 'production');

        $res = ['message' => 'User registration successful', 'username' => $validData->username];

        if ($ENV === 'production') {

            $sms = new \App\Helpers\FastSms;

            $uid = $this->db->id();

            $sent = $sms->send($uid);

            if (! $sent) {

                return $this->json(['error' => 'Error sending sms in OTP. Contact administrator'], 422);
            }

            return $this->json($res);
        }

        $res['otp'] = $otp;

        return $this->json($res);

    }
}

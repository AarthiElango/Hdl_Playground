<?php
namespace App\Controllers\Guest;

use App\Controllers\Controller;
use Carbon\Carbon;
use function _\get;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ForgotPasswordController extends Controller
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
            'username' => 'required',
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

        $where = [
            'OR' => [
                'email'  => $validData->username,
                'mobile' => $validData->username,
                'username' => $validData->username,
            ],
        ];
        $user = (object) $this->db->get('users', ['id', 'fullname', 'email', 'username' ,'otp_created_at'], $where);

        if (empty($user) || empty($user->id)) {

            return $this->json(['error' => 'Invalid user'], 422);
        }

        $uid = $user->id;

        if ($user->otp_created_at) {

            $createdAt = Carbon::parse($user->otp_created_at);

            $minutesPassed = $createdAt->diffInMinutes(Carbon::now());

            if ($minutesPassed < 5) {

                //  return $this->json(['error' => 'You cannot generate otp for 5 mins'], 422);
            }

        }

        $random = new \App\Helpers\Random;
        $otp    = $random->otp();

        $args = [
            'otp'            => $otp,
            'otp_created_at' => Carbon::now()->toDateTimeString(),
        ];

        try {
            $this->db->update('users', $args, ['id' => $uid]);

        } catch (\Exception $e) {

            return $this->json(['error' => $e->getMessage()], 422);
        }

        $ENV = get($_ENV, 'APP_ENV', 'production');

        $res = ['message' => 'OTP generated successful','username' => $user->username];

        $res['env'] = $ENV;

       if ($ENV !== 'production') {
            $res['otp'] = $otp;

            return $this->json($res);

        }
        $args = [
            'from'                  => 'HDL Playground <postmaster@silicon-craft.com>',
            'to'                    => $user->email,
            'template'              => 'otp for hdlplayground',
            'h:X-Mailgun-Variables' => '{"otp": ' . $otp . ',"name":"' . $user->fullname . '"}',
        ];

        $mail = new \App\Helpers\Mailgun;

        $mail->send($args);

        return $this->json($res);

    }
}

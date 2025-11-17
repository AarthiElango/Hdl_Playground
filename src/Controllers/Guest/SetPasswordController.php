<?php
namespace App\Controllers\Guest;

use App\Controllers\Controller;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Carbon\Carbon;

class SetPasswordController extends Controller
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
            'username'  => 'required',
            'password'  => 'required|min:8|max:16',
            'cpassword' => 'required|same:password',
            'otp'       => 'required|min:6|max:6|regex:/^\d{6}$/|exists:users,otp',
        ];
        $messages = [
        ];
        $validationResult = $validator->make($data, $rules, $messages);
        if ($validationResult !== true) {
            return $this->json(['errors' =>$validationResult], 409);
        }
        $validData = $validator->validData;

        $where = [
            'username' => $validData->username,
            'otp' => $validData->otp,
            'otp_created_at[!]' => NULL
        ];

        $user = (object) $this->db->get('users', '*', $where);
        if (empty($user->id)) {

            return $this->json(['error' => 'Invalid user'], 422);
        }

        $uid = $user->id;

        $createdAt = Carbon::parse($user->otp_created_at);

// Get the difference in minutes
        $minutesPassed = $createdAt->diffInMinutes(Carbon::now());

        if ($minutesPassed > 10) {

            return $this->json(['message' => 'OTP expired. Try again'], 422);
        }

        $args = [
            'password' => password_hash($validData->password, PASSWORD_DEFAULT),
            'otp' => NULL,
            'otp_created_at' => NULL,
            'verified_at' => Carbon::now()->toDateTimeString()
        ];

        $this->db->update('users', $args, ['id' => $uid]);

        return $this->json([
            'message' => 'Password updated successful',
        ]);

    }
}

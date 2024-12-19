<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use Carbon\Carbon;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Jobs\SendVerificationsOTP;
use App\Models\CustomerReferral;
use App\Models\LogVerifications;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;



class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $request->validated();

        DB::beginTransaction();
        try {

            $user = new Customer();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->status = 'inactive';
            $user->referral_code = $request->referral_code;
            $user->password = Hash::make($request->password);
            $user->save();

            if ($request->filled('referral_code')) {
                CustomerReferral::create(
                    [
                        'customer_id' => $user->id
                    ]
                );
            }

            $randomCode = random_int(100000, 999999);

            $otpType = DB::table('otp_types')->where('name', 'verify')->first();

            LogVerifications::create(
                [
                    'otp_type_id' => $otpType->id,
                    'phone' => $user->phone,
                    'code' => $randomCode,
                    'duration' => Carbon::now()->addMinutes(5),
                    'is_used' => false,
                    'is_expired' => false,
                ]
            );

            dispatch(new SendVerificationsOTP($user, $randomCode))->delay(Carbon::now()->addSeconds(1));

            DB::commit();

            return response()->api($user, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            $statusCode = ($e->getCode() > 100 && $e->getCode() < 600 && !$e instanceof QueryException) ? $e->getCode() : 500;
            $response = [
                'errors' => $e,
            ];
            return response()->api($response, $statusCode);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'nullable|string|email|max:150|required_without:phone',
                'phone' => 'nullable|string|max:20|regex:/^62[0-9]{9,18}$/|required_without:email',
                'password' => 'required|string|min:8'
            ]);

            $user = Customer::where('email', $request->email)
                ->orWhere('phone', $request->phone)
                ->first();

            if (!$user) {
                throw new \Exception('Akun tidak ditemukan. Pastikan email dan nomor telepon benar', 404);
            }

            if ($user->status !== 'active') {
                throw new \Exception('Akun belum diverifikasi. Masukkan kode OTP untuk melanjutkan.', 400);
            }

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw new \Exception('Kata sandi salah', 401);
            }

            $data = [
                'id' => $user->id,
                'name' => $user->name,
            ];

            $token = JWTAuth::claims($data)->fromUser($user);

            return response()->api($token, 200);
        } catch (\Exception $e) {
            $statusCode = ($e->getCode() > 100 && $e->getCode() < 600 && !$e instanceof QueryException) ? $e->getCode() : 500;
            $response = [
                'errors' => $e->getMessage(),
            ];
            return response()->json($response, $statusCode);
        }
    }


    public function verifications(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|regex:/^62[0-9]{9,18}$/|max:20',
            'code' => 'required|string|max:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => true,
                'message' => $validator->errors(),
            ]);
        }

        DB::beginTransaction();
        try {

            $data = DB::table('log_verification_request')
                ->where('phone', $request->phone)
                ->first();

            if (!$data) {
                throw new \Exception('Data tidak ditemukan', 404);
            }

            if ($data->code !== $request->code) {
                throw new \Exception('Kode OTP salah', 400);
            }


            $expired = Carbon::parse($data->created_at)->addMinutes(5);
            if (Carbon::now()->greaterThan($expired)) {
                throw new \Exception('Kode OTP telah kadaluarsa', 400);
            }

            DB::table('customers')
                ->where('phone', $request->phone)
                ->update([
                    'status' => 'active'
                ]);

            DB::table('log_verification_request')
                ->where('id', $data->id)
                ->delete();

            DB::commit();

            return response()->api('Konfirmasi Berhasil', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            $statusCode = ($e->getCode() > 100 && $e->getCode() < 600 && !$e instanceof QueryException) ? $e->getCode() : 500;
            $response = [
                'errors' => $e->getMessage(),
            ];
            return response()->json($response, $statusCode);
        }
    }

    public function resendOTP(string $phone)
    {
        $validator = Validator::make(['phone' => $phone], [
            'phone' => 'required|string|max:20|regex:/^62[0-9]{9,18}$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $user = Customer::Where('phone', $phone)->first();

            if (!$user) {
                throw new \Exception('Nomor Telepon tidak terdaftar', 404);
            }

            DB::table('log_verification_request')->where('phone', $phone)->delete();

            $randomCode = random_int(100000, 999999);

            $otpType = DB::table('otp_types')->where('name', 'verify')->first();

            LogVerifications::create([
                'otp_type_id' => $otpType->id,
                'phone' => $phone,
                'code' => $randomCode,
                'duration' => Carbon::now()->addMinutes(5),
                'is_used' => false,
                'is_expired' => false,
            ]);

            $user = DB::table('log_verification_request')->where('phone', $phone)->first();

            dispatch(new SendVerificationsOTP($user, $randomCode))->delay(Carbon::now()->addSeconds(1));

            DB::commit();

            return response()->api('Kode OTP Berhasil Dikirim', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            $statusCode = ($e->getCode() > 100 && $e->getCode() < 600 && !$e instanceof QueryException) ? $e->getCode() : 500;
            $response = [
                'errors' => $e,
            ];
            return response()->api($response, $statusCode);
        }
    }

    public function phoneVerify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:20|regex:/^62[0-9]{9,18}$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $user = Customer::Where('phone', $request->phone)->first();

            if (!$user) {
                throw new \Exception('Nomor Telepon tidak terdaftar', 404);
            }

            DB::table('log_verification_request')->where('phone', $request->phone)->delete();

            $randomCode = random_int(100000, 999999);

            $otpType = DB::table('otp_types')->where('name', 'forgotPassword')->first();

            LogVerifications::create([
                'otp_type_id' => $otpType->id,
                'phone' => $request->phone,
                'code' => $randomCode,
                'duration' => Carbon::now()->addMinutes(5),
                'is_used' => false,
                'is_expired' => false,
            ]);

            dispatch(new SendVerificationsOTP($user, $randomCode))->delay(Carbon::now()->addSeconds(1));

            DB::commit();

            return response()->api('Kode OTP Berhasil Dikirim', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            $statusCode = ($e->getCode() > 100 && $e->getCode() < 600 && !$e instanceof QueryException) ? $e->getCode() : 500;
            $response = [
                'errors' => $e,
            ];
            return response()->api($response, $statusCode);
        }
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:20|regex:/^62[0-9]{9,18}$/',
            'code' => 'required|string|min:6',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {

            $data = DB::table('log_verification_request')
                ->where('phone', $request->phone)
                ->first();

            if (!$data) {
                throw new \Exception('Data tidak ditemukan', 404);
            }

            if ($data->code !== $request->code) {
                throw new \Exception('Kode OTP salah', 400);
            }

            $expired = Carbon::parse($data->created_at)->addMinutes(5);
            if (Carbon::now()->greaterThan($expired)) {
                throw new \Exception('Kode OTP telah kadaluarsa', 400);
            }
            $user = Customer::where('phone', $request->phone)->firstOrFail();
            $user->password = Hash::make($request->new_password);
            $user->save();

            DB::table('log_verification_request')
                ->where('id', $data->id)
                ->delete();

            DB::commit();
            return response()->api('Password berhasil diganti', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            $statusCode = ($e->getCode() > 100 && $e->getCode() < 600 && !$e instanceof QueryException) ? $e->getCode() : 500;
            $response = [
                'errors' => $e,
            ];
            return response()->api($response, $statusCode);
        }
    }

    public function resendOtpforgotpassword(string $phone)
    {
        $validator = Validator::make(['phone' => $phone], [
            'phone' => 'required|string|max:20|regex:/^62[0-9]{9,18}$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $user = Customer::where('phone', $phone)
                ->where('status', 'active')
                ->first();

            if (!$user) {
                throw new \Exception('Nomor Telepon tidak terdaftar', 404);
            }

            DB::table('log_verification_request')->where('phone', $phone)->delete();

            $randomCode = random_int(100000, 999999);

            $otpType = DB::table('otp_types')->where('name', 'forgotPassword')->first();

            LogVerifications::create([
                'otp_type_id' => $otpType->id,
                'phone' => $phone,
                'code' => $randomCode,
                'duration' => Carbon::now()->addMinutes(5),
                'is_used' => false,
                'is_expired' => false,
            ]);

            $user = DB::table('log_verification_request')->where('phone', $phone)->first();

            dispatch(new SendVerificationsOTP($user, $randomCode))->delay(Carbon::now()->addSeconds(1));

            DB::commit();

            return response()->api('Kode OTP Berhasil Dikirim', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            $statusCode = ($e->getCode() > 100 && $e->getCode() < 600 && !$e instanceof QueryException) ? $e->getCode() : 500;
            $response = [
                'errors' => $e,
            ];
            return response()->api($response, $statusCode);
        }
    }

    public function me(Request $request)
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                throw new \Exception('User not found', 404);
            }

            $data = Customer::select(
                'id',
                'name',
                'email',
                'phone',
            )
                ->where('id', $user->id)
                ->first();

            return response()->api($data, 200);
        } catch (\Exception $e) {

            $statusCode = ($e->getCode() > 100 && $e->getCode() < 600 && !$e instanceof QueryException) ? $e->getCode() : 500;
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], $statusCode);
        }
    }
}

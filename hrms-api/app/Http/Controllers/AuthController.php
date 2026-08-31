<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;

use App\Http\Controllers\Controller;
use App\Utilities\Utility;

// Declare All Used Models
use App\Http\Models\Employment;
use App\Http\Models\AccessRole;
use App\Http\Models\Feature;
use App\Models\Employee;

class AuthController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Check if the mobile device_id is matched with saved device id at database.
     * if at the database is null, then insert it.
     *
     * @param string $deviceId
     * @param string $nik
     * @return bool
     */
    private function isDeviceMatch($deviceId, string $nik)
    {
        $emp = Employee::where('nik', $nik)->first();
        if ($emp->device_id){
            return $emp->device_id == $deviceId;
        } else {
            $emp->device_id = $deviceId;
            $emp->save();
        }
        return true;
    }

    function auth(Request $request)
    {
        $nik = $request->json()->get('nik');
        $password = $request->json()->get('password');
        $application = $request->json()->get('application');
        $deviceId = $request->json()->get('device_id');

        if (($nik == null) || ($nik == '')) {
            return Utility::setResponseExT(901, "Unknown NIK!", []);
        }
        if (($password == null) || ($password == '')) {
            return Utility::setResponseExT(901, "Unknown Password!", []);
        }
        $data = Employment::getUser($nik);
        if (!$data) {
            return Utility::setResponseExT(901, "NIK tidak terdaftar!", []);
        }
        if (Utility::encryption($password) != $data[0]->strpassword) {
            return Utility::setResponseExT(901, "Kombinasi NIK dan password tidak sesuai!", []);
        }

        // if the frontend is send a device_id
        if ($deviceId != null){
            if (! $this->isDeviceMatch($deviceId, $nik)){
                return Utility::setResponseExT(901, 'Device Anda tidak sama dengan yang sebelumnya. Silahkan melakukan permintaan perubahan ke Divisi HR.', []);
            }
        }

        $features = Feature::getFeatureByApplicationCode($application);
        if (!$features) {
            return Utility::setResponseExT(901, "Anda tidak berhak menggunakan feature aplikasi ini!", []);
        };

        $organizations = AccessRole::getRoleOrganization($data[0]->id);

        $status = array(
            "error" => false,
            "message" => "Login success!",
            "code" => 200
        );
        $meta = array(
            "more" => false,
            "timestamp" => date("Y-m-d H:i:s", time()),
            "cursor" => array(
                "count_rec" => 0,
                "count_data" => 0,
                "count_page" => 0,
                "curr_page" => 0,
                "next_page" => 0
            )
        );
        $rows = array(
            "employee" => $data[0]->id,
            "nik" => $data[0]->nik,
            "strname" => $data[0]->strname,
            "passwordstate" => $data[0]->passwordstate,
            "roleid" => $data[0]->roleid,
            "role" => $data[0]->rolename,
            "securityRoles" => $organizations,
            "token" =>  Utility::newToken($data[0]->id, $data[0]->businessunitid, $data[0]->roleid)
        );
        $result["status"] = $status;
        $result["data"] = $rows;
        $result["meta"] = $meta;
        return response()->json($result);
    }

    function authTicket(Request $request)
    {
        $nik = $request->json()->get('nik');
        $password = $request->json()->get('password');
        $application = $request->json()->get('application');

        if (($nik == null) || ($nik == '')) {
            return Utility::setResponseExT(901, "Unknown NIK!", []);
        }
        if (($password == null) || ($password == '')) {
            return Utility::setResponseExT(901, "Unknown Password!", []);
        }
        $data = Employment::getUserForTicket($nik);
        if (!$data) {
            return Utility::setResponseExT(901, "NIK tidak terdaftar!", []);
        }
        if (Utility::encryption($password) != $data[0]->strpassword) {
            return Utility::setResponseExT(901, "Kombinasi NIK dan password tidak sesuai!", []);
        }

        $features = Feature::getFeatureByApplicationCode($application);
        if (!$features) {
            return Utility::setResponseExT(901, "Anda tidak berhak menggunakan feature aplikasi ini!", []);
        };

        $status = array(
            "error" => false,
            "message" => "Login success!",
            "code" => 200
        );
        $meta = array(
            "more" => false,
            "timestamp" => date("Y-m-d H:i:s", time()),
            "cursor" => array(
                "count_rec" => 0,
                "count_data" => 0,
                "count_page" => 0,
                "curr_page" => 0,
                "next_page" => 0
            )
        );
        $rows = array(
            "employee" => $data[0]->id,
            "nik" => $data[0]->nik,
            "strname" => $data[0]->employeename,
            "roleid" => $data[0]->roleid,
            "role" => $data[0]->rolename,
            "token" =>  Utility::newToken($data[0]->id, $data[0]->businessunitid, $data[0]->roleid)
        );

        $result["status"] = $status;
        $result["data"] = $rows;
        $result["meta"] = $meta;
        return response()->json($result);
    }

    function generateNewToken(Request $request)
    {
        $status = array(
            "error" => false,
            "message" => "Refresh Token",
            "code" => 200
        );
        $meta = array(
            "more" => false,
            "timestamp" => date("Y-m-d H:i:s", time()),
            "cursor" => array(
                "count_rec" => 0,
                "count_data" => 0,
                "count_page" => 0,
                "curr_page" => 0,
                "next_page" => 0
            )
        );
        $rows = array(
            "token" =>  Utility::newToken($request->creds->id, $request->creds->off, $request->creds->grp)
        );
        $result["status"] = $status;
        $result["data"] = $rows;
        $result["meta"] = $meta;
        return response()->json($result);
    }

    function testApiRequest(Request $request)
    {
        $token = $request->bearerToken();
        $credentials = JWT::decode($token, env('JWT_SECRET'), [env('JWT_HASH')]);
        return json_encode($credentials->id);
    }

    function changePassword(Request $request)
    {
        $currentPassword = $request->json()->get('current_password');
        $newPassword = $request->json()->get('new_password');
        $newPasswordConfirmation = $request->json()->get('new_password_confirmation');

        if (($currentPassword == null) || ($currentPassword == '')) {
            return Utility::setResponseExT(901, "Password sekarang harus diisi!", []);
        }
        if (($newPassword == null) || ($newPassword == '')) {
            return Utility::setResponseExT(901, "Password baru harus diisi!", []);
        }
        if (($newPasswordConfirmation == null) || ($newPasswordConfirmation == '')) {
            return Utility::setResponseExT(901, "Konfirmasi password baru harus diisi!", []);
        }
        if ($newPassword !== $newPasswordConfirmation) {
            return Utility::setResponseExT(901, "Password baru dan konfirmasi password tidak sama!", []);
        }
        if (strlen($newPassword) < 6) {
            return Utility::setResponseExT(901, "Password harus berisi 6 karakter atau lebih", []);
        }

        $uppercase = preg_match('@[A-Z]@', $newPassword);
        $lowercase = preg_match('@[a-z]@', $newPassword);
        $number = preg_match('@[0-9]@', $newPassword);
        $special = preg_match('/[#$%^&*()+=\-\[\]\';,.\/{}|":<>?~\\\\]/', $newPassword);
        $length = strlen($newPassword) >= 6;

        if (!$uppercase || !$lowercase || !$number || !$special || !$length) {
            return Utility::setResponseExT(901, "Password harus terdiri dari huruf besar, huruf kecil , nomor , dan simbol !", []);
        }

        $storedPassword = '';
        $hashedCurrentPassword = md5($currentPassword);
        $employee = Employment::getById($request->creds->id);
        if ($employee) {
            $storedPassword = $employee[0]->strpassword;
        }
        $hashedNewPassword = md5($newPassword);

        if ($storedPassword !== $hashedCurrentPassword) {
            return Utility::setResponseExT(901, "Password sekarang yang anda masukkan salah!", []);
        }

        return response()->json(Employment::changePassword($request->creds->id, $hashedNewPassword));
    }
}

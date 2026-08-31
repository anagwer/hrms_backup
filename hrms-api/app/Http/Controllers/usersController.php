<?php

namespace App\Http\Controllers;
use Validator;

use Illuminate\Http\Request;
use Illuminate\Support\Facades;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Utilities\Utility;

// Declare All Used Models
use App\Http\Models\TUsers;
use App\Http\Models\TRoles;
use App\Http\Models\VUserRole;

class usersController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {

    }

    function list(Request $request){
        $page = $request->json()->get('page'); if (($page == null) || ($page == '')) { $page = 1; }
		$rows = $request->json()->get('rows'); if (($rows == null) || ($rows == '')) { $rows = Utility::DEFAULT_LIMIT(); }
        $args = json_encode($request->json()->get('args'));
        $offset = ($page-1)*$rows;

        // Editable Class
        $data = TUsers::getList($args, $offset, $rows);
        $dataRec = TUsers::getCount($args)[0]->cnt;
        $dataCount = count($data);
        // return $data;
        return Utility::setResponseExT(200, '', $data, intval($page), $dataRec, $dataCount, $rows);
        return Utility::setResponseExT($request, 200, '', $data, intval($page), $dataRec, $dataCount, $rows);
    }

    function auth(Request $request){
        $FieldNames = array(
            'username' => 'username',
            'password' => 'password'
        );
        $this->validate($request, [
            'username'  => 'required',
            'password'  => 'required'
        ], [], $FieldNames);

        $login = TUsers::getuserLogin($request['username']);
        if(!$login){
            $status = array(
                "error" => true,
                "message"=>"user not found!",
                "code" => 505
            );
            $result['status'] = $status;
            return response()->json($result);
        }

        if(Utility::Encryption($request['password']) != $login[0]->password) {
            $status = array(
                "error" => true,
                "message" =>  "These Password does not match!",
                "code" => 505
            );
            $result["status"] = $status;
            return response()->json($result, 200);
        }
        $status = array(
            "error" => false,
            "message"=>"Login success!",
            "code" => 200
        );
        $meta = array(
            "more" => false,
            // "token" =>  Utility::newToken($request['user'], $login[0]->idbranch, $login[0]->idgroup),
            "token" =>  Utility::newToken($request['user'], 02, 01),
            "timestamp" => date("Y-m-d H:i:s",time()),
            "cursor" => array (
                "count_rec" => 0,
                "count_data" => 0,
                "count_page" => 0,
                "curr_page" => 0,
                "next_page" => 0
            )
        );
        $result["status"] = $status;
        $result["data"] = TUsers::getUserLogin($request['username'])[0];
        $result["meta"] = $meta;
        return response()->json($result);
    }

    function create(Request $request){
        $username = $request->json()->get('username');
        $email = $request->json()->get('email');
        $password =  Utility::Encryption($request->json()->get('password'));
        $args = array(
            "src" => $username
        );
        $userRole = VUserRole::getList(json_encode($args), 0, 1);
        $users = TUsers::getList(json_encode($args), 0, 1);
        if($users) {
            return Utility::setResponseExT(502, 'User Found!', $userRole, intval(1), 1,1,1);
        } else {
            $newID = Utility::newID();
            $newRoleID = Utility::newID();
            $roleID = TRoles::getRole($request->json()->get('role'))[0]->id;

            $params = array (
                "id" => $newID,
                "username" => $username,
                "email" => $email,
                "password" => $password
            );

            $userRoleParams = array(
                "id" => $newRoleID,
                "roleid" => $roleID,
                "userid" => $newID
            );

            DB::begintransaction();
                $resJson = TUsers::createEx(json_encode($params));
                $resJson = VUserRole::createEx(json_encode($userRoleParams));
            DB::commit();
            return Utility::setResponseExT(200, 'User Created!', $userRole, intval(1), 1,1,1);
        }

    }
}

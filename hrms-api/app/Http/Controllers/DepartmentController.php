<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Utilities\Utility;

// Declare All Used Models
use App\Http\Models\Department;
use App\Http\Models\Division;
use App\Http\Models\RoleOrganization;
use App\Http\Models\securityRole;
use stdClass;

class DepartmentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {

    }

    function listAll(Request $request) {
		$page = $request->json()->get('page'); if (($page == null) || ($page == '')) { $page = 1; }
		$rows = $request->json()->get('rows'); if (($rows == null) || ($rows == '')) { $rows = Utility::DEFAULT_LIMIT(); }
        $args = json_encode($request->json()->get('args'));
        $offset = ($page-1)*$rows;

        // Editable Class
        $data = Department::getList($args, $offset, $rows);
        $dataRec = Department::getCount($args)[0]->cnt;
        $dataCount = count($data);

        return Utility::setResponseExT(200, '', $data, intval($page), $dataRec, $dataCount, $rows);
    }

    function create(Request $request){
        if($request->json()->get('id') == "") {
            $newID = Utility::newID();
            $params = array (
                "id" => "$newID",
                "strname" => $request->json()->get('strname'),
                "head" => $request->json()->get('head'),
                "division" => $request->json()->get('division')

            );
            $resJSON = Department::createEx(json_encode($params));
        } else {
            $newID = $request->json()->get('id');
            $params = array (
                "id" => "$newID",
                "strname" => $request->json()->get('strname'),
                "head" => $request->json()->get('head'),
                'division' => $request->json()->get('division')
            );
            $resJSON = Department::updateEx(json_encode($params));
        }

        $object = json_decode($resJSON);
        $resArray = Department::getList(Utility::setArgs($newID), 0, 1);

        return Utility::setResponse($request, $object->code, $object->message, $resArray, 0, 0, 0, 0);
    }

    // function create(Request $request){
    //
    //     if($request['strname'] ==""){return Utility::setResponseExT(901, "Filed Nama harus diisi!", []);}
    //     if($request['division'] ==""){return Utility::setResponseExT(901, "Filed Divisi harus diisi!", []);}
    //
    //     $newID = Utility::newID();
    //     $params = array (
    //         "id" => "$newID",
    //         'strname' => $request->json()->get('strname'),
    //         'head' => $request->json()->get('head'),
    //         'division' => $request->json()->get('division')
    //     );
    //
    //     $resJSON = Department::createEx(json_encode($params));
    //     $object = json_decode($resJSON);
    //     $resArray = Department::getList(Utility::setArgs($newID), 0, 1);
    //     return
    //     Utility::setResponseexT($object->code, $object->message, $resArray, 0, 0, 0, 0);
    // }
    //
    // function update(Request $request){
    //
    //     if($request['strname'] ==""){return Utility::setResponseExT(901, "Filed Nama harus diisi!", []);}
    //     if($request['division'] ==""){return Utility::setResponseExT(901, "Filed Divisi harus diisi!", []);}
    //
    //     $newID = $request['id'];
    //     $params = array (
    //         "id" => "$newID",
    //         'strname' => $request->json()->get('strname'),
    //         'head' => $request->json()->get('head'),
    //         'division' => $request->json()->get('division')
    //     );
    //
    //     $resJSON = Department::updateEx(json_encode($params));
    //     $object = json_decode($resJSON);
    //     $resArray = Department::getList(Utility::setArgs($newID), 0, 1);
    //     return Utility::setResponseexT($object->code, $object->message, $resArray, 0, 0, 0, 0);
    // }

    function delete(Request $request) {
        // Editable Class
        $params = array (
            "id" => $request->json()->get('id')
        );
        $resJSON = Department::deleteEx(json_encode($params));
        $object = json_decode($resJSON);

        return Utility::setResponse($request, $object->code, $object->message, array(), 0, 0, 0, 0);
    }

    /**
     * Get Department and Division by securityRoles.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    function getDepartmentDivisionBySecurityRoles(Request $request)
    {
        $args = $request->json()->get('args');
        $divisionId = isset($args['divisionId']) ? $args['divisionId'] : '';
        $result = array();

        if (empty($args)): return Utility::setResponseExT(400, 'securityRoles and businessUnitId required.', ''); endif;
        if (empty($args['securityRoles'])): return Utility::setResponseExT(400, 'securityRoles required.', ''); endif;
        if (empty($args['businessUnitId'])): return Utility::setResponseExT(400, 'businessUnitId required.', ''); endif;

        foreach ($args['securityRoles'] as $securityRole)
        {
            if ($securityRole['businessUnitId'] == $args['businessUnitId'])
            {
                $roleOrganization = RoleOrganization::getRoleOrganizationById($securityRole['roleOrganizationId']);
                $department = "";
                $division = "";

                if ($divisionId != "" || !empty($divisionId)) {
                    if ($divisionId === $roleOrganization->idDivision) {
                        $department = Department::getDepartmentById($roleOrganization->idDept);
                        $division = Division::getDivisionById($divisionId);
                        $departmentDivisionObject = new stdClass;
                    $departmentDivisionObject->department = $department;
                    $departmentDivisionObject->division = $division;
                    array_push($result, $departmentDivisionObject);
                    }
                } else {
                    $department = Department::getDepartmentById($roleOrganization->idDept);
                    $division = Division::getDivisionById($roleOrganization->idDivision);
                    $departmentDivisionObject = new stdClass;
                    $departmentDivisionObject->department = $department;
                    $departmentDivisionObject->division = $division;
                    array_push($result, $departmentDivisionObject);
                }
            }
        }

        return Utility::setResponseExT(200, 'Department and devision retrieved successfully.', $result);
    }
}

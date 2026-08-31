<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Models\AccessRole;
use App\Http\Models\Feature;
use App\Http\Models\RoleOrganization;
use App\Utilities\Utility;

// Declare All Used Models
use App\Http\Models\EmployeeEdu;

class EmployeeEduController extends Controller
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
        $data = EmployeeEdu::getList($args, $offset, $rows);
        $dataRec = EmployeeEdu::getCount($args)[0]->cnt;
        $dataCount = count($data);

        return Utility::setResponseExt( 200, '', $data, intval($page), $dataRec, $dataCount, $rows);
    }

    function create(Request $request){
        // validation
        if($request['institutionname'] == ""): return Utility::setResponseExT(901, "Field Institusi Pendidikan harus diisi!", []); endif;
        if($request['degree'] ==""): return Utility::setResponseExT(901, "Field Jenjang Pendidikan harus diisi!", []); endif;

        if ($request->json()->get('id') == "") {
            $newID = Utility::newID();
            $params = array (
                "id" => "$newID",
                'institutionname' => $request->json()->get('institutionname'),
                'major' => $request->json()->get('major'),
                'degree' => $request->json()->get('degree'),
                'location' => $request->json()->get('location'),
                'startyear' => $request->json()->get('startyear'),
                'endyear' => $request->json()->get('endyear'),
                'employee' => $request->json()->get('employee')
            );
            $resJSON = EmployeeEdu::createEx(json_encode($params));
        } else {
            $newID = $request->json()->get('id');
            $lastD = EmployeeEdu::getList(Utility::setArgs($newID), 0, 1);
            $params = array (
                "id" => $newID,
                'institutionname' => $request->json()->get('institutionname'),
                'major' => $request->json()->get('major'),
                'degree' => $request->json()->get('degree'),
                'location' => $request->json()->get('location'),
                'startyear' => $request->json()->get('startyear'),
                'endyear' => $request->json()->get('endyear'),
                'employee' => $request->json()->get('employee')
            );
            $resJSON = EmployeeEdu::updateEx(json_encode($params));
        }
        $object = json_decode($resJSON);
        $resArray = EmployeeEdu::getList(Utility::setArgs($newID), 0, 1);

        return Utility::setResponse($request, $object->code, $object->message, $resArray, 0, 0, 0, 0);
    }

    function delete(Request $request) {
        // Editable Class
        $params = array (
            "id" => $request->json()->get('id')
        );
        $resJSON = EmployeeEdu::deleteEx(json_encode($params));
        $object = json_decode($resJSON);

        return Utility::setResponse($request, $object->code, $object->message, array(), 0, 0, 0, 0);
    }

    /**
     * Check if user with specified role can access a feature.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    function canAccess(Request $request)
    {
        $roleId = $request->json()->get('roleId');
        $featureCode = $request->json()->get('featureCode');

        if (! isset($roleId))
        {
            return Utility::setResponseExt(400, 'roleId required.', '');
        }

        if (! isset($featureCode))
        {
            return Utility::setResponseExt(400, 'featureCode required.', '');
        }

        $featureId = Feature::getFeatureByFeatureCode($featureCode)->id;
        $result = AccessRole::getCanAccess($roleId, $featureId);
        return Utility::setResponseExt(200, 'Access retrieved succesfully.', $result);
    }

    /**
     * Check if user with multiple securityRole can update or delete
     * an employee or data with certain businessUnit.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    function canUpdateDelete(Request $request)
    {
        $employeeBusinessId = $request->json()->get('businessUnitId');
        $securityRoles = $request->json()->get('securityRoles');
        $featureCode = $request->json()->get('featureCode');

        if (! isset($employeeBusinessId))
        {
            return Utility::setResponseExt(400, 'businessUnitId required.', '');
        }

        if (! isset($securityRoles))
        {
            return Utility::setResponseExt(400, 'securityRoles required.', '');
        }

        if (! isset($featureCode))
        {
            return Utility::setResponseExt(400, 'featureCode required.', '');
        }

        foreach ($securityRoles as $securityRole)
        {
            if ($employeeBusinessId === $securityRole['businessUnitId'])
            {
                $roleId = RoleOrganization::getRoleOrganizationById($securityRole['roleOrganizationId'])->idRole;
                $featureId = Feature::getFeatureByFeatureCode($featureCode)->id;
                $result = AccessRole::getAccessRoleByRoleFeature($roleId, $featureId);
                return Utility::setResponseExT(200, 'Permissions retrieved successfully.', $result);
            }
        }
    }
}

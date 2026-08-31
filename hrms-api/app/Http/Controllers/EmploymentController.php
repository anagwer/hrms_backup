<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Utilities\Utility;
use Illuminate\Support\Facades\Hash;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\File;

// Declare All Used Models
use App\Http\Models\Employment;
use App\Models\Employee;
use App\Models\EmployeeOrganization;
use App\Models\Log;
use App\Notifications\ResetPinNotification;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class EmploymentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        ini_set('memory_limit', -1);
    }

    function detailByToken(Request $request)
    {
        // $params['employee'] = $request->creds->id;
        $data = Employment::getByID($request->creds->id);
        return response()->json([
            'success' => true,
            'message' => 'Get record success',
            'data' => ($data)
        ], 201);
    }

    function detailByTokenTicket(Request $request)
    {
        $data = Employment::getByIDTicket($request->creds->id);
        return response()->json([
            'success' => true,
            'message' => 'Get record success',
            'data' => ($data)
        ], 201);
    }

    /**
     * Get employee by nik. This method used in login form to check if user is exists, then show the strname.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    function detailByNik(Request $request)
    {
        $nik = $request->json()->get('nik');

        if (!isset($nik) || $nik == NULL) : return Utility::setResponseExT(400, 'nik required.', '');
        endif;

        $employee = Employment::getUser($nik);

        if ($employee) {
            return Utility::setResponseExT(200, 'Employee details retrieved successfully.', $employee[0], 0, 0);
        }

        return Utility::setResponseExT(901, 'Employee not found.', $employee, 0, 0);
    }

    function listAll(Request $request)
    {
        $page = $request->json()->get('page');
        if (($page == null) || ($page == '')) {
            $page = 1;
        }
        $rows = $request->json()->get('rows');
        if (($rows == null) || ($rows == '')) {
            $rows = Utility::DEFAULT_LIMIT();
        }
        $args = json_encode($request->json()->get('args'));
        $offset = ($page - 1) * $rows;

        if (empty(json_decode($args))) {
            return Utility::setResponseExT(400, 'securityRoles or businessunit, department, and division required.', '');
        }

        if (empty(json_decode($args)->securityRoles)) {
            return Utility::setResponseExT(400, 'args -> securityRoles required.', '');
        }

        $data = Employment::getListBySecurityRoles($args, $offset, $rows);
        $dataRec = Employment::getCountBySecurityRoles($args);
        $dataCount = count($data);

        return Utility::setResponseExT(200, 'Employee retrieved successfully.', $data, intval($page), $dataRec, $dataCount, $rows);
    }

    function listAllTicket(Request $request)
    {
        $page = $request->json()->get('page');
        if (($page == null) || ($page == '')) {
            $page = 1;
        }
        $rows = $request->json()->get('rows');
        if (($rows == null) || ($rows == '')) {
            $rows = Utility::DEFAULT_LIMIT();
        }
        $args = json_encode($request->json()->get('args'));
        $offset = ($page - 1) * $rows;

        $data = Employment::getListForTicket($args, $offset, $rows);
        $dataRec = Employment::getCountListForTicket($args);
        $dataCount = count($data);

        return Utility::setResponseExT(200, 'Employee retrieved successfully.', $data, intval($page), $dataRec, $dataCount, $rows);
    }


    function create(Request $request)
    {
        // validation, check if required fields is empty
        if ($request['fingerprintid'] == "") : return Utility::setResponseExT(901, "Field FingerprintID harus diisi!", []);
        endif;
        if ($request['nik'] == "") : return Utility::setResponseExT(901, "Field NIK harus diisi!", []);
        endif;
        if ($request['strname'] == "") :    return Utility::setResponseExT(901, "Field Nama harus diisi!", []);
        endif;
        if ($request['datebirth'] == "") : return Utility::setResponseExT(901, "Field Tgl. Lahir harus diisi!", []);
        endif;
        if ($request['birthplace'] == "") : return Utility::setResponseExT(901, "Field Tempat Lahir harus diisi!", []);
        endif;
        if ($request['gender'] == "") : return Utility::setResponseExT(901, "Field Jenis Kelamin harus diisi!", []);
        endif;
        if ($request['religion'] == "") : return Utility::setResponseExT(901, "Field Agama harus diisi!", []);
        endif;
        if ($request['address'] == "") : return Utility::setResponseExT(901, "Field Alamat harus diisi!", []);
        endif;
        if ($request['city'] == "") : return Utility::setResponseExT(901, "Field Alamat Kota harus diisi!", []);
        endif;
        if ($request['province'] == "") : return Utility::setResponseExT(901, "Field Proinsi harus diisi!", []);
        endif;
        if ($request['citizenidnumber'] == "") : return Utility::setResponseExT(901, "Field No. KTP harus diisi!", []);
        endif;
        if ($request['familycardnumber'] == "") : return Utility::setResponseExT(901, "Field No. Kartu Keluarga harus diisi!", []);
        endif;
        if ($request['maritalstate'] == "") : return Utility::setResponseExT(901, "Field Status Perkawinan harus diisi!", []);
        endif;
        if ($request['primaryphonenumber'] == "") : return Utility::setResponseExT(901, "Field No. Telpon Utama harus diisi!", []);
        endif;
        if ($request['contactname'] == "") : return Utility::setResponseExT(901, "Field Nama Kontak harus diisi!", []);
        endif;
        if ($request['contactrelation'] == "") : return Utility::setResponseExT(901, "Field Relasi Kontak harus diisi!", []);
        endif;
        if ($request['fathername'] == "") : return Utility::setResponseExT(901, "Field Nama Ayah harus diisi!", []);
        endif;
        if ($request['mothername'] == "") : return Utility::setResponseExT(901, "Field Nama Ibu harus diisi!", []);
        endif;
        if ($request['contactaddress'] == "") : return Utility::setResponseExT(901, "Field Nama Kontak harus diisi!", []);
        endif;
        if ($request['bankname'] == "") : return Utility::setResponseExT(901, "Field Nama Bank harus diisi!", []);
        endif;
        if ($request['accountnumber'] == "") : return Utility::setResponseExT(901, "Field No. Rekening harus diisi!", []);
        endif;
        if ($request['bankaccountholder'] == "") : return Utility::setResponseExT(901, "Field Nama Rekening harus diisi!", []);
        endif;
        if ($request['joindate'] == "") : return Utility::setResponseExT(901, "Field Tgl. Join harus diisi!", []);
        endif;
        if ($request['lasteducation'] == "") : return Utility::setResponseExT(901, "Field Pendidikan Terakhir harus diisi!", []);
        endif;
        if ($request['businessunit'] == "") : return Utility::setResponseExT(901, "Field Unit Bisnis harus diisi!", []);
        endif;
        if ($request['division'] == "") : return Utility::setResponseExT(901, "Field Divisi harus diisi!", []);
        endif;
        if ($request['department'] == "") : return Utility::setResponseExT(901, "Field Departemen harus diisi!", []);
        endif;
        if ($request['contractstatus'] == "") : return Utility::setResponseExT(901, "Field Status Kepegawaian harus diisi!", []);
        endif;
        if ($request['shiftcategory'] == "") : return Utility::setResponseExT(901, "Field Shift harus diisi!", []);
        endif;
        if ($request['offday'] == "") : return Utility::setResponseExT(901, "Field Hari Libur harus diisi!", []);
        endif;
        if ($request['repeatdays'] == "") : return Utility::setResponseExT(901, "Field Repeat days harus diisi!", []);
        endif;
        if ($request['job'] == "") : return Utility::setResponseExT(901, "Field Jabatan harus diisi!", []);
        endif;
        if ($request['grade'] == "") : return Utility::setResponseExT(901, "Field Grade harus diisi!", []);
        endif;
        if ($request['role'] == "") : return Utility::setResponseExT(901, "Field Role harus diisi!", []);
        endif;
        if ($request['usemobileapps'] == "") : return Utility::setResponseExT(901, "Field Use mobile apps harus diisi!", []);
        endif;

        $datebirth = date("Y/m/d", strtotime($request->json()->get('datebirth')));
        $pinNumber = Hash::make(date("dmy", strtotime($request->json()->get('datebirth'))));
        $joindate = date("Y/m/d", strtotime($request->json()->get('joindate')));

        // check if employee age less than 15 years old
        if (date_diff(date_create($datebirth), date_create('now'))->format('%R%y') < 15) {
            return Utility::setResponseExT(901, "Karyawan harus berumur 15 tahun atau lebih per hari ini", []);
        }

        if ($request->json()->get('id') == "") {

            if ($request['strpassword'] == "") : return Utility::setResponseExT(901, "Field Password harus diisi!", []);
            endif;

            $empByCitizenID = Employment::getByKTP($request['citizenidnumber']);
            if ($empByCitizenID) {
                return Utility::setResponseExT(901, "Karyawan perdah terdaftar!", []);
            }

            $empByFamilyCard = Employment::getByKK($request['familycardnumber']);
            if ($empByFamilyCard) {
                return Utility::setResponseExT(901, "Nomor KK Sudah Terdaftar, Karyawan Memiliki Hubungan Kandung!", []);
            }

            $empByFatherAndMotherName = Employment::getByFatherAndMotherName($request['fathername'], $request['mothername']);
            if ($empByFatherAndMotherName) {
                return Utility::setResponseExT(901, "Nama Ayah dan Ibu Memiliki Kesamaan, Pastikan Kembali Apakah Karyawan Memiliki Hubungan Kandung", []);
            }

            $nik = $request->json()->get('nik');
            $args = array(
                "nik" => $nik
            );

            $emp = Employment::getList(json_encode($args), 0, 1);
            if ($emp == null) {

                $newID = Utility::newID();
                $linesID = Utility::newID();
                $params = array(
                    "id" => $newID,
                    "fingerprintid" => $request->json()->get('fingerprintid'),
                    "nik" => $request->json()->get('nik'),
                    "strname" => $request->json()->get('strname'),
                    "joindate" => $joindate,
                    "datebirth" => $datebirth,
                    "birthplace" => $request->json()->get('birthplace'),
                    "gender" => $request->json()->get('gender'),
                    "religion" => $request->json()->get('religion'),
                    "maritalstate" => $request->json()->get('maritalstate'),
                    "address" => $request->json()->get('address'),
                    "kelurahan" => $request->json()->get('kelurahan'),
                    "kecamatan" => $request->json()->get('kecamatan'),
                    "city" => $request->json()->get('city'),
                    "postalcode" => $request->json()->get('postalcode'),
                    "province" => $request->json()->get('province'),
                    "citizenidnumber" => $request->json()->get('citizenidnumber'),
                    "familycardnumber" => $request->json()->get('familycardnumber'),
                    "primaryphonenumber" => $request->json()->get('primaryphonenumber'),
                    "secondphonenumber" => $request->json()->get('secondphonenumber'),
                    "email" => $request->json()->get('email'),
                    "bankname" => $request->json()->get('bankname'),
                    "accountnumber" => $request->json()->get('accountnumber'),
                    "bankaccountholder" => $request->json()->get('bankaccountholder'),
                    "basicsalary" => $request->json()->get('basicsalary'),
                    "bpjskesehatannumber" => $request->json()->get('bpjskesehatannumber'),
                    "bpjstknumber" => $request->json()->get('bpjstknumber'),
                    "taxnumber" => $request->json()->get('taxnumber'),
                    "ptkp" => $request->json()->get('ptkp'),
                    "lasteducation" => $request->json()->get('lasteducation'),
                    "majors" => $request->json()->get('majors'),
                    "photo" => $request->json()->get('photo'),
                    "title" => $request->json()->get('title'),
                    "reports_to" => $request->json()->get('reports_to'),
                    "usemobileapps" => $request->json()->get('usemobileapps'),
                    "strpassword" => MD5($request->json()->get('strpassword')),
                    "password" => Hash::make($request->json()->get('strpassword')),
                    "fathername" => $request->json()->get('fathername'),
                    "mothername" => $request->json()->get('mothername'),
                    "contactname" => $request->json()->get('contactname'),
                    "contactaddress" => $request->json()->get('contactaddress'),
                    "contactpostalcode" => $request->json()->get('contactpostalcode'),
                    "contactcity" => $request->json()->get('contactcity'),
                    "contactrelation" => $request->json()->get('contactrelation'),
                    "contactphone" => $request->json()->get('contactphone'),
                    "residentalphone" => $request->json()->get('residentalphone'),
                    "firstudf" => $request->json()->get('firstudf'),
                    "secondudf" => $request->json()->get('secondudf'),
                    "thirdudf" => $request->json()->get('thirdudf'),
                    "fourthudf" => $request->json()->get('fourthudf'),
                    "linesid" => $linesID,
                    "employee" => $newID,
                    "businessunit" => $request->json()->get('businessunit'),
                    "branch" => $request->json()->get('branch'),
                    "plant" => $request->json()->get('plant'),
                    "pool" => $request->json()->get('pool'),
                    "division" => $request->json()->get('division'),
                    "department" => $request->json()->get('department'),
                    "job" => $request->json()->get('job'),
                    "grade" => $request->json()->get('grade'),
                    "contractstatus" => $request->json()->get('contractstatus'),
                    "shiftcategory" => $request->json()->get('shiftcategory'),
                    "defaultlocation" => $request->json()->get('defaultlocation'),
                    "offday" => $request->json()->get('offday'),
                    "repeatdays" => $request->json()->get('repeatdays'),
                    "role" => $request->json()->get('role'),
                    "activedate" => $joindate,
                    "period" => $request->json()->get('period'),
                    "enddate" => $request->json()->get('enddate'),
                    "state" => 0,
                    "createdby" => $request->json()->get('createdby'),
                    "pin" => $pinNumber

                );
                $resJSON = employment::createEx(json_encode($params));
            } else {
                return Utility::setResponseExT(901, "NIK Sudah Terdaftar!", []);
            }
        } else {
            $newID = $request->json()->get('id');
            $linesID = $request->json()->get('linesid');
            $nik = $request->json()->get('nik');
            $citizenidnumber = $request->json()->get('citizenidnumber');

            $args = array(
                "nik" => $nik
            );

            $emp = Employment::getList(json_encode($args), 0, 1);
            if ($emp == null) { // tidak ada employee yang menggunakan nik yang diinput

                // proses update dengan nik yang diinput
                $params = array(
                    "id" => $newID,
                    "fingerprintid" => $request->json()->get('fingerprintid'),
                    "nik" => $request->json()->get('nik'),
                    "strname" => $request->json()->get('strname'),
                    "joindate" => $joindate,
                    "datebirth" => $datebirth,
                    "birthplace" => $request->json()->get('birthplace'),
                    "gender" => $request->json()->get('gender'),
                    "religion" => $request->json()->get('religion'),
                    "maritalstate" => $request->json()->get('maritalstate'),
                    "address" => $request->json()->get('address'),
                    "kelurahan" => $request->json()->get('kelurahan'),
                    "kecamatan" => $request->json()->get('kecamatan'),
                    "city" => $request->json()->get('city'),
                    "postalcode" => $request->json()->get('postalcode'),
                    "province" => $request->json()->get('province'),
                    "citizenidnumber" => $request->json()->get('citizenidnumber'),
                    "familycardnumber" => $request->json()->get('familycardnumber'),
                    "primaryphonenumber" => $request->json()->get('primaryphonenumber'),
                    "secondphonenumber" => $request->json()->get('secondphonenumber'),
                    "email" => $request->json()->get('email'),
                    "bankname" => $request->json()->get('bankname'),
                    "accountnumber" => $request->json()->get('accountnumber'),
                    "bankaccountholder" => $request->json()->get('bankaccountholder'),
                    "basicsalary" => $request->json()->get('basicsalary'),
                    "bpjskesehatannumber" => $request->json()->get('bpjskesehatannumber'),
                    "bpjstknumber" => $request->json()->get('bpjstknumber'),
                    "taxnumber" => $request->json()->get('taxnumber'),
                    "ptkp" => $request->json()->get('ptkp'),
                    "lasteducation" => $request->json()->get('lasteducation'),
                    "majors" => $request->json()->get('majors'),
                    "photo" => $request->json()->get('photo'),
                    "title" => $request->json()->get('title'),
                    "reports_to" => $request->json()->get('reports_to'),
                    "usemobileapps" => $request->json()->get('usemobileapps'),
                    "fathername" => $request->json()->get('fathername'),
                    "mothername" => $request->json()->get('mothername'),
                    "contactname" => $request->json()->get('contactname'),
                    "contactaddress" => $request->json()->get('contactaddress'),
                    "contactpostalcode" => $request->json()->get('contactpostalcode'),
                    "contactcity" => $request->json()->get('contactcity'),
                    "contactrelation" => $request->json()->get('contactrelation'),
                    "contactphone" => $request->json()->get('contactphone'),
                    "residentalphone" => $request->json()->get('residentalphone'),
                    "firstudf" => $request->json()->get('firstudf'),
                    "secondudf" => $request->json()->get('secondudf'),
                    "thirdudf" => $request->json()->get('thirdudf'),
                    "fourthudf" => $request->json()->get('fourthudf'),
                    "linesid" => $linesID,
                    "employee" => $newID,
                    "businessunit" => $request->json()->get('businessunit'),
                    "branch" => $request->json()->get('branch'),
                    "plant" => $request->json()->get('plant'),
                    "pool" => $request->json()->get('pool'),
                    "division" => $request->json()->get('division'),
                    "department" => $request->json()->get('department'),
                    "job" => $request->json()->get('job'),
                    "grade" => $request->json()->get('grade'),
                    "contractstatus" => $request->json()->get('contractstatus'),
                    "shiftcategory" => $request->json()->get('shiftcategory'),
                    "defaultlocation" => $request->json()->get('defaultlocation'),
                    "offday" => $request->json()->get('offday'),
                    "repeatdays" => $request->json()->get('repeatdays'),
                    "role" => $request->json()->get('role'),
                    "activedate" => $joindate,
                    "period" => $request->json()->get('period'),
                    "enddate" => $request->json()->get('enddate')
                );

                if ($request->json()->get('strpassword') != '') {
                    array_push($params, [
                        "strpassword" => MD5($request->json()->get('strpassword')),
                        "password" => Hash::make($request->json()->get('strpassword')),
                    ]);
                }

                $resJSON = employment::updateEx(json_encode($params));
                // end proses update

            } else { // sudah ada employee yang menggunakan nik yang diinput, perlu dicek, apakah employee yang sama atau tidak
                if ($emp[0]->id == $newID) { // jika employee yang sama
                    // proses update
                    $params = array(
                        "id" => $newID,
                        "fingerprintid" => $request->json()->get('fingerprintid'),
                        "nik" => $request->json()->get('nik'),
                        "strname" => $request->json()->get('strname'),
                        "joindate" => $joindate,
                        "datebirth" => $datebirth,
                        "birthplace" => $request->json()->get('birthplace'),
                        "gender" => $request->json()->get('gender'),
                        "religion" => $request->json()->get('religion'),
                        "maritalstate" => $request->json()->get('maritalstate'),
                        "address" => $request->json()->get('address'),
                        "kelurahan" => $request->json()->get('kelurahan'),
                        "kecamatan" => $request->json()->get('kecamatan'),
                        "city" => $request->json()->get('city'),
                        "postalcode" => $request->json()->get('postalcode'),
                        "province" => $request->json()->get('province'),
                        "citizenidnumber" => $request->json()->get('citizenidnumber'),
                        "familycardnumber" => $request->json()->get('familycardnumber'),
                        "primaryphonenumber" => $request->json()->get('primaryphonenumber'),
                        "secondphonenumber" => $request->json()->get('secondphonenumber'),
                        "email" => $request->json()->get('email'),
                        "bankname" => $request->json()->get('bankname'),
                        "accountnumber" => $request->json()->get('accountnumber'),
                        "bankaccountholder" => $request->json()->get('bankaccountholder'),
                        "basicsalary" => $request->json()->get('basicsalary'),
                        "bpjskesehatannumber" => $request->json()->get('bpjskesehatannumber'),
                        "bpjstknumber" => $request->json()->get('bpjstknumber'),
                        "taxnumber" => $request->json()->get('taxnumber'),
                        "ptkp" => $request->json()->get('ptkp'),
                        "lasteducation" => $request->json()->get('lasteducation'),
                        "majors" => $request->json()->get('majors'),
                        "photo" => $request->json()->get('photo'),
                        "title" => $request->json()->get('title'),
                        "reports_to" => $request->json()->get('reports_to'),
                        "usemobileapps" => $request->json()->get('usemobileapps'),
                        "fathername" => $request->json()->get('fathername'),
                        "mothername" => $request->json()->get('mothername'),
                        "contactname" => $request->json()->get('contactname'),
                        "contactaddress" => $request->json()->get('contactaddress'),
                        "contactpostalcode" => $request->json()->get('contactpostalcode'),
                        "contactcity" => $request->json()->get('contactcity'),
                        "contactrelation" => $request->json()->get('contactrelation'),
                        "contactphone" => $request->json()->get('contactphone'),
                        "residentalphone" => $request->json()->get('residentalphone'),
                        "firstudf" => $request->json()->get('firstudf'),
                        "secondudf" => $request->json()->get('secondudf'),
                        "thirdudf" => $request->json()->get('thirdudf'),
                        "fourthudf" => $request->json()->get('fourthudf'),
                        "linesid" => $linesID,
                        "employee" => $newID,
                        "businessunit" => $request->json()->get('businessunit'),
                        "branch" => $request->json()->get('branch'),
                        "plant" => $request->json()->get('plant'),
                        "pool" => $request->json()->get('pool'),
                        "division" => $request->json()->get('division'),
                        "department" => $request->json()->get('department'),
                        "job" => $request->json()->get('job'),
                        "grade" => $request->json()->get('grade'),
                        "contractstatus" => $request->json()->get('contractstatus'),

                        "shiftcategory" => $request->json()->get('shiftcategory'),
                        "defaultlocation" => $request->json()->get('defaultlocation'),
                        "offday" => $request->json()->get('offday'),
                        "repeatdays" => $request->json()->get('repeatdays'),

                        "role" => $request->json()->get('role'),
                        "activedate" => $joindate,
                        "period" => $request->json()->get('period'),
                        "enddate" => $request->json()->get('enddate')
                    );

                    if ($request->json()->get('strpassword')) {
                        array_push($params, [
                            "strpassword" => MD5($request->json()->get('strpassword')),
                            "password" => Hash::make($request->json()->get('strpassword')),
                        ]);
                    }

                    $resJSON = employment::updateEx(json_encode($params));
                    // end proses update

                } else { // jika sudah ada yang menggunakan nik yang diinput
                    return Utility::setResponseExT(901, "NIK sudah digunakan karyawan lain, gunakan NIK lainnya!", []);
                }
            }
        }

        $object = json_decode($resJSON);
        $resArray = employment::getList(Utility::setArgs($newID), 0, 1);
        return Utility::setResponse($request, $object->code, $object->message, $resArray, 0, 0, 0, 0);
    }

    function createForTicket(Request $request)
    {
        // return $request;
        if ($request['nik'] == "") {
            return Utility::setResponseExT(901, "Field NIK harus diisi!", []);
        }
        if ($request['strname'] == "") {
            return Utility::setResponseExT(901, "Field Nama harus diisi!", []);
        }
        if ($request['businessunitid'] == "") {
            return Utility::setResponseExT(901, "Field Unit Bisnis harus diisi!", []);
        }
        if ($request['divisionid'] == "") {
            return Utility::setResponseExT(901, "Field Divisi harus diisi!", []);
        }
        if ($request['departmentid'] == "") {
            return Utility::setResponseExT(901, "Field Departemen harus diisi!", []);
        }
        if ($request['roleid'] == "") {
            return Utility::setResponseExT(901, "Field Role harus diisi!", []);
        }
        if ($request['strpassword'] == "") {
            return Utility::setResponseExT(901, "Field Role harus diisi!", []);
        }

        if ($request->json()->get('id') == "") {
            $nik = $request->json()->get('nik');
            $args = array(
                "nik" => $nik
            );

            $emp = Employment::getListForTicket(json_encode($args), 0, 1);
            if ($emp == null) {
                $newID = Utility::newID();
                $params = array(
                    "id" => $newID,
                    "nik" => $request->json()->get('nik'),
                    "strname" => $request->json()->get('strname'),
                    "businessunitid" => $request->json()->get('businessunitid'),
                    "divisionid" => $request->json()->get('divisionid'),
                    "departmentid" => $request->json()->get('departmentid'),
                    "roleid" => $request->json()->get('roleid'),
                    "strpassword" => Utility::Encryption($request->json()->get('strpassword')),
                );
                $resJSON = employment::createExTicket(json_encode($params));
            } else {
                return Utility::setResponseExT(901, "NIK sudah digunakan karyawan lain, gunakan NIK lainnya!", []);
            }
        }

        $object = json_decode($resJSON);
        $resArray = employment::getListForTicket(Utility::setArgs($newID), 0, 1);
        return Utility::setResponse($request, $object->code, $object->message, $resArray, 0, 0, 0, 0);
    }


    function delete(Request $request)
    {
        $newID = $request['id'];
        $employee = employment::getByID($newID);
        if (!$employee) {
            return Utility::setResponseExT(901, "Employee tidak ditemukan!", []);
        }
        if ($employee[0]->employmentstate != 0) {
            return Utility::setResponseExT(901, "Employee tidak dapat dihapus!", []);
        }
        $params = array(
            "id" => $newID
        );
        $resJSON = employment::deleteEx(json_encode($params));
        $object = json_decode($resJSON);

        return Utility::setResponse($request, $object->code, $object->message, array(), 0, 0, 0, 0);
    }

    function deleteEmployeeTicket(Request $request)
    {
        $newID = $request['id'];

        $params = array(
            "id" => $newID
        );
        $resJSON = employment::deleteExTicket(json_encode($params));
        $object = json_decode($resJSON);

        return Utility::setResponse($request, $object->code, $object->message, array(), 0, 0, 0, 0);
    }

    function setPassword(Request $request)
    {

        if ($request['strpassword'] == "") {
            return Utility::setResponseExT(901, "Field Password harus diisi!", []);
        }
        $nik = $request['nik'];
        $employee = employment::getUser($nik);
        if (!$employee) {
            return Utility::setResponseExT(901, "Employee tidak ditemukan!", []);
        }
        if ($employee[0]->employmentstate == 9) {
            return Utility::setResponseExT(901, "Password Employee sudah tidak dapat diubah!", $employee);
        }

        $params = array(
            "nik" => "$nik",
            'strpassword' => Utility::Encryption($request->json()->get('strpassword')),
            'passwordstate' => 1
        );

        $resJSON = employment::setPasswordEx(json_encode($params));
        $object = json_decode($resJSON);

        $args = array(
            "nik" => $nik
        );

        $resArray = employment::getList(json_encode($args), 0, 1);
        return Utility::setResponse($request, $object->code, $object->message, $resArray, 0, 0, 0, 0);
    }

    function listEmployeeContract(Request $request)
    {
        $params = array();
        $params['contractstatus'] = 'CONTRACT';
        $page = $request->json()->get('page');
        if (($page == null) || ($page == '')) {
            $page = 1;
        }
        $rows = $request->json()->get('rows');
        if (($rows == null) || ($rows == '')) {
            $rows = Utility::DEFAULT_LIMIT();
        }
        // $args = json_encode($request->json()->get('args'));
        $offset = ($page - 1) * $rows;

        // Editable Class
        $data = employment::getListNonPermanent($params, $offset, $rows);
        $dataRec = employment::getCountNonPermanent($params)->count;
        $dataCount = count($data);

        return Utility::setResponseExT(200, '', $data, intval($page), $dataRec, $dataCount, $rows);
    }

    function listEmployeeTraining(Request $request)
    {
        $params = array();
        $params['contractstatus'] = 'TRAINING';
        $page = $request->json()->get('page');
        if (($page == null) || ($page == '')) {
            $page = 1;
        }
        $rows = $request->json()->get('rows');
        if (($rows == null) || ($rows == '')) {
            $rows = Utility::DEFAULT_LIMIT();
        }
        // $args = json_encode($request->json()->get('args'));
        $offset = ($page - 1) * $rows;

        // Editable Class
        $data = employment::getListNonPermanent($params, $offset, $rows);
        $dataRec = employment::getCountNonPermanent($params)->count;
        $dataCount = count($data);

        return Utility::setResponseExT(200, '', $data, intval($page), $dataRec, $dataCount, $rows);
    }

    function deactive(Request $request)
    {
        if ($request['id'] == "") {
            return Utility::setResponseExT(901, "ID Employee wajib diisi", []);
        }
        if ($request['deactive'] == "") {
            return Utility::setResponseExT(901, "Deactive Employee wajib diisi", []);
        }
        $id = $request->json()->get('id');
        $data = Employment::getByID($id);
        if (!$data) {
            return Utility::setResponseExT(901, "ID Employee tidak ditemukan", []);
        }
        // $search['id'] = $request['id'];
        // $data = Employment::getList(($search), 1,1);
        // if($data->isEmpty()) {return Utility::setResponseExT(901, "ID Employee tidak ditemukan", []);}
        if ($data[0]->state != 0) {
            return Utility::setResponseExT(901, "Employee sudah di " . $data[0]->statestr, []);
        }
        // $current_time = Carbon::now()->toDateTimeString();

        switch ($request['deactive']) {
            case 1:
                $temp = array(
                    "id" => $request['id'],
                    "state" => 9,
                    // "approvalby" => $request->creds->id,
                    // "approvaldatetime" => $current_time
                );
                break;
            case 9:
                $temp = array(
                    "id" => $request['id'],
                    "state" => 9
                    // "approvalby" => $request->creds->id,
                    // "approvaldatetime" => $current_time
                );
                break;
            default:
                return Utility::setResponseExT(901, "Terdapat kesalahan pada field Deactive Employee", []);
                break;
        }

        $resJSON = Employment::deactiveEmployee($temp);
        // echo $resJSON;
        return Utility::setResponse($request, $resJSON['code'], $resJSON['message'], array(), 0, 0, 0, 0);
    }

    /**
     * Used to activate an employee after registered/created. Employee can't login before activated.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    function activate(Request $request)
    {
        $employeeId = $request->json()->get('employeeId');

        if (!isset($employeeId) || $employeeId == NULL) : return Utility::setResponseExT(400, 'employeeId required.', '');
        endif;

        $result = Employment::activate($employeeId);

        return Utility::setResponseExT($result['code'], $result['message'], [], 0, 0);
    }

    /**
     * Created for print employee's ID Card.
     */
    public function getIDCardInfo($nik)
    {
        $employee = Employee::where('nik', preg_replace('/((?<=[a-zA-Z]{2}))/', '.', $nik))
            ->with('employeeOrganization.job', 'employeeOrganization.businessUnit', 'employeeOrganization.department')->firstOrFail();
        $employee->setVisible(['nik', 'strname', 'primaryphonenumber', 'email', 'employeeOrganization', 'gender', 'photo', 'title']);
        $employee->employeeOrganization->setVisible(['job', 'businessUnit', 'department']);
        return $employee;
    }

    public function pinVerification(Request $request)
    {
        $this->validate($request, [
            'pin' => 'required'
        ]);

        try {
            $data = Employee::findOrFail($request->creds->id, 'pin');
            if (!Hash::check($request->pin, $data->pin)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nomor pin salah.',
                    'data' => (null)
                ], 422);
            }
            return response()->json([
                'success' => true,
                'message' => 'Nomor pin benar.',
                'data' => ([])
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $ex) {
            return response()->json([
                'success' => true,
                'message' => $ex->getMessage(),
                'data' => (null)
            ], 404);
        }
    }

    public function changePin(Request $request)
    {
        $this->validate($request, [
            'old_pin' => 'required',
            'new_pin' => 'required|confirmed|min:6',
        ]);

        try {
            $user = Employee::findOrFail($request->creds->id);
            if (!Hash::check($request->old_pin, $user->pin)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pin Lama Salah.',
                    'data' => (null)
                ], 422);
            }
            $user->update(['pin' => Hash::make($request->new_pin)]);
            return response()->json([
                'success' => true,
                'message' => 'Update PIN Success',
                'data' => ([])
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $ex) {
            return response()->json([
                'success' => true,
                'message' => $ex->getMessage(),
                'data' => (null)
            ], 404);
        }
    }

    public function resetPin(Request $request)
    {
        try {
            $this->validate($request, ['password' => 'required'], [
                'required' => ':attribute dibutuhkan'
            ]);

            $user = Employee::findOrFail($request->creds->id);

            if ($user->strpassword != md5($request->password)) {
                throw new Exception('Password tidak cocok');
            }

            if (!$user->email) {
                throw new Exception('Email belum diatur.', 500);
            }

            DB::beginTransaction();

            $randomPin = random_int(101011, 999999);
            $user->pin = Hash::make($randomPin);
            $user->save();

            Notification::route('mail', [$user->email => $user->strname])->notify(new ResetPinNotification($user->strname, $randomPin));

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Reset PIN Sukses.  Silahkan cek email Anda.  Jika tidak ada, pastikan juga untuk melihat kotak spam.',
                'data' => ([])
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $error) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $error->getMessage(),
                'data' => $error->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $ex) {
            DB::rollBack();
            return response()->json([
                'success' => true,
                'message' => $ex->getMessage(),
                'data' => (null)
            ], 404);
        } catch (\Exception $error) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $error->getMessage(),
                'data' => (null)
            ], 500);
        }
    }

    public function resetPinAllEmployee()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '0');
        try {
            $user = Employee::all()->whereNull('pin');
            foreach ($user as $value) {
                $defaultPin = \Carbon\Carbon::parse($value->datebirth)->format('dmy');
                $value->update(['pin' => Hash::make($defaultPin)]);
            }
            return response()->json([
                'success' => true,
                'message' => 'Reset PIN Success',
                'data' => ([])
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $ex) {
            return response()->json([
                'success' => true,
                'message' => $ex->getMessage(),
                'data' => (null)
            ], 404);
        }
    }

    public function downloadQrCode(Request $request)
    {
        $this->validate($request, [
            'single' => 'numeric',
            'nik' => 'required_with:single'
        ]);

        $url = $request->url ?? 'https://hrms.sinarjoyoboyo.com/id-card/';
        $single = $request->single ?? 0;

        try {
            if ($single == 0) {
                $employees = EmployeeOrganization::with(['employeeParent', 'businessUnit'])
                    ->when($request->businessunit, function ($query) use ($request) {
                        $query->where('businessunit', $request->businessunit);
                    })
                    ->when($request->division, function ($query) use ($request) {
                        $query->where('division', $request->division);
                    })
                    ->when($request->department, function ($query) use ($request) {
                        $query->where('department', $request->department);
                    })
                    ->when($request->status, function ($query) use ($request) {
                        $query->where('state', $request->status);
                    })
                    ->when($request->search, function ($query) use ($request) {
                        $query->whereHas('employeeParent', function ($query) use ($request) {
                            $query->where('strname', 'LIKE', '%' . $request->search . '%');
                        });
                    })
                    ->get();
            } else {
                $employees = EmployeeOrganization::with(['employeeParent', 'businessUnit'])
                    ->whereHas('employeeParent', function ($query) use ($request) {
                        $query->where('nik', $request->nik);
                    })->get();
            }

            $zip = new \ZipArchive();
            $filename = 'employee_qrcode.zip';
            $filepath = storage_path('app' . DIRECTORY_SEPARATOR . $filename);

            // clean temp directory and previous .zip file
            $file = new \Illuminate\Filesystem\FileSystem;
            $file->delete($filepath);
            $file->cleanDirectory(storage_path('temp'));

            foreach ($employees as $employee) {
                $path = storage_path('temp' . DIRECTORY_SEPARATOR . $this->_parseNik($employee->employeeParent->nik) . '.png');
                QrCode::format('png')
                    ->size(300)
                    ->margin(2)
                    ->errorCorrection('H')
                    ->merge($this->_getLogoPath($employee->businessUnit->strname), .3)
                    ->generate($url . $this->_parseNik($employee->employeeParent->nik), $path);
            }

            if ($zip->open($filepath, \ZipArchive::CREATE == TRUE)) {
                $files = File::files(storage_path('temp'));
                foreach ($files as $key => $value) {
                    $relativeName = basename($value);
                    $zip->addFile($value, $relativeName);
                }
                $zip->close();
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }

        return response()->download($filepath);
    }

    private function _parseNik($nik)
    {
        return strtolower(str_replace('.', '', $nik));
    }

    private function _getLogoPath($businessUnit)
    {
        $businessUnit = strtolower($businessUnit);
        $s = DIRECTORY_SEPARATOR;
        switch ($businessUnit) {
            case 'lintas nusantara':
            case 'palmas prima indonesia':
            case 'lidah buaya':
            case 'koperasi mandiri sejahtera':
            case 'thermopak':
                return "${s}storage${s}app${s}logo${s}thermopak.jpg";
            default:
                return "${s}storage${s}app${s}logo${s}sinar_joyoboyo.jpg";
        }
    }

    public function resetDeviceId(Request $request, $employeeId)
    {
        try {
            $this->validate($request, ['detail' => 'required|string']);

            DB::beginTransaction();
            $employee = Employee::findOrFail($employeeId);
            $employee->device_id = null;
            $employee->save();

            $log = new Log(['detail' => $request->detail]);
            $log->type = 'RESET_DEVICE_ID';
            $log->employee_id = $employeeId;
            $log->save();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Reset Device ID Success.',
                'data' => ([])
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $error) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $error->getMessage(),
                'data' => $error->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $error) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $error->getMessage(),
                'data' => (null)
            ], 404);
        } catch (\Exception $error) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $error->getMessage(),
                'data' => (null)
            ], 500);
        }
    }

    public function updateEmail(Request $request)
    {
        try {
            $this->validate($request, ['email' => 'required|email|unique:App\Models\Employee'], [
                'required' => ':attribute dibutuhkan',
                'unique' => ':attribute sudah digunakan orang lain'
            ]);

            DB::beginTransaction();
            $employee = Employee::findOrFail($request->creds->id);
            $employee->email = $request->email;
            $employee->save();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Successfully update email.',
                'data' => ([])
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $error) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $error->getMessage(),
                'data' => $error->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $error) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $error->getMessage(),
                'data' => (null)
            ], 404);
        } catch (\Exception $error) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $error->getMessage(),
                'data' => (null)
            ], 500);
        }
    }
}

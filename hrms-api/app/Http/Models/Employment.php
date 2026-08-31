<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use PDOException;

class Employment extends Model
{
    public static function getByKTP($citizenidnumber)
    {
        $stmt = DB::select(
            "SELECT *
            FROM employment
            WHERE citizenidnumber = '$citizenidnumber'"
        );
        return $stmt;
    }

    public static function getByKK($familycardnumber)
    {
        $stmt = DB::select(
            "SELECT *
            FROM employment
            WHERE familycardnumber = '$familycardnumber'"
        );
        return $stmt;
    }

    public static function getByFatherAndMotherName($fathername, $mothername)
    {
        $stmt = DB::select(
            "SELECT *
            FROM employment
            WHERE fathername = '$fathername' and mothername = '$mothername'"
        );
        return $stmt;
    }

    public static function getUser($nik)
    {
        $stmt = DB::select(
            "SELECT *
            FROM employmentwp
            WHERE nik = '$nik'"
        );
        return $stmt;
    }

    public static function getUserForTicket($nik)
    {
        $stmt = DB::select(
            "SELECT *
            FROM employeeticketview
            WHERE nik = '$nik'"
        );
        return $stmt;
    }

    //update to employmentwp
    public static function getByID($id)
    {
        $stmt = DB::select(
            "SELECT *
            FROM employmentwp
            WHERE id = '$id'"
        );
        return $stmt;
    }

    /**
     * similar to function getByID but this function lookup from employment view.
     *
     * @param int $id
     */
    public static function getEmployeeByID($id)
    {
        $stmt = DB::select(
            "SELECT *
            FROM employment
            WHERE id = '$id'"
        );
        return $stmt;
    }

    public static function getByIDTicket($id)
    {
        $stmt = DB::select(
            "SELECT *
            FROM employeeticketview
            WHERE id = '$id'"
        );
        return $stmt;
    }

    /**
     * Get list of employee
     *
     * @param string $args
     * @param int $offset
     * @param int $rec
     * @return array
     */
    public static function getList($args, $rec, $offset)
    {
        $params = json_decode($args);
        $filter = "";
        $where = "";

        if (isset($params->businessunit) && ($params->businessunit != "")) {
            $filter = $filter . " AND (businessunitid = '$params->businessunit')";
        }

        if (isset($params->division) && ($params->division != "")) {
            $filter = $filter . " AND (divisionid = '$params->division')";
        }

        if (isset($params->department) && ($params->department != "")) {
            $filter = $filter . " AND (departmentid = '$params->department')";
        }

        if (isset($params->nik) && ($params->nik != "")) {
            $filter = $filter . " AND (nik = '$params->nik')";
        }

        if (isset($params->status) && ($params->status != "")) {
            $filter = $filter . " AND (state = '$params->status')";
        } else {
            $filter = $filter . " AND (state != 9 )";
        }

        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " AND (strname like '%$params->src%')";
        }

        $stmt = DB::select(
            "SELECT *
            FROM employment
            WHERE id is not null {$filter} {$where}
            ORDER BY nik
            LIMIT :offset, :rec",
            [
                'offset' => $offset,
                'rec' => $rec
            ]
        );
        return $stmt;
    }

    public static function getListForTicket($args, $offset, $rec)
    {
        $params = json_decode($args);
        $where = "";

        if (isset($params->nik) && ($params->nik != "")) {
            $where = $where . " AND (nik = '$params->nik')";
        }

        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " AND (strname like '%$params->src%')";
        }

        $stmt = DB::select(
            "SELECT *
            FROM employment
            WHERE id is not null {$where}
            ORDER BY nik
            LIMIT :offset, :rec",
            [
                'offset' => $offset,
                'rec' => $rec
            ]
        );
        return $stmt;
    }

    public static function getCountListForTicket($args)
    {
        $params = json_decode($args);
        $where = "";

        $stmt = DB::select(
            "SELECT count(nik) total
            FROM employment"
        );
        return $stmt[0]->total;
    }

    /**
     * Get list of employee with certain businessUnitIds. (filtered by array of securityRoles)
     *
     * @param string $args
     * @param int $offset
     * @param int $rec
     * @return array
     */
    public static function getListBySecurityRoles($args, $offset, $rec)
    {
        $params = json_decode($args);
        $filter = "";
        $where = "";

        $departmentIds = array();
        $divisionIds = array();
        $businessUnitIds = array();

        // get user businessunit, division, and department by user security roles.
        foreach ($params->securityRoles as $securityRole) {
            $department = RoleOrganization::getRoleOrganizationById($securityRole->roleOrganizationId);
            array_push($businessUnitIds, $securityRole->businessUnitId);
            array_push($departmentIds, $department->idDept);
            array_push($divisionIds, $department->idDivision);
        }

        // if filter by department is empty, then filter by user department from security roles
        if ((!isset($params->department)) || ($params->department == "")) {
            if (!empty($departmentIds)) {
                $filter = $filter . " AND (departmentid IN ('" . join("', '", $departmentIds) . "'))";
            }
        } else {
            $filter = $filter . " AND (departmentid = '$params->department')";
        }

        // if filter by division is empty, then filter by user division from security roles
        if ((!isset($params->division)) || ($params->division == "")) {
            if (!empty($divisionIds)) {
                $filter = $filter . " AND (divisionid IN ('" . join("', '", $divisionIds) . "'))";
            }
        } else {
            $filter = $filter . " AND (divisionid = '$params->division')";
        }

        // if filter by businessunit is empty, then filter by user businessunit from security roles
        if ((!isset($params->businessunit)) || ($params->businessunit == "")) {
            if (!empty($businessUnitIds)) {
                $filter = $filter . " AND (businessunitid IN ('" . join("', '", $businessUnitIds) . "'))";
            }
        } else {
            $filter = $filter . " AND (businessunitid = '$params->businessunit')";
        }

        if (isset($params->nik) && ($params->nik != "")) {
            $filter = $filter . " AND (nik = '$params->nik')";
        }

        if (isset($params->status) && ($params->status != "")) {
            $filter = $filter . " AND (state = '$params->status')";
        } else {
            $filter = $filter . " AND (state != 9 )";
        }
        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " AND (nik like '%$params->src%')";
        }
        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " OR (strname like '%$params->src%')";
        }


        $stmt = DB::select(
            "SELECT *
            FROM employment
            WHERE id is not null {$filter} {$where}
            ORDER BY nik
            LIMIT :offset, :rec",
            [
                'offset' => $offset,
                'rec' => $rec
            ]
        );
        return $stmt;
    }

    /**
     * Get count of employee with certain businessUnitIds. (filtered by array of securityRoles)
     *
     * @param string $args
     * @return int
     */
    public static function getCountBySecurityRoles($args)
    {
        $params = json_decode($args);
        $filter = "";
        $where = "";

        $departmentIds = array();
        $divisionIds = array();
        $businessUnitIds = array();

        // get user businessunit, division, and department by user security roles.
        foreach ($params->securityRoles as $securityRole) {
            $department = RoleOrganization::getRoleOrganizationById($securityRole->roleOrganizationId);
            array_push($businessUnitIds, $securityRole->businessUnitId);
            array_push($departmentIds, $department->idDept);
            array_push($divisionIds, $department->idDivision);
        }

        // if filter by department is empty, then filter by user department from security roles
        if ((!isset($params->department)) || ($params->department == "")) {
            if (!empty($departmentIds)) {
                $filter = $filter . " AND (departmentid IN ('" . join("', '", $departmentIds) . "'))";
            }
        } else {
            $filter = $filter . " AND (departmentid = '$params->department')";
        }

        // if filter by division is empty, then filter by user division from security roles
        if ((!isset($params->division)) || ($params->division == "")) {
            if (!empty($divisionIds)) {
                $filter = $filter . " AND (divisionid IN ('" . join("', '", $divisionIds) . "'))";
            }
        } else {
            $filter = $filter . " AND (divisionid = '$params->division')";
        }

        // if filter by businessunit is empty, then filter by user businessunit from security roles
        if ((!isset($params->businessunit)) || ($params->businessunit == "")) {
            if (!empty($businessUnitIds)) {
                $filter = $filter . " AND (businessunitid IN ('" . join("', '", $businessUnitIds) . "'))";
            }
        } else {
            $filter = $filter . " AND (businessunitid = '$params->businessunit')";
        }

        if (isset($params->nik) && ($params->nik != "")) {
            $filter = $filter . " AND (nik = '$params->nik')";
        }

        if (isset($params->status) && ($params->status != "")) {
            $filter = $filter . " AND (state = '$params->status')";
        } else {
            $filter = $filter . " AND (state != 9 )";
        }

        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " AND (strname like '%$params->src%')";
        }

        $stmt = DB::select(
            "SELECT count(nik) total
            FROM employment
            WHERE id is not null {$filter} {$where}
            ORDER BY nik"
        );
        return $stmt[0]->total;
    }

    public static function createEx($params)
    {
        $input = json_decode($params);
        DB::beginTransaction();
        try {
            DB::insert(
                "INSERT INTO employee
                        (id,  fingerprintid,  nik,  strname,  joindate,  datebirth,  birthplace,  gender,  religion,  maritalstate,  address, kelurahan, kecamatan, city,  postalcode,  province,
                        citizenidnumber, familycardnumber,  primaryphonenumber,  secondphonenumber,  email,  bankname,  accountnumber, bankaccountholder,  basicsalary,  bpjskesehatannumber,  bpjstknumber,  taxnumber,  ptkp,
                        lasteducation, majors, photo, title, reports_to,
                        usemobileapps,  strpassword, password, pin, fathername, mothername, contactname,  contactaddress,  contactpostalcode,  contactcity,  contactrelation,  contactphone,  residentalphone,
                        firstudf,  secondudf,  thirdudf,  fourthudf)
                    VALUES
                        (:id, :fingerprintid, :nik, :strname, :joindate, :datebirth, :birthplace, :gender, :religion, :maritalstate, :address, :kelurahan, :kecamatan, :city, :postalcode, :province,
                        :citizenidnumber, :familycardnumber, :primaryphonenumber, :secondphonenumber, :email, :bankname, :accountnumber, :bankaccountholder, :basicsalary, :bpjskesehatannumber, :bpjstknumber, :taxnumber, :ptkp,
                        :lasteducation, :majors, :photo, :title, :reports_to,
                        :usemobileapps, :strpassword, :password, :pin, :fathername, :mothername, :contactname, :contactaddress, :contactpostalcode, :contactcity, :contactrelation, :contactphone, :residentalphone,
                        :firstudf, :secondudf, :thirdudf, :fourthudf)",
                [
                    "id" => $input->id,
                    "fingerprintid" => $input->fingerprintid,
                    "nik" => $input->nik,
                    "strname" => $input->strname,
                    "joindate" => $input->joindate,
                    "datebirth" => $input->datebirth,
                    "birthplace" => $input->birthplace,
                    "gender" => $input->gender,
                    "religion" => $input->religion,
                    "maritalstate" => $input->maritalstate,
                    "address" => $input->address,
                    "kelurahan" => $input->kelurahan,
                    "kecamatan" => $input->kecamatan,
                    "city" => $input->city,
                    "postalcode" => $input->postalcode,
                    "province" => $input->province,
                    "citizenidnumber" => $input->citizenidnumber,
                    "familycardnumber" => $input->familycardnumber,
                    "primaryphonenumber" => $input->primaryphonenumber,
                    "secondphonenumber" => $input->secondphonenumber,
                    "email" => $input->email,
                    "bankname" => $input->bankname,
                    "accountnumber" => $input->accountnumber,
                    "bankaccountholder" => $input->bankaccountholder,
                    "basicsalary" => $input->basicsalary,
                    "bpjskesehatannumber" => $input->bpjskesehatannumber,
                    "bpjstknumber" => $input->bpjstknumber,
                    "taxnumber" => $input->taxnumber,
                    "ptkp" => $input->ptkp,
                    "lasteducation" => $input->lasteducation,
                    "majors" => $input->majors,
                    "photo" => $input->photo,
                    "title" => $input->title, // this field should be in emporganization. Just follow Jira IC-22
                    "reports_to" => $input->reports_to,
                    "usemobileapps" => $input->usemobileapps,
                    "strpassword" => $input->strpassword,
                    "password" => $input->password,
                    "pin" => $input->pin,
                    "fathername" => $input->fathername,
                    "mothername" => $input->mothername,
                    "contactname" => $input->contactname,
                    "contactaddress" => $input->contactaddress,
                    "contactpostalcode" => $input->contactpostalcode,
                    "contactcity" => $input->contactcity,
                    "contactrelation" => $input->contactrelation,
                    "contactphone" => $input->contactphone,
                    "residentalphone" => $input->residentalphone,
                    "firstudf" => $input->firstudf,
                    "secondudf" => $input->secondudf,
                    "thirdudf" => $input->thirdudf,
                    "fourthudf" => $input->fourthudf
                ]
            );

            DB::insert(
                "INSERT INTO emporganization
                    (id, employee, businessunit, branch, plant, pool, division, defaultlocation, department, job, grade, contractstatus,
                    shiftcategory, offday, repeatdays,
                    role, activedate, period, enddate, state, createdby)
                    VALUES(:id, :employee, :businessunit, :branch, :plant, :pool, :division, :defaultlocation, :department, :job, :grade, :contractstatus,
                    :shiftcategory, :offday, :repeatdays,
                    :role, :activedate, :period, :enddate, :state, :createdby)",
                [
                    "id" => $input->linesid,
                    "employee" => $input->employee,
                    "businessunit" => $input->businessunit,
                    "branch" => $input->branch,
                    "plant" => $input->plant,
                    "pool" => $input->pool,
                    "division" => $input->division,
                    "department" => $input->department,
                    "job" => $input->job,
                    "grade" => $input->grade,
                    "contractstatus" => $input->contractstatus,
                    "shiftcategory" => $input->shiftcategory,
                    "defaultlocation" => $input->defaultlocation,
                    "offday" => $input->offday,
                    "repeatdays" => $input->repeatdays,
                    "role" => $input->role,
                    "activedate" => $input->activedate,
                    "period" => $input->period,
                    "enddate" => $input->enddate,
                    "state" => $input->state,
                    "createdby" => $input->createdby
                ]
            );
            DB::commit();

            $status = array(
                "error" => false,
                "message" =>  "",
                "code" => 200
            );
        } catch (PDOException $e) {
            DB::rollBack();
            $status = array(
                "error" => true,
                "message" =>  $e->getMessage(),
                "code" => 405
            );
        }
        return json_encode($status);
    }


    public static function createExTicket($params)
    {
        $input = json_decode($params);
        DB::beginTransaction();
        try {
            DB::insert(
                "INSERT INTO employeeticket
                        (id, nik,  strname, roleid, businessunitid , departmentid, divisionid, strpassword)
                    VALUES
                        (:id, :nik, :strname,  :roleid, :businessunitid, :departmentid, :divisionid, :strpassword)",
                [
                    "id" => $input->id,
                    "nik" => $input->nik,
                    "strname" => $input->strname,
                    "roleid" => $input->roleid,
                    "businessunitid" => $input->businessunitid,
                    "departmentid" => $input->departmentid,
                    "divisionid" => $input->divisionid,
                    "strpassword" => $input->strpassword
                ]
            );

            DB::commit();

            $status = array(
                "error" => false,
                "message" =>  "",
                "code" => 200
            );
        } catch (PDOException $e) {
            DB::rollBack();
            $status = array(
                "error" => true,
                "message" =>  $e->getMessage(),
                "code" => 405
            );
        }
        return json_encode($status);
    }

    public static function updateEx($params)
    {
        $input = json_decode($params);
        DB::beginTransaction();
        try {
            $vars = [
                "id" => $input->id,
                "fingerprintid" => $input->fingerprintid,
                "nik" => $input->nik,
                "strname" => $input->strname,
                "joindate" => $input->joindate,
                "datebirth" => $input->datebirth,
                "birthplace" => $input->birthplace,
                "gender" => $input->gender,
                "religion" => $input->religion,
                "maritalstate" => $input->maritalstate,
                "address" => $input->address,
                "kelurahan" => $input->kelurahan,
                "kecamatan" => $input->kecamatan,
                "city" => $input->city,
                "postalcode" => $input->postalcode,
                "province" => $input->province,
                "citizenidnumber" => $input->citizenidnumber,
                "familycardnumber" => $input->familycardnumber,
                "primaryphonenumber" => $input->primaryphonenumber,
                "secondphonenumber" => $input->secondphonenumber,
                "email" => $input->email,
                "bankname" => $input->bankname,
                "accountnumber" => $input->accountnumber,
                "bankaccountholder" => $input->bankaccountholder,
                "basicsalary" => $input->basicsalary,
                "bpjskesehatannumber" => $input->bpjskesehatannumber,
                "bpjstknumber" => $input->bpjstknumber,
                "taxnumber" => $input->taxnumber,
                "ptkp" => $input->ptkp,
                "lasteducation" => $input->lasteducation,
                "majors" => $input->majors,
                "photo" => $input->photo,
                "title" => $input->title, // this field should be in emporganization. Just follow Jira IC-22
                "reports_to" => $input->reports_to,
                "usemobileapps" => $input->usemobileapps,
                "fathername" => $input->fathername,
                "mothername" => $input->mothername,
                "contactname" => $input->contactname,
                "contactaddress" => $input->contactaddress,
                "contactpostalcode" => $input->contactpostalcode,
                "contactcity" => $input->contactcity,
                "contactrelation" => $input->contactrelation,
                "contactphone" => $input->contactphone,
                "residentalphone" => $input->residentalphone,
                "firstudf" => $input->firstudf,
                "secondudf" => $input->secondudf,
                "thirdudf" => $input->thirdudf,
                "fourthudf" => $input->fourthudf
            ];

            if (isset($input->password) && isset($input->strpassword)){
                array_push($vars, [
                    "strpassword" => $input->strpassword,
                    "password" => $input->password,
                ]);
            }

            DB::update(
                "UPDATE employee SET
                    fingerprintid = :fingerprintid,
                    nik = :nik,
                    strname = :strname,
                    joindate = :joindate,
                    datebirth = :datebirth,
                    birthplace = :birthplace,
                    gender = :gender,
                    religion = :religion,
                    maritalstate = :maritalstate,
                    address = :address,
                    kelurahan = :kelurahan,
                    kecamatan = :kecamatan,
                    city = :city,
                    postalcode = :postalcode,
                    province = :province,
                    citizenidnumber = :citizenidnumber,
                    familycardnumber = :familycardnumber,
                    primaryphonenumber = :primaryphonenumber,
                    secondphonenumber = :secondphonenumber,
                    email = :email,
                    bankname = :bankname,
                    accountnumber = :accountnumber,
                    bankaccountholder = :bankaccountholder,
                    basicsalary = :basicsalary,
                    bpjskesehatannumber = :bpjskesehatannumber,
                    bpjstknumber = :bpjstknumber,
                    taxnumber = :taxnumber,
                    ptkp = :ptkp,
                    lasteducation = :lasteducation,
                    majors = :majors,
                    photo = :photo,
                    title = :title,
                    reports_to = :reports_to,
                    usemobileapps = :usemobileapps,
                    " . (isset($input->password) && isset($input->strpassword) ? "strpassword = :strpassword, password = :password," : "") . "
                    fathername = :fathername,
                    mothername = :mothername,
                    contactname = :contactname,
                    contactaddress = :contactaddress,
                    contactpostalcode = :contactpostalcode,
                    contactcity = :contactcity,
                    contactrelation = :contactrelation,
                    contactphone = :contactphone,
                    residentalphone = :residentalphone,
                    firstudf = :firstudf,
                    secondudf = :secondudf,
                    thirdudf = :thirdudf,
                    fourthudf = :fourthudf
                WHERE id = :id",
                $vars
            );

            DB::update(
                "UPDATE emporganization set
                employee = :employee,
                businessunit = :businessunit,
                branch = :branch,
                plant = :plant,
                pool = :pool,
                division = :division,
                department = :department,
                job = :job,
                grade = :grade,
                contractstatus = :contractstatus,
                shiftcategory = :shiftcategory,
                defaultlocation = :defaultlocation,
                offday = :offday,
                repeatdays = :repeatdays,
                role = :role,
                activedate = :activedate,
                period = :period,
                enddate = :enddate
                where id = :id",
                [
                    "id" => $input->linesid,
                    "employee" => $input->employee,
                    "businessunit" => $input->businessunit,
                    "branch" => $input->branch,
                    "plant" => $input->plant,
                    "pool" => $input->pool,
                    "division" => $input->division,
                    "department" => $input->department,
                    "job" => $input->job,
                    "grade" => $input->grade,
                    "contractstatus" => $input->contractstatus,
                    "shiftcategory" => $input->shiftcategory,
                    "defaultlocation" => $input->defaultlocation,
                    "offday" => $input->offday,
                    "repeatdays" => $input->repeatdays,
                    "role" => $input->role,
                    "activedate" => $input->activedate,
                    "period" => $input->period,
                    "enddate" => $input->enddate
                ]
            );

            DB::commit();
            $status = array(
                "error" => false,
                "message" =>  "",
                "code" => 200
            );
        } catch (PDOException $e) {

            DB::rollback();
            $status = array(
                "error" => true,
                "message" =>  $e->getMessage(),
                "code" => 405
            );
        }
        return json_encode($status);
    }

    public static function deleteExTicket($params)
    {
        $input = json_decode($params);
        DB::beginTransaction();
        try {
            DB::delete("DELETE FROM employeeticket WHERE id = :id", ['id' => $input->id]);

            $status = array(
                "error" => false,
                "message" =>  "",
                "code" => 200
            );
            DB::commit();
        } catch (PDOException $e) {
            $status = array(
                "error" => true,
                "message" =>  $e->getMessage(),
                "code" => 405
            );
            DB::rollback();
        }
        return json_encode($status);
    }

    public static function deleteEx($params)
    {
        $input = json_decode($params);
        DB::beginttransaction();
        try {
            DB::delete("DELETE FROM employee WHERE id = :id", ['id' => $input->id]);
            DB::delete("DELETE FROM emporganization WHERE employee = :id", ['id' => $input->id]);

            $status = array(
                "error" => false,
                "message" =>  "",
                "code" => 200
            );
            DB::commit();
        } catch (PDOException $e) {
            $status = array(
                "error" => true,
                "message" =>  $e->getMessage(),
                "code" => 405
            );
            DB::rollback();
        }
        return json_encode($status);
    }

    public static function setPasswordEx($params)
    {
        $input = json_decode($params);
        try {
            DB::update(
                "UPDATE employee SET
                    strpassword = :strpassword,
                    passwordstate = :passwordstate
                 WHERE nik = :nik",
                [
                    "nik" => $input->nik,
                    "strpassword" => $input->strpassword,
                    "passwordstate" => $input->passwordstate
                ]
            );
            $status = array(
                "error" => false,
                "message" =>  "",
                "code" => 200
            );
        } catch (PDOException $e) {
            $status = array(
                "error" => true,
                "message" =>  $e->getMessage(),
                "code" => 405
            );
        }
        return json_encode($status);
    }

    public static function employeeConfirmation($params)
    {
        $input = json_decode($params);
        try {
            DB::update(
                "UPDATE employee SET
                    strpassword = :strpassword,
                    passwordstate = :passwordstate
                 WHERE nik = :nik",
                [
                    "nik" => $input->nik,
                    "strpassword" => $input->strpassword,
                    "passwordstate" => $input->passwordstate
                ]
            );
            $status = array(
                "error" => false,
                "message" =>  "",
                "code" => 200
            );
        } catch (PDOException $e) {
            $status = array(
                "error" => true,
                "message" =>  $e->getMessage(),
                "code" => 405
            );
        }
        return json_encode($status);
    }

    public static function getListNonPermanent($params, $offset, $rows)
    {
        $data = DB::table('employmentwp')
            ->where($params)
            ->whereRaw('MONTH(NOW()) = MONTH(tanggal_notifikasi)')
            ->whereRaw('YEAR(NOW()) = YEAR(tanggal_notifikasi)')
            ->offset($offset)
            ->limit($rows)
            ->get();
        return $data;
    }

    public static function getCountNonPermanent($params)
    {
        $data = DB::table('employmentwp')
            ->where($params)
            ->whereRaw('MONTH(NOW()) = MONTH(tanggal_notifikasi)')
            ->whereRaw('YEAR(NOW()) = YEAR(tanggal_notifikasi)')
            ->select(DB::raw('count(*) as count'))
            ->first();
        return $data;
    }

    public static function deactiveEmployee($params)
    {
        // return json_encode($params);
        try {
            DB::table('employment')
                ->where('id', $params['id'])
                // ->where('approvalby', $params['approvalby'])
                // ->where('state', '=', $params['state'])
                ->update([
                    'state' => $params['state']
                    // 'approvalby' => $params['approvalby'],
                    // 'approvaldatetime' => date('Y-m-d')
                ]);
            $status = array(
                "error" => false,
                "message" =>  "",
                "code" => 200
            );
        } catch (PDOException $e) {
            $status = array(
                "error" => true,
                "message" =>  $e->getMessage(),
                "code" => 405
            );
        }
        return ($status);
    }

    /**
     * Activate an employee.
     *
     * @param string $employeeId
     * @return array
     */
    public static function activate($employeeId)
    {
        try {
            DB::beginTransaction();
            DB::table('employee')->where('id', $employeeId)->update(['employmentstate' => 1]);
            DB::table('emporganization')->where('employee', $employeeId)->update(['state' => 1]);
            DB::commit();
            return ['code' => 200, 'message' => 'Employee successfully activated.'];
        } catch (PDOException $error) {
            DB::rollBack();
            return ['code' => 405, 'message' => $error];
        }
    }

    public static function changePassword($employeeId, $newHashedPassword)
    {
        try {
            DB::beginTransaction();
            DB::table('employee')->where('id', $employeeId)->update(['strpassword' => $newHashedPassword]);
            DB::commit();
            return [
                'status' => [
                    'error' => false,
                    'code' => 200,
                    'message' => 'Ganti password berhasil.'
                ]
            ];
        } catch (PDOException $error) {
            DB::rollBack();
            return [
                'status' => [
                    'error' => true,
                    'code' => 405,
                    'message' => $error
                ]
            ];
        }
    }

    public static function updateLeave($params)
    {
        $input = json_decode($params);
        DB::beginTransaction();
        try {
            DB::update(
                "UPDATE employee SET
                    leavetotal = :leavetotal
                 WHERE id = :id",
                [
                    "id" => $input->id,
                    "leavetotal" => $input->leavetotal
                ]
            );
        } catch (PDOException $e) {
            DB::rollback();
        }
    }
}

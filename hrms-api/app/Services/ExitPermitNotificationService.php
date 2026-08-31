<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\ExitPermit;
use Kreait\Firebase\Messaging\Notification;

class ExitPermitNotificationService
{
    private FCMService $FCMService;

    private ExitPermit $exitPermit;

    private Employee $employee;

    private array $data;

    private Notification $notification;

    /**
     * @param App\Models\ExitPermit $exitPermit
     */
    public function __construct($exitPermit)
    {
        $this->FCMService = new FCMService();
        $this->exitPermit = $exitPermit;
        $this->employee = $exitPermit->employee()->first();
        $this->notification = Notification::create('Permintaan Sikel Karyawan', $this->employee->strname);
        $this->buildData();
    }

    public function notify()
    {
        if ($this->isManager()){
            if ($this->isPersonalConcern() || $this->isOutOfTown()){
                return $this->notifyToDirectHead();
            }

            return $this->notifyToHR();
        }

        if ($result = $this->notifyToDirectHead()) return $result;

        if ($result = $this->notifyToDepartmentHead()) return $result;

        if ($result = $this->notifyToHR()) return $result;

        return false;
    }

    private function buildData()
    {
        $this->data = [
            "click_action" => "FLUTTER_NOTIFICATION_CLICK",
            "type" => "sikel",
            "data" => json_encode([
                "docnumber" => $this->exitPermit->docnumber,
                "employeename" => $this->employee->strname,
                "jenissikel" => $this->getExitPermitTypeString(),
                "description" => $this->exitPermit->description,
                "state" => $this->exitPermit->state,
            ])
        ];
    }

    private function getExitPermitTypeString()
    {
        switch($this->exitPermit->sikeltype){
            case '0':
                return 'Keperluan Perusahaan (lokal)';
            case '1':
                return 'Keperluan Perusahaan (luar kota)';
            case '2':
                return 'Keperluan Pribadi';
            default:
                return 'Unknown exit permit type';
        }
    }

    /**
     * @return bool
     */
    private function notifyToDirectHead()
    {
        if ($this->employee->directHead){
            if ($token = $this->employee->directHead->messagingToken->token){
                return is_array($this->FCMService->sendMessage(
                    $this->data,
                    $token,
                    $this->notification
                ));
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    private function notifyToDepartmentHead()
    {
        if ($this->employee->department->departmentHead){
            if ($token = $this->employee->department->departmentHead->messagingToken->token){
                return is_array($this->FCMService->sendMessage(
                    $this->data,
                    $token,
                    $this->notification
                ));
            }
        }
        return false;
    }

    /**
     * @return void
     */
    private function notifyToHR()
    {
        $result = false;
        $receivers = Employee::with('messagingToken')
            ->whereHas('employeeOrganization', function($query){
                $query->whereHas('department', function ($query){
                    $query->where('strname', 'LIKE', 'HUMAN RESOURCE%');
                });
            })
            ->has('messagingToken')
            ->get();

        foreach ($receivers as $receiver){
            $res = $this->FCMService->sendMessage(
                $this->data,
                $receiver->messagingToken->token,
                $this->notification
            );
            if (!$result){
                $result = $res;
            }
        }

        return $result;
    }

    /**
     * Check if requestor employee is Manager
     *
     * @return bool
     */
    private function isManager()
    {
        return is_int(strpos($this->employee->role()->first(), 'MANAGER'));
    }

    /**
     * Check if exitPermit type is for personal or company concern.
     *
     * @return bool
     */
    private function isPersonalConcern()
    {
        return $this->exitPermit->sikelType == 2;
    }

    /**
     * Check if exitPermit type is on local area or out of town
     *
     * @return bool
     */
    private function isOutOfTown()
    {
        return $this->exitPermit->sikelType == 1;
    }
}

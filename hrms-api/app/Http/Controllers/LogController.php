<?php

namespace App\Http\Controllers;

use App\Models\ContactPerson;
use App\Models\Log;
use Exception;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function writeContactPersonLog(Request $request)
    {
        try {
            $this->validate($request, [
                'detail' => 'required|string',
            ]);

            $log = new Log(['detail' => $request->detail]);
            $log->type = 'CONTACT_PERSON_CLICKED';
            $log->employee_id = $request->creds->id;
            $log->save();
            return response()->json([
                'success' => true,
                'message' => 'Successfully write log.',
                'data' => $log
            ], 200);
        } catch (Exception $ex){
            return response()->json([
                'success' => false,
                'message' => $ex->getMessage(),
                'data' => ([])
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\FCMToken;
use Illuminate\Http\Request;

class FCMController extends Controller
{
    public function saveToken(Request $request)
    {
        try {
            $this->validate($request, ['token' => 'required|string']);
            $created = FCMToken::updateOrCreate(
                ['employee_id' => $request->creds->id],
                ['token' => $request->token]
            );
            return response()->json([
                'success' => true,
                'message' => 'Successfully save token',
                'data' => $created
            ], 200);
        } catch(\Illuminate\Validation\ValidationException $error){
            return response()->json([
                'success' => false,
                'message' => $error->getMessage(),
                'data' => $error->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $error){
            return response()->json([
                'success' => false,
                'message' => $error->getMessage(),
                'data' => (null)
            ], 404);
        } catch (\Exception $error){
            return response()->json([
                'success' => false,
                'message' => $error->getMessage(),
                'data' => (null)
            ], 500);
        }
    }
}

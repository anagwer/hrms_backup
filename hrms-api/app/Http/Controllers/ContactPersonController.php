<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Utilities\Utility;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

// Declare All Used Models
use App\Models\ContactPerson;

class ContactPersonController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|required',
            'phone_number' => 'string|required',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => Arr::flatten($validator->errors()->getMessages())
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Request Form is created',
            'data' => ContactPerson::create(
                $request->only(['name', 'phone_number'])
            )
        ], 200);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'string|required',
            'name' => 'string|required',
            'phone_number' => 'string|required',
        ]);
        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => Arr::flatten($validator->errors()->getMessages())
            ];
        }
        $contactPerson  = ContactPerson::findOrFail($request->id);
        return response()->json([
            'success' => $contactPerson->update($request->only(['name', 'phone_number'])),
            'message' => 'Contact person is updated',
            'data' => $request->all()
        ], 200);
    }

    public function delete($id)
    {
        $request  = ContactPerson::findOrFail($id);
        return response()->json([
            'success' => $request->delete(),
            'message' => 'Contact person is deleted'
        ], 200);
    }

    private function getContactFormCount()
    {
        $contactPersonCounted = ContactPerson::count();
        return $contactPersonCounted;
    }

    public function getContactPersonPaginated(Request $request)
    {
        $page = $request->has('page') ? $request->page : 1;
        $rows = $request->has('rows') ? $request->rows : 10;
        $offset = ($page - 1) * $rows;
        $contactPerson = ContactPerson::skip($offset)
            ->take($rows)
            ->orderBy('name')
            ->get();
        return Utility::setResponseExT(200, '', $contactPerson, intval($page), $this->getContactFormCount(), count($contactPerson), $rows);
    }
}

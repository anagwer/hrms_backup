<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Utilities\Utility;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

// Declare All Used Models
use App\Models\RequestFormDocument;

class RequestFormDocumentController extends Controller
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
            'business_unit_id' => 'string|required',
            'name' => 'string|required',
            'description' => 'string|required',
            'file_name' => 'string|required',
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
            'data' => RequestFormDocument::create(
                $request->only(['business_unit_id', 'name', 'description', 'file_name'])
            )
        ], 200);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'string|required',
            'business_unit_id' => 'string|required',
            'name' => 'string|required',
            'description' => 'string|required',
            'file_name' => 'string|required',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => Arr::flatten($validator->errors()->getMessages())
            ];
        }

        $requestFormDocument  = RequestFormDocument::findOrFail($request->id);
        return response()->json([
            'success' => $requestFormDocument->update($request->only(['business_unit_id', 'name', 'description', 'file_name'])),
            'message' => 'Request Form is updated',
            'data' => $request->all()
        ], 200);
    }

    public function delete($id)
    {
        $requestFormDocument  = RequestFormDocument::findOrFail($id);
        return response()->json([
            'success' => $requestFormDocument->delete(),
            'message' => 'Request Form is deleted'
        ], 200);
    }

    private function getRequestFormDocumentCount($request)
    {
        $requestFormDocumentCounted = RequestFormDocument::when($request->business_unit_id, function ($query) use ($request) {
            $query->where('business_unit_id', "$request->business_unit_id");
        })
            ->with([
                'business_unit:id,strname',
            ])->count();
        return $requestFormDocumentCounted;
    }

    public function getRequestFormDocumentPaginated(Request $request)
    {
        $this->validate($request, [
            'business_unit_id' => 'string'
        ]);

        $page = $request->has('page') ? $request->page : 1;
        $rows = $request->has('rows') ? $request->rows : 10;
        $offset = ($page - 1) * $rows;

        $requestFormDocuments = RequestFormDocument::when($request->business_unit_id, function ($query) use ($request) {
            $query->where('business_unit_id', "$request->business_unit_id");
        })
            ->with([
                'business_unit:id,strname',
            ])
            ->skip($offset)
            ->take($rows)
            ->orderBy('name')
            ->get();

        return Utility::setResponseExT(200, '', $requestFormDocuments, intval($page), $this->getRequestFormDocumentCount($request), count($requestFormDocuments), $rows);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Utilities\Utility;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

// Declare All Used Models
use App\Http\Models\Ticket;
use App\Http\Models\SequenceNumber;
use App\Models\Employee;
use App\Models\TicketNew;
use App\Services\JiraService;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    function getByID(Request $request)
    {
        $params = array(
            "id" => $request->json()->get('id')
        );
        $data = Ticket::getByID(json_encode($params));
        return Utility::setResponseExT(200, '', $data, intval(1), 1, 1, 1);
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

        // Editable Class
        $data = Ticket::getList($args, $offset, $rows);
        $dataRec = Ticket::getCount($args)[0]->cnt;
        $dataCount = count($data);

        return Utility::setResponseExT(200, '', $data, intval($page), $dataRec, $dataCount, $rows);
    }

    public function createTicket(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'requestor' => 'string|required',
            'businessunit' => 'string|required',
            'division' => 'string|required',
            'location' => 'string',
            'phone' => 'string',
            'description' => 'string|required',
            'ipaddress' => 'string',
            'tickettype' => 'string|required',
            'ticket_problem_type' => 'string|required',
            'regarding' => 'string|required',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => Arr::flatten($validator->errors()->getMessages())
            ];
        }

        $params = array('code' => 'ticket');

        $input = [
            'docno' => SequenceNumber::generateNumberEx(json_encode($params))[0]->docno,
            'requestor' => $request->requestor,
            'businessunit' => $request->businessunit,
            'division' => $request->division,
            'location' => $request->location,
            'phone' => $request->phone,
            'description' => $request->description,
            'ipaddress' => $request->ipaddress,
            'tickettype' => $request->tickettype,
            'ticket_problem_type' => $request->ticket_problem_type,
            'regarding' => $request->regarding,
        ];

        if ($request->attachment) {
            $input += ['attachment' => $request->attachment];
        }
        if ($request->createdby) {
            $input += ['createdby' => $request->createdby];
        }

        try {
            DB::beginTransaction();
            $createdTicket = TicketNew::create($input);

            $service = new JiraService($createdTicket);
            $jiraTicket = $service->create();

            $createdTicket->jira_id = $jiraTicket->get('id');
            $createdTicket->save();

            DB::commit();
            return response()->json([
                'success' => $createdTicket,
                'message' => 'Ticket is created',
                'data' => ($input)
            ], 200);
        } catch (\Exception $error) {
            DB::rollBack();
            return response()->json([
                'success' => true,
                'message' => $error->getMessage(),
                'data' => (null)
            ], 500);
        }
    }

    private function getTicketCount($request)
    {
        $ticketCounted = TicketNew::where('status', 'like', '%' . $request->state . '%')
            ->when($request->requestor_id, function ($query) use ($request) {
                $query->where('requestor', "$request->requestor_id");
            })
            ->with([
                'requestor:id,strname',
                'businessunit:id,strname',
                'division:id,strname',
                'tickettype.ticketProblemDivision',
                'ticketProblemTypeRelation.ticketProblemCategory'
            ])->when($request->search, function ($query) use ($request) {
                $query->whereHas('requestor', function ($query) use ($request) {
                    $query->where('docno', 'LIKE', "%$request->search%")
                        ->orWhere('strname', 'LIKE', "%$request->search%");
                });
            })->when($request->ticket_division, function ($query) use ($request) {
                $query->whereHas('tickettype.ticketProblemDivision', function ($query) use ($request) {
                    $query->where('id', "$request->ticket_division");
                });
            })
            ->count();
        return $ticketCounted;
    }

    public function getTicketPaginated(Request $request)
    {
        $this->validate($request, [
            'state' => 'integer',
            'ticket_division' => 'string|max:255',
            'search' => 'string|max:255',
            'requestor' => 'string|max:255'
        ]);

        $page = $request->has('page') ? $request->page : 1;
        $rows = $request->has('rows') ? $request->rows : 10;
        $offset = ($page - 1) * $rows;

        $tickets = TicketNew::where('status', 'like', '%' . $request->state . '%')
            ->when($request->requestor_id, function ($query) use ($request) {
                $query->where('requestor', "$request->requestor_id");
            })
            ->with([
                'requestor:id,strname',
                'businessunit:id,strname',
                'division:id,strname',
                'tickettype.ticketProblemDivision',
                'ticketProblemTypeRelation.ticketProblemCategory'
            ])->when($request->search, function ($query) use ($request) {
                $query->whereHas('requestor', function ($query) use ($request) {
                    $query->where('docno', 'LIKE', "%$request->search%")
                        ->orWhere('strname', 'LIKE', "%$request->search%");
                });
            })->when($request->ticket_division, function ($query) use ($request) {
                $query->whereHas('tickettype.ticketProblemDivision', function ($query) use ($request) {
                    $query->where('id', "$request->ticket_division");
                });
            })
            ->skip($offset)
            ->take($rows)
            ->orderBy('docno', 'desc')
            ->get();

        return Utility::setResponseExT(200, '', $tickets, intval($page), $this->getTicketCount($request), count($tickets), $rows);
    }

    public function getTicketAll(Request $request)
    {
        $this->validate($request, [
            'state' => 'integer',
            'ticket_division' => 'string|max:255',
            'search' => 'string|max:255'
        ]);

        $tickets = TicketNew::where('status', 'like', '%' . $request->state . '%')
            ->with([
                'requestor:id,strname',
                'businessunit:id,strname',
                'division:id,strname',
                'tickettype.ticketProblemDivision',
                'ticketProblemTypeRelation.ticketProblemCategory'
            ])->when($request->search, function ($query) use ($request) {
                $query->whereHas('requestor', function ($query) use ($request) {
                    $query->where('docno', 'LIKE', "%$request->search%")
                        ->orWhere('strname', 'LIKE', "%$request->search%");
                });
            })->when($request->ticket_division, function ($query) use ($request) {
                $query->whereHas('tickettype.ticketProblemDivision', function ($query) use ($request) {
                    $query->where('id', "$request->ticket_division");
                });
            })
            ->orderBy('docno', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Ticket is confirmed',
            'data' => ($tickets)
        ], 200);
    }

    public function getTicketOpen(Request $request)
    {
        $tickets = TicketNew::with([
            'requestor:id,strname',
            'businessunit:id,strname',
            'division:id,strname',
            'tickettype.ticketProblemDivision',
            'ticketProblemTypeRelation.ticketProblemCategory'
        ])
            ->whereHas('tickettype')
            ->where('status', '0')
            ->when($request->ticketDivision, function ($query) use ($request) {
                $query->whereHas('tickettype.ticketProblemDivision', function ($query) use ($request) {
                    $query->where('id', 'like', "%$request->ticketDivision%");
                });
            })
            ->orderBy('createddatetime', 'desc')
            ->get();

        $open = TicketNew::where('status', '0')
            ->with('tickettype.ticketProblemDivision')
            ->when($request->ticketDivision, function ($query) use ($request) {
                $query->whereHas('tickettype.ticketProblemDivision', function ($query) use ($request) {
                    $query->where('id', 'like', "%$request->ticketDivision%");
                });
            })->count();
        $confirm = TicketNew::where('status', '1')
            ->with('tickettype.ticketProblemDivision')
            ->when($request->ticketDivision, function ($query) use ($request) {
                $query->whereHas('tickettype.ticketProblemDivision', function ($query) use ($request) {
                    $query->where('id', 'like', "%$request->ticketDivision%");
                });
            })->count();
        $onProcess = TicketNew::where('status', '2')
            ->with('tickettype.ticketProblemDivision')
            ->when($request->ticketDivision, function ($query) use ($request) {
                $query->whereHas('tickettype.ticketProblemDivision', function ($query) use ($request) {
                    $query->where('id', 'like', "%$request->ticketDivision%");
                });
            })->count();
        $close = TicketNew::where('status', '3')
            ->with('tickettype.ticketProblemDivision')
            ->when($request->ticketDivision, function ($query) use ($request) {
                $query->whereHas('tickettype.ticketProblemDivision', function ($query) use ($request) {
                    $query->where('id', 'like', "%$request->ticketDivision%");
                });
            })->count();
        $total = TicketNew::count();

        $results = [
            "ticket" => $tickets,
            "open" => $open,
            "confirm" => $confirm,
            "on_process" => $onProcess,
            "closed" => $close,
            "total" => $total,
        ];

        return response()->json([
            'success' => true,
            'message' => "Ticket retrieved successfully",
            'data' => ($results)
        ], 200);
    }

    public function readTicket(Request $request)
    {
        $this->validate($request, [
            'ticket_id' => 'required',
        ]);
        try {
            $ticket = TicketNew::findOrFail($request->ticket_id);
            $ticket->isread = 1;
            $ticket->save();
            return response()->json([
                'success' => true,
                'message' => 'Ticket is readed',
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

    public function confirmTicket(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ticket_id' => 'required',
            'target' => 'required',
            'regarding' => 'required',
            'tickettype' => 'required',
            'ticket_problem_type' => 'required',
            'priority' => 'required',
            'confirmdatetime' => 'required',
        ]);
        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => Arr::flatten($validator->errors()->getMessages())
            ];
        }
        try {
            DB::beginTransaction();
            $ticket = TicketNew::findOrFail($request->ticket_id);
            $ticket->isread = 1;
            $ticket->status = 1;
            $ticket->target = $request->target;
            $ticket->regarding = $request->regarding;
            $ticket->tickettype = $request->tickettype;
            $ticket->ticket_problem_type = $request->ticket_problem_type;
            $ticket->priority = $request->priority;
            $ticket->confirmdatetime = date("Y-m-d H:i:s", strtotime($request->confirmdatetime));
            $ticket->save();

            $service = new JiraService($ticket);
            $service->update();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Ticket is confirmed',
                'data' => ([])
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $ex) {
            DB::rollBack();
            return response()->json([
                'success' => true,
                'message' => $ex->getMessage(),
                'data' => (null)
            ], 404);
        } catch (\Throwable $ex) {
            DB::rollBack();
            return response()->json([
                'success' => true,
                'message' => $ex->getMessage(),
                'data' => (null)
            ], 500);
        }
    }

    public function startTicket(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ticket_id' => 'required',
            'startdatetime' => 'required',
            'pic_id' => 'required',
        ]);
        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => Arr::flatten($validator->errors()->getMessages())
            ];
        }
        try {
            DB::beginTransaction();
            $pic = Employee::findOrFail($request->pic_id);
            $ticket = TicketNew::findOrFail($request->ticket_id);
            $ticket->status = 2;
            $ticket->pic = $pic->strname;
            $ticket->pic_id = $pic->id;
            $ticket->startdatetime = date("Y-m-d H:i:s", strtotime($request->startdatetime));
            $ticket->save();

            $service = new JiraService($ticket);
            $service->assign($pic);
            $service->start();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Ticket is started',
                'data' => ([])
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $ex) {
            DB::rollBack();
            return response()->json([
                'success' => true,
                'message' => $ex->getMessage(),
                'data' => (null)
            ], 404);
        } catch (\Throwable $ex) {
            DB::rollBack();
            return response()->json([
                'success' => true,
                'message' => $ex->getMessage(),
                'data' => (null)
            ], 500);
        }
    }

    public function endTicket(Request $request)
    {
        $this->validate($request, [
            'ticket_id' => 'required',
            'enddatetime' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $ticket = TicketNew::findOrFail($request->ticket_id);
            $ticket->status = 3;
            $ticket->corrective = $request->corrective;
            $ticket->enddatetime = date("Y-m-d H:i:s", strtotime($request->enddatetime));
            $ticket->save();

            $service = new JiraService($ticket);
            $service->done();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Ticket is ended',
                'data' => ([])
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $ex) {
            DB::rollBack();
            return response()->json([
                'success' => true,
                'message' => $ex->getMessage(),
                'data' => (null)
            ], 404);
        } catch (\Throwable $ex) {
            DB::rollBack();
            return response()->json([
                'success' => true,
                'message' => $ex->getMessage(),
                'data' => (null)
            ], 500);
        }
    }

    public function postFeedback(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ticket_id' => 'required',
            'feedback_note' => 'string',
            'feedback_datetime' => 'required',
            'feedback_score' => 'required',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => Arr::flatten($validator->errors()->getMessages())
            ];
        }
        try {
            $ticket = TicketNew::findOrFail($request->ticket_id);
            $ticket->feedbacknote = $request->feedback_note;
            $ticket->feedbackdatetime = date("Y-m-d H:i:s", strtotime($request->feedback_datetime));
            $ticket->score = $request->feedback_score;
            $ticket->save();
            return response()->json([
                'success' => true,
                'message' => 'Feedback is posted',
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

    public function getPIC()
    {
        return Utility::setResponseExT(
            200,
            'PIC retrieved successfully',
            Employee::whereNotNull('jira_user_id')->get(),
        );
    }
}

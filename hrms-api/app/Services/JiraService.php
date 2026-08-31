<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\TicketNew As Ticket;
use App\Models\TicketProblemDivision;
use Illuminate\Support\Facades\Http;
use stdClass;

class JiraService
{
    private string $url;

    private string $issue_type_id;

    private string $corrective_field;

    private string $project_id;

    private string $reporter_id;

    private ?string $assignee_id;

    private ?string $due_date;

    private array $labels;

    private Ticket $ticket;

    private string $token;

    private TicketProblemDivision $division;

    /**
     * @param App\Models\TicketNew $ticket
     */
    public function __construct($ticket)
    {
        $this->token = config('jira.auth_token');
        $this->ticket = $ticket;
        $this->division = $ticket->ticketProblemDivision()->first();

        $division = strtolower($this->division->name);
        $this->url = config("jira.$division.url");
        $this->project_id = config("jira.$division.project_id");
        $this->reporter_id = config("jira.$division.reporter_id");
        $this->assignee_id = null;
        $this->due_date = date('Y-m-d', strtotime($ticket->createddatetime . '+2 days'));
        $this->labels = [
            $this->toLabelStyle($division),
            $this->toLabelStyle($ticket->ticketProblemTypeRelation()->first()->name)
        ];
        $this->setIssueTypeId();
        $this->setCorrectiveField();
    }

    /**
     * @throws \Exception
     * @return \Illuminate\Support\Collection
     */
    public function create()
    {
        try {
            $response = Http::withToken($this->token, 'Basic')
                ->post(
                    $this->url . '/rest/api/3/issue?updateHistory=false',
                    $this->buildRequestBody()
                );

            if ($response->successful()){
                return $response->collect();
            } else {
                $response->throw();
            }
        } catch (\Exception $error) {
            throw $error;
        }
    }

    /**
     * @throws \Exception
     * @return \Illuminate\Support\Collection
     */
    public function update()
    {
        try {
            if ($this->ignoreTicket()) return;
            $jiraId = $this->ticket->jira_id;

            if ($jiraId == null){
                throw new \Exception('This ticket doesn\'t have jira ID');
            }

            $response = Http::withToken($this->token, 'Basic')
            ->put(
                $this->url . "/rest/api/3/issue/$jiraId",
                $this->buildRequestBody()
            );

            if ($response->successful()){
                if ($this->ticket->priority){
                    $this->changeIssuePriority();
                }
                return $response->collect();
            } else {
                $response->throw();
            }

        } catch (\Exception $error) {
            throw $error;
        }
    }

    /**
     * @param App\Models\Employee $employee
     * @return \Illuminate\Support\Collection
     * @throws \Illuminate\Http\Client\RequestException
     * @throws \Exception
     */
    public function assign(Employee $employee)
    {
        try {
            if ($this->ignoreTicket()) return;
            $issueId = $this->ticket->jira_id;
            $response = Http::withToken($this->token, 'Basic')
                ->put(
                    $this->url . "/rest/api/3/issue/$issueId/assignee",
                    [
                        'accountId' => $employee->jira_user_id
                    ]
                );

            if ($response->successful()){
                return $response->collect();
            } else {
                $response->throw();
            }
        } catch (\Exception $error) {
            throw $error;
        }
    }

    /**
     * @return \Illuminate\Support\Collection
     * @throws \Illuminate\Http\Client\RequestException
     * @throws \Exception
     */
    public function start()
    {
        if ($this->ignoreTicket()) return;
        return $this->changeIssuePosition(31);
    }

    /**
     * @return \Illuminate\Support\Collection|null
     * @throws \Illuminate\Http\Client\RequestException
     * @throws \Exception
     */
    public function done()
    {
        if ($this->ignoreTicket()) return;
        $this->update();
        return $this->changeIssuePosition(41);
    }

    /**
     * @param int $transitionId
     * @return \Illuminate\Support\Collection
     * @throws \Illuminate\Http\Client\RequestException
     * @throws \Exception
     */
    private function comment($comment)
    {
        try {
            $issueId = $this->ticket->jira_id;
            $response = Http::withToken($this->token, 'Basic')
                ->post(
                    $this->url . "/rest/api/3/issue/$issueId/comment",
                    [
                        'body' => [
                            'type' => 'doc',
                            'version' => 1,
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'content' => [
                                        [
                                            'text' => $comment,
                                            'type' => 'text',
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                );

            if ($response->successful()){
                return $response->collect();
            } else {
                $response->throw();
            }
        } catch (\Exception $error) {
            throw $error;
        }
    }

    /**
     * @param int $transitionId
     * @return \Illuminate\Support\Collection
     * @throws \Illuminate\Http\Client\RequestException
     * @throws \Exception
     */
    private function changeIssuePosition($transitionId)
    {
        try {
            $issueId = $this->ticket->jira_id;
            $response = Http::withToken($this->token, 'Basic')
                ->post(
                    $this->url . "/rest/api/3/issue/$issueId/transitions",
                    [
                        'transition' => [
                            'id' => $transitionId
                        ]
                    ]
                );

            if ($response->successful()){
                return $response->collect();
            } else {
                $response->throw();
            }
        } catch (\Exception $error) {
            throw $error;
        }
    }

    /**
     * @param int $transitionId
     * @return \Illuminate\Support\Collection
     * @throws \Illuminate\Http\Client\RequestException
     * @throws \Exception
     */
    private function changeIssuePriority()
    {
        try {
            $issueId = $this->ticket->jira_id;
            $response = Http::withToken($this->token, 'Basic')
                ->put(
                    $this->url . "/rest/api/3/issue/$issueId",
                    [
                        'update' => [
                            'priority' => [
                                [
                                    'set' => [
                                        'name' => $this->ticket->priority ?? 'Medium'
                                    ]
                                ]
                            ]
                        ]
                    ]
                );

            if ($response->successful()){
                return $response->collect();
            } else {
                $response->throw();
            }
        } catch (\Exception $error) {
            throw $error;
        }
    }

    private function setIssueTypeId()
    {
        $division = strtolower($this->division->name);
        if ($type = $this->ticket->regarding){
            $this->issue_type_id = $type == 'COMPLAIN'
                ? config("jira.$division.complaint_issue_type")
                : config("jira.$division.request_issue_type");
        }
    }

    private function setCorrectiveField()
    {
        $division = strtolower($this->division->name);
        if ($type = $this->ticket->regarding){
            $this->corrective_field = $type == 'COMPLAIN'
                ? config("jira.$division.complaint_corrective_field")
                : config("jira.$division.request_corrective_field");
        }
    }

    private function buildRequestBody()
    {
        $body = [
            "update" => new stdClass,
            "fields" => [
                "summary" => $this->ticket->docno . ' - ' . $this->ticket->ticketProblemTypeRelation()->first()->name,
                "project" => [
                    "id" => $this->project_id,
                ],
                "issuetype" => [
                    "id" => $this->issue_type_id,
                ],
                "description" => [
                    "type" => "doc",
                    "version" => 1,
                    "content" => [
                        [
                            "type" => "paragraph",
                            "content" => [
                                [
                                    "text" => "Nomor Extension :" . $this->ticket->phone,
                                    "type" => "text"
                                ],
                            ]
                        ],
                        [
                            "type" => "paragraph",
                            "content" => [
                                [
                                    "text" => "IP Address : " . $this->ticket->ipaddress,
                                    "type" => "text"
                                ],
                            ]
                        ],
                        [
                            "type" => "paragraph",
                            "content" => [
                                [
                                    "text" => 'Requestor : ' . $this->ticket->requestor()->first()->strname,
                                    "type" => "text"
                                ],
                            ]
                        ],
                        [
                            "type" => "paragraph",
                            "content" => [
                                [
                                    "text" => "Description : " . $this->ticket->description,
                                    "type" => "text"
                                ],
                            ]
                        ],
                    ]
                ],
                "reporter" => [
                    "id" => $this->reporter_id
                ],
                "labels" => $this->labels,
                "duedate" => $this->due_date
            ]
        ];

        if ($this->hasCorrective()) {
            $body['fields'][$this->corrective_field] = [
                'type' => 'doc',
                'version' => 1,
                'content' => [
                    [
                        "type" => "paragraph",
                        "content" => [
                            [
                                "text" => $this->ticket->corrective,
                                "type" => "text"
                            ],
                        ]
                    ]
                ]
            ];
        }

        return $body;
    }

    private function toLabelStyle($label)
    {
        return str_replace(' ', '_', strtoupper($label));
    }

    /**
     * @return bool
     */
    private function hasCorrective()
    {
        return $this->ticket->corrective != null
            && $this->ticket->corrective != ''
            && $this->ticket->corrective != '-';
    }

    private function ignoreTicket()
    {
        // ignore old ticket that doesn't have jira_id
        return $this->ticket->jira_id == null;
    }
}

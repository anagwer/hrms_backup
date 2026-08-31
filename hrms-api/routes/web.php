<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});


$router->post('application', 'ApplicationController@listAll');
$router->post('role', 'RoleController@listAll');
$router->post('roleorganization', 'RoleOrganizationController@listAll');
$router->post('securityrole', 'SecurityRoleController@listAll');
$router->post('company', 'CompanyController@listAll');
$router->post('businessunit', 'BusinessUnitController@listAll');
$router->post('businessunitbyrole', 'BusinessUnitController@getBusinessUnitBySecurityRoles');
$router->post('branch', 'BranchController@listAll');
$router->post('department', 'DepartmentController@listAll');
$router->post('departmentdivisionbyrole', 'DepartmentController@getDepartmentDivisionBySecurityRoles');
$router->post('division', 'DivisionController@listAll');
$router->post('plant', 'PlantController@listAll');
$router->post('pool', 'PoolController@listAll');
$router->post('job', 'JobController@listAll');
$router->post('joblevel', 'JobLevelController@listAll');
$router->post('grade', 'GradeController@listAll');
$router->post('shift', 'ShiftCategoryController@listAll');
$router->post('workcalendar', 'WorkCalendarController@listAll');
$router->post('workgroup', 'WorkGroupController@listAll');
$router->post('workgroup/line', 'WorkGroupController@listLine');
$router->post('employee', 'EmploymentController@listAll');
$router->post('accessrole', 'AccessRoleController@listAll');
$router->post('absence', 'AbsenceController@listAll');
$router->post('presence', 'PresenceController@listAll');
$router->post('absencetype', 'AbsenceTypeController@listAll');
$router->post('leavetype', 'LeaveTypeController@listAll');
$router->post('cuti', 'CutiController@listAll');
$router->post('trainingreminder', 'TrainingReminderController@listAll');
$router->post('contractreminder', 'ContractReminderController@listAll');
$router->post('mutation', 'MutationController@listAll');
$router->post('resign', 'ResignEmployeeController@listAll');
$router->post('reward', 'RewardController@listAll');
$router->post('disciplinary', 'DisciplinaryController@listAll');
$router->post('disciplinarytype', 'DisciplinaryTypeController@listAll');
$router->post('educationinfo', 'EmployeeEduController@listAll');
$router->post('employeefamily', 'EmployeeFamilyController@listAll');
$router->post('tickettype', 'TicketTypeController@listAll');
$router->post('tickettype/create', 'TicketTypeController@create');
$router->post('tickettype/delete', 'TicketTypeController@delete');
$router->post('ticket', 'TicketController@listAll');
$router->post('ticket/detail', 'TicketController@getByID');
$router->post('ticketdivision', 'TicketDivisionController@listAll');

$router->get('ticket-paginate', 'TicketController@getTicketPaginated');
$router->get('ticket-problem-division', 'TicketProblemDivisionController@getTicketProblemDivision');
$router->get('ticket/all', 'TicketController@getTicket');
$router->post('ticket/create', 'TicketController@createTicket');
$router->get('ticket/open', 'TicketController@getTicketOpen');
$router->post('ticket/read', 'TicketController@readTicket');
$router->post('ticket/confirm', 'TicketController@confirmTicket');
$router->post('ticket/start', 'TicketController@startTicket');
$router->post('ticket/end', 'TicketController@endTicket');
$router->post('ticket/feedback', 'TicketController@postFeedback');
$router->get('ticket/pic', 'TicketController@getPIC');

$router->post('requestForm/create', 'RequestFormDocumentController@create');
$router->post('requestForm/update', 'RequestFormDocumentController@update');
$router->delete('requestForm/{id}', 'RequestFormDocumentController@delete');
$router->get('requestForm/paginate', 'RequestFormDocumentController@getRequestFormDocumentPaginated');

$router->post('problemtypelist', 'ProblemTypeController@listAll');
$router->post('problemtype', 'ProblemTypeController@getByID');
$router->post('problemtype/create', 'ProblemTypeController@create');
$router->post('problemtype/delete', 'ProblemTypeController@delete');
$router->post('resigntype', 'ResignTypeController@listAll');
$router->post('workflow', 'WorkflowController@listAll');
$router->post('feature', 'FeatureController@listAll');
$router->post('workday', 'WorkDayController@listAll');

$router->post('sikel', 'SikelController@listAll');
$router->post('documenttype', 'DocumentTypeController@listAll');
$router->post('employeedocument', 'EmployeeDocumentController@listAll');
$router->post('aplocation', 'APLocationController@listAll');

$router->post('employeedetails', 'EmploymentController@detailByNik');
$router->post('login', 'AuthController@auth');
$router->post('loginticket', 'AuthController@authTicket');
$router->post('employeeticket', 'EmploymentController@listAllTicket');
$router->get('employee/{nik}/id-card', 'EmploymentController@getIDCardInfo');
$router->get('employee/salary/{nik}', 'SalaryController@getSalaryInfo');

$router->group(
    ['middleware' => 'jwt.auth'],
    function () use ($router) {
        $router->get('dashboard/mobile', 'MobileController@getHomeData');
        $router->get('token/refresh', 'AuthController@generateNewToken');
        $router->post('sequencenumber/generate', 'SequenceNumberController@generateSequence');

        $router->post('salary/import', 'SalaryController@importSalary');
        $router->get('salary/state', 'SalaryController@getSalaryState');
        $router->post('salary/print', 'SalaryController@getPrintedSalary');

        $router->post('contactPerson/create', 'ContactPersonController@create');
        $router->post('contactPerson/update', 'ContactPersonController@update');
        $router->delete('contactPerson/{id}', 'ContactPersonController@delete');
        $router->get('contactPerson/paginated', 'ContactPersonController@getContactPersonPaginated');

        $router->get('organization', 'OrganizationController@index');
        $router->post('organization', 'OrganizationController@createOrUpdateTree');

        $router->post('roleorganization/create', 'RoleOrganizationController@create');
        $router->post('roleorganization/delete', 'RoleOrganizationController@delete');

        $router->post('securityrole/create', 'SecurityRoleController@create');
        $router->post('securityrole/delete', 'SecurityRoleController@delete');

        $router->post('educationinfo/create', 'EmployeeEduController@create');
        $router->post('educationinfo/delete', 'EmployeeEduController@delete');

        $router->post('employeefamily/create', 'EmployeeFamilyController@create');
        $router->post('employeefamily/delete', 'EmployeeFamilyController@delete');

        $router->post('branch/create', 'BranchController@create');
        $router->post('branch/delete', 'BranchController@delete');

        $router->post('department/create', 'DepartmentController@create');
        $router->post('department/delete', 'DepartmentController@delete');

        $router->post('job/create', 'JobController@create');
        $router->post('job/delete', 'JobController@delete');

        $router->post('grade/create', 'GradeController@create');
        $router->post('grade/delete', 'GradeController@delete');

        $router->post('documenttype/create', 'DocumentTypeController@create');
        $router->post('documenttype/delete', 'DocumentTypeController@delete');

        $router->post('employeedocument/create', 'EmployeeDocumentController@create');
        $router->post('employeedocument/delete', 'EmployeeDocumentController@delete');

        $router->post('aplocation/create', 'APLocationController@create');
        $router->post('aplocation/delete', 'APLocationController@delete');

        $router->post('workgroup/create', 'WorkGroupController@create');
        $router->post('workgroup/delete', 'WorkGroupController@delete');

        $router->post('workgroup/line/create', 'WorkGroupController@createLine');
        $router->post('workgroup/line/delete', 'WorkGroupController@deleteLine');

        $router->post('absence/list', 'AbsenceController@listAllByEmployee');
        $router->post('absence/create', 'AbsenceController@manualAbsence');
        $router->post('absence/delete', 'AbsenceController@delete');
        $router->post('absence/post', 'AbsenceController@post');
        $router->post('absence/accumulation', 'AbsenceController@accumulation');
        $router->post('absence/recap', 'AbsenceController@absenceRecap');
        $router->get('absence/image/{name}', 'AbsenceController@getImage');

        $router->post('presence/pulangawal', 'PresenceController@listEmployeePulangAwal');
        $router->post('presence/terlambat', 'PresenceController@listEmployeeTerlambat');
        $router->post('presence/create', 'PresenceController@create');
        $router->post('presence/delete', 'PresenceController@delete');
        $router->get('presence/outstanding', 'PresenceController@outstanding');
        $router->post('presence/presence', 'PresenceController@presence');
        $router->post('presence/history', 'PresenceController@presencehistory');
        $router->post('presence/lateaccumulation', 'PresenceController@lateAccumulation');
        $router->post('presence/workday', 'PresenceController@workDayRecap');
        $router->get('presence/lastpresenceaccumulation', 'PresenceController@getTodayPresence');

        $router->post('workcalendar/create', 'WorkCalendarController@create');
        $router->post('workcalendar/delete', 'WorkCalendarController@delete');

        $router->post('workday/create', 'WorkDayController@create');
        $router->post('workday/delete', 'WorkDayController@delete');

        $router->post('employee/contract', 'EmploymentController@listEmployeeContract');
        $router->post('employee/training', 'EmploymentController@listEmployeeTraining');
        $router->post('employee/create', 'EmploymentController@create');
        $router->post('employee/delete', 'EmploymentController@delete');
        $router->post('employee/setpassword', 'EmploymentController@setpassword');
        $router->get('employee/detail', 'EmploymentController@detailbytoken');
        $router->post('employee/deactive', 'EmploymentController@deactive');
        $router->post('employee/activate', 'EmploymentController@activate');
        $router->post('employee/createforticket', 'EmploymentController@createForTicket');
        $router->post('employee/detailemployeeticket', 'EmploymentController@detailByTokenTicket');
        $router->post('employee/deleteemployeeticket', 'EmploymentController@deleteEmployeeTicket');
        $router->post('employee/pin/check', 'EmploymentController@pinVerification');
        $router->post('employee/pin/reset', 'EmploymentController@resetPin');
        $router->post('employee/pin/resetall', 'EmploymentController@resetPinAllEmployee');
        $router->post('employee/pin/update', 'EmploymentController@changePin');
        $router->post('employee/qrcode/download', 'EmploymentController@downloadQrCode');
        $router->post('employee/{employeeId}/reset-device-id', 'EmploymentController@resetDeviceId');
        $router->post('employee/email/update', 'EmploymentController@updateEmail');

        $router->post('sikel/create', 'SikelController@create');
        $router->post('sikel/delete', 'SikelController@delete');
        $router->post('sikel/approval', 'SikelController@approve');
        $router->get('sikel/detail/{docnumber}', 'SikelController@detail');
        $router->get('sikel/outstanding', 'SikelController@outstanding');
        $router->post('sikel/accumulation', 'SikelController@accumulation');
        $router->post('sikel/history', 'SikelController@sikelhistory');

        $router->get('cuti/type/list', 'CutiController@gettypelist');
        $router->get('cuti/detail/{id}', 'CutiController@detail');
        $router->get('cuti/outstanding', 'CutiController@outstanding');
        $router->post('cuti/create', 'CutiController@create');
        $router->post('cuti/approve', 'CutiController@approve');
        $router->post('cuti/delete', 'CutiController@delete');
        $router->post('cuti/cancel', 'CutiController@cancel');
        $router->post('cuti/history', 'CutiController@cutihistory');

        $router->post('reward/create', 'RewardController@create');
        $router->post('reward/delete', 'RewardController@delete');
        $router->post('reward/approve', 'RewardController@approve');

        $router->post('disciplinary/create', 'DisciplinaryController@create');
        $router->post('disciplinary/delete', 'DisciplinaryController@delete');
        $router->post('disciplinary/approve', 'DisciplinaryController@approve');

        $router->post('mutation/create', 'MutationController@create');
        $router->post('mutation/delete', 'MutationController@delete');
        $router->post('mutation/approve', 'MutationController@approve');

        $router->post('resign/create', 'ResignEmployeeController@create');
        $router->post('resign/delete', 'ResignEmployeeController@delete');
        $router->post('resign/approve', 'ResignEmployeeController@approve');
        $router->get('resign/type/list', 'ResignEmployeeController@gettypelist');

        $router->post('workflow/create', 'WorkflowController@create');
        $router->post('workflow/delete', 'WorkflowController@delete');

        $router->post('checkaccess', 'SecurityRoleController@canAccess');
        $router->post('checkupdatedelete', 'SecurityRoleController@canUpdateDelete');
        $router->post('changepassword', 'AuthController@changePassword');

        $router->post('report/cutiperiode', 'ReportController@getLeavesYearDistinct');
        $router->get('report/cutirecap', 'ReportController@getLeavesRecap');

        $router->post('log/contact-person', 'LogController@writeContactPersonLog');

        /**
         * FAQ
         */
        $router->get('question_type', 'FAQController@getQuestionTypes');
        $router->get('question_type/paginated', 'FAQController@getPaginatedQuestionTypes');
        $router->post('question_type', 'FAQController@createQuestionType');
        $router->post('question_type/{questionTypeId}', 'FAQController@updateQuestionType');
        $router->delete('question_type/{questionTypeId}', 'FAQController@deleteQuestionType');
        $router->get('question', 'FAQController@getQuestions');
        $router->get('question/paginated', 'FAQController@getPaginatedQuestions');
        $router->post('question', 'FAQController@createQuestion');
        $router->post('question/{questionId}', 'FAQController@updateQuestion');
        $router->delete('question/{questionId}', 'FAQController@deleteQuestion');
        $router->get('answer', 'FAQController@getAnswers');
        $router->post('answer', 'FAQController@createAnswer');
        $router->post('answer/{answerId}', 'FAQController@updateAnswer');
        $router->delete('answer/{answerId}', 'FAQController@deleteAnswer');

        /**
         * Firebase Cloud Messaging
         */
        $router->post('fcm-token', 'FCMController@saveToken');
    }
);

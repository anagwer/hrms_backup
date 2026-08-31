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


$router->post('application','ApplicationController@listAll');
$router->post('role','RoleController@listAll');
$router->post('company','CompanyController@listAll');
$router->post('businessunit','BusinessUnitController@listAll');
$router->post('branch','BranchController@listAll');
$router->post('department','DepartmentController@listAll');
$router->post('division','DivisionController@listAll');
$router->post('plant','PlantController@listAll');
$router->post('pool','PoolController@listAll');
$router->post('job','JobController@listAll');
$router->post('joblevel','JobLevelController@listAll');
$router->post('grade','GradeController@listAll');
$router->post('shift','ShiftCategoryController@listAll');
$router->post('workcalendar','WorkCalendarController@listAll');
$router->post('workgroup','WorkGroupController@listAll');
$router->post('workgroup/line','WorkGroupController@listLine');
$router->post('employee','EmploymentController@listAll');
$router->post('accessrole','AccessRoleController@listAll');

$router->post('sikel','SikelController@listAll');


$router->post('aplocation','APLocationController@listAll');

$router->post('login','AuthController@auth');

$router->group(
    ['middleware' => 'jwt.auth'],
    function() use ($router) {
        $router->post('sequencenumber/generate','SequenceNumberController@generateSequence');
        
        $router->post('branch/create','BranchController@create');
        $router->post('branch/delete','BranchController@delete');
        
        $router->post('job/create','JobController@create');
        $router->post('job/delete','JobController@delete');
        
        $router->post('grade/create','GradeController@create');
        $router->post('grade/delete','GradeController@delete');

        $router->post('aplocation/create','APLocationController@create');
        $router->post('aplocation/delete','APLocationController@delete');
        
        
        $router->post('workgroup/create','WorkGroupController@create');
        $router->post('workgroup/delete','WorkGroupController@delete');
        
        $router->post('workgroup/line/create','WorkGroupController@createLine');
        $router->post('workgroup/line/delete','WorkGroupController@deleteLine');
        


        $router->post('workcalendar/create','WorkCalendarController@create');
        $router->post('workcalendar/delete','WorkCalendarController@delete');

        $router->post('employee/create','EmploymentController@create');
        $router->post('employee/delete','EmploymentController@delete');
        $router->post('employee/setpassword','EmploymentController@setpassword');

        $router->post('sikel/create','SikelController@create');
        $router->post('sikel/delete','SikelController@delete');


        
    }
);

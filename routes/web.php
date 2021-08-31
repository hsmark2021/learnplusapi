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

Route::group([
    'prefix' => 'auth'
], function ($router) {
    $router->post('register', 'AuthController@register');
    $router->post('login', 'AuthController@login');
});

Route::group([
    'middleware' => ['auth','cors'],
], function ($router) {
    //User API
    $router->get('me/joinedcourses', 'CourseController@getJoinedCourses'); 
    $router->get('me/courses/{courseid}/join', 'CourseController@joinCourses'); 
    $router->get('me/courses/{courseid}/unjoin', 'CourseController@unjoinCourses'); 
    //Supplier API
    $router->get('me/createdcourses', 'CourseController@getCreatedCourses'); //ok
    $router->post('me/courses', 'CourseController@addCourse'); //ok
    $router->put('me/course/{courseid}', 'CourseController@editCourse');
    $router->delete('me/course/{courseid}', 'CourseController@deleteCourse');//ok

    $router->get('me/course/{courseid}/sections', 'CourseController@getCourseSections'); //ok
    $router->post('me/course/{courseid}/sections', 'CourseController@addSection'); //ok
    $router->put('me/course/{courseid}/section/{sectionid}', 'CourseController@updateSection'); //ok

});

//Public API
$router->get('categories', 'CourseController@getCategories'); //ok
$router->post('courses', 'CourseController@getCourses'); //ok
$router->get('course/{id}', 'CourseController@getCourse'); //ok

//Flight Test for ORM Learning
$router->get('flights','FlightController@getFlight');
$router->post('flights','FlightController@addFlight');
$router->put('flights/{id}','FlightController@updateFlight');
$router->delete('flights/{id}','FlightController@deleteFlight');

$router->get('lectures','LectureController@getLectures');
$router->get('lecture/{id}','LectureController@getSingleLecture');
$router->post('lectures','LectureController@addLecture');
$router->put('lecture/{id}','LectureController@updateLecture');
$router->delete('lecture/{id}','LectureController@deleteLecture');

$router->post('mail', 'CourseController@sendmail'); //ok

$router->get('testsecurity', 'CourseController@testSecurity'); //ok

//Basic Test
$router->get('/', function () use ($router) {
    return $router->app->version();
});

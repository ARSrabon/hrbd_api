<?php
/**
 * Created by PhpStorm.
 * User: msrabon
 * Date: 1/3/17
 * Time: 8:28 PM
 */
require_once '../include/DbHandler.php';
require_once '../include/PassHash.php';
require '.././libs/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

// User id from db - Global Variable
$user_id = NULL;

/**
 * Verifying required params posted or not
 */
function verifyRequiredParams($required_fields)
{
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;
    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }

    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echoRespnse(400, $response);
        $app->stop();
    }
}


/**
 * Validating email address
 */
function validateEmail($email)
{
    $app = \Slim\Slim::getInstance();
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response["error"] = true;
        $response["message"] = 'Email address is not valid';
        echoRespnse(400, $response);
        $app->stop();
    }
}


/**
 * User Registration
 * url - /register
 * method - POST
 * params - name, email, password
 */
$app->post('/register', function () use ($app) {
    // check for required params
    verifyRequiredParams(array('user_id', 'Username', 'fullName', 'email', 'mobile_no', 'address', 'city', 'area'));

    $response = array();

    // reading post params
    $user_id = $app->request->post('user_id');
    $Username = $app->request->post('Username');
    $fullName = $app->request->post('fullName');
    $email = $app->request->post('email');
    $mobile_no = $app->request->post('mobile_no');
    $address = $app->request->post('address');
    $city = $app->request->post('city');
    $area = $app->request->post('area');

    // validating email address
    validateEmail($email);

    $db = new DbHandler();
    $res = $db->createUser($user_id, $Username, $fullName, $email, $mobile_no, $address, $city, $area);

    if ($res == USER_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["message"] = "You are successfully registered";
        echoRespnse(201, $response);
    } else if ($res == USER_CREATE_FAILED) {
        $response["error"] = true;
        $response["message"] = "Oops! An error occurred while registereing";
        echoRespnse(200, $response);
    } else if ($res == USER_ALREADY_EXISTED) {
        $response["error"] = true;
        $response["message"] = "Sorry, this email already existed";
        echoRespnse(200, $response);
    }
});

/**
 *
 */
$app->put('/user/:user_id', function ($task_id) use ($app) {
    // check for required params
    verifyRequiredParams(array('user_id', 'Username', 'fullName', 'email', 'mobile_no', 'address', 'city', 'area'));

    global $user_id;
    $task = $app->request->put('task');
    $status = $app->request->put('status');

    $db = new DbHandler();
    $response = array();

    // updating task
    $result = $db->updateTask($user_id, $task_id, $task, $status);
    if ($result) {
        // task updated successfully
        $response["error"] = false;
        $response["message"] = "Task updated successfully";
    } else {
        // task failed to update
        $response["error"] = true;
        $response["message"] = "Task failed to update. Please try again!";
    }
    echoRespnse(200, $response);
});

/**
 * Listing all rents of particual user
 * method GET
 * url /tasks
 */
$app->get('/userrents/:user_id', function ($user_id) {
    $response = array();
    $db = new DbHandler();

    // fetching all user tasks
    $result = $db->getAllUserRents($user_id);

    if ($result->num_rows > 0) {
        $response["error"] = false;
        $response["rents"] = array();

        // looping through result and preparing tasks array
        while ($task = $result->fetch_assoc()) {
            $tmp = array();
            $tmp["id"] = $task["id"];
            $tmp["user_id"] = $task["user_id"];
            $tmp["area_id"] = $task["area_id"];
            $tmp["rentPrice"] = $task["rentPrice"];
            array_push($response["rents"], $tmp);
        }
    } else {
        $response["error"] = true;
        $response["rents"] = array();
    }


    echoRespnse(200, $response);
});

/**
 * Listing all tasks of particual user
 * method GET
 * url /tasks
 */
$app->get('/rents', function () {
    $response = array();
    $db = new DbHandler();

    // fetching all user tasks
    $result = $db->getRentalAds();

    $response["error"] = false;
    $response["rents"] = array();

//    array_push($response["rents"], $result);

//     looping through result and preparing tasks array
    while ($task = $result->fetch_assoc()) {
        $tmp = array();
        $tmp["id"] = $task["id"];
        $tmp["area_id"] = $task["area_id"];
        $tmp["rent_type_id"] = $task["rent_type_id"];
        $tmp["banner"] = $task["banner"];
        $tmp["beds"] = $task["beds"];
        $tmp["size"] = $task["size"];
        $tmp["available"] = $task["available"];
        $tmp["avgrating"] = $task["avgrating"];
        $tmp["reviews"] = $task["reviews"];
        $tmp["img_banner"] = $task["img_banner"];
        array_push($response["rents"], $tmp);
    }



    echoRespnse(200, $response);
});

/**
 * Listing all tasks of particual user
 * method GET
 * url /tasks
 */
$app->get('/rents/:rent_id', function ($rent_id) {
    $response = array();
    $db = new DbHandler();

    // fetching all user tasks
    $result = $db->getSingleRentalAds($rent_id);

    $response["error"] = false;
    $response["rents"] = array();

    // looping through result and preparing tasks array
    while ($task = $result->fetch_assoc()) {
        $tmp = array();
        $tmp["id"] = $task["id"];
        $tmp["user_id"] = $task["user_id"];
        $tmp["area_id"] = $task["area_id"];
        $tmp["rentprice"] = $task["rentprice"];
        array_push($response["rents"], $tmp);
    }

    echoRespnse(200, $response);
});

/**
 * Create a review for rental AD
 * method POST
 * url /reviews
 */
$app->post('/reviews', function () use ($app) {
    // check for required params
    verifyRequiredParams(array('rents_id', 'user_id', 'rating', 'review'));

    $response = array();

    // reading post params
    $user_id = $app->request->post('user_id');
    $rents_id = $app->request->post('rents_id');
    $ratings = $app->request->post('rating');
    $reviews = $app->request->post('review');

    $db = new DbHandler();
    $res = $db->createRentalAdReviews($rents_id, $user_id, $ratings, $reviews);

    if ($res) {
        $response["error"] = false;
        $response["message"] = "Review is added Successfully";
        echoRespnse(201, $response);
    } else {
        $response["error"] = true;
        $response["message"] = "Oops! An error occurred while adding review";
        echoRespnse(200, $response);
    }
});


/**
 * Listing all tasks of particual user
 * method GET
 * url /tasks
 */
$app->get('/reviews/:rent_id', function ($rent_id) {
    $response = array();
    $db = new DbHandler();

    // fetching all user tasks
    $result = $db->getSingleRentalAdReviews($rent_id);

    if ($result->num_rows > 0) {
        $response["error"] = false;
        $response["review"] = array();

        // looping through result and preparing tasks array
        while ($task = $result->fetch_assoc()) {
            $tmp = array();
            $tmp["id"] = $task["id"];
            $tmp["rents_id"] = $task["rents_id"];
            $tmp["user_id"] = $task["user_id"];
            $tmp["ratings"] = $task["ratings"];
            $tmp["reviews"] = $task["reviews"];
            array_push($response["review"], $tmp);
        }
    } else {
        $response["error"] = true;
        $response["review"] = array();
    }

    echoRespnse(200, $response);
});


/**
 * Echoing json response to client
 * @param String $status_code Http response code
 * @param Int $response Json response
 */
function echoRespnse($status_code, $response)
{
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);

    // setting response content type to json
    $app->contentType('application/json');

    echo json_encode($response);
}

$app->run();


?>
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
 * User Registration
 * url - /register
 * method - POST
 * params - name, email, password
 */
$app->post('/rents', function () use ($app) {
    // check for required params
    verifyRequiredParams(array('user_id', 'area_id', 'rent_type_id', 'banner', 'beds', 'baths', 'size', 'floordetails', 'lift',
        'parking', 'rentprice', 'rentdetails', 'address', 'geoloc_lat', 'geoloc_lng', 'available', 'img_banner', 'img_other_one', 'img_other_two'));

    $response = array();

    // reading post params
    $user_id = $app->request->post('user_id');
    $area_id = $app->request->post('area_id');
    $rent_type_id = $app->request->post('rent_type_id');
    $banner = $app->request->post('banner');
    $beds = $app->request->post('beds');
    $baths = $app->request->post('baths');
    $size = $app->request->post('size');
    $floordetails = $app->request->post('floordetails');
    $lift = $app->request->post('lift');
    $parking = $app->request->post('parking');
    $rentprice = $app->request->post('rentprice');
    $rentdetails = $app->request->post('rentdetails');
    $address = $app->request->post('address');
    $geoloc_lat = $app->request->post('geoloc_lat');
    $geoloc_lng = $app->request->post('geoloc_lng');
    $available = $app->request->post('available');
    $img_banner = $app->request->post('img_banner');
    $img_other_one = $app->request->post('img_other_one');
    $img_other_two = $app->request->post('img_other_two');


    $db = new DbHandler();
    $res = $db->createRentalAd($user_id, $area_id, $rent_type_id, $banner, $beds, $baths, $size, $floordetails, $lift, $parking,
        $rentprice, $rentdetails, $address, $geoloc_lat, $geoloc_lng, $available, $img_banner, $img_other_one, $img_other_two);

    if ($res) {
        $response["error"] = false;
        $response["message"] = "You are successfully registered";
        echoRespnse(201, $response);
    } else {
        $response["error"] = true;
        $response["message"] = "Oops! An error occurred while registereing";
        echoRespnse(200, $response);
    }
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
 * Retrive specific Rental ad for detail View
 * method GET
 * url /rents/:rent_id
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
        $tmp["rent_type_id"] = $task["rent_type_id"];
        $tmp["banner"] = $task["banner"];
        $tmp["beds"] = $task["beds"];
        $tmp["baths"] = $task["baths"];
        $tmp["size"] = $task["size"];
        $tmp["flordetails"] = $task["floordetails"];
        $tmp["lift"] = $task["lift"];
        $tmp["parking"] = $task["parking"];
        $tmp["rentprice"] = $task["rentprice"];
        $tmp["rentdetails"] = $task["rentdetails"];
        $tmp["address"] = $task["address"];
        $tmp["geoloc_lat"] = $task["geoloc_lat"];
        $tmp["geoloc_lng"] = $task["geoloc_lng"];
        $tmp["available"] = $task["available"];
        $tmp["img_banner"] = $task["img_banner"];
        $tmp["img_other_one"] = $task["img_other_one"];
        $tmp["img_other_two"] = $task["img_other_two"];
        $tmp["created_at"] = $task["created_at"];
        array_push($response["rents"], $tmp);
    }

    echoRespnse(200, $response);
});

/**
 * User Rent Query
 * url - /rent_query
 * method - POST
 * params - name, email, password
 */
$app->post('/rent_query', function () use ($app) {
    // check for required params
    verifyRequiredParams(array('user_id', 'area_id', 'rent_type_id', 'banner', 'beds', 'baths', 'lift',
        'parking', 'others', 'rentprice_range', 'validity'));

    $response = array();

    // reading post params
    $user_id = $app->request->post('user_id');
    $area_id = $app->request->post('area_id');
    $rent_type_id = $app->request->post('rent_type_id');
    $banner = $app->request->post('banner');
    $beds = $app->request->post('beds');
    $baths = $app->request->post('baths');
    $lift = $app->request->post('lift');
    $parking = $app->request->post('parking');
    $others = $app->request->post('others');
    $rentprice_range = $app->request->post('rentprice_range');
    $validity = $app->request->post('validity');

    $db = new DbHandler();
    $res = $db->createQueryPost($user_id, $area_id, $rent_type_id, $banner, $beds, $baths, $lift, $parking, $others, $rentprice_range, $validity);

    if ($res) {
        $response["error"] = false;
        $response["message"] = "You are successfully registered";
        echoRespnse(201, $response);
    } else {
        $response["error"] = true;
        $response["message"] = "Oops! An error occurred while registereing";
        echoRespnse(200, $response);
    }
});


/**
 * Listing all tasks of particual user
 * method GET
 * url /tasks
 */
$app->get('/rent_query', function () {
    $response = array();
    $db = new DbHandler();

    // fetching all user tasks
    $result = $db->getQueryPosts();

    $response["error"] = false;
    $response["rent_query_post"] = array();

//    array_push($response["rents"], $result);

//     looping through result and preparing tasks array
    while ($task = $result->fetch_assoc()) {
        $tmp = array();
        $tmp["id"] = $task["id"];
        $tmp["area_id"] = $task["area_id"];
        $tmp["rent_type_id"] = $task["rent_type_id"];
        $tmp["banner"] = $task["banner"];
        $tmp["beds"] = $task["beds"];
        $tmp["validity"] = $task["validity"];
        array_push($response["rent_query_post"], $tmp);
    }


    echoRespnse(200, $response);
});

/**
 * Retrive specific Rental ad for detail View
 * method GET
 * url /rents/:rent_id
 */
$app->get('/rent_query/:query_id', function ($query_id) {
    $response = array();
    $db = new DbHandler();

    // fetching all user tasks
    $result = $db->getSingleQueryPost($query_id);

    $response["error"] = false;
    $response["rent_query_post"] = array();

    // looping through result and preparing tasks array
    while ($task = $result->fetch_assoc()) {
        $tmp = array();
        $tmp["id"] = $task["id"];
        $tmp["user_id"] = $task["user_id"];
        $tmp["area_id"] = $task["area_id"];
        $tmp["rent_type_id"] = $task["rent_type_id"];
        $tmp["banner"] = $task["banner"];
        $tmp["beds"] = $task["beds"];
        $tmp["baths"] = $task["baths"];
        $tmp["lift"] = $task["lift"];
        $tmp["parking"] = $task["parking"];
        $tmp["others"] = $task["others"];
        $tmp["rentprice_range"] = $task["rentprice_range"];
        $tmp["validity"] = $task["validity"];
        $tmp["posted_at"] = $task["posted_at"];
        array_push($response["rent_query_post"], $tmp);
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
 * Create a review for rental AD
 * method PUT
 * url /reviews
 */
$app->put('/reviews/:review_id', function ($review_id) use ($app) {
    // check for required params
    verifyRequiredParams(array('rating', 'review'));

    $response = array();

    // reading post params
    $ratings = $app->request->post('rating');
    $reviews = $app->request->post('review');

    $db = new DbHandler();
    $res = $db->updateRentalAdReviews($review_id, $ratings, $reviews);

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
 * Listing all reviews of particular rental ad
 * method GET
 * url /reviews/:rent_id
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
 * Create and Add to wishlist
 * method POST
 * url /wishlist
 */
$app->post('/wishlist', function () use ($app) {
    // check for required params
    verifyRequiredParams(array('rent_id', 'user_id'));

    $response = array();

    // reading post params
    $user_id = $app->request->post('user_id');
    $rent_id = $app->request->post('rent_id');

    $db = new DbHandler();
    $res = $db->createWishList($rent_id, $user_id);

    if ($res) {
        $response["error"] = false;
        $response["message"] = "Added To Wishlist Successfully";
        echoRespnse(201, $response);
    } else {
        $response["error"] = true;
        $response["message"] = "Oops! An error occurred while adding review";
        echoRespnse(200, $response);
    }
});


/**
 * Listing all wishlist of a particuler user
 * method GET
 * url /wishlist/:user_id
 */
$app->get('/wishlist/:user_id', function ($user_id) {
    $response = array();
    $db = new DbHandler();

    // fetching all user tasks
    $result = $db->getFullWishList($user_id);

    if ($result->num_rows > 0) {
        $response["error"] = false;
        $response["wishlist"] = array();

        // looping through result and preparing tasks array
        while ($task = $result->fetch_assoc()) {
            $tmp = array();
            $tmp["id"] = $task["id"];
            $tmp["user_id"] = $task["user_id"];
            $tmp["rent_id"] = $task["rent_id"];
            array_push($response["wishlist"], $tmp);
        }
    } else {
        $response["error"] = true;
        $response["wishlist"] = array();
    }

    echoRespnse(200, $response);
});

/**
 * Send message to rental AD owner
 * method POST
 * url /rmessages
 */
$app->post('/rmessages', function () use ($app) {
    // check for required params
    verifyRequiredParams(array('rent_id', 'sender_id', 'receiver_id', 'message', 'status'));

    $response = array();

    // reading post params
    $rent_id = $app->request->post('rent_id');
    $sender_id = $app->request->post('sender_id');
    $receiver_id = $app->request->post('receiver_id');
    $message = $app->request->post('message');
    $status = $app->request->post('status');

    $db = new DbHandler();
    $res = $db->createRentMessage($rent_id, $sender_id, $receiver_id, $message, $status);

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
 * retriev messages of particular rental ad and user
 * method POST
 * url /rmessages/conv
 */
$app->post('/rmessages/conv', function () use ($app) {

    verifyRequiredParams(array('rent_id', 'sender_id'));
    $response = array();
    $db = new DbHandler();

    $rent_id = $app->request->post('rent_id');
    $sender_id = $app->request->post('sender_id');

    // fetching all user tasks
    $result = $db->getSingleRentalAdmessages($rent_id, $sender_id);

    if ($result->num_rows > 0) {
        $response["error"] = false;
        $response["rmessages"] = array();

        // looping through result and preparing tasks array
        while ($task = $result->fetch_assoc()) {
            $tmp = array();
            $tmp["id"] = $task["id"];
            $tmp["rent_id"] = $task["rent_id"];
            $tmp["sender_id"] = $task["sender_id"];
            $tmp["receiver_id"] = $task["receiver_id"];
            $tmp["message"] = $task["message"];
            $tmp["status"] = $task["status"];
            $tmp["time"] = $task["time"];
            array_push($response["rmessages"], $tmp);
        }
    } else {
        $response["error"] = true;
        $response["rmessages"] = array();
    }

    echoRespnse(200, $response);
});

/**
 * Send message to Query AD owner
 * method POST
 * url /qmessages
 */
$app->post('/qmessages', function () use ($app) {
    // check for required params
    verifyRequiredParams(array('query_id', 'sender_id', 'receiver_id', 'message', 'status'));

    $response = array();

    // reading post params
    $query_id = $app->request->post('query_id');
    $sender_id = $app->request->post('sender_id');
    $receiver_id = $app->request->post('receiver_id');
    $message = $app->request->post('message');
    $status = $app->request->post('status');

    $db = new DbHandler();
    $res = $db->createQueryMessage($query_id, $sender_id, $receiver_id, $message, $status);

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
 * retriev messages of particular query ad and user
 * method POST
 * url /qmessages/conv
 */
$app->post('/qmessages/conv', function () use ($app) {

    verifyRequiredParams(array('query_id', 'sender_id'));
    $response = array();
    $db = new DbHandler();

    $query_id = $app->request->post('query_id');
    $sender_id = $app->request->post('sender_id');

    // fetching all user tasks
    $result = $db->getSingleQueryAdMessages($query_id, $sender_id);

    if ($result->num_rows > 0) {
        $response["error"] = false;
        $response["qmessages"] = array();

        // looping through result and preparing tasks array
        while ($task = $result->fetch_assoc()) {
            $tmp = array();
            $tmp["id"] = $task["id"];
            $tmp["query_id"] = $task["query_id"];
            $tmp["sender_id"] = $task["sender_id"];
            $tmp["receiver_id"] = $task["receiver_id"];
            $tmp["message"] = $task["message"];
            $tmp["status"] = $task["status"];
            $tmp["time"] = $task["time"];
            array_push($response["qmessages"], $tmp);
        }
    } else {
        $response["error"] = true;
        $response["qmessages"] = array();
    }

    echoRespnse(200, $response);
});


/**
 * Listing all wishlist of a particuler user
 * method GET
 * url /rtype
 */
$app->get('/rtype', function () {
    $response = array();
    $db = new DbHandler();

    // fetching all user tasks
    $result = $db->getRentTypes();

    if ($result->num_rows > 0) {
        $response["error"] = false;
        $response["types"] = array();

        // looping through result and preparing tasks array
        while ($task = $result->fetch_assoc()) {
            $tmp = array();
            $tmp["id"] = $task["id"];
            $tmp["type"] = $task["type"];
            array_push($response["types"], $tmp);
        }
    } else {
        $response["error"] = true;
        $response["types"] = array();
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
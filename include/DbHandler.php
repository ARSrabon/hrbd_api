<?php

/**
 * Created by PhpStorm.
 * User: msrabon
 * Date: 1/3/17
 * Time: 9:40 PM
 */
class DbHandler
{

    private $conn;

    function __construct()
    {
        require_once dirname(__FILE__) . '/DbConnect.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    /* ------------- `users` table method ------------------ */

    /**
     * @param $user_id firebase authentication UID
     * @param $Username : username for better navigation
     * @param $fullName : users full name.
     * @param $email : user email address.
     * @param $mobile_no
     * @param $address
     * @param $city
     * @param $area
     * @return array|int
     *
     */
    public function createUser($user_id, $Username, $fullName, $email, $mobile_no, $address, $city, $area)
    {
        require_once 'PassHash.php';
        $response = array();

        // First check if user already existed in db
        if (!$this->isUserExists($email)) {

            // insert query
            $stmt = $this->conn->prepare("INSERT INTO users(user_id, Username, fullName, email, mobile_no, address, city, area) values(?, ?, ?, ?,?, ?, $city, $area)");
            if (!$stmt->bind_param("ssssss", $user_id, $Username, $fullName, $email, $mobile_no, $address)) {
                return $response;
            }

            $result = $stmt->execute();
//            var_dump($this->db->error);
            $stmt->close();

            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                return USER_CREATED_SUCCESSFULLY;
            } else {
                // Failed to create user
                return USER_CREATE_FAILED;
            }
        } else {
            // User with same email already existed in the db
            return USER_ALREADY_EXISTED;
        }

        return $response;
    }


    /**
     * Checking for duplicate user by email address
     * @param String $email email to check in db
     * @return boolean
     */
    private function isUserExists($email)
    {
        $stmt = $this->conn->prepare("SELECT id from users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    /**
     * Fetching user by email
     * @param String $email User email id
     */
    public function getUserByEmail($email)
    {
        $stmt = $this->conn->prepare("SELECT user_id,Username,fulName,mobile_no FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching user by user_id (Firebase UID)
     * @param $user_id
     * @return null
     */
    public function getUserByUID($user_id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->bind_param("s", $user_id);
        if ($stmt->execute()) {
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching all user tasks
     * @param String $user_id id of the user
     */
    public function getAllUserRents($user_id)
    {
        $sql = "SELECT * FROM rents WHERE user_id = '$user_id'";
        $tasks = $this->conn->query($sql);
        return $tasks;
    }

/**
     * Fetching all user tasks
     * @param String $user_id id of the user
     */
    public function getAllUserQuery($user_id)
    {
        $sql = "SELECT * FROM rent_queries WHERE user_id = '$user_id'";
        $tasks = $this->conn->query($sql);
        return $tasks;
    }


    /**
     * Creating new rent
     * @param String $user_id user id to whom task belongs to
     * @param String $task task text
     */
    public function createRentalAd($user_id,$area_id,$rent_type_id,$banner,$beds,$baths,$size,$floordetails,$lift,$parking,
                                   $rentprice,$rentdetails,$address,$geoloc_lat,$geoloc_lng,$available_date,$img_banner,$img_other_one,$img_other_two)
    {

        $date = DateTime::createFromFormat('Y-m-d', $available_date);
        $available = $date->format('Y-m-d');

        $sql = "INSERT INTO rents (user_id, area_id, rent_type_id, banner, beds, baths, size, floordetails, lift, parking, 
rentprice, rentdetails, address, geoloc_lat, geoloc_lng, available, img_banner, img_other_one, img_other_two) 
VALUES('$user_id',$area_id,$rent_type_id,'$banner',$beds,$baths,$size,'$floordetails',$lift,$parking, $rentprice,'$rentdetails','$address',$geoloc_lat,$geoloc_lng,'$available','$img_banner','$img_other_one','$img_other_two')";

        $result = $this->conn->query($sql);

        if ($result) {
            return $result;
        } else {
            // task failed to create
            return NULL;
        }
    }

    public function getRentalAds()
    {
        $sql = "SELECT id,banner,area_id,rent_type_id,beds,size,rentprice,available,avgrating,reviews,img_banner 
FROM rents INNER JOIN (SELECT rents_id,AVG(rating) as avgrating,COUNT(rents_id) as reviews FROM `reviews` GROUP BY rents_id)AS t2 ON rents.id = t2.rents_id ";
        $result = $this->conn->query($sql);
        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function getSingleRentalAds($rent_id)
    {
        $sql = " SELECT * FROM rents WHERE id = $rent_id";
        $result = $this->conn->query($sql);
        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }


    public function updateRentalAd($user_id, $area_id, $city_id, $banner, $beds, $baths, $floorDetails, $lift, $parking,
                                   $rentPrice, $rentDetails, $location, $Address, $img_banner, $img_other_one, $img_other_two)
    {
        $sql = "INSERT INTO rents(user_id,area_id,city_id,banner,beds,baths,floorDeatils,lift,
                                                  parking,rentPrice,rentDetails,location,Address,img_banner,img_other_one,img_other_two) 
                                                  VALUES($user_id,$area_id,$city_id,$banner,$beds,$baths,$floorDetails,
                                                  $lift,$parking,$rentPrice,$rentDetails,$location,$Address,$img_banner,
                                                  $img_other_one,$img_other_two)";

        $result = $this->conn->query($sql);
        if ($result) {
            return $result;
        } else {
            // task failed to create
            return NULL;
        }
    }

    public function createRentalAdReviews($rent_id, $user_id, $rating, $review)
    {
        $sql = "INSERT INTO reviews(rents_id,user_id,rating,review) VALUES($rent_id,'$user_id',$rating,'$review')";
        $result = $this->conn->query($sql);
        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function updateRentalAdReviews($review_id, $rating, $review)
    {
        $sql = "UPDATE reviews SET rating= $rating,review='$review' WHERE id= $review_id";
        $result = $this->conn->query($sql);
        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function getSingleRentalAdReviews($rent_id)
    {
        $sql = " SELECT * FROM reviews WHERE rents_id = $rent_id";
        $result = $this->conn->query($sql);
        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function createRentMessage($rent_id, $sender_id, $receiver_id, $message, $status)
    {
        $sql = "INSERT INTO rent_messages(rent_id,sender_id,receiver_id,message,status) VALUES($rent_id,'$sender_id','$receiver_id','$message',$status)";
        $result = $this->conn->query($sql);
        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function getSingleRentalAdmessages($rent_id, $sender_id)
    {
        $sql = " SELECT * FROM `rent_messages` WHERE rent_id = $rent_id AND sender_id='$sender_id' OR receiver_id='$sender_id'";
        $result = $this->conn->query($sql);
        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function createQueryPost($query_id, $sender_id, $receiver_id, $message, $status)
    {
        $sql = "INSERT INTO query_messages(query_id,sender_id,receiver_id,message,status) VALUES($query_id,'$sender_id','$receiver_id','$message',$status)";
        $result = $this->conn->query($sql);
        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function getQueryPosts()
    {
        $sql = "SELECT id,banner,area_id,rent_type_id,beds,size,rentprice,available,avgrating,reviews,img_banner 
FROM rents INNER JOIN (SELECT rents_id,AVG(rating) as avgrating,COUNT(rents_id) as reviews FROM `reviews` GROUP BY rents_id)AS t2 ON rents.id = t2.rents_id ";
        $result = $this->conn->query($sql);
        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function getSingleQueryPost($query_id, $sender_id)
    {
        $sql = " SELECT * FROM query_messages WHERE query_id = $query_id AND sender_id='$sender_id' OR receiver_id='$sender_id'";
        $result = $this->conn->query($sql);
        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function createQueryMessage($query_id, $sender_id, $receiver_id, $message, $status)
    {
        $sql = "INSERT INTO query_messages(query_id,sender_id,receiver_id,message,status) VALUES($query_id,'$sender_id','$receiver_id','$message',$status)";
        $result = $this->conn->query($sql);
        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function getSingleQueryAdMessages($query_id, $sender_id)
    {
        $sql = " SELECT * FROM query_messages WHERE query_id = $query_id AND sender_id='$sender_id' OR receiver_id='$sender_id'";
        $result = $this->conn->query($sql);
        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function createWishList($rent_id, $user_id)
    {
        $sql = "INSERT INTO wishlist(user_id, rent_id) VALUES ('$user_id',$rent_id)";
        $result = $this->conn->query($sql);
        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function getFullWishList($user_id)
    {
        $sql = "SELECT * FROM wishlist WHERE user_id='$user_id'";
        $result = $this->conn->query($sql);
        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public  function getRentTypes(){
        $sql = "SELECT * FROM rent_types";
        $result = $this->conn->query($sql);
        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

}

?>
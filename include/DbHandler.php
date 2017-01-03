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
        $stmt = $this->conn->prepare("SELECT Username,fulName,mobile_no FROM users WHERE user_id = ?");
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
     * Creating new rent
     * @param String $user_id user id to whom task belongs to
     * @param String $task task text
     */
    public function createTask($user_id, $area_id, $city_id, $banner, $beds, $baths, $floorDetails, $lift, $parking, $rentPrice,
                               $rentDetails, $location, $Address, $img_banner, $img_other_one, $img_other_two)
    {
        $stmt = $this->conn->prepare("INSERT INTO rents(user_id,area_id,city_id,banner,beds,baths,floorDeatils,lift,
                                                  parking,rentPrice,rentDetails,location,Address,img_banner,img_other_one,img_other_two) 
                                                  VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("ssssssssssssss", $user_id, $area_id, $city_id, $banner, $beds, $baths, $floorDetails, $lift, $parking, $rentPrice,
            $rentDetails, $location, $Address, $img_banner, $img_other_one, $img_other_two);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            // task row created
            // now assign the task to user
            $new_task_id = $this->conn->insert_id;
            $res = $this->createUserTask($user_id, $new_task_id);
            if ($res) {
                // task created successfully
                return $new_task_id;
            } else {
                // task failed to create
                return NULL;
            }
        } else {
            // task failed to create
            return NULL;
        }
    }

}

?>
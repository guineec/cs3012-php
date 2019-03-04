<?php
/*
 *  CS3012 - Software Engineering
 *  Group Project - Inventory Management App
 *  Team-ID: AA16
 *
 *  A (somewhat) RESTful API for querying the database
 *  Better routing not implemented
 */

//TODO: Login System

header("Content-Type:application/json");
//Turn off error reporting for security
//error_reporting(0);

//Includes
require_once __DIR__ . '/vendor/autoload.php';
require_once(__DIR__ . "/queries.php");

//Establish connection to database
$conn = new mysqli("HOSTNAME_HERE", "USER_NAME_HERRE", "DB_PASS_HERE", "DB_NAME_HERE");

//----------------------------Remove in live------------------------------------
if ($conn->connect_error) {
    die("Connection to Database Failed: " . $conn->connect_error);
}

//-------------------------------------------------------------------------------------


define("NULL_DATA", "");

$req_method = $_SERVER['REQUEST_METHOD'];

if ($req_method == 'GET') {


    //Respond to GET requests
    if(!empty($_GET['op'])) {
        $operation = mysqli_real_escape_string($conn, $_GET['op']);
        if($operation == 'ping') {
            //Check that there is a connection to the api
            deliver_response(200, "API connection working", NULL_DATA);
        } else if($operation == 'listObjectsByDate') {
            //Return a list of objects that have a user specified end-date
            $target_end_date = mysqli_real_escape_string($conn, $_GET['endDate']);
            $user_id = mysqli_real_escape_string($conn, $_GET['userId']);
            $data = get_objects_by_end_date($conn, $target_end_date, $user_id);
            if($data == NULL) {
                deliver_response(404, "no results", NULL_DATA);
            } else {
                deliver_response(200, "listObjectsByDate success", $data);
            }
        } else if($operation == 'getObjsAttachedToInd') {
            //Return a list of objects attached to a given individual
            $individual_id = mysqli_real_escape_string($conn, $_GET['indId']);
            $user_id = mysqli_real_escape_string($conn, $_GET['userId']);
            $data = get_objects_attached_to_individual($conn, $individual_id, $user_id);
            if($data == NULL) {
                deliver_response(404, "no results", NULL_DATA);
            } else {
                deliver_response(200, "getObjsAttachedToInd success", $data);
            }
        } else if($operation == 'getObjsAttachedToProj') {
            //Return a list of objects attached to a given individual
            $project_id = mysqli_real_escape_string($conn, $_GET['projId']);
            $user_id = mysqli_real_escape_string($conn, $_GET['userId']);
            $data = get_objects_attached_to_project($conn, $project_id, $user_id);
            if($data == NULL) {
                deliver_response(404, "no results", NULL_DATA);
            } else {
                deliver_response(200, "getObjsAttachedToProj success", $data);
            }
        } else if($operation == 'getProjUsing') {
            $obj_id = mysqli_real_escape_string($conn, $_GET['objId']);
            $user_id = mysqli_real_escape_string($conn, $_GET['userId']);
            $data = get_project_using($conn, $obj_id, $user_id);
            if($data == NULL) {
                deliver_response(404, "No results", NULL_DATA);
            } else {
                deliver_response(200, "project found", $data);
            }
        } else if($operation == 'getIndResponsibleFor') {
            $obj_id = mysqli_real_escape_string($conn, $_GET['objId']);
            $user_id = mysqli_real_escape_string($conn, $_GET['userId']);
            $data = get_ind_responsible_for($conn, $obj_id, $user_id);
            if($data == NULL) {
                deliver_response(404, "No results", NULL_DATA);
            } else {
                deliver_response(200, "individual found", $data);
            }
        } else if($operation == "findObjects") {
            //Find any objects matching the given barcode
            $barcode = mysqli_real_escape_string($conn, $_GET['barcode']);
            $user_id = mysqli_real_escape_string($conn, $_GET['userId']);
            $data = find_objects($conn, $barcode, $user_id);
            if($data == NULL) {
                deliver_response(404, "no results", NULL_DATA);
            } else {
                deliver_response(200, "findObjects success", $data);
            }
        } else if($operation == 'listObjects') {
            //Return a list of all objects for a given user
            $user_id = mysqli_real_escape_string($conn, $_GET["userId"]);
            $data = list_objects($conn, $user_id);
            if($data == NULL) {
                deliver_response(404, "no results found", NULL_DATA);
            } else {
                deliver_response(200, "listObjects success", $data);
            }
        } else if($operation == "listIndividuals") {
            //Return a list of all individuals added by the given user
            $user_id = mysqli_real_escape_string($conn, $_GET["userId"]);
            $data = list_individuals($conn, $user_id);
            if($data == NULL) {
                deliver_response(404, "no results found", NULL_DATA);
            } else {
                deliver_response(200, "listIndividuals success", $data);
            }
        } else if($operation == "listProjects") {
            //Return a list of all projects owned by given user
            $user_id = mysqli_real_escape_string($conn, $_GET["userId"]);
            $data = list_projects($conn, $user_id);
            if($data == NULL) {
                deliver_response(404, "no results found", NULL_DATA);
            } else {
                deliver_response(200, "listProjects success", $data);
            }
        } else if($operation == "listDamaged") {
            //Return a list of damaged objects
            $user_id = mysqli_real_escape_string($conn, $_GET['userId']);
            $data = list_damaged($conn, $user_id);
            if($data == NULL) {
                deliver_response(404, "no results found", NULL_DATA);
            } else {
                deliver_response(200, "listDamaged success", $data);
            }
        } else if($operation == 'getIndsAttachedToProj') {
            //Return a list of individuals attached to the given project
            $user_id = mysqli_real_escape_string($conn, $_GET['userId']);
            $project_id = mysqli_real_escape_string($conn, $_GET['projId']);
            $data = get_inds_attached_to_proj($conn, $project_id, $user_id);
            if($data == NULL) {
                deliver_response(404, "no results found", NULL_DATA);
            } else {
                deliver_response(200, "success", $data);
            }
        } else {
            deliver_response(400, "no get op specified", NULL_DATA);
        }
    }

} else if ($req_method == 'POST') {

    //Respond to POST requests
    if (!empty($_POST['op'])) {
        $operation = mysqli_real_escape_string($conn, $_POST['op']);
        if ($operation == 'addProject') {
            //Add a project to the database
            $proj_end_date = mysqli_real_escape_string($conn, $_POST['endDate']);
            $created_by = mysqli_real_escape_string($conn, $_POST['userId']);
            $initial_individual = mysqli_real_escape_string($conn, $_POST['individualId']);
            $proj_name = mysqli_real_escape_string($conn, $_POST['projectName']);
            $result = add_project($conn, $proj_name, $proj_end_date, $initial_individual, $created_by);
            if ($result == "success") {
                deliver_response(200, "addProject success", NULL_DATA);
            } else if($result == "duplicate") {
                deliver_response(200, "addProject failed", "already exists");
            } else {
                deliver_response(404, "addProject failed", NULL_DATA);
            }
        } else if ($operation == 'addIndividual') {
            //Add an individual to the database
            $ind_name = mysqli_real_escape_string($conn, $_POST['indName']);
            $created_by = mysqli_real_escape_string($conn, $_POST['userId']);
            if(add_individual($conn, $ind_name, $created_by)) {
                deliver_response(200, "addIndividual success", NULL_DATA);
            } else {
                deliver_response(404, "addIndividual failed", NULL_DATA);
            }
        } else if($operation == 'attachIndToProj') {
            //Attach an individual to a project
            $individual_id = mysqli_real_escape_string($conn, $_POST['indId']);
            $project_id = mysqli_real_escape_string($conn, $_POST['projId']);
            if(attach_ind_to_proj($conn, $individual_id, $project_id)) {
                deliver_response(200, "attachIndToProj success", NULL_DATA);
            } else {
                deliver_response(404, "attachIndToProj failed", NULL_DATA);
            }
        } else if($operation == 'addObject') {
            //Add an object to the database
            $barcode = mysqli_real_escape_string($conn, $_POST['barcode']);
            $created_by = mysqli_real_escape_string($conn, $_POST['userId']);
            if(!empty($_POST['projId'])) {
                $project_id = mysqli_real_escape_string($conn, $_POST['projId']);
            } else {
                $project_id = "";
            }
            if(!empty($_POST['indId'])) {
                $individual_id = mysqli_real_escape_string($conn, $_POST['indId']);
            } else {
                $individual_id = "";
            }
            if(!empty($_POST['description'])) {
                $description = mysqli_real_escape_string($conn,$_POST['description']);
            } else {
                $description = "";
            }

            if(add_object($conn, $barcode, $individual_id, $project_id, $description, $created_by)) {
                deliver_response(200, "addObject success", NULL_DATA);
            } else {
                deliver_response(404, "addObject failed", NULL_DATA);
            }
        } else if($operation == 'attachObjToInd') {
            //Attach an object to an individual
            $obj_id = mysqli_real_escape_string($conn, $_POST['objId']);
            $individual_id = mysqli_real_escape_string($conn, $_POST['individualId']);
            $user_id = mysqli_real_escape_string($conn, $_POST['userId']);
            if(attach_obj_to_ind($conn, $obj_id, $individual_id, $user_id)) {
                deliver_response(200, "attachObjToInd successful", NULL_DATA);
            } else {
                deliver_response(404, "attachObjToInd failed", NULL_DATA);
            }
        } else if($operation == 'attachObjToProj') {
            //Attach an object to a project
            $obj_id = mysqli_real_escape_string($conn, $_POST['objId']);
            $individual_id = mysqli_real_escape_string($conn, $_POST['individualId']);
            $project_id = mysqli_real_escape_string($conn, $_POST['projId']);
            $user_id = mysqli_real_escape_string($conn, $_POST['userId']);
            if(attach_obj_to_proj($conn, $obj_id, $individual_id, $project_id, $user_id)) {
                deliver_response(200, "attachObjToInd successful", NULL_DATA);
            } else {
                deliver_response(404, "attachObjToInd failed", NULL_DATA);
            }
        } else if($operation == 'addUser') {
            //Add a new user of the app
            $name = mysqli_real_escape_string($conn, $_POST['userName']);
            $email = mysqli_real_escape_string($conn, $_POST['userEmail']);
            $hashedPass = mysqli_real_escape_string($conn, $_POST['pass']);
            $hashedPass = hash('sha512', $hashedPass);
            $result = add_user($conn, $name, $email, $hashedPass);
            if($result == 'success') {
                deliver_response(200, "new user added", NULL_DATA);
            } else if($result == 'duplicate') {
                deliver_response(404, "addUser failed", "email exists");
            } else {
                deliver_response(404, "addUser failed", NULL_DATA);
            }
        } else if($operation == 'login') {
            //Login as a User of the app.
            $email = mysqli_real_escape_string($conn, $_POST['userEmail']);
            $password = hash("sha512", mysqli_real_escape_string($conn, $_POST['pass']));
            $result = login($conn, $email, $password);
            if($result == NULL) {
                deliver_response(404, "login failed", NULL_DATA);
            } else {
                deliver_response(200, "login success", $result);
            }
        } else if($operation == 'markDamaged') {
            //Mark an object as damaged
            $obj_id = mysqli_real_escape_string($conn, $_POST['objId']);
            $user_id = mysqli_real_escape_string($conn, $_POST['userId']);
            $result = mark_damaged($conn, $obj_id, $user_id);
            if($result) {
                deliver_response(200, "marked as damaged", NULL_DATA);
            } else {
                deliver_response(404, "Couldn't mark damaged", NULL_DATA);
            }
        } else if($operation == "markFixed") {
            //Mark an object as fixed
            $obj_id = mysqli_real_escape_string($conn, $_POST['objId']);
            $user_id = mysqli_real_escape_string($conn, $_POST['userId']);
            $result = mark_fixed($conn, $obj_id, $user_id);
            if($result) {
                deliver_response(200, "marked as fixed", NULL_DATA);
            } else {
                deliver_response(404, "couldn't mark fixed", NULL_DATA);
            }
        } else {
            deliver_response(404, "Not a valid operation", NULL_DATA);
        }
    } else {
        deliver_response(404, "No post op specified", NULL_DATA);
    }

} else {


    deliver_response(400, "bad request method", NULL_DATA);


}

function deliver_response($status_code, $status_message, $data)
{
    //header($status_code, $status_message);
    $response['status_code'] = $status_code;
    $response['status_message'] = $status_message;
    $response['data'] = $data;

    $json = json_encode($response);
    echo $json;
}
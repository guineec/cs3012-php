<?php
/*  CS3012 - Software Engineering
 *  Group Project - Inventory Management App
 *  Team-ID: AA16
 *
 *  Functions to query the database, based on what is needed by the API.
 */

//Insertion queries
function add_project(mysqli $dbconn, $name, $end_date, $ind_id, $user_id)
{
    $end_date = new DateTime($end_date);
    $end_date = $end_date->format('Y-m-d');
    $query = "SELECT * FROM `projects` WHERE name = '$name' AND created_by = '$user_id'";
    $result = $dbconn->query($query);
    if ($result->num_rows > 0 && $result != FALSE) {
        return "duplicate";
    } else {
        $query = "INSERT INTO `projects` (end_date, created_by, name) VALUES ('$end_date', '$user_id', '$name');";
        if ($dbconn->query($query) === TRUE) {
            $proj_id = $dbconn->insert_id;
            $query = "INSERT INTO `individuals_projects` (individual_id, project_id) VALUES ('$ind_id', '$proj_id')";
            if ($dbconn->query($query) === TRUE) {
                return "success";
            } else {
                return FALSE;
            }
        }
    }
}

function add_individual(mysqli $dbconn, $name, $user_id)
{
    $query = "INSERT INTO `individuals` (name, created_by) VALUE ('$name', '$user_id')";
    if ($dbconn->query($query) === TRUE) {
        return TRUE;
    } else {
        return FALSE;
    }
}

function attach_ind_to_proj(mysqli $dbconn, $ind_id, $proj_id)
{
    $query = "INSERT INTO `individuals_projects` (individual_id, project_id) VALUES ('$ind_id', '$proj_id')";
    if ($dbconn->query($query) === TRUE) {
        return TRUE;
    } else {
        return false;
    }
}

function add_object(mysqli $dbconn, $bar, $ind_id, $proj_id, $desc, $user_id)
{
    $query = "INSERT INTO `objects` (barcode, project_id, individual_id, description, created_by) VALUES ('$bar', '$proj_id', '$ind_id', '$desc', $user_id)";
    if ($dbconn->query($query) === TRUE) {
        return TRUE;
    } else {
        return FALSE;
    }
}

function attach_obj_to_ind(mysqli $dbconn, $id, $ind_id, $user_id)
{
    $query = "UPDATE `objects` SET individual_id = '$ind_id' WHERE id = '$id' AND created_by = '$user_id'";
    if ($dbconn->query($query)) {
        return TRUE;
    } else {
        return FALSE;
    }
}

function attach_obj_to_proj(mysqli $dbconn, $id, $ind_id, $proj_id, $user_id)
{
    if (attach_obj_to_ind($dbconn, $id, $ind_id, $user_id)) {
        $query = "UPDATE `objects` SET project_id = '$proj_id' WHERE id = '$id' AND created_by = '$user_id'";
        if ($dbconn->query($query)) {
            return TRUE;
        } else {
            return FALSE;
        }
    } else {
        return FALSE;
    }
}

function add_user(mysqli $dbconn, $name, $email, $hashedPass)
{
    $query = "SELECT * FROM `users` WHERE email = '$email'";
    $result = $dbconn->query($query);
    if ($result->num_rows > 0 && result != FALSE) {
        return "duplicate";
    } else {
        $query = "INSERT INTO `users` (name, email, password) VALUES ('$name', '$email', '$hashedPass')";
        if ($dbconn->query($query)) {
            return "success";
        } else {
            return FALSE;
        }
    }
}

function mark_damaged(mysqli $dbconn, $obj_id, $user_id)
{
    $query = "UPDATE `objects` SET damaged = 1 WHERE id = '$obj_id' AND created_by = '$user_id'";
    if($dbconn->query($query)) {
        return TRUE;
    } else {
        return FALSE;
    }

}

function mark_fixed(mysqli $dbconn, $obj_id, $user_id)
{
    $query = "UPDATE `objects` SET damaged = 0 WHERE id = '$obj_id' AND created_by = '$user_id'";
    if($dbconn->query($query)) {
        return TRUE;
    } else {
        return FALSE;
    }
}


//Retreival queries

function get_objects_by_end_date(mysqli $dbconn, $end_date, $user_id)
{
    $formatted_end_date = new DateTime($end_date);
    $formatted_end_date = $formatted_end_date->format("Y-m-d");

    $query = "SELECT * FROM `projects` WHERE end_date = '$formatted_end_date' AND created_by = '$user_id'";
    $result = $dbconn->query($query);
    $ids = array();
    $i = 0;
    if ($result->num_rows > 0 && $result != false) {
        while ($row = $result->fetch_assoc()) {
            $ids[$i] = $row['id'];
            $i++;
        }

        $ret_arr = array();
        $i = 0;
        foreach ($ids as $id) {
            $query = "SELECT * from `objects` WHERE project_id = '$id' AND created_by = '$user_id' AND damaged = 0";
            $result = $dbconn->query($query);
            if ($result->num_rows > 0 && $result != FALSE) {
                while ($row = $result->fetch_assoc()) {
                    $ret_arr[$i] = $row;
                    $i++;
                }
            }
        }
        return $ret_arr;
    } else {
        return NULL;
    }
}

function get_objects_attached_to_individual(mysqli $dbconn, $ind_id, $user_id)
{
    $query = "SELECT * FROM `objects` WHERE individual_id = '$ind_id' AND created_by = '$user_id'";

    $result = $dbconn->query($query);
    $ret_arr = array();
    if ($result->num_rows > 0 && $result != FALSE) {
        $i = 0;
        while ($row = $result->fetch_assoc()) {
            $ret_arr[$i] = $row;
            $i++;
        }

        return $ret_arr;
    } else {
        return NULL;
    }
}

function get_objects_attached_to_project(mysqli $dbconn, $proj_id, $user_id)
{
    $query = "SELECT * FROM `objects` WHERE project_id = '$proj_id' AND created_by = '$user_id'";

    $result = $dbconn->query($query);
    $ret_arr = array();
    if ($result->num_rows > 0 && $result != FALSE) {
        $i = 0;
        while ($row = $result->fetch_assoc()) {
            $ret_arr[$i] = $row;
            $i++;
        }

        return $ret_arr;
    } else {
        return NULL;
    }
}

function get_project_using(mysqli $dbconn, $obj_id, $user_id)
{
    $query = "SELECT * FROM `objects` WHERE id = '$obj_id' AND created_by = $user_id";
    $result = $dbconn->query($query);
    if($result->num_rows <= 0 || $result == NULL) {
        return NULL;
    } else {
        $row = $result->fetch_assoc();
        $proj_id = $row['project_id'];

        $query = "SELECT * FROM `projects` WHERE id = '$proj_id' AND created_by = '$user_id'";
        $result = $dbconn->query($query);
        $ret_array = array();
        $i = 0;
        if($result->num_rows > 0 && $result != NULL) {
            while($row = $result->fetch_assoc()) {
                $ret_array[$i] = $row;
                $i++;
            }
            return $ret_array;
        } else {
            return NULL;
        }
    }
}

function get_ind_responsible_for(mysqli $dbconn, $obj_id, $user_id)
{
    $query = "SELECT * FROM `objects` WHERE id = '$obj_id' AND created_by = $user_id";
    $result = $dbconn->query($query);
    if($result->num_rows <= 0 || $result == NULL) {
        return NULL;
    } else {
        $row = $result->fetch_assoc();
        $ind_id = $row['individual_id'];

        $query = "SELECT * FROM `individuals` WHERE id = '$ind_id' AND created_by = '$user_id'";
        $result = $dbconn->query($query);
        $ret_array = array();
        $i = 0;
        if($result->num_rows > 0 && $result != NULL) {
            while($row = $result->fetch_assoc()) {
                $ret_array[$i] = $row;
                $i++;
            }
            return $ret_array;
        } else {
            return NULL;
        }
    }
}

function get_inds_attached_to_proj(mysqli $dbconn, $proj_id, $user_id)
{
    $query = "SELECT * FROM `individuals_projects` WHERE project_id = '$proj_id'";
    $result = $dbconn->query($query);
    $ids = array();
    $i = 0;
    if($result->num_rows > 0 && $result != null) {
        while($row = $result->fetch_assoc()) {
            $ids[$i] = $row['individual_id'];
            $i++;
        }

        $i = 0;
        $ret_arr = array();
        foreach($ids as $id) {
            $query = "SELECT * FROM `individuals` WHERE id = '$id' AND created_by = '$user_id'";
            $result = $dbconn->query($query);
            while($row = $result->fetch_assoc()) {
                $ret_arr[$i] = $row;
                $i++;
            }
        }
        return $ret_arr;
    } else {
        return NULL;
    }
}

function find_objects(mysqli $dbconn, $barcode, $user_id)
{
    $query = "SELECT * FROM `objects` WHERE barcode = '$barcode' AND created_by = '$user_id'";

    $result = $dbconn->query($query);
    $ret_arr = array();
    if ($result->num_rows > 0 && $result != FALSE) {
        $i = 0;
        while ($row = $result->fetch_assoc()) {
            $ret_arr[$i] = $row;
            $i++;
        }

        return $ret_arr;
    } else {
        return NULL;
    }
}

function list_objects(mysqli $dbconn, $user_id)
{
    $query = "SELECT * FROM `objects` WHERE created_by = '$user_id'";
    $result = $dbconn->query($query);
    $ret_arr = array();
    if ($result->num_rows > 0 && $result != FALSE) {
        $i = 0;
        while ($row = $result->fetch_assoc()) {
            $ret_arr[$i] = $row;
            $i++;
        }

        return $ret_arr;
    } else {
        return NULL;
    }
}

function list_individuals(mysqli $dbconn, $user_id)
{
    $query = "SELECT * FROM `individuals` WHERE created_by = '$user_id'";
    $result = $dbconn->query($query);
    $ret_arr = array();
    if ($result->num_rows > 0 && $result != FALSE) {
        $i = 0;
        while ($row = $result->fetch_assoc()) {
            $ret_arr[$i] = $row;
            $i++;
        }

        return $ret_arr;
    } else {
        return NULL;
    }
}

function list_projects(mysqli $dbconn, $user_id)
{
    $query = "SELECT * FROM `projects` WHERE created_by = '$user_id'";
    $result = $dbconn->query($query);
    $ret_arr = array();
    if ($result->num_rows > 0 && $result != FALSE) {
        $i = 0;
        while ($row = $result->fetch_assoc()) {
            $ret_arr[$i] = $row;
            $i++;
        }

        return $ret_arr;
    } else {
        return NULL;
    }
}

function list_damaged(mysqli $dbconn, $user_id)
{
    $query = "SELECT * FROM `objects` WHERE created_by = '$user_id' AND damaged = 1";
    $result = $dbconn->query($query);
    $ret_arr = array();
    if ($result->num_rows > 0 && $result != FALSE) {
        $i = 0;
        while ($row = $result->fetch_assoc()) {
            $ret_arr[$i] = $row;
            $i++;
        }

        return $ret_arr;
    } else {
        return NULL;
    }
}

//Login system
function login(mysqli $dbconn, $email, $pass)
{
    $query = "SELECT * FROM `users` WHERE email = '$email' AND password = '$pass'";
    $result = $dbconn->query($query);
    if ($result->num_rows > 0 && $result != FALSE) {
        $row = mysqli_fetch_row($result);
        $ret = array();
        $ret['id'] = $row[0];
        $ret['name'] = $row[1];
        $ret['email'] = $row[2];
        return $ret;
    } else {
        return NULL;
    }
}
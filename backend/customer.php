<?php

require_once 'settings.php';

/**
 * Functions related to creation, editing, and deletion of customers
 * and customer information. Some of which is not solely located in the 
 * customers and customerattributes table.
 * 
 * Customers are stored in the customers table with this schema
 * Field           | Type         | Null | Key | Default           | Extra
 * id              | int(11)      | NO   | PRI | NULL              | auto_increment
 * name            | int(11)      | NO   | MUL | NULL              | 
 * email           | varchar(127) | NO   |     | NULL              | 
 * user_id         | timestamp    | YES  | MUL | NULL              | 
 * birthday        | timestamp    | YES  |     | NULL              | 
 * visits          | int(11)      | NO   | MUL | 0                 | 
 * status          | tinyint(1)   | NO   |     | 1                 | 
 * timestamp       | timestamp    | NO   |     | CURRENT_TIMESTAMP | 
 * 
 * Customer Attributes are stored in the customerattributes table with this schema
 * Field       | Type          | Null | Key | Default           | Extra
 * id          | int(11)       | NO   | PRI | NULL              | auto_increment
 * customer_id | int(11)       | NO   | MUL | NULL              | 
 * name        | varchar(128)  | NO   |     | NULL              | 
 * value       | varchar(8192) | NO   |     | NULL              | 
 * status      | tinyint(1)    | NO   |     | 1                 | 
 * timestamp   | timestamp     | NO   |     | CURRENT_TIMESTAMP | 
 */

function createCustomer($birthday, $email, $name){
    validateCustomerBirthday($birthday);
    validateCustomerName($name);
    validateCustomerEmail($email);
    $existingCustomerSQL = "
        SELECT COUNT(*) as count
        FROM customers
        WHERE customers.email = '$email'
        AND customers.name = '$name'
        AND customers.status = '1'";
    $existingCustomerQuery = mysql_query($existingCustomerSQL) or die(mysql_error());
    $existingCustomer = mysql_fetch_array($existingCustomerQuery);
    if($existingCustomer['count'] == 0){
        $insertCustomerSQL = "INSERT INTO customers VALUES('', '$name', '$email', '', '$birthday', '0', '1', CURRENT_TIMESTAMP)";
        mysql_query($insertCustomerSQL) or die(mysql_error());
        return readCustomer(mysql_insert_id());
    } else {
        throw new Exception("customer already exists with provided name and email");
    }
}

function readCustomer($cid){
    validateCustomerID($cid);
    $existingCustomerSQL = "
        SELECT *
        FROM customers
        WHERE customers.id = '$cid'";
    $existingCustomerQuery = mysql_query($existingCustomerSQL) or die(mysql_error());
    $existingCustomer = mysql_fetch_array($existingCustomerQuery);
    if($existingCustomer){
        return $existingCustomer;
    } else {
        throw new Exception("customerID refers to non-existent customer");
    }
}

function updateCustomer($birthday, $cid, $email, $name, $visits){
    validateCustomerBirthday($birthday);
    validateCustomerID($cid);
    validateCustomerEmail($email);
    validateCustomerName($name);
    validateCustomerVisits($visits);
    readCustomer($cid);
    $updateCustomerSQL = "
        UPDATE customers
        SET customers.birthday = '$birthday', customers.email = '$email',
            customers.name = '$name', customers.visits = '$visits'
        WHERE customers.id = '$cid'";
    mysql_query($updateCustomerSQL) or die(mysql_error());
    return readCustomer($cid);
}

function deleteCustomer($cid){
    validateCustomerID($cid);
    readCustomer($cid);
    $updateCustomerSQL = "
        UPDATE customers
        SET customers.status = '0'
        WHERE customers.id = '$cid'";
    mysql_query($updateCustomerSQL) or die(mysql_error());
    return readCustomer($cid);
}

/**
 * Decrements the number of visits for provided customer by 1
 * @param int $cid Customer ID
 * @throws Exception if $cid is not a positive integer
 * @throws Exception if number of customer visits is less than 1
 */
function decrementCustomerVisits($cid){
    if(!isInteger($cid) || $cid < 1){
        throw new Exception("Customer ID must be a positive integer");
    }
    $getCustomerVisitsSQL =
            "SELECT visits
            FROM customers
            WHERE customers.id = '$cid'";
    $visits = mysql_query($getCustomerVisitsSQL) or die(mysql_error());
    $visits = mysql_fetch_array($visits);
    if($visits['visits'] < 1){
        throw new Exception("Customer visits is less than 1 and cannot decrement.");
    }
    $decrementCustomerVisitsSQL =
            "UPDATE customers
            SET customers.visits = customers.visits - 1
            WHERE customers.id = '$cid'";
    mysql_query($decrementCustomerVisitsSQL) or die (mysql_error());
}

/**
 * Sets customer birthday to NULL
 * @param int $cid Customer ID
 * @throws Exception When Customer ID is not a positive integer.
 */
function deleteCustomerBirthday($cid){
    if(!isInteger($cid) || $cid < 1){
        throw new Exception("Customer ID must be a positive integer.");
    }
    $sql = "UPDATE customers
            SET customers.birthday = 'NULL'
            WHERE customers.id = '$cid'";
    mysql_query($sql) or die (mysql_error());
}

/**
 * Edits the Customer's Birthday
 * @param int $cid Customer ID
 * $param String $date Customer's Birthday in YYYY-MM-DD H:i:s format
 * @throws Exception if $cid is not a positive integer
 * @throws Exception if $birthday is not in YYYY-MM-DD H:i:s format
 */
function editCustomerBirthday($cid, $birthday){
    if(empty($birthday) || $birthday == "0000-00-00 00:00:00"){
        return deleteCustomerBirthday($cid);
    }
    if(!isInteger($cid) || $cid < 1){
        throw new Exception("Customer ID must be a positive integer.");
    }
    $dt = DateTime::createFromFormat("Y-m-d H:i:s", $birthday);
    if($dt === false || array_sum($dt->getLastErrors())){
        throw new Exception("Date format is incorrect.");
    }
    $sql = "SELECT birthday
            FROM customers
            WHERE id = '$cid'";
    $query = mysql_query($sql) or die (mysql_error());
    $result = mysql_fetch_array($query);
    if($result && $result['birthday'] != 'NULL'){
        $sql = "UPDATE customers
                SET customers.birthday = '$birthday'
                WHERE id = '$cid'";
        $query = mysql_query($sql) or die (mysql_error());
    } else {
        throw new Exception("Invalid Customer ID");
    }
}

/**
 * Edits the number of free entrances the customer has to the newly provided number
 * @param int $cid customer id number
 * @param int $number number of free entrances
 * @throws Exception if $cid is not a positive integer 
 * @throws Exception if $number is not a non-negative integer
 */
function editCustomerNumberOfFreeEntrances($cid, $number){
    if($number < 0 || !isInteger($number)){
        throw new Exception("Number must be a non-negative integer for free entrances");
    }
    if($cid < 1 || !isInteger($cid)){
        throw new Exception("Customer ID must be a positive integer");
    }
    $sql = "SELECT *
            FROM customerattributes
            WHERE customer_id = '$cid'
            AND name = 'Free Entrances'
            AND customerattributes.status = '1' ";
    $query = mysql_query($sql) or die (mysql_error());
    $result = mysql_fetch_array($query);
    if($result){
        $id = $result['id'];
        $sql = "UPDATE customerattributes SET value = '$number' WHERE id = '$id'";
        $query = mysql_query($sql) or die (mysql_error());
    }
    else{
        $sql = "INSERT INTO customerattributes VALUES(NULL, '$cid', 'Free Entrances', '$number', '1', CURRENT_TIMESTAMP)";
        $query = mysql_query($sql) or die (mysql_error());
    }
}

/**
 * Gets checkin ID based off the event ID and customer ID
 * @param int $cid Customer ID
 * @param int $eventID Event ID
 * @return int
 * @throws Exception if $cid is not a positive integer
 * @throws Exception if $eventID is not a positive integer
 */
function getCheckinIDForCustomerAndEvent($cid, $eventID){
    if($cid < 1 || !isInteger($cid)){
        throw new Exception("Customer ID must be a positive integer");
    }
    if($eventID < 1 || !isInteger($eventID)){
        throw new Exception("Event ID must be a positive integer");
    }
    $sql = "SELECT *
            FROM checkins
            WHERE customer_id = '$cid' 
            AND event_id = '$eventID'
            AND checkins.status = '1'";
    $query = mysql_query($sql) or die (mysql_error());
    $result = mysql_fetch_array($query);
    if($result){
        return $result['id'];
    }
    return 0;
}

/**
 * Gets the Customer's birthday
 * @param int $cid Customer ID
 * @return string Customer Birthday
 * @throws Exception if $cid is not a positive integer
 */
function getCustomerBirthday($cid){
    if(!isInteger($cid) || $cid < 1){
        throw new Exception("Customer ID must be a positive integer.");
    }
    $sql = "SELECT birthday
            FROM customers
            WHERE customers.id = '$cid'";
    $query = mysql_query($sql) or die (mysql_error());
    $result = mysql_fetch_array($query);
    if($result){
        return $result['birthday'];
    }
    else {
        return "";
    }
}

function getCustomerByCheckinID($checkinID){
    if(!isInteger($checkinID) || $checkinID < 1){
        throw new Exception("Checkin ID must be a positive integer.");
    }
    $sql =
        "SELECT checkins.id as checkinID, customers.id as cid, 
        customers.name as name, customers.email as email, 
        checkins.payment as payment, checkins.status as status,
        customers.birthday as birthday
        FROM checkins
        LEFT JOIN customers
        ON checkins.customer_id = customers.id
        WHERE checkins.id = '$checkinID'";
    $query = mysql_query($sql) or die(mysql_error());
    $result = mysql_fetch_array($query);
    if($result){
        $cid = $result['cid'];
        $result['isCheckedIn'] = $result['status'] == 1 ? true : false;
        $result['numberOfFreeEntrances'] = getCustomerNumberOfFreeEntrances($cid);
        $result['usedFreeEntrance'] = hasCustomerUsedFreeEntrance($cid, $checkinID);
    } else {
        throw new Exception("Invalid Checkin ID");
    }
    return $result;
}

/**
 * Gets Customer Checkin ID for Event ID provided
 * @param int $cid Customer ID
 * @param int $eventID Event ID
 * @return int
 * @throws Exception if $cid is not a positive integer
 * @throws Exception if $eventID is not a positive integer
 */
function getCustomerCheckinID($cid, $eventID){
    if(!isInteger($cid) || $cid < 1){
        throw new Exception("Customer ID must be a positive integer");
    }
    if(!isInteger($eventID) || $eventID < 1){
        throw new Exception("Event ID must be a positive integer");
    }
    $SQL = 
        "SELECT id
        FROM checkins
        WHERE checkins.customer_id = '$cid'
        AND checkins.event_id = '$eventID'
        AND checkins.status = '1'
        ORDER BY timestamp DESC";
    $query = mysql_query($SQL) or die (mysql_error());
    $result = mysql_fetch_array($query);
    if($result){
        return $result['id'];
    }
    return null;
}

/**
 * Gets Customer Payment for the Event ID provided
 * @param int $cid Customer ID
 * @param int $eventID Event ID
 * @return int
 * @throws Exception if $cid is not a positive integer
 * @throws Exception if $eventID is not a positive integer
 */
function getCustomerCheckedInPayment($cid, $eventID){
    if(!isInteger($cid) || $cid < 1){
        throw new Exception("Customer ID must be a positive integer");
    }
    if(!isInteger($eventID) || $eventID < 1){
        throw new Exception("Event ID must be a positive integer");
    }
    $result = mysql_query("
        SELECT payment
        FROM checkins
        WHERE customer_id = '$cid'
        AND event_id = '$eventID'
        AND checkins.status = '1'
        ORDER BY timestamp DESC
        LIMIT 1") or die(mysql_error());
    $result = mysql_fetch_array($result);
    if($result){
        return $result['payment'];
    }
    else{
        return null;
    }
}

/**
 * Returns the email of the customer given the cid
 * @param int $cid Customer ID
 * @return String
 * @throws Exception if $cid is not a positive integer
 */
function getCustomerEmail($cid){
    if(!isInteger($cid) || $cid < 1){
        throw new Exception("Customer ID must be a positive integer");
    }
    $sql = "SELECT email FROM customers WHERE id = '$cid'";
    $query = mysql_query($sql) or die (mysql_error());
    $result = mysql_fetch_array($query);
    return $result['email'];
}

/**
 * Gets the number of free entrances the customer has.
 * @param int $cid customer ID number
 * @throws Exception if $cid is not a positive integer
 */
function getCustomerNumberOfFreeEntrances($cid){
    if(!isInteger($cid) || $cid < 1){
        throw new Exception("Customer ID must be a positive integer");
    }
    $sql = "SELECT * FROM customerattributes
            WHERE customer_id = '$cid'
            AND name = 'Free Entrances'
            AND customerattributes.status = '1' ";
    $query = mysql_query($sql) or die (mysql_error());
    $result = mysql_fetch_array($query);
    if($result){
        return $result['value'];
    } else {
        return 0;
    }
}

/**
 * Returns whether the customer has checked in
 * for the event ID provided.
 * @param int $cid Customer ID
 * @param int $eventID Event ID
 * @return boolean
 * @throws Exception if $cid is not a positive integer
 * @throws Exception if $eventID is not a positive integer
 */
function hasCustomerCheckedIn($cid, $eventID){
    if(!isInteger($cid) || $cid < 1){
        throw new Exception("Customer ID must be a positive integer.");
    }
    if(!isInteger($eventID) || $eventID < 1){
        throw new Exception("Event ID must be a positive integer.");
    }
    $SQL =
        "SELECT *
        FROM checkins
        WHERE checkins.customer_id = '$cid'
        AND checkins.event_id = '$eventID'
        AND checkins.status = '1'";
    $query = mysql_query($SQL) or die (mysql_error());
    $result = mysql_fetch_array($query);
    if($result){
        return true;
    }
    else {
        return false;
    }
}

/**
 * Returns true or false as to whether the customer has used a free
 * entrance for the given event ID
 * @param int $cid Customer ID
 * @param int $checkinID Checkin ID
 * @return boolean
 * @throws Exception if $cid is not a positive integer
 * @throws Exception if $checkinID is not a positive integer
 */
function hasCustomerUsedFreeEntrance($cid, $checkinID){
    if(!isInteger($cid) || $cid < 1){
        throw new Exception("Customer ID must be a positive integer");
    }
    if(!isInteger($checkinID) || $checkinID < 1){
        throw new Exception("Checkin ID must be a positive integer");
    }
    $sql = "SELECT * FROM customerattributes
            WHERE customer_id = '$cid'
            AND name = 'Used Free Entrance'
            AND value = '$checkinID'
            AND customerattributes.status = '1'";
    $query = mysql_query($sql) or die (mysql_error());
    $result = mysql_fetch_array($query);
    if($result){
        return true;
    }
    else{
        return false;
    }
}

/**
 * Increments the number of visits for provided customer by 1
 * @param int $cid Customer ID
 * @throws Exception if $cid is not a positive integer
 */
function incrementCustomerVisits($cid){
    if(!isInteger($cid) || $cid < 1){
        throw new Exception("Customer ID must be a positive integer");
    }
    $incrementCustomerVisitsSQL =
            "UPDATE customers
            SET customers.visits = customers.visits + 1
            WHERE customers.id = '$cid'";
    mysql_query($incrementCustomerVisitsSQL) or die (mysql_error());
}

/**
 * Searches the customers table and returns the results
 * for a specific event (this is function is used for the 
 * front-end events.php page)
 * @param int $eventID Event ID
 * @param int $limit Limit the returned search results to a specific amount
 * @param string $name Name of the customer
 * @return array Where $array['customers'] contains an array of customers
 * from index 0 up to $limit with keys such as birthday, checkinID, cid,
 * email, payment, name, visits, isCheckedIn, usedFreeEntrance, and
 * numberOfFreeEntrances. It also contains a second key called 
 * $array['numberOfExtra'] which specifies how many people were not included
 * in the returned result but matched the criteon of LIKE %$name%
 * @throws Exception if $eventID is not a positive integer
 * @throws Exception if $limit is not a positive integer
 */
function searchCustomers($eventID, $limit, $name){
    if(!isInteger($eventID) || $eventID < 1){
        throw new Exception("Event ID must be a positive integer.");
    }
    if(!isInteger($limit) || $limit < 1){
        throw new Exception("Limit must be a positive integer.");
    }
    $numInSystemSQL =
        "SELECT COUNT(*) as count
        FROM customers
        WHERE status = '1'
        AND name LIKE '%$name%'
        ";
    $numInSystemQuery = mysql_query($numInSystemSQL) or die (mysql_error());
    $numInSystemNumber = mysql_fetch_array($numInSystemQuery);
    $numInSystemNumber = $numInSystemNumber['count'];
    $highestVisitsAndLikeName =
        "SELECT id as cid, name, visits, email, birthday
        FROM customers
        WHERE name LIKE '%$name%'
        AND status = '1'
        GROUP BY id
        ORDER BY visits DESC, name ASC, id DESC
        " . ($numInSystemNumber > ($limit + 1) ? ("LIMIT " .  $limit) : "");
    $visitquery = mysql_query($highestVisitsAndLikeName) or die (mysql_error());
    $customerArray = array();
    while($visit = mysql_fetch_array($visitquery)){
        $cid = $visit['cid'];
        $name = $visit['name'];
        $visits = $visit['visits'];
        $isCheckedIn = getCustomerCheckedInPayment($cid, $eventID);
        $checkinID = getCheckinIDForCustomerAndEvent($cid, $eventID);
        $usedFreeEntrance = $checkinID ? hasCustomerUsedFreeEntrance($cid, $checkinID) : false;
        $numberOfFreeEntrances = getCustomerNumberOfFreeEntrances($cid);
        array_push($customerArray, 
            array(
            "birthday" => $visit['birthday'],
            "checkinID" => $checkinID,
            "cid" => $cid,
            "email" => $visit['email'],
            "payment" => ($isCheckedIn == null ? "" : $isCheckedIn),
            "name" => $name,
            "visits" => $visits,
            "isCheckedIn" => ($isCheckedIn != null),
            "usedFreeEntrance" => $usedFreeEntrance,
            "numberOfFreeEntrances" => $numberOfFreeEntrances,
            )
        );
    }
    $resultsArray = array();
    $resultsArray['customers'] = $customerArray;
    if($numInSystemNumber > $limit + 1){
        $resultsArray['numberOfExtra'] = $numInSystemNumber - $limit;
    } else {
        $resultsArray['numberOfExtra'] = 0;
    }
    return $resultsArray;
}

/**
 * Unuses a Free Entrance.
 * @param int $cid Customer ID
 * @param int $checkinID Checkin ID
 * @throws Exception if trying to unuse a free entrance when no used free entrance exists
 * @throws Exception if $cid is not a positive integer
 * @throws Exception if $checkinID is not a positive integer
 */
function unuseFreeEntrance($cid, $checkinID){
    if(!isInteger($cid) || $cid < 1){
        throw new Exception("Customer ID must be a positive integer");
    }
    if(!isInteger($checkinID) || $checkinID < 1){
        throw new Exception("Checkin ID must be a positive integer");
    }
    $sql = "SELECT *
           FROM customerattributes
           WHERE customer_id = '$cid'
           AND name = 'Used Free Entrance'
           AND value = '$checkinID'
           AND customerattributes.status = '1'";
    $query = mysql_query($sql) or die (mysql_error());
    $result = mysql_fetch_array($query);
    if($result){
        $id = $result['id'];
        $sql = "UPDATE customerattributes SET customerattributes.status = '0' WHERE id = '$id'";
        $query = mysql_query($sql) or die (mysql_error());
        $databaseNumberOfFreeEntrances = getCustomerNumberOfFreeEntrances($cid);
        editCustomerNumberOfFreeEntrances($cid, $databaseNumberOfFreeEntrances + 1);
        return null;
    }
    throw new Exception("Tried to unuse a free entrance within unuseFreeEntrance method when no used free entrance existed.");
}

/**
 * Uses a Free Entrance
 * @param int $cid Customer ID
 * @param int $checkinID Checkin ID
 * @throws Exception if trying to use a free entrance credit when no credit exists
 * @throws Exception if $cid is not a positive integer
 * @throws Exception if $checkinID is not a positive integer
 */
function useFreeEntrance($cid, $checkinID){
    if(!isInteger($cid) || $cid < 1){
        throw new Exception("Customer ID must be a positive integer");
    }
    if(!isInteger($checkinID) || $checkinID < 1){
        throw new Exception("Checkin ID must be a positive integer");
    }
    $sql = "SELECT * FROM customerattributes WHERE customer_id = '$cid' AND name = 'Free Entrances'";
    $query = mysql_query($sql) or die (mysql_error());
    $result = mysql_fetch_array($query);
    if($result){
        if($result['value'] > 0){
            $newFreeEntrancesAmount = $result['value'] - 1;
            $id = $result['id'];
            $sql = "UPDATE customerattributes SET value = '$newFreeEntrancesAmount' WHERE id = '$id'";
            $query = mysql_query($sql) or die (mysql_error());
            $sql = "INSERT INTO customerattributes VALUES (NULL, '$cid', 'Used Free Entrance', '$checkinID', 1, CURRENT_TIMESTAMP)";
            $query = mysql_query($sql) or die (mysql_error());
            return null;
        }
    }
    throw new Exception("Tried to use a free entrance within useFreeEntrance method when none were available.");
}
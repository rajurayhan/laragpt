<?php
    // /**
    //  * Requires libcurl
    //  */

    // const teamId = "1272651";
    // const userId = "49059276";
    // const folderId = "134229668";
    // const listId = "182248192";

    // $query = array(
    // "include_shared" => "false"
    // );

    // $curl = curl_init();

    // curl_setopt_array($curl, [
    // CURLOPT_HTTPHEADER => [
    //     "Authorization: pk_38254709_31XI582SYM6HN73D7ZZNAF17B51KI1Y2"
    // ],
    // CURLOPT_PORT => "",
    // CURLOPT_URL => "https://api.clickup.com/api/v2/team/" . teamId . "/user/" . userId . "?" . http_build_query($query),
    // CURLOPT_RETURNTRANSFER => true,
    // CURLOPT_CUSTOMREQUEST => "GET",
    // ]);

    // $response = curl_exec($curl);
    // $error = curl_error($curl);

    // curl_close($curl);

    // if ($error) {
    // echo "cURL Error #:" . $error;
    // } else {
    //     echo $response;
    // }

    /**
     * Requires libcurl
     */

    const listId = "182248192";
    $query = array(
    "custom_task_ids" => "true",
    "team_id" => "123"
    );

    $curl = curl_init();

    $payload = array(
    "name" => "New Task Name From Hive Yelp Automation",
    "description" => "New Task Description From Hive Yelp Automation",
    "markdown_description" => "New Task Description From Hive Yelp Automation",
    "assignees" => array(
        82155993
    ),
    "archived" => false,
    "tags" => array(
        "tag name 1"
    ),
    "status" => "recently added",
    "priority" => 3,
    "due_date" => 1508369194377,
    "due_date_time" => false,
    "time_estimate" => 8640000,
    "start_date" => 1567780450202,
    "start_date_time" => false,
    "points" => 3,
    "notify_all" => true,
    "parent" => NULL,
    "links_to" => NULL,
    "check_required_custom_fields" => true,
    "custom_fields" => array()
    );

    curl_setopt_array($curl, [
    CURLOPT_HTTPHEADER => [
        "Authorization: pk_38254709_31XI582SYM6HN73D7ZZNAF17B51KI1Y2",
        "Content-Type: application/json"
    ],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_PORT => "",
    CURLOPT_URL => "https://api.clickup.com/api/v2/list/" . listId . "/task?" . http_build_query($query),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "POST",
    ]);

    $response = curl_exec($curl);
    $error = curl_error($curl);

    curl_close($curl);

    if ($error) {
    echo "cURL Error #:" . $error;
    } else {
    echo $response;
    }
?>

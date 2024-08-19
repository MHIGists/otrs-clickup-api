<?php
require_once 'ClickupAPI.php';
require_once 'logger.php';
require_once 'constants.php';
if (!empty($_POST)) {
    //If there is a client error log it and exit the script
    if (array_key_exists('clientError', $_POST) && $_POST['clientError'] === true) {
        logClientJSError($_POST);
        clearVariables();
        exit();
    }

    $clickup = new clickupAPI(CLICKUP_API_KEY);

// Get the task name and info from the POST data do a basic data clean
    $taskName = htmlspecialchars($_POST['taskName'], ENT_QUOTES, 'UTF-8');
    $info = htmlspecialchars($_POST['info'], ENT_QUOTES, 'UTF-8');
    $otrs_ticket_id = htmlspecialchars($_POST['otrs_ticket_id'], ENT_QUOTES, 'UTF-8');

    $response = $clickup->create_task(OTRS_LIST, $taskName, $info, $otrs_ticket_id);
// Send a response back to the client if there is an error with clickup log it
    if (!$response) {
        $response = ['success' => 'exists'];
    }else{
        if (array_key_exists('id', $response)) {
            $response = ['success' => true];
        } else {
            $response = ['success' => false];
            logClickupTicketError($response);
        }
    }


//Send response to client
    header('Content-Type: application/json');
    echo json_encode($response);
}
clearVariables();

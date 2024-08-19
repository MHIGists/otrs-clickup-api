<?php
require_once 'ClickupAPI.php';
require_once 'logger.php';
require_once 'constants.php';
require_once 'OTRSapi.php';

if (!empty($_POST) && array_key_exists('ticketIDS', $_POST)) {
    $clickup = new ClickUpAPI(CLICKUP_API_KEY);
    $otrs = new OTRSapi('10.1.13.40', 'test', OTRS_USERNAME, OTRS_PASSWORD);
    $session = $otrs->createSession();

    $savedTicketsJson = file_get_contents('ticket_ids.json');
    $savedTickets = json_decode($savedTicketsJson, true) ?? [];

    foreach ($_POST['ticketIDS'] as $key => $ticketName) {
        $continue = false;
        foreach ($savedTickets as $entryID => $savedTicket) {
            if (str_contains($savedTicket['name'], $ticketName)) {
                $ticketID = $savedTicket['id'];
                if ($savedTicket['resolved']) {
                    $continue = true;
                    break;
                }
            }
        }
        if ($continue) {
            continue;
        }
        $comment = $clickup->get_comment($ticketName);
        if ($comment !== false) {
            try {
                //Owner ID will be the id of FLS
                $ticketUpdates = [
                    'OwnerID' => 2
                ];
                $articleUpdate = [
                    'CommunicationChannel' => 'email',
                    'ArticleTypeID' => 1,
                    'SenderTypeID' => 2,
                    'Subject' => 'Ticket resolved',
                    'Body' => $comment,
                    'ContentType' => 'text/plain; charset=utf8',
                    'Charset' => 'utf8',
                    'MimeType' => 'text/plain',
                    'From' => 'cms@ctgaming.com'
                ];
                $otrs->updateTicket($session, $ticketID, $ticketUpdates, $articleUpdate);
                foreach ($savedTickets as &$ticket) {
                    if ($ticket['id'] === $ticketID) {
                        $ticket['resolved'] = true;
                        break;
                    }
                }
                file_put_contents('ticket_ids.json', json_encode($savedTickets));
            } catch (Exception $e) {
                logOTRSerror($e->getMessage());
            }
        }
    }
}
clearVariables();

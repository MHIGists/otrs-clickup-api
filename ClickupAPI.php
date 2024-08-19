<?php
declare(strict_types=1);

class ClickUpAPI
{
    private string $api_key;
    private string $base_url = "https://api.clickup.com/api/v2/";

    public function __construct($api_key)
    {
        $this->api_key = $api_key;
        require_once 'constants.php';
        require_once 'logger.php';
    }

    public function __destruct()
    {
        clearVariables();
    }

    private function send_request($method, $endpoint, $data = array()): array
    {
        $curl = curl_init();

        $headers = array(
            "Content-Type: application/json",
            "Authorization: " . $this->api_key
        );

        curl_setopt($curl, CURLOPT_URL, $this->base_url . $endpoint);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        if ($method == "POST") {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        } else if ($method == "PUT") {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        } else if ($method == "DELETE") {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
        }

        $result = curl_exec($curl);
        curl_close($curl);

        return json_decode($result, true);
    }

    public function create_task($list_id, $name, $description, $otrs_ticket_id): array|bool
    {
        $ticketsJson = file_get_contents('ticket_ids.json');
        $tickets = json_decode($ticketsJson, true) ?? [];
            foreach ($tickets as $ticket) {
                if ($ticket['id'] === $otrs_ticket_id) {
                    return false;
                }
            }
            $data = array(
                "name" => $name,
                "description" => $description
            );
            $result = $this->send_request("POST", "list/" . $list_id . "/task", $data);
            if (isset($result['id'])) {
                $tickets[] = array(
                    'name' => $name,
                    'id' => $otrs_ticket_id
                );
                file_put_contents('ticket_ids.json', json_encode($tickets));
            }
            return $result;
    }

    function fetchFromClickUpAPI($url, $apiToken)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            "Authorization: $apiToken"
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    public function get_comment(string $taskNameToFind): string|bool
    {
        $url = 'https://api.clickup.com/api/v2/list/' . OTRS_LIST . '/task';

        $response = $this->fetchFromClickUpAPI($url, $this->api_key);
        if (isset($response['tasks'])) {
            $tasks = $response['tasks'];
            foreach ($tasks as $task) {
                if ($this->is_task_name_matching($task, $taskNameToFind)) {
                    $comment = $this->get_complete_comment_from_task($task);
                    if ($comment !== false) {
                            return $comment;
                    }
                }
            }
        }
        return false;
    }
    public
    function is_task_name_matching($task, $taskNameToFind): bool
    {
        return str_contains($task['name'], $taskNameToFind);
    }

    public function get_complete_comment_from_task($task): array|bool|string
    {
        $comments = $this->fetchFromClickUpAPI("https://api.clickup.com/api/v2/task/{$task['id']}/comment", $this->api_key)['comments'];
        foreach ($comments as $comment) {
            if (str_contains($comment['comment_text'], '@COMPLETE@')) {
                return str_replace('@COMPLETE@', '', $comment['comment_text']);
            }
        }
        return false;
    }

}
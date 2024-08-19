<?php
declare(strict_types=1);
class OTRSapi
{
    private string $fqdn;
    private string $baseUrl;
    private string $userLogin;
    private string $password;

    public function __construct($fqdn, $webserviceName, $userLogin, $password)
    {
        $this->fqdn = $fqdn;
        $this->baseUrl = "http://$fqdn/otrs/nph-genericinterface.pl/Webservice/$webserviceName";
        $this->userLogin = $userLogin;
        $this->password = $password;
    }
    public function __destruct()
    {
        clearVariables();
    }

    private function sendRequest($method, $endpoint, $data = [])
    {
        $url = $this->baseUrl . $endpoint;
        file_put_contents('uri', $url . PHP_EOL, FILE_APPEND);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    /**
     * @throws Exception
     */
    public function createSession()
    {
        $params = [
            "UserLogin" => $this->userLogin,
            "Password"  => $this->password,
        ];

        $response = $this->sendRequest("POST", "/CreateSession", $params);


        if (!isset($response['SessionID'])) {
            throw new Exception("No SessionID returned");
        }

        return $response['SessionID'];
    }


    /**
     * @throws Exception
     */
    public function updateTicket($sessionID, $ticketID, $ticketUpdates, $articleUpdates)
    {
        $params = array_merge(
            ['SessionID' => $sessionID],
            ['Ticket' => $ticketUpdates],
            ['Article' => $articleUpdates]
        );

        $response = $this->sendRequest("POST", "/TicketUpdate/" . $ticketID, $params);

        if (isset($response['Error'])) {
            $errorCode = $response['Error']['ErrorCode'];
            $errorMessage = $response['Error']['ErrorMessage'];
            throw new Exception("Error $errorCode: $errorMessage");
        }

        return $response;
    }

}

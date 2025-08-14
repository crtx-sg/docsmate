<?php
namespace TP\Tools;

#
# @Class TestLink
#
# This class is used for performing following operation on Openfire
# Add user, delete user, add user to a group, login as admin, 
# send notification message to a user from admin.

class Openfire
{
    private $BASE_PATH;
    private $HOST_NAME;
    private $BASE_URL;
    private $ADMIN;
    private $SECRET_KEY;

    public function __construct()
    {
        $this->HOST_NAME = getenv('OF_HOST');
        $this->BASE_PATH = '/rest/api/restapi/v1/';
        $this->BASE_URL = 'http://' . $this->HOST_NAME . ':7070' . $this->BASE_PATH;
        $this->ADMIN = getenv('OF_ADMIN_USERNAME');
        $this->SECRET_KEY = getenv('OF_SECRET_KEY');
    }

    private function postReq($url, $payload = "", $requestType = "POST")
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);

        if($requestType == "POST"){
            curl_setopt($ch, CURLOPT_POST, true);
        }else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $requestType);
        }

        $headers = array(
            'Content-Type: application/json',
            'Accept: text/plain',
            'Authorization: ' . ($this->SECRET_KEY),
        );

        if ($payload != "") {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            array_push($headers, 'Content-Length: ' . strlen($payload));
        } 
        
        // Set HTTP Header for POST request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Submit the POST request
        $result = curl_exec($ch);
        // Close cURL session handle
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $result;
    }

    public function createUser($userDetails)
    {
        $url = $this->BASE_URL . 'users';
        $payload = json_encode($userDetails);
        $result = $this->postReq($url, $payload);
    }

    public function updateUser($userDetails){
        $url = $this->BASE_URL . 'users/'. $userDetails['username'];
        $payload = json_encode($userDetails);
        $result = $this->postReq($url, $payload, 'PUT');
    }
    public function addUserToGroup($userName, $groupName)
    {
        $url = $this->BASE_URL . 'users/' . $userName . '/groups/' . $groupName;
        $result = $this->postReq($url);
    }

    public function deleteUser($userName)
    {
        $url = $this->BASE_URL . 'users/' . $userName;
        $result = $this->postReq($url, '', 'DELETE');
    }

    public function login()
    {
        $url = $this->BASE_URL . 'chat/' . $this->ADMIN . '/login';
        $result = $this->postReq($url);
        return $result;
    }

    public function sendNotification($to, $message)
    {
        $stream_id = $this->login();
        $url = $this->BASE_URL . 'chat/' . $stream_id . '/messages/' . $to . '@' . $this->HOST_NAME;

        $payload = array(
            "body" => $message,
        );

        $payload = json_encode($payload);
        $result = $this->postReq($url, $payload);
    }

    public function broadcastMessage($message)
    {
        $url = $this->BASE_URL . 'messages/users';
        $payload = array(
            "body" => $message,
        );

        $payload = json_encode($payload);
        $result = $this->postReq($url, $payload);
    }


}

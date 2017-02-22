<?php

/**
 * Created by PhpStorm.
 * User: ian
 * Date: 11/23/16
 * Time: 10:09 AM
 */

const CR = "\r";
const NL = "\n";

require_once "classes/websockets/websockets.php";
require_once "classes/Room.php";
require_once "classes/logging/Logger.php";
require_once "components/StandardHeader.php";

class RoomSocketServer extends WebSocketServer
{
    protected $maxBufferSize = 4096;
    /**
     * @var Room[]
     */

    /**
     * @param $user_socket
     * @param $message
     * @param Room $room
     * @param Account $account
     */
    protected function on_client_join($user_socket, $message, Room &$room, Account &$account)
    {

        $room_id     = $room->getRoomID();
        $new_user_id = $account->getAccountID();
        $nick        = $account->getScreenName();

        //make sure clients for a room are an array
        if (!is_array($this->_clients[$room_id])) {
            $this->_clients[$room_id] = [];
        }

        //generate message
        $response = $this->create_response(
            "Participant Joined",
            [
                "id" => $new_user_id,
                "nick" => $nick,
                "notify" => $nick . " has joined"
            ]
        );

        //send message to all registered participant
        foreach ($this->_clients[$room_id] as $k=>$participant) {
            $this->send($participant, $response);
        }

        //add the new user to the array
        $this->_clients[$room_id][$new_user_id] = $user_socket;

        //generate message for newly registered client
        $response = $this->create_response(
            "Register",
            [
                "success" => true
            ]
        );
        $this->send($user_socket, $response);
    }

    protected function on_client_chat($user_socket, $message, Room &$room, Account &$account)
    {
        $account_id = $account->getAccountID();
        $room_id    = $room->getRoomID();
        $text       = htmlspecialchars($message['text']);

        if (isset($message['fileid'])) {
            $room->getChat()->AddFile($message['fileid'], $message['filepath'], $message['text']);
            $file_id = $message['fileid'];
        } else {
            $file_id = null;
        }

        if(strlen($text) <= 2000){

            $this->Log(SLN_MESSAGE_SENT, "", $account_id, $room_id);

            $room->addMessage(
                Database::getFlakeID(),
                $room_id,
                $account_id,
                $text,
                $file_id);

            $response = $this->create_response(
                "Message",
                [
                    "sender" => $account_id,
                    "text" => $text,
                    "fileid" => $file_id
                ]
            );

            //generate message
            foreach ($this->_clients[$room_id] as $client_account_id => $client_socket) {
                //send message to all registered participant
                $this->send($client_socket, $response);
            }

            $response = $this->create_response(
                "Confirmation",
                [
                    "action" => $message['action'],
                    "success" => true
                ]
            );

        }else{
            $response = $this->create_response(
                "Confirmation",
                [
                    'action'=>$message['action'],
                    'success'=>false,
                    "message"=>"Message over 2000 characters"
                ]
            );
        }
        $this->send($user_socket, $response);
    }


    protected function connected($user)
    {

    }

    protected function closed($user)
    {

    }

    private function create_response($type, array $optionals){
        echo "response type: ", $type, "<br>";
        $response = $optionals;
        $response["type"] = $type;
        return json_encode($response);
    }

    private function generate_error_response($error_type){
        $response = ["Type"=>"Error", "ErrorCode"=>$error_type];
        switch ($error_type){
            case SLN_NOT_AUTHORIZED:
            {
                $response["ErrorMessage"] = "This action requires authorization to complete";
            }
            break;

            case SLN_NOT_AUTHENTICATED:
            {
                $response["ErrorMessage"] = "The token provided does not exist in the database";
            }
            break;

            case ERR_ACCESS_DENIED:
            {
                $response["ErrorMessage"] = "You are not authorized to perform this action";
            }
            break;

        }
        return $response;
    }


    protected function on_client_alter_name($user_socket, $message, Room &$room, Account &$account)
    {
        echo('<script>console.log("Alter Name")</script>');
        $room_id           = $room->getRoomID();
        $account_id        = $account->getAccountID();
        $current_nick_name = $account->getScreenName();

        $nick_name         = $message["user"];

        if(strlen($nick_name) <= 20) {
            //do not report until database has been updated
            //$this->Log(SLN_CHANGED_NAME, "", $account_id, $room_id);

            $account->_screenName = $nick_name;
            //There are probably better ways to take care of this...
            foreach ($room->getAccounts() as $k=>$participant) {
                if($message["token"] == $participant->getToken()){
                    $participant->_screenName = $nick_name;
                    echo($participant->getScreenName());
                    break;
                }
            }

            //generate message
            $response = $this->create_response(
                "Participant Changed Their Name",
                [
                    "id" => $account_id,
                    "nick" => $nick_name,
                    "notify" => $current_nick_name . " has changed their name to " . $nick_name
                ]
            );

            //send message to all registered participant
            foreach ($this->_clients[$room_id] as $k=>$participant) {
                $this->send($participant, $response);
            }

            //generate message for name change
            $response = $this->create_response(
                "Name Changed",
                [
                    "success" => true
                ]
            );

        }else{
            $response = $this->create_response(
                "Confirmation",
                [
                    'action'=>$message['action'],
                    'success'=>false,
                    "message"=>"Name over 20 characters"
                ]
            );
        }
        $this->send($user_socket, $response);
    }

    protected function on_download_file($user_socket, $message, Room &$room, Account &$account)
    {
        $account_id = $account->getAccountID();
        $room_id    = $room->getRoomID();

//        var_dump($message);

        var_dump("accounts: ", $room->getAccounts());

        if ($file = $room->validateDownload($message['fileid'], $message['token'])){

            $response = $this->create_response(
                "Download",
                [
                    "sender" => $account_id,
                    "fileid" => $file->fileID,
                    "filename" => $file->fileName,
                    "filepath" => $file->filePath
                ]
            );
        } else {
            $response = $this->create_response(
                "Confirmation",
                [
                    'action'=>$message['action'],
                    'success'=>false,
                    "message"=>"Permission denied"
                ]
            );
        }

        $this->send($user_socket, $response);
    }

    protected function on_alter_roomcode($user_socket, $message, Room &$room, Account &$account)
    {
        //echo("<script>console.log('Alter Room Code')</script>");
        $newUses = $message["remaining"];

        //echo "newUses: ", $newUses;
        $roomid = $room->getRoomID();
        $current_nick_name = $account->getScreenName();

        if($newUses < 100) {
            echo "uses: ", $newUses;
            $room->setUsesLeft($newUses);

            $response = $this->create_response(
                "Room Code Changed",
                [
                    "uses" => $newUses,
                    "notify" => $current_nick_name . " has modified a room code"
                ]
            );

            var_dump($response);
            foreach ($this->_clients[$roomid] as $k => $participant) {
                $this->send($participant, $response);
            }
            //$this->_clients[$roomid][$newUses] = $user_socket;  //add the new user to the array
            //generate message
            $response = $this->create_response(
                "Confirmation",
                [
                    "success" => true
                ]
            );
        }
        else{
            $response = $this->create_response(
                "Confirmation",
                [
                    'action'=>$message['action'],
                    'success'=>false,
                    "message"=>"Room Code Error"
                ]
            );
        }
        $this->send($user_socket, $response);
    }

    protected function on_client_alter_voice($user_socket, $message, Room &$room, Account &$account)
    {
        // TODO: Implement on_client_alter_voice() method.
    }

    protected function on_client_alter_video($user_socket, $message, Room &$room, Account &$account)
    {
        // TODO: Implement on_client_alter_video() method.
    }
}


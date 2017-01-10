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
    
    protected function on_client_join($user_socket, $message, Room &$room, Account &$account)
    {
        //User has just connected to the room, and requests to be notified of all changes to the room state
        $roomid = $room->getRoomID();
        if (!is_array($this->_clients[$roomid])) {    //make sure clients for a room are an array
            $this->_rooms[$roomid] = [];
        }
        $newUserID = $account->getAccountID(); //get the id of the new participant
        $nick = $account->getScreenName();

        $response = $this->create_response("Participant Joined", ["id" => $newUserID, "nick" => $nick, "notify" => $nick . " has joined"]);     //generate message
        foreach ($this->_clients[$roomid] as $k=>$participant) {
            $this->send($participant, $response);               //send message to all registered participant
        }
        $this->_clients[$roomid][$newUserID] = $user_socket;        //add the new user to the array
        //generate message
        $response = $this->create_response("Register", ["success" => true]);
        $this->send($user_socket, $response);
    }

    protected function on_client_chat($user_socket, $message, Room &$room, Account &$account)
    {
        if(strlen($message['text']) <= 2000){

            $accountID = $account->getAccountID();
            $roomid = $room->getRoomID();
            $this->Log(SLN_MESSAGE_SENT, "", $accountID, $roomid);
            $text = htmlspecialchars($message['text']);
            $accountID = $account->getAccountID();
            $room->addMessage(Database::getFlakeID(), $room->getRoomID(), $accountID, $text);
            $response = $this->create_response("Message", ["sender" => $accountID, "text" => $text]);     //generate message
            foreach ($this->_clients[$roomid] as $k => $participant) {
                $this->send($participant, $response);               //send message to all registered participant
            }
            $response = $this->create_response("Confirmation", ["action" => $message['action'], "success" => true]);
        }else{
            $response = $this->create_response("Confirmation", ['action'=>$message['action'], 'success'=>false, "message"=>"Message over 2000 characters"]);
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


    protected function on_client_namechange($user_socket, $message, Room &$room, Account &$account)
    {
        // TODO: Implement on_client_namechange() method.
    }

    protected function on_alter_roomcode($user_socket, $message, Room &$room, Account &$account)
    {
        // TODO: Implement on_alter_roomcode() method.
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


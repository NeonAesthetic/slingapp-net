<?php

/**
 * Created by PhpStorm.
 * User: ian
 * Date: 11/23/16
 * Time: 10:09 AM
 */

const ERR_REQUIRES_AUTH = 0;
const ERR_INVALID_TOKEN = 1;
const ERR_ACCESS_DENIED = 2;

require_once "./websockets.php";
require_once "../Room.php";
class RoomSocketServer extends WebSocketServer
{
    protected $maxBufferSize = 4096;
    /**
     * @var Room[]
     */
    private $_rooms = [];

    private $_clients = [];
    protected function process($user, $message)
    {
        /*************************************************************************************
         *  SETUP ALL VARIABLES AND CACHED OBJECTS
         *************************************************************************************/
        $resource = $user->requestedResource;
        preg_match("#/rooms/([0-9]+)#", $resource, $matches);
        $roomid = $matches[1];
        $request = json_decode($message, true);
        $room = null;
        $account = Account::Login($request['token']);
        //$account->getParticipantInfo();

        echo "Request for RoomID: [$roomid]\nClient has requested action " . $request['action'] . "\n";
        if(!array_key_exists($roomid, $this->_rooms)){
            try{
                $room = new Room($roomid);
            }catch (Exception $e){
                echo $e . "";
                $room = false;
            }
            if($room){
                echo "Added Room to cache\n";
                $this->_rooms[$roomid] = $room;
                $this->_clients[$roomid] = [];
                $room = &$this->_rooms[$roomid];
            }else{

            }
        }else{
            echo "Retrieved Room from cache\n";
            $room = &$this->_rooms[$roomid];
        }
        $response = null;
        /** Make sure that the account has permissions to access the room */
        if(!$room->accountInRoom($account))
        {
            $response = $this->generate_error_response(ERR_ACCESS_DENIED);
            $this->send($user, $response);
            return;
        }

        /*********************************************************************************************************
         *              RESPOND TO MESSAGE ACTIONS
         *********************************************************************************************************/

        switch ($request["action"]){
            case "Register":
            {
                echo "In Register\n";
                //User has just connected to the room, and requests to be notified of all changes to the room state

                if(!is_array($this->_clients[$roomid])){    //make sure clients for a room are an array
                    $this->_rooms[$roomid] = [];
                }
                $newUserID = $account->getAccountID(); //get the id of the new participant
                $nick = $account->getScreenName();

                echo $nick;
                $response = $this->create_response("Participant Joined", ["id"=>$newUserID, "nick" => $nick, "notify"=>$nick . " has joined"]);     //generate message
                foreach ($this->_clients[$roomid] as $participant){
                    $this->send($participant, $response);               //send message to all registered participant
                }
                $this->_clients[$roomid][$request["token"]] = $user;        //add the new user to the array
                //generate message
                $response = $this->create_response("Register", ["success"=>true]);

            }
            break;

            case "Send Message":
            {
                $text = htmlspecialchars($request['text']);
                echo "MESSAGE: ".$text."\n";
                $accountID = $account->getAccountID();
                $room->addMessage(Database::getFlakeID(), $room->getRoomID(), $accountID, $text);
                $response = $this->create_response("Message", ["Sender"=>$accountID, "text"=>$text]);     //generate message
                foreach ($this->_clients[$roomid] as $participant){
                    $this->send($participant, $response);               //send message to all registered participant
                }
            }
            break;

            default:
                $response = $this->create_response("Error", ["message"=>"Invalid action"]);
        }

        $this->send($user, json_encode($response));
    }

    protected function connected($user)
    {

    }

    protected function closed($user)
    {

    }

    private function create_response($type, array $optionals){
        $response = $optionals;
        $response["Type"] = $type;
        return json_encode($response);
    }

    private function generate_error_response($error_type){
        $response = ["Type"=>"Error", "ErrorCode"=>$error_type];
        switch ($error_type){
            case ERR_REQUIRES_AUTH:
            {
                $response["ErrorMessage"] = "This action requires authorization to complete";
            }
            break;

            case ERR_INVALID_TOKEN:
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
        return json_encode($response);
    }


}


<?php

/**
 * Created by PhpStorm.
 * User: ian
 * Date: 11/23/16
 * Time: 10:09 AM
 */

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
        $resource = $user->requestedResource;
        preg_match("#/rooms/([0-9]+)#", $resource, $matches);
        $roomid = (int)$matches[1];
        $request = json_decode($message, true);
        $room = null;

        echo "Request for RoomID: [$roomid]\nClient has requested action " . $request['action'] . "\n";
        if(!array_key_exists($roomid, $this->_rooms)){
            try{
                $room = new Room($roomid);
            }catch (Exception $e){
                echo $e . "";
                $room = false;
            }
            if($room){
                echo "Added Room to cache";
                $this->_rooms[$roomid] = $room;
                $this->_clients[$roomid] = [];
                $room = &$this->_rooms[$roomid];
            }else{

            }
        }
        $response = null;
        switch ($request["action"]){
            case "Register":
            {
                //User has just connected to the room, and requests to be notified of all changes to the room state

                if(!is_array($this->_clients[$roomid])){    //make sure clients for a room are an array
                    $this->_rooms[$roomid] = [];
                }
                $newUserID = Account::Login($request["token"])->getAccountID(); //get the id of the new participant
                $response = $this->create_response("Participant Joined", ["user"=>$newUserID]);     //generate message
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

            }
            break;
        }

        $this->send($user, json_encode($response));
    }

    protected function connected($user)
    {

    }

    protected function closed($user)
    {
        echo "Client has disconnected\n";
    }

    private function create_response($type, array $optionals){
        $response = $optionals;
        $response["Type"] = $type;
        return json_encode($response);
    }
}
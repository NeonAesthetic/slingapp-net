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
const CR = "\r";
const NL = "\n";

require_once "classes/websockets/websockets.php";
require_once "classes/Room.php";

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
        $roomid = $matches[1];
        $accountID = null;
        try {


            /*************************************************************************************
             *  SETUP ALL VARIABLES AND CACHED OBJECTS
             *************************************************************************************/

            $request = json_decode($message, true);
            $room = null;
            $account = Account::Login($request['token']);
            $accountID = $account->getAccountID();

            $this->Log($request['action'], "Client has access websocket endpoint", $account->getAccountID(), $roomid, "UNDEFINED");

            if (!array_key_exists($roomid, $this->_rooms)) {
                try {
                    $room = new Room($roomid);
                } catch (Exception $e) {
                    echo $e . "";
                    $room = false;
                }
                if ($room) {
                    $this->Log("Cache Miss", "Add room to cache", $account->getAccountID(), $roomid, "UNDEFINED");
                    $this->_rooms[$roomid] = $room;
                    $this->_clients[$roomid] = [];
                    $room = &$this->_rooms[$roomid];
                } else {

                }
            } else {
                $this->Log("Cache Hit", "Room Found in cache", $account->getAccountID(), $roomid, "UNDEFINED");
                $room = &$this->_rooms[$roomid];
            }
            $response = null;
            /** Make sure that the account has permissions to access the room */
            if (!$room->accountInRoom($account)) {
                $this->Log("UNAUTHORIZED ACCESS", "Client has requested access to Room but is not authorized", $account->getAccountID(), $roomid, "UNDEFINED");
                $response = $this->generate_error_response(ERR_ACCESS_DENIED);
                $this->send($user, $response);
                return;
            }

            /*********************************************************************************************************
             *              RESPOND TO MESSAGE ACTIONS
             *********************************************************************************************************/

            switch ($request["action"]) {
                case "Register": {

                    //User has just connected to the room, and requests to be notified of all changes to the room state

                    if (!is_array($this->_clients[$roomid])) {    //make sure clients for a room are an array
                        $this->_rooms[$roomid] = [];
                    }
                    $newUserID = $account->getAccountID(); //get the id of the new participant
                    $nick = $account->getScreenName();

                    $response = $this->create_response("Participant Joined", ["id" => $newUserID, "nick" => $nick, "notify" => $nick . " has joined"]);     //generate message
                    foreach ($this->_clients[$roomid] as $participant) {
                        $this->send($participant, json_encode($response));               //send message to all registered participant
                    }
                    $this->_clients[$roomid][$newUserID] = $user;        //add the new user to the array
                    //generate message
                    $response = $this->create_response("Register", ["success" => true]);

                }
                    break;

                case "Send Message": {
                    $text = htmlspecialchars($request['text']);
                    $accountID = $account->getAccountID();
                    $room->addMessage(Database::getFlakeID(), $room->getRoomID(), $accountID, $text);
                    $response = $this->create_response("Message", ["Sender" => $accountID, "text" => $text]);     //generate message
                    foreach ($this->_clients[$roomid] as $k => $participant) {
                        echo $k . "\n";
                        $this->send($participant, json_encode($response));               //send message to all registered participant
                    }
                    $response = $this->create_response("Confirmation", ["action" => $request['action'], "success" => true]);
                }
                    break;

                default:
                    $response = $this->create_response("Error", ["message" => "Invalid action"]);
            }

            $this->send($user, json_encode($response));
        }catch (Throwable $e){
            $this->Log("Fatal Error", $e->getMessage(), $accountID, $roomid, "UNDEFINED");
        }
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
        return $response;
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
        return $response;
    }

    public function Log($action, $msg, $userid, $roomid, $ip){
        echo "[" . date(DATE_ATOM) . "] " . $action . ": " . $msg . "\n";
        DatabaseObject::Log(__FILE__, $action, $msg, $userid, $roomid, $ip);
    }

    public function run() {

        $buffer = "";
        while(true) {
            if (empty($this->sockets)) {
                $this->sockets["m"] = $this->master;
            }
            $read = $this->sockets;
            $write = $except = null;
            $this->_tick();
            $this->tick();
            @socket_select($read,$write,$except,1); # suppress warnings with @
            foreach ($read as $socket) {
                if ($socket == $this->master) {
                    $client = socket_accept($socket);
                    if ($client === false) {
//                        msg("SKT: Error at 'socket_accept()'. Reason: ".socket_strerror(socket_last_error()),ERR,CONT);
                        continue;
                    } else {
                        $this->connect($client);
                    }
                } else {
                    $numBytes = socket_recv($socket,$socketData,$this->maxBufferSize,0);
                    if ($numBytes === false) {
                        $sockErrNo = socket_last_error($socket);
                        switch ($sockErrNo) {
                            case 102: # ENETRESET    - Network dropped connection because of reset
                            case 103: # ECONNABORTED - Software caused connection abort
                            case 104: # ECONNRESET   - Connection reset by peer
                            case 108: # ESHUTDOWN    - Can't send after transport endpoint shutdown - probably more of an error on our part, if we're trying to write after the socket is closed. Probably not a critical error, though
                            case 110: # ETIMEDOUT    - Connection timed out
                            case 111: # ECONNREFUSED - Connection refused - We shouldn't see this one, since we're listening... Still not a critical error
                            case 112: # EHOSTDOWN    - Host is down - Again, we shouldn't see this, and again, not critical because it's just one connection and we still want to listen to/for others
                            case 113: # EHOSTUNREACH - No route to host
                            case 121: # EREMOTEIO    - Rempte I/O error - Their hard drive just blew up
                            case 125: # ECANCELED    - Operation canceled
//                                msg("SKT: Unusual disconnect on socket: ".$socket,WRN,CONT);
                                $this->disconnect($socket,true,$sockErrNo); # disconnect before clearing error, in case someone with their own implementation wants to check for error conditions on the socket
                                break;
                            default:
//                                msg("SKT: Error: ".socket_strerror($sockErrNo),WRN,CONT);
                        }
                    } elseif ($numBytes == 0) {
//                        msg("SKT: Received 0 bytes but expected more",WRN,CONT);
                        $this->disconnect($socket);
//                        msg("SKT: Client disconnected. TCP connection lost: ".$socket,WRN,CONT);
                    } else {
                        $buffer .= $socketData;
                        $user = $this->getUserBySocket($socket);
                        if (!$user->handshake) {
                            $tmp = str_replace(CR,"",$buffer);
                            if (strpos($tmp,NL.NL) === false ) { continue; } # when client has not finished sending header, wait before sending upgrade response
                            $this->doHandshake($user,$buffer);
                        } else {
                            # split packet into frame and send it to deframe
                            $this->split_packet($numBytes,$buffer,$user);
                        }
                        $buffer = "";
                    }
                }
            }
        }
    }


}


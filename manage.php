<?php

require_once("./settings.php");

$command = $argv[1];

switch($command){
	case "reload":
	{
		print("Reloading apache server".NL);
		exec("service apache2 reload", $output, $result);
		if ($result){
			print("\033[34m[ FAIL ]\033[0m".NL);
			print($output[0] . NL);
		}else{
			print("\033[32m[ OK ]\033[0m".NL);
		}
	}break;

	case "restart":
	{
		print("Restarting apache server".NL);
		exec("service apache2 restart", $output, $result);
		if ($result){
			print("\033[34m[ FAIL ]\033[0m".NL);
			print($output[0] . NL);
		}else{
			print("\033[32m[ OK ]\033[0m".NL);
		}
	}break;

	case "websocket":
	{
		$subcommand  = $argv[2];
		$screen_name = "socket_" . ENVIRONMENT_NAME;
		switch($subcommand){
			case "start":
			{	
				if(!screen_session_exists($screen_name)){
					print("Starting Room Websocket" . NL);
					start_websocket($screen_name);	
					print("\033[32m[ OK ]\033[0m".NL);
				}else{
					print("Websocket session is already running.  Try stopping it with 'websocket stop' first." . NL);
				}
			}break;

			case "restart":
			{
				if(screen_session_exists($screen_name)){
					stop_websocket($screen_name);
				}
				print("Starting websocket" . NL);
				start_websocket($screen_name);
				print("\033[32m[ OK ]\033[0m".NL);
			}break;

			case "stop":
			{
				if(screen_session_exists($screen_name)){
					print("Stopping websocket" . NL);
					stop_websocket($screen_name);
				}
			}
		}
	}break;

	case "help":
	{
		print(	"COMMANDS: ".NL.
				"reload    - (Reloads Apache configuration.)".NL.
				"restart   - (Restarts the Apache Web Server.)".NL.
				"websocket [start|stop|restart] - (Starts, stops, or restarts the websocket.)".NL
			);
	}
}


function screen_session_exists($name){
	exec("screen -S $name -X select .", $out, $return);
	return !$return;
}

function start_websocket($screen_name){
	exec("screen -dmS $screen_name php -c " . PHP_INI_PATH . " " . BASE_PATH . "/assets/php/classes/websockets/socket.php");
}

function stop_websocket($screen_name){
	exec("screen -S $screen_name -X quit");
}

function restart_websocket($screen_name){
	if(screen_session_exists($screen_name)){
		stop_websocket($screen_name);
	}
	start_websocket($screen_name);
}

?>
<?php
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/gameutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";

	if(isset($_GET['serverid'])) {
		if(isset($_GET['removeplayer'])) {
			$player_id = intval($_GET['removeplayer']);
			$server_id = $_GET['serverid'];
			if($player_id != 0) {
				GameUtils::RemovePlayer($server_id, $player_id);
			}
		} else if(isset($_GET['addplayer'])) {
			$player_id = intval($_GET['addplayer']);
			$server_id = $_GET['serverid'];
			if($player_id != 0) {
				GameUtils::AddPlayer($server_id, $player_id);
			}
		}
	} else {
		if(isset($_POST['creategameserver'])) {
			$user = UserUtils::GetLoggedInUser();
			$place = intval($_POST['creategameserver']);
			if($user != null && $place > 0) {
				die(strval(GameUtils::CreateGame($place)));
			} else {
				die("null");
			}
		} else if(isset($_GET['runningservers'])) {
			$place = intval($_GET['runningservers']);
			if($place != 0) {
				header("Content-Type: application/json");
				$running_games = GameUtils::GetAllRunningGamesPublicWise($place);
				die(json_encode($running_games));
			}
			
		} else if(isset($_GET['closeserver'])) {
			$jobid = $_GET['closeserver'];
			GameUtils::CloseGame($jobid);
		}
	}
?>
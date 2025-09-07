<?php
	class GameUtils {
		// get data from running games
		// parse and be able to restrict playing games if user is already in a game


		public static function GeneratePort(): int {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			$port = rand(50000, 52500);
			$stmt = $con->prepare('SELECT * FROM `running_games` WHERE `game_port` = ?');
			$stmt->bind_param('i', $port);
			$stmt->execute();
			$stmt->store_result();
			
			$instances = $stmt->num_rows;
			
			if($instances != 0) {
				return self::GeneratePort();
			} else {
				return $port;
			}
			
		}
		
		public static function CreateGame($id) {
			require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
			require_once $_SERVER["DOCUMENT_ROOT"]."/core/assetutils.php";
			$user = UserUtils::GetLoggedInUser();
			if($user != null) {
				/*$running_games = GetAllRunningGamesWithPlayer($playerid);
				if(count($running_games) == 0) {

				}*/
				$asset = Place::FromID($id);
				if($asset != null) {
					require $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
					$stmt_kickplayer = $con->prepare('SELECT * from `running_games` WHERE 1;');
					$stmt_kickplayer->execute();
					$result = $stmt_kickplayer->get_result();

					if($result->num_rows != 0) {
						while($row = $result->fetch_assoc()) {
							self::KickPlayer($row['game_id'], $user->id);
						}
					}
					
					$running_games = self::GetAllRunningGames($id);
					if(count($running_games) == 0) {
						require_once $_SERVER["DOCUMENT_ROOT"]."/core/rcclib.php";
						$jobid = strval(md5(rand()));
						$game_port = self::GeneratePort();
						$domain = $_SERVER['SERVER_NAME'];
	
						$RCCLIB = new RCCServiceSoap("192.168.0.124");
						$script = <<<EOT
							loadfile("http://$domain/game/gameserver.ashx?PWNMODE=true")($id, $game_port, "http://$domain/asset/?id=$id&givemecodeall=3544bd46-a09e-4e9f-9f4a-8cb6821ad356")
							game:GetService("Players").PlayerAdded:connect(function(player)
								math.randomseed(tick())
								game:httpGet("http://$domain/api/gameserverutils?addplayer="   .. player.userId .. "&serverid=$jobid&rand=".. math.random(0, 100000))
							end)
	
							game:GetService("Players").PlayerRemoving:connect(function(player)
								math.randomseed(tick())
								game:httpGet("http://$domain/api/gameserverutils?removeplayer=".. player.userId .. "&serverid=$jobid&rand=".. math.random(0, 100000))
							end)
	
							local counter = 15
	
							while wait(1) do
								if #game:service("Players"):GetPlayers() == 0 then
									counter = counter - 1
								else
									counter = 15
								end
	
								if counter <= 0 then
									math.randomseed(tick())
									game:httpGet("http://$domain/api/gameserverutils?closeserver=$jobid&rand=".. math.random(0, 100000))
									print("calling to stop server")
								end
							end
						EOT;
						$RCCLIB->execScript($script, "$jobid", 9999999999);
						
						require $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
						$stmt = $con->prepare('INSERT INTO `running_games`(`game_id`, `game_placeid`, `game_playersdata`, `game_players`, `game_maxplayers`, `game_port`) VALUES (?,?,\'\', 0,?, ?)');
						$stmt->bind_param('siii', $jobid, $id, $asset->maxplayers, $game_port);
						$stmt->execute();

						
	
						return $game_port;
					} else {
						foreach($running_games as $game) {
							if($game['game_players'] < $asset->maxplayers) {
								return $game['game_port'];
							}
						}

						//if no available server was found, create new one
						require_once $_SERVER["DOCUMENT_ROOT"]."/core/rcclib.php";
						$jobid = strval(md5(rand()));
						$game_port = self::GeneratePort();
						$domain = $_SERVER['SERVER_NAME'];
	
						$RCCLIB = new RCCServiceSoap("192.168.0.124");
						$script = <<<EOT
							loadfile("http://$domain/game/gameserver.ashx?PWNMODE=true")($id, $game_port, "http://$domain/asset/?id=$id&givemecodeall=3544bd46-a09e-4e9f-9f4a-8cb6821ad356")
							game:GetService("Players").PlayerAdded:connect(function(player)
								math.randomseed(tick())
								game:httpGet("http://$domain/api/gameserverutils?addplayer="   .. player.userId .. "&serverid=$jobid&rand=".. math.random(0, 100000))
							end)
	
							game:GetService("Players").PlayerRemoving:connect(function(player)
								math.randomseed(tick())
								game:httpGet("http://$domain/api/gameserverutils?removeplayer=".. player.userId .. "&serverid=$jobid&rand=".. math.random(0, 100000))
							end)
	
							local counter = 15
	
							while wait(1) do
								if #game:service("Players"):GetPlayers() == 0 then
									counter = counter - 1
								else
									counter = 15
								end
	
								if counter <= 0 then
									math.randomseed(tick())
									game:httpGet("http://$domain/api/gameserverutils?closeserver=$jobid&rand=".. math.random(0, 100000))
									print("calling to stop server")
								end
							end
						EOT;
						$RCCLIB->execScript($script, "$jobid", 9999999999);
						
						require $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
						$stmt = $con->prepare('INSERT INTO `running_games`(`game_id`, `game_placeid`, `game_playersdata`, `game_players`, `game_maxplayers`, `game_port`) VALUES (?,?,\'\', 0,?, ?)');
						$stmt->bind_param('siii', $jobid, $id, $asset->maxplayers, $game_port);
						$stmt->execute();

						return $game_port;
					}
				}
				
			}
			return "null";
		}

		public static function KickPlayer($id, $playerid) {
			require_once $_SERVER["DOCUMENT_ROOT"]."/core/rcclib.php";
			$script = <<<EOT
				for _, v in pairs(game.NetworkServer:children()) do
					if v:GetPlayer() then
						if v:GetPlayer().userId == $playerid then
							v:CloseConnection()
							break
						end
					end
				end
			EOT;
			
			$rcc = new RCCServiceSoap("192.168.0.124");
			$rcc->execute($script, $id);
		}

		public static function CloseGame($id) {
			
			require $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			require_once $_SERVER["DOCUMENT_ROOT"]."/core/rcclib.php";
			$stmt = $con->prepare('DELETE FROM `running_games` WHERE `game_id` = ?;');
			$stmt->bind_param('s', $id);
			$stmt->execute();

			$rcc = new RCCServiceSoap("192.168.0.124");
			$rcc->closeJob($id);
		}

		public static function GetAllRunningGamesWithPlayer($playerid) {
			require_once $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			$stmt = $con->prepare('SELECT * from `running_games` WHERE JSON_CONTAINS(`game_playersdata`, \'?\', \'$\');');
			$stmt->bind_param('i', $playerid);
			$stmt->execute();
			$result = $stmt->get_result();

			$games = [];

			if($result->num_rows != 0) {
				while($row = $result->fetch_assoc()) {
					$asset = $row;
					array_push($games, $asset);
				}
			}

			return $games;
		}

		public static function GetAllRunningGamesPublicWise($id): array {
			require $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
			require_once $_SERVER["DOCUMENT_ROOT"]."/core/assetutils.php";
			require_once $_SERVER["DOCUMENT_ROOT"]."/core/friending.php";

			$place = AssetUtils::GetAsset($id);
			$user = UserUtils::GetLoggedInUser();
			$friends_with = FriendUtils::isUserFriendsWith($user->id, $place->creator->id) || $user->id == $place->creator->id;
			$games = [];

			if($place instanceof Place && $place->friends_only && !$friends_with) {
				return [];
			}
			
			$stmt = $con->prepare('SELECT * FROM `running_games` WHERE `game_placeid` = ?;');
			$stmt->bind_param('i', $id);
			$stmt->execute();
			$result = $stmt->get_result();

			if($result->num_rows != 0) {
				while($row = $result->fetch_assoc()) {
					$asset = $row;
					unset($asset['game_id']);
					unset($asset['game_placeid']);
					
					$asset['players'] = $asset['game_playersdata'];
					$asset['player_count'] = $asset['game_players'];
					$asset['max_player_count'] = $asset['game_maxplayers'];
					$port = $asset['game_port'];
					unset($asset['game_port']);
					$asset['game_port'] = $port;

					unset($asset['game_playersdata']);
					unset($asset['game_players']);
					unset($asset['game_maxplayers']);

					array_push($games, $asset);
				}
			}

			return $games;
		}

		public static function GetAllRunningGames($id): array {
			require $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			$stmt = $con->prepare('SELECT * FROM `running_games` WHERE `game_placeid` = ?;');
			$stmt->bind_param('i', $id);
			$stmt->execute();
			$result = $stmt->get_result();

			$games = [];

			if($result->num_rows != 0) {
				while($row = $result->fetch_assoc()) {
					$asset = $row;
					array_push($games, $asset);
				}
			}

			return $games;
		}

		public static function AddPlayer($id, $playerid) {
			require $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			$stmt = $con->prepare('SELECT * FROM `running_games` WHERE `game_id` = ?;');
			$stmt->bind_param('s', $id);
			$stmt->execute();

			$result = $stmt->get_result();
			$row = $result->fetch_assoc();

			if(trim($row['game_playersdata']) != "") {
				// idk man i just know it fucks up here
				$thing = explode(",",$row['game_playersdata']) ?? [$row['game_playersdata']];
				if(!in_array($playerid, $thing)) {
					array_push($thing, $playerid);
				}

				$json = "";

				foreach($thing as $player) {
					$json .= $player.",";
				}

				$json = substr($json, 0, strlen($json)-1);

				if(str_starts_with($json, ",")) {
					$json = substr($json, 1);
				} else if(str_starts_with($json, "[],")) {
					$json = substr($json, 3);
				}
			
				$players = count($thing);
			} else {
				$json = "$playerid";
				$players = 1;
			}

			$stmt = $con->prepare('UPDATE `running_games` SET `game_playersdata` = ?, `game_players` = ? WHERE `game_id` = ?');
			$stmt->bind_param('sis', $json, $players, $id);
			$stmt->execute();

			self::CountVisit($row['game_placeid'], $playerid);
			self::GetVisitCount($row['game_placeid']);
			self::UpdatePlayerCount($row['game_placeid']);
			self::GetPlayerCount($row['game_placeid']);
		}

		public static function CountVisit($placeid, $userid) {
			require $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";

			$stmt_checkvisit = $con->prepare('SELECT * FROM `visit` WHERE `visit_place` = ? AND `visit_player` = ? AND `visit_time` >= CURDATE() - INTERVAL 1 HOUR;');
			$stmt_checkvisit->bind_param('ii', $placeid, $userid);
			$stmt_checkvisit->execute();

			if($stmt_checkvisit->get_result()->num_rows == 0) {
				$stmt_addvisit = $con->prepare('INSERT INTO `visit`(`visit_place`, `visit_player`) VALUES (?, ?)');
				$stmt_addvisit->bind_param('ii', $placeid, $userid);
				$stmt_addvisit->execute();

				// Update

				$stmt_visitcount = $con->prepare('SELECT * FROM `visit` WHERE `visit_place` = ?;');
				$stmt_visitcount->bind_param('i', $placeid);
				$stmt_visitcount->execute();
	
				$visits = $stmt_visitcount->get_result()->num_rows;

				if($visits > 100 && !Asset::FromID($placeid)->creator->HasBadge(User::BADGE_HOMESTEAD)) {
					Asset::FromID($placeid)->creator->GiveBadge(User::BADGE_HOMESTEAD);
				}

				if($visits > 1000 && !Asset::FromID($placeid)->creator->HasBadge(User::BADGE_BRICKSMITH)) {
					Asset::FromID($placeid)->creator->GiveBadge(User::BADGE_BRICKSMITH);
				}
	
				$stmt = $con->prepare('UPDATE `assets` SET `place_visitcount` = ? WHERE `asset_id` = ?;');
				$stmt->bind_param('ii', $visits, $placeid);
				$stmt->execute();
			}
		}

		public static function GetVisitCount($placeid) {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			$stmt_visitcount = $con->prepare('SELECT * FROM `visit` WHERE `visit_place` = ?;');
			$stmt_visitcount->bind_param('i', $placeid);
			$stmt_visitcount->execute();

			$visits = $stmt_visitcount->get_result()->num_rows;

			if($visits > 100 && !Asset::FromID($placeid)->creator->HasBadge(User::BADGE_HOMESTEAD)) {
				Asset::FromID($placeid)->creator->GiveBadge(User::BADGE_HOMESTEAD);
			}

			if($visits > 1000 && !Asset::FromID($placeid)->creator->HasBadge(User::BADGE_BRICKSMITH)) {
				Asset::FromID($placeid)->creator->GiveBadge(User::BADGE_BRICKSMITH);
			}

			$stmt = $con->prepare('UPDATE `assets` SET `place_visitcount` = ? WHERE `asset_id` = ?;');
			$stmt->bind_param('ii', $visits, $placeid);
			$stmt->execute();
			
			return $visits;
		}

		public static function RemovePlayer($id, $playerid) {
			require $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			$stmt = $con->prepare('SELECT * FROM `running_games` WHERE `game_id` = ?;');
			$stmt->bind_param('s', $id);
			$stmt->execute();

			$result = $stmt->get_result();
			$row = $result->fetch_assoc();

			if(trim($row['game_playersdata']) != "") {
				// idk man i just know it fucks up here
				$thing = explode(",",$row['game_playersdata']) ?? [];
				if(in_array($playerid, $thing)) {
					if(($key = array_search($playerid, $thing)) !== NULL) {
						$thing = array_diff($thing, [$playerid]);
					}
				}

				$json = "";

				foreach($thing as $player) {
					$json .= $player.",";
				}

				$json = substr($json, 0, strlen($json)-1);

				if(str_starts_with($json, ",")) {
					$json = substr($json, 1);
				} else if(str_starts_with($json, "[],")) {
					$json = substr($json, 3);
				}
			
				$players = count($thing);
			}

			$stmt = $con->prepare('UPDATE `running_games` SET `game_playersdata` = ?, `game_players` = ? WHERE `game_id` = ?');
			$stmt->bind_param('sis', $json, $players, $id);
			$stmt->execute();

			self::UpdatePlayerCount($row['game_placeid']);
			self::GetPlayerCount($row['game_placeid']);
		}
		
		public static function UpdatePlayerCount($place_id) {
			require $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			$stmt = $con->prepare('SELECT * FROM `running_games` WHERE `game_placeid` = ?');
			$stmt->bind_param('i', $place_id);
			$stmt->execute();

			$result = $stmt->get_result();

			$total_players = 0;

			while(($row = $result->fetch_assoc()) != null) {
				$total_players += $row['game_players'];
			}

			$stmt = $con->prepare('UPDATE `assets` SET `place_playercount` = ? WHERE `asset_id` = ?;');
			$stmt->bind_param('ii', $total_players, $place_id);
			$stmt->execute();

			return $total_players;
		}


		public static function GetPlayerCount($id): int {
			self::UpdatePlayerCount($id);

			$games = self::GetAllRunningGames($id);
			$playercount = 0;
			foreach($games as $game) {
				$playercount += $game['game_players'];
			}

			return $playercount;
		}
	}

	
?>

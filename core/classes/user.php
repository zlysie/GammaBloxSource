<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/core/classes/banmessage.php';
class User {
	public int $id;
	public string $name;
	public string $blurb;
	public int $chat_type;
	public DateTime $join_date;
	public bool $online;
	public DateTime $last_online;

	const BADGE_ADMINISTRATOR = 1;
	const BADGE_FORUM_MOD = 2;
	const BADGE_IMAGE_MOD = 3;
	const BADGE_HOMESTEAD = 4;
	const BADGE_BRICKSMITH = 5;
	const BADGE_FRIENDSHIP = 6;
	const BADGE_INVITER = 7;
	const BADGE_COMBAT_INITIATION = 8;
	const BADGE_WARRIOR = 9;
	const BADGE_BLOXXER = 10;


	public static function FromID($user_id): User|null {
		include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
		$stmt_getuser = $con->prepare("SELECT * FROM `users` WHERE `id` = ?");
		$stmt_getuser->bind_param('i', $user_id);
		$stmt_getuser->execute();
		$result = $stmt_getuser->get_result();

		if($result->num_rows == 1) {
			return new self($result->fetch_assoc());
		} else {
			return null;
		}
	}

	public static function GetUserFromKey($security_key): User|null {
		include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
		$stmt_getuser = $con->prepare("SELECT * FROM `users` WHERE `security_key` = ?");
		$stmt_getuser->bind_param('s', $security_key);
		$stmt_getuser->execute();
		$result = $stmt_getuser->get_result();

		if($result->num_rows == 1) {
			return new self($result->fetch_assoc());
		} else {
			return null;
		}
	}

	public static function Exists($user_id): bool {
		include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
		$stmt_getuser = $con->prepare("SELECT * FROM `users` WHERE `id` = ?");
		$stmt_getuser->bind_param('i', $user_id);
		$stmt_getuser->execute();
		$result = $stmt_getuser->get_result();

		return $result->num_rows == 1;
	}

	function __construct($rowdata) {
		include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
		
		$this->id = intval($rowdata['id']);
		$this->name = $rowdata['username'];
		$this->blurb = str_replace("<", "&lt;", str_replace(">", "&gt;", $rowdata['blurb']));
		
		$stmt_user_status_check = $con->prepare('SELECT * FROM `activity` WHERE `userid` = ? AND `action_time` > DATE_SUB(NOW(),INTERVAL 15 MINUTE)');
		$stmt_user_status_check->bind_param('i', $this->id);
		$stmt_user_status_check->execute();
		$activity_result = $stmt_user_status_check->get_result();
		$query_status = $activity_result->num_rows == 1;

		$stmt_user_status_check = $con->prepare('SELECT * FROM `activity` WHERE `userid` = ?');
		$stmt_user_status_check->bind_param('i', $this->id);
		$stmt_user_status_check->execute();
		$activity_result = $stmt_user_status_check->get_result();
		$activity = $activity_result->fetch_assoc();

		$this->online = $query_status;
		if($activity != null) {
			$this->last_online = DateTime::createFromFormat("Y-m-d H:i:s", $activity['action_time']);
		} else {
			$this->last_online = DateTime::createFromFormat('U', time());
		}
		
	}

	function GetStatus(?bool $link = false): string {
		if($this->online) {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";

			$stmt_find_running_games = $con->prepare('SELECT * FROM `running_games` WHERE `game_playersdata` LIKE ?;');
			$string = "".$this->id;
			$stmt_find_running_games->bind_param('s', $string);
			$stmt_find_running_games->execute();
			$find_running_games_result = $stmt_find_running_games->get_result();
			if($find_running_games_result->num_rows == 1) {
				$game = $find_running_games_result->fetch_assoc();
				$data = Place::FromID($game['game_placeid']);
				$place_name = $data->name;
				$place_id = $data->id;
				return $link ? "<a href=\"/Item.aspx?ID=$place_id\" style=\"color:inherit;\">$place_name</a>" : $place_name;
			}

			$stmt_find_running_games = $con->prepare('SELECT * FROM `running_games` WHERE `game_playersdata` LIKE ?;');
			$string = $this->id.",%";
			$stmt_find_running_games->bind_param('s', $string);
			$stmt_find_running_games->execute();
			$find_running_games_result = $stmt_find_running_games->get_result();
			if($find_running_games_result->num_rows == 1) {
				$game = $find_running_games_result->fetch_assoc();
				$data = Place::FromID($game['game_placeid']);
				$place_name = $data->name;
				$place_id = $data->id;
				return $link ? "<a href=\"/Item.aspx?ID=$place_id\" style=\"color:inherit;\">$place_name</a>" : $place_name;
			}

			$stmt_find_running_games = $con->prepare('SELECT * FROM `running_games` WHERE `game_playersdata` LIKE ?;');
			$string = "%,".$this->id.",%";
			$stmt_find_running_games->bind_param('s', $string);
			$stmt_find_running_games->execute();
			$find_running_games_result = $stmt_find_running_games->get_result();
			if($find_running_games_result->num_rows == 1) {
				$game = $find_running_games_result->fetch_assoc();
				$data = Place::FromID($game['game_placeid']);
				$place_name = $data->name;
				$place_id = $data->id;
				return $link ? "<a href=\"/Item.aspx?ID=$place_id\" style=\"color:inherit;\">$place_name</a>" : $place_name;
			}

			$stmt_find_running_games = $con->prepare('SELECT * FROM `running_games` WHERE `game_playersdata` LIKE ?;');
			$string = "%,".$this->id;
			$stmt_find_running_games->bind_param('s', $string);
			$stmt_find_running_games->execute();
			$find_running_games_result = $stmt_find_running_games->get_result();
			if($find_running_games_result->num_rows == 1) {
				$game = $find_running_games_result->fetch_assoc();
				$data = Place::FromID($game['game_placeid']);
				$place_name = $data->name;
				$place_id = $data->id;
				return $link ? "<a href=\"/Item.aspx?ID=$place_id\" style=\"color:inherit;\">$place_name</a>" : $place_name;
			}

			return "Website";
		}

		return "Offline";
	}

	function GetPassword(): string|null {
		include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
		$stmt_getpassword = $con->prepare('SELECT * FROM `users` WHERE `id` = ?');
		$stmt_getpassword->bind_param('i', $this->id);
		$stmt_getpassword->execute();
		return strval($stmt_getpassword->get_result()->fetch_assoc()['password']);
	}

	function GetSecurity(): string|null {
		include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
		$stmt_getsecuritykey = $con->prepare('SELECT * FROM `users` WHERE `id` = ?');
		$stmt_getsecuritykey->bind_param('i', $this->id);
		$stmt_getsecuritykey->execute();
		return strval($stmt_getsecuritykey->get_result()->fetch_assoc()['security_key']);
	}
 
	function IsAdmin(): bool {
		include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
		$stmt_checkadmin = $con->prepare('SELECT * FROM `badges` WHERE `badge_user` = ? AND `badge_type` = 1');
		$stmt_checkadmin->bind_param('i', $this->id);
		$stmt_checkadmin->execute();
		return $stmt_checkadmin->get_result()->num_rows == 1;
	}

	function IsBanned(): bool {
		include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
		$stmt_checkban = $con->prepare('SELECT * FROM `suspended` WHERE `suspended_user` = ? AND (`suspended_enddate` > CURRENT_DATE() OR `suspended_enddate` IS NULL);');
		$stmt_checkban->bind_param('i', $this->id);
		$stmt_checkban->execute();
		return $stmt_checkban->get_result()->num_rows != 0;
	}

	function GetBanReason(): BanMessage|null {
		if($this->IsBanned()) {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			$stmt_checkban = $con->prepare('SELECT * FROM `suspended` WHERE `suspended_user` = ? AND (`suspended_enddate` > CURRENT_DATE() OR `suspended_enddate` IS NULL);');
			$stmt_checkban->bind_param('i', $this->id);
			$stmt_checkban->execute();
			return new BanMessage($stmt_checkban->get_result()->fetch_assoc());
		}
		return null;
	}

	function GetBadges(): array {
		include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
		$stmt_getbadge = $con->prepare('SELECT * FROM `badges` WHERE `badge_user` = ? ORDER BY `badge_recieved` ASC');
		$stmt_getbadge->bind_param('i', $this->id);
		$stmt_getbadge->execute();
		$stmtres = $stmt_getbadge->get_result();
		$result = [];

		while(($row = $stmtres->fetch_assoc()) != null) {
			$stmt_getbadgeinfo = $con->prepare('SELECT * FROM `badges_info` WHERE `badge_id` = ?');
			$stmt_getbadgeinfo->bind_param('i', $row['badge_type']);
			$stmt_getbadgeinfo->execute();
			$stmt__res = $stmt_getbadgeinfo->get_result();
			$badge = $stmt__res->fetch_assoc();
			array_push($result, $badge);
		}

		return $result;
	}

	function HasBadge(?int $badge_id): bool {
		include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
		$stmt_hasbadge = $con->prepare('SELECT * FROM `badges` WHERE `badge_user` = ? AND `badge_type` = ?;');
		$stmt_hasbadge->bind_param('ii', $this->id, $badge_id);
		$stmt_hasbadge->execute();
		return $stmtres = $stmt_hasbadge->get_result()->num_rows == 1;
	}

	function GiveBadge(?int $badge_id) {
		include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
		$stmt_hasbadge = $con->prepare('SELECT * FROM `badges` WHERE `badge_user` = ? AND `badge_type` = ?;');
		$stmt_hasbadge->bind_param('ii', $this->id, $badge_id);
		$stmt_hasbadge->execute();
		$stmtres = $stmt_hasbadge->get_result();
		
		if($stmtres->num_rows == 0) {
			$stmt_givebadge = $con->prepare('INSERT INTO `badges`(`badge_user`, `badge_type`) VALUES (?, ?);');
			$stmt_givebadge->bind_param('ii', $this->id, $badge_id);
			$stmt_givebadge->execute();
		}
	}

	function GetKillCount() {
		include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
		$stmt_getkillcount = $con->prepare('SELECT * FROM `kills` WHERE `killer` = ?');
		$stmt_getkillcount->bind_param('i', $this->id);
		$stmt_getkillcount->execute();
		return $stmt_getkillcount->get_result()->num_rows;
	}

	function GetDeathCount() {
		include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
		$stmt_getdeathcount = $con->prepare('SELECT * FROM `kills` WHERE `victim` = ?');
		$stmt_getdeathcount->bind_param('i', $this->id);
		$stmt_getdeathcount->execute();
		return $stmt_getdeathcount->get_result()->num_rows;
	}
}

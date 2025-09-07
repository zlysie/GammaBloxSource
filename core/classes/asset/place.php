<?php

class Place extends Asset {
	public int $maxplayers;
	public bool $copylocked;
	public bool $friends_only;
	public int $visits;
	public int $currently_playing;

	public static function FromID($asset_id): Place|null {
		include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
		$stmt_getuser = $con->prepare("SELECT * FROM `assets` WHERE `asset_id` = ?");
		$stmt_getuser->bind_param('i', $asset_id);
		$stmt_getuser->execute();
		$result = $stmt_getuser->get_result();

		if($result->num_rows == 1) {
			return new self($result->fetch_assoc());
		} else {
			return null;
		}
	}

	//TO-DO: return array of running games, check if they even exist, and remove accordingly.
	public static function GetAllRunningGames() {

	}

	function __construct($rowdata) {
		parent::__construct($rowdata);
		$this->copylocked = boolval($rowdata['place_copylocked']);
		$this->friends_only = boolval($rowdata['place_access']);
		$this->visits = intval($rowdata['place_visitcount']);
		$this->currently_playing = intval($rowdata['place_playercount']);
		$this->maxplayers = intval($rowdata['place_maxplayers']);
	}
}

<?php

class Asset {

	const TSHIRT = 2;
	const HAT = 8;
	const PLACE = 9;
	const MODEL = 10;
	const SHIRT = 11;
	const PANTS = 12;
	const DECAL = 13;
	
	const REJECTED = -1;
	const PENDING = 1;
	const ACCEPTED = 0;

	/**
	 * ID of asset
	 * @var int
	 */
	public int $id;
	/**
	 * Category of asset
	 * @var int
	 */
	public int $type;
	public string $name;
	public string $description;
	public User|null $creator;
	/**
	 * Number of favourites accumulated.
	 * @var int
	 */
	public int $favourites;
	/**
	 * Is comments enabled.
	 * @var bool
	 */
	public bool $comments_enabled;
	public int $status;
	public DateTime $last_updated;
	public DateTime $time_created;

	public static function FromID($asset_id): Asset|null {
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

	function __construct($rowdata) {
		include_once $_SERVER["DOCUMENT_ROOT"]."/core/classes/user.php";
		$this->id = intval($rowdata['asset_id']);
		$this->type = intval($rowdata['asset_type']);
		$this->name = str_replace("<", "&lt;", str_replace(">", "&gt;", $rowdata['asset_name']));
		$this->description = str_replace("<", "&lt;", str_replace(">", "&gt;", $rowdata['asset_description']));
		$this->creator = User::FromID(intval($rowdata['asset_creator']));
		$this->favourites = intval($rowdata['asset_favcount']);
		$this->comments_enabled = boolval($rowdata['asset_enablecomments']);

		$this->status = intval($rowdata['asset_status']);
		$this->last_updated = new DateTime();
		$this->last_updated->setTimestamp(strtotime($rowdata['asset_lastupdate']));
		$this->time_created = new DateTime();
		$this->time_created->setTimestamp(strtotime($rowdata['asset_creationdate']));
	}

	function GetLastUpdateTimestamp(): int {
		return strtotime($this->last_updated->format('Y-m-d H:i:s'));
	}

	function GetTimeCreatedTimestamp(): int {
		return strtotime($this->time_created->format('Y-m-d H:i:s'));
	}

	function Favourite() {
		include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
		require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";

		$user = UserUtils::GetLoggedInUser();
		
		if($user != null) {
			$stmt_getasset = $con->prepare("SELECT * FROM `favourites` WHERE `fav_assetid` = ? AND `fav_userid` = ?");
			$stmt_getasset->bind_param('ii', $this->id, $user->id);
			$stmt_getasset->execute();

			$result = $stmt_getasset->get_result();

			if($result->num_rows == 0) {
				$stmt_favasset = $con->prepare("INSERT INTO `favourites`(`fav_assetid`, `fav_userid`, `fav_assettype`) VALUES (?,?,?)");
				$stmt_favasset->bind_param('iii', $this->id, $user->id, $this->type);
				$stmt_favasset->execute();
				
				$this->UpdateFavouriteCount();
			}
		}
	}
	
	function Unfavourite() {
		include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
		require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
		
		$user = UserUtils::GetLoggedInUser();
		if($user != null) {
			$stmt_getasset = $con->prepare("DELETE FROM `favourites` WHERE `fav_assetid` = ? AND `fav_userid` = ?");
			$stmt_getasset->bind_param('ii', $this->id, $user->id);
			$stmt_getasset->execute();

			$this->UpdateFavouriteCount();
		}
	}

	private function UpdateFavouriteCount() {
		include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
		$stmt_get_fav_count = $con->prepare("SELECT * FROM `favourites` WHERE `fav_assetid` = ?");
		$stmt_get_fav_count->bind_param('i', $this->id);
		$stmt_get_fav_count->execute();
		$fav_count = $stmt_get_fav_count->get_result()->num_rows;

		$stmt_update_fav_stat = $con->prepare("UPDATE `assets` SET `asset_favcount` = ? WHERE `asset_id` = ?");
		$stmt_update_fav_stat->bind_param('ii', $fav_count, $this->id);
		$stmt_update_fav_stat->execute();
	}

}

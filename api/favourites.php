<?php

require_once $_SERVER["DOCUMENT_ROOT"]."/core/assetutils.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
require_once $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";

$user = UserUtils::GetLoggedInUser();
if($user == null) {
	die();
}

if(isset($_GET['getfavlist']) && isset($_GET['type']) && isset($_GET['id'])) {
	header("content-type: application/json");
	
	$type = intval($_GET['type']);
	$user = intval($_GET['id']);
	
	$stmt_get_all_items = $con->prepare('SELECT * FROM `favourites` WHERE `fav_userid` = ? AND `fav_assettype` = ?');
	$stmt_get_all_items->bind_param('ii', $user, $type);
	$stmt_get_all_items->execute();
	$totalpages = (($stmt_get_all_items->get_result()->num_rows-1)/6)+1;
	$totalpages = intval($totalpages);
	
	if(isset($_GET['page'])) {
		$page = intval($_GET['page']) - 1;
	} else {
		$page = 1 - 1;
	}
	
	if($page < 0) {
		$page = 0;
	} else if($page > $totalpages) {
		$page = $totalpages - 1;
	}

	$rows = 6;
	$start = ($rows) * ($page);

	$stmt_get_items = $con->prepare('SELECT * FROM favourites WHERE fav_userid = ? AND `fav_assettype` = ? LIMIT ?, ?');
	$stmt_get_items->bind_param("isii", $user, $type, $start, $rows);
	$stmt_get_items->execute();
	$item_result = $stmt_get_items->get_result();
	$item_count = $item_result->num_rows;
	
	if($item_count > 0) {
		$asset_array = array("page"=>$page+1, "totalpages"=>$totalpages);
		while($raw_asset = $item_result->fetch_assoc()) {
			$asset = AssetUtils::GetAsset($raw_asset['fav_assetid']);
			array_push($asset_array, array("CreatorUserID"=>$asset->creator->id, "ID"=>$asset->id, "Name"=>$asset->name, "CreatorName"=>$asset->creator->name));
		}
	} else {
		$asset_array = array();
	}
	
	echo json_encode($asset_array);
	die();
} else if(isset($_GET['removeitem']) && isset($_POST['id']) && isset($_POST['userid'])) {

	$asset_id = intval($_POST['id']);
	$user_id = intval($_POST['userid']);
	Asset::FromID($asset_id)->Unfavourite();
}

?>
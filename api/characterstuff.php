<?php
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/assetutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	require $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";

	$user = UserUtils::GetLoggedInUser();

	if($user == null) {
		die();
	}

	function GetAppearance($userid) {
		require $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
		$stmt_getappearance = $con->prepare("SELECT * FROM `inventory` WHERE `userid` = ?");
		$stmt_getappearance->bind_param('i', $userid);
		$stmt_getappearance->execute();
		return $stmt_getappearance->get_result()->fetch_assoc();
	}

	function CountAllAssets($userid, $asset_type) {
		require $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
		$stmt_get_items = $con->prepare("SELECT `ta_asset` FROM `transactions` WHERE `ta_userid` = ? AND `ta_assettype` = $asset_type AND `ta_asset` IS NOT NULL");
		$stmt_get_items->bind_param('i', $userid);
		$stmt_get_items->execute();
		$_result = $stmt_get_items->get_result();
		$item_count = $_result->num_rows;
		$count = 0;

		$appearance = GetAppearance($userid);
		
		while($raw_asset = $_result->fetch_assoc()) {
			if($raw_asset['ta_asset'] != $appearance['hat'] && 
			$raw_asset['ta_asset'] != $appearance['tshirt'] && 
			$raw_asset['ta_asset'] != $appearance['shirt'] && 
			$raw_asset['ta_asset'] != $appearance['pants']) {
				$asset = AssetUtils::GetAsset(intval($raw_asset['ta_asset']));
				if($asset->status == 0) {
					$count += 1;
				}
				
			}
		}

		return $count;
	}

	function GetAssetsPaged($userid, $asset_type, $page, $count = 8) {
		$c_page = ($page*$count);
		require $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
		$stmt_get_items = $con->prepare("SELECT `ta_asset` FROM `transactions` WHERE `ta_userid` = ? AND `ta_assettype` = $asset_type AND `ta_asset` IS NOT NULL LIMIT ?, ?");
		$stmt_get_items->bind_param('iii', $userid, $c_page, $count);
		$stmt_get_items->execute();
		$_result = $stmt_get_items->get_result();
		$item_count = $_result->num_rows;

		$total_assets_count = CountAllAssets($userid, $asset_type);
		$page_count = ceil($total_assets_count/$count);

		$asset_array = array("page"=>$page+1, "totalpages"=>$page_count);

		$appearance = GetAppearance($userid);

		while($raw_asset = $_result->fetch_assoc()) {
			if($raw_asset['ta_asset'] != $appearance['hat'] && 
			$raw_asset['ta_asset'] != $appearance['tshirt'] && 
			$raw_asset['ta_asset'] != $appearance['shirt'] && 
			$raw_asset['ta_asset'] != $appearance['pants']) {
				$asset = AssetUtils::GetAsset(intval($raw_asset['ta_asset']));
				$creator = $asset->creator;
				if($asset->status == 0) {
					array_push($asset_array, array("ID"=>$asset->id, "Name"=>$asset->name, "CreatorUserID" => $creator->id, "CreatorName" => $creator->name));
				}
			}
		}

		return $asset_array;
	}
	header("content-type: application/json");
	if(isset($_GET['c'])) {

		$category = intval($_GET['c']);
		$count = 8;
		$page = intval($_GET['p']);
		$page = $page-1;

		$userid = $user->id;

		$asset_array = array();
		if($category == 2) { // T-Shirts
			$asset_array = GetAssetsPaged($userid, $category, $page);
		}
		else if($category == 8) { // Hats
			$asset_array = GetAssetsPaged($userid, $category, $page);
		}
		else if($category == 11) { // Shirts
			$asset_array = GetAssetsPaged($userid, $category, $page);
		} 
		else if($category == 12) { // Pants
			$asset_array = GetAssetsPaged($userid, $category, $page);
		} 
		
		die(json_encode($asset_array));
	} else if(isset($_GET['wear'])) {
		header("content-type: application/json");

		$asset_id = intval($_GET['wear']);

		$userid = $user->id;

		$asset = AssetUtils::GetAsset(intval($asset_id));
		if($asset != null) {
			$creator = $asset->creator;

			if($asset->type == Asset::TSHIRT || 
			$asset->type == Asset::HAT || 
			$asset->type == Asset::SHIRT ||
			$asset->type == Asset::PANTS) {

				$stmt_get_items = $con->prepare("SELECT `ta_asset` FROM `transactions` WHERE `ta_userid` = ? AND `ta_asset` = ?");
				$stmt_get_items->bind_param('ii', $userid, $asset_id);
				$stmt_get_items->execute();
				$_result = $stmt_get_items->get_result();
				$item_count = $_result->num_rows;
				if($item_count != 0) {
					if($asset->type == 2) {
						$stmt_getappearance = $con->prepare("UPDATE `inventory` SET `tshirt` = ? WHERE `userid` = ?");
						$stmt_getappearance->bind_param('ii', $asset_id, $userid);
						$stmt_getappearance->execute();
					} else if($asset->type == 8) {
						$stmt_getappearance = $con->prepare("UPDATE `inventory` SET `hat` = ? WHERE `userid` = ?");
						$stmt_getappearance->bind_param('ii', $asset_id, $userid);
						$stmt_getappearance->execute();
					} else if($asset->type == 11) {
						$stmt_getappearance = $con->prepare("UPDATE `inventory` SET `shirt` = ? WHERE `userid` = ?");
						$stmt_getappearance->bind_param('ii', $asset_id, $userid);
						$stmt_getappearance->execute();
					} else if($asset->type == 12) {
						$stmt_getappearance = $con->prepare("UPDATE `inventory` SET `pants` = ? WHERE `userid` = ?");
						$stmt_getappearance->bind_param('ii', $asset_id, $userid);
						$stmt_getappearance->execute();
					}
				}
				
				$mediadir = $_SERVER['DOCUMENT_ROOT']."/../gamma-assets/player";
				$md5hash = UserUtils::GetUserAppearanceHashed($user->id);
				if(!is_dir("$mediadir/$md5hash/")) {
					file_get_contents("http://localhost:64209/render?id=".$user->id."&type=character&data=".urlencode(UserUtils::GetUserAppearance($user->id))."&md5=".$md5hash, false);
				}
			}

		}
		echo "[]";
	} else if(isset($_GET['takeoff'])) {
		$userid = $user->id;
		$asset_id = intval($_GET['takeoff']);
		$asset = AssetUtils::GetAsset($asset_id);
		if($asset != null) {
			$type = $asset->type;
			
			$stmt_get_items = $con->prepare("SELECT `ta_asset` FROM `transactions` WHERE `ta_userid` = ? AND `ta_asset` = ?");
			$stmt_get_items->bind_param('ii', $userid, $asset_id);
			$stmt_get_items->execute();
			$_result = $stmt_get_items->get_result();
			$item_count = $_result->num_rows;
			if($item_count != 0) {
				if($type == Asset::TSHIRT) {
					$stmt_getappearance = $con->prepare("UPDATE `inventory` SET `tshirt` = NULL WHERE `userid` = ?");
					$stmt_getappearance->bind_param('i', $userid);
					$stmt_getappearance->execute();
				} else if($type == Asset::HAT) {
					$stmt_getappearance = $con->prepare("UPDATE `inventory` SET `hat` = NULL WHERE `userid` = ?");
					$stmt_getappearance->bind_param('i', $userid);
					$stmt_getappearance->execute();
				} else if($type == Asset::SHIRT) {
					$stmt_getappearance = $con->prepare("UPDATE `inventory` SET `shirt` = NULL WHERE `userid` = ?");
					$stmt_getappearance->bind_param('i', $userid);
					$stmt_getappearance->execute();
				} else if($type == Asset::PANTS) {
					$stmt_getappearance = $con->prepare("UPDATE `inventory` SET `pants` = NULL WHERE `userid` = ?");
					$stmt_getappearance->bind_param('i', $userid);
					$stmt_getappearance->execute();
				}
			}
		}
		

		$mediadir = $_SERVER['DOCUMENT_ROOT']."/../gamma-assets/player";
		$md5hash = UserUtils::GetUserAppearanceHashed($user->id);
		if(!is_dir("$mediadir/$md5hash/")) {
			file_get_contents("http://localhost:64209/render?id=".$user->id."&type=character&data=".urlencode(UserUtils::GetUserAppearance($user->id))."&md5=".$md5hash, false);
		}
	} else if(isset($_GET['getwearing'])) {
		$userid = $user->id;
		$appearance = GetAppearance($userid);
		$hat_asset = AssetUtils::GetAsset($appearance['hat']);
		$tshirt_asset = AssetUtils::GetAsset($appearance['tshirt']);
		$shirt_asset = AssetUtils::GetAsset($appearance['shirt']);
		$pants_asset = AssetUtils::GetAsset($appearance['pants']);

		$asset_array = [];

		if($hat_asset != null) {
			$creator = $hat_asset->creator;
			array_push($asset_array, array(
				"ID"=>$hat_asset->id, 
				"Name"=>$hat_asset->name, 
				"CreatorUserID" => $creator->id, 
				"CreatorName" => $creator->name,
				"Type" => 8));
		}

		if($tshirt_asset != null) {
			$creator = $tshirt_asset->creator;
			array_push($asset_array, array(
				"ID"=>$tshirt_asset->id, 
				"Name"=>$tshirt_asset->name, 
				"CreatorUserID" => $creator->id, 
				"CreatorName" => $creator->name,
				"Type" => 2));
		}

		if($shirt_asset != null) {
			$creator = $shirt_asset->creator;
			array_push($asset_array, array(
				"ID"=>$shirt_asset->id, 
				"Name"=>$shirt_asset->name, 
				"CreatorUserID" => $creator->id, 
				"CreatorName" => $creator->name,
				"Type" => 11));
		}

		if($pants_asset != null) {
			$creator = $pants_asset->creator;
			array_push($asset_array, array(
				"ID"=>$pants_asset->id, 
				"Name"=>$pants_asset->name, 
				"CreatorUserID" => $creator->id, 
				"CreatorName" => $creator->name,
				"Type" => 12));
		}

		die(json_encode($asset_array));
	}
?>
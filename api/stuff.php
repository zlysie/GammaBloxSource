<?php 
	session_start();
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/assetutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/transactionutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
	header("content-type: application/json"); 
	$user = UserUtils::GetLoggedInUser();
	if($user == null) {
		die();
	}

	function getUsernameFromID($id): string {
		return User::FromID($id)->name;
	}

	if(isset($_GET['type']) && isset($_GET['usr_id'])) {
		$type_to_look_for = intval($_GET['type']);
		$user = intval($_GET['usr_id']);
		
		$stmt_get_all_items = $con->prepare('SELECT * FROM `transactions` WHERE `ta_userid` = ? AND `ta_assettype` = ?');
		$stmt_get_all_items->bind_param("ii", $user, $type_to_look_for);
		$stmt_get_all_items->execute();
		$totalpages = (($stmt_get_all_items->get_result()->num_rows-1)/15)+1;
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
		
		$rows = 15;
		$start = ($rows) * ($page);
		
		if($type_to_look_for != 9 && $type_to_look_for != 10 && $type_to_look_for != 13) {
			$stmt_assetinfo = $con->prepare('SELECT * FROM `transactions` WHERE (`ta_userid` = ? OR (`ta_userid` = ? AND `ta_assetcreator` = ?)) AND `ta_assettype` = ? ORDER BY `ta_date` DESC LIMIT ?, ?');
			$stmt_assetinfo->bind_param('iiiiii', $user, $user, $user, $type_to_look_for, $start, $rows);
			$stmt_assetinfo->execute();
			$result = $stmt_assetinfo->get_result();
			$num_rows = $result->num_rows;
	
			
			if($num_rows > 0) {
				$asset_array = array("page"=>$page+1, "totalpages"=>$totalpages);		
				while($row = $result->fetch_assoc()) {
					$asset = AssetUtils::GetAsset($row['ta_asset']);
					if($asset instanceof BuyableAsset) {
						$cost = $asset->tux;
						if(!$asset->onsale) {
							$cost = 0;
						}
						array_push($asset_array, array("CreatorUserID" => $asset->creator->id, "ID" => $asset->id, "Name" => $asset->name, "CreatorName" => $asset->creator->name, "Cost" => $cost));
					}
				}
			} else {
				$asset_array = array();
			}
		} else {
			$stmt_assetinfo = $con->prepare('SELECT * FROM `assets` WHERE `asset_creator` = ? AND `asset_type` = ? ORDER BY `asset_creationdate` DESC LIMIT ?, ?');
			$stmt_assetinfo->bind_param('iiii', $user, $type_to_look_for, $start, $rows);
			$stmt_assetinfo->execute();
			$result = $stmt_assetinfo->get_result();
			$num_rows = $result->num_rows;
	
			
			if($num_rows > 0) {
				$asset_array = array("page"=>$page+1, "totalpages"=>$totalpages);		
				while($row = $result->fetch_assoc()) {
					$asset = new BuyableAsset($row);
					array_push($asset_array, array("CreatorUserID" => $asset->creator->id, "ID" => $asset->id, "Name" => $asset->name, "CreatorName" => $asset->creator->name, "Cost" => $asset->tux));
				}
			} else {
				$asset_array = array();
			}
		}
		
		echo json_encode($asset_array);
	}
?>

<?php
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/assetutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	require $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
	$user = UserUtils::GetLoggedInUser();
	if($user == null) {
		die();
	}

	function GetAssetsFromUser($asset_type) {
		$asset_array = array();
		require $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
		$user = UserUtils::GetLoggedInUser();
		$stmt_get_items = $con->prepare("SELECT * FROM `assets` WHERE `asset_type` = $asset_type AND `asset_creator` = ? AND `asset_status` = 0 ORDER BY `asset_creationdate` ASC");
		$stmt_get_items->bind_param("i", $user->id);
		$stmt_get_items->execute();
		$item_result = $stmt_get_items->get_result();
		$item_count = $item_result->num_rows;
		while($raw_asset = $item_result->fetch_assoc()) {
			$asset = AssetUtils::GetAsset($raw_asset['asset_id']);
			array_push($asset_array, array("ID"=>$asset->id, "Name"=>$asset->name));
		}

		return $asset_array;
	}

	function GetAssetsFromUserPaged($asset_type, $page, $count = 20) {
		$c_page = ($page*$count);
		require $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
		$user = UserUtils::GetLoggedInUser();
		$stmt_get_items = $con->prepare("SELECT * FROM `assets` WHERE `asset_type` = $asset_type AND `asset_creator` = ? AND `asset_status` = 0 ORDER BY `asset_creationdate` ASC LIMIT ?, ?");
		$stmt_get_items->bind_param("iii", $user->id, $c_page, $count);
		$stmt_get_items->execute();
		$_result = $stmt_get_items->get_result();
		$item_count = $_result->num_rows;

		$total_assets_count = count(GetAssetsFromUser($asset_type));
		$page_count = ceil($total_assets_count/$count);

		$asset_array = array("page"=>$page+1, "totalpages"=>$page_count, "assetcount" => $item_count, "totalassets" => $total_assets_count);


		while($raw_asset = $_result->fetch_assoc()) {
			$asset = AssetUtils::GetAsset($raw_asset['asset_id']);
			array_push($asset_array, array("ID"=>$asset->id, "Name"=>$asset->name));
		}

		return $asset_array;
	}

	function GetAssetsFromStuffUser($asset_type) {
		$asset_array = array();
		require $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
		$user = UserUtils::GetLoggedInUser();
		$stmt_get_items = $con->prepare("SELECT * FROM `assets` WHERE `asset_type` = $asset_type AND `asset_creator` = ? AND `asset_status` = 0 ORDER BY `asset_creationdate` ASC");
		$stmt_get_items->bind_param("i", $user->id);
		$stmt_get_items->execute();
		$item_result = $stmt_get_items->get_result();
		$item_count = $item_result->num_rows;
		while($raw_asset = $item_result->fetch_assoc()) {
			$asset = AssetUtils::GetAsset($raw_asset['asset_id']);
			array_push($asset_array, array("ID"=>$asset->id, "Name"=>$asset->name));
		}

		return $asset_array;
	}

	function GetAssetsFromStuffUserPaged($asset_type, $page, $count = 20) {
		$c_page = ($page*$count);
		require $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
		$user = UserUtils::GetLoggedInUser();
		$stmt_get_items = $con->prepare("SELECT * FROM `assets` WHERE `asset_type` = $asset_type AND `asset_creator` = ? AND `asset_status` = 0 ORDER BY `asset_creationdate` ASC LIMIT ?, ?");
		$stmt_get_items->bind_param("iii", $user->id, $c_page, $count);
		$stmt_get_items->execute();
		$_result = $stmt_get_items->get_result();
		$item_count = $_result->num_rows;

		$total_assets_count = count(GetAssetsFromUser($asset_type));
		$page_count = ceil($total_assets_count/$count);

		$asset_array = array("page"=>$page+1, "totalpages"=>$page_count, "assetcount" => $item_count, "totalassets" => $total_assets_count);


		while($raw_asset = $_result->fetch_assoc()) {
			$asset = AssetUtils::GetAsset($raw_asset['asset_id']);
			array_push($asset_array, array("ID"=>$asset->id, "Name"=>$asset->name));
		}

		return $asset_array;
	}

	function GetAllAssets($asset_type, $query) {
		require $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
		$asset_array = array();
		$stmt_get_items = $con->prepare("SELECT * FROM `assets` WHERE `asset_name` LIKE ? AND `asset_type` = $asset_type AND `asset_status` = 0 ORDER BY `asset_creationdate` ASC");
		$stmt_get_items->bind_param('s', $query);
		$stmt_get_items->execute();
		$item_result = $stmt_get_items->get_result();
		$item_count = $item_result->num_rows;
		while($raw_asset = $item_result->fetch_assoc()) {
			$asset = AssetUtils::GetAsset($raw_asset['asset_id']);
			array_push($asset_array, array("ID"=>$asset->id, "Name"=>$asset->name));
		}

		return $asset_array;
	}

	function GetAllAssetsPaged($asset_type, $query, $page, $count = 20) {
		$c_page = ($page*$count);
		require $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
		$stmt_get_items = $con->prepare("SELECT * FROM `assets` WHERE `asset_name` LIKE ? AND `asset_type` = $asset_type AND `asset_status` = 0 ORDER BY `asset_creationdate` ASC LIMIT ?, ?");
		$stmt_get_items->bind_param('sii', $query, $c_page, $count);
		$stmt_get_items->execute();
		$_result = $stmt_get_items->get_result();
		$item_count = $_result->num_rows;

		$total_assets_count = count(GetAllAssets($asset_type, $query));
		$page_count = ceil($total_assets_count/$count);

		$asset_array = array("page"=>$page+1, "totalpages"=>$page_count, "assetcount" => $item_count, "totalassets" => $total_assets_count);

		while(($raw_asset = $_result->fetch_assoc()) != null) {
			$asset = AssetUtils::GetAsset($raw_asset['asset_id']);
			array_push($asset_array, array("ID"=>$asset->id, "Name"=>$asset->name));
		}

		return $asset_array;
	}

	if(isset($_GET['c'])) {
		header("content-type: application/json");

		$category = intval($_GET['c']);
		$query = "%".urldecode($_GET['q'])."%";
		$count = 20;
		$page = intval($_GET['p']);
		$page = $page-1;
		
		$bricks = [
			[ 'ID' => 1, 'Name' => "White" ],
			[ 'ID' => 2, 'Name' => "Gray"],
			[ 'ID' => 3, 'Name' => "Dark Gray" ],
			[ 'ID' => 4, 'Name' => "Black" ],
			[ 'ID' => 5, 'Name' => "Red" ],
			[ 'ID' => 6, 'Name' => "Yellow" ],
			[ 'ID' => 7, 'Name' => "Green" ],
			[ 'ID' => 8, 'Name' => "Blue" ],
			[ 'ID' => 9, 'Name' => "Sand Yellow" ],
			[ 'ID' => 10, 'Name' => "Brick Yellow" ],
			[ 'ID' => 11, 'Name' => "Brown" ],
			[ 'ID' => 12, 'Name' => "Sand Green" ],
		];
		$skyboxes = [
			[ 'ID' => 295, 'Name' => "Winterness" ],
			[ 'ID' => 481, 'Name' => "Broken Sky" ],
			[ 'ID' => 504, 'Name' => "Alien Red" ],
			[ 'ID' => 516, 'Name' => "Walls of Autumn" ],
		];
		$tools = [
			[ 'ID' => 518, 'Name' => "Brickbattle Tools" ]
		];

		$asset_array = array();
		if($category == 1) { // Bricks
			foreach($bricks as $asset) { array_push($asset_array, $asset); }
		} 
		else if($category == 4) { // Tools
			foreach($tools as $asset) { array_push($asset_array, $asset); }
		} 
		else if($category == 7) { // Skyboxes
			foreach($skyboxes as $asset) { array_push($asset_array, $asset); }
		} 
		else if($category == 10) { // My Decals
			$asset_array = GetAssetsFromUserPaged(13, $page);
		} 
		else if($category == 11) { // All Decals
			$asset_array = GetAllAssetsPaged(13, $query, $page);
		} 
		else if($category == 12) { // My Models
			$asset_array = GetAssetsFromUserPaged(10, $page);
		} 
		else if($category == 13) { // All Models
			$asset_array = GetAllAssetsPaged(10, $query, $page);
		}
		die(json_encode($asset_array));
	}
?>
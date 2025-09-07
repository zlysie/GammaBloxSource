<?php 
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/assetutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/transactionutils.php";

	$user = UserUtils::GetLoggedInUser();
	header("Content-Type: application/json");
	if($user != null && isset($_POST['asset_id'])) {
		//echo "user is logged in and recieved post";
		$asset_id = intval($_POST['asset_id']);
		$item = AssetUtils::GetAsset($asset_id);
		if($item != null) {
			if($item instanceof BuyableAsset) {
				if(!TransactionUtils::DoesUserOwnAsset($user->id, $asset_id) && $item->onsale) {
					$result = TransactionUtils::BuyItem("tickets", $asset_id);
					if($result != "yay") {
						echo "{ \"error\" : true, \"message\":\"$result\"}";
					} else {
						echo "{ \"error\" : false, \"message\":\"Success!\"}";
					}
					
				} else {
					echo "{ \"error\" : true, \"message\":\"User has already purchased this item!\"}";
				}
			} else {
				echo "{ \"error\" : true, \"message\":\"Asset does not exist!\"}";
			}
		} else {
			echo "{ \"error\" : true, \"message\":\"Asset does not exist!\"}";
		}
		die();
	} else {
		echo "{ \"error\" : true, \"message\":\User is not logged in.\"}";
	}
?>
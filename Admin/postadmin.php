<?php
	include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/assetutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	
	$user = UserUtils::GetLoggedInUser();

	function guidv4($data = null) {
		// Generate 16 bytes (128 bits) of random data or use the data passed into the function.
		$data = $data ?? random_bytes(16);
		assert(strlen($data) == 16);

		// Set version to 0100
		$data[6] = chr(ord($data[6]) & 0x0f | 0x40);
		// Set bits 6-7 to 10
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80);

		// Output the 36 character UUID.
		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
	}

	if($user->IsAdmin()) {
		if(isset($_POST['type'])) {
			if(isset($_POST['id'])) {
				$id = $_POST['id'];
				if($_POST['type'] == "accept") {
					$stmt = $con->prepare('UPDATE `assets` SET `asset_status`= 0  WHERE `asset_id` = ?');
					$stmt -> bind_param("i", $id);
					$stmt->execute();
					$message = "Success!";
				} if($_POST['type'] == "render") {
					$asset = AssetUtils::GetAsset($id);
					$type = $asset->type;
					if($type == Asset::PLACE) {
						echo file_get_contents("http://localhost:64209/render?id=$id&type=place", false);
					} else if($type == Asset::MODEL) {
						echo file_get_contents("http://localhost:64209/render?id=$id&type=model", false);
					} else if($type == Asset::SHIRT) {
						echo file_get_contents("http://localhost:64209/render?id=$id&type=shirt", false);
					} else if($type == Asset::PANTS) {
						echo file_get_contents("http://localhost:64209/render?id=$id&type=pants", false);
					}
					$message = "Success!";
				} else if($_POST['type'] == "deny") {
					$stmt = $con->prepare('UPDATE `assets` SET `asset_status`= -1  WHERE `asset_id` = ?');
					$stmt -> bind_param("i", $id);
					$stmt->execute();

					$stmt = $con->prepare('UPDATE `assets` SET `asset_name`= "[ Content Deleted ]", `asset_description`= "[ Content Deleted ]" WHERE `asset_id` = ?');
					$stmt -> bind_param("i", $id);
					$stmt->execute();

					$asset = AssetUtils::GetAsset($id);
					unlink("/var/www/gamma-assets/assets/$id");
					if(file_exists("/var/www/gamma-assets/thumbs/".$id."_250")) {
						unlink("/var/www/gamma-assets/thumbs/".$id."_250");
					}
					if(file_exists("/var/www/gamma-assets/thumbs/".$id."_120")) {
						unlink("/var/www/gamma-assets/thumbs/".$id."_120");
					}
					if(file_exists("/var/www/gamma-assets/thumbs/".$id."_120")) {
						unlink("/var/www/gamma-assets/thumbs/".$id."_420");
					}
					$message = "Success!";
				} else if($_POST['type'] == "delete") {
					$stmt = $con->prepare('UPDATE `assets` SET `asset_status`= -1  WHERE `asset_id` = ?');
					$stmt -> bind_param("i", $id);
					$stmt->execute();

					$stmt = $con->prepare('UPDATE `assets` SET `asset_name`= "[ Content Deleted ]", `asset_description`= "[ Content Deleted ]" WHERE `asset_id` = ?');
					$stmt -> bind_param("i", $id);
					$stmt->execute();

					$asset = AssetUtils::GetAsset($id);
					unlink("/var/www/gamma-assets/assets/$id");
					if(file_exists("/var/www/gamma-assets/thumbs/".$id."_250")) {
						unlink("/var/www/gamma-assets/thumbs/".$id."_250");
					}
					if(file_exists("/var/www/gamma-assets/thumbs/".$id."_120")) {
						unlink("/var/www/gamma-assets/thumbs/".$id."_120");
					}
					if(file_exists("/var/www/gamma-assets/thumbs/".$id."_120")) {
						unlink("/var/www/gamma-assets/thumbs/".$id."_420");
					}
					$message = "Success!";
				}
			} else if($_POST['type'] == "generatekey") {
				$message = guidv4();

				$stmt = $con->prepare('INSERT INTO `invite_keys`(`inv_key`) VALUES (?);');
				$stmt -> bind_param("s", $message);
				$stmt->execute();
			} else if($_POST['type'] == "getunverified") {
				$message = strval(count(AssetUtils::GetAllUncheckedAssets()));
			} else if($_POST['type'] == "giverhat") {
				require_once $_SERVER["DOCUMENT_ROOT"]."/core/transactionutils.php";
				
				if(isset($_POST['assetid']) && isset($_POST['userid'])) {
					
					$user = User::FromID(intval($_POST['userid']));
					$asset_id = intval($_POST['assetid']);
					$item = AssetUtils::GetAsset($asset_id);
					
					if($user != null && $item != null) {
						if($item instanceof BuyableAsset && $item->type == Asset::HAT) {
							if(!TransactionUtils::DoesUserOwnAsset($user->id, $asset_id)) {
								$ta_id = TransactionUtils::GenerateID();
								$stmt_processtransaction = $con->prepare("INSERT INTO `transactions`(`ta_id`, `ta_userid`, `ta_currency`, `ta_cost`, `ta_asset`, `ta_assettype`, `ta_assetcreator`) VALUES (?, ?, 'tickets', 0, ?, ?, ?)");
								$stmt_processtransaction->bind_param('siiii', $ta_id, $user->id, $asset_id, $item->type, $item->creator->id);
								$stmt_processtransaction->execute();
								$message = "Successfully given \"".$item->name."\" to ".$user->name."!";
							} else {
								$message = "Error: User has already has this item!";
							}
						} else {
							$message = "Error: Asset does not exist!";
						}
					} else {
						if($user == null && $item == null) {
							$message = "Error: User and Asset do not exist!";
						} else if($user == null) {
							$message = "Error: User does not exist!";
						} else if($asset == null) {
							$message = "Error: Asset does not exist!";
						}
					}
				} 
			} else if($_POST['type'] == "givetux") {
				require_once $_SERVER["DOCUMENT_ROOT"]."/core/transactionutils.php";
				
				if(isset($_POST['tux']) && isset($_POST['userid'])) {
					
					$user = User::FromID(intval($_POST['userid']));
					$tux = intval($_POST['tux']);
					
					if($user != null) {
						TransactionUtils::GiftTicketsToUser($user->id, $tux);
						$message = "Successfully given: ".$tux." Tux to ".$user->name."!";
					} else {
						$message = "Error: User does not exist!";
					}
				} 
			}
		}
	} else {
		$message = "You are not authorised to use this.";
	}

	die($message);
?>
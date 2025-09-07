<?php

	/*
	 ta_id *
	 ta_userid *
	 ta_currency *
	 ta_cost *
	 ta_asset (NULL)
	 ta_date (CURRENT_TIMESTAMP)
	 */

	class TransactionUtils {
		private static function getRandomString($length): string {
			$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$randomString = '';
			
			for ($i = 0; $i < $length; $i++) {
				$index = rand(0, strlen($characters) - 1);
				$randomString .= $characters[$index];
			}
	
			return $randomString;
		}

		
		public static function GenerateID() {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			$id = self::getRandomString(15); //id
			$stmt = $con->prepare('SELECT * FROM `transactions` WHERE `ta_id` LIKE ?');
			$stmt->bind_param('s', $id);
			$stmt->execute();
			$stmt->store_result();
			
			$instances = $stmt->num_rows;
			
			if($instances != 0) {
				return self::GenerateID();
			} else {
				return $id;
			}
		}

		public static function GiftTicketsToUser(?int $user_id, ?int $amount) {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			include_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
			$ta_id = self::GenerateID();
			$ta_userid = $user_id;
			$ta_cost = $amount;
			$stmt = $con->prepare('INSERT INTO `transactions`(`ta_id`, `ta_userid`, `ta_currency`, `ta_cost`) VALUES (?, ?, "tickets", ?)');
			$stmt->bind_param("sii", $ta_id, $ta_userid, $ta_cost);
			$stmt->execute();
		}

		public static function BuyItem(?string $currency, ?int $asset_id): string {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			include_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
			include_once $_SERVER["DOCUMENT_ROOT"]."/core/assetutils.php";
			$get_user = UserUtils::GetLoggedInUser();
			$asset = AssetUtils::GetAsset($asset_id);
			if($get_user != null) {
				if($asset != null) {
					if($currency == "robux") {
						$user_amount = self::GetNetRobuxFromUser($get_user->id);
						$asset_amount = AssetUtils::GetRobuxCostOf($asset_id);

						$result = $user_amount-$asset_amount;

						if($result >= 0) {
							$ta_id = self::GenerateID();
							$ta_userid = $get_user->id;
							$ta_cost = $asset_amount;
							$ta_asset = $asset->id;
							$stmt_processtransaction = $con->prepare("INSERT INTO `transactions`(`ta_id`, `ta_userid`, `ta_currency`, `ta_cost`, `ta_asset`, `ta_assettype`, `ta_assetcreator`) VALUES (?, ?, 'robux', ?, ?, ?, ?)");
							$stmt_processtransaction->bind_param('siiiii', $ta_id, $ta_userid, $ta_cost, $ta_asset, $asset->type, $asset->creator->id);
							if($stmt_processtransaction->execute()) {
								$stmt_get_sale_count = $con->prepare("SELECT * FROM `transactions` WHERE `ta_asset` = ? AND `ta_userid`!= ?");
								$stmt_get_sale_count->bind_param('ii', $asset_id, $asset->creator->id);
								$stmt_get_sale_count->execute();
								$sale_count = $stmt_get_sale_count->get_result()->num_rows;
			
								$stmt_update_sale_stat = $con->prepare("UPDATE `assets` SET `asset_salecount` = ? WHERE `asset_id` = ?");
								$stmt_update_sale_stat->bind_param('ii', $sale_count, $asset_id);
								$stmt_update_sale_stat->execute();
								return "yay";
							} else {
								return "Something went wrong at our end!";
							}
						} else {
							return "User did not have sufficient funds to perform this action!";
						}
					} else if($currency == "tickets") {
						$user_amount = self::GetNetTicketsFromUser($get_user->id);
						$asset_amount = AssetUtils::GetTicketCostOf($asset_id);

						$result = $user_amount-$asset_amount;

						if($result >= 0) {
							$ta_id = self::GenerateID();
							$ta_userid = $get_user->id;
							$ta_cost = $asset_amount;
							$ta_asset = $asset->id;
							$stmt_processtransaction = $con->prepare("INSERT INTO `transactions`(`ta_id`, `ta_userid`, `ta_currency`, `ta_cost`, `ta_asset`, `ta_assettype`, `ta_assetcreator`) VALUES (?, ?, 'tickets', ?, ?, ?, ?)");
							$stmt_processtransaction->bind_param('siiiii', $ta_id, $ta_userid, $ta_cost, $ta_asset, $asset->type, $asset->creator->id);
							if($stmt_processtransaction->execute()) {
								$stmt_get_sale_count = $con->prepare("SELECT * FROM `transactions` WHERE `ta_asset` = ? AND `ta_userid` != ?");
								$stmt_get_sale_count->bind_param('ii', $asset_id, $asset->creator->id);
								$stmt_get_sale_count->execute();
								$sale_count = $stmt_get_sale_count->get_result()->num_rows;
			
								$stmt_update_sale_stat = $con->prepare("UPDATE `assets` SET `asset_salecount` = ? WHERE `asset_id` = ?");
								$stmt_update_sale_stat->bind_param('ii', $sale_count, $asset_id);
								$stmt_update_sale_stat->execute();

								return "yay";
							} else {
								return "Something went wrong at our end!";
							}
						} else {
							return "User did not have sufficient funds to perform this action!";
						}
					} else if($currency == "free") {
						$ta_id = self::GenerateID();
						$ta_userid = $get_user->id;
						$ta_asset = $asset->id;
						$stmt_processtransaction = $con->prepare("INSERT INTO `transactions`(`ta_id`, `ta_userid`, `ta_currency`, `ta_cost`, `ta_asset`, `ta_assettype`, `ta_assetcreator`) VALUES (?, ?, 'tickets', ?, ?, ?, ?)");
						$stmt_processtransaction->bind_param('siiiii', $ta_id, $ta_userid, $ta_cost, $ta_asset, $asset->type, $asset->creator->id);
						if($stmt_processtransaction->execute()) {
							$stmt_get_sale_count = $con->prepare("SELECT * FROM `transactions` WHERE `ta_asset` = ? AND `ta_userid`!= ?");
							$stmt_get_sale_count->bind_param('ii', $asset_id, $asset->creator->id);
							$stmt_get_sale_count->execute();
							$sale_count = $stmt_get_sale_count->get_result()->num_rows;
		
							$stmt_update_sale_stat = $con->prepare("UPDATE `assets` SET `asset_salecount` = ? WHERE `asset_id` = ?");
							$stmt_update_sale_stat->bind_param('ii', $sale_count, $asset_id);
							$stmt_update_sale_stat->execute();
							return "yay";
						} else {
							return "Something went wrong at our end!";
						}
					}
					return "That was not a valid currency!";
				} else {
					return "That asset doesn't exist!";
				}
				
			} else {
				return "User is not authorised to perform this action!";
			}
		}

		public static function GetNetTicketsFromUser(?int $userid): int {
			return self::GetNetAmountFromUser("tickets", $userid);
		}

		public static function GetNetRobuxFromUser(?int $userid): int {
			return self::GetNetAmountFromUser("robux", $userid);
		}

		public static function GetNetAmountFromUser(?string $currency, ?int $userid): int {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			$stmt_getuser = $con->prepare("SELECT * FROM `transactions` WHERE (`ta_userid` = ? OR `ta_assetcreator` = ?) AND `ta_currency` LIKE ?");
			$stmt_getuser->bind_param('iis', $userid, $userid, $currency);
			$stmt_getuser->execute();

			$result = $stmt_getuser->get_result();
			$result_sum = 0;
			
			if($result->num_rows != 0) {
				while($row = $result->fetch_assoc()) {
					if(!$row['ta_asset']) {
						$result_sum += $row['ta_cost'];
					} else {
						if($row['ta_userid'] == $userid) {
							$result_sum -= $row['ta_cost'];
						} else {
							$result_sum += $row['ta_cost'];
						}
					}
					
				}
			}

			return $result_sum;
		}

		public static function DoesUserOwnAsset(?int $user_id, ?int $asset_id) {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			$stmt_getuser = $con->prepare("SELECT * FROM `transactions` WHERE `ta_userid` = ? AND `ta_asset` = ?");
			$stmt_getuser->bind_param('ii', $user_id, $asset_id);
			$stmt_getuser->execute();

			$result = $stmt_getuser->get_result();

			$result_array = [];
			return $result->num_rows != 0;
		}

		public static function GetAllOwnedAssetsFromUsername($username): array|null {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			include_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
			
			return self::GetAllOwnedAssetsFromUser(User::FromIDFromName($username)['id']);
		}
		
		public static function GetAllOwnedAssetsFromUser(?int $user_id): array|null {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			$stmt_getuser = $con->prepare("SELECT * FROM `transactions` WHERE `ta_userid` = ?");
			$stmt_getuser->bind_param('i', $user_id);
			$stmt_getuser->execute();

			$result = $stmt_getuser->get_result();

			$result_array = [];

			if($result->num_rows != 0) {
				while($row = $result->fetch_assoc()) {
					array_push($result_array, $row);
				}
				return $result_array;
			}

			return null;
		}
	}
?>
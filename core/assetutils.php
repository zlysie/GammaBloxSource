<?php

	include_once 'classes/asset.php';
	include_once 'classes/asset/buyable.php';
	include_once 'classes/asset/place.php';

	/*
		ta_id *
		ta_userid *
		ta_currency *
		ta_cost *
		ta_asset (NULL)
		ta_date (CURRENT_TIMESTAMP)
	*/

	class AssetUtils {

		public static function GetTicketCostOf(?int $asset_id): int {
			$asset = self::GetAsset($asset_id);
			if($asset instanceof BuyableAsset) {
				return $asset->tux;
			}

			return -1;
		}

		public static function GetRobuxCostOf(?int $asset_id): int {
			$asset = self::GetAsset($asset_id);
			if($asset instanceof BuyableAsset) {
				return $asset->bux;
			}

			return -1;
		}

		public static function GetAsset(?int $asset_id): Asset|null {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			$stmt_getasset = $con->prepare("SELECT * FROM `assets` WHERE `asset_id` = ?");
			$stmt_getasset->bind_param('i', $asset_id);
			$stmt_getasset->execute();

			$result = $stmt_getasset->get_result();
			

			if($result->num_rows != 0) {
				$row = $result->fetch_assoc();
				if($row['asset_type'] == Asset::TSHIRT || 
					$row['asset_type'] == Asset::HAT || 
					$row['asset_type'] == Asset::SHIRT || 
					$row['asset_type'] == Asset::PANTS ||
					$row['asset_type'] == Asset::DECAL) {
					return new BuyableAsset($row);
				} else if($row['asset_type'] == Asset::PLACE) {
					return new Place($row);
				} else {
					
					return new Asset($row);
				}
			}

			return null;
		}

		

		public static function GetAllUncheckedAssets(): array|null {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			$stmt_getallusers = $con->prepare("SELECT * FROM `assets` WHERE `asset_status` = 1");
			$stmt_getallusers->execute();
			$result = $stmt_getallusers->get_result();
			$result_array = array();

			if($result->num_rows != 0) {
				while($row = $result->fetch_assoc()) {
					if(User::FromID($row['asset_creator']) != null) {
						if($row['asset_type'] == Asset::TSHIRT || 
							$row['asset_type'] == Asset::HAT || 
							$row['asset_type'] == Asset::SHIRT || 
							$row['asset_type'] == Asset::PANTS) {
							array_push($result_array, new BuyableAsset($row));
						} else if($row['asset_type'] == Asset::PLACE) {
							array_push($result_array, new Place($row));
						} else {
							array_push($result_array, new Asset($row));
						}
					}
				}
				return $result_array;
			}
			return [];
		}

		public static function GetAllAssets(?int $c): array|null {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			$stmt_getallusers = $con->prepare("SELECT * FROM `assets` WHERE `asset_type` = ?");
			$stmt_getallusers->bind_param('i', $c);
			$stmt_getallusers->execute();
			$result = $stmt_getallusers->get_result();
			$result_array = array();

			if($result->num_rows != 0) {
				while($row = $result->fetch_assoc()) {
					if(User::FromID($row['asset_creator']) != null) {
						if($row['asset_type'] == Asset::TSHIRT || 
							$row['asset_type'] == Asset::HAT || 
							$row['asset_type'] == Asset::SHIRT || 
							$row['asset_type'] == Asset::PANTS) {
							array_push($result_array, new BuyableAsset($row));
						} else if($row['asset_type'] == Asset::PLACE) {
							array_push($result_array, new Place($row));
						} else {
							array_push($result_array, new Asset($row));
						}
					}
				}
				return $result_array;
			}
			return [];
		}

		public static function GetAllAssetsByName(?int $c, ?string $name, ?string $time, ?string $sort): array|null {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			if($time == "AllTime") {
				$query_time = "";
			} else if($time == "PastMonth") {
				$query_time = "AND `asset_lastupdate` >= NOW() - INTERVAL 1 MONTH";
			} else if($time == "PastWeek") {
				$query_time = "AND `asset_lastupdate` >= NOW() - INTERVAL 1 WEEK";
			} else if($time == "PastDay") {
				$query_time = "AND `asset_lastupdate` >= NOW() - INTERVAL 1 DAY";
			} else if($time == "PastHour") {
				$query_time = "AND `asset_lastupdate` >= NOW() - INTERVAL 1 HOUR";
			}

			if($sort == "TopFavorites") {
				$query_sort = "AND `asset_status` = 0 ORDER BY `asset_favcount` DESC";
			} else if($sort == "BestSelling") {
				$query_sort = "AND `asset_onsale` = 1 AND `asset_status` = 0 ORDER BY `asset_salecount` DESC";
			} else if($sort == "ForSale") {
				$query_sort = "AND `asset_onsale` = 1 AND `asset_status` = 0 ORDER BY `asset_lastupdate` DESC";
			} else if($sort == "RecentlyUpdated" || $sort == "PublicDomain") { 
				$query_sort = "ORDER BY `asset_lastupdate` DESC";
			} else {
				$query_sort = "";
			}
			$stmt_getallusers = $con->prepare("SELECT * FROM `assets` WHERE `asset_type` = ? AND `asset_name` LIKE ? $query_time $query_sort");
			$page = (($pagenum-1)*$count);
			$p_name = "%".$name."%";
			$stmt_getallusers->bind_param('is', $c, $p_name);
			$stmt_getallusers->execute();
			$result = $stmt_getallusers->get_result();
			$result_array = array();

			if($result->num_rows != 0) {
				while($row = $result->fetch_assoc()) {
					if(User::FromID($row['asset_creator']) != null) {
						if($row['asset_type'] == Asset::TSHIRT || 
							$row['asset_type'] == Asset::HAT || 
							$row['asset_type'] == Asset::SHIRT || 
							$row['asset_type'] == Asset::PANTS) {
							array_push($result_array, new BuyableAsset($row));
						} else if($row['asset_type'] == Asset::PLACE) {
							array_push($result_array, new Place($row));
						} else {
							array_push($result_array, new Asset($row));
						}
					}
					
				}
				return $result_array;
			} else {
				if($time == "AllTime") {
					return [];
				} else {
					return self::GetAllAssetsByName($c, str_replace("%", "", $name), "AllTime", $sort);
				}
				
			}
		}

		public static function GetAssetsPagedByName(?int $c, ?string $name,  ?int $pagenum, ?int $count, ?string $time, ?string $sort): array|null {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			if($time == "AllTime") {
				$query_time = "";
			} else if($time == "PastMonth") {
				$query_time = "AND `asset_lastupdate` >= NOW() - INTERVAL 1 MONTH";
			} else if($time == "PastWeek") {
				$query_time = "AND `asset_lastupdate` >= NOW() - INTERVAL 1 WEEK";
			} else if($time == "PastDay") {
				$query_time = "AND `asset_lastupdate` >= NOW() - INTERVAL 1 DAY";
			} else if($time == "PastHour") {
				$query_time = "AND `asset_lastupdate` >= NOW() - INTERVAL 1 HOUR";
			}
			if($sort == "TopFavorites") {
				$query_sort = "AND `asset_status` = 0 ORDER BY `asset_favcount` DESC";
			} else if($sort == "BestSelling") {
				$query_sort = "AND `asset_onsale` = 1 AND `asset_status` = 0 ORDER BY `asset_salecount` DESC";
			} else if($sort == "ForSale") {
				$query_sort = "AND `asset_onsale` = 1 AND `asset_status` = 0 ORDER BY `asset_lastupdate` DESC";
			} else if($sort == "RecentlyUpdated" || $sort == "PublicDomain") { 
				$query_sort = "ORDER BY `asset_lastupdate` DESC";
			} else if($sort == "MostPopular") {
				$query_sort = "ORDER BY `place_playercount` DESC, `place_visitcount` DESC";
			} else {
				$query_sort = "";
			}
			$stmt_getallusers = $con->prepare("SELECT * FROM `assets` WHERE `asset_type` = ? $query_time AND `asset_name` LIKE ? $query_time $query_sort LIMIT ?, ?");
			$page = (($pagenum-1)*$count);
			$p_name = "%".$name."%";
			$stmt_getallusers->bind_param('isii', $c, $p_name, $page, $count);
			$stmt_getallusers->execute();
			$result = $stmt_getallusers->get_result();
			$result_array = array();

			if($result->num_rows != 0) {
				while($row = $result->fetch_assoc()) {
					if(User::FromID($row['asset_creator']) != null) {
						if($row['asset_type'] == Asset::TSHIRT || 
							$row['asset_type'] == Asset::HAT || 
							$row['asset_type'] == Asset::SHIRT || 
							$row['asset_type'] == Asset::PANTS) {
							array_push($result_array, new BuyableAsset($row));
						} else if($row['asset_type'] == Asset::PLACE) {
							array_push($result_array, new Place($row));
						} else {
							array_push($result_array, new Asset($row));
						}
					}
				}
				return $result_array;
			} else {
				if($time == "AllTime") {
					return [];
				} else {
					return self::GetAssetsPagedByName($c, str_replace("%", "", $name), $pagenum, $count, "AllTime", $sort);
				}
			}
		}

		public static function GetAssetsPaged(?int $c, ?int $pagenum, ?int $count): array|null {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			$stmt_getallusers = $con->prepare("SELECT * FROM `assets` WHERE `asset_type` = ? ORDER BY `asset_lastupdate` LIMIT ?, ?");
			$page = (($pagenum-1)*$count);
			$stmt_getallusers->bind_param('iii', $c, $page, $count);
			$stmt_getallusers->execute();
			$result = $stmt_getallusers->get_result();
			$result_array = array();

			if($result->num_rows != 0) {
				while($row = $result->fetch_assoc()) {
					if(User::FromID($row['asset_creator']) != null) {
						if($row['asset_type'] == Asset::TSHIRT || 
							$row['asset_type'] == Asset::HAT || 
							$row['asset_type'] == Asset::SHIRT || 
							$row['asset_type'] == Asset::PANTS) {
							array_push($result_array, new BuyableAsset($row));
						} else if($row['asset_type'] == Asset::PLACE) {
							array_push($result_array, new Place($row));
						} else {
							array_push($result_array, new Asset($row));
						}
					}
				}
				return $result_array;
			}
			return [];
		}

		//IsItemFavourited
		public static function IsItemFavourited(?int $asset_id) {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
			$user = UserUtils::GetLoggedInUser();
			if($user != null) {
				$stmt_getasset = $con->prepare("SELECT * FROM `favourites` WHERE `fav_assetid` = ? AND `fav_userid` = ?");
				$stmt_getasset->bind_param('ii', $asset_id, $user->id);
				$stmt_getasset->execute();
				$result = $stmt_getasset->get_result();
				return $result->num_rows != 0;
			}
		}
	}
?>
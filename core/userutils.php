<?php

	include_once 'classes/user.php';

	include_once 'assetutils.php';

	class UserUtils {
		private static function getRandomString($length): string {
			$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$randomString = '';
			
			for ($i = 0; $i < $length; $i++) {
				$index = rand(0, strlen($characters) - 1);
				$randomString .= $characters[$index];
			}
	
			return $randomString;
		}
	
		public static function GetSecurity($length = 255): string {
			$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_?-/=;#!';
			$randomString = '';
			
			for ($i = 0; $i < $length; $i++) {
				$index = rand(0, strlen($characters) - 1);
				$randomString .= $characters[$index];
			}
	
			return $randomString;
		}

		public static function SetUserSessionStuff(?User $user) {
			if (session_status() === PHP_SESSION_NONE) {
				session_start();
			}
			if(!isset($_SESSION['LOGIN_USER_DETAIL_ID'])) {
				if($user != null) {
					$_SESSION['LOGIN_USER_DETAIL_ID'] = $user->GetSecurity();
				}
			}
		}
	
		public static function SetUserCookies($key) {
			setcookie(".GAMMASECURITY", $key, time() + (460800* 30), "/", $_SERVER['SERVER_NAME']);
		}
	
		public static function VerifyUserFromDetailsNoCookies($username, $password, $cookies = false): User|null {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			$stmt_user_check = $con->prepare('SELECT * FROM users WHERE `username` = ? OR (`username` = ? AND `password` = ?)');
			$pass = urldecode($password);
			$stmt_user_check->bind_param('sss', $username, $username, $pass);
			$stmt_user_check->execute();
			$result = $stmt_user_check->get_result();
			$num_rows = $result->num_rows;
			$return_user = null;
			
			if($num_rows == 1) {
				$row_data = $result->fetch_assoc();
				$query_user = new User($row_data);
				$query_banned = $row_data['banned'];
				$query_admin = $row_data['admin'];

				$stmt_user_status_check = $con->prepare('SELECT * FROM `activity` WHERE `userid` = ? AND `action_time` > DATE_SUB(NOW(),INTERVAL 15 MINUTE)');
				$stmt_user_status_check->bind_param('i', $query_user->id);
				$stmt_user_status_check->execute();
				$query_status = $stmt_user_status_check->get_result()->num_rows == 1;

				
				if(strcmp(trim($password), $query_user->GetPassword()) == 0 ||
					strcmp(trim(urldecode($password)), $query_user->GetPassword()) == 0 ||
					password_verify(urldecode($password), $query_user->GetPassword()) ||
					password_verify($password, $query_user->GetPassword())) {
				
					$return_user = $query_user;
					if($query_user->GetSecurity() == null || $query_user->GetSecurity() == "") {
						$stmt_user_check = $con->prepare('UPDATE `users` SET `security_key` = ? WHERE `id` = ?');
						$security = self::GetSecurity();
						$stmt_user_check->bind_param('si', $security, $query_user->id);
						$stmt_user_check->execute();
					}

					if(!$query_banned) {
						self::SetUserSessionStuff($query_user);
						if($cookies) {
							self::SetUserCookies($return_user->GetSecurity());
						}
					}
				}
			}
	
			return $return_user;
		}

		public static function LockOutUserIfNotLoggedIn() {
			if(self::GetLoggedInUser() == null) {
				die(include($_SERVER["DOCUMENT_ROOT"]."/core/login.php"));
			}
		}

		public static function VerifyUserFromSecurity($security_key): User|null {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			
			$stmt_user_check = $con->prepare('SELECT * FROM `users` WHERE `security_key` = ?');
			$stmt_user_check->bind_param('s', $security_key);
			$stmt_user_check->execute();
			$result = $stmt_user_check->get_result();
			$num_rows = $result->num_rows;
			if($num_rows == 1) {
				return new User($result->fetch_assoc());
			}
	
			return null;
		}

		public static function VerifyUserFromDetails($username, $password): User|null {
			return self::VerifyUserFromDetailsNoCookies($username, $password, true);
		}
		
		public static function VerifyUserFromCookies(): User|null {
			$failcheck = true;
			$user = null;
			;
			
			if(isset($_COOKIE['_GAMMASECURITY'])) {
				$security_key = urldecode($_COOKIE['_GAMMASECURITY']);

				$user = self::VerifyUserFromSecurity($security_key);

				if($user != null) {
					$failcheck = false;
				}
				
				if($failcheck) {
					self::LogoutUser();
					self::RemoveUserCookies();
				} 
			}
			return $user;
		}
		
		public static function GetUserAppearance($userid): string {
			$user = User::FromID($userid);
			if($user != null) {
				include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
				$stmt_getappearance = $con->prepare('SELECT * FROM `inventory` WHERE `userid` = ?');
				$stmt_getappearance->bind_param('i', $userid);
				$stmt_getappearance->execute();

				$result = "http://gamma.lambda.cam/Asset/BodyColors.ashx?userID=$userid;";

				$res = $stmt_getappearance->get_result();
				if($res->num_rows == 0) {
					$stmt_insertappearance = $con->prepare('INSERT INTO `inventory`(`userid`) VALUES (?)');
					$stmt_insertappearance->bind_param('i', $userid);
					$stmt_insertappearance->execute();
				} else {
					
					$row = $res->fetch_assoc();
					$assets = [];
					if($row['hat'] != null && $row['hat'] != "0") {
						array_push($assets, $row['hat']);
					}
					if($row['tshirt'] != null && $row['tshirt'] != "0") {
						array_push($assets, $row['tshirt']);
					}
					if($row['shirt'] != null && $row['shirt'] != "0") {
						array_push($assets, $row['shirt']);
					}
					if($row['pants'] != null && $row['pants'] != "0") {
						array_push($assets, $row['pants']);
					}
					foreach($assets as $id) {
						$result .= "http://gamma.lambda.cam/asset/?id=$id;";
					}
				}
				if(str_ends_with($result, ";")) {
					$result = substr($result, 0, strlen($result)-1);
				}
				return $result;
			}

			return "";
		}

		public static function GetUserAppearanceHashed($userid) {
			$user_exists = User::Exists($userid);
			if($user_exists) {
				include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
				$stmt_getappearance = $con->prepare('SELECT * FROM `inventory` WHERE `userid` = ?');
				$stmt_getappearance->bind_param('i', $userid);
				$stmt_getappearance->execute();

				$result = self::GetBodyColorsXML($userid)."\n";

				$res = $stmt_getappearance->get_result();
				if($res->num_rows == 0) {
					$stmt_insertappearance = $con->prepare('INSERT INTO `inventory`(`userid`) VALUES (?)');
					$stmt_insertappearance->bind_param('i', $userid);
					$stmt_insertappearance->execute();
				} else {
					
					$row = $res->fetch_assoc();
					$assets = [];
					if($row['hat'] != null && $row['hat'] != "0") {
						array_push($assets, $row['hat']);
					}
					if($row['tshirt'] != null && $row['tshirt'] != "0") {
						array_push($assets, $row['tshirt']);
					}
					if($row['shirt'] != null && $row['shirt'] != "0") {
						array_push($assets, $row['shirt']);
					}
					if($row['pants'] != null && $row['pants'] != "0") {
						array_push($assets, $row['pants']);
					}
					foreach($assets as $id) {
						$result .= "http://gamma.lambda.cam/asset/?id=$id;";
					}
				}
				if(str_ends_with($result, ";")) {
					$result = substr($result, 0, strlen($result)-1);
				}
				return md5($result);
			}
			return "null";
		}
  		
		public static function GetLoggedInUser(): User|null {
			self::RemoveLegacyUserCookies();
			if (session_status() === PHP_SESSION_NONE) {
				session_start();
			}

			if(isset($_POST['__EVENTTARGET']) && $_POST['__EVENTTARGET'] == 'ctl00$lsLoginStatus$ctl00') {
				self::LogoutUser();
				header("Location: /Default.aspx");
				return null;
			}

			$user = self::VerifyUserFromCookies();
			
			if(isset($_SESSION['LOGIN_USER_DETAIL_ID']) || $user != null) {
				if($user != null) {
					$retrieved_user = $user;
				} else {
					$retrieved_user = User::GetUserFromKey($_SESSION['LOGIN_USER_DETAIL_ID']);
					if($retrieved_user == null) {
						self::LogoutUser();
						self::RemoveUserCookies();
						self::RemoveLegacyUserCookies();
					}
				}
				
				
				if($retrieved_user->IsBanned()) {
					self::LogoutUser();
					self::RemoveUserCookies();
					die(header("Location: /NotApproved.aspx?id=".$retrieved_user->id));
				}

				include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";

				$stmt = $con->prepare('SELECT * FROM `friends` WHERE (`sender` = ? OR `reciever` = ?) AND `status` = 1');
				$stmt->bind_param('ii', $retrieved_user->id, $retrieved_user->id);
				$stmt->execute();
				$friend_result = $stmt->get_result();
				$friend_count = $friend_result->num_rows;

				if($friend_count >= 20 && !$retrieved_user->HasBadge(User::BADGE_FRIENDSHIP)) {
					$retrieved_user->GiveBadge(User::BADGE_FRIENDSHIP);
				}

				
				require_once $_SERVER["DOCUMENT_ROOT"]."/core/transactionutils.php";
				$stmt_getuser = $con->prepare("SELECT * FROM `subscriptions` WHERE `userid` = ?");
				$stmt_getuser->bind_param('i', $retrieved_user->id);
				$stmt_getuser->execute();
				$result = $stmt_getuser->get_result();


				if($result->num_rows == 1) {
					$user = $result->fetch_assoc();

					$stmt_user_status_check = $con->prepare('SELECT * FROM `subscriptions` WHERE `userid` = ? AND `lastpaytime` > DATE_SUB(NOW(),INTERVAL 1 DAY)');
					$stmt_user_status_check->bind_param('i', $retrieved_user->id);
					$stmt_user_status_check->execute();
					$activity_result = $stmt_user_status_check->get_result();
					if($activity_result->num_rows == 0) {
						$stmt_user_status_check = $con->prepare('UPDATE `subscriptions` SET `lastpaytime` = now() WHERE `userid` = ?');
						$stmt_user_status_check->bind_param('i', $retrieved_user->id);
						$stmt_user_status_check->execute();
						TransactionUtils::GiftTicketsToUser($retrieved_user->id, 10);
					}
					$query_status = $activity_result->num_rows == 1;
					$activity = $activity_result->fetch_assoc();
				} else {
					$stmt_user_status_check = $con->prepare('INSERT INTO `subscriptions`(`userid`) VALUES (?)');
					$stmt_user_status_check->bind_param('i', $retrieved_user->id);
					$stmt_user_status_check->execute();
					TransactionUtils::GiftTicketsToUser($retrieved_user->id, 10);

				}
				
				return $retrieved_user;
			}
			
			return null;
		}

		public static function LogoutUser() {
			if (session_status() === PHP_SESSION_NONE) {
				session_start();
			}
			self::RemoveUserCookies();

			unset($_SESSION['LOGIN_USER_DETAIL_ID']);
			session_destroy();
		}

		private static function RemoveUserCookies()  {
			unset($_COOKIE['_GAMMASECURITY']);
			$domain = $_SERVER['SERVER_NAME'];
			setcookie(".GAMMASECURITY", "", time()- 36000, "/", $domain);
		}

		private static function RemoveLegacyUserCookies()  {
			unset($_COOKIE['gmablx_userpasscookie']);
			unset($_COOKIE['gmablx_passpasscookie']);
			$domain = $_SERVER['SERVER_NAME'];
			setcookie("gmablx_passpasscookie", "", time()- 36000, "/", $domain);
			setcookie("gmablx_userpasscookie", "", time()- 36000, "/", $domain);
		}


		public static function GetAllUsersPaged(?int $pagenum, ?int $count, ?string $query = ""): array|null {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			$queryfiltered = "%$query%";

			$banned_line = "AND `banned` = 0";
			
			if(strlen($query) > 2) {
				$banned_line = "";
			}

			$stmt_getallusers = $con->prepare("SELECT * FROM `users` WHERE `username` LIKE ? $banned_line ORDER BY `joindate` DESC LIMIT ?, ?");
			$page = (($pagenum-1)*$count);
			
			$stmt_getallusers->bind_param('sii', $queryfiltered, $page, $count);
			$stmt_getallusers->execute();
			$result = $stmt_getallusers->get_result();
			$result_array = [];

			if($result->num_rows != 0) {
				while($row = $result->fetch_assoc()) {
					array_push($result_array, new User($row));
				}
				return $result_array;
			}
			return [];
		}

		public static function GetAllUsers(?string $query = ""): array|null {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			$queryfiltered = "%$query%";
			$stmt_getallusers = $con->prepare("SELECT * FROM `users` WHERE `username` LIKE ?");
			$stmt_getallusers->bind_param('s', $queryfiltered);
			$stmt_getallusers->execute();
			$result = $stmt_getallusers->get_result();
			$result_array = [];

			if($result->num_rows != 0) {
				while($row = $result->fetch_assoc()) {
					array_push($result_array, new User($row));
				}
				return $result_array;
			}
			return [];
		}

		public static function IsInviteKeyValid(?string $invite_key): bool {
			if($invite_key == null) {
				return false;
			}
			if(empty(trim($invite_key))) {
				return false;
			}
			if(strlen(trim($invite_key)) != 36 ) {
				return false;
			}
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			$stmt_check_key = $con->prepare('SELECT * FROM invite_keys WHERE inv_key = ?');
			$stmt_check_key->bind_param('s', $invite_key);
			$stmt_check_key->execute();
			$stmt_check_key->store_result();
			return $stmt_check_key->num_rows != 0;
		}

		/**
		 * Track user activity (aka set current time when they entered new page)
		 * @param mixed $action What action took place?
		 * @return void
		 */
		public static function RegisterAction(?string $action = "Website"): void {
			$reg_user = self::GetLoggedInUser();
			if($reg_user != null) {
				include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
				// Check if row exists
				$stmt_check_row = $con->prepare('SELECT * FROM `activity` WHERE `userid` = ?');
				$stmt_check_row->bind_param('i', $reg_user->id);
				$stmt_check_row->execute();
				$stmt_check_row->store_result();

				// If it doesn't then create one
				if($stmt_check_row->num_rows == 0) {
					$stmt_insert_row = $con->prepare('INSERT INTO `activity`(`userid`, `action`, `action_time`) VALUES (?, ?, now())');
					$stmt_insert_row->bind_param('is', $reg_user->id, $action);
					$stmt_insert_row->execute();
				} else {
					// Else, Update row
					$stmt_update_row = $con->prepare('UPDATE `activity` SET `action` = ?,`action_time` = now() WHERE `userid` = ?');
					$stmt_update_row->bind_param('si', $action, $reg_user->id);
					$stmt_update_row->execute();
				}
			}
		}

		public static function GetBodyColorsXML($user_id) {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";

			if($user_id <= 0) {
				$head = 24;
				$torso = 23;
				$left_arm = 24;
				$right_arm = 24;
				$left_leg = 119;
				$right_leg = 119;
			} else {
				$user_exists = User::Exists($user_id);
				if(!$user_exists) {
					die();
				} else {
					$stmt_getbodycolors = $con->prepare('SELECT * FROM `bodycolors` WHERE `userid` = ?');
					$stmt_getbodycolors->bind_param('i', $user_id);
					$stmt_getbodycolors->execute();

					$res = $stmt_getbodycolors->get_result();
					if($res->num_rows == 0) {
						$stmt_insertbodycolor = $con->prepare('INSERT INTO `bodycolors`(`userid`) VALUES (?)');
						$stmt_insertbodycolor->bind_param('i', $user_id);
						$stmt_insertbodycolor->execute();

						$head = 24;
						$torso = 23;
						$left_arm = 24;
						$right_arm = 24;
						$left_leg = 119;
						$right_leg = 119;
					} else {
						$row = $res->fetch_assoc();

						$head = $row['head'];
						$torso = $row['torso'];
						$left_arm = $row['leftarm'];
						$right_arm = $row['rightarm'];
						$left_leg = $row['leftleg'];
						$right_leg = $row['rightleg'];
					}
				}
			}

			$domain = $_SERVER['SERVER_NAME'];
			header("Content-Type: text/plain");

			$body_colors = <<<EOT
			<roblox xmlns:xmime="http://www.w3.org/2005/05/xmlmime" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="http://www.roblox.com/roblox.xsd" version="4">
				<External>null</External>
				<External>nil</External>
				<Item class="BodyColors" referent="RBX0">
					<Properties>
						<int name="HeadColor">$head</int>
						<int name="LeftArmColor">$left_arm</int>
						<int name="LeftLegColor">$left_leg</int>
						<string name="Name">Body Colors</string>
						<int name="RightArmColor">$right_arm</int>
						<int name="RightLegColor">$right_leg</int>
						<int name="TorsoColor">$torso</int>
						<bool name="archivable">true</bool>
					</Properties>
				</Item>
			</roblox>
			EOT;

			return $body_colors;
		}
	}

?>

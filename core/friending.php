<?php

	class FriendUtils {
		/**
		 * Adds a new relationship or accept an existing relation based on the users given as parameter.
		 * If the user already has sent a request it will do nothing.
		 * @param mixed $sending The user sending the request
		 * @param mixed $recieving The user recieving the request
		 * @return void
		 */
		public static function addFriend(?int $sending, ?int $recieving) {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			if($sending != $recieving) {
				if(self::checkExistingRelationships($sending, $recieving) == 0) {
					$stmt_sendmessage = $con->prepare("INSERT INTO `friends`(`sender`, `reciever`, `status`) VALUES (?, ?, 0)");
					$stmt_sendmessage->bind_param("ii", $sending, $recieving);
					$stmt_sendmessage->execute();
				} else {
					$stmt_getrelation = $con->prepare("SELECT * FROM `friends` WHERE (`sender` = ? AND `reciever` = ?) OR (`sender` = ? AND `reciever` = ?)");
					$stmt_getrelation->bind_param("iiii", $sending, $recieving, $recieving, $sending);
					$stmt_getrelation->execute();
					$result = $stmt_getrelation->get_result();
					$relation = $result->fetch_assoc();
					if($relation['reciever'] == $sending && $relation['status'] == 0) {
						$stmt_updaterelation = $con->prepare("UPDATE `friends` SET `status` = 1 WHERE `sender` = ? AND `reciever` = ?");
						$stmt_updaterelation->bind_param("ii", $recieving, $sending);
						$stmt_updaterelation->execute();
					} else {
						// nothing because like, ??? why accept your own request
					} 
				}
			}
			
		}

		/**
		 * Remove the request / relationship (so mean)
		 * @param mixed $user1 User wanting to remove the bond
		 * @param mixed $user2 User to remove the bond from
		 * @return void Nothing because like you are removing do you want null?
		 */
		public static function removeFriend(?int $user1, ?int $user2): void {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			$stmt_removerelation = $con->prepare("DELETE FROM `friends` WHERE (`sender` = ? AND `reciever` = ? ) OR (`sender` = ? AND `reciever` = ?)");
			$stmt_removerelation->bind_param("iiii", $user1, $user2, $user2, $user1);
			$stmt_removerelation->execute();
		}
		
		/**
		 * This function serves to return all the pairing rows that the two users share
		 * @param int $u1 User 1
		 * @param int $u2 User 2
		 * @param int $status 1 = accepted and 0 = waiting/pending
		 * @return int The number of rows
		 */
		public static function checkExistingRelationshipsByStatus(?int $u1, ?int $u2, ?int $status): int {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			$stmt_getrelations = $con->prepare("SELECT * FROM `friends` WHERE ((`sender` = ? AND `reciever` = ?) OR (`sender` = ? AND `reciever` = ?)) AND `status` = ?");
			$stmt_getrelations->bind_param("iiiii", $u1, $u2, $u2, $u1, $status);
			$stmt_getrelations->execute();
			return $stmt_getrelations->get_result()->num_rows;
		}

		/**
		 * This function serves to return all the pairing rows that the two users share
		 * @param int $u1 User 1
		 * @param int $u2 User 2
		 * @return int The number of rows
		 */
		public static function checkExistingRelationships(?int $u1, ?int $u2): int {
			return self::checkExistingRelationshipsByStatus($u1, $u2, 1) + self::checkExistingRelationshipsByStatus($u1, $u2, 0);
		}

		public static function isUserFriendsWith(?int $u1, ?int $u2):bool {
            return self::checkExistingRelationshipsByStatus($u1, $u2, 1) == 1;
		}

		public static function getStatusOfFriendship(?int $u1, ?int $u2) {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			$stmt_getrelations = $con->prepare("SELECT * FROM `friends` WHERE (`sender` = ? AND `reciever` = ?) OR (`sender` = ? AND `reciever` = ?)");
			$stmt_getrelations->bind_param("iiii", $u1, $u2, $u2, $u1);
			$stmt_getrelations->execute();
			$result = $stmt_getrelations->get_result();
			if($result->num_rows == 0) {
				return -1;
			}
			return $result->fetch_assoc()['status'];
		}

        public static function RecievingRequest(?int $u1, ?int $u2) {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			
            $stmt_getrelations = $con->prepare("SELECT * FROM `friends` WHERE `reciever` = ? AND `sender` = ? AND `status` = 0");
			$stmt_getrelations->bind_param("ii", $u1, $u2);
			$stmt_getrelations->execute();
			return $stmt_getrelations->get_result()->num_rows != 0;
		}
	}
?>
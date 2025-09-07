<?php
	if(!isset($_GET['code'])) {
		http_response_code(403);
		die("Request invalid!");
	} else {
		if(isset($_GET['code']) && $_GET['code'] != "3544bd46-a09e-4e9f-9f4a-8cb6821ad356") {
			http_response_code(403);
			die("Request invalid!");
		} else {
			require_once $_SERVER['DOCUMENT_ROOT'].'/core/classes/user.php';
			require_once $_SERVER['DOCUMENT_ROOT'].'/core/classes/asset.php';
			require_once $_SERVER['DOCUMENT_ROOT'].'/core/classes/asset/place.php';
			if(isset($_GET['TypeID'])) {
				$type = intval($_GET['TypeID']);
				if(isset($_GET['UserID']) && isset($_GET['AssociatedUserID']) && isset($_GET['AssociatedPlaceID'])) {
					if($type == 15) {
						$killer_id = intval($_GET['UserID']);
						$victim_id = intval($_GET['AssociatedUserID']);
						$place_id = intval($_GET['AssociatedPlaceID']);
	
						$killer = User::FromID($killer_id);
						$victim = User::FromID($victim_id);
						$place = Asset::FromID($place_id);
	
						if($killer != null && $victim != null && $place != null) {
							include $_SERVER['DOCUMENT_ROOT'].'/core/connection.php';
							$stmt = $con->prepare('INSERT INTO `kills`(`killer`, `victim`, `place`) VALUES (?, ?, ?);');
							$stmt->bind_param('iii', $killer_id, $victim_id, $place_id);
							$stmt->execute();
	
							if(!$killer->HasBadge(User::BADGE_COMBAT_INITIATION) && $killer->GetKillCount() >= 10) {
								$killer->GiveBadge(User::BADGE_COMBAT_INITIATION);
							}
	
							if(!$killer->HasBadge(User::BADGE_WARRIOR) && $killer->GetKillCount() >= 100) {
								$killer->GiveBadge(User::BADGE_WARRIOR);
							}
	
							if(!$killer->HasBadge(User::BADGE_BLOXXER) && $killer->GetKillCount() >= 250 && $killer->GetDeathCount() < $killer->GetKillCount()-20) {
								$killer->GiveBadge(User::BADGE_BLOXXER);
							}
	
							die();
						}
					} else if($type == 16) {
						$victim_id = intval($_GET['UserID']);
						$place_id = intval($_GET['AssociatedPlaceID']);

						$victim = User::FromID($victim_id);
						$place = Asset::FromID($place_id);
	
						if($victim != null && $place != null) {
							include $_SERVER['DOCUMENT_ROOT'].'/core/connection.php';
							$stmt = $con->prepare('INSERT INTO `kills`(`killer`, `victim`, `place`) VALUES (0, ?, ?);');
							$stmt->bind_param('ii', $victim_id, $place_id);
							$stmt->execute();

							die();
						}
					}
					

					
				}
				die("Request Invalid!");
			}
			// TypeID = 15
			// UserID = victorId 
			// AssociatedUserID = victim.userId
			// AssociatedPlaceID = placeId
		}
	}
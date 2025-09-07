<?php
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";

	if(isset($_GET['isuserloggedin'])) {
		header("content-type: text/plain");

		$user = UserUtils::GetLoggedInUser();

		die($user != null ? "true" : "false");
	} else if(isset($_GET['getloggedid'])) {
		header("content-type: text/plain");

		$user = UserUtils::GetLoggedInUser();

		die($user != null ? strval($user->id) : strval(-1));
	} else if(isset($_GET['getplayerinfo'])) {
		$id = intval($_GET['getplayerinfo']);
		if($id != 0) {
			$user = User::FromID($id);


			//unset($user['password']);
			//unset($user['last_online']);
			
			echo json_encode([
				"id" => $user->id,
				"username" => $user->name,
				"blurb" => $user->blurb,
				"characterhash" => UserUtils::GetUserAppearanceHashed($user->id)
			]);
			die(header("Content-Type: application/json"));
		}
	} else if(isset($_GET['updatechar'])) {
		require $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
		$user = UserUtils::GetLoggedInUser();

		$mediadir = $_SERVER['DOCUMENT_ROOT']."/../gamma-assets/player";

		if($user != null) {
			if(isset($_POST['color']) && isset($_POST['type'])) {
				$color = intval($_POST['color']);
				$type = $_POST['type'];
			}
			if($color != 0 && $type != null) {
				$stmt_updatebodycolor = $con->prepare("UPDATE `bodycolors` SET `$type` = ? WHERE `userid` = ?");
				$stmt_updatebodycolor->bind_param('ii', $color, $user->id);
				$stmt_updatebodycolor->execute();

				$md5hash = UserUtils::GetUserAppearanceHashed($user->id);
				if(!is_dir("$mediadir/$md5hash/")) {
					file_get_contents("http://localhost:64209/render?id=".$user->id."&type=character&data=".urlencode(UserUtils::GetUserAppearance($user->id))."&md5=".$md5hash, false);
				}
			}
		}
	}
?>
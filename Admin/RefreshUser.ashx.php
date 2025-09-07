<?php
	session_start();
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/assetutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";

	$user = UserUtils::GetLoggedInUser();

	if(!$user->IsAdmin()) {
		http_response_code(401);
		die("Not authorised");
	}


	$get_user = User::FromID(intval($_GET['userid']));
	if($get_user != null) {
		$md5hash = UserUtils::GetUserAppearanceHashed($get_user->id);
		file_get_contents("http://localhost:64209/render?id=".$get_user->id."&type=character&data=".urlencode(UserUtils::GetUserAppearance($get_user->id))."&md5=".$md5hash, false);
		echo "Done render";
	}

?>
<?php 
    require_once $_SERVER["DOCUMENT_ROOT"]."/core/assetutils.php";
    require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";

	$type = intval($_GET["type"] ?? "250");

	if(isset($_GET['id'])) {
		$id = intval($_GET['id']);
		$asset = AssetUtils::GetAsset($id);
		$user = UserUtils::GetLoggedInUser();
		if($asset != null) {
			$filename =  $_SERVER["DOCUMENT_ROOT"]."/../gamma-assets/thumbs/".$id."_".$type;

			if($asset->status == Asset::PENDING && ($user == null || ($user != null && !$user->IsAdmin()))) {
				$filename = $_SERVER["DOCUMENT_ROOT"]."/images/review-pending.png";
			}

			header("content-type: image/png");
			$handle = fopen($filename, "r"); 
			$contents = fread($handle, filesize($filename)); 
			fclose($handle);
			echo $contents;
		} else {
			die("id given was MID!");
		}
		
	} else {
		die("not id given");
	}
?>
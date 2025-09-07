<?php
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/assetutils.php";

	$domain = $_SERVER['SERVER_NAME'];
	$placeID = $_GET['placeID'] ?? "nil";
	$loadurl = "http://$domain/asset/?id=$placeID";

	$dontscript = false;
	$asset = null;
	$uploadurl = "";

	if($placeID == "nil") {
		$loadurl = "";
	} else {
		$asset = AssetUtils::GetAsset(intval($placeID));
		if($asset == null) {
			$dontscript = true;
		}
	}

	$user = UserUtils::GetLoggedInUser();

	$mode = $user != null ? "true" : "false";

	if($user != null) {
		$userID = $user->id;
		if($asset != null && $userID == $asset->creator->id) {
			$uploadurl = "http://$domain/Data/Upload.ashx?assetid=$placeID&type=Place&creds_id=$userID&creds_pw=".urlencode($user->GetPassword());
		}
	} else {
		$dontscript = true;
	}

	header("Content-Type: text/plain");
?>
<?php if(!$dontscript): ?>
visit = game:GetService("Visit")

workspace:SetPhysicsThrottleEnabled(true)

function doVisit()
	if <?= $mode ?> then
		game:Load("<?= $loadurl ?>")
		visit:SetUploadUrl("<?= $uploadurl ?>")
	end
	
	if <?= $mode ?> then
		visit:SetPing("", 300)
	end
end

success, err = pcall(doVisit)

if not success then
	print(err)
	if <?= $mode ?> then
		visit:SetUploadUrl("")
	end
	wait(5)
	message.Text = "Error on visit: " .. err
end
<?php endif ?>
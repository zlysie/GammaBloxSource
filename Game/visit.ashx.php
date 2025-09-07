<?php
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/gameutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/assetutils.php";

	$domain = $_SERVER['SERVER_NAME'];
	$placeID = $_GET['placeID'] ?? "nil";
	$load = "game:Load(\"http://$domain/asset/?id=$placeID\")";

	$asset = null;
	$uploadurl = "";

	if($placeID == "nil") {
		$load = "";
	} else {
		$asset = AssetUtils::GetAsset(intval($placeID));
	}

	$user = UserUtils::GetLoggedInUser();

	$mode = $user != null ? "true" : "false";
	if($placeID == "nil") {
		$mode = "false";
	}

	if($user != null) {
		$userID = $user->id;
		$userName = $user->name;

		if($asset != null && $userID == $asset->creator->id && $placeID != "nil") {
			$uploadurl = "http://$domain/Data/Upload.ashx?assetid=$placeID&type=Place";
		}

		if($_SERVER['HTTP_USER_AGENT'] == "Gamma") {
			GameUtils::CountVisit($placeID, $user->id);
		}	
	} else {
		$userID = 0;
		$userName = "Player";
	}

	
	header("Content-Type: text/plain");
?>
visit = game:GetService("Visit")

local message = Instance.new("Message")
message.Parent = workspace

workspace:SetPhysicsThrottleEnabled(true)

-- This code might move to C++
function characterRessurection(player)
	if player.Character then
		local humanoid = player.Character.Humanoid
		humanoid.Died:connect(function() wait(5) player:LoadCharacter() end)
	end
end
game:GetService("Players").PlayerAdded:connect(function(player)
	characterRessurection(player)
	player.Changed:connect(function(name)
		if name=="Character" then
			characterRessurection(player)
		end
	end)
end)

function doVisit()
	message.Text = "Loading Game"
	if <?= $mode ?> then
		<?= $load ?>
		visit:SetUploadUrl("<?= $uploadurl ?>")
	end
	
	message.Text = "Running"
	game:GetService("RunService"):Run()

	message.Text = "Creating Player"
	if <?= $mode ?> then
		player = game:GetService("Players"):CreateLocalPlayer(<?= $userID ?>)
		player.Name = [====[<?= $userName ?>]====]
	else
		player = game:GetService("Players"):CreateLocalPlayer(0)
	end
	player.CharacterAppearance = "<?= UserUtils::GetUserAppearance($userID) ?>"
	player:LoadCharacter()

	if player.Character:FindFirstChild("Clothing") and not player.Character:FindFirstChild("Shirt Graphic") then
		for _, v in pairs(player.Character:children()) do
			if v.className == "Part" then
				if v.Name == "Torso" then
					for i, j in pairs(v:children()) do
						if j.className == "Decal" then
							j:Remove()
						end
					end
				end
			end
		end
	end

	message.Text = "Setting GUI"
	player:SetUnder13(false)
	player:SetSuperSafeChat(false)
	
	if <?= $mode ?> then
		message.Text = "Setting Ping"
		visit:SetPing("", 300)

		message.Text = "Sending Stats"
	end
end

success, err = pcall(doVisit)

if success then
	message.Parent = nil
else
	print(err)
	if <?= $mode ?> then
		visit:SetUploadUrl("")
	end
	wait(5)
	message.Text = "Error on visit: " .. err
end
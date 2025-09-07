<?php
	session_start();
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/assetutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
	UserUtils::LockOutUserIfNotLoggedIn();

	// get logged in user
	$user = UserUtils::GetLoggedInUser();
	global $user;

    if($user != null && $user->IsBanned()) {
        die(header("Location: /Login/Default.aspx"));
    }

	if(!isset($_GET['ID'])) {
		die(header("Location: /User.aspx"));
	}

	$item_id = intval($_GET['ID']);
	$item = AssetUtils::GetAsset($item_id);
	if($item == null) {
		die(header("Location: /User.aspx"));
	}

	switch($item->type) {
		case Asset::TSHIRT:
			$category = "T-Shirt";
			break;
		case Asset::HAT:
			$category = "Hat";
			break;
		case Asset::PLACE:
			$category = "Place";
			break;
		case Asset::MODEL:
			$category = "Model";
			break;
		case Asset::SHIRT:
			$category = "Shirt";
			break;
		case Asset::PANTS:
			$category = "Pants";
			break;
		case Asset::DECAL:
			$category = "Decal";
			break;
	}

	$creator = $item->creator;

    if($creator->id != $user->id && !$user->IsAdmin()) {
        die(header("Location: /User.aspx"));
    }
	/*
		[ctl00$cphRoblox$rbxItem$Name] => test
		[ctl00$cphRoblox$rbxItem$Description] => test
		[ctl00$cphRoblox$rbxItem$AllowComments] => on
		[__EVENTTARGET] => ctl00$cphRoblox$rbxItem$Submit
	*/

	$blockedchars = array('𒐫', '‮', '﷽', '𒈙', '⸻ ', '꧅');
	
	

	if(isset($_POST['__EVENTTARGET']) && $_POST['__EVENTTARGET'] == 'ctl00$cphRoblox$rbxItem$ToggleOnSale') {
		if($item instanceof BuyableAsset) {
			$onsale = $item->onsale ? 0 : 1;
		} else {
			$onsale = 0;
		}
		
		if($item->type == Asset::PLACE || $item->type == Asset::MODEL) {
			$onsale = 0;
		}

		$stmt_updateasset = $con->prepare('UPDATE `assets` SET `asset_onsale` = ? WHERE `asset_id` = ?');
		$stmt_updateasset->bind_param('ii', $onsale, $item_id);
		$stmt_updateasset->execute();

		die(header("Location: /My/Item.aspx?ID=$item_id"));
	}

	if(isset($_POST['__EVENTTARGET']) && 
	$_POST['__EVENTTARGET'] == 'ctl00$cphRoblox$rbxItem$Submit' &&
	isset($_POST['ctl00$cphRoblox$rbxItem$Name']) && 
	isset($_POST['ctl00$cphRoblox$rbxItem$Description'])) {
		$item_name = $_POST['ctl00$cphRoblox$rbxItem$Name'];
		$item_name = trim(str_replace($blockedchars, '', $item_name));
		$item_name = substr($item_name, 0, 64);

		$item_desc = $_POST['ctl00$cphRoblox$rbxItem$Description'];
		$item_desc = trim(str_replace($blockedchars, '', $item_desc));
		$item_desc = substr($item_desc, 0, 512);

		$item_enablecomments = isset($_POST['ctl00$cphRoblox$rbxItem$AllowComments']) ? 1 : 0;
			
		if(strlen($item_name) != 0 && strlen($item_desc) != 0) {
			if(strcmp(strtolower(trim($item_name)), strtolower(trim($item->name))) != 0 ||
			strcmp(strtolower(trim($item_desc)), strtolower(trim($item->description))) != 0
			|| $item_enablecomments != $item->comments_enabled) {
				$stmt_updateasset = $con->prepare('UPDATE `assets` SET `asset_lastupdate` = now() WHERE `asset_id` = ?');
				$stmt_updateasset->bind_param('i', $item_id);
				$stmt_updateasset->execute();
			}

			if($item->type != Asset::PLACE) {
				$price_cost = 0;
				if($item instanceof BuyableAsset) {
					$price_cost = isset($_POST['pricetickets']) ? abs(intval($_POST['pricetickets'])) : $item->tux;
				}
				
				$stmt_updateasset = $con->prepare('UPDATE `assets` SET `asset_name` = ?,`asset_description` = ?, `asset_enablecomments` = ?, `asset_tixcost` = ? WHERE `asset_id` = ?');
				$stmt_updateasset->bind_param('ssiii', $item_name, $item_desc, $item_enablecomments, $price_cost, $item_id);
				$stmt_updateasset->execute();
			} else {
				if($item instanceof Place) {
					$place_access = $item->friends_only ? 1 : 0;
					if($_POST['ctl00$cphRoblox$rbxItem$PlaceAccess'] == "rbPrivateAccess") {
						$place_access = 1;
					} else if($_POST['ctl00$cphRoblox$rbxItem$PlaceAccess'] == "rbPublicAccess") {
						$place_access = 0;
					}
					$place_copylocked = isset($_POST['ctl00$cphRoblox$rbxItem$IsCopyProtected']) ? 1 : 0;

					$stmt_updateasset = $con->prepare('UPDATE `assets` SET `asset_name` = ?,`asset_description` = ?, `asset_enablecomments` = ?, `place_access` = ?, `place_copylocked` = ? WHERE `asset_id` = ?');
					$stmt_updateasset->bind_param('ssiiii', $item_name, $item_desc, $item_enablecomments, $place_access, $place_copylocked, $item_id);
					$stmt_updateasset->execute();
				}
			}
			

			die(header("Location: /Item.aspx?ID=$item_id"));
		} else {
			$errors = "";
			if(strlen($item_name) == 0) {
				$errors .= "Name field cannot be empty!<br>";
			}

			if(strlen($item_desc) == 0) {
				$errors .= "Description field cannot be empty!<br>";
			}
		}
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<func>
		<title>GAMMA - Edit Item</title>
		<link rel="stylesheet" type="text/css" href="/CSS/AllCSS.css">
		<link rel="Shortcut Icon" type="image/ico" href="/favicon.ico">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="author" content="Zlysie">
		<meta name="description" content="GAMMA is a FREE (invite only) casual virtual world with fully constructible/desctructible 3D environments and immersive physics. Build, battle, chat, or just hang out.">
		<meta name="keywords" content="game, video game, building game, construction game, online game, LEGO game, LEGO, MMO, MMORPG, gammablox, gamma roblox, old roblox">
		<meta name="robots" content="none">
		<script src="/js/WebResource.js" type="text/javascript"></script>
		<script src="/js/jquery.js" type="text/javascript"></script>
		<style>
			.Validators {
				color:red;
			}
		</style>
		<script>
			// this is purely a visual thing.
			// doesn't affect post data but does push the user to not put in letter using the blank spaces
			function updateTicketsData() {
				
				var input = parseInt($("#ticketsinput").val());
				console.log(input);
				if(!isNaN(input)) {
					$("#market_ticket").html("0");
					$("#earn_ticket").html(input);
				} else {
					$("#market_ticket").html("---");
					$("#earn_ticket").html("---");
				}
				
			}
		</script>
	</head>
	<body>
		<form name="aspnetForm" method="post" action="Item.aspx?ID=<?= $item_id ?>" id="aspnetForm">
			<div id="Container">
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_header.php"; ?>
				<div id="Body">
                    <div id="EditItem">
                        <div id="EditItemContainer">
					        <h2>Configure <?= $category ?></h2>
							<?php if(isset($errors)): ?>
							<div style="color:red;font-weight:bold">
								<?= $errors ?>
							</div>
							<?php endif ?>
							<div id="ItemName">
								<legend><b>Name:</b></legend>
								<input class="TextBox" name="ctl00$cphRoblox$rbxItem$Name" style="width:420px" value="<?= $item->name; ?>">
							</div>
							
							<?php if($item->type == 9): ?>
							<div id="ItemThumbnail">
								<img src="/thumbs/?id=<?= $item_id ?>&type=420" style="height:100%">
							</div>
							<?php endif ?>
							
							<div id="ItemDescription">
								<legend><b>Description:</b></legend>
<textarea class="MultilineTextBox" name="ctl00$cphRoblox$rbxItem$Description" style="max-height:125px;height:125px;min-height:125px;resize: none;width:420px">
<?= $item->description; ?>
</textarea>
							</div>

							<div class="Buttons">
								<button class="Button" onclick="__doPostBack('ctl00$cphRoblox$rbxItem$Submit','');">Update</button>
								<button class="Button" onclick="window.location.href='/Item.aspx?ID=<?= $item_id ?>'; return false;">Cancel</button>
							</div>

							<div id="Comments">
								<fieldset title="Turn comments on/off">
									<legend>Turn comments on/off</legend>
									<div class="Suggestion">
										Choose whether or not this item is open for comments.
									</div>
									<div class="EnableCommentsRow">
										<input type="checkbox" name="ctl00$cphRoblox$rbxItem$AllowComments" <?php if($item->comments_enabled): ?>checked="checked"<?php endif ?>><label>Allow Comments</label>
									</div>
								</fieldset>
							</div>
							<?php if($item instanceof Place): ?>
							<div id="PlaceAccess">
								<fieldset title="Access">
									<legend>Access</legend>
									<div class="Suggestion">
										This determines who can access your place.
									</div>
									<div class="PlaceAccessRow">
										<img id="ctl00_cphRoblox_iPublicAccess" src="/images/public.png" alt="Public" style="border-width:0px;">
										<input name="ctl00$cphRoblox$rbxItem$PlaceAccess" type="radio" name="PlaceAccess" value="rbPublicAccess" <?php if(!$item->friends_only): ?>checked="checked"<?php endif ?>>
										<label>Public: Anybody can visit my place</label><br>
										
										<img id="ctl00_cphRoblox_iPrivateAccess" src="/images/locked.png" alt="Friends-only" style="border-width:0px;">
										<input name="ctl00$cphRoblox$rbxItem$PlaceAccess" type="radio" name="PlaceAccess" value="rbPrivateAccess" <?php if($item->friends_only): ?>checked="checked"<?php endif ?>>
										<label>Friends: Only my friends can visit my place</label><br>
									</div>
								</fieldset>
							</div>

							<div id="PlaceCopyProtection" visible="True">
								<fieldset title="Copy Protection">
									<legend>Copy Protection</legend>
									<div class="Suggestion">Checking this will prevent your place from being copied but will also make it available to others only in online mode.</div>
									<div class="CopyProtectionRow">
										<input type="checkbox" name="ctl00$cphRoblox$rbxItem$IsCopyProtected" <?php if($item->copylocked): ?>checked="checked"<?php endif ?>>
										<label>Copy-Lock my place</label>
									</div>
								</fieldset>
							</div>
							<?php endif ?>
							<?php if($item->type == Asset::MODEL): ?>
								<?php
									$model = Place::FromID($item->id);
								?>
								<div id="PlaceAccess">
								<fieldset title="Access">
									<legend>Access</legend>
									<div class="Suggestion">
										This determines who can access your model.
									</div>
									<div class="PlaceAccessRow">
										<img id="ctl00_cphRoblox_iPublicAccess" src="/images/public.png" alt="Public" style="border-width:0px;">
										<input name="ctl00$cphRoblox$rbxItem$PlaceAccess" type="radio" name="PlaceAccess" value="rbPublicAccess" <?php if(!$model->friends_only): ?>checked="checked"<?php endif ?>>
										<label>Public: Anybody can use my model</label><br>
										
										<img id="ctl00_cphRoblox_iPrivateAccess" src="/images/locked.png" alt="Friends-only" style="border-width:0px;">
										<input name="ctl00$cphRoblox$rbxItem$PlaceAccess" type="radio" name="PlaceAccess" value="rbPrivateAccess" <?php if($model->friends_only): ?>checked="checked"<?php endif ?>>
										<label>Private: Only I can use my model</label><br>
									</div>
								</fieldset>
							</div>
							<?php endif ?>
							<?php if($item instanceof BuyableAsset): ?>
							<div id="SellThisItem">
								<fieldset title="Sell this Item">
									<legend>Sell this Item</legend>
									<div class="Suggestion">
										Check the box below and enter a price if you want to sell this item in the GAMMA catalog. 
										Uncheck the box to remove the item from the catalog.
									</div>
									
									<div class="SellThisItemRow">
										<input type="checkbox" onclick="__doPostBack('ctl00$cphRoblox$rbxItem$ToggleOnSale', '');" <?php if($item->onsale): ?>checked<?php endif ?>><label>Sell this Item</label>
										<?php if($item->onsale): ?>
										<div id="Pricing" style="width: 330px;margin: 0 auto;">
											<table>
												<tr>
													<td></td>
													<td></td>
												</tr>
												<tr>
													<td><b>Price:</b></td>
													<td id="Price" style="width: 100px;">
														Tux<input type="text" class="Textbox" name="pricetickets" id="ticketsinput" value="<?=  $item->tux ?>" onchange="updateTicketsData()">
													</td>
												</tr>
												<tr>
													<td><b>Marketplace Fee @ 10%:</b></td>
													<td>
														<label id="market_ticket">---</labe>
													</td>
												</tr>
												<tr>
													<td><b>You Earn:</b></td>
													<td>
														<label id="earn_ticket">---</labe>
													</td>
												</tr>
											</table>
										</div>
										<?php endif ?>
									</div>
									
								</fieldset>
							</div>
							<?php endif ?>

							<div class="Buttons">
								<button class="Button" onclick="__doPostBack('ctl00$cphRoblox$rbxItem$Submit','');">Update</button>
								<button class="Button" onclick="window.location.href='/Item.aspx?ID=<?= $item_id ?>'; return false;">Cancel</button>
							</div>
                        </div>
                    </div>
				</div>
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_footer.php"; ?>
			</div>
			<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/formvars.php"; ?>
		</form>
	</body>
</html>
<?php 
	unset($_SESSION['errors']);
?>
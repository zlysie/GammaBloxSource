<?php 

	// ContentBuilder:
	//   Uploads and processes images

	/**
	 * I should probably move this to some fileutils class or something.
	 * 
	 * Gets the MIMEType of file using magic numbers and not file extension
	 * @param mixed $file Path to file
	 * @return bool|string MIME Type
	 */
	function checkMimeType(?string $file): bool|string {
		$file_info = new finfo(FILEINFO_MIME_TYPE);
		return $file_info->buffer(file_get_contents($file));
	}

	function checkDecalSpace(?int $base_id) {
		$mediadir = $_SERVER['DOCUMENT_ROOT']."/../gamma-assets";
		return !file_exists($mediadir."/".$base_id) && !file_exists($mediadir."/".($base_id+1));
	}

	function checkHatSpace(?int $base_id) {
		$mediadir = $_SERVER['DOCUMENT_ROOT']."/../gamma-assets";
		return !file_exists($mediadir."/".$base_id) && !file_exists($mediadir."/".($base_id+1)) && !file_exists($mediadir."/".($base_id+2));
	}

	function readFromFile($file) {
		$mediadir = $_SERVER['DOCUMENT_ROOT']."/../gamma-assets";
		$handle = fopen("$mediadir/$file", "r"); 
		$contents = fread($handle, filesize("$mediadir/$file")); 
		fclose($handle);
		return $contents;
	}

	function readFromFileUpload($file) {
		$handle = fopen("$file", "r"); 
		$contents = fread($handle, filesize("$file")); 
		fclose($handle);
		return $contents;
	}

	session_start();
	
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/assetutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/transactionutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/rcclib.php";
	UserUtils::LockOutUserIfNotLoggedIn();
	
	$mediadir = $_SERVER['DOCUMENT_ROOT']."/../gamma-assets"; // to make the website non-platform specific

	// get logged in user
	$user = UserUtils::GetLoggedInUser();
	global $user;

	if(!isset($_GET['ContentType'])) { // redirect to image builder panel if no ContentType get was found
		die(header('Location: /My/ContentBuilder.aspx?ContentType=1'));
	}

	$content_type = intval($_GET['ContentType']); // get contenttype

	//if the contentType was not in specified range / number then redirect to image builder
	if(!($content_type == 1 || ($content_type >= 11 && $content_type <= 13) || $content_type == 2) && !$user->IsAdmin()) {
		die(header('Location: /My/ContentBuilder.aspx?ContentType=1'));
	}

	$_SESSION['errors'] = [];

	// blocked characters from past experience...
	$blockedchars = array('𒐫', '‮', '﷽', '𒈙', '⸻ ', '꧅');

	// if the user has uploaded something...
	if(isset($_FILES['CONTENTUploader']) && isset($_POST['CONTENTUploadButton'])) {
		// get current asset count
		$stmt_getcount = $con->prepare("SELECT `AUTO_INCREMENT` FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'gammablox' AND TABLE_NAME = 'assets';");
		$stmt_getcount->execute();
		$result = $stmt_getcount->get_result();
		$asset_id = $result->fetch_assoc()['AUTO_INCREMENT']; 
		
		// check if image is common
		$type = checkMimeType($_FILES['CONTENTUploader']['tmp_name']);
		if(strcmp($type, "image/png") == 0 || strcmp($type, "image/jpeg") == 0 || strcmp($type, "image/gif") == 0) {
			// load image into GD (as to process it and turn it into PNG)
			$original_image = imagecreatefromstring(file_get_contents($_FILES['CONTENTUploader']['tmp_name']));
			list($width, $height) = getimagesize($_FILES['CONTENTUploader']['tmp_name']);

			$image = imagecreatetruecolor($width, $height);
			$bga = imagecolorallocatealpha($image, 0, 0, 0, 127);
			imagefill($image, 0, 0, $bga);
			imagecopy($image, $original_image, 0, 0, 0, 0, $width, $height);
			imagesavealpha($image, true);

			if($content_type == Asset::DECAL) {
				// this basically makes it so the image is capped at 256 and scales to original aspect ratio
				if($width > $height) {
					$new_width = 256;
					$new_height = -1;
				} else if($width < $height) {
					$new_width = -1;
					$new_height = 256;
				} else {
					$new_width = 256;
					$new_height = 256;
				}
				
				// scales images to specified dimensions (no filtering yet :P)
				$resultimage = imagescale($image, $new_width, $new_height);
				$thumb250image = imagescale($image, 250, 250);
				$thumb120image = imagescale($image, 120, 120);

				// this tells the exporter to save the images as a transparent png (as it doesn't do it by default)
				imagesavealpha($resultimage, true);   // the actual decal
				imagesavealpha($thumb250image, true); // 250x250 thumbs
				imagesavealpha($thumb120image, true); // 120x120 thumbs

				while(!checkDecalSpace($asset_id)) {
					$asset_id += 1;
				}

				imagepng($resultimage, "$mediadir/$asset_id", 5);
				$pngdata = readFromFile($asset_id);
				$pngdata = "RELATEDID=".(intval($asset_id)+1)."\n" . $pngdata;
				file_put_contents("$mediadir/$asset_id", $pngdata);
				
				$asset_id += 1;
				imagepng($thumb250image, "$mediadir/thumbs/".$asset_id."_250", 5);
				imagepng($thumb120image, "$mediadir/thumbs/".$asset_id."_120", 5);
				$rbxm = readFromFile("decal.rbxm");
				$rbxm = str_replace("[[ID]]", $asset_id-1, $rbxm);
				file_put_contents("$mediadir/$asset_id", $rbxm);
				
				// insert asset into table
				$stmt_insertasset = $con->prepare('INSERT INTO `assets`(`asset_id`, `asset_name`, `asset_description`, `asset_creator`, `asset_type`) VALUES (?, ?, "Decal", ?, ?)');
				$name = pathinfo($_FILES['CONTENTUploader']['name'], PATHINFO_FILENAME);
				$name = str_replace($blockedchars, '', $name);
			
				$stmt_insertasset->bind_param('isii', $asset_id, $name, $user->id, $content_type);
				$stmt_insertasset->execute();

				$ta_id = TransactionUtils::GenerateID();
				$stmt_processtransaction = $con->prepare("INSERT INTO `transactions`(`ta_id`, `ta_userid`, `ta_currency`, `ta_cost`, `ta_asset`, `ta_assettype`, `ta_assetcreator`) VALUES (?, ?, 'tickets', 0, ?, ?, ?)");
				$stmt_processtransaction->bind_param('siiii', $ta_id, $user->id, $asset_id, $content_type, $user->id);
				$stmt_processtransaction->execute();
				
				die(header("Location: /Item.aspx?ID=".$asset_id));
				
			} else if($content_type == Asset::TSHIRT) {
				// this basically makes it so the image is capped at 128 and scales to original aspect ratio
				if($width > $height) {
					$new_width = 128;
					$new_height = -1;
				} else if($width < $height) {
					$new_width = -1;
					$new_height = 128;
				} else {
					$new_width = 128;
					$new_height = 128;
				}
				
				// calculate resized image
				$r_image = imagescale($image, $new_width, $new_height);
				// get size parameters of scaled image as for easier copying
				$r_width  = imagesx($r_image);
				$r_height = imagesy($r_image);
				
				// if the height is taller than the width then attempt to center it
				if($r_width < $r_height) {
					$dst_x = (128 - $r_width)/2;
				} else {
					$dst_x = 0;
				}
				
				// actual resized image
				// why do this?
				//    to make sure the image is resized with correct filtering as imagescale uses nearest neighbor for its scaling
				$resizedimage = imagecreatetruecolor(128, 128);
				$trans_colour = imagecolorallocatealpha($resizedimage, 0, 0, 0, 127);
				imagefill($resizedimage, 0, 0, $trans_colour);
				imagecopyresampled($resizedimage, $image, $dst_x, 0, 0, 0, $r_width, $r_height, $width, $height);
				
				
				// create tshirt THUMBNAIL image
				// create base image of size 420x420 with transparent background
				$tshirt = imagecreatetruecolor(420, 420);
				$trans_colour = imagecolorallocatealpha($tshirt, 0, 0, 0, 127);
				imagefill($tshirt, 0, 0, $trans_colour);
				
				// paste tshirt (the icon thing) into image
				$bg_tshirt = imagecreatefrompng($mediadir."/tshirt.png");
				imagecopy($tshirt, $bg_tshirt, 0, 0, 0, 0, 420, 420);
				// and paste the processed resizedimage on top of it
				imagecopyresampled($tshirt, $resizedimage, 84, 84, 0, 0, 252, 252, 128, 128);
				
				// we created that tshirt thumbnail image just for this
				$thumb250image = imagescale($tshirt, 250, 250);
				$thumb120image = imagescale($tshirt, 120, 120);
				
				// this tells the exporter to save the images as a transparent png (as it doesn't do it by default)
				imagesavealpha($resizedimage, true);  // the actual tshirt
				imagesavealpha($tshirt, true);        // thumbnail tshirt
				imagesavealpha($thumb250image, true); // 250x250 thumbs
				imagesavealpha($thumb120image, true); // 120x120 thumbs

				while(!checkDecalSpace($asset_id)) {
					$asset_id += 1;
				}

				// save images
				imagepng($resizedimage, "$mediadir/$asset_id", 5);

				// embed asset id into image asset as to make sure its able to be handled correctly by the asset deliverer (very hacky lol)
				$pngdata = readFromFile($asset_id);
				$pngdata = "RELATEDID=".(intval($asset_id)+1)."\n" . $pngdata;
				file_put_contents("$mediadir/$asset_id", $pngdata);
				
				// increase by one since this is all just the asset id we want to upload.
				$asset_id += 1;
				imagepng($thumb250image, "$mediadir/thumbs/".$asset_id."_250", 5);
				imagepng($thumb120image, "$mediadir/thumbs/".$asset_id."_120", 5);
				$rbxm = readFromFile("tshirt.rbxm");
				$rbxm = str_replace("[[ID]]", $asset_id-1, $rbxm);
				file_put_contents("$mediadir/$asset_id", $rbxm);
				
				// insert asset into table
				$stmt_insertasset = $con->prepare('INSERT INTO `assets`(`asset_id`, `asset_name`, `asset_description`, `asset_creator`, `asset_type`, `asset_onsale`) VALUES (?, ?, "T-Shirt", ?, ?, 1)');
				$name = pathinfo($_FILES['CONTENTUploader']['name'], PATHINFO_FILENAME);
				$name = str_replace($blockedchars, '', $name);
				$stmt_insertasset->bind_param('isii', $asset_id, $name, $user->id, $content_type);
				$stmt_insertasset->execute();

				$ta_id = TransactionUtils::GenerateID();
				$stmt_processtransaction = $con->prepare("INSERT INTO `transactions`(`ta_id`, `ta_userid`, `ta_currency`, `ta_cost`, `ta_asset`, `ta_assettype`, `ta_assetcreator`) VALUES (?, ?, 'tickets', 0, ?, ?, ?)");
				$stmt_processtransaction->bind_param('siiii', $ta_id, $user->id, $asset_id, $content_type, $user->id);
				$stmt_processtransaction->execute();

				die(header("Location: /Item.aspx?ID=".$asset_id));
			} else if($content_type == Asset::SHIRT) {
				if($width != 585 || $height != 559) {
					array_push($_SESSION['errors'], "Not a valid shirt image! (Incorrect size)");
				} else {
					// this tells the exporter to save the images as a transparent png (as it doesn't do it by default)
					imagesavealpha($image, true);  // the actual tshirt
									
					while(!checkDecalSpace($asset_id)) {
						$asset_id += 1;
					}

					// save images
					imagepng($image, "$mediadir/$asset_id", 5);

					// embed asset id into image asset as to make sure its able to be handled correctly by the asset deliverer (very hacky lol)
					$pngdata = readFromFile($asset_id);
					$pngdata = "RELATEDID=".(intval($asset_id)+1)."\n" . $pngdata;
					file_put_contents("$mediadir/$asset_id", $pngdata);

					// increase by one since this is all just the asset id we want to upload.
					$asset_id += 1;
					$rbxm = readFromFile("shirt.rbxm");
					$rbxm = str_replace("[[ID]]", $asset_id-1, $rbxm);
					file_put_contents("$mediadir/$asset_id", $rbxm);

					// insert asset into table
					$stmt_insertasset = $con->prepare('INSERT INTO `assets`(`asset_id`, `asset_name`, `asset_description`, `asset_creator`, `asset_type`, `asset_onsale`) VALUES (?, ?, "Shirt", ?, ?, 1)');
					$name = pathinfo($_FILES['CONTENTUploader']['name'], PATHINFO_FILENAME);
					$name = str_replace($blockedchars, '', $name);
					$stmt_insertasset->bind_param('isii', $asset_id, $name, $user->id, $content_type);
					$stmt_insertasset->execute();

					$ta_id = TransactionUtils::GenerateID();
					$stmt_processtransaction = $con->prepare("INSERT INTO `transactions`(`ta_id`, `ta_userid`, `ta_currency`, `ta_cost`, `ta_asset`, `ta_assettype`, `ta_assetcreator`) VALUES (?, ?, 'tickets', 0, ?, ?, ?)");
					$stmt_processtransaction->bind_param('siiii', $ta_id, $user->id, $asset_id, $content_type, $user->id);
					$stmt_processtransaction->execute();

					echo file_get_contents("http://localhost:64209/render?id=$asset_id&type=shirt", false);

					die(header("Location: /Item.aspx?ID=".$asset_id));
				}
			} else if($content_type == Asset::PANTS) {
				if($width != 585 || $height != 559) {
					array_push($_SESSION['errors'], "Not valid pants image! (Incorrect size)");
				} else {
					// this tells the exporter to save the images as a transparent png (as it doesn't do it by default)
					imagesavealpha($image, true);  // the actual tshirt
					
					while(!checkDecalSpace($asset_id)) {
						$asset_id += 1;
					}

					// save images
					imagepng($image, "$mediadir/$asset_id", 5);

					// embed asset id into image asset as to make sure its able to be handled correctly by the asset deliverer (very hacky lol)
					$pngdata = readFromFile($asset_id);
					$pngdata = "RELATEDID=".(intval($asset_id)+1)."\n" . $pngdata;
					file_put_contents("$mediadir/$asset_id", $pngdata);
					
					// increase by one since this is all just the asset id we want to upload.
					$asset_id += 1;
					$rbxm = readFromFile("pants.rbxm");
					$rbxm = str_replace("[[ID]]", $asset_id-1, $rbxm);
					file_put_contents("$mediadir/$asset_id", $rbxm);
					
					// insert asset into table
					$stmt_insertasset = $con->prepare('INSERT INTO `assets`(`asset_id`, `asset_name`, `asset_description`, `asset_creator`, `asset_type`, `asset_onsale`) VALUES (?, ?, "Pants", ?, ?, 1)');
					$name = pathinfo($_FILES['CONTENTUploader']['name'], PATHINFO_FILENAME);
					$name = str_replace($blockedchars, '', $name);
					$stmt_insertasset->bind_param('isii', $asset_id, $name, $user->id, $content_type);
					$stmt_insertasset->execute();

					$ta_id = TransactionUtils::GenerateID();
					$stmt_processtransaction = $con->prepare("INSERT INTO `transactions`(`ta_id`, `ta_userid`, `ta_currency`, `ta_cost`, `ta_asset`, `ta_assettype`, `ta_assetcreator`) VALUES (?, ?, 'tickets', 0, ?, ?, ?)");
					$stmt_processtransaction->bind_param('siiii', $ta_id, $user->id, $asset_id, $content_type, $user->id);
					$stmt_processtransaction->execute();

					echo file_get_contents("http://localhost:64209/render?id=$asset_id&type=pants", false);

					die(header("Location: /Item.aspx?ID=".$asset_id));
				}
			}

		} else {
			array_push($_SESSION['errors'], "Image was not a valid type!");
		}
	} 
	/*
		CONTENTName
		CONTENTDescription
		CONTENTTextUploader
		CONTENTMeshUploader
	*/
	
	else if(isset($_POST['CONTENTName']) && isset($_POST['CONTENTDescription']) &&
		isset($_FILES['CONTENTTextUploader']) && isset($_FILES['CONTENTMeshUploader'])
		&& isset($_FILES['CONTENTModelUploader']) && isset($_POST['CONTENTUploadButton'])
		&& isset($_POST['CONTENTTix']) && $user->IsAdmin()) {
			
		$stmt_getcount = $con->prepare("SELECT `AUTO_INCREMENT` FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'gammablox' AND TABLE_NAME = 'assets';");
		$stmt_getcount->execute();
		$result = $stmt_getcount->get_result();
		$asset_id = $result->fetch_assoc()['AUTO_INCREMENT']; 

		while(!checkHatSpace($asset_id)) {
			$asset_id += 1;
		}

		$image = imagecreatefromstring(file_get_contents($_FILES['CONTENTTextUploader']['tmp_name']));
		list($width, $height) = getimagesize($_FILES['CONTENTTextUploader']['tmp_name']);

		// this basically makes it so the image is capped at 256 and scales to original aspect ratio
		if($width > $height) {
			$new_width = 256;
			$new_height = -1;
		} else if($width < $height) {
			$new_width = -1;
			$new_height = 256;
		} else {
			$new_width = 256;
			$new_height = 256;
		}

		$resultimage = imagescale($image, $new_width, $new_height);
		
		// this tells the exporter to save the images as a transparent png (as it doesn't do it by default)
		imagesavealpha($resultimage, true);   // the actual decal
		imagepng($resultimage, "$mediadir/$asset_id", 5);

		// save images
		imagepng($resultimage, "$mediadir/$asset_id", 5);

		// embed asset id into image asset as to make sure its able to be handled correctly by the asset deliverer (very hacky lol)
		$pngdata = readFromFile($asset_id);
		$pngdata = "RELATEDID=".(intval($asset_id)+2)."\n" . $pngdata;
		file_put_contents("$mediadir/$asset_id", $pngdata);
		
		// increase by one since this is all just the asset id we want to upload.
		$asset_id += 1;
		move_uploaded_file($_FILES['CONTENTMeshUploader']['tmp_name'], "$mediadir/$asset_id");
		$meshdata = readFromFile($asset_id);
		$meshdata = "RELATEDID=".(intval($asset_id)+1)."\n" . $meshdata;
		file_put_contents("$mediadir/$asset_id", $meshdata);

		$asset_id += 1;
		move_uploaded_file($_FILES['CONTENTModelUploader']['tmp_name'], "$mediadir/$asset_id");

		$rbxm = readFromFile("$asset_id");
		$rbxm = str_replace("[[MESH_ID]]", $asset_id-1, $rbxm);
		$rbxm = str_replace("[[TEXT_ID]]", $asset_id-2, $rbxm);
		file_put_contents("$mediadir/$asset_id", $rbxm);

		$tix = intval(trim($_POST['CONTENTTix']));
		
		// insert asset into table
		$stmt_insertasset = $con->prepare('INSERT INTO `assets`(`asset_id`, `asset_name`, `asset_description`, `asset_creator`, `asset_type`, `asset_status`, `asset_tixcost`, `asset_onsale`) VALUES (?, ?, ?, ?, 8, 0, ?, 1)');
		$name = trim($_POST['CONTENTName']);
		$name = str_replace($blockedchars, '', $name);
		$desc = trim($_POST['CONTENTDescription']);
		$desc = str_replace($blockedchars, '', $desc);
		$stmt_insertasset->bind_param('issii', $asset_id, $name, $desc, $user->id, $tix);
		$stmt_insertasset->execute();

		$ta_id = TransactionUtils::GenerateID();
		$stmt_processtransaction = $con->prepare("INSERT INTO `transactions`(`ta_id`, `ta_userid`, `ta_currency`, `ta_cost`, `ta_asset`, `ta_assettype`) VALUES (?, ?, 'tickets', 0, ?, 8)");
		$stmt_processtransaction->bind_param('sii', $ta_id, $user->id, $asset_id);
		$stmt_processtransaction->execute();
		//debug mode: check ipconfig
		echo file_get_contents("http://localhost:64209/render?id=$asset_id&type=hat", false);
	
		die(header("Location: /Item.aspx?ID=".$asset_id));
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>GAMMA: A FREE Virtual World-Building Game with Avatar Chat, 3D Environments, and Physics</title>
		<link rel="stylesheet" type="text/css" href="/CSS/AllCSS.css">
		<link rel="Shortcut Icon" type="image/ico" href="/favicon.ico">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="author" content="Zlysie">
		<meta name="description" content="GAMMA is a FREE (invite only) casual virtual world with fully constructible/desctructible 3D environments and immersive physics. Build, battle, chat, or just hang out.">
		<meta name="keywords" content="game, video game, building game, construction game, online game, LEGO game, LEGO, MMO, MMORPG, gammablox, gamma roblox, old roblox">
		<script src="/js/WebResource.js" type="text/javascript"></script>
		<script src="/js/jquery.js" type="text/javascript"></script>
		<style>
			.Validators {
				color:red;
			}
		</style>
	</head>
	<body>
		<form name="aspnetForm" method="post" action="ContentBuilder.aspx?ContentType=<?= $content_type ?>" id="aspnetForm"  enctype="multipart/form-data">
			<div id="Container">
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_header.php"; ?>
				<div id="Body">
					<div id="ContentBuilderContainer">
						<?php if($content_type == 1): ?>
						<h2>Image Builder</h2>
						<div class="UpsellPanel">
							<p style="text-align: left;padding-top:10px">Click one of the buttons below to choose the type of content you wish to create.</p>
							
							<p><button class="Button" onclick="window.location.href = 'ContentBuilder.aspx?ContentType=13'; return false;">Decal</button></p>
							<p><button class="Button" onclick="window.location.href = 'ContentBuilder.aspx?ContentType=12'; return false;">Pants</button></p>
							<p><button class="Button" onclick="window.location.href = 'ContentBuilder.aspx?ContentType=11'; return false;">Shirt</button></p>
							<p><button class="Button" onclick="window.location.href = 'ContentBuilder.aspx?ContentType=2'; return false;">T-Shirt</button></p>
							<?php if($user->IsAdmin()): ?>
							<p><button class="Button" onclick="window.location.href = 'ContentBuilder.aspx?ContentType=8'; return false;">Hat</button></p>
							<?php endif ?>
						</div>
						<?php endif ?>
						<?php if($content_type == 13): ?>
						<h2>Decal Builder</h2>
						<div class="InstructionsPanel">
							<h3>Instructions</h3>
							<div style="padding:15px 0">
								On GAMMA, a Decal is an image that can be applied to one of a part's faces. To create a Decal:
								<ol>
									<li>Click the "Browse" button below</li>
									<li>Use the File Explorer that pops up to browse your computer.</li>
									<li>Find and select the picture that you want to use as your decal. Any standard image (.png, .jpg, .gif) will work.</li>
									<li>Finally, click the "Create Decal" button.</li>
								</ol>
								The image you selected will be uploaded to GAMMA, where we will create a Decal and add it to your inventory. To use this Decal, simply open the <b>Insert</b> menu in GAMMA, choose My Decals, and click the Decal you wish to insert. You can drag the Decal onto the part you wish to decorate.
							</div>
						</div>   
						<?php endif ?>
						<?php if($content_type == 12): ?>
						<h2>Pants Builder</h2>

						<div class="InstructionsPanel">
							<h3>Instructions</h3>
							<div style="padding: 15px 0px 10px 0px">
								On GAMMA, Pants are a texture character adornment that is applied to all surfaces of the character's legs and torso. To create Pants:
								<ol>
									<li>Open the <a href="/images/PantsTemplate.png">Pants Template</a> in the image editor of your choice.</li>
									<li>Modify the template to suit your tastes.</li>
									<li>Save the customized Pants Texture to your computer.</li>
									<li>Click the "Browse" button below.</li>
									<li>Use the File Explorer that pops up to browse your computer.</li>
									<li>Find and select the newly created Pants Texture</li>
									<li>Finally, click the "Create Pants" button.</li>
								</ol>
								<p>
									The texture you created will be uploaded to GAMMA, where we will create a pair of Pants and add it to your inventory.
									To wear the Pants, simply go to the <a href="/My/Character.aspx">Change Character</a> page, find them in your wardrobe, and click to wear them.
								</p>
								<p>
									For more information, read the tutorial: <a href="">How to Make Shirts and Pants</a>.
								</p>
							</div>
						</div>
						<?php endif ?>
						<?php if($content_type == 11): ?>
						<h2>Shirt Builder</h2>

						<div class="InstructionsPanel">
							<h3>Instructions</h3>
							<div style="padding: 15px 0px 10px 0px">
								On GAMMA, a Shirt is a texture character adornment that is applied to all surfaces of the character's arms and torso. To create Pants:
								<ol>
									<li>Open the <a href="/images/ShirtTemplate.png">Shirt Template</a> in the image editor of your choice.</li>
									<li>Modify the template to suit your tastes.</li>
									<li>Save the customized Shirt Texture to your computer.</li>
									<li>Click the "Browse" button below.</li>
									<li>Use the File Explorer that pops up to browse your computer.</li>
									<li>Find and select the newly created Shirt Texture</li>
									<li>Finally, click the "Create Shirt" button.</li>
								</ol>
								<p>
									The texture you created will be uploaded to GAMMA, where we will create a Shirt and add it to your inventory.
									To wear this Shirt, simply go to the <a href="/My/Character.aspx">Change Character</a> page, find it in your wardrobe, and click to wear it.
								</p>
								<p>
									For more information, read the tutorial: <a href="">How to Make Shirts and Pants</a>.
								</p>
							</div>
						</div>
						<?php endif ?>

						<?php if($content_type == 2): ?>
						<h2>T-Shirt Builder</h2>

						<div class="InstructionsPanel">
							<h3>Instructions</h3>
							<div style="padding: 15px 0px 10px 0px">
								On GAMMA, a T-Shirt is a transparent torso adornment with a decal applied to the front surface. To create T-Shirt:
								<ol>
									<li>Click the "Browse" button below.</li>
									<li>Use the File Explorer that pops up to browse your computer.</li>
									<li>Find and select the picture that you want to use as the shirt's decal. Any standard image (.png, .jpg, .gif) will work.</li>
									<li>Finally, click the "Create T-Shirt" button.</li>
								</ol>
								<p>
									The texture you created will be uploaded to GAMMA, where we will create a T-Shirt and add it to your inventory.
									To wear the T-Shirt, simply go to the <a href="/My/Character.aspx">Change Character</a> page, find it in your wardrobe, and click to wear it.
								</p>
							</div>
						</div>
						<?php endif ?>
						<?php if($content_type == 8): ?>
						<h2>Hat Builder</h2>
						<div class="InstructionsPanel">
							<h3>Instructions</h3>
							<div style="padding: 15px 0px 10px 0px">
								Hey you know what this does. Upload the texture, mesh and model.
							</div>
						</div>
						<?php endif ?>

						<?php 
							switch ($content_type) {
								case 13:
									$content_label = "Decal";
									break;
								
								case 12:
									$content_label = "Pants";
									break;
								
								case 11:
									$content_label = "Shirt";
									break;

								case 2:
									$content_label = "T-Shirt";
									break;
								case 8:
									$content_label = "Hat";
									break;
							}
							if($content_type != 1 && $content_type != 8):
						?>
						<div class="UploaderPanel">
							<h3>Upload Texture</h3>
							<div style="color:red; margin: 10px auto; font-weight: bold;">
							<?php 
								foreach($_SESSION['errors'] as $error) {
									echo "<p>$error</p>";
								}
							?>
							</div>
							<blockquote>
								<p><input type="file"   name="CONTENTUploader" accept=".png,.jpg,.jpeg"></p>
								<p><input type="submit" name="CONTENTUploadButton" value="Create <?= $content_label ?>"></p>
							</blockquote>
							<span class="DisclaimerLink">
								All uploaded images are moderated. Please upload only appropriate content. 
								<a href="#" onclick="return false;" onmouseenter="$(this).find('span').css('display','block')" onmouseleave="$(this).find('span').css('display','none')">
									Image rules 
									<span style="display: none;position: absolute;color: white;left: 0;right: 0;margin-inline: auto;width: 800px;height:200px;top: 25%;bottom:50%;text-align: left;border: 1px solid black;background: #6e99c9;padding: 10px;">
										GAMMA allows users to upload their own images for the purpose of decorating their character and places.
										All uploaded images are screened by human moderators. Inappropriate images are removed
										from GAMMA. Users who upload inappropriate content may be warned or banned (depending
										on level of offense). All images must be suitable for viewing by kids aged 4-124.
										<br><br>
										Guidelines
										<ul>
											<li>No photos of real (regular) people including users, kids, parents, etc </li>
											<li>No imagery that would not appear in a G-rated movie</li>
											<li>No images with subversive intent</li>
											<li>No images with offensive text</li>
											<li>No images with drugs or drug related items</li>
											<li>No nudity or suggestive images</li>
											<li>No gory or bloody things</li>
											<li>No real guns/swords/other weapons</li>
										</ul>
									</span>
								</a>
							</span>
							<p></p>
						</div>
						<?php endif ?>
						<?php if($content_type == 8): ?>
						<div class="UploaderPanel">
							<h3>Upload Hat</h3> 
							<blockquote>
								<p>Name:       <br><input    type="text" name="CONTENTName" class="TextBox" style="width: 334px;"></p>
								<p>Description:<br><textarea type="text" name="CONTENTDescription" class="MultilineTextbox" style="width: 334px; height: 54px;"></textarea></p>
								<p>Tx Price:   <br><input    type="text" name="CONTENTTix" class="TextBox" style="width: 334px;"></p>
								<p>Texture:    <br><input    type="file" name="CONTENTTextUploader" accept=".png,.jpg,.jpeg"></p>
								<p>Mesh:       <br><input    type="file" name="CONTENTMeshUploader"></p>
								<p>Model:      <br><input    type="file" name="CONTENTModelUploader"></p>
								<p><input type="submit" name="CONTENTUploadButton" value="Create <?= $content_label ?>"></p>
							</blockquote>
							<p></p>
						</div>
						<?php endif ?>
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
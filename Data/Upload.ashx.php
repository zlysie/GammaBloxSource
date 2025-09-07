<?php
	session_start();
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/assetutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
	
	$mediadir = $_SERVER['DOCUMENT_ROOT']."/../gamma-assets";
	$thumbsdir = $mediadir .'/thumbs';

	$unavail120 = file_get_contents($mediadir.'/unavail-120x120.png');
	$unavail250 = file_get_contents($mediadir.'/unavail-250x250.png');

	function libxml_display_error($error) {
		$return = "<br/>\n";
		switch ($error->level) {
			case LIBXML_ERR_WARNING:
				$return .= "<b>Warning $error->code</b>: ";
				break;
			case LIBXML_ERR_ERROR:
				$return .= "<b>Error $error->code</b>: ";
				break;
			case LIBXML_ERR_FATAL:
				$return .= "<b>Fatal Error $error->code</b>: ";
				break;
		}
		$return .= trim($error->message);
		if ($error->file) {
			$return .=    " in <b>$error->file</b>";
		}
		$return .= " on line <b>$error->line</b>\n";

		return $return;
	}

	function libxml_display_errors() {
		$errors = libxml_get_errors();
		foreach ($errors as $error) {
			print libxml_display_error($error);
		}
		libxml_clear_errors();
	}

	/* thank you weeg <3 */
	function ValidateRoblox_XML(?string $XML_Data) {
		libxml_use_internal_errors(true);
		$data = "";
		foreach(preg_split("/((\r?\n)|(\r\n?))/", $XML_Data) as $line) {
			if(!str_starts_with(trim($line),"<External>")) {
				$data .= $line;
			}
		}

		//FIND BETTER WAY TO DO THIS
		$xml = new DOMDocument();
		$xml->loadXML($data, LIBXML_NOBLANKS);
		if(!$xml->schemaValidate($_SERVER['DOCUMENT_ROOT']."/roblox.xsd")){
			//throw new Exception("Invalid LEGACY GAMMA XML Format file");
			libxml_display_errors();
			http_response_code(403);
			echo ("Recieved xml has incorrect data!");
			return false;
		}else{
			echo "Valid XML File";
			return true;
		}
	}

	function ValidateRoblox_XML_Place(?string $XML_Data): array|null {
		libxml_use_internal_errors(true);
		$data = "";
		$maxplayers = -1;
		foreach(preg_split("/((\r?\n)|(\r\n?))/", $XML_Data) as $line) {
			if(!str_starts_with(trim($line),"<External>")) {
				$data .= $line;
				if(str_starts_with(trim($line), "<int name=\"MaxPlayers\">")) {
					$maxplayers = intval(str_replace("<int name=\"MaxPlayers\">", "", str_replace("</int>", "", trim($line))));
				}
			}
		}

		//FIND BETTER WAY TO DO THIS
		$xml = new DOMDocument();
		$xml->loadXML($data, LIBXML_NOBLANKS);
		if(!$xml->schemaValidate($_SERVER['DOCUMENT_ROOT']."/roblox.xsd")){
			//throw new Exception("Invalid LEGACY GAMMA XML Format file");
			libxml_display_errors();
			http_response_code(403);
			echo ("Recieved xml has incorrect data!");
			return null;
		}else{
			echo "Valid XML File";
			return [$maxplayers];
		}
	}

	function checkMimeType($file) {
		$file_info = new finfo(FILEINFO_MIME_TYPE);
		return $file_info->buffer(file_get_contents($file));
	}
	
	function checkSpace(?int $base_id) {
		$mediadir = $_SERVER['DOCUMENT_ROOT']."/../gamma-assets";
		return !file_exists($mediadir."/".$base_id);
	}

	$user = UserUtils::GetLoggedInUser();

	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	
	if(isset($_GET['assetid']) && $user != null) {
		$asset_id = intval($_GET['assetid']);

		if($_GET['type'] == "Place") {
			if($asset_id == 0 && isset($_GET['name']) && isset($_GET['description']) && isset($_GET['ispublic']) && isset($_GET['iscopylocked'])) {
				$asset_name = urldecode($_GET['name']);
				$asset_desc = urldecode($_GET['description']);
				$asset_public = intval($_GET['ispublic']);
				$asset_copylocked = intval($_GET['iscopylocked']);
				header("Content-Type: text/plain");
				$recieveddata = file_get_contents("php://input");
				if(strlen(gzdecode($recieveddata)) != 0) {
					$recieveddata = gzdecode($recieveddata);
					echo "decoding using gz\n";
				}

				$result = ValidateRoblox_XML_Place($recieveddata);
				
				if($result != null && $result[0] > 0) {

					$maxplayers = $result[0];

					// get current asset count
					$stmt_getcount = $con->prepare("SELECT `AUTO_INCREMENT` FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'gammablox' AND TABLE_NAME = 'assets';");
					$stmt_getcount->execute();
					$result = $stmt_getcount->get_result();
					$asset_id = $result->fetch_assoc()['AUTO_INCREMENT']; 
					while(!checkSpace($asset_id)) {
						$asset_id += 1;
					}
					
					$stmt_insertasset = $con -> prepare('INSERT INTO `assets`(`asset_id`, `asset_type`, `asset_creator`, `asset_name`, `asset_description`, `place_access`, `place_copylocked`, `place_maxplayers`) VALUES (?, 9, ?, ?, ?, ?, ?, ?)');
					$stmt_insertasset -> bind_param('iissiii', $asset_id, $user->id, $asset_name, $asset_desc, $asset_public, $asset_copylocked, $maxplayers);
					$stmt_insertasset -> execute();
					$asset_id = $con -> insert_id;
					
					$myfile = fopen("$mediadir/$asset_id", "w");
					fwrite($myfile, $recieveddata);
					fclose($myfile);
					file_get_contents("http://localhost:64209/render?id=$asset_id&type=place", false);
				}
			} else {
				header("Content-Type: text/plain");
				$recieveddata = file_get_contents("php://input");
				if(strlen(gzdecode($recieveddata)) != 0) {
					$recieveddata = gzdecode($recieveddata);
					echo "decoding using gz\n";
				}
				$result = ValidateRoblox_XML_Place($recieveddata);
				if($result != null && $result[0] > 0) {

					$maxplayers = $result[0];

					echo "maxplayers: $maxplayers";

					$myfile = fopen("$mediadir/$asset_id", "w");
					fwrite($myfile, $recieveddata);
					fclose($myfile);
					
					$stmt_updateasset = $con -> prepare('UPDATE `assets` SET `asset_lastupdate` = now(), `place_maxplayers` = ? WHERE `asset_id` = ?');
					$stmt_updateasset->bind_param('ii', $maxplayers, $asset_id);
					$stmt_updateasset->execute();
	
					file_get_contents("http://localhost:64209/render?id=$asset_id&type=place", false);
				}
			}
		} else if($_GET['type'] == "Model") {
			if($asset_id == 0 && isset($_GET['name']) && isset($_GET['description']) && isset($_GET['ispublic'])) {
				$asset_name = urldecode($_GET['name']);
				$asset_desc = urldecode($_GET['description']);
				$asset_public = intval($_GET['ispublic']);
				$asset_copylocked = 0;
				header("Content-Type: text/plain");
				$recieveddata = file_get_contents("php://input");
				if(strlen(gzdecode($recieveddata)) != 0) {
					$recieveddata = gzdecode($recieveddata);
					echo "decoding using gz\n";
				}
				
				if(ValidateRoblox_XML($recieveddata)) {
					// get current asset count
					$stmt_getcount = $con->prepare("SELECT `AUTO_INCREMENT` FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'gammablox' AND TABLE_NAME = 'assets';");
					$stmt_getcount->execute();
					$result = $stmt_getcount->get_result();
					$asset_id = $result->fetch_assoc()['AUTO_INCREMENT']; 
					while(!checkSpace($asset_id)) {
						$asset_id += 1;
					}
					
					$stmt_insertasset = $con -> prepare('INSERT INTO `assets`(`asset_id`, `asset_type`, `asset_creator`, `asset_name`, `asset_description`, `place_access`, `place_copylocked`) VALUES (?, 10, ?, ?, ?, ?, ?)');
					$stmt_insertasset -> bind_param('iissii', $asset_id, $user->id, $asset_name, $asset_desc, $asset_public, $asset_copylocked);
					$stmt_insertasset -> execute();
					$asset_id = $con -> insert_id;
					
					$myfile = fopen("$mediadir/$asset_id", "w");
					fwrite($myfile, $recieveddata);
					fclose($myfile);
					file_get_contents("http://localhost:64209/render?id=$asset_id&type=model", false);
				} else {
					http_response_code(500);
					die("Place file had invalid data!");
				}
			} else {
				header("Content-Type: text/plain");
				$recieveddata = file_get_contents("php://input");
				if(strlen(gzdecode($recieveddata)) != 0) {
					$recieveddata = gzdecode($recieveddata);
					echo "decoding using gz\n";
				}
				
				if(ValidateRoblox_XML($recieveddata)) {
					// get current asset count
					$stmt_getcount = $con->prepare("SELECT `AUTO_INCREMENT` FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'gammablox' AND TABLE_NAME = 'assets';");
					$stmt_getcount->execute();
					$result = $stmt_getcount->get_result();
					$asset_id = $result->fetch_assoc()['AUTO_INCREMENT']; 
					while(!checkSpace($asset_id)) {
						$asset_id += 1;
					}				

					$myfile = fopen("$mediadir/$asset_id", "w");
					fwrite($myfile, $recieveddata);
					fclose($myfile);
					
					$stmt_updateasset = $con->prepare('UPDATE `assets` SET `asset_lastupdate` = now() WHERE `asset_id` = ?');
					$stmt_updateasset->bind_param('i', $asset_id);
					$stmt_updateasset->execute();
	
					file_get_contents("http://localhost:64209/render?id=$asset_id&type=model", false);
				} else {
					http_response_code(500);
					die("Place file had invalid data!");
				}
			}
		} 
	} else {
		echo "Stupid bitch you ain't logged in brah :(";
		die();
	}
?>

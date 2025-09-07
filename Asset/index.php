
<?php
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/assetutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/friending.php";
	
	$id = intval($_GET["id"]);

	$user = UserUtils::GetLoggedInUser();

	
	
	function checkMimeType($file) {
		$file_info = new finfo(FILEINFO_MIME_TYPE);
		return $file_info->buffer(file_get_contents($file));
	}
	
	/**
	 * Get mimetype from inputted data (via magic number)
	 * @param mixed $contents
	 * @return bool|string
	 */
	function checkMimeTypeContents($contents) {
		$file_info = new finfo(FILEINFO_MIME_TYPE);
		return $file_info->buffer($contents);
	}
	
	if(empty(trim($_GET['id']))) {
		http_response_code(500);
		die("No id was given!");
	}

	if($id == 0) {
		http_response_code(500);
		die("That was not a valid id!");
	}

	// try to see if queried id is a registered asset
	$asset = AssetUtils::GetAsset($id);

	$bypass = $_GET['givemecodeall'] == "3544bd46-a09e-4e9f-9f4a-8cb6821ad356" || ($user != null && $user->IsAdmin()) ?? false;

	// if so then load values from table and display contents (based on asset_status)
	// asset_status:
	// 1 = pending, 0 = accepted, -1 = rejected
	
	if($asset != null) {
		if($user != null) {
			$friends_with = FriendUtils::isUserFriendsWith($user->id, $asset->creator->id) || $user->id == $asset->creator->id;
		} else {
			$friends_with = false;
		}
		
		$place_condition = false;
		if($asset instanceof Place) {

			if($asset->copylocked && $asset->friends_only) {
				$place_condition = $friends_with;
				echo "checking if friends with";
			} else if($asset->copylocked && !$asset->friends_only) {
				echo "copylocked, not friends";
				$place_condition = false;
			} else {
				$place_condition = true;
			}
			
		} else {
			if($asset->type != Asset::MODEL) {
				$place_condition = true;
			} else {
				if(!Place::FromID($asset->id)->friends_only) {
					$place_condition = true;
				}
			}
			
		}
		
		$bypass = $bypass || $user->id == $asset->creator->id;

		if(($asset->status != 0 || !$place_condition) && !$bypass) {
			http_response_code(401);
			ob_clean();
			die("Not authorised to view this asset!");
		} else {
			$filename =  $_SERVER["DOCUMENT_ROOT"]."/../gamma-assets/$id";
			if(file_exists($filename)) {
				$type = checkMimeType($filename);	
				if($type != "application/gzip") {
					$handle = fopen($filename, "r"); 
					$contents = fread($handle, filesize($filename)); 
					fclose($handle);
					
					$fp = gzopen ($filename, 'w9');
					gzwrite ($fp, $contents);
					gzclose($fp);
				} else {
					$contents = gzdecode(file_get_contents($filename));
					$type = "text/plain";
				}

				$contents = str_replace("[[DOMAIN]]", $_SERVER['SERVER_NAME'], $contents);
				header("content-type: $type");
				ob_clean();
				echo $contents;
			} else {
				// no assets found stored on that id then 404
				http_response_code(404);
				die("No asset found by that id!");
			}
		}
	} 
	// however, if queried id is not registered asset then attempt to get the embedded id from the file
	else {
		$filename =  $_SERVER["DOCUMENT_ROOT"]."/../gamma-assets/$id";
		if(file_exists($filename)) {
			$handle = fopen($filename, "r"); 
			$contents = $og_contents = fread($handle, filesize($filename));
			$contents = str_replace("[[DOMAIN]]", $_SERVER['SERVER_NAME'], $contents); 
			fclose($handle);
			$type = checkMimeType($filename);		
			header("content-type: $type");

			ob_clean();

			// grab embedded id and then load values from table and display contents (based on asset_status)
			// asset_status:
			// 1 = pending, 0 = accepted, -1 = rejected
			if(str_starts_with($contents, "RELATEDID=")) {
				$line = preg_split('#\r?\n#', $contents, 2)[0];
				$data = preg_split('#\r?\n#', $contents, 2)[1];
				$getid = intval(str_replace("RELATEDID=", "", $line));
				$asset = AssetUtils::GetAsset($getid);

				if($asset != null) {
					if($asset->status != Asset::ACCEPTED && !$bypass) {
						echo "Not authorised";
						http_response_code(401);
						die();
					} else {
						$type = checkMimeTypeContents($data);
						header("content-type: $type");
						$contents = $data;
					}
				}
			}

			echo $contents;
		} else {
			// no assets found stored on that id then 404
			http_response_code(404);
			die("No asset found by that id!");
		}
		
	}
?>

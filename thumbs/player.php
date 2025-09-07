<?php 
    require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";

	$type = intval($_GET["type"] ?? "180");
	
	function checkMimeType($contents) {
		$file_info = new finfo(FILEINFO_MIME_TYPE);
		return $file_info->buffer($contents);
	}

	if(isset($_GET['id'])) {
		$id = intval($_GET['id']);
		$hash = UserUtils::GetUserAppearanceHashed($id);

		if($hash == "null") {
			$filename =  $_SERVER["DOCUMENT_ROOT"]."/images/render.png";
			$handle = fopen($filename, "r"); 
			$contents = fread($handle, filesize($filename)); 
			fclose($handle);
			$type = checkMimeType($contents);
			header("content-type: $type");
			echo $contents;
		} else {
			$path =  $_SERVER["DOCUMENT_ROOT"]."/../gamma-assets/player/$hash";
			$filename = "$path/$type";

			if($type != 100 && $type != 180) {
				$image = imagecreatefromstring(file_get_contents("$path/100"));

				$width  = imagesx($image);
				$height = imagesy($image);

				if($type == 64) {
					$resizedimage = imagescale($image, 64, 64);
					imagesavealpha($resizedimage, true);
					imagepng($resizedimage, null, 5);
				} else if($type == 48) {
					$resizedimage = imagecreatetruecolor(48, 48);
					$trans_colour = imagecolorallocatealpha($resizedimage, 255, 255, 255, 0);
					imagefill($resizedimage, 0, 0, $trans_colour);
					imagecopyresampled($resizedimage, $image, 0, 0, 0, 0, 48, 48, $width,  $width);
					imagejpeg($resizedimage);
				}
			} else {
				$handle = fopen($filename, "r"); 
				$contents = fread($handle, filesize($filename)); 
				fclose($handle);
				$type = checkMimeType($contents);
				header("content-type: $type");
				echo $contents;
			}
			

			
		}

		if($hash != "null") {
			
		}
		

		

		
		
		
	} else {
		die("not id given");
	}
?>
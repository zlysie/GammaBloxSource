<?php
	$mediadir = $_SERVER['DOCUMENT_ROOT']."/../gamma-assets";
	if(isset($_POST['data']) && isset($_POST['verif']) && isset($_GET['id'])) {
		if($_POST['verif'] == "3544bd46-a09e-4e9f-9f4a-8cb6821ad356") {
			$asset_id = intval($_GET['id']);
			if(!isset($_GET['type'])) {
				$myfile = fopen("$mediadir/thumbs/".$asset_id."_250", "w");
				fwrite($myfile, file_get_contents("data:image/png;base64,".$_POST['data']));
				fclose($myfile);
				
				error_log($_POST['data']);
				//resize to 120x120
				$image = imagecreatefromstring(file_get_contents("data:image/png;base64,".$_POST['data']));
				$resizedimage = imagecreatetruecolor(120, 120);
				$trans_colour = imagecolorallocatealpha($resizedimage, 0, 0, 0, 127);
				imagefill($resizedimage, 0, 0, $trans_colour);
				imagecopyresampled($resizedimage, $image, 0, 0, 0, 0, 120, 120, 250, 250);
				imagesavealpha($resizedimage, true);
				imagepng($resizedimage, "$mediadir/thumbs/".$asset_id."_120", 5);
			} else {
				$type = $_GET['type'];

				if($type == "Place") {
					$myfile = fopen("$mediadir/thumbs/".$asset_id."_420", "w");
					fwrite($myfile, file_get_contents("data:image/png;base64,".$_POST['data']));
					fclose($myfile);

					$image = imagecreatefromstring(file_get_contents("$mediadir/thumbs/".$asset_id."_420"));
					imagesavealpha($image, true);  // the actual tshirt
					imagepng($image, "$mediadir/thumbs/$asset_id"."_420", 5);
				} else if($type == "player") {
					$md5hash = $_POST['md5'];
					$image = imagecreatefromstring(file_get_contents("data:image/png;base64,".$_POST['data']));
					$width  = imagesx($image);
					$height = imagesy($image);

					$path = "$mediadir/player/$md5hash";

					if(!is_dir($path)) {
						mkdir($path);
					}

					if($width != $height) {
						$resizedimage = imagecreatetruecolor($width, $height);
						$trans_colour = imagecolorallocatealpha($resizedimage, 0, 0, 0, 127);
						imagefill($resizedimage, 0, 0, $trans_colour);
						imagecopyresampled($resizedimage, $image, 0, 0, 0, 0, $width, $height, $width, $height);
						imagecopyresampled($resizedimage, $image, 0, 0, 0, 0, $width, $height, $width, $height);
						imagecopyresampled($resizedimage, $image, 0, 0, 0, 0, $width, $height, $width, $height);
						imagesavealpha($resizedimage, true);
						imagepng($resizedimage, "$path/180", 5);
					} else {
						$resizedimage = imagecreatetruecolor(100, 100);
						$trans_colour = imagecolorallocatealpha($resizedimage, 0, 0, 0, 127);
						imagefill($resizedimage, 0, 0, $trans_colour);
						imagecopyresampled($resizedimage, $image, 0, 0, 0, 0, 100, 100, $width, $width);
						imagecopyresampled($resizedimage, $image, 0, 0, 0, 0, 100, 100, $width, $width);
						imagecopyresampled($resizedimage, $image, 0, 0, 0, 0, 100, 100, $width, $width);
						imagesavealpha($resizedimage, true);
						imagepng($resizedimage, "$path/100", 5);
					}
				}
			}
			

		}
	}
?>
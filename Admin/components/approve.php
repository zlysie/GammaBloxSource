<?php 
	session_start();
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/assetutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	
	$user = UserUtils::GetLoggedInUser();

	if(!$user->IsAdmin()) {
		http_response_code(401);
		die("Not authorised");
	}

	$assets = AssetUtils::GetAllUncheckedAssets();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<style>
			body {
				background-color: lightgray;
				margin:15px;
				width:100%;
				font:normal 10pt/normal 'Comic Sans MS','Comic Sans MS Web',Verdana,sans-serif;
			}
			
			#ModBox {
				border:1px solid black;
				background-color: rgb(230, 230, 230);
				padding:15px;
				width:100%;
				float:left;
			}

			#ModBox h2 {
				margin-top:0;
			}

			#ModBox h3 {
				margin-top:0;
			}
			
			@font-face {
				font-family: 'Comic Sans MS Web';
				src: url('/CSS/COMIC.ttf');
			}

			.Asset {
				float:left;
				width: 150px;
				text-align: center;
				
				
			}
		</style>
		<script src="/js/jquery.js" type="text/javascript"></script>
		<script>
			function checkAndRemove() {
				if($('.Asset').length == 0) {
					$("#ModBox").append("<p id=\"noassetthang\">No assets for you buddy.</p>");
				} else {
					$("#noassetthang");
				}
			}

			function reject(pass_id) {
				$.post( "/Admin/postadmin", { id: pass_id, type: "deny" }).done(function( data ) {
					$("#asset_"+pass_id).remove();
					checkAndRemove();
				});
			}

			function render(pass_id) {
				$.post( "/Admin/postadmin", { id: pass_id, type: "render" }).done(function( data ) {
					$("#asset_"+pass_id).find("#AssetThumbnailHyperLink").find("img").attr("src","/thumbs/?id="+pass_id+"&type=120&t="+Math.random());
					checkAndRemove();
				});
			}
			
			function accept(pass_id) {
				$.post( "/Admin/postadmin", { id: pass_id, type: "accept" }).done(function( data ) {
					$("#asset_"+pass_id).remove();
					checkAndRemove();
				});
			}
		</script>
	</head>
	<body>
		<div id="ModBox">
			<h2>Asset Approval Panel</h2>
			<h3>Rules</h3>
			<ul>
				<li>No roblox reuploads (like if someone uploads Flood Escape then reject cuz thats obviously not theirs)</li>
				<li>No NSFW (and other common sense things like racial content)</li>
				<li>No freaky shit (like femboys and stuff)</li>
			</ul>
			<br>
			<?php 
				if(count($assets) != 0) {
					foreach($assets as $asset) {
						$asset_id = $asset->id;
						$asset_name = $asset->name;
						$creator = $asset->creator;
						$creator_id = $creator->id;
						$creator_name = $creator->name;
						$asset_thumburl = "/thumbs/?id=$asset_id&type=120";

						echo <<<EOT
							<div class="Asset" id="asset_$asset_id">
								<div class="AssetThumbnail">
									<a id="AssetThumbnailHyperLink" title="$asset_name" href="/Item.aspx?ID=$asset_id" style="display:inline-block;cursor:pointer;"><img src="$asset_thumburl" border="0" alt="$asset_name" blankurl="http://t6.roblox.com:80/blank-120x120.gif"></a>
								</div>
								<div class="AssetDetails">
									<div class="AssetName"><a href="Item.aspx?ID=$asset_id">$asset_name</a></div>
									<div class="AssetCreator"><span class="Label">Creator:</span> <span class="Detail"><a href="User.aspx?ID=$creator_id">$creator_name</a></span></div>
									<div style="margin-top: 5px;display: inline-block;">
										<button id="submit" onclick="accept($asset_id); return false;">Approve</button>
										<button id="submit" onclick="reject($asset_id); return false;">Reject</button><br>
										<button id="submit" onclick="render($asset_id); return false;">Render</button>
									</div>
								</div>
							</div>
						EOT;
					}
				}
			?>
		</div>
		<script>
			$(function(){
				checkAndRemove();
			});
		</script>
	</body>
</html>
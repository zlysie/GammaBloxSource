<?php
	session_start();
	
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
	UserUtils::LockOutUserIfNotLoggedIn();
	
	$user = UserUtils::GetLoggedInUser();
	
	$stmt_getbodycolors = $con->prepare('SELECT * FROM `bodycolors` WHERE `userid` = ?');
	$stmt_getbodycolors->bind_param('i', $user->id);
	$stmt_getbodycolors->execute();

	$res = $stmt_getbodycolors->get_result();
	if($res->num_rows == 0) {
		$stmt_insertbodycolor = $con->prepare('INSERT INTO `bodycolors`(`userid`) VALUES (?)');
		$stmt_insertbodycolor->bind_param('i', $user->id);
		$stmt_insertbodycolor->execute();

		$head = 24;
		$torso = 23;
		$left_arm = 24;
		$right_arm = 24;
		$left_leg = 119;
		$right_leg = 119;
	} else {
		$row = $res->fetch_assoc();

		$head = $row['head'];
		$torso = $row['torso'];
		$left_arm = $row['leftarm'];
		$right_arm = $row['rightarm'];
		$left_leg = $row['leftleg'];
		$right_leg = $row['rightleg'];
	}
	
	//for clients
	$colors = [
		1 => "#f2f3f2",
		208 => "#e5e4de",
		194 => "#a3a2a4",
		199 => "#635f61",
		26 => "#1b2a34",
		21 => "#c4281b",
		24 => "#f5cd2f",
		226 => "#fdea8c",
		23 => "#0d69ab",
		107 => "#008f9b",
		102 => "#6e99c9",
		11 => "#80bbdb",
		45 => "#b4d2e3",
		135 => "#74869c",
		106 => "#da8540",
		105 => "#e29b3f",
		141 => "#27462c",
		28 => "#287f46",
		37 => "#4b974a",
		119 => "#a4bd46",
		29 => "#a1c48b",
		151 => "#789081",
		38 => "#a05f34",
		192 => "#694027",
		104 => "#6b327b",
		9 => "#e8bac7",
		101 => "#da8679",
		5 => "#d7c599",
		153 => "#957976",
		217 => "#7c5c45",
		18 => "#cc8e68",
		125 => "#eab891"
	];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" id="gamma-lambda-cam">
	<head>
		<title>GAMMA - Character</title>
		<link rel="stylesheet" type="text/css" href="/CSS/AllCSS.css">
		<link rel="Shortcut Icon" type="image/ico" href="/favicon.ico">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="author" content="Zlysie">
		<meta name="description" content="GAMMA is a FREE (invite only) casual virtual world with fully constructible/desctructible 3D environments and immersive physics. Build, battle, chat, or just hang out.">
		<meta name="keywords" content="game, video game, building game, construction game, online game, LEGO game, LEGO, MMO, MMORPG, gammablox, gamma roblox, old roblox">
		<script src="/js/jquery.js" type="text/javascript"></script>
		<script src="/js/jquery-ui.js" type="text/javascript"></script>
		<script src="/js/jquery-modal.js" type="text/javascript"></script>
		<script src="/js/WebResource.js" type="text/javascript"></script>
		<script src="/js/Character.js?t=<?= time() ?>" type="text/javascript"></script>
		<style>
			button {
				border: 0;
			}

			.SelectPart {
				border:1px solid black;
				width: 215px;
				height:350px;
				z-index: 1;
			}
				
			.SelectPart {
				height:290px;
				width: 215px;
				border:1px solid black;
			}
				
			.SelectedPart {
				text-align:center;
				padding-left:35px;
				background: white;
				padding:3px;
			}
				
			.SelectedPart button {
				border:1px solid white;
			}
				
			.Head {
				width:45px; 
				height:46px; 
				margin-bottom:5px;
				padding:0;
				text-align:Center;
				overflow:hidden;
			}
				
			.Face {
				top: 2px;
				right: 2.5px;
				width:45px; 
				height:40px; 
				position:relative;
			}

			.RightArm, .LeftArm {
				width:38px; 
				height:82px;
			}

			.Torso {
				width:86px;
				height:82px; 
				margin-left:5px; 
				margin-right:5px;
				text-align:Center;
				overflow:hidden;
			}

			.LeftLeg, .RightLeg {
				width:40px; 
				height:82px; 
				margin: 5px 3px;
			}

			.TorsoDiv, .HeadDiv, .LegDiv {
				width: 215px;
				text-align: center;
				margin: 0 auto;
			}
				
			.ColorPallete {
				border:1px solid black;
				margin:0 10px;
				width:406px;
				background-color: #c0c0c0;
			}
				
			.ColorDisplay {
				padding:35px;
			}

			.ColorDisplay button {
				width:35px;
				height:35px;
				margin:2px;
			}
				
			.BodyPart {
				position: absolute;
				width:380px;
				height:280px;
				top:60px;
				right: -78px;
				padding-right:20px;
			}

			.Colors {
				text-align: center;
				padding: 35px;
			}
		</style>
		<script>
			var selectedPart = "";

			var HEXColors = {
				White: "#f2f3f2",
				LightStoneGrey: "#e5e4de",
				MediumStoneGrey: "#a3a2a4",
				DarkStoneGrey:"#635f61",
				Black:"#1b2a34",
				BrightRed:"#c4281b",
				BrightYellow:"#f5cd2f",
				CoolYellow:"#fdea8c",
				BrightBlue:"#0d69ab",
				BrightBluishGreen:"#008f9b",
				MediumBlue:"#6e99c9",
				PastelBlue:"#80bbdb",
				LightBlue:"#b4d2e3",
				SandBlue:"#74869c",
				BrightOrange:"#da8540",
				BrYellowishOrange:"#e29b3f",
				EarthGreen:"#27462c",
				DarkGreen:"#287f46",
				BrightGreen:"#4b974a",
				BrYellowishGreen:"#a4bd46",
				MediumGreen:"#a1c48b",
				SandGreen:"#789081",
				DarkOrange:"#a05f34",
				ReddishBrown:"#694027",
				BrightViolet:"#6b327b",
				LightReddishViolet:"#e8bac7",
				MediumRed:"#da8679",
				BrickYellow:"#d7c599",
				SandRed:"#957976",
				Brown:"#7c5c45",
				Nougat:"#cc8e68",
				LightOrange:"#eab891"
			};
			
			//for clients
			var IDColors = {
				White:1,
				LightStoneGrey:208,
				MediumStoneGrey:194,
				DarkStoneGrey:199,
				Black:26,
				BrightRed:21,
				BrightYellow:24,
				CoolYellow:226,
				BrightBlue:23,
				BrightBluishGreen:107,
				MediumBlue:102,
				PastelBlue:11,
				LightBlue:45,
				SandBlue:135,
				BrightOrange:106,
				BrYellowishOrange:105,
				EarthGreen:141,
				DarkGreen:28,
				BrightGreen:37,
				BrYellowishGreen:119,
				MediumGreen:29,
				SandGreen:151,
				DarkOrange:38,
				ReddishBrown:192,
				BrightViolet:104,
				LightReddishViolet:9,
				MediumRed:101,
				BrickYellow:5,
				SandRed:153,
				Brown:217,
				Nougat:18,
				LightOrange:125
			};

			function selectPart(partName) {
				selectedPart = partName;
				$(".ColorPallete").modal({showClose: false});
			}

			function changeColor(keyName) {
				$.modal.close();
				$("#"+selectedPart).css("background-color", HEXColors[keyName]);
				//$("#Char").css("display", "none");
				//$("#LoadingChar").css("display", "block");
				$.post( "/api/user?updatechar", { color: IDColors[keyName], type: selectedPart.toLowerCase() }).done(function( data ) {
					$("#Char").attr("src", "/thumbs/player?id=<?= $user->id ?>&t="+Math.random());
					/*window.setTimeout(function() {
						$("#LoadingChar").css("display", "none");
						$("#Char").css("display", "inline");
					}, 500);*/

					
				});
			}

			$(function() {
				$("#Head").css("background-color", "<?= $colors[$head] ?>");
				$("#Torso").css("background-color", "<?= $colors[$torso] ?>");
				$("#LeftArm").css("background-color", "<?= $colors[$left_arm] ?>");
				$("#LeftLeg").css("background-color", "<?= $colors[$left_leg] ?>");
				$("#RightArm").css("background-color", "<?= $colors[$right_arm] ?>");
				$("#RightLeg").css("background-color", "<?= $colors[$right_leg] ?>");

				LoadToSelectAssets(8);
				RefreshWearingItems();
			});
		</script>
	</head>
	<body>
		<form name="aspnetForm" method="post" action="/Default.aspx" id="aspnetForm">
			<div id="Container">
				<div id="AdvertisingLeaderboard"></div>
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_header.php"; ?>
				<div id="Body">
					<div class="ColorPallete" style="display:none">
						<div class="ColorDisplay">
							<button type="button" onclick="changeColor('White');" style="background-color:#f2f3f2;" class="colorButton"></button>
							<button type="button" onclick="changeColor('LightStoneGrey');" style="background-color:#e5e4de;" class="colorButton"></button>
							<button type="button" onclick="changeColor('MediumStoneGrey');" style="background-color:#a3a2a4;" class="colorButton"></button>
							<button type="button" onclick="changeColor('DarkStoneGrey');" style="background-color:#635f61;" class="colorButton"></button>
							<button type="button" onclick="changeColor('Black');" style="background-color:#1b2a34;" class="colorButton"></button>
							<button type="button" onclick="changeColor('BrightRed');" style="background-color:#c4281b;" class="colorButton"></button>
							<button type="button" onclick="changeColor('BrightYellow');" style="background-color:#f5cd2f;" class="colorButton"></button>
							<button type="button" onclick="changeColor('CoolYellow');" style="background-color:#fdea8c;" class="colorButton"></button>
							<button type="button" onclick="changeColor('BrightBlue');" style="background-color:#0d69ab;" class="colorButton"></button>
							<button type="button" onclick="changeColor('BrightBluishGreen');" style="background-color:#008f9b;" class="colorButton"></button>
							<button type="button" onclick="changeColor('MediumBlue');" style="background-color:#6e99c9;" class="colorButton"></button>
							<button type="button" onclick="changeColor('PastelBlue');" style="background-color:#80bbdb;" class="colorButton"></button>
							<button type="button" onclick="changeColor('LightBlue');" style="background-color:#b4d2e3;" class="colorButton"></button>
							<button type="button" onclick="changeColor('SandBlue');" style="background-color:#74869c;" class="colorButton"></button>
							<button type="button" onclick="changeColor('BrightOrange');" style="background-color:#da8540;" class="colorButton"></button>
							<button type="button" onclick="changeColor('BrYellowishOrange');" style="background-color:#e29b3f;" class="colorButton"></button>
							<button type="button" onclick="changeColor('EarthGreen');" style="background-color:#27462c;" class="colorButton"></button>
							<button type="button" onclick="changeColor('DarkGreen');" style="background-color:#287f46;" class="colorButton"></button>
							<button type="button" onclick="changeColor('BrightGreen');" style="background-color:#4b974a;" class="colorButton"></button>
							<button type="button" onclick="changeColor('BrYellowishGreen');" style="background-color:#a4bd46;" class="colorButton"></button>
							<button type="button" onclick="changeColor('MediumGreen');" style="background-color:#a1c48b;" class="colorButton"></button>
							<button type="button" onclick="changeColor('SandGreen');" style="background-color:#789081;" class="colorButton"></button>
							<button type="button" onclick="changeColor('DarkOrange');" style="background-color:#a05f34;" class="colorButton"></button>
							<button type="button" onclick="changeColor('ReddishBrown');" style="background-color:#694027;" class="colorButton"></button>
							<button type="button" onclick="changeColor('BrightViolet');" style="background-color:#6b327b;" class="colorButton"></button>
							<button type="button" onclick="changeColor('LightReddishViolet');" style="background-color:#e8bac7;" class="colorButton"></button>
							<button type="button" onclick="changeColor('MediumRed');" style="background-color:#da8679;" class="colorButton"></button>
							<button type="button" onclick="changeColor('BrickYellow');" style="background-color:#d7c599;" class="colorButton"></button>
							<button type="button" onclick="changeColor('SandRed');" style="background-color:#957976;" class="colorButton"></button>
							<button type="button" onclick="changeColor('Brown');" style="background-color:#7c5c45;" class="colorButton"></button>
							<button type="button" onclick="changeColor('Nougat');" style="background-color:#cc8e68;" class="colorButton"></button>
							<button type="button" onclick="changeColor('LightOrange');" style="background-color:#eab891;" class="colorButton"></button>
						</div>
					</div>
					<div id="CustomizeCharacterContainer">
						<div class="AttireChooser" style="margin-bottom: 10px;">
							<h4>My Wardrobe</h4>
							<div class="AttireCategory">
								<a href="javascript:LoadToSelectAssets(2)"  c="2"  id="category">T-Shirts</a>&nbsp;|&nbsp;
								<a href="javascript:LoadToSelectAssets(11)" c="11" id="category">Shirts</a>&nbsp;|&nbsp;
								<a href="javascript:LoadToSelectAssets(12)" c="12" id="category">Pants</a>&nbsp;|&nbsp;
								<a href="javascript:LoadToSelectAssets(8)"  c="8"  id="category" class="AttireCategorySelector_Selected">Hats</a>
								<div class="AttireOptions">
									<a href="">Shop</a>&nbsp;&nbsp;&nbsp;
									<a href="">Create</a>
								</div>
							</div>
							<div id="asset_template" style="display:none">
								<td class="Asset" valign="top">
									<div class="AssetThumbnail">
										<a href="" class="DeleteButtonOverlay">&nbsp;[ wear ]&nbsp;</a>
										<a id="AssetThumbnailLink" title="" href="/Item.aspx?ID=" style="display:inline-block;cursor:pointer;">
											<img src="" border="0" alt="" style="width:110px; height: 110px;">
										</a>
									</div>
									<div class="AssetDetails">
										<div class="AssetName"><a href="Item.aspx?ID=">Name</a></div>
										<div class="AssetCreator"><span class="Label">Creator:</span> <span class="Detail"><a href="User.aspx?ID=">Creator</a></span></div>
									</div>
								</td>
							</div>
							<table id="SelectFromAssetsTable">
							<tr></tr>
							<tr></tr>
							</table>
							<div class="FooterPager">
								<a id="FirstLinker" href="javascript:LoadToSelectAssets()">First</a>
								<a id="PreviousLinker" href="javascript:LoadToSelectAssets(currentSelectCategory, currentPage-1)">Previous</a>
								<span id="SelectPages">
									&nbsp;
								</span>
								<a id="NextLinker" href="javascript:LoadToSelectAssets(currentSelectCategory, currentPage+1)">Next</a>
								<a id="LastLinker" href="javascript:LoadToSelectAssets(currentSelectCategory, pageCount)">Last</a>
							</div>
						</div>
						<div class="CharacterViewer" style="text-align: center;">
							<h4>My Character</h4>
							<img id="Char" style="width: 75%;margin: 20px 0px;" src="/thumbs/player?id=<?= $user->id ?>">
							<div id="LoadingChar" style="display: none;">
								<img src="/images/ProgressIndicator2.gif" style="position: absolute;margin-top: 20px;">
								<img style="width: 75%;margin: 20px 0px;" src="/images/unavail-250x250.png">
							</div>
							
						</div>
						
						<div class="Mannequin">
							<h4>Color Chooser</h4>
							<p class="tip">Click a body part to change its color:</p>
							<div style="margin: 40px 0;">
								<div class="HeadDiv">
									<button type="button" id="Head" onclick="selectPart('Head');" class="Head" style="background-color: rgb(245, 205, 47);"></button>
								</div>
								<div class="TorsoDiv">
									<button type="button" id="RightArm" onclick="selectPart('RightArm');" class="RightArm" style="background-color: rgb(245, 205, 47);"></button>
									<button type="button" id="Torso" onclick="selectPart('Torso');" class="Torso" style="background-color: rgb(13, 105, 171);"></button>
									<button type="button" id="LeftArm" onclick="selectPart('LeftArm');" class="LeftArm" style="background-color: rgb(245, 205, 47);"></button>
								</div>
								<div class="LegDiv">
									<button type="button" id="LeftLeg" onclick="selectPart('LeftLeg');" class="LeftLeg" style="background-color: rgb(75, 151, 74);"></button>
									<button type="button" id="RightLeg" onclick="selectPart('RightLeg');" class="RightLeg" style="background-color: rgb(75, 151, 74);"></button>
								</div>
							</div>
						</div>
						
						<div class="Accoutrements">
							<div id="asset_wearing_template" style="display:none">
								<td class="Asset" valign="top">
									<div class="AssetThumbnail">
										<a href="" class="DeleteButtonOverlay">&nbsp;[ remove ]&nbsp;</a>
										<a id="AssetThumbnailLink" title="" href="" style="display:inline-block;cursor:pointer;">
											<img src="" border="0" alt="" style="width:110px; height: 110px;">
										</a>
									</div>
									<div class="AssetDetails">
										<div class="AssetName"><a href="Item.aspx?ID=">Name</a></div>
										<div class="AssetType"><span class="Label">Type:</span> <span class="Detail"><a href="Catalog.aspx?c=">Type</a></span></div>
										<div class="AssetCreator"><span class="Label">Creator:</span> <span class="Detail"><a href="User.aspx?ID=">Creator</a></span></div>
									</div>
								</td>
							</div>
							<h4>Currently Wearing</h4>
							<table id="WearingAssetsTable"><tr></tr><tr></tr></table>
							<div class="NoResults">You are not wearing any items from your wardrobe.</div>
							<div class="FooterPager">
								<a style="color:#ccc" onclick="return false;">First</a>
								<a style="color:#ccc" onclick="return false;">Previous</a>
								<a style="color:#ccc" onclick="return false;">1</a>
								<a style="color:#ccc" onclick="return false;">Next</a>
								<a style="color:#ccc" onclick="return false;">Last</a>
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
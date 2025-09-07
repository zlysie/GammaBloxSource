<?php
	session_start();
	
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/friending.php";

	$id = intval($_GET['id']) ?? null;

	if($id == 0 || $id == null) {
		die(header("Location: /"));
	}

	$user = User::FromID($id);

	if($user == null) {
		die(header("Location: /"));
	}

	$bandetails= $user->GetBanReason();
	if($bandetails == null) {
		die(header("Location: /"));
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" id="gamma-lambda-cam">
	<head>
		<title>GAMMA | Disabled Account</title>
		<link id="ctl00_Imports" rel="stylesheet" type="text/css" href="/CSS/AllCSS.css">
		<link id="ctl00_Favicon" rel="Shortcut Icon" type="image/ico" href="favicon.ico">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta http-equiv="Content-Language" content="en-us">
		<meta name="author" content="Zlysie">
		<meta name="description" content="GAMMA is a FREE (invite only) casual virtual world with fully constructible/desctructible 3D environments and immersive physics. Build, battle, chat, or just hang out.">
		<meta name="keywords" content="game, video game, building game, construction game, online game, LEGO game, LEGO, MMO, MMORPG, gammablox, gamma roblox, old roblox">
		<script src="/js/jquery.js"></script>
		<script src="/js/WebResource.js" type="text/javascript"></script>
		<script src="/js/Stuff.js"></script>
		<style>
			.ProfileLink {
				padding : 0 10px;
			}

			.ProfileLink > td {
				margin: 5px 0;
				display:block;
			}
			td {
				text-align: center;
			}
		</style>
	</head>
	<body>
		<form name="aspnetForm" method="post" action="NotApproved.aspx" id="aspnetForm">
			<div id="Container">
				<div id="AdvertisingLeaderboard">
				</div>
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_header_nonav.php"; ?>
				<div id="Body">
					<div style="margin: 150px auto 150px auto; width: 500px; border: black thin solid; padding: 22px;">
						<?php if(!$bandetails->terminated): ?>
						<h2>Banned for <?= $bandetails->GetBanDuration() ?></h2>
						<?php else: ?>
						<h2>Terminated</h2>
						<?php endif ?>
						<p>
							Our content monitors have determined that your behavior at GAMMA has been in violation of our Terms of Service.
							We will terminate your account if you do not abide by the rules.
						</p>
						<p>
							Issued by: <span style="font-weight: bold"><?= $bandetails->issuer->name ?></span><br>
							Reason: <span style="font-weight: bold"><?= $bandetails->reason ?></span><br>
							Reported: <span style="font-weight: bold"><?= $bandetails->issueddate->format("d/m/Y H:i:s A") ?></span>
						</p>
						<p>
							Moderator Note:<br>
							<span style="font-weight: bold"><?= $bandetails->message ?></span>
						</p>
						<?php if(!$bandetails->terminated): ?>
						<p>
							Please abide by the <a href="http://wiki.lambda.cam/wiki/index.php?title=Community_Guidelines">GAMMA Community Guidelines</a> so that GAMMA can be for all.
						</p>
						<div id="ctl00_cphRoblox_Panel3">
							<p>Your account has been disabled for <?= $bandetails->GetBanDuration() ?>. You may re-activate it after <span id="ctl00_cphRoblox_Label6"><?= $bandetails->enddate->format("d/m/Y H:i:s A") ?></span><br></p>
						</div>
						<?php else: ?>
						<div id="ctl00_cphRoblox_Panel3">
							<p>Your account has been terminated. You will not return.<br></p>
						</div>
						<?php endif ?>
					</div>
				</div>
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_footer.php"; ?>
			</div>
		</form>
	</body>
</html>
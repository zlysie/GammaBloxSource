<?php 
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	UserUtils::LockOutUserIfNotLoggedIn();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>GAMMA: A FREE Virtual World-Building Game with Avatar Chat, 3D Environments, and Physics</title>
		<link rel="stylesheet" type="text/css" href="/CSS/AllCSS.css">
		<link rel="Shortcut Icon" type="image/ico" href="/favicon.ico">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="author" content="GAMMA Corporation">
		<meta name="description" content="GAMMA is SAFE for kids! GAMMA is a FREE casual virtual world with fully constructible/desctructible environments and immersive physics. Build, battle, chat, or just hang out.">
		<meta name="keywords" content="game, video game, building game, contstruction game, online game, LEGO game, LEGO, MMO, MMORPG, virtual world, avatar chat">
		<meta name="robots" content="none"></head>
	<body>
		<form name="aspnetForm" method="post" action="LaunchGame.aspx" id="aspnetForm">
			<div id="Container">
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_header_nonav.php"; ?>
				<div id="Body">
					<div id="ctl00_cphGamma_BrowserMessage" align="center">
						<p>&nbsp;</p>
						<p>
							Gamma has detected that it cannot be started from within your browser. Instead,
							you will need to launch Gamma from your Windows Start Menu.
						</p>
						<p>
							If you have not installed Gamma then
							<a id="ctl00_cphGamma_HyperLink1" href="Default.aspx">click here</a>
						</p>
						<p><img id="ctl00_cphGamma_iGammaViaStartMenu" src="/images/GammaViaStartMenu.png" alt="GAMMA via START menu" border="0"></p>
						<p>&nbsp;</p>
					</div>
				</div>
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_footer.php"; ?>
			</div>
		</form>
	</body>
</html>
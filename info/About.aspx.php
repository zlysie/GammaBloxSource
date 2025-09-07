<?php 
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	UserUtils::LockOutUserIfNotLoggedIn();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" id="gamma-lambda-cam">
	<head>
		<title>About GAMMA</title>
		<link id="ctl00_Imports" rel="stylesheet" type="text/css" href="/CSS/AllCSS.css">
		<link id="ctl00_Favicon" rel="Shortcut Icon" type="image/ico" href="/favicon.ico">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta http-equiv="Content-Language" content="en-us">
		<style>
			body {transition: opacity ease-in 0.2s; } 
			body[unresolved] {opacity: 0; display: block; overflow: hidden; position: relative; } 
		</style>
	</head>
	<body>
		<form name="aspnetForm" method="post" action="About.aspx" id="aspnetForm">
			<div id="Container">
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_header.php"; ?>
				<div id="Body">
					<h2>About Us</h2>
					<p>
						Gamma is a passion project aiming to recreate the 2008 era of Roblox.
						This is a one man operation. Many sleepless nights and risk of failing exams for this :)
						Our project helps to seperate from now-a-days Roblox and its issues to give a room for a breather to have some genuine fun.
						Even if it means server-sided movement is involved (sorry US player :[) 
					</p>
					<p>
						Gamma is free to play and is intended for people who are 14 and up. We deeply value community feedback as we continue to make Gamma an enjoyable experience for all.
					</p>
					<p>For more information about Gamma, visit the News and Help sections.</p>
					<p>Please contact me on discord at @realoikmo.</p>
					</div>
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_footer.php"; ?>
			</div>
			<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/formvars.php"; ?>
		</form>
	</body>
</html>
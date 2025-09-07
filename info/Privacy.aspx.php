<?php 
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	UserUtils::LockOutUserIfNotLoggedIn();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" id="www-GAMMA-com">
	<head>
		<title>GAMMA Login</title>
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
					
			<br clear="all">
			<p><b>GAMMA Privacy Policy</b>
			</p><p>
				<b>I. The Information We Collect </b>
				<br>
				<br>
				At GAMMA, we actually don't carry any personal info about you. Yeah, 
				we may track ips in logs but we genuinely have no ways
				to know who you are as the ips aren't connected!</p>
				<p><b>II. How We (Don't) Use the Information</b>
				<br>
				<br>
				Most of the time those same logs are all but cleared after a couple of days.
				It's not fun being tracked, and we get that.
				<br>
				<br>
				<b>III. Cookies</b><br>
				<br>
				GAMMA uses a software technology called "cookies." Cookies are small text 
				files that we place in visitors' computer browsers to store their preferences. 
				Cookies themselves do not contain any personally identifiable information.
				<br>
				<br>
				<b>IV. Contact Us</b><br>
				<br>
				If you have any questions, comments, or concerns regarding our privacy policy 
				and/or practices, please contact us in the discord!
				<br>
				<br>
				<a href=""></a>
				<br>
				<br>
				Privacy Manager - gamma.lambda.cam
				<br> <!--footer--></p>
				</div>
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_footer.php"; ?>
			</div>
			<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/formvars.php"; ?>
		</form>
	</body>
</html>
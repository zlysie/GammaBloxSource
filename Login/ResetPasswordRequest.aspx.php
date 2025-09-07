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
		<meta name="description" content="GAMMA is SAFE for kids! GAMMA is a FREE casual virtual world with fully constructible/desctructible environments and immersive physics. Build, battle, chat, or just hang out."><meta name="keywords" content="game, video game, building game, contstruction game, online game, LEGO game, LEGO, MMO, MMORPG, virtual world, avatar chat">
		<meta name="robots" content="none">
		<script src="/js/WebResource.js" type="text/javascript"></script>
		<style>
			.Validators {
				color:red;
			}
		</style>
	</head>
	<body>
		<form name="aspnetForm" method="post" action="ResetPasswordRequest.aspx" id="aspnetForm">
			<div id="Container">
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_header_nonav.php"; ?>
				<div id="Body" style="margin-top: 15px;">
					<h3>Forgot your password or wish to change it?</h3>
					<p>
						We can send you an email to reset it. If you can't remember your username then
						just enter your email address.<br>
					</p>
					<p>
						Username or email:
						<input name="ctl00$cphRoblox$UserName" type="text" id="ctl00_cphRoblox_UserName">
					</p>
					<p></p>
					<table id="Table1">
						<tbody>
							<tr>
								<td><!-- Errors most likely --></td>
								<td width="140">
									<a id="ctl00_cphRoblox_LinkButtonResetPassword" href="javascript:__doPostBack('ctl00$cphRoblox$LinkButtonResetPassword','')">Reset password</a>
								</td>
								<td>
									<input name="ctl00$cphRoblox$hiddenEmail" type="hidden" id="ctl00_cphRoblox_hiddenEmail" value="An email with instructions has been sent to {0}">
								</td>
							</tr>
						</tbody>
					</table>
					<p></p>
				</div>
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_footer.php"; ?>
			</div>
			<script type="text/javascript">
			//<!--
			Roblox.Controls.Image.IE6Hack($get('ctl00_Image1'));S
			// -->
			</script>
			<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/formvars.php"; ?>
		</form>
	</body>
</html>
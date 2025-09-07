<?php 
	session_start();
	
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	
	$user = UserUtils::GetLoggedInUser();
	
	if(!isset($_SESSION["errors"])) {
		$_SESSION["errors"] = [];
	}
	
	if($user != null) {
		die(header("Location: /User.aspx"));
	} else {
		// Presence check
		if(isset($_POST['ctl00$cphRoblox$lRobloxLogin$UserName']) 
		&& isset($_POST['ctl00$cphRoblox$lRobloxLogin$Password']) 
		&& isset($_POST['ctl00$cphRoblox$lRobloxLogin$LoginButton'])) {
			
			$query_username = trim($_POST['ctl00$cphRoblox$lRobloxLogin$UserName']);
			$query_password = trim($_POST['ctl00$cphRoblox$lRobloxLogin$Password']);

			$error_check = false;

			if(strlen($query_username) == 0) {
				$error_check = true;
				array_push($_SESSION['errors'], "Username field was empty!<br>");
			}

			if(strlen($query_password) == 0) {
				$error_check = true;
				array_push($_SESSION['errors'], "Password field was empty!");
			}

			if(!$error_check) {
				if(UserUtils::VerifyUserFromDetails($query_username, $query_password) == null) {
					array_push($_SESSION['errors'], "User details were incorrect!");
				} else {
					die(header("Location: /"));
				}
			}
			
		}
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>GAMMA: A FREE Virtual World-Building Game with Avatar Chat, 3D Environments, and Physics</title>
		<link rel="stylesheet" type="text/css" href="/CSS/AllCSS.css">
		<link rel="Shortcut Icon" type="image/ico" href="/favicon.ico">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="author" content="Zlysie">
		<meta name="description" content="GAMMA is a FREE (invite only) casual virtual world with fully constructible/desctructible 3D environments and immersive physics. Build, battle, chat, or just hang out.">
		<meta name="keywords" content="game, video game, building game, construction game, online game, LEGO game, LEGO, MMO, MMORPG, gammablox, gamma roblox, old roblox">
		<script src="/js/WebResource.js" type="text/javascript"></script>
		<style>
			.Validators {
				color:red;
			}
		</style>
	</head>
	<body>
		<form name="aspnetForm" method="post" action="Default.aspx" id="aspnetForm">
			<div id="Container">
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_header_nonav.php"; ?>
				<div id="Body">
					<div id="FrameLogin" style="margin: 150px auto 150px auto; width: 500px; border: black thin solid; padding: 22px;">
						<div id="PaneNewUser">
							<h3>New User?</h3>
							<p>You need an account to play GAMMA.</p>
							<p>If you aren't a GAMMA member then fuck off!!!</p>
						</div>
						<div id="PaneLogin">
							<h3>Log In</h3>
							<div style="color:red">
								<?php 
									foreach($_SESSION['errors'] as $err) {
										echo $err;
									}
									$_SESSION['errors'] = [];
									unset($_SESSION['errors']);
								?>
							</div>
							<div class="AspNet-Login">
								<div class="AspNet-Login-UserPanel">
									<label for="ctl00_cphRoblox_lRobloxLogin_UserName" class="TextboxLabel"><em>U</em>ser Name:</label>
									<input type="text" id="ctl00_cphRoblox_lRobloxLogin_UserName" name="ctl00$cphRoblox$lRobloxLogin$UserName" value="" accesskey="u">&nbsp;
								</div>
								<div class="AspNet-Login-PasswordPanel">
									<label for="ctl00_cphRoblox_lRobloxLogin_Password" class="TextboxLabel"><em>P</em>assword:</label>
									<input type="password" id="ctl00_cphRoblox_lRobloxLogin_Password" name="ctl00$cphRoblox$lRobloxLogin$Password" value="" accesskey="p">&nbsp;
								</div>
								<div class="AspNet-Login-SubmitPanel">
									<input type="submit" value="Log In" id="ctl00_cphRoblox_lRobloxLogin_LoginButton" name="ctl00$cphRoblox$lRobloxLogin$LoginButton" onclick="__doPostBack(&quot;ctl00$cphRoblox$lRobloxLogin$LoginButton&quot;, &quot;&quot;)">
								</div>
								<div class="AspNet-Login-PasswordRecoveryPanel">
									<a href="ResetPasswordRequest.aspx" title="Password recovery">Forgot your password?</a>
								</div>
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
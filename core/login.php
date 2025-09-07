<?php 
	session_start();
	
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";

	$user = UserUtils::GetLoggedInUser();
	
	if(!isset($_SESSION["errors"])) {
		$_SESSION["errors"] = [];
	}
	
	if($user != null) {
		die(header("Location: /User.aspx"));
	} else {
		// Presence check
		if(isset($_POST['ctl00$cphGamma$lGammaLogin$UserName']) 
		&& isset($_POST['ctl00$cphGamma$lGammaLogin$Password']) 
		&& isset($_POST['ctl00$cphGamma$lGammaLogin$LoginButton'])) {
			
			$query_username = trim($_POST['ctl00$cphGamma$lGammaLogin$UserName']);
			$query_password = trim($_POST['ctl00$cphGamma$lGammaLogin$Password']);

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
					die(header("Location: ".$_SERVER['REQUEST_URI']));
				}
			}
			
		}
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>GAMMA - Login</title>
		<link rel="Shortcut Icon" type="image/ico" href="/favicon.ico">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="author" content="Zlysie">
		<meta name="description" content="Login this epic thing">
		<script src="/js/WebResource.js" type="text/javascript"></script>
		<style>
			.Validators {
				color:red;
			}
			@font-face {
    			font-family: 'Comic Sans MS Web';
				src: url('/CSS/COMIC.ttf');
			}

			body {
				font-family:'Comic Sans MS','Comic Sans MS Web',Verdana,sans-serif;
				background-image:url('/images/login.png');
				background-size: cover;
			}

			#FrameLogin {
				margin: 150px auto 150px auto;
				width: 300px;
				border: white thick solid;
				padding: 22px;
				background: #00000096;
				color: white;
				text-align: center;
			}
		</style>
	</head>
	<body>
		<form name="aspnetForm" method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" id="aspnetForm">
			<div id="Container">
				<div id="Body">
					<div id="FrameLogin">
						<div id="PaneLogin">
							<img src="/images/logo.png">
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
									<label for="ctl00_cphGamma_lGammaLogin_UserName" class="TextboxLabel">Username</label><br>
									<input type="text" name="ctl00$cphGamma$lGammaLogin$UserName" value="" accesskey="u">&nbsp;
								</div>
								<div class="AspNet-Login-PasswordPanel">
									<label for="ctl00_cphGamma_lGammaLogin_Password" class="TextboxLabel">Password</label><br>
									<input type="password" name="ctl00$cphGamma$lGammaLogin$Password" value="" accesskey="p">&nbsp;
								</div><br>
								<div class="AspNet-Login-SubmitPanel">
									<input type="submit" value="Log In" name="ctl00$cphGamma$lGammaLogin$LoginButton" onclick="__doPostBack(&quot;ctl00$cphGamma$lGammaLogin$LoginButton&quot;, &quot;&quot;)">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/formvars.php"; ?>
		</form>
	</body>
</html>
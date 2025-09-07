<?php
	session_start();
	
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/transactionutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/splasher.php";

	$inv_key = isset($_GET['thatkey']) ? $_GET['thatkey'] : null;

	// Reject and send to login page.
	if(!UserUtils::IsInviteKeyValid($inv_key)) {
		die(header("Location: /Login/Default.aspx"));
	}

	$user = UserUtils::GetLoggedInUser();
	
	$_SESSION["errors"] = [
		"username" => [],
		"password" => [],
	];

	$blocked_usernames = [
		"shedletsky",
		"mattdusek",
		"erikcassel",
		"stickmasterluke",
		"builderman",
		"mrdoombringer",
		"Telamon",
		"reesemcblox",
		"mse6",
		"spirit",
		"1x1x1x1",
		"roblox",
		"davidbaszucki",
		"dracoswordmaster",
		"clockwork",
		"007n"
	];
	
	if($user != null) {
		die(header("Location: /User.aspx"));
	} else {
		if(isset($_POST['ctl00$cphRoblox$rblAgeGroup']) && isset($_POST['ctl00$cphRoblox$UserName']) && 
		isset($_POST['ctl00$cphRoblox$Password']) && isset($_POST['ctl00$cphRoblox$TextBoxPasswordConfirm']) && 
		isset($_POST['ctl00$cphRoblox$rblChatMode']) && isset($_POST['ctl00$cphRoblox$ButtonCreateAccount'])) {
			$query_age_group = $_POST['ctl00$cphRoblox$rblAgeGroup'];
			$query_username = trim($_POST['ctl00$cphRoblox$UserName']);
			$query_password = $_POST['ctl00$cphRoblox$Password'];
			$query_passwordconfirm = $_POST['ctl00$cphRoblox$TextBoxPasswordConfirm'];
			$query_chat_mode = $_POST['ctl00$cphRoblox$rblChatMode'];
			$query_invite_key = trim($inv_key);

			$stmt_blox_usernames = $con->prepare('SELECT * FROM users WHERE username = ?');
			$stmt_blox_usernames->bind_param('s', $query_username);
			$stmt_blox_usernames->execute();
			$stmt_blox_usernames->store_result();
			$names = $stmt_blox_usernames->num_rows;

			$errorcheck = false;

			$_SESSION["errors"] = [
				"username" => [],
				"password" => [],
			];

			if(empty($query_username)) {
				$errorcheck = true;
				array_push($_SESSION["errors"]['username'], "No username was provided!<br>");
			} else {

				if(!preg_match('/^[A-z0-9]*$/', $query_username)) {
					$errorcheck = true;
					array_push($_SESSION["errors"]['username'], "Username was not valid!<br>");
				} else {
					if($names > 0) {
						$errorcheck = true;
						array_push($_SESSION["errors"]['username'], "Username already in use!<br>");
					}

					if(strlen($query_username) > 20) {
						$errorcheck = true;
						array_push($_SESSION["errors"]['username'], "Username was too long!<br>");
					}

					if(strlen($query_username) < 3) {
						$errorcheck = true;
						array_push($_SESSION["errors"]['username'], "Username was too short!<br>");
					}

					if(in_array(strtolower($query_username), $blocked_usernames)) {
						$errorcheck = true;
						array_push($_SESSION["errors"]['username'], "Username is blocked!<br>");
					}
				}
			}
			
			if(empty($query_password) == true) {
				$errorcheck = true;
				array_push($_SESSION["errors"]['password'], "Password fields were empty!<br>");
			} else {
				if(strlen($query_password) < 7) {
					$errorcheck = true;
					array_push($_SESSION["errors"]['password'], "Password was too short!<br>");
				}

				if(strcmp($query_password, $query_passwordconfirm) != 0) {
					$errorcheck = true;
					array_push($_SESSION["errors"]['password'], "Password was not equal!<br>");
				}
			}
			
			if(!$errorcheck) {
				$password = password_hash($query_password, PASSWORD_DEFAULT);
				$stmt_insertuser = $con->prepare('INSERT INTO `users`(`username`, `password`, `chat_type`) VALUES (?, ?, ?)');
				$stmt_insertuser->bind_param('ssi', $query_username, $password, $query_chat_mode);
				$security = UserUtils::GetSecurity();
				$stmt_getcount = $con->prepare("SELECT `AUTO_INCREMENT` FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'gammablox' AND TABLE_NAME = 'users';");
				$stmt_getcount->execute();
				$result = $stmt_getcount->get_result();
				$id = $result->fetch_assoc()['AUTO_INCREMENT'];

				if($stmt_insertuser->execute()) {
					UserUtils::SetUserSessionStuff(User::FromID($id));
					UserUtils::SetUserCookies($security);
					
					$stmt_removekey = $con->prepare('DELETE FROM invite_keys WHERE inv_key = ?;');
					$stmt_removekey->bind_param('s', $query_invite_key);
					$stmt_removekey->execute();
					
					TransactionUtils::GiftTicketsToUser($id, 5);

					$stmt_getcount = $con->prepare("SELECT `AUTO_INCREMENT` FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'gammablox' AND TABLE_NAME = 'assets';");
					$stmt_getcount->execute();
					$result = $stmt_getcount->get_result();
					$asset_id = $result->fetch_assoc()['AUTO_INCREMENT']; 
					
					$mediadir = $_SERVER['DOCUMENT_ROOT']."/../gamma-assets"; // to make the website non-platform specific
					copy("$mediadir/place/StartPlace.rbxl", "$mediadir/$asset_id");
					copy("$mediadir/place/Place_120x120.png", "$mediadir/thumbs/$asset_id"."_120");
					copy("$mediadir/place/Place_230x230.png", "$mediadir/thumbs/$asset_id"."_230");
					copy("$mediadir/place/Place_420x230.png", "$mediadir/thumbs/$asset_id"."_420");

					// insert asset into table
					$stmt_insertasset = $con->prepare('INSERT INTO `assets`(`asset_id`, `asset_name`, `asset_description`, `asset_creator`, `asset_type`, `asset_status`) VALUES (?, ?, "", ?, 9, 0)');
					$name = str_replace($blockedchars, '', $query_username);
					$name = $name."'s Place";
					$stmt_insertasset->bind_param('isi', $asset_id, $name, $id);
					$stmt_insertasset->execute();

					die(header("Location: /User.aspx"));
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
		<form name="aspnetForm" method="post" action="New.aspx?thatkey=<?= $inv_key ?>" id="aspnetForm">
			<div id="Container">
				<div id="Header">
					<div id="Banner" style="font-family: sans-serif;">
						<div id="Options">
							<div id="Authentication">
							</div>
							<div id="Settings">
							</div>
						</div>
						<div id="Logo">
							<a id="ctl00_rbxImage_Logo" title="GAMMA" href="/Default.aspx" style="display:inline-block;height:70px;width:267px;cursor:pointer;">
								<img src="/images/logo.png" border="0" id="img" alt="GAMMA" style="margin-top:-5px; height:69px;">
							</a>
						</div>
					</div>
					<?php Splasher::GenerateSplashHeader(); ?>
				</div>
				<div id="Body">
					<div id="Registration">
						<div id="ctl00_cphRoblox_upAccountRegistration">
							<h2>Sign Up and Play</h2>
							<h3>Step 1 of 2: Create Account</h3>
							<div id="EnterAgeGroup">
								<fieldset title="Provide your age-group">
									<legend>Provide your age-group</legend>
										<div class="Suggestion">
											This will help us to customize your experience.  Users under 13 years will only be shown pre-approved images.
										</div>
									<div class="AgeGroupRow">
										<span id="ctl00_cphRoblox_rblAgeGroup">
											<input id="ctl00_cphRoblox_rblAgeGroup_0" type="radio" name="ctl00$cphRoblox$rblAgeGroup" value="1" checked="checked" tabindex="5">
											<label for="ctl00_cphRoblox_rblAgeGroup_0">Under 13 years</label><br>
											<input id="ctl00_cphRoblox_rblAgeGroup_1" type="radio" name="ctl00$cphRoblox$rblAgeGroup" value="2" tabindex="5">
											<label for="ctl00_cphRoblox_rblAgeGroup_1">13 years or older</label>
										</span>
									</div>
								</fieldset>
							</div>
							<div id="EnterUsername">
								<fieldset title="Choose a name for your GAMMA character">
									<legend>Choose a name for your GAMMA character</legend>
									<div class="Suggestion">
										Use 3-20 alphanumeric characters: A-Z, a-z, 0-9, no spaces
									</div>
									<div class="Validators">
										<div>
											<?php 
											foreach($_SESSION['errors']['username'] as $err) {
												echo $err;
											}
											?>
										</div>
									</div>
									<div class="UsernameRow">
										<label for="ctl00_cphRoblox_UserName" id="ctl00_cphRoblox_UserNameLabel" class="Label">Character Name:</label>&nbsp;
										<input name="ctl00$cphRoblox$UserName" type="text" id="ctl00_cphRoblox_UserName" tabindex="1" maxlength="20" class="TextBox" required>
									</div>
								</fieldset>
							</div>
							<div id="EnterPassword">
								<fieldset title="Choose your GAMMA password">
									<legend>Choose your GAMMA password</legend>
									<div class="Suggestion">
										4-10 characters, no spaces
									</div>
									<div class="Validators">
										<div>
											<?php 
											foreach($_SESSION['errors']['password'] as $err) {
												echo $err;
											}
											?>
										</div>
									</div>
									<div class="PasswordRow">
										<label for="ctl00_cphRoblox_Password" id="ctl00_cphRoblox_LabelPassword" class="Label">Password:</label>&nbsp;
										<input name="ctl00$cphRoblox$Password" type="password" id="ctl00_cphRoblox_Password" tabindex="2" class="TextBox">
									</div>
									<div class="ConfirmPasswordRow">
										<label for="ctl00_cphRoblox_TextBoxPasswordConfirm" id="ctl00_cphRoblox_LabelPasswordConfirm" class="Label">Confirm Password:</label>&nbsp;
										<input name="ctl00$cphRoblox$TextBoxPasswordConfirm" type="password" id="ctl00_cphRoblox_TextBoxPasswordConfirm" tabindex="3" class="TextBox">
									</div>
								</fieldset>
							</div>
							<div id="EnterChatMode">
								<fieldset title="Choose your chat mode">
									<legend>Choose your chat mode</legend>
									<div class="Suggestion">
										All in-game chat is subject to profanity filtering and moderation.  For enhanced chat safety, choose SuperSafe Chat; only chat from pre-approved menus will be shown to you.
									</div>
									<div class="ChatModeRow">
										<span id="ctl00_cphRoblox_rblChatMode">
											<input id="ctl00_cphRoblox_rblChatMode_0" type="radio" name="ctl00$cphRoblox$rblChatMode" value="false" checked="checked" tabindex="6">
											<label for="ctl00_cphRoblox_rblChatMode_0">Safe Chat</label><br>
											
											<input id="ctl00_cphRoblox_rblChatMode_1" type="radio" name="ctl00$cphRoblox$rblChatMode" value="true" tabindex="6">
											<label for="ctl00_cphRoblox_rblChatMode_1">SuperSafe Chat</label>
										</span>
									</div>
								</fieldset>
							</div>
							<div class="Confirm">
								<input type="submit" name="ctl00$cphRoblox$ButtonCreateAccount" value="Register" id="ctl00_cphRoblox_ButtonCreateAccount" tabindex="5" class="BigButton">
							</div>
						</div>
					</div>
					<div id="Sidebars">
						<div id="AlreadyRegistered">
							<h3>Already Registered?</h3>
							<p>If you just need to login, go to the <a id="ctl00_cphRoblox_HyperLinkLogin" href="Default.aspx?ReturnUrl=%2f">Login</a> page.</p>
							<p>If you have already registered but you still need to download the game installer, go directly to <a id="ctl00_cphRoblox_HyperLinkDownload" href="/Install/Default.aspx?ReturnUrl=%2f">download</a>.</p>
						</div>
						<div id="TermsAndConditions">
							<h3>Terms &amp; Conditions</h3>
							<p>Registration does not provide any guarantees of service. See our <a id="ctl00_cphRoblox_HyperLinkToS" href="/Info/TermsOfService.aspx?layout=null" target="_blank">Terms of Service</a> and <a id="ctl00_cphRoblox_HyperLinkEULA" href="/Info/EULA.htm" target="_blank">Licensing Agreement</a> for details.</p>
							<p>GAMMA will not share your email address with 3rd parties. See our <a id="ctl00_cphRoblox_HyperLinkPrivacy" href="/Info/Privacy.aspx?layout=null" target="_blank">Privacy Policy</a> for details.</p>
						</div>
					</div>
				</div>
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_footer.php"; ?>
			</div>
			<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/formvars.php"; ?>
		</form>
	</body>
</html>
<?php 
	unset($_SESSION['errors']);
?>
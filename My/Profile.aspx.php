<?php
	session_start();
	
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
	UserUtils::LockOutUserIfNotLoggedIn();

	$user = UserUtils::GetLoggedInUser();
	global $user;

	/*
	[ctl00$cphRoblox$rblAgeGroup] => 1
    [ctl00$cphRoblox$rblChatMode] => false
    [ctl00$cphRoblox$rblBlurb] => 
    [__EVENTTARGET] => ctl00$cphRoblox$rbxProfile$Submit
	*/

	$blockedchars = array('𒐫', '‮');

	if(isset($_POST['__EVENTTARGET']) && $_POST['__EVENTTARGET']) {
		if(isset($_POST['ctl00$cphRoblox$rblAgeGroup']) && isset($_POST['ctl00$cphRoblox$rblChatMode']) && isset($_POST['ctl00$cphRoblox$rblBlurb'])) {
			$age_group = trim($_POST['ctl00$cphRoblox$rblAgeGroup']);
			$chat_mode = trim($_POST['ctl00$cphRoblox$rblChatMode']);
			$blurb = trim($_POST['ctl00$cphRoblox$rblBlurb']);
			$blurb = str_replace($blockedchars, '', $blurb);

			if(strlen($age_group) != 0) {
				$age_group = intval($age_group);
			} else {
				$age_group = 1;
			}

			if(strlen($chat_mode) != 0) {
				if($chat_mode == "false") {
					$chat_mode = false;
				} else {
					$chat_mode = true;
				}
			} else {
				$chat_mode = true;
			}

			$stmt_update = $con->prepare('UPDATE `users` SET `blurb` = ?, `chat_type` = ? WHERE `id` = ?');
			$stmt_update->bind_param('sii', $blurb, $chat_mode, $user->id);
			$stmt_update->execute();
			$user->blurb = $blurb;
		}
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>GAMMA - Profile</title>
		<link rel="stylesheet" type="text/css" href="/CSS/AllCSS.css">
		<link rel="Shortcut Icon" type="image/ico" href="/favicon.ico">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="author" content="Zlysie">
		<meta name="description" content="GAMMA is a FREE (invite only) casual virtual world with fully constructible/desctructible 3D environments and immersive physics. Build, battle, chat, or just hang out.">
		<meta name="keywords" content="game, video game, building game, construction game, online game, LEGO game, LEGO, MMO, MMORPG, gammablox, gamma roblox, old roblox">
		<meta name="robots" content="none">
		<script src="/js/MicrosoftAjax.js" type="text/javascript"></script>
		<script src="/js/MicrosoftAjaxWebForms.js" type="text/javascript"></script>
		<script src="/js/WebResource.js" type="text/javascript"></script>
		<style>
			.Validators {
				color:red;
			}
		</style>
	</head>
	<body>
		<form name="aspnetForm" method="post" action="Profile.aspx" id="aspnetForm">
			<div id="Container">
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_header.php"; ?>
				<div id="Body">
					<div id="EditProfileContainer">
					<h2>Edit Profile</h2>
						<div id="ctl00_cphRoblox_upAccountRegistration">
							<div id="AgeGroup">
								<fieldset title="Provide your age-group">
									<legend>Update your age-group</legend>
										<div class="Suggestion">
											This is used to customize your GAMMA experience.
										</div>
									<div class="AgeGroupRow">
										<span id="ctl00_cphRoblox_rblAgeGroup">
											<input id="ctl00_cphRoblox_rblAgeGroup_0" type="radio" name="ctl00$cphRoblox$rblAgeGroup" value="1" tabindex="5">
											<label for="ctl00_cphRoblox_rblAgeGroup_0">Under 13 years</label><br>
											<input id="ctl00_cphRoblox_rblAgeGroup_1" type="radio" name="ctl00$cphRoblox$rblAgeGroup" value="2" checked="checked" tabindex="5">
											<label for="ctl00_cphRoblox_rblAgeGroup_1">13 years or older</label>
										</span>
									</div>
								</fieldset>
							</div>
							<div id="ChatMode">
								<fieldset title="Update your chat mode">
									<legend>Update your chat mode</legend>
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
							<div id="ResetPassword">
								<fieldset title="Update your chat mode">
									<legend>Change your password</legend>
									<div class="Suggestion">
										Click the button below to change your password.
									</div>
									<div class="ResetPasswordRow" style="text-align: center;margin: 10px 0 10px 10px;">
										<a href="">Change Password</a>
									</div>
								</fieldset>
							</div>
							<div id="Blurb">
								<fieldset title="Update your chat mode">
									<legend>Update your personal blurb</legend>
									<div class="Suggestion">
										Describe yourself here (max. 1000 characters). Make sure not to provide any details that can be used to identify you outside GAMMA.
									</div>
									<div class="BlurbRow" style="text-align: center;margin: 10px 0 10px 10px;">
										<textarea name="ctl00$cphRoblox$rblBlurb" max="1000" class="MultilineTextBox"><?= $user->blurb; ?></textarea>
									</div>
								</fieldset>
							</div>
							
							<div class="Buttons">
								<button class="Button" onclick="__doPostBack('ctl00$cphRoblox$rbxProfile$Submit','');">Update</button>
								<button class="Button" onclick="window.location.href='/User.aspx'">Cancel</button>
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
<?php 
	unset($_SESSION['errors']);
?>
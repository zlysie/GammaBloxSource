<?php 
	session_start();
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/friending.php";
	UserUtils::LockOutUserIfNotLoggedIn();
	
	$user = UserUtils::GetLoggedInUser();

	$blockedchars = array('𒐫', '‮', '﷽', '𒈙', '⸻ ', '꧅');

	$recipient_searchid = intval($_GET['RecipientID']);
	$recipient = User::FromID($recipient_searchid);

	$invalid_recipient = $recipient == null;

	if($recipient->id == $user->id) {
		$invalid_recipient = true;
	}

	function sendMessage($recipient, $subject, $body) {
		$user = UserUtils::GetLoggedInUser();
		$blockedchars = array('𒐫', '‮', '﷽', '𒈙', '⸻ ', '꧅');
		if($user != null) {
			$subject = trim(str_replace($blockedchars, '', $subject));
			$body = trim(str_replace($blockedchars, '', $body));

			if(strlen($subject) > 0 && strlen($body) > 0) {
				include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
				$stmt_sendmessage = $con->prepare("INSERT INTO `messages`(`message_sender`, `message_recipient`, `message_subject`, `message_content`, message_friendreq) VALUES (?, ?, ?, ?, 1)");
				$stmt_sendmessage->bind_param("iiss", $user->id, $recipient, $subject, $body);
				$stmt_sendmessage->execute();
			}
		}
	}

	function sendFriendRequest(?int $recipient, ?string $subject, ?string $body) {
		sendMessage($recipient, $subject, $body);
		FriendUtils::addFriend(UserUtils::GetLoggedInUser()->id, $recipient);
	}

	$sent = false;
	if(isset($_POST['__EVENTTARGET']) && $_POST['__EVENTTARGET'] == 'ctl00$cphRoblox$lbSend' && isset($_POST['ctl00$cphRoblox$rbxMessageEditor$txtSubject']) && isset($_POST['ctl00$cphRoblox$rbxMessageEditor$txtBody'])) {
		$subject = trim($_POST['ctl00$cphRoblox$rbxMessageEditor$txtSubject']);
		$subject = str_replace(array("\r", "\n"), ' ', $subject);
		$subject = str_replace("<", "&lt;", str_replace(">", "&gt;", $subject));
		if(strlen($subject) == 0) {
			$subject = "Friend Request";
		}
		$body = trim($_POST['ctl00$cphRoblox$rbxMessageEditor$txtBody']);
		$body = str_replace("<", "&lt;", str_replace(">", "&gt;", $body));
		if(strlen($body) == 0) {
			$body = $user->name." would like to be friends with you!";
		}
		sendFriendRequest($recipient->id, $subject, $body);
		$sent = true;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" id="gamma-lambda-cam">
	<head>
		<title>GAMMA - Friend Invitation</title>
		<link id="ctl00_Imports" rel="stylesheet" type="text/css" href="/CSS/AllCSS.css">
		<link id="ctl00_Favicon" rel="Shortcut Icon" type="image/ico" href="/favicon.ico">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta http-equiv="Content-Language" content="en-us"><meta name="author" content="Zlysie">
		<meta name="description" content="GAMMA is a FREE (invite only) casual virtual world with fully constructible/desctructible 3D environments and immersive physics. Build, battle, chat, or just hang out.">
		<meta name="keywords" content="game, video game, building game, construction game, online game, LEGO game, LEGO, MMO, MMORPG, gammablox, gamma roblox, old roblox">
		<script src="/js/WebResource.js" type="text/javascript"></script>
		<script src="/js/jquery.js" type="text/javascript"></script>
	</head>
	<body>
		<form name="aspnetForm" method="post" action="FriendInvitation.aspx?<?=  $_SERVER['QUERY_STRING'] ?>" id="aspnetForm">
			<div id="Container">
				<div id="AdvertisingLeaderboard"></div>
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_header.php"; ?>
				<div id="Body">
				<?php if(!$invalid_recipient && !$sent): ?>
				<div id="MessageContainer">
					<h3>Your Friend Request</h3>
					<div id="MessageReaderContainer" style="margin-left: 0px; width: 715px; float: left;">
					<div style="width: 690px; float: left; padding: 10px;border: solid 2px Gray;">
						<div id="ctl00_cphRoblox_pFriendInvitation">
							<div id="ctl00_cphRoblox_pMessageEditor">
							<br>
								<div id="ctl00_cphRoblox_MessageEditorControlContainer">
									<div class="MessageEditor">
									<table width="100%">
										<tr valign="top">
											<td style="width: 12em">
												<div id="From">	
													<span class="Label"><span id="ctl00_cphRoblox_rbxMessageEditor_lblFrom">From:</span></span> 
													<span class="Field"><span id="ctl00_cphRoblox_rbxMessageEditor_lblAuthor"><?= $user->name ?></span></span>
												</div>
												<div id="To">
													<span class="Label"><span id="ctl00_cphRoblox_rbxMessageEditor_lblTo">Send To:</span></span> 
													<span class="Field"><span id="ctl00_cphRoblox_rbxMessageEditor_lblRecipient"><?= $recipient->name?></span></span>
												</div>
											</td>
											<td style="padding: 0 24px 6px 12px">
												<span id="ctl00_cphRoblox_rbxMessageEditor_CustomValidatorFloodCheck" style="color:red;display:none;">You have reached your send limit.  Please wait a few moments to re-send.</span>
												<div id="Subject">
													<div class="Label">
													<label for="ctl00_cphRoblox_rbxMessageEditor_txtSubject" id="ctl00_cphRoblox_rbxMessageEditor_lblSubject">Subject:</label></div>
													<div class="Field">
													<input name="ctl00$cphRoblox$rbxMessageEditor$txtSubject" type="text" value="Friend Request" id="ctl00_cphRoblox_rbxMessageEditor_txtSubject" class="TextBox" style="width:100%;" /></div>
												</div>
												<div class="Body">
													<div class="Label"><label for="ctl00_cphRoblox_rbxMessageEditor_txtBody" id="ctl00_cphRoblox_rbxMessageEditor_lblBody">Message:</label></div>
													<textarea name="ctl00$cphRoblox$rbxMessageEditor$txtBody" rows="2" cols="20" id="ctl00_cphRoblox_rbxMessageEditor_txtBody" class="MultilineTextBox" style="height:300px;width:100%;"></textarea>
													<div class="Validators"><div></div></div>
												</div>
											</td>
										</tr>
									</table>
								</div>
							</div>
						</div>
					</div>
					</div>
					<div class="Buttons" style="text-align: right; float: right;margin-top: 15px;">
									<a id="ctl00_cphRoblox_lbSend" class="Button" href="javascript:__doPostBack(&quot;ctl00$cphRoblox$lbSend&quot;, &quot;&quot;)">Send</a>
								</div>
					</div>
				</div>
				<?php endif ?>
				<?php if($invalid_recipient): ?>
				<div id="MessageContainer">
					<h3>Your Friend Request</h3>
					<div style="width: 690px; float: left; padding: 10px;border: 2px solid gray;">
						<div id="ctl00_cphRoblox_pConfirmation">
							<div style="margin-bottom: 10px;">
								<span id="ctl00_cphRoblox_lConfirmationMessage">Erm who the hell is this user you are trying to send this request to???</span>
							</div>
							<a href="/My/Inbox.aspx" class="Button">Continue</a>
						</div>
					</div>
				</div>
				<?php endif ?>
				<?php if($sent): ?>
				<div id="MessageContainer">
					<div class="StandardBoxHeader" style="width: 700px; float: left;">
						<h3>Your Friend Request</h3>
					</div>
					<div style="width: 690px; float: left; padding: 10px;border: 2px solid gray;">
						<div id="ctl00_cphRoblox_pConfirmation">
							<div style="margin-bottom: 10px;">
								<span id="ctl00_cphRoblox_lConfirmationMessage">Your friend invitation has been sent to <?= $recipient->name ?>.</span>
							</div>
							<a href="/User.aspx?ID=<?= $recipient->id ?>" class="Button">Continue</a>
						</div>
					</div>
				</div>
				<?php endif ?>
				<div style="clear:both"></div>
				<div style="clear:both"></div>
				<div style="clear:both"></div>
				</div>
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_footer.php"; ?>
			</div>
			<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/formvars.php"; ?>
		</form>
	</body>
</html>
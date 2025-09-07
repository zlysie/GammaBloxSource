<?php
	session_start();
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/friending.php";
	UserUtils::LockOutUserIfNotLoggedIn();
	
	$user_details = UserUtils::GetLoggedInUser();

	function sendMessage($recipient, $subject, $body) {
		$user = UserUtils::GetLoggedInUser();
		$blockedchars = array('𒐫', '‮', '﷽', '𒈙', '⸻ ', '꧅');
		if($user != null) {
			$subject = trim(str_replace($blockedchars, '', $subject));
			$body = trim(str_replace($blockedchars, '', $body));

			if(strlen($subject) > 0 && strlen($body) > 0) {
				include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
				$stmt_sendmessage = $con->prepare("INSERT INTO `messages`(`message_sender`, `message_recipient`, `message_subject`, `message_content`) VALUES (?, ?, ?, ?)");
				$stmt_sendmessage->bind_param("iiss", $user->id, $recipient, $subject, $body);
				$stmt_sendmessage->execute();
			}
		}
	}

	function getMessageFromID(?int $messageID): array|null {
		include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
		$stmt_getmessage = $con->prepare('SELECT * FROM `messages` WHERE `message_id` = ?');
		$stmt_getmessage->bind_param('s', $messageID);
		$stmt_getmessage->execute();
		$result = $stmt_getmessage->get_result();
		$num_rows = $result->num_rows;
		if($num_rows == 1) {
			$row = $result->fetch_assoc();
			$row['message_subject'] = str_replace("<", "&lt;", str_replace(">", "&gt;", $row['message_subject']));
			$row['message_content'] = str_replace("<", "&lt;", str_replace(">", "&gt;", $row['message_content']));
			return $row;
		}

		return null;
	}

	if(isset($_POST['__EVENTTARGET']) && strcmp($_POST['__EVENTTARGET'],'ctl00$ctl00$cphRoblox$cphMyRobloxContent$lbCancel') == 0) {
		echo "<script>window.location.href=\"/My/Inbox.aspx\"</script>";
		exit();
	}

	if(isset($_GET['MessageID'])) {
		$mode = "read";
		$messageID = intval($_GET['MessageID']);
		$message = getMessageFromID($messageID);
		if($message['message_recipient'] != $user_details->id) {
			$error = "You are not authorised to read this message!";
		}
		$recipientdata = User::FromID($message['message_recipient']);
		$senderdata = User::FromID($message['message_sender']);

		$sent = false;
		if(!isset($error) && isset($_POST['__EVENTTARGET']) && ($_POST['__EVENTTARGET'] == 'ctl00$ctl00$cphRoblox$cphMyRobloxContent$lbSend' || $_POST['__EVENTTARGET'] == 'ctl00$ctl00$cphRoblox$cphMyRobloxContent$lbSendAndDelete') && isset($_POST['ctl00$ctl00$cphRoblox$cphMyRobloxContent$rbxMessageEditor$txtSubject']) && isset($_POST['ctl00$ctl00$cphRoblox$cphMyRobloxContent$rbxMessageEditor$txtBody'])) {
			$subject = trim($_POST['ctl00$ctl00$cphRoblox$cphMyRobloxContent$rbxMessageEditor$txtSubject']);
			$subject = str_replace(array("\r", "\n"), ' ', $subject);
			$subject = str_replace("<", "&lt;", str_replace(">", "&gt;", $subject));
			if(strlen($subject) == 0) {
				$subject = "Message from ".$recipientdata->name;
			}
			$body = trim($_POST['ctl00$ctl00$cphRoblox$cphMyRobloxContent$rbxMessageEditor$txtBody']);
			$body = str_replace("<", "&lt;", str_replace(">", "&gt;", $body));
			if(strlen($body) == 0) {
				$error = "You cannot have a message without a body!";
			} else {
				sendMessage($senderdata->id, $subject, $body);
				$sent = true;
			}
			if($_POST['__EVENTTARGET'] == 'ctl00$ctl00$cphRoblox$cphMyRobloxContent$lbSendAndDelete') {
				$stmt_updatearchivedstate = $con->prepare('DELETE FROM `messages` WHERE `message_id` = ?');
				$stmt_updatearchivedstate->bind_param('i', $messageID);
				$stmt_updatearchivedstate->execute();
				die(header("Location: /My/Inbox.aspx"));
			}
		}

		$date = new DateTime(datetime: $message['message_timesent']);
		$reply = isset($_POST['__EVENTTARGET']) && (strcmp($_POST['__EVENTTARGET'],'ctl00$cphRoblox$cphMyRobloxContent$lbReply') == 0 || strcmp($_POST['__EVENTTARGET'],'ctl00$ctl00$cphRoblox$cphMyRobloxContent$lbReply') == 0);
		if(!isset($error)) {
			$stmt_updatearchivedstate = $con->prepare('UPDATE `messages` SET `message_read` = 1 WHERE `message_id` = ? AND `message_recipient` = ?');
			$stmt_updatearchivedstate->bind_param('ii', $messageID, $user_details->id);
			$stmt_updatearchivedstate->execute();

			if(isset($_POST['__EVENTTARGET']) && strcmp($_POST['__EVENTTARGET'],'ctl00$ctl00$cphRoblox$cphMyRobloxContent$lbDelete') == 0) {
				$stmt_updatearchivedstate = $con->prepare('DELETE FROM `messages` WHERE `message_id` = ?');
				$stmt_updatearchivedstate->bind_param('i', $messageID);
				$stmt_updatearchivedstate->execute();
				die(header("Location: /My/Inbox.aspx"));
			}
		}
	} else if(isset($_GET['RecipientID'])) {
		$mode = "write";
		$recipientID = intval($_GET['RecipientID']);
		$recipientdata = User::FromID($recipientID);
		$senderdata = UserUtils::GetLoggedInUser();
		/*if($recipientID == $user_details->id) {
			$error = "Don't try to message yourself!";
		}*/
		if($recipientdata == null) {
			$error = "This user does not exist!";
		}

		$sent = false;
		if(!isset($error) && isset($_POST['__EVENTTARGET']) 
			&& $_POST['__EVENTTARGET'] == 'ctl00$cphRoblox$lbSend'
			&& isset($_POST['ctl00$cphRoblox$rbxMessageEditor$txtSubject'])
			&& isset($_POST['ctl00$cphRoblox$rbxMessageEditor$txtBody'])) {

			$subject = trim($_POST['ctl00$cphRoblox$rbxMessageEditor$txtSubject']);
			$subject = str_replace(array("\r", "\n"), ' ', $subject);
			$subject = str_replace("<", "&lt;", str_replace(">", "&gt;", $subject));
			if(strlen($subject) == 0) {
				$subject = "Message from ".$senderdata->name;
			}
			$body = trim($_POST['ctl00$cphRoblox$rbxMessageEditor$txtBody']);
			$body = str_replace("<", "&lt;", str_replace(">", "&gt;", $body));
			if(strlen($body) == 0) {
				$error = "You cannot have a message without a body!";
			} else {
				sendMessage($recipientID, $subject, $body);
				$sent = true;

				if(!empty($_POST) && $_SERVER['REQUEST_METHOD'] == 'POST'){
					unset($_POST);
				}
			}
		}
	} else {
		header("Location: /My/Inbox.aspx");
		die();
	}

	if(isset($_POST['__EVENTTARGET']) && strcmp($_POST['__EVENTTARGET'],'ctl00$ctl00$cphRoblox$cphMyRobloxContent$lbRejectFriend') == 0) {
		FriendUtils::removeFriend($recipientdata->id, $senderdata->id);
		$stmt_updatearchivedstate = $con->prepare('DELETE FROM `messages` WHERE `message_id` = ?');
		$stmt_updatearchivedstate->bind_param('i', $messageID);
		$stmt_updatearchivedstate->execute();
		exit(header("Location: /My/Inbox.aspx"));
	}

	if(isset($_POST['__EVENTTARGET']) && strcmp($_POST['__EVENTTARGET'],'ctl00$ctl00$cphRoblox$cphMyRobloxContent$lbAcceptFriend') == 0) {
		FriendUtils::addFriend($recipientdata->id, $senderdata->id);
		$stmt_updatearchivedstate = $con->prepare('DELETE FROM `messages` WHERE `message_id` = ?');
		$stmt_updatearchivedstate->bind_param('i', $messageID);
		$stmt_updatearchivedstate->execute();
		exit(header("Location: /My/Inbox.aspx"));
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" id="gamma-lambda-cam">
	<head>
		<title>GAMMA - Message</title>
		<link id="ctl00_Imports" rel="stylesheet" type="text/css" href="/CSS/AllCSS.css">
		<link id="ctl00_Favicon" rel="Shortcut Icon" type="image/ico" href="/favicon.ico">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta http-equiv="Content-Language" content="en-us">
		<meta name="author" content="Zlysie">
		<meta name="description" content="GAMMA is a FREE (invite only) casual virtual world with fully constructible/desctructible 3D environments and immersive physics. Build, battle, chat, or just hang out.">
		<meta name="keywords" content="game, video game, building game, construction game, online game, LEGO game, LEGO, MMO, MMORPG, gammablox, gamma roblox, old roblox">
		<script src="/js/WebResource.js" type="text/javascript"></script>
		<script src="/js/jquery.js" type="text/javascript"></script>
	</head>
	<body>
		<form name="aspnetForm" method="post" action="PrivateMessage.aspx?<?=  $_SERVER['QUERY_STRING'] ?>" id="aspnetForm">
			<div id="Container">
				<div id="AdvertisingLeaderboard"></div>
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_header.php"; ?>
				<div id="Body">
					<?php if(!isset($error)): ?>
					<?php if($mode == "read" && !$sent):?>
					<div class="MessageContainer" style="margin-top: 10px;">
						<div id="MessageReaderContainer" style="margin-left: 0px; width: 700px; float: left;">
							<h3>Private Message</h3>
							<div class="MessageReaderContainer" style="width: 680px;">
								<div id="Message" style="width: 100%;">
									<div class="Body" style="float: right;width: 80%;">
										<pre id="pBody" class="MultilineTextBox" style="text-wrap: auto;"><?= $message['message_content'] ?></pre>
									</div>
									<div id="MessageHeader">
										<div id="DateSent" style="position:relative;right:0px;float: left;"><?= $date->format('m/d/Y h:i:s A') ?></div>
										<div id="Author" style="display:inline;">
											<a disabled="disabled" title="" onclick="return false" style="display:inline-block;height:100px;width:100px;">
												<img style="height:90px;" src="/thumbs/player?id=<?= $senderdata->id ?>&type=100" border="0" alt="<?= $senderdata->name ?>">
											</a><br>
											<a title="Visit <?= $senderdata->name ?>'s Home Page" href="/User.aspx?ID=<?= $senderdata->id ?>"><?= $senderdata->name ?></a>
										</div>
										<div id="Subject"><?= $message['message_subject'] ?><br><br></div>
										<div class="Buttons" style="margin-right:20px;text-align:left;float:left;">
											<div class="ReportAbuse" style="text-align:left; top:12px">
												<div class="ReportAbuse">
													<span class="AbuseIcon">
														<a href="/AbuseReport/Message.aspx?ID=339142889&amp;RedirectUrl=%2fMy%2fPrivateMessage.aspx%3fMessageID%3d339142889%26from%3dInbox">
															<img src="/images/abuse.PNG?v=2" alt="Report Abuse" style="">
														</a>
													</span>
													<span class="AbuseButton">
														<a href="/AbuseReport/Message.aspx?ID=339142889&amp;RedirectUrl=%2fMy%2fPrivateMessage.aspx%3fMessageID%3d339142889%26from%3dInbox">Report Abuse</a>
													</span>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div style="clear: both"></div>
							</div>
							<div class="Buttons" style="text-align: right; margin-top: 10px;">
								<?php if($message['message_friendreq'] == 1 && !FriendUtils::isUserFriendsWith($senderdata->id, $recipientdata->id)): ?>
								<a class="Button" href="javascript:__doPostBack(&quot;ctl00$ctl00$cphRoblox$cphMyRobloxContent$lbAcceptFriend&quot;, &quot;&quot;)">Accept</a>
								<a class="Button" href="javascript:__doPostBack(&quot;ctl00$ctl00$cphRoblox$cphMyRobloxContent$lbRejectFriend&quot;, &quot;&quot;)">Reject</a>
								<?php else: ?>
								<a class="Button" href="javascript:__doPostBack(&quot;ctl00$ctl00$cphRoblox$cphMyRobloxContent$lbReply&quot;, &quot;&quot;)">Reply</a>
								<a class="Button" href="javascript:__doPostBack(&quot;ctl00$ctl00$cphRoblox$cphMyRobloxContent$lbDelete&quot;, &quot;&quot;)">Delete</a>
								<?php endif ?>
								<a class="Button" href="javascript:__doPostBack(&quot;ctl00$ctl00$cphRoblox$cphMyRobloxContent$lbCancel&quot;, &quot;&quot;)">Cancel</a>
								
							</div>
							<?php if($reply): ?>
							<div id="ctl00_ctl00_cphRoblox_cphMyRobloxContent_pPrivateMessageEditor">
								<h3>Your Message</h3>
								<div id="MessageEditorContainer" style="width: 680px;">
									<div class="MessageEditor">
										<table width="100%">
											<tbody>
												<tr valign="top">
													<td style="width: 12em">
														<div id="From">
															<span class="Label"><span>From:</span></span> 
															<span class="Field"><span><?= $recipientdata->name ?></span></span>
														</div>
														<div id="To">
															<span class="Label"><span>Send To:</span></span> 
															<span class="Field"><span><?= $senderdata->name ?></span></span>
														</div>
													</td>
													<td style="padding: 0 24px 6px 12px">
														<span id="CustomValidatorFloodCheck" style="color:Red;display:none;">You have reached your send limit.  Please wait a few moments to re-send.</span>
														<div id="Subject">
															<div class="Label">
																<label for="ctl00_ctl00_cphRoblox_cphMyRobloxContent_rbxMessageEditor_txtSubject">Subject:</label>
															</div>
															<div class="Field">
																<input name="ctl00$ctl00$cphRoblox$cphMyRobloxContent$rbxMessageEditor$txtSubject" type="text" value="RE: <?= $message['message_subject'] ?>" id="ctl00_ctl00_cphRoblox_cphMyRobloxContent_rbxMessageEditor_txtSubject" class="TextBox" style="width:100%;">
															</div>
														</div>
														<div class="Body">
															<div class="Label">
																<label for="ctl00_ctl00_cphRoblox_cphMyRobloxContent_rbxMessageEditor_txtBody" id="ctl00_ctl00_cphRoblox_cphMyRobloxContent_rbxMessageEditor_lblBody">Message:</label>
															</div>
<textarea name="ctl00$ctl00$cphRoblox$cphMyRobloxContent$rbxMessageEditor$txtBody" rows="2" cols="20" id="ctl00_ctl00_cphRoblox_cphMyRobloxContent_rbxMessageEditor_txtBody" class="MultilineTextBox" style="height:300px;width:100%;">


------------------------------
On <?= $date->format('d/m/Y') ?> at <?= $date->format('h:i A') . " " . $senderdata->name ?> wrote:
<?= $message['message_content'] ?>
</textarea>
															<div class="Validators">
																<div>
																	<span style="color:Red;display:none;">GAMMA does not condone password trading</span>
																</div>
															</div>
														</div>
													</td>
												</tr>
											</tbody>
										</table>
									</div>
								</div>
								<div style="clear: both"></div>
								<div class="Buttons" style="text-align: right; float: right;margin-top: 15px;margin-right: -2px;">
									<a class="Button" href="javascript:__doPostBack(&quot;ctl00$ctl00$cphRoblox$cphMyRobloxContent$lbSendAndDelete&quot;, &quot;&quot;)">Send & Delete</a>
									<a class="Button" href="javascript:__doPostBack(&quot;ctl00$ctl00$cphRoblox$cphMyRobloxContent$lbSend&quot;, &quot;&quot;)">Send</a>
								</div>
							</div>
							<?php endif ?>
						</div>
						<div style="clear:both"></div>
					</div>
					<?php endif ?>
					<?php if($mode == "write" && !$sent):?>
						<div id="MessageContainer">
						<h3>Your Message</h3>
						<div style="width: 715px;float: left;">
							<div style="width: 690px; float: left; padding: 10px;border: solid 2px Gray;">
								<div id="ctl00_cphRoblox_pFriendInvitation">
									<div id="ctl00_cphRoblox_pMessageEditor">
										
										<br>
										<div id="ctl00_cphRoblox_MessageEditorControlContainer">
										<div class="MessageEditor">
											<table width="100%">
												<tbody><tr valign="top">
													<td style="width: 12em">
														<div id="From">	
															<span class="Label"><span id="ctl00_cphRoblox_rbxMessageEditor_lblFrom">From:</span></span> 
															<span class="Field"><span id="ctl00_cphRoblox_rbxMessageEditor_lblAuthor"><?= $senderdata->name ?></span></span>
														</div>
														<div id="To">
															<span class="Label"><span id="ctl00_cphRoblox_rbxMessageEditor_lblTo">Send To:</span></span> 
															<span class="Field"><span id="ctl00_cphRoblox_rbxMessageEditor_lblRecipient"><?= $recipientdata->name?></span></span>
														</div>
													</td>
													<td style="padding: 0 24px 6px 12px">
														<span id="ctl00_cphRoblox_rbxMessageEditor_CustomValidatorFloodCheck" style="color:red;display:none;">You have reached your send limit.  Please wait a few moments to re-send.</span>
														<div id="Subject">
															<div class="Label">
															<label for="ctl00_cphRoblox_rbxMessageEditor_txtSubject" id="ctl00_cphRoblox_rbxMessageEditor_lblSubject">Subject:</label></div>
															<div class="Field">
															<input name="ctl00$cphRoblox$rbxMessageEditor$txtSubject" type="text" value="" id="ctl00_cphRoblox_rbxMessageEditor_txtSubject" class="TextBox" style="width:100%;"></div>
														</div>
														<div class="Body">
															<div class="Label"><label for="ctl00_cphRoblox_rbxMessageEditor_txtBody" id="ctl00_cphRoblox_rbxMessageEditor_lblBody">Message:</label></div>
															<textarea name="ctl00$cphRoblox$rbxMessageEditor$txtBody" rows="2" cols="20" id="ctl00_cphRoblox_rbxMessageEditor_txtBody" class="MultilineTextBox" style="height:300px;width:100%;"></textarea>
															<div class="Validators"><div></div></div>
														</div>
													</td>
												</tr>
											</tbody></table>
										</div>
									</div>
								</div>
								</div>
							</div>
<div class="Buttons" style="text-align: right; float: right;margin-top: 15px;">
									<a id="ctl00_cphRoblox_lbSend" class="Button" href="javascript:__doPostBack(&quot;ctl00$cphRoblox$lbSend&quot;, &quot;&quot;)">Send</a>
								</div></div>
					</div>
					<?php endif ?>
					<?php if($sent): ?>
					<div class="MessageContainer" style="margin-top: 10px;">
						<div id="MessagePane" style="margin-left:0px;width:700px;font-family:Verdana, Helvetica, Sans-Serif; float:left;">
							<div id="ctl00_ctl00_cphRoblox_cphMyRobloxContent_pConfirmation">
								<div id="Confirmation">
									<h3>Message Sent</h3>
									<div id="Message"><span id="ctl00_ctl00_cphRoblox_cphMyRobloxContent_lConfirmationMessage">Your message has been sent to <?= $mode == "write" ? $recipientdata->name : $senderdata->name ?>.</span></div>
									<div class="Buttons"><a id="ctl00_ctl00_cphRoblox_cphMyRobloxContent_lbContinue" class="Button" href="<?= $mode == "write" ? "/User.aspx?ID=".$recipientdata->id : "/My/Inbox.aspx"?>">Continue</a></div>
								</div>
							</div>
						</div>
							<div id="AdsPane" style="float:right;">
							<div id="ctl00_ctl00_cphRoblox_cphMyRobloxContent_adsInboxWideSkyscraper_OutsideAdPanel" class="AdPanel">
								<iframe id="ctl00_ctl00_cphRoblox_cphMyRobloxContent_adsInboxWideSkyscraper_AsyncAdIFrame" allowtransparency="true" frameborder="0" scrolling="no" height="600" src="/Ads/IFrameAdContent.aspx?v=2&amp;slot=Roblox_Message_Right_160x600&amp;format=skyscraper&amp;v=2" width="160" data-ruffle-polyfilled=""></iframe>
							</div>
							<a id="ctl00_ctl00_cphRoblox_cphMyRobloxContent_adsInboxWideSkyscraper_ReportAdButton" title="click to report an offensive ad" class="BadAdButton" href="javascript:__doPostBack(&quot;ctl00$ctl00$cphRoblox$cphMyRobloxContent$adsInboxWideSkyscraper$ReportAdButton&quot;, &quot;&quot;)">[ report ]</a>
						</div>
						<div style="clear: both;"></div>
					</div>
					<script>
						if ( window.history.replaceState ) {window.history.replaceState( null, null, "/My/Inbox.aspx" );}
					</script>
					<?php endif ?>
					<?php endif ?>
					<?php if(isset($error)): ?>
					<div class="MessageContainer" style="margin-top: 10px;">
						<div id="MessagePane" style="margin-left:0px;width:700px;font-family:Verdana, Helvetica, Sans-Serif; float:left;">
							<div id="ctl00_ctl00_cphRoblox_cphMyRobloxContent_pConfirmation">
								<div id="Confirmation">
									<h3>Error!</h3>
									<div id="Message"><span id="ctl00_ctl00_cphRoblox_cphMyRobloxContent_lConfirmationMessage" style="color:red"><?= $error ?></span></div>
									<div class="Buttons"><a id="ctl00_ctl00_cphRoblox_cphMyRobloxContent_lbContinue" class="Button" href="/My/Inbox.aspx">Continue</a></div>
								</div>
							</div>
						</div>
							<div id="AdsPane" style="float:right;">
							<div id="ctl00_ctl00_cphRoblox_cphMyRobloxContent_adsInboxWideSkyscraper_OutsideAdPanel" class="AdPanel">
								<iframe id="ctl00_ctl00_cphRoblox_cphMyRobloxContent_adsInboxWideSkyscraper_AsyncAdIFrame" allowtransparency="true" frameborder="0" scrolling="no" height="600" src="/Ads/IFrameAdContent.aspx?v=2&amp;slot=Roblox_Message_Right_160x600&amp;format=skyscraper&amp;v=2" width="160" data-ruffle-polyfilled=""></iframe>
							</div>
							<a id="ctl00_ctl00_cphRoblox_cphMyRobloxContent_adsInboxWideSkyscraper_ReportAdButton" title="click to report an offensive ad" class="BadAdButton" href="javascript:__doPostBack(&quot;ctl00$ctl00$cphRoblox$cphMyRobloxContent$adsInboxWideSkyscraper$ReportAdButton&quot;, &quot;&quot;)">[ report ]</a>
						</div>
						<div style="clear: both;"></div>
					</div>
					<?php endif ?>
					<div style="clear:both"></div>
				</div>
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_footer.php"; ?>
			</div>
			<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/formvars.php"; ?>
		</form>
	</body>
</html>
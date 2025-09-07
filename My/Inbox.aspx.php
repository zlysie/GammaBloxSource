<?php
	session_start();
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	UserUtils::LockOutUserIfNotLoggedIn();

	$user_details = UserUtils::GetLoggedInUser();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" id="gamma-lambda-cam">
	<head>
		<title>GAMMA - Inbox</title>
		<link id="ctl00_Imports" rel="stylesheet" type="text/css" href="/CSS/AllCSS.css">
		<link id="ctl00_Favicon" rel="Shortcut Icon" type="image/ico" href="/favicon.ico">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta http-equiv="Content-Language" content="en-us"><meta name="author" content="Zlysie">
		<meta name="description" content="GAMMA is a FREE (invite only) casual virtual world with fully constructible/desctructible 3D environments and immersive physics. Build, battle, chat, or just hang out.">
		<meta name="keywords" content="game, video game, building game, construction game, online game, LEGO game, LEGO, MMO, MMORPG, gammablox, gamma roblox, old roblox">
		<script src="/js/WebResource.js"></script>
		<script src="/js/jquery.js"></script>
		<script src="/js/Messages.js?t=<?= time() ?>" ></script>
	</head>
	<body>
		<form name="aspnetForm" method="post" action="Inbox.aspx" id="aspnetForm">
			<div id="Container">
				
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_header.php"; ?>
				<div id="Body">
					<div id="InboxContainer">
						<h2>Inbox</h2>
						<div id="InboxPane">
							<div id="MessageList" class="StandardBoxGray" style="width: 700px">
								<table id="tableHeader" cellspacing="0" cellpadding="3" style="width: 698px; border-collapse: collapse">
									<tr class="InboxHeader subMenu">
										<th id="selectAllBox" align="left" scope="col" style="width: 28px">
											<input type="checkbox" id="selectAll" onclick = "selectAllCheckboxesFn()">
										</th>
										<th id="FromCol" align="left" scope="col" style="width: 198px">
											<a>From</a>
										</th>
										<th id="SubjectCol" align="left" scope="col">
											<a>Subject</a>
										</th>
										<th id="DateCol" align="right" scope="col">
											<a>Date</a>
										</th>
									</tr>
								</table>
								<div id="Processing" style="display: none;text-align:center;vertical-align:middle">
									<br /><img id="ctl00_ctl00_ctl00_cphRoblox_cphMyRobloxContent_cpMyInboxContent_Image22" src="/images/ProgressIndicator2.gif" alt="Loading..." align="middle" style="border-width:0px;" />&nbsp&nbsp; Loading messages...
								</div>
								<div id="Messages">
								<table id="tableTemplate" cellspacing="0" cellpadding="3" style="width: 698px; border-collapse: collapse">
									<tbody>
										<tr id="trTemplate" style="display: none">
											<td class="Select" style="width: 39px" align="left"><input class="checkbox" type="checkbox"></td>
											<td class="From" style="width: 209px" align="left"><a class="FromText" href="../User.aspx?ID=" title=""></a></td>
											<td class="Subject" style="width: 300px" align="left"><div style="width:300px;overflow:hidden"><a class="SubjectText" href="PrivateMessage.aspx?MessageID="></a></div></td>
											<td class="Date" align="right"><a class = "DateText"></a></td>
										</tr>
									</tbody>
								</table>
								</div>
								<div id="pager" class="InboxPager" style="height: 23px; text-align: left; display: none; padding: 3px 3px 3px 3px"></div>
								<div style="text-align: center" class="Buttons">
									<a id="messageButton" class="Button" onclick="deleteMessages()">Delete</a>
								</div>
								<div id="EmptyInbox" class="EmptyInbox" style="display: none; text-align: center">You have no messages in your Inbox.</div>
							</div>
						</div>
						<div style="clear: both;"></div>
					</div>
					<div style="clear:both"></div>
				</div>
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_footer.php"; ?>
			</div>
			<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/formvars.php"; ?>
		</form>
	</body>
</html>
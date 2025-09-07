<?php 
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/transactionutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/splasher.php";
	UserUtils::LockOutUserIfNotLoggedIn();
	$nav_get_user = UserUtils::GetLoggedInUser();

	if($nav_get_user != null) {
		$stmt_assetinfo = $con->prepare('SELECT * FROM `messages` WHERE `message_recipient` = ? AND `message_read` = 0');
		$stmt_assetinfo->bind_param('i', $nav_get_user->id);
		$stmt_assetinfo->execute();
		$result = $stmt_assetinfo->get_result();
		$msgcount = $result->num_rows;

		$nav_robux_count = TransactionUtils::GetNetRobuxFromUser($nav_get_user->id);
		$nav_tickets_count = TransactionUtils::GetNetTicketsFromUser($nav_get_user->id);
	}

	UserUtils::RegisterAction();
?>

<div id="Header">
	<div id="Banner" style="font-family: sans-serif;">
		<div id="Options">
			<div id="Authentication">
				<span>
					<span id="ctl00_lnLoginName">Logged in as <?= $nav_get_user->name ?> | </span>
					<a id="ctl00_lsLoginStatus" href="javascript:__doPostBack('ctl00$lsLoginStatus$ctl00','')">Logout</a>
				</span>
			</div>
			<div id="Settings">
				<span id="ctl00_lSettings"></span>
			</div>
		</div>
		<div id="Logo" style="margin-bottom: -4px;">
			<a id="ctl00_rbxImage_Logo" title="GAMMA" href="/Default.aspx" style="display:inline-block;height:70px;width:267px;cursor:pointer;">
				<img src="/images/logo.png" border="0" id="img" alt="GAMMA" style="margin-top:-5px; height:69px;">
			</a>
		</div>
		<?php if($nav_robux_count != 0 || $nav_tickets_count != 0 || $msgcount != 0): ?>
		<div id="Alerts">
			<table style="width:100%;height:100%">
				<tbody>
					<tr>
						<td valign="middle">
							<div id="ctl00_rbxAlerts_AlertSpacePanel">
								<div id="AlertSpace">
									<?php if($msgcount != 0): ?>
									<div id="ctl00_rbxAlerts_MessageAlertPanel">
										<div id="MessageAlert">
											<a id="ctl00_rbxAlerts_MessageAlertIconHyperLink" class="MessageAlertIcon" href="/My/Inbox.aspx">
												<img src="/images/Message.png" style="border-width:0px;">
											</a>&nbsp;
											<a id="ctl00_rbxAlerts_MessageAlertCaptionHyperLink" class="MessageAlertCaption" href="/My/Inbox.aspx" style="font-weight: bold;">
												<b><?= $msgcount ?> new message<?php if($msgcount != 1):?>s<?php endif ?>!</b>
											</a>
										</div>
									</div>
									<?php endif ?>
									<?php if($nav_robux_count != 0): ?>
									<div id="ctl00_rbxAlerts_RobuxAlertPanel">
										<div id="RobuxAlert">
											<a id="ctl00_rbxAlerts_RobuxAlertIconHyperLink" class="RobuxAlertIcon" href="/My/AccountBalance.aspx">
												<img src="/images/Robux.png" style="border-width:0px;">
											</a>&nbsp;
											<a id="ctl00_rbxAlerts_RobuxAlertCaptionHyperLink" class="RobuxAlertCaption" href="/My/AccountBalance.aspx"><?= $nav_robux_count ?> ROBUX</a>
										</div>
									</div>
									<?php endif ?>
									<?php if($nav_tickets_count != 0): ?>
									<div id="ctl00_rbxAlerts_TicketsAlertPanel">
										<div id="TicketsAlert">
											<a id="ctl00_rbxAlerts_TicketsAlertIconHyperLink" class="TicketsAlertIcon" href="/My/AccountBalance.aspx">
												<img src="/images/Tux.png" style="border-width:0px;">
											</a>&nbsp;
											<a id="ctl00_rbxAlerts_TicketsAlertCaptionHyperLink" class="TicketsAlertCaption" href="/My/AccountBalance.aspx"><?= $nav_tickets_count ?> Tux</a>
										</div>
									</div>
									<?php endif ?>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php endif ?>
	</div>
	<div class="Navigation">
		<span><a id="ctl00_hlMyRoblox" class="MenuItem" href="/User.aspx">My GAMMA</a></span>
		<span class="Separator">&nbsp;|&nbsp;</span>
		<span><a id="ctl00_hlGames" class="MenuItem" href="/Games.aspx">Games</a></span>
		<span class="Separator">&nbsp;|&nbsp;</span>
		<span><a id="ctl00_hlCatalog" class="MenuItem" href="/Catalog.aspx">Catalog</a></span>
		<span class="Separator">&nbsp;|&nbsp;</span>
		<span><a id="ctl00_hlBrowse" class="MenuItem" href="/Browse.aspx">People</a></span>
		<span class="Separator">&nbsp;|&nbsp;</span>
		<span><a id="ctl00_hlForum" class="MenuItem" href="/Forum/Default.aspx">Forum</a></span>
		<span class="Separator">&nbsp;|&nbsp;</span>
		<span><a id="ctl00_hlNews" class="MenuItem" href="http://www.lambda.cam/user/gamma" target="_blank">News</a>&nbsp;<a id="ctl00_hlNewsFeed" href="http://www.lambda.cam/user/gamma?feed=rss"><img src="/images/feed-icons/feed-icon-14x14.png" alt="RSS" border="0"></a></span>
		<span class="Separator">&nbsp;|&nbsp;</span>
		<span><a id="ctl00_hlHelp" class="MenuItem" href="https://wiki.lambda.cam/wiki/" target="_blank">Help</a></span>
	</div>
	<?php Splasher::GenerateSplashHeader(); ?>
</div>
<div id="ctl00_cphRoblox_VisitButtons_rbxPlaceLauncher_Panel1" class="modalPopup" style="display: none">
	<div style="margin: 1.5em">
		<div id="Spinner" style="float:left;margin:0 1em 1em 0">
			<img id="ctl00_cphRoblox_rbxUserPlacesPane_ctl02_rbxPlatform_rbxVisitButtons_rbxPlaceLauncher_Image1" src="/images/ProgressIndicator2.gif" alt="Progress" border="0">
		</div>
		<div id="Requesting" style="display: none">Requesting a server</div>
		<div id="Waiting" style="display: none">Waiting for a server</div>
		<div id="Loading" style="display: none">A server is loading the game</div>
		<div id="Joining" style="display: none">The server is ready. Joining the game...</div>
		<div id="Error" style="display: none">An error occured. Please try again later</div>
		<div id="Expired" style="display: none">There are no game servers available at this time. Please try again later</div>
		<div id="GameEnded" style="display: none">The game you requested has ended</div>
		<div id="GameFull" style="display: none">The game you requested is full. Please try again later</div>
		<div style="text-align: center; margin-top: 1em"><input id="Cancel" type="button" class="Button" value="Cancel" onclick="Gamma.PlaceLauncher.Cancel()"></div>
	</div>
</div>
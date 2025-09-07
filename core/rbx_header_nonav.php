<?php 
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/splasher.php";
	if(!str_starts_with($_SERVER['REQUEST_URI'], "/NotApproved.aspx")) {
		UserUtils::LockOutUserIfNotLoggedIn();
	}
	
	$nav_get_user = UserUtils::GetLoggedInUser();
	UserUtils::RegisterAction();
?>
<?php if($nav_get_user == null): ?>
<div id="Header">
	<div id="Banner" style="font-family: sans-serif;">
		<div id="Options">
			<div id="Authentication">
				<span><a id="ctl00_lsLoginStatus" href="/Login/Default.aspx">Login</a></span>
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
		<div id="Alerts">
			<table style="width:100%;height:100%">
				<tbody>
					<tr>
						<td valign="middle">
							<a id="ctl00_rbxAlerts_SignupAndPlayHyperLink" class="SignUpAndPlay" href="/Login/New.aspx">
								<img src="/images/SignupBanner.png" alt="Sign-up and Play!" border="0" blankurl="/images/blank-267x70.gif">
							</a>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<?php Splasher::GenerateSplashHeader(); ?>
</div>
<?php endif ?>
<?php if($nav_get_user != null): ?>
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
				<span id="ctl00_lSettings">Age: 13+, Chat Mode: Safe</span>
			</div>
		</div>
		<div id="Logo">
			<a id="ctl00_rbxImage_Logo" title="GAMMA" href="/Default.aspx" style="display:inline-block;height:70px;width:267px;cursor:pointer;">
				<img src="/images/logo.png" border="0" id="img" alt="GAMMA">
			</a>
		</div>
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
	</div>
	<?php Splasher::GenerateSplashHeader(); ?>
</div>
<?php endif ?>
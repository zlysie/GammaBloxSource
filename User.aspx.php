<?php
	session_start();
	
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/friending.php";

	UserUtils::LockOutUserIfNotLoggedIn();
	
	$get_user = null;
	$private_profile = false;
	
	if(isset($_GET['ID'])) {
		if(!empty(trim($_GET['ID']))) {
			$get_user = User::FromID(intval($_GET['ID']));
		}
	}
	
	$user = UserUtils::GetLoggedInUser();
	
	if($get_user == null && $user != null) {
		$get_user = $user;
		$private_profile = true;
	}

	if(isset($_POST['__EVENTTARGET'])) {
		if($user != null && $_POST['__EVENTTARGET'] == 'ctl00$cphRoblox$rbxUser$acceptFriend$Submit') {
			FriendUtils::addFriend($user->id, $get_user->id);
		} else if($user != null && $_POST['__EVENTTARGET'] == 'ctl00$cphRoblox$rbxUser$removeFriend$Submit') {
			FriendUtils::removeFriend($user->id, $get_user->id);
		}
	}

	$stmt_getallplaces = $con->prepare("SELECT * FROM `assets` WHERE `asset_type` = 9 AND `asset_status` = 0 AND `asset_creator` = ?");
	$stmt_getallplaces->bind_param('i', $get_user->id);
	$stmt_getallplaces->execute();
	$result = $stmt_getallplaces->get_result();
	$places = array();

	$visit_count = 0;

	if($result->num_rows != 0) {
		while($row = $result->fetch_assoc()) {
			$place = new Place($row);
			$visit_count += $place->visits;
			array_push($places, $place);
		}
	}

	$status_string = $get_user->online ? "Online" : "Offline";
	$status_label = $get_user->online ? "Online: ".$get_user->GetStatus(true) : "Offline";

	$stmt = $con->prepare('SELECT * FROM `friends` WHERE (`sender` = ? OR `reciever` = ?) AND `status` = 1 LIMIT 0, 6');
	$stmt->bind_param('ii', $get_user->id, $get_user->id);
	$stmt->execute();
	$friend_result = $stmt->get_result();
	$friend_count = $friend_result->num_rows;
	$user_count = 0;

	$stmt = $con->prepare('SELECT * FROM `friends` WHERE (`sender` = ? OR `reciever` = ?) AND `status` = 1');
	$stmt->bind_param('ii', $get_user->id, $get_user->id);
	$stmt->execute();
	$all_friends_count = $stmt->get_result()->num_rows;

	$stmt = $con->prepare('SELECT * FROM `friends` WHERE (`sender` = ? OR `reciever` = ?) AND `status` = 1 AND `time_added` < DATE_SUB(NOW(),INTERVAL 1 WEEK)');
	$stmt->bind_param('ii', $get_user->id, $get_user->id);
	$stmt->execute();
	$all_friends_week_count = $stmt->get_result()->num_rows;

	if($user != null) {
		$friends_with = FriendUtils::isUserFriendsWith($user->id, $get_user->id) || $user->id == $get_user->id;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" id="gamma-lambda-cam">
	<head>
		<title><?= $get_user->name ?>'s GAMMA Home Page</title>
		<link id="ctl00_Imports" rel="stylesheet" type="text/css" href="/CSS/AllCSS.css">
		<link id="ctl00_Favicon" rel="Shortcut Icon" type="image/ico" href="favicon.ico">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta http-equiv="Content-Language" content="en-us">
		<meta name="author" content="Zlysie">
		<meta name="title" content="<?= $get_user->name ?>">
		<meta name="description" content="<?= htmlspecialchars(substr($get_user->blurb, 0, 128), ENT_QUOTES) ?>"><!-- Max 128 chars -->
		<meta name="keywords" content="game, video game, building game, construction game, online game, LEGO game, LEGO, MMO, MMORPG, gammablox, gamma roblox, old roblox">
		<script src="/js/jquery.js"></script>
		<script src="/js/jquery-ui.js"></script>
		<script src="/js/jquery-modal.js"></script>
		<script src="/js/WebResource.js" type="text/javascript"></script>
		<script src="/js/Stuff.js?t=<?= time() ?>"></script>
		<script src="/js/PlaceLauncher.js?t=<?= time() ?>"></script>
		<style>
			.ProfileLink {
				padding : 0 10px;
			}

			.ProfileLink > td {
				margin: 5px 0;
				display:block;
			}
			td {
				text-align: center;
			}
		</style>
	</head>
	
	<body>
		<form name="aspnetForm" method="post" action="User.aspx?ID=<?= $get_user->id ?>" id="aspnetForm">
			<script type="text/javascript">
				//<![CDATA[
				function checkRobloxInstall(){window.location="/Install/LaunchGame.aspx"; return false;};//]]>
			</script>
			<div id="Container">
				<div id="AdvertisingLeaderboard"></div>
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_header.php"; ?>
				<div id="Body">
					<div id="UserContainer">
						<div id="LeftBank">
							<div id="ProfilePane">
								<table width="100%" bgcolor="lightsteelblue" cellpadding="6" cellspacing="0">
									<tbody>
										<tr>
											<td>
												<span id="ctl00_cphRoblox_rbxUserPane_lUserName" class="Title"><?= $get_user->name ?></span><br>
												<span id="ctl00_cphRoblox_rbxUserPane_lUserOnlineStatus" class="User<?= $status_string ?>Message">[ <?= $status_label ?> ]</span>
											</td>
										</tr>
										<tr>
											<td>
												<span id="ctl00_cphRoblox_rbxUserPane_lUserRobloxURL"><?= $get_user->name ?>'s GAMMA:</span><br>
												<a id="ctl00_cphRoblox_rbxUserPane_hlUserRobloxURL" href="User.aspx?ID=<?= $get_user->id ?>">http://gamma.lambda.cam/User.aspx?ID=<?= $get_user->id ?></a><br>
												<br>
												<div style="left: 0px; float: left; position: relative; top: 0px">
													<a id="ctl00_cphRoblox_rbxUserPane_Image1" disabled="disabled" title="<?= $get_user->name ?>" onclick="return false" style="display:inline-block;"><img src="/thumbs/player?id=<?= $get_user->id?>&type=180" border="0" alt="<?= $get_user->name ?>" blankurl="http://t7.roblox.com:80/blank-180x220.gif"></a><br>
													<div id="ctl00_cphRoblox_rbxUserPane_AbuseReportButton1_AbuseReportPanel" class="ReportAbusePanel">
														<span class="AbuseIcon"><a id="ctl00_cphRoblox_rbxUserPane_AbuseReportButton1_ReportAbuseIconHyperLink" href="AbuseReport/UserProfile.aspx?userID=<?= $get_user->id ?>&amp;ReturnUrl=http%3a%2f%2fwww.roblox.com%2fUser.aspx%3fID%3d<?= $get_user->id ?>"><img src="images/abuse.PNG" alt="Report Abuse" border="0"></a></span>
														<span class="AbuseButton"><a id="ctl00_cphRoblox_rbxUserPane_AbuseReportButton1_ReportAbuseTextHyperLink" href="AbuseReport/UserProfile.aspx?userID=<?= $get_user->id ?>&amp;ReturnUrl=http%3a%2f%2fwww.roblox.com%2fUser.aspx%3fID%3d<?= $get_user->id ?>">Report Abuse</a></span>
													</div>
												</div>
												<p>
													<?php if($user != null && !$private_profile && $user->id != $get_user->id): ?>
														<?php if(FriendUtils::getStatusOfFriendship($user->id, $get_user->id) == -1): ?>
														<a href="/My/FriendInvitation.aspx?RecipientID=<?= $get_user->id ?>">Send Friend Request</a>
														<?php endif ?>

														<?php if(FriendUtils::getStatusOfFriendship($user->id, $get_user->id) == 1 && FriendUtils::isUserFriendsWith($user->id, $get_user->id)): ?>
														<a href="javascript:__doPostBack('ctl00$cphRoblox$rbxUser$removeFriend$Submit','')">Remove Friend</a>
														<?php endif ?>
													<?php endif ?>
												</p>
												<p><?php if(!$private_profile && $user != null): ?><a href="/My/PrivateMessage.aspx?RecipientID=<?= $get_user->id ?>">Send Message</a><?php endif ?></p>
												<p>
													<?php if(!$private_profile): ?><span id="ctl00_cphRoblox_rbxUserPane_rbxPublicUser_lBlurb"><?= $get_user->blurb ?></span><?php endif ?>
													<?php if($private_profile): ?>
													<span id="ctl00_cphRoblox_rbxUserPane_rbxPublicUser_lBlurb">
														<table style="width:241px">
															<tbody>
																<tr class="ProfileLink"><td><a href="/My/Inbox.aspx">Inbox</a></td></tr>
																<tr class="ProfileLink"><td><a href="/My/Character.aspx">Change Character</a></td></tr>
																<tr class="ProfileLink"><td><a href="/My/Profile.aspx">Edit Profile</a></td></tr>
																<tr class="ProfileLink"><td><a href="">Account Upgrades</a></td></tr>
																<tr class="ProfileLink"><td><a href="">Account Balance</a></td></tr>
																<tr class="ProfileLink"><td><a href="/User.aspx?ID=<?= $get_user->id ?>">View Public Profile</a></td></tr>
																<!--<tr class="ProfileLink"><td><a>Create New Place</a><br>(5 remaining)</td></tr>-->
																<!--<tr class="ProfileLink"><td><a href="">Share GAMMA</a></td></tr>-->
															</tbody>
														</table>
													</span>
													<?php endif ?>
												</p>
											</td>
										</tr>
									</tbody>
								</table>
							</div>

							<div id="UserBadgesPane">
								<div id="UserBadges">
									<h4><a id="ctl00_cphRoblox_rbxUserBadgesPane_hlHeader" href="Badges.aspx">Badges</a></h4>
									<table id="ctl00_cphRoblox_rbxUserBadgesPane_dlBadges" cellspacing="0" align="Center" border="0">
										<tbody>
											<?php 
												if(count($get_user->GetBadges()) != 0) {
													foreach($get_user->GetBadges() as $badge) {
														if($badge_count == 0) {
															echo <<<EOT
																<tr>
															EOT;
														}
	
														$badge_name = $badge['badge_name'];
														$badge_desc = $badge['badge_desc'];
														$badge_icofile = $badge['badge_icofile'];
	
														echo <<<EOT
															<td>
																<div class="Badge">
																	<div class="BadgeImage"><a href="Badges.aspx"><img src="/images/Badges/$badge_icofile" alt="$badge_desc" height="75" border="0"></a></div>
																	<div class="BadgeLabel"><a href="Badges.aspx" title="$badge_desc">$badge_name</a></div>
																</div>
															</td>
														EOT;
		
														$badge_count = ($badge_count + 1) % 4;
														if($badge_count == 4) {
															echo <<<EOT
																</tr>
															EOT;
														}
													}
												} else {
													echo "<span style=\"height: 120px;display: inline-block;line-height: 120px;\">".$get_user->name." has no GAMMA Badges.</span>";
												}
												
												
											?>
										</tbody>
									</table>
								</div>
							</div>
							<div id="UserStatisticsPane">
								<div id="UserStatistics">
									<h4>Statistics</h4>
									<div class="Statistic">
										<div class="Label"><acronym title="The number of this user's friends.">Friends</acronym>:</div>
										<div class="Value"><span id="ctl00_cphRoblox_rbxUserStatisticsPane_lFriendsStatistics"><?= $all_friends_count ?> (<?= $all_friends_week_count ?> last week)</span></div>
									</div>
									<div class="Statistic">
										<div class="Label"><acronym title="The number of posts this user has made to the GAMMA forum.">Forum Posts</acronym>:</div>
										<div class="Value"><span id="ctl00_cphRoblox_rbxUserStatisticsPane_lForumPostsStatistics">0</span></div>
									</div>
									<div class="Statistic">
										<div class="Label"><acronym title="The number of times this user's profile has been viewed.">Profile Views</acronym>:</div>
										<div class="Value"><span id="ctl00_cphRoblox_rbxUserStatisticsPane_lProfileViewsStatistics">0 (0 last week)</span></div>
									</div>
									<div class="Statistic">
										<div class="Label"><acronym title="The number of times this user's place has been visited.">Place Visits</acronym>:</div>
										<div class="Value"><span id="ctl00_cphRoblox_rbxUserStatisticsPane_lPlaceVisitsStatistics"><?= $visit_count ?> (0 last week)</span></div>
									</div>
									<div class="Statistic">
										<div class="Label"><acronym title="The number of times this user's character has destroyed another user's character in-game.">Knockouts</acronym>:</div>
										<div class="Value"><span id="ctl00_cphRoblox_rbxUserStatisticsPane_lKillsStatistics"><?= $get_user->GetKillCount() ?> (nil last week)</span></div>
									</div>
								</div>
							</div>
						</div>
						<div id="RightBank">
							<div id="UserPlacesPane">
								<div id="UserPlaces">
									<h4>Showcase</h4>
									<?php if(count($places) == 0): ?>
									<div class="NoResults">	
										<span class="NoResults"><?= $get_user->name ?> has no places</span>
									</div>
									<?php endif ?>
									<?php if(count($places) != 0): ?>
									<div id="accordion">
										<input type="hidden" name="ctl00$cphRoblox$rbxUserPlacesPane$ShowcasePlacesAccordion_AccordionExtender_ClientState" id="ctl00_cphRoblox_rbxUserPlacesPane_ShowcasePlacesAccordion_AccordionExtender_ClientState" value="0">
										<?php
											foreach($places as $place) {
												if($place instanceof Place) {

													$place_id = $place->id;
													$place_name = $place->name;
													$place_desc = $place->description;
													$place_sololine = "";
													$place_onlineline = "";
													$item_owner = $place->creator->id == $user->id;
													

													if($place->friends_only) {
														if(!$friends_with) {
															$place_access_line = '<span style="display:inline"><img src="images/locked.png" alt="Locked" border="0">&nbsp;Friends-only</span>';
														} else {
															$place_access_line = '<span style="display:inline"><img src="images/unlocked.png" alt="Unlocked" border="0">&nbsp;Friends-only: You have access</span>';
														}
													} else {
														$place_access_line = '<span style="display:inline"><img src="images/public.png" alt="Public" border="0">&nbsp;Public</span>';
													}

													if((!$place->friends_only || ($place->friends_only) && $friends_with) || $place->friends_only && $friends_with) {
														$place_onlineline = "onclick=\"Gamma.PlaceLauncher.JoinGame($place_id); return false;\"";
													} else {
														$place_onlineline = "disabled";
													}

													if(!$place->copylocked && (!$place->friends_only || ($place->friends_only) && $friends_with)) {
														$place_sololine = "onclick=\"Gamma.PlaceLauncher.VisitPlace($place_id); return false;\"";
													} 
													else if($place->copylocked && $place->friends_only && $friends_with) {
														$place_sololine = "onclick=\"Gamma.PlaceLauncher.VisitPlace($place_id); return false;\"";
													} 
													else if($place->copylocked && !($place->friends_only && $friends_with)) {
														$place_sololine = "disabled";
													} 
													else {
														$place_sololine = "disabled";
													}

													echo <<<EOT
													<div class="place-container">
														<div class="AccordionHeader">$place_name</div>
														<div class="Place">
															<div class="PlayStatus">
																$place_access_line
															</div>
															<div class="PlayOptions">
																<div id="ctl00_cphRoblox_rbxUserPlacesPane_ctl02_rbxPlatform_rbxVisitButtons_VisitMPButton" style="display:inline">
																	<input type="hidden" name="ctl00\$cphRoblox\$rbxUserPlacesPane\$ctl02\$rbxPlatform\$rbxVisitButtons\$rbxPlaceLauncher\$HiddenField1" id="ctl00_cphRoblox_rbxUserPlacesPane_ctl02_rbxPlatform_rbxVisitButtons_rbxPlaceLauncher_HiddenField1">
																	<button id="ctl00_cphRoblox_rbxUserPlacesPane_ctl02_rbxPlatform_rbxVisitButtons_hlMultiplayerVisit" class="Button" $place_onlineline>Visit Online</button>
																</div>
																<div id="ctl00_cphRoblox_rbxUserPlacesPane_ctl02_rbxPlatform_rbxVisitButtons_VisitButton" style="display:inline">
																	&nbsp;&nbsp;&nbsp;<button id="ctl00_cphRoblox_rbxUserPlacesPane_ctl02_rbxPlatform_rbxVisitButtons_hlSoloVisit" class="Button" $place_sololine>Visit Solo</button>
																</div>
															</div>
															<div class="Statistics">
																<span id="ctl00_cphRoblox_rbxUserPlacesPane_ctl02_rbxPlatform_lStatistics">Visited 0 times (0 last week)</span>
															</div>
															<div class="Thumbnail">
																<a id="ctl00_cphRoblox_rbxUserPlacesPane_ctl02_rbxPlatform_rbxPlaceThumbnail" disabled="disabled" title="$place_name" href="/Item.aspx?ID=$place_id" style="display:inline-block;"><img src="/thumbs/?id=$place_id&type=420" border="0" alt="$place_name" blankurl="http://t1.roblox.com:80/blank-420x230.gif"></a>
															</div>
															<div id="ctl00_cphRoblox_rbxUserPlacesPane_ctl02_rbxPlatform_pDescription">
																<div class="Description">
																	<span id="ctl00_cphRoblox_rbxUserPlacesPane_ctl02_rbxPlatform_lDescription">$place_desc</span>
																</div>
															</div>
														</div>
													</div>
													EOT;
												}
											}
										?>
									</div>
									<?php endif ?>
								</div>
							</div>
							<div id="FriendsPane">
								<div id="Friends">
									<h4><?= $get_user->name ?>'s Friends <a href="Friends.aspx?UserID=<?= $get_user->id ?>">See all <?= $all_friends_count ?></a> <?php if($private_profile): ?>(<a href="/My/Friends.aspx">Edit</a>)<?php endif ?></h4>
									<table id="ctl00_cphRoblox_rbxFriendsPane_dlFriends" cellspacing="0" align="Center" border="0">
										<tbody>
											<?php 
												
												if($friend_count != 0) {
													while($row = $friend_result->fetch_assoc()) {
														if($user_count == 0) {
															echo <<<EOT
																<tr>
															EOT;
														}
														
														if($row['sender'] == $get_user->id) {
															$friend_id = $row['reciever'];
														} else {
															$friend_id = $row['sender'];
														}
														
														$friend = User::FromID($friend_id);

														$user_id = $friend->id;
														$user_name = $friend->name;
														$user_status = $friend->online ? "Online" : "Offline";

														$date = $friend->last_online;
														$user_msg_status = $friend->online ? $user_name." is online" : $user_name." is offline (".$date->format('m/d/Y h:i:s A').")";
														
														//builderman is offline (last seen at 4/2/2012 2:59:01 PM).

														echo <<<EOT
															<td>
																<div class="Friend">
																	<div class="Avatar">
																		<a title="$user_name" href="/User.aspx?ID=$user_id" style="display:inline-block;cursor:pointer;">
																			<img src="/thumbs/player?id=$user_id&type=100" height="100" border="0" alt="$user_name" blankurl="http://web.archive.org/web/20080515042338im_/http://t6.roblox.com:80/blank-100x100.gif">
																		</a>
																	</div>
																	<div class="Summary">
																		<span class="OnlineStatus"><img src="images/OnlineStatusIndicator_Is$user_status.gif" alt="$user_msg_status." border="0"></span>
																		<span class="Name"><a href="User.aspx?ID=$user_id">$user_name</a></span>
																	</div>
																</div>
															</td>
														EOT;
														
														$user_count = ($user_count + 1) % 3;
														if($user_count == 3) {
															echo <<<EOT
																</tr>
															EOT;
														}
													}
												} else {
													echo "<tr><td style=\"padding:70px\">".$get_user->name." has no friends.</td></tr>";
												}
											?>
										</tbody>
									</table>
								</div>
							</div>
							<div id="FavoritesPane">
								<div id="ctl00_cphRoblox_rbxFavoritesPane_FavoritesPane">
									<div id="template_clone_asset_fav" style="display:none">
										<td class="Asset" valign="top">
											<div style="padding:5px">
												<div class="AssetThumbnail">
													<a id="AssetThumbnailLink" title="" href="/Item.aspx?ID=" style="display:inline-block;cursor:pointer;">
														<img width="110" height="110" src="" border="0" alt="" style="width:110px; height: 110px;">
													</a>
												</div>
												<div class="AssetDetails">
													<div class="AssetName"><a href="Item.aspx?ID="></a></div>
													<div class="AssetCreator"><span class="Label">Creator:</span> <span class="Detail"><a href="User.aspx?ID="></a></span></div>
												</div>
											</div>
										</td>
									</div>
									<div id="Favorites">
										<h4>Favorites</h4>
										<div id="FavoritesContent">
											<div id="ctl00_cphRoblox_rbxFavoritesPane_NoResultsPanel" class="NoResults" style="display:none;" username="<?= $get_user->name ?>">	
												<span id="ctl00_cphRoblox_rbxFavoritesPane_NoResultsLabel" class="NoResults"></span>
											</div>
											<div id="ctl00_cphRoblox_rbxFavoritesPane_HeaderPagerPanel" class="HeaderPager">
												<span id="ctl00_cphRoblox_rbxFavoritesPane_HeaderPagerLabel">Page 1 of 4</span>
												<a id="ctl00_cphRoblox_rbxFavoritesPane_HeaderPageSelector_Next" href="javascript:__doPostBack('ctl00$cphRoblox$rbxFavoritesPane$HeaderPageSelector_Next','')">Next <span class="NavigationIndicators">&gt;&gt;</span></a>
											</div>
											<table id="ctl00_cphRoblox_rbxFavoritesPane_FavoritesDataList" cellspacing="0" border="0">
												<tbody>
													<tr></tr>
												</tbody>
											</table>
											<div id="ctl00_cphRoblox_rbxFavoritesPane_FooterPagerPanel" class="FooterPager">
												<span id="ctl00_cphRoblox_rbxFavoritesPane_FooterPagerLabel">Page 1 of 4</span>
												<a id="ctl00_cphRoblox_rbxFavoritesPane_FooterPageSelector_Next" href="javascript:__doPostBack('ctl00$cphRoblox$rbxFavoritesPane$FooterPageSelector_Next','')">Next <span class="NavigationIndicators">&gt;&gt;</span></a>
											</div>
										</div>
										<div class="PanelFooter">
											Category:&nbsp;
											
											<select name="ctl00$cphRoblox$rbxFavoritesPane$AssetCategoryDropDownList" id="ctl00_cphRoblox_rbxFavoritesPane_AssetCategoryDropDownList" onchange="">
												<option value="2">T-Shirts</option>
												<option value="11">Shirts</option>
												<option value="12">Pants</option>
												<option value="8">Hats</option>
												<option value="13">Decals</option>
												<option value="10">Models</option>
												<option selected="selected" value="9">Places</option>
											</select>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div style="clear: both"></div>
						<?php if(false): ?>
 						<div class="FriendRequestsPane" style="text-align:center">
							<h4 style="margin:1px">Friend Requests (1)</h4>
							<a href="">Accept all</a>&nbsp;|&nbsp;<a href="">Decline all</a>
							<div class="Friend" style="width:100px; margin: auto 0;">
								<div class="Avatar">
									<a title="zopawa" href="/User.aspx?ID=6" style="display:inline-block;cursor:pointer;">
										<img src="/thumbs/player?id=6&amp;type=100" height="100" border="0" alt="zopawa" blankurl="http://web.archive.org/web/20080515042338im_/http://t6.roblox.com:80/blank-100x100.gif">
									</a>
								</div>
								<div class="Summary">
									<span class="OnlineStatus"><img src="images/OnlineStatusIndicator_IsOffline.gif" alt="zopawa is offline (06/16/2025 01:12:00 AM)." border="0"></span>
									<span class="Name"><a href="User.aspx?ID=6">zopawa</a></span>
								</div>
							</div>
						</div>
						<?php endif ?>
						<div style="clear: both"></div>
						<div id="UserAssetsPane">
							<div id="ctl00_cphRoblox_rbxUserAssetsPane_upUserAssetsPane">
								<div id="UserAssets">
									<h4>Stuff</h4>
									<div id="AssetsMenu">
										<div class="AssetsMenuItem">
											<a class="AssetsMenuButton" href="javascript:LoadAssetMenu(<?= $get_user->id ?>, 2)" cat="2">T-Shirts</a>
										</div>

										<div class="AssetsMenuItem">
											<a class="AssetsMenuButton" href="javascript:LoadAssetMenu(<?= $get_user->id ?>, 11)" cat="11">Shirts</a>
										</div>

										<div class="AssetsMenuItem">
											<a class="AssetsMenuButton" href="javascript:LoadAssetMenu(<?= $get_user->id ?>, 12)" cat="12">Pants</a>
										</div>

										<div class="AssetsMenuItem_Selected">
											<a class="AssetsMenuButton_Selected" href="javascript:LoadAssetMenu(<?= $get_user->id ?>, 8)" cat="8">Hats</a>
										</div>

										<div class="AssetsMenuItem">
											<a class="AssetsMenuButton" href="javascript:LoadAssetMenu(<?= $get_user->id ?>, 13)" cat="13">Decals</a>
										</div>

										<div class="AssetsMenuItem">
											<a class="AssetsMenuButton" href="javascript:LoadAssetMenu(<?= $get_user->id ?>, 10)" cat="10">Models</a>
										</div>

										<div class="AssetsMenuItem">
											<a class="AssetsMenuButton" href="javascript:LoadAssetMenu(<?= $get_user->id ?>, 9)" cat="9">Places</a>
										</div>
									</div>
									<div id="AssetsContent">

										<div id="template_clone_asset" style="display:none">
											<td class="Asset" valign="top">
												<div style="padding:5px">
													<div class="AssetThumbnail">
														<a id="ctl00_cphRoblox_rbxUserAssetsPane_UserAssetsDataList_ctl00_AssetThumbnailHyperLink" title="" href="/Item.aspx?ID=" style="display:inline-block;cursor:pointer;">
															<img width="110" height="110" src="" border="0" alt="">
														</a>
													</div>
													<div class="AssetDetails">
														<div class="AssetName"><a id="ctl00_cphRoblox_rbxUserAssetsPane_UserAssetsDataList_ctl00_AssetNameHyperLink" href="Item.aspx?ID="></a></div>
														<div class="AssetCreator"><span class="Label">Creator:</span> <span class="Detail"><a id="ctl00_cphRoblox_rbxUserAssetsPane_UserAssetsDataList_ctl00_AssetCreatorHyperLink" href="User.aspx?ID="></a></span></div>
														<div class="AssetPrice"><span class="PriceInTickets">Tux: NaN</span></div>
													</div>
												</div>
											</td>
											
										</div>
										<?php if($user != null && ($private_profile || $user->id == $get_user->id)): ?>
										<div style="text-align: center;margin: 0px 0 10px 0;" id="ctl00_cphRoblox_rbxUserAssetsPane_HeaderPagerPanel" class="HeaderPager">
											<a href="" id="ctl00_cphRoblox_rbxUserAssetsPane_CatalogHyperLink">Shop</a>&nbsp;&nbsp;&nbsp;&nbsp;
											<a href="" id="ctl00_cphRoblox_rbxUserAssetsPane_CreateHyperLink">Create</a>
										</div>
										<?php endif?>
										<table id="ctl00_cphRoblox_rbxUserAssetsPane_UserAssetsDataList" cellspacing="0" border="0">
											<tbody>
												<tr></tr>
												<tr></tr>
												<tr></tr>
											</tbody>
										</table>
										<div id="ctl00_cphRoblox_rbxUserAssetsPane_FooterPagerPanel" class="FooterPager" style="display:none">
											<a id="FooterPageSelector_Back" href=""><span class="NavigationIndicators">&lt;&lt;</span> Back </a>
											<span id="FooterPagerLabel">Page 1 of 2</span>
											<a id="FooterPageSelector_Next" href=""> Next <span class="NavigationIndicators">&gt;&gt;</span></a>
										</div>
									</div>
									<div style="clear:both;"></div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_footer.php"; ?>
			</div>
			<script type="text/javascript">
				$(function() {
					$('#accordion').find(".place-container").each(function( index ) {
						$(this).find(".Place").slideUp();
					});

					$($('#accordion').find(".place-container")[0]).find(".Place").slideDown();

					// accordion nonsense
					$('.AccordionHeader').on('click', function() {
						var parent = $(this).parent();
						$('#accordion').find(".place-container").each(function( index ) {
							if ($(this)[0] !== parent[0]) {
								$(this).find(".Place").slideUp();
							}
						})
						$(this).next('.Place').slideDown();
					});

					LoadAssetMenu(<?= $get_user->id?>, 8);
					LoadFavourites(<?= $get_user->id ?>, 9, 1);

					$('#ctl00_cphRoblox_rbxFavoritesPane_AssetCategoryDropDownList').on('change', function (e) {
						var optionSelected = $("option:selected", this);
						var valueSelected = this.value;
						LoadFavourites(<?= $get_user->id ?>, valueSelected);
					});
				});
			</script>
			<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/formvars.php"; ?>
		</form>
	</body>
</html>
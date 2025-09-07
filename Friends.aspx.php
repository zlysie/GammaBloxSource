<?php
	session_start();
	
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/friending.php";

	UserUtils::LockOutUserIfNotLoggedIn();
	
	$get_user = null;
	$private_profile = false;
	
	if(isset($_GET['UserID'])) {
		if(!empty(trim($_GET['UserID']))) {
			$get_user = User::FromID(intval($_GET['UserID']));
		}
	}
	
	$user = UserUtils::GetLoggedInUser();
	
	if($get_user == null && $user != null) {
		$get_user = $user;
		$private_profile = true;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" id="gamma-lambda-cam">
	<head>
		<title><?= $get_user->name ?>'s GAMMA Friends</title>
		<link id="ctl00_Imports" rel="stylesheet" type="text/css" href="/CSS/AllCSS.css">
		<link id="ctl00_Favicon" rel="Shortcut Icon" type="image/ico" href="favicon.ico">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta http-equiv="Content-Language" content="en-us">
		<meta name="author" content="Zlysie">
		<meta name="description" content="GAMMA is a FREE (invite only) casual virtual world with fully constructible/desctructible 3D environments and immersive physics. Build, battle, chat, or just hang out.">
		<meta name="keywords" content="game, video game, building game, construction game, online game, LEGO game, LEGO, MMO, MMORPG, gammablox, gamma roblox, old roblox">
		<meta name="title" content="<?= $get_user->name ?>">
		<meta name="description" content="<?= substr($get_user->blurb, 0, 128) ?>"><!-- Max 128 chars -->
		<script src="/js/jquery.js"></script>
		<script src="/js/WebResource.js" type="text/javascript"></script>
		<script src="/js/Stuff.js"></script>
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
		<form name="aspnetForm" method="post" action="Friends.aspx?UserID=<?= intval($_GET['UserID']) ?>" id="aspnetForm">
			<div id="Container">
				<div id="AdvertisingLeaderboard">
				</div>
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_header.php"; ?>
				<div id="Body">
					<div id="FriendsContainer">
						<div id="Friends">
							<h4><?= $get_user->name ?>'s Friends</h4>
							<div id="ctl00_cphRoblox_rbxFriendsPane_Pager1_PanelPages" align="center">
								Pages:
								<a id="ctl00_cphRoblox_rbxFriendsPane_Pager1_LinkButtonNext" href="javascript:__doPostBack('ctl00$cphRoblox$rbxFriendsPane$Pager1$LinkButtonNext','')">Next &gt;&gt;</a>
							</div>

							<table id="ctl00_cphRoblox_rbxFriendsPane_dlFriends" cellspacing="0" align="Center" border="0">
								<tbody>
									<?php 
										$stmt = $con->prepare('SELECT * FROM `friends` WHERE (`sender` = ? OR `reciever` = ?) AND `status` = 1');
										$stmt->bind_param('ii', $get_user->id, $get_user->id);
										$stmt->execute();
										$result = $stmt->get_result();
										$num_rows = $result->num_rows;
										$user_count = 0;
										if($num_rows != 0) {
											while($row = $result->fetch_assoc()) {
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
																	<img src="/thumbs/player?id=$user_id&type=100" height="100" border="0" alt="$user_name" blankurl="http://t6.roblox.com:80/blank-100x100.gif">
																</a>
															</div>
															<div class="Summary">
																<span class="OnlineStatus"><img src="images/OnlineStatusIndicator_Is$user_status.gif" alt="$user_msg_status." border="0"></span>
																<span class="Name"><a href="User.aspx?ID=$user_id">$user_name</a></span>
															</div>
														</div>
													</td>
												EOT;
												
												$user_count = ($user_count + 1) % 6;
												if($user_count == 6) {
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
				</div>
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_footer.php"; ?>
			</div>
		</form>
	</body>
</html>
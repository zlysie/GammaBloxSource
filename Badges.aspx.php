<?php
	
	include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	$stmt = $con->prepare('SELECT * FROM `badges_info`;');
	$stmt->execute();
	//$stmt->store_result();
	$result = $stmt->get_result();
	$badges = [];
	while(($row = $result->fetch_assoc()) != null) {
		array_push($badges, $row);
	}

	UserUtils::LockOutUserIfNotLoggedIn();
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
		<meta name="robots" content="none">
	</head>
	<body>
		<form name="aspnetForm" method="post" action="Badges.aspx" id="aspnetForm">
			<div id="Container">
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_header.php"; ?>
				<div id="Body">
					<div id="BadgesContainer">
						<div id="ctl00_cphRoblox_aBadgesAndRankings">
							<input type="hidden" name="ctl00$cphRoblox$aBadgesAndRankings_AccordionExtender_ClientState" id="ctl00_cphRoblox_aBadgesAndRankings_AccordionExtender_ClientState" value="0">
							<div><h4 class="TopAccordionHeader">Community Badges</h4></div>
							<div style="display:block;">
								<div id="CommunityBadges">
									<div class="Legend">
										<ul class="BadgesList">
											<li id="Administrator">
												<h4>Administrator Badge</h4>
												<div><?= $badges[0]['badge_desc'] ?></div>
											</li>
											<li id="ForumModerator">
												<h4>Forum Moderator Badge</h4>
												<div><?= $badges[1]['badge_desc'] ?></div>
											</li>
											<li id="ImageModerator">
												<h4>Image Moderator Badge</h4>
												<div><?= $badges[2]['badge_desc'] ?></div>
											</li>
										</ul>
									</div>
									<div id="FeaturedBadge_Community">
										<h4>Builders Club</h4>
										<div class="FeaturedBadgeContent">
											<div class="FeaturedBadgeIcon"><img id="ctl00_cphRoblox_ctl01_iFeaturedBadge_Community" src="images/Badges/BuildersClub-125x125.png" height="125" width="125" border="0"></div>
											<p>Members of the illustrious Builders Club display this badge proudly. The Builders Club is a paid premium service. Members receive several benefits: they get ten places on their account instead of one, they earn a daily income of 15 ROBUX, they can sell their creations to others in the GAMMA Catalog, they get the ability to browse the web site without external ads, and they receive the exclusive Builders Club construction hat.</p>
										</div>
									</div>
									<div style="clear:both;"></div>
								</div>
							</div>
							<div><h4 class="AccordionHeader">Builder Badges</h4></div>
							<div style="">
								<div id="VisitsBadges">
									<div class="Legend">
										<ul class="BadgesList">
											<li id="Homestead">
												<h4>Homestead Badge</h4>
												<div><?= $badges[3]['badge_desc'] ?></div>
											</li>
											<li id="Bricksmith">
												<h4>Bricksmith Badge</h4>
												<div><?= $badges[4]['badge_desc'] ?></div>
											</li>
										</ul>
									</div>
									<div id="StatisticsRankingsPane_Visits">
									</div>
								<div style="clear:both;"></div>
								</div>
							</div>
							<div><h4 class="AccordionHeader">Friendship Badges</h4></div>
							<div style="">
								<div id="FriendshipBadges">
									<div class="Legend">
										<ul class="BadgesList">
											<li id="Friendship">
												<h4>Friendship Badge</h4>
												<div><?= $badges[5]['badge_desc'] ?></div>
											</li>
											<li id="Inviter">
												<h4>Inviter Badge</h4>
												<div><?= $badges[6]['badge_desc'] ?></div>
											</li>
										</ul>
									</div>
									<div id="StatisticsRankingsPane_Friendship"></div>
									<div style="clear:both;"></div>
								</div>
							</div>
							<div><h4 class="BottomAccordionHeader">Combat Badges</h4></div>
							<div style="">
								<div id="CombatBadges">
									<div class="Legend">
										<ul class="BadgesList">
											<li id="CombatInitiation">
												<h4>Combat Initiation Badge</h4>
												<div><?= $badges[7]['badge_desc'] ?></div>
											</li>
											<li id="Warrior">
												<h4>Warrior Badge</h4>
												<div><?= $badges[8]['badge_desc'] ?></div>
											</li>
											<li id="Bloxxer">
												<h4>Bloxxer Badge</h4>
												<div><?= $badges[9]['badge_desc'] ?></div>
											</li>
										</ul>
									</div>
									<div id="StatisticsRankingsPane_Combat"></div>
									<div style="clear:both;"></div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_footer.php"; ?>
			</div>
		</form>
	</body>
</html>
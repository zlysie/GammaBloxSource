<?php
	session_start();
	
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";

	UserUtils::LockOutUserIfNotLoggedIn();

	$_SESSION["errors"] = [];

	$user = UserUtils::GetLoggedInUser();

	$ie6 = strpos($_SERVER["HTTP_USER_AGENT"], 'MSIE 6.0') ? true : false;
	$ie7 = strpos($_SERVER["HTTP_USER_AGENT"], 'MSIE 7.0') ? true : false;
	$ie8 = strpos($_SERVER["HTTP_USER_AGENT"], 'MSIE 8.0') ? true : false;

	$stmt_rand = $con->prepare('SELECT * FROM `assets` WHERE `asset_type` = 9 AND `asset_status` = 0 ORDER BY RAND() LIMIT 0, 5');
	$stmt_rand->execute();

	$rand_places_res = $stmt_rand->get_result();

	$stmt_getlatestposts = $lfs_con->prepare('SELECT * FROM `posts` WHERE `post_creator` = "gamma" ORDER BY `post_date` DESC LIMIT 0, 5');
	$stmt_getlatestposts->execute();

	$latest_posts_res = $stmt_getlatestposts->get_result();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" id="gamma-lambda-cam">
	<head>
		<title>GAMMA: A FREE Virtual World-Building Game with Avatar Chat, 3D Environments, and Physics</title>
		<link rel="stylesheet" type="text/css" href="/CSS/AllCSS.css">
		<link rel="Shortcut Icon" type="image/ico" href="/favicon.ico">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="author" content="Zlysie">
		<meta name="description" content="GAMMA is a FREE (invite only) casual virtual world with fully constructible/desctructible 3D environments and immersive physics. Build, battle, chat, or just hang out.">
		<meta name="keywords" content="game, video game, building game, construction game, online game, LEGO game, LEGO, MMO, MMORPG, gammablox, gamma roblox, old roblox">
		<script src="/js/WebResource.js" type="text/javascript"></script>
		<script src="/js/jquery.js"></script>
	</head>
	<body>
		<form name="aspnetForm" method="post" action="/Default.aspx" id="aspnetForm">
			<div id="Container">
				<div id="AdvertisingLeaderboard"></div>
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_header.php"; ?>
				<div id="Body">
					<div id="SplashContainer">
						<div id="SignInPane">
							<div id="LoginViewContainer">
								<div id="LoginView">
									<h5>Logged In</h5>
									<a id="ctl00_cphRoblox_LoginView1_ImageFigure" href="/User.aspx" title="<?= $user->name ?>">
										<img src="/thumbs/player?id=<?= $user->id ?>&type=180" border="0" alt="<?= $user->name ?>" style="width: 100%;margin: 15px 0px;">
									</a>
								</div>
							</div>
							<br>
							<div class="RobloxNews" style="text-align: center;">
								<h3 style="color:gray;margin:15px 0px">GAMMA News</h3>
								<div>
									<?php
										while(($post = $latest_posts_res->fetch_assoc()) != null) {
											$post_name = $post['post_name'];
											$post_id = $post['post_id'];
											echo "<h4><a href=\"http://www.lambda.cam/view/$post_id\" target=\"__blank\">$post_name</a></h4>";
										}
									?>
								</div>
							</div>
						</div>
						<div id="RobloxAtAGlance">
							<h2>GAMMA Virtual Playworld</h2>
							<h3>GAMMA is Free!</h3>
							<ul id="ThingsToDo">
								<li id="Point1">
									<h3>Build your personal Place</h3>
									<div>Create buildings, vehicles, scenery, and traps with thousands of virtual bricks.</div>
								</li>
								<li id="Point2">
									<h3>Meet new friends online</h3>
									<div>Visit your friend's place, chat in 3D, and build together.</div>
								</li>
								<li id="Point3">
									<h3>Battle in the Brick Arenas</h3>
									<div>Play with the slingshot, rocket, or other brick battle tools.  Be careful not to get "bloxxed".</div>
								</li>
							</ul>
							<div id="Showcase">
								<?php if($ie6 || $ie7 || $ie8): ?>
								<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0" width="400" height="326" align="middle">
									<param name="movie" value="PlayTrailer.swf">
									<param name="quality" value="high">
									<embed src="PlayTrailer.swf" width="400" height="326" align="middle" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash">
								</object>
								<?php endif ?>
								<?php if(!($ie6 || $ie7 || $ie8)): ?>
								<video width="400" height="326" controls>
									<source src="ChristmasDemoFinal.mp4" type="video/mp4">
									Your browser does not support the video tag.
								</video> 
								<?php endif ?>
							</div>
							<div id="ctl00_cphRoblox_pForParents">
								<div id="ForParents">
									<a id="ctl00_cphRoblox_hlKidSafe" title="GAMMA is NOT kid-safe!" href="Parents.aspx" style="display:inline-block;"><img title="GAMMA is NOT kid-safe!" src="/images/anti-coppa.jpg" border="0"></a>
								</div>
							</div>
						</div>
						<div id="UserPlacesPane">
							<div id="UserPlaces_Content">
								<table id="ctl00_cphRoblox_CoolPlacesDataList" cellspacing="0" border="0" width="100%">
									<tbody>
										<tr>
											<?php
												while(($asset = $rand_places_res->fetch_assoc()) != null) {
													$place = new Place($asset);
													$place_id = $place->id;
													$place_name = $place->name;
													echo <<<EOT
													<td class="UserPlace">
														<a title="$place_name" href="/Item.aspx?ID=$place_id" style="display:inline-block;cursor:pointer;">
															<img src="/thumbs/?id=$place_id&type=420" border="0" alt="$place_name" width="120" height="70">
														</a>
													</td>
													EOT;
												}
											?>
										</tr>
									</tbody>
								</table>
							</div>
							<div id="UserPlaces_Header">
								<h3>Cool Places</h3>
								<p>Check out some of our favorite GAMMA places!</p>
							</div>
							<div id="ctl00_cphRoblox_ie6_peekaboo" style="clear: both"></div>
						</div>
					</div>
				</div>
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_footer.php"; ?>
			</div>
			<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/formvars.php"; ?>
			</div>
		</form>
	</body>
</html>
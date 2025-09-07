<?php
	session_start();
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	
	UserUtils::LockOutUserIfNotLoggedIn();

	$query = $_GET['q'] ?? "";
	$page = $_GET['p'] ?? 1;
	$page = intval($page);
	//die(strval($page));

	/*
		[ctl00$cphRoblox$tbSearch] => test
    	[__EVENTTARGET] => ctl00$cphRoblox$lbSearch
	*/

	if(isset($_POST['ctl00$cphRoblox$tbSearch']) && 
		isset($_POST['__EVENTTARGET']) && $_POST['__EVENTTARGET'] == 'ctl00$cphRoblox$lbSearch' 
		&& !empty(trim($_POST['ctl00$cphRoblox$tbSearch']))) {

		$query = urlencode($_POST['ctl00$cphRoblox$tbSearch']);
		die(header("Location: Browse.aspx?q=$query"));
	}

	$users = UserUtils::GetAllUsersPaged($page, 10, $query);
	$all_users = UserUtils::GetAllUsers($query);
	
	$page_count = intval(ceil(count($all_users)/10));

	if($page_count == 0) {
		$page_count = 1;
	}
	if($page > $page_count) {
		die(header("Location: Browse.aspx?q=$query&p=$page"));
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" id="gamma-lamnda-cam">
	<head>
		<title>GAMMA: A FREE Virtual World-Building Game with Avatar Chat, 3D Environments, and Physics</title>
		<link id="ctl00_Imports" rel="stylesheet" type="text/css" href="/CSS/AllCSS.css">
		<link id="ctl00_Favicon" rel="Shortcut Icon" type="image/ico" href="favicon.ico">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta http-equiv="Content-Language" content="en-us">
		<meta name="author" content="Zlysie">
		<meta name="description" content="GAMMA is a FREE (invite only) casual virtual world with fully constructible/desctructible 3D environments and immersive physics. Build, battle, chat, or just hang out.">
		<meta name="keywords" content="game, video game, building game, construction game, online game, LEGO game, LEGO, MMO, MMORPG, gammablox, gamma roblox, old roblox">
		<script src="/js/WebResource.js" type="text/javascript"></script>
		<style>
			#ctl00_cphRoblox_gvUsersBrowsed_ctl02_lBlurb {
				word-wrap: anywhere;
			}

			#ctl00_cphRoblox_namePanel {
				width:620px;
				text-wrap: anywhere;
			}

			.GridItem {
				height: 52px;
			}
		</style>
	</head>
	<body>
		<form name="aspnetForm" method="post" action="Browse.aspx" id="aspnetForm">
			<div id="Container">
				<div id="AdvertisingLeaderboard"></div>
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_header.php"; ?>
				<div id="Body">
					<div id="ctl00_cphRoblox_Panel1">
						<div id="BrowseContainer" style="text-align:center">
							<input name="ctl00$cphRoblox$FormSubmitWithoutOnClickEventWorkaround" type="text" value="http://aspnet.4guysfromrolla.com/articles/060805-1.aspx" id="ctl00_cphRoblox_FormSubmitWithoutOnClickEventWorkaround" style="visibility:hidden;display:none;">
							<input name="ctl00$cphRoblox$tbSearch" type="text" maxlength="100" id="ctl00_cphRoblox_tbSearch">&nbsp;<a id="ctl00_cphRoblox_lbSearch" href="javascript:__doPostBack('ctl00$cphRoblox$lbSearch','')">Search</a>
							<br><br>
							<div>
								<table class="Grid" cellspacing="0" cellpadding="4" border="0" id="ctl00_cphRoblox_gvUsersBrowsed" style="text-align:center;">
									<tbody>
										<tr class="GridHeader">
											<th scope="col">Avatar</th>
											<th scope="col">
												<a href="javascript:__doPostBack('ctl00$cphRoblox$gvUsersBrowsed','Sort$userName')">Name</a>
											</th>
											<th scope="col">Status</th>
											<th scope="col" style="width:73px">
												<a href="javascript:__doPostBack('ctl00$cphRoblox$gvUsersBrowsed','Sort$lastActivity')">Location / Last Seen</a>
											</th>
										</tr>
										<?php
											foreach($users as $user) {
												$user_id = $user->id;
												$user_name = $user->name;
												$user_blurb = $user->blurb;
												$user_status = $user->online ? "Online" : "Offline";
												$user_lastonline = $user->last_online->format('j/n/Y g:i:s A');

												if($user->online) {
													$user_lastonline = $user->GetStatus();
												}

												echo <<<EOT
													<tr class="GridItem">
														<td>
															<a id="ctl00_cphRoblox_gvUsersBrowsed_ctl02_hlAvatar" title="$user_name" href="/User.aspx?ID=$user_id" style="display:inline-block;cursor:pointer;">
															<img src="/thumbs/player?id=$user_id&type=48" width="48" height="48" border="0" alt="$user_name"></a>
														</td>
														<td id="ctl00_cphRoblox_namePanel">
															<a id="ctl00_cphRoblox_gvUsersBrowsed_ctl02_hlName" href="User.aspx?ID=$user_id">$user_name</a><br>
															<span id="ctl00_cphRoblox_gvUsersBrowsed_ctl02_lBlurb">$user_blurb</span>
														</td>
														<td>
															<span id="ctl00_cphRoblox_gvUsersBrowsed_ctl02_lblUserOnlineStatus">$user_status</span><br>
														</td>
														<td>
															<span id="ctl00_cphRoblox_gvUsersBrowsed_ctl02_lblUserLocationOrLastSeen">$user_lastonline</span>
														</td>
													</tr>
												EOT;
											}
										?>
										<tr class="GridPager">
											<td colspan="4">
												<table border="0">
													<tbody>
														<tr>
															<?php 
																if($page_count > 1) {
																	$p = 1;

																	$count = $page_count;
																	// do later im fucking TIRED
																	/*if($count > 11) {
																		$count = 11;
																	}*/
																	while($p < $count+1) {
																		$label = $p;
																		/*if($p > 10) {
																			$label = "...";
																		}*/

																		if($p == $page) {
																			echo "<td><span>$label</span></td>";
																		} else {
																			echo "<td><a href=\"Browse.aspx?q=$query&p=$p\">$label</a></td>";
																		}
																		
																		$p += 1;
																	}
																} else {
																	echo "<td><span>1</span></td>";
																}
															?>
														</tr>
													</tbody>
												</table>
											</td>
										</tr>
									</tbody>
								</table>
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
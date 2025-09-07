<?php
	include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/classes/forum.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	UserUtils::LockOutUserIfNotLoggedIn();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" id="gamma-lambda-cam">
	<head>
		<link id="ctl00_Imports" rel="stylesheet" type="text/css" href="/CSS/AllCSS.css" />
		<link id="ctl00_Favicon" rel="Shortcut Icon" type="image/ico" href="/favicon.ico" />
		<title>GAMMA Forum</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="Content-Language" content="en-us" />
		<meta name="author" content="GAMMA Corporation" />
		<meta name="description" content="GAMMA is SAFE for kids! GAMMA is a FREE casual virtual world with fully constructible/desctructible 3D environments and immersive physics.Build, battle, chat, or just hang out." />
		<meta name="keywords" content="game, video game, building game, construction game, online game, LEGO game, LEGO, MMO, MMORPG, rowblocks, rowbloks, roblocks, robloks, roblocs, roblok" />
	</head>
	<body>
		<form name="aspnetForm" method="post" action="Default.aspx" id="aspnetForm">
			<div id="Container">
				<div id="AdvertisingLeaderboard"></div>
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_header.php"; ?>
				<div id="Body">
					<link rel="stylesheet" href="/Forum/skins/default/style/default.css" type="text/css" />
					<table width="100%" cellspacing="0" cellpadding="0" border="0">
						<tr><td></td></tr>
						<tr valign="bottom">
							<td>
								<table width="100%" height="100%" cellspacing="0" cellpadding="0" border="0">
									<tr valign="top">
										<!-- left column -->
										<td class="LeftColumn">&nbsp;&nbsp;&nbsp;</td>
										<td id="ctl00_cphRoblox_LeftColumn" nowrap="nowrap" width="180" class="LeftColumn">
											<p>
												<span id="ctl00_cphRoblox_SearchRedirect">
													<table Class="tableBorder" cellSpacing="1" cellPadding="3" width="100%">
														<tr>
															<th class="tableHeaderText" align="left" colspan="2">&nbsp;Search Roblox Forums</th>
														</tr>
														<tr>
															<td class="forumRow" align="left" valign="top" colspan="2">
																<table cellspacing="1" border="0" cellpadding="2">
																	<tr>
																		<td>
																			<input name="ctl00$cphRoblox$SearchRedirect$ctl00$SearchText" type="text" maxlength="50" id="ctl00_cphRoblox_SearchRedirect_ctl00_SearchText" size="10" />
																		</td>
																		<td align="right" colspan="2">
																			<input type="submit" name="ctl00$cphRoblox$SearchRedirect$ctl00$SearchButton" value="Search" id="ctl00_cphRoblox_SearchRedirect_ctl00_SearchButton" />
																		</td>
																	</tr>
																</table>
																<span class="normalTextSmall">
																	<Br><a href="/Forum/Search/default.aspx">More search options</a>
																</span>
															</td>
														</tr>
													</table>
												</span>
												<br>
												<br>
												<span id="ctl00_cphRoblox_Whoisonline1">
													<table class="tableBorder" cellspacing="1" cellpadding="3" width="100%" style="display:none">
													<tbody>
														<tr>
															<th class="tableHeaderText" align="left" colspan="2">&nbsp;Who is Online</th>
														</tr>
														<tr>
															<td class="forumRow" valign="top">
																<p>
																	<span class="normalTextSmaller">
																		There are currently:
																		<br>
																		<b><span id="ctl00_cphRoblox_Whoisonline1_ctl00_AnonymousUsers">70</span></b> anonymous users online.<br><br>
																		<b><span id="ctl00_cphRoblox_Whoisonline1_ctl00_UsersOnline">415</span></b> registered users online
																	</span>
																</p>
																<p>
																	<span class="userOnlineLinkBold">Users</span><br>
																	<span class="moderatorOnlineLinkBold">Moderators</span>
																</p>
															</td>
														</tr>
													</tbody>
													</table>
												</span>
											</p>
										</td>

										<td class="LeftColumn">&nbsp;&nbsp;&nbsp;</td>
										<!-- center column -->
										<td class="CenterColumn">&nbsp;&nbsp;&nbsp;</td>
										<td id="ctl00_cphRoblox_CenterColumn" width="95%" class="CenterColumn">
											<span id="ctl00_cphRoblox_NavigationMenu2">
												<table width="100%" cellspacing="1" cellpadding="0">
													<tr>
														<td align="right" valign="middle">
															<a class="menuTextLink" href="/Forum/Default.aspx"><img src="/Forum/skins/default/images/icon_mini_home.gif" border="0">Home &nbsp;</a>
															<a class="menuTextLink" href="/Forum/Search/default.aspx"><img src="/Forum/skins/default/images/icon_mini_search.gif" border="0">Search &nbsp;</a>
															<a class="menuTextLink" href="/Forum/User/CreateUser.aspx"><img src="/Forum/skins/default/images/icon_mini_register.gif" border="0">Register &nbsp;</a>
														</td>
													</tr>
												</table>
											</span>
											<br>
											<table Cellpadding="0" Cellspacing="2" width="100%">
												<Tr>
													<td align="left">
														<span class="normalTextSmallBold">Current time: </span><span class="normalTextSmall">May 15, 5:37 PM</span>
													</td>
													<td align="right"></td>
												</Tr>
											</table>
											<table cellpadding="2" cellspacing="1" border="0" width="100%" class="tableBorder">
												<tr>
													<th class="tableHeaderText" colspan="2" height="20">Forum</th>
													<th class="tableHeaderText" width="50" nowrap="nowrap">&nbsp;&nbsp;Threads&nbsp;&nbsp;</th>
													<th class="tableHeaderText" width="50" nowrap="nowrap">&nbsp;&nbsp;Posts&nbsp;&nbsp;</th>
													<th class="tableHeaderText" width="135" nowrap="nowrap" style="min-width: 85px;">&nbsp;Last Post&nbsp;</th>
												</tr>
												<?php 
													foreach(ForumGroup::GetAll() as $group) {
														if($group instanceof ForumGroup) {
															$gid = $group->id;
															$gname = $group->name;
															echo <<<EOT
																<tr>
																	<td class="forumHeaderBackgroundAlternate" colspan="5" height="20">
																		<a class="forumTitle" href="/Forum/ShowForumGroup.aspx?ForumGroupID=$gid">$gname</a>
																	</td>
																</tr>
															EOT;

															foreach($group->GetForums() as $forum) {
																if($forum instanceof Forum) {
																	$fid = $forum->id;
																	$fname = $forum->name;
																	$fdesc = $forum->description;
																	$fthreadscount = $forum->GetThreadsCount();
																	$fpostscount = $forum->GetPostsCount();
																	echo <<<EOT
																	<tr>
																		<td class="forumRow" align="center" valign="top" width="34" nowrap="nowrap">
																			<img src="/Forum/skins/default/images/forum_status.gif" width="34" border="0" />
																		</td>
																		<td class="forumRow" width="80%">
																			<a class="forumTitle" href="/Forum/ShowForum.aspx?ForumID=$fid">$fname</a>
																			<span class="normalTextSmall"><br>$fdesc</span>
																		</td>
																		<td class="forumRowHighlight" align="center">
																			<span class="normalTextSmaller">$fthreadscount</span>
																		</td>
																		<td class="forumRowHighlight" align="center">
																			<span class="normalTextSmaller">$fpostscount</span>
																		</td>
																		<td class="forumRowHighlight" align="center">
																	EOT;
																	$latestPost = $forum->GetLatestPost();
																	if($latestPost != null && $latestPost instanceof Post) {
																		$username = $latestPost->poster->name;
																		$time = "05:37 PM";
																		
																		if($latestPost->isThreader) {
																			$postLink = $latestPost->id;
																		} else {
																			$postLink = $latestPost->id."#".$latestPost->thread->id;
																		}
																		
																		echo <<<EOT
																			<span class="normalTextSmaller">
																				<span><b>Today @ $time</b></span>
																			</span><BR>
																			<span class="normalTextSmaller">
																				by <a href="/Forum/User/UserProfile.aspx?UserName=$username">$username</a>
																				<a href="/Forum/ShowPost.aspx?PostID=$postLink">
																					<img border="0" src="/Forum/skins/default/images/icon_mini_topic.gif">
																				</a>
																			</span>
																		EOT;
																	} else {
																		echo <<<EOT
																			<span class="normalTextSmaller">
																				<span><b>[ null ]</b></span>
																			</span>
																		EOT;
																	}
																	echo <<<EOT
																		</td>
																	</tr>
																	EOT;
																}
															}

														}
														
													}
												?>
											</table>
											<P></P>
										</td>
										<td class="CenterColumn">&nbsp;&nbsp;&nbsp;</td>
										<!-- right margin -->
										<td class="RightColumn">&nbsp;&nbsp;&nbsp;</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</div>
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_footer.php"; ?>
			</div>
		</form>
	</body>
</html>
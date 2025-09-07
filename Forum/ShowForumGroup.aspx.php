<?php 
//ForumGroupID=1
	include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/classes/forum.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	UserUtils::LockOutUserIfNotLoggedIn();

	if(isset($_GET['ForumGroupID'])) {
		$group = ForumGroup::FromID(intval($_GET['ForumGroupID']));
		if($group == null) {
			die(header("Location: /Forum/Default.aspx"));
		} 
	} else {
		die(header("Location: /Forum/Default.aspx"));
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" id="gamma-lambda-cam">
	<head>
		<title>GAMMA Forum</title>
		<link id="ctl00_Imports" rel="stylesheet" type="text/css" href="/CSS/AllCSS.css" />
		<link id="ctl00_Favicon" rel="Shortcut Icon" type="image/ico" href="/favicon.ico" />
		<link rel="stylesheet" href="/Forum/skins/default/style/default.css" type="text/css" />
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="Content-Language" content="en-us" />
		<meta name="author" content="GAMMA Corporation" />
		<meta name="description" content="GAMMA is SAFE for kids! GAMMA is a FREE casual virtual world with fully constructible/desctructible 3D environments and immersive physics.Build, battle, chat, or just hang out." />
		<meta name="keywords" content="game, video game, building game, construction game, online game, LEGO game, LEGO, MMO, MMORPG, rowblocks, rowbloks, roblocks, robloks, roblocs, roblok" />
	</head>
	<body>
		<form name="aspnetForm" method="post" action="ShowForumGroup.aspx?ForumGroupID=<?= $group->id ?>" id="aspnetForm">
			<div id="Container">
				<div id="AdvertisingLeaderboard"></div>
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_header.php"; ?>
				<div id="Body">
					<table width="100%" cellspacing="0" cellpadding="0" border="0">
						<tr><td></td></tr>
						<tr valign="bottom">
							<td>
								<table width="100%" height="100%" cellspacing="0" cellpadding="0" border="0">
									<tr valign="top">
										<!-- left column -->
										<td>&nbsp; &nbsp; &nbsp;</td>
										<!-- center column -->
										<td id="ctl00_cphRoblox_CenterColumn" width="95%" class="CenterColumn">
											<br>
											<span id="ctl00_cphRoblox_Navigationmenu1">
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
											<span id="ctl00_cphRoblox_Whereami1">
												<table cellPadding="0" cellSpacing="0" width="100%">
													<tr>
														<td valign="top" align="left" width="1px"><nobr></nobr></td>
														<td class="popupMenuSink" valign="top" align="left" width="1px">
															<nobr>
																<a class="linkMenuSink" href="/Forum/ShowForumGroup.aspx?ForumGroupID=<?= $group->id ?>"><?= $group->name ?></a>
															</nobr>
														</td>
														<td class="popupMenuSink" valign="top" align="left" width="1px"><nobr></nobr></td>
														<td class="popupMenuSink" valign="top" align="left" width="1px"><nobr></nobr></td>
														<td valign="top" align="left" width="*">&nbsp;</td>
													</tr>
												</table>
												<span id="ctl00_cphRoblox_Whereami1_ctl00_MenuScript"></span>
											</span>
											<P></P>
											<table cellpadding="2" cellspacing="1" border="0" width="100%" class="tableBorder">
												<tr>
													<th class="tableHeaderText" colspan="2" height="20">Forum</th>
													<th class="tableHeaderText" width="50" nowrap="nowrap">&nbsp;&nbsp;Threads&nbsp;&nbsp;</th>
													<th class="tableHeaderText" width="50" nowrap="nowrap">&nbsp;&nbsp;Posts&nbsp;&nbsp;</th>
													<th class="tableHeaderText" width="135" nowrap="nowrap" style="min-width: 85px;">&nbsp;Last Post&nbsp;</th>
												</tr>
												<?php 
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
												?>
											</table>
											<P></P>
											<span id="ctl00_cphRoblox_Whereami2">
												<table cellPadding="0" cellSpacing="0" width="100%">
													<tr>
														<td valign="top" align="left" width="1px">
															<nobr>
																<a class="linkMenuSink" href="/Forum/Default.aspx">GAMMA Forum</a>
															</nobr>
														</td>
														<td class="popupMenuSink" valign="top" align="left" width="1px">
															<nobr>
																<span class="normalTextSmallBold">&nbsp;&gt;</span>
																<a class="linkMenuSink" href="/Forum/ShowForumGroup.aspx?ForumGroupID=<?= $group->id ?>"><?= $group->name ?></a>
															</nobr>
														</td>

														<td class="popupMenuSink" valign="top" align="left" width="1px"><nobr></nobr></td>
														<td class="popupMenuSink" valign="top" align="left" width="1px"><nobr></nobr></td>
														<td valign="top" align="left" width="*">&nbsp;</td>
													</tr>
												</table>
												<span id="ctl00_cphRoblox_Whereami2_ctl00_MenuScript"></span>
											</span>
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
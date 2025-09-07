<?php 
//ForumGroupID=1
	include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/classes/forum.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	UserUtils::LockOutUserIfNotLoggedIn();

	if(isset($_GET['ForumID'])) {
		$forum = Forum::FromID(intval($_GET['ForumID']));
		if($forum == null) {
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
<tbody><tr>
<td>
</td>
</tr>
<tr valign="bottom">
<td>
<table width="100%" height="100%" cellspacing="0" cellpadding="0" border="0">
<tbody><tr valign="top">
<!-- left column -->
<td>&nbsp; &nbsp; &nbsp;</td>
<!-- center column -->
<td id="ctl00_cphRoblox_CenterColumn" width="95%" class="CenterColumn">
<br>
<span id="ctl00_cphRoblox_Navigationmenu1">
<table width="100%" cellspacing="1" cellpadding="0">
<tbody><tr>
<td align="right" valign="middle">
<a class="menuTextLink" href="/Forum/Default.aspx"><img src="/Forum/skins/default/images/icon_mini_home.gif" border="0">Home &nbsp;</a>
<a class="menuTextLink" href="/Forum/Search/default.aspx"><img src="/Forum/skins/default/images/icon_mini_search.gif" border="0">Search &nbsp;</a>
<a class="menuTextLink" href="/Forum/User/CreateUser.aspx"><img src="/Forum/skins/default/images/icon_mini_register.gif" border="0">Register &nbsp;</a>
</td>
</tr>
</tbody></table>
</span>
<span id="ctl00_cphRoblox_ThreadView1">
<table cellpadding="0" width="100%">
<tbody><tr>
<td colspan="2" align="left"><span id="ctl00_cphRoblox_ThreadView1_ctl00_Whereami1" name="Whereami1">
<table cellpadding="0" cellspacing="0" width="100%">
<tbody><tr>
<td valign="top" align="left" width="1px">
<nobr>

</nobr>
</td>
<td id="ctl00_cphRoblox_ThreadView1_ctl00_Whereami2_ctl00_ForumGroupMenu" class="popupMenuSink" valign="top" align="left" width="1px">
<nobr>
<a id="ctl00_cphRoblox_ThreadView1_ctl00_Whereami2_ctl00_LinkForumGroup" class="linkMenuSink" href="/Forum/ShowForumGroup.aspx?ForumGroupID=<?= $forum->group->id ?>"><?= $forum->group->name ?></a>
</nobr>
</td>

<td id="ctl00_cphRoblox_ThreadView1_ctl00_Whereami2_ctl00_ForumMenu" class="popupMenuSink" valign="top" align="left" width="1px">
<nobr>
<span id="ctl00_cphRoblox_ThreadView1_ctl00_Whereami2_ctl00_ForumSeparator" class="normalTextSmallBold">&nbsp;&gt;</span>
<a id="ctl00_cphRoblox_ThreadView1_ctl00_Whereami2_ctl00_LinkForum" class="linkMenuSink" href="/Forum/ShowForum.aspx?ForumID=<?= $forum->id ?>"><?= $forum->name ?></a>
</nobr>
</td>

<td id="ctl00_cphRoblox_ThreadView1_ctl00_Whereami1_ctl00_PostMenu" class="popupMenuSink" valign="top" align="left" width="1px">
<nobr>


</nobr>
</td>

<td valign="top" align="left" width="*">&nbsp;</td>
</tr>
</tbody></table>

<span id="ctl00_cphRoblox_ThreadView1_ctl00_Whereami1_ctl00_MenuScript"></span></span></td>
</tr>
<tr>
<td>
&nbsp;
</td>
</tr>
<tr>
<td valign="bottom" align="left"><a id="ctl00_cphRoblox_ThreadView1_ctl00_NewThreadLinkTop" href="/Forum/AddPost.aspx?ForumID=13"><img id="ctl00_cphRoblox_ThreadView1_ctl00_NewThreadImageTop" src="/Forum/skins/default/images/newtopic.gif" border="0"></a></td>
<td align="right"><span class="normalTextSmallBold">Search 
this forum: </span>
<input name="ctl00$cphRoblox$ThreadView1$ctl00$Search" type="text" id="ctl00_cphRoblox_ThreadView1_ctl00_Search">
<input type="submit" name="ctl00$cphRoblox$ThreadView1$ctl00$SearchButton" value=" Go " id="ctl00_cphRoblox_ThreadView1_ctl00_SearchButton"></td>
</tr>
<tr>
<td valign="top" colspan="2"><table id="ctl00_cphRoblox_ThreadView1_ctl00_ThreadList" class="tableBorder" cellspacing="1" cellpadding="3" border="0" width="100%">
<tbody>
<tr>
	<th class="tableHeaderText" align="left" colspan="2" height="25">&nbsp;Thread&nbsp;</th>
	<th class="tableHeaderText" align="center" nowrap="nowrap">&nbsp;Started By&nbsp;</th>
	<th class="tableHeaderText" align="center">&nbsp;Replies&nbsp;</th>
	<th class="tableHeaderText" align="center">&nbsp;Views&nbsp;</th>
	<th class="tableHeaderText" align="center" nowrap="nowrap">&nbsp;Last Post&nbsp;</th>
</tr>
<tr>
	<td class="forumRow" align="center" valign="middle" width="25">
		<img title="Popular post" src="/Forum/skins/default/images/topic-popular.gif" border="0">
	</td>
	<td class="forumRow" height="25">
		<a class="linkSmallBold" href="/Forum/ShowPost.aspx?PostID=20276">Have a question?</a>
	</td>
	<td class="forumRowHighlight" align="left" width="100">
		&nbsp;<a class="linkSmall" href="/Forum/User/UserProfile.aspx?UserName=erik.cassel">erik.cassel</a>
	</td>
	<td class="forumRowHighlight" align="center" width="50">
		<span class="normalTextSmaller">-</span>
	</td>
	<td class="forumRowHighlight" align="center" width="50">
		<span class="normalTextSmaller">4,531</span>
	</td>
	<td class="forumRowHighlight" align="center" width="140" nowrap="nowrap">
		<span class="normalTextSmaller"><b>Pinned Post</b><br>by </span>
		<a class="linkSmall" href="/Forum/User/UserProfile.aspx?UserName=erik.cassel">erik.cassel</a>
		<a href="/Forum/ShowPost.aspx?PostID=20276#20276"><img border="0" src="/Forum/skins/default/images/icon_mini_topic.gif"></a>
	</td>
</tr>
<tr><td class="forumRow" align="center" valign="middle" width="25"><img title="Popular post" src="/Forum/skins/default/images/topic-popular.gif" border="0"></td><td class="forumRow" height="25"><a class="linkSmallBold" href="/Forum/ShowPost.aspx?PostID=324345">Happy Holidays and a great big Thank You!</a><span class="normalTextSmall"> (Page: </span><a class="linkSmall" href="/Forum/ShowPost.aspx?PostID=324345&amp;PageIndex=1">1</a><span class="normalTextSmall">, </span><a class="linkSmall" href="/Forum/ShowPost.aspx?PostID=324345&amp;PageIndex=2">2</a><span class="normalTextSmall">, </span><a class="linkSmall" href="/Forum/ShowPost.aspx?PostID=324345&amp;PageIndex=3">3</a><span class="normalTextSmall">, </span><a class="linkSmall" href="/Forum/ShowPost.aspx?PostID=324345&amp;PageIndex=4">4</a><span class="normalTextSmall">, </span><a class="linkSmall" href="/Forum/ShowPost.aspx?PostID=324345&amp;PageIndex=5">5</a><span class="normalTextSmall">)</span></td><td class="forumRowHighlight" align="left" width="100">&nbsp;<a class="linkSmall" href="/Forum/User/UserProfile.aspx?UserName=Builderman">Builderman</a></td><td class="forumRowHighlight" align="center" width="50"><span class="normalTextSmaller">110</span></td><td class="forumRowHighlight" align="center" width="50"><span class="normalTextSmaller">1,039</span></td><td class="forumRowHighlight" align="center" width="140" nowrap="nowrap"><span class="normalTextSmaller"><b>Pinned Post</b><br>by </span><a class="linkSmall" href="/Forum/User/UserProfile.aspx?UserName=Maxanmum">Maxanmum</a><a href="/Forum/ShowPost.aspx?PostID=324345#338688"><img border="0" src="/Forum/skins/default/images/icon_mini_topic.gif"></a></td></tr>
<tr><td class="forumRow" align="center" valign="middle" width="25"><img title="Post (Not Read)" src="/Forum/skins/default/images/topic_notread.gif" border="0"></td><td class="forumRow" height="25"><a class="linkSmallBold" href="/Forum/ShowPost.aspx?PostID=338856">Balance, or die! </a></td><td class="forumRowHighlight" align="left" width="100">&nbsp;<a class="linkSmall" href="/Forum/User/UserProfile.aspx?UserName=XxFirexX">XxFirexX</a></td><td class="forumRowHighlight" align="center" width="50"><span class="normalTextSmaller">-</span></td><td class="forumRowHighlight" align="center" width="50"><span class="normalTextSmaller">4</span></td><td class="forumRowHighlight" align="center" width="140" nowrap="nowrap"><span class="normalTextSmaller"><b>Today @ 11:01 PM</b><br>by </span><a class="linkSmall" href="/Forum/User/UserProfile.aspx?UserName=XxFirexX">XxFirexX</a><a href="/Forum/ShowPost.aspx?PostID=338856#338856"><img border="0" src="/Forum/skins/default/images/icon_mini_topic.gif"></a></td></tr>
<tr><td class="forumRow" align="center" valign="middle" width="25"><img title="Post (Not Read)" src="/Forum/skins/default/images/topic_notread.gif" border="0"></td><td class="forumRow" height="25"><a class="linkSmallBold" href="/Forum/ShowPost.aspx?PostID=338496">400 posts is top 100 poster?</a></td><td class="forumRowHighlight" align="left" width="100">&nbsp;<a class="linkSmall" href="/Forum/User/UserProfile.aspx?UserName=supermario444">supermario444</a></td><td class="forumRowHighlight" align="center" width="50"><span class="normalTextSmaller">8</span></td><td class="forumRowHighlight" align="center" width="50"><span class="normalTextSmaller">55</span></td><td class="forumRowHighlight" align="center" width="140" nowrap="nowrap"><span class="normalTextSmaller"><b>Today @ 11:01 PM</b><br>by </span><a class="linkSmall" href="/Forum/User/UserProfile.aspx?UserName=diehardbob">diehardbob</a><a href="/Forum/ShowPost.aspx?PostID=338496#338855"><img border="0" src="/Forum/skins/default/images/icon_mini_topic.gif"></a></td></tr>
<tr><td class="forumRow" align="center" valign="middle" width="25"><img title="Post (Not Read)" src="/Forum/skins/default/images/topic_notread.gif" border="0"></td><td class="forumRow" height="25"><a class="linkSmallBold" href="/Forum/ShowPost.aspx?PostID=170962">Chefs Wanted!</a></td><td class="forumRowHighlight" align="left" width="100">&nbsp;<a class="linkSmall" href="/Forum/User/UserProfile.aspx?UserName=cuisine">cuisine</a></td><td class="forumRowHighlight" align="center" width="50"><span class="normalTextSmaller">21</span></td><td class="forumRowHighlight" align="center" width="50"><span class="normalTextSmaller">108</span></td><td class="forumRowHighlight" align="center" width="140" nowrap="nowrap"><span class="normalTextSmaller"><b>Today @ 11:00 PM</b><br>by </span><a class="linkSmall" href="/Forum/User/UserProfile.aspx?UserName=herooftime101">herooftime101</a><a href="/Forum/ShowPost.aspx?PostID=170962#338853"><img border="0" src="/Forum/skins/default/images/icon_mini_topic.gif"></a></td></tr>
<tr><td class="forumRow" align="center" valign="middle" width="25"><img title="Post (Not Read)" src="/Forum/skins/default/images/topic_notread.gif" border="0"></td><td class="forumRow" height="25"><a class="linkSmallBold" href="/Forum/ShowPost.aspx?PostID=338603">OMG WHAT TELEMONS NAME MEANS!</a></td><td class="forumRowHighlight" align="left" width="100">&nbsp;<a class="linkSmall" href="/Forum/User/UserProfile.aspx?UserName=matter61">matter61</a></td><td class="forumRowHighlight" align="center" width="50"><span class="normalTextSmaller">14</span></td><td class="forumRowHighlight" align="center" width="50"><span class="normalTextSmaller">96</span></td><td class="forumRowHighlight" align="center" width="140" nowrap="nowrap"><span class="normalTextSmaller"><b>Today @ 11:00 PM</b><br>by </span><a class="linkSmall" href="/Forum/User/UserProfile.aspx?UserName=Boltryke">Boltryke</a><a href="/Forum/ShowPost.aspx?PostID=338603#338850"><img border="0" src="/Forum/skins/default/images/icon_mini_topic.gif"></a></td></tr>
<tr><td class="forumRow" align="center" valign="middle" width="25"><img title="Post (Not Read)" src="/Forum/skins/default/images/topic_notread.gif" border="0"></td><td class="forumRow" height="25"><a class="linkSmallBold" href="/Forum/ShowPost.aspx?PostID=338057">The Grand Range Competition!</a></td><td class="forumRowHighlight" align="left" width="100">&nbsp;<a class="linkSmall" href="/Forum/User/UserProfile.aspx?UserName=GrandRange">GrandRange</a></td><td class="forumRowHighlight" align="center" width="50"><span class="normalTextSmaller">7</span></td><td class="forumRowHighlight" align="center" width="50"><span class="normalTextSmaller">44</span></td><td class="forumRowHighlight" align="center" width="140" nowrap="nowrap"><span class="normalTextSmaller"><b>Today @ 10:56 PM</b><br>by </span><a class="linkSmall" href="/Forum/User/UserProfile.aspx?UserName=diehardbob">diehardbob</a><a href="/Forum/ShowPost.aspx?PostID=338057#338835"><img border="0" src="/Forum/skins/default/images/icon_mini_topic.gif"></a></td></tr>
<tr><td class="forumRow" align="center" valign="middle" width="25"><img title="Post (Not Read)" src="/Forum/skins/default/images/topic_notread.gif" border="0"></td><td class="forumRow" height="25"><a class="linkSmallBold" href="/Forum/ShowPost.aspx?PostID=338832">music video being made at my place!</a></td><td class="forumRowHighlight" align="left" width="100">&nbsp;<a class="linkSmall" href="/Forum/User/UserProfile.aspx?UserName=builderkirby2">builderkirby2</a></td><td class="forumRowHighlight" align="center" width="50"><span class="normalTextSmaller">-</span></td><td class="forumRowHighlight" align="center" width="50"><span class="normalTextSmaller">5</span></td><td class="forumRowHighlight" align="center" width="140" nowrap="nowrap"><span class="normalTextSmaller"><b>Today @ 10:54 PM</b><br>by </span><a class="linkSmall" href="/Forum/User/UserProfile.aspx?UserName=builderkirby2">builderkirby2</a><a href="/Forum/ShowPost.aspx?PostID=338832#338832"><img border="0" src="/Forum/skins/default/images/icon_mini_topic.gif"></a></td></tr>
<tr><td class="forumRow" align="center" valign="middle" width="25"><img title="Popular post" src="/Forum/skins/default/images/topic-popular.gif" border="0"></td><td class="forumRow" height="25"><a class="linkSmallBold" href="/Forum/ShowPost.aspx?PostID=335048">NEED SIX ADMINS</a><span class="normalTextSmall"> (Page: </span><a class="linkSmall" href="/Forum/ShowPost.aspx?PostID=335048&amp;PageIndex=1">1</a><span class="normalTextSmall">, </span><a class="linkSmall" href="/Forum/ShowPost.aspx?PostID=335048&amp;PageIndex=2">2</a><span class="normalTextSmall">, </span><a class="linkSmall" href="/Forum/ShowPost.aspx?PostID=335048&amp;PageIndex=3">3</a><span class="normalTextSmall">)</span></td><td class="forumRowHighlight" align="left" width="100">&nbsp;<a class="linkSmall" href="/Forum/User/UserProfile.aspx?UserName=Patninja1">Patninja1</a></td><td class="forumRowHighlight" align="center" width="50"><span class="normalTextSmaller">61</span></td><td class="forumRowHighlight" align="center" width="50"><span class="normalTextSmaller">359</span></td><td class="forumRowHighlight" align="center" width="140" nowrap="nowrap"><span class="normalTextSmaller"><b>Today @ 10:43 PM</b><br>by </span><a class="linkSmall" href="/Forum/User/UserProfile.aspx?UserName=Patninja1">Patninja1</a><a href="/Forum/ShowPost.aspx?PostID=335048#338808"><img border="0" src="/Forum/skins/default/images/icon_mini_topic.gif"></a></td></tr>
<tr><td class="forumRow" align="center" valign="middle" width="25"><img title="Post (Not Read)" src="/Forum/skins/default/images/topic_notread.gif" border="0"></td><td class="forumRow" height="25"><a class="linkSmallBold" href="/Forum/ShowPost.aspx?PostID=338774">ninja foo</a></td><td class="forumRowHighlight" align="left" width="100">&nbsp;<a class="linkSmall" href="/Forum/User/UserProfile.aspx?UserName=Zyuka">Zyuka</a></td><td class="forumRowHighlight" align="center" width="50"><span class="normalTextSmaller">2</span></td><td class="forumRowHighlight" align="center" width="50"><span class="normalTextSmaller">29</span></td><td class="forumRowHighlight" align="center" width="140" nowrap="nowrap"><span class="normalTextSmaller"><b>Today @ 10:36 PM</b><br>by </span><a class="linkSmall" href="/Forum/User/UserProfile.aspx?UserName=Zyuka">Zyuka</a><a href="/Forum/ShowPost.aspx?PostID=338774#338786"><img border="0" src="/Forum/skins/default/images/icon_mini_topic.gif"></a></td></tr>
<tr><td class="forumRow" align="center" valign="middle" width="25"><img title="Post (Not Read)" src="/Forum/skins/default/images/topic_notread.gif" border="0"></td><td class="forumRow" height="25"><a class="linkSmallBold" href="/Forum/ShowPost.aspx?PostID=338183">OMG Can a builders club member get ban for life?!</a></td><td class="forumRowHighlight" align="left" width="100">&nbsp;<a class="linkSmall" href="/Forum/User/UserProfile.aspx?UserName=supermario444">supermario444</a></td><td class="forumRowHighlight" align="center" width="50"><span class="normalTextSmaller">18</span></td><td class="forumRowHighlight" align="center" width="50"><span class="normalTextSmaller">108</span></td><td class="forumRowHighlight" align="center" width="140" nowrap="nowrap"><span class="normalTextSmaller"><b>Today @ 10:34 PM</b><br>by </span><a class="linkSmall" href="/Forum/User/UserProfile.aspx?UserName=okami101">okami101</a><a href="/Forum/ShowPost.aspx?PostID=338183#338776"><img border="0" src="/Forum/skins/default/images/icon_mini_topic.gif"></a></td></tr>
<tr>
<td class="forumHeaderBackgroundAlternate" colspan="6">&nbsp;</td>
</tr>
</tbody></table><span id="ctl00_cphRoblox_ThreadView1_ctl00_Pager"><table cellspacing="0" cellpadding="0" border="0" width="100%">
<tbody><tr>
<td><span class="normalTextSmallBold">Page 1 of 521</span></td><td align="right"><span><span class="normalTextSmallBold">Goto to page: </span><a id="ctl00_cphRoblox_ThreadView1_ctl00_Pager_Page0" class="normalTextSmallBold" href="javascript:__doPostBack('ctl00$cphRoblox$ThreadView1$ctl00$Pager$Page0','')">1</a><span class="normalTextSmallBold">, </span><a id="ctl00_cphRoblox_ThreadView1_ctl00_Pager_Page1" class="normalTextSmallBold" href="javascript:__doPostBack('ctl00$cphRoblox$ThreadView1$ctl00$Pager$Page1','')">2</a><span class="normalTextSmallBold">, </span><a id="ctl00_cphRoblox_ThreadView1_ctl00_Pager_Page2" class="normalTextSmallBold" href="javascript:__doPostBack('ctl00$cphRoblox$ThreadView1$ctl00$Pager$Page2','')">3</a><span class="normalTextSmallBold"> ... </span><a id="ctl00_cphRoblox_ThreadView1_ctl00_Pager_Page519" class="normalTextSmallBold" href="javascript:__doPostBack('ctl00$cphRoblox$ThreadView1$ctl00$Pager$Page519','')">520</a><span class="normalTextSmallBold">, </span><a id="ctl00_cphRoblox_ThreadView1_ctl00_Pager_Page520" class="normalTextSmallBold" href="javascript:__doPostBack('ctl00$cphRoblox$ThreadView1$ctl00$Pager$Page520','')">521</a><span class="normalTextSmallBold">&nbsp;</span><a id="ctl00_cphRoblox_ThreadView1_ctl00_Pager_Next" class="normalTextSmallBold" href="javascript:__doPostBack('ctl00$cphRoblox$ThreadView1$ctl00$Pager$Next','')">Next</a></span></td>
</tr>
</tbody></table></span></td>
</tr>
<tr>
<td colspan="2">
&nbsp;
</td>
</tr>
<tr>
<td align="left" valign="top">
<span id="ctl00_cphRoblox_ThreadView1_ctl00_Whereami2" name="Whereami2">
<table cellpadding="0" cellspacing="0" width="100%">
<tbody><tr>
<td valign="top" align="left" width="1px">
<nobr>
<a id="ctl00_cphRoblox_ThreadView1_ctl00_Whereami2_ctl00_LinkHome" class="linkMenuSink" href="/Forum/Default.aspx">GAMMA Forum</a>
</nobr>
</td>
<td id="ctl00_cphRoblox_ThreadView1_ctl00_Whereami2_ctl00_ForumGroupMenu" class="popupMenuSink" valign="top" align="left" width="1px">
<nobr>
<span id="ctl00_cphRoblox_ThreadView1_ctl00_Whereami2_ctl00_ForumGroupSeparator" class="normalTextSmallBold">&nbsp;&gt;</span>
<a id="ctl00_cphRoblox_ThreadView1_ctl00_Whereami2_ctl00_LinkForumGroup" class="linkMenuSink" href="/Forum/ShowForumGroup.aspx?ForumGroupID=<?= $forum->group->id ?>"><?= $forum->group->name ?></a>
</nobr>
</td>

<td id="ctl00_cphRoblox_ThreadView1_ctl00_Whereami2_ctl00_ForumMenu" class="popupMenuSink" valign="top" align="left" width="1px">
<nobr>
<span id="ctl00_cphRoblox_ThreadView1_ctl00_Whereami2_ctl00_ForumSeparator" class="normalTextSmallBold">&nbsp;&gt;</span>
<a id="ctl00_cphRoblox_ThreadView1_ctl00_Whereami2_ctl00_LinkForum" class="linkMenuSink" href="/Forum/ShowForum.aspx?ForumID=<?= $forum->id ?>"><?= $forum->name ?></a>
</nobr>
</td>

<td id="ctl00_cphRoblox_ThreadView1_ctl00_Whereami2_ctl00_PostMenu" class="popupMenuSink" valign="top" align="left" width="1px">
<nobr>


</nobr>
</td>

<td valign="top" align="left" width="*">&nbsp;</td>
</tr>
</tbody></table>

<span id="ctl00_cphRoblox_ThreadView1_ctl00_Whereami2_ctl00_MenuScript"></span></span>

</td>
<td align="right">
<span class="normalTextSmallBold">Display threads for: </span>
<select name="ctl00$cphRoblox$ThreadView1$ctl00$DisplayByDays" id="ctl00_cphRoblox_ThreadView1_ctl00_DisplayByDays">
<option selected="selected" value="0">All Days</option>
<option value="1">Today</option>
<option value="3">Past 3 Days</option>
<option value="7">Past Week</option>
<option value="14">Past 2 Weeks</option>
<option value="30">Past Month</option>
<option value="90">Past 3 Months</option>
<option value="180">Past 6 Months</option>
<option value="360">Past Year</option>
</select>
<br>
<a id="ctl00_cphRoblox_ThreadView1_ctl00_MarkAllRead" class="linkSmallBold" href="javascript:__doPostBack('ctl00$cphRoblox$ThreadView1$ctl00$MarkAllRead','')">Mark all threads as read</a>
<br>
<span class="normalTextSmallBold">
</span>
</td>
</tr>
<tr>
<td colspan="2">&nbsp;
</td>
</tr>
</tbody></table>
</span>
</td>
<td class="CenterColumn">&nbsp;&nbsp;&nbsp;</td>
<!-- right margin -->
<td class="RightColumn">&nbsp;&nbsp;&nbsp;</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
				</div>
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_footer.php"; ?>
			</div>
		</form>
	</body>
</html>
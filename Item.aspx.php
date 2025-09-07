<?php
	
	/**
	 * Returns human friendly time ago
	 * @param mixed $time
	 * @return string
	 */
	function humanTiming($i_time) {
		$time = time() - $i_time; // to get the time since that moment
		//echo time() . " " . $i_time;
		$time = ($time<1)? 1 : $time;
		$tokens = array (
			31536000 => 'year',
			2592000 => 'month',
			604800 => 'week',
			86400 => 'day', 
			3600 => 'hour',
			60 => 'minute',
			1 => 'second'
		);

		foreach ($tokens as $unit => $text) {
			if ($time < $unit) continue;
			$numberOfUnits = floor($time / $unit);
			return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'')." ago";
		}
	}

	session_start();
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/assetutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/transactionutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/friending.php";

	UserUtils::LockOutUserIfNotLoggedIn();
	

	// get logged in user
	$user = UserUtils::GetLoggedInUser();
	global $user;

	if(!isset($_GET['ID']) && !isset($_GET['id'])) {
		die(header("Location: /Catalog.aspx"));
	}

	$item_id = intval($_GET['ID'] ?? $_GET['id']);
	$item = AssetUtils::GetAsset($item_id);
	if($item == null) {
		die(header("Location: /Catalog.aspx"));
	}

	$blockedchars = array('𒐫', '‮', '﷽', '𒈙', '⸻ ', '꧅');

	switch($item->type) {
		case Asset::TSHIRT:
			$category = "T-Shirt";
			break;
		case Asset::HAT:
			$category = "Hat";
			break;
		case Asset::PLACE:
			$category = "Place";
			break;
		case Asset::MODEL:
			$category = "Model";
			break;
		case Asset::SHIRT:
			$category = "Shirt";
			break;
		case Asset::PANTS:
			$category = "Pants";
			break;
		case Asset::DECAL:
			$category = "Decal";
			break;
	}

	$creator = $item->creator;
	if($item->type != Asset::PLACE) {
		$asset_thumburl = "/thumbs/?id=$item_id";
	} else {
		$asset_thumburl = "/thumbs/?id=$item_id&type=420";
	}
	

	$admin_view = $user != null && $user->IsAdmin();
	if($admin_view) {
		if($item->status != Asset::REJECTED) {
			if($item->type != Asset::PLACE) {
				$asset_thumburl = "/thumbs/?id=$item_id";
			} else {
				$asset_thumburl = "/thumbs/?id=$item_id&type=420";
			}
		} 
		
	}

	if(isset($_POST['__EVENTTARGET']) && $_POST['__EVENTTARGET'] == 'ctl00$cphRoblox$FavoriteThisButton') {
		$item->Favourite();
		die(header("Location: /Item.aspx?ID=$item_id"));
	}

	$item_owner = $user != null && $user->id == $creator->id;

	if($user != null && isset($_POST['__EVENTTARGET'])) {
		if($_POST['__EVENTTARGET'] == 'ctl00$cphRoblox$TabbedInfo$CommentaryTab$CommentsPane$PostComment' &&
		isset($_POST['ctl00$cphRoblox$TabbedInfo$CommentaryTab$CommentsPane$txtBody'])) {
			$comment = substr(trim(str_replace($blockedchars, '', $_POST['ctl00$cphRoblox$TabbedInfo$CommentaryTab$CommentsPane$txtBody'])), 0, 256);
			if(strlen($comment) > 2) {
				$stmt_postcomment = $con->prepare('INSERT INTO `comments`(`comment_item`, `comment_poster`, `comment_body`) VALUES (?,?,?)');
				$stmt_postcomment->bind_param('iis', $item_id, $user->id, $comment);
				$stmt_postcomment->execute();
				die(header("Location: /Item.aspx?id=".$item_id));
			} else {
				$com_error = "Comment was too short!";
			}
		}
	}

	if($user != null) {
		$favourite_status = AssetUtils::IsItemFavourited($item_id);
		$friends_with = FriendUtils::isUserFriendsWith($user->id, $item->creator->id) || $item_owner;

		$com_page = 1;

		if(isset($_POST['__EVENTTARGET']) && isset($_POST['__EVENTARGUMENT']) && $_POST['__EVENTTARGET'] == 'ctl00$cphRoblox$TabbedInfo$CommentaryTab$CommentsPane$CommentsRepeater$ctl11$PageSelector_Next') {
			if(intval($_POST['__EVENTARGUMENT']) != 0) {
				//die(strval(intval($_POST['__EVENTARGUMENT'])));
				$com_page = intval($_POST['__EVENTARGUMENT']);
			}
		}

		$comments = array();

		$stmt_getallcomments = $con->prepare("SELECT * FROM `comments` WHERE `comment_item` = ?");
		$stmt_getallcomments->bind_param('i', $item_id);
		$stmt_getallcomments->execute();
		$result = $stmt_getallcomments->get_result();
		$allcomments_count = $result->num_rows;
		$stmt_getcomments = $con->prepare("SELECT * FROM `comments` WHERE `comment_item` = ? LIMIT ?, ?");
		$com_page_calc = ($com_page*10)-10;
		$rows = $com_page*10;

		$stmt_getcomments->bind_param('iii', $item_id, $com_page_calc, $rows);
		$stmt_getcomments->execute();
	
		$result = $stmt_getcomments->get_result();
	
		if($result->num_rows != 0) {
			while($row = $result->fetch_assoc()) {
				$row['comment_body'] = str_replace("<", "&lt;", str_replace(">", "&gt;", $row['comment_body']));
				array_push($comments, $row);
			}
		}

		$comments_count = $result->num_rows;

		$total_pages = ceil(intval($allcomments_count/10));

		$asset_owned = TransactionUtils::DoesUserOwnAsset($user->id, $item_id);

		$category_label = $category;
		if(!str_ends_with($category_label, 's')) {
			$category_label .= "s";
		}
	}

	if($item instanceof Place) {
		if($item->copylocked) {
			$copylabel = "CopyLocked";
		} else {
			$copylabel = "Shared";
		}
	}
	
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" id="gamma-lambda-cam">
	<head>
		<title><?= $item->name ?> - GAMMA <?= $category_label ?></title>
		<link id="ctl00_Imports" rel="stylesheet" type="text/css" href="/CSS/AllCSS.css?t=<?= time() ?>">
		<link id="ctl00_Imports" rel="stylesheet" type="text/css" href="/CSS/tabs.css">
		<link rel="Shortcut Icon" type="image/ico" href="/favicon.ico">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="author" content="Zlysie">
		<meta name="description" content="GAMMA is a FREE (invite only) casual virtual world with fully constructible/desctructible 3D environments and immersive physics. Build, battle, chat, or just hang out.">
		<meta name="keywords" content="game, video game, building game, construction game, online game, LEGO game, LEGO, MMO, MMORPG, gammablox, gamma roblox, old roblox">
		<script src="/js/json2.js" type="text/javascript"></script>
		<script src="/js/jquery.js" type="text/javascript"></script>
		<script src="/js/jquery-ui.js" type="text/javascript"></script>
		<script src="/js/jquery-modal.js?t=<?= time() ?>" type="text/javascript"></script>
		<script src="/js/WebResource.js" type="text/javascript"></script>
		<script src="/js/PlaceLauncher.js" type="text/javascript"></script>
		<?php if($admin_view): ?>
			<script>
				function reject() {
					$.post( "/Admin/postadmin", { id: <?= $item_id ?>, type: "deny" }).done(function( data ) {
						alert("Successfully rejected asset.");
						location.reload();
					});
				}
				
				function accept() {
					$.post( "/Admin/postadmin", { id: <?= $item_id ?>, type: "accept" }).done(function( data ) {
						alert("Successfully accepted asset.");
						location.reload();
					});
				}

				function item_delete() {
					$.post( "/Admin/postadmin", { id: <?= $item_id ?>, type: "delete" }).done(function( data ) {
						location.reload();
					});
				}

				function render() {
					$.post( "/Admin/postadmin", { id: <?= $item_id ?>, type: "render" }).done(function( data ) {
						location.reload();
					});
				}
			</script>
		<?php endif ?>
		<script>
			function openPurchasePanel() {
				$("#ctl00_cphRoblox_ItemPurchasePopupPanel").modal({escapeClose: false, clickClose: false, showClose: false}); 
			}

			function purchase() {
				document.getElementById('VerifyPurchase_Free').style.display = 'none';
				document.getElementById('ProcessPurchase_Free').style.display = 'block';

				$.post( "/api/purchase", { asset_id: <?= $item_id ?> }).done(function( data ) {
					document.getElementById('VerifyPurchase_Free').style.display = 'none';
					document.getElementById('ProcessPurchase_Free').style.display = 'none';
					if(data.error) {
						

						var errorpanel = $("#PurchaseFailed");
						$(errorpanel.find("p")[0]).html(data.message);
						errorpanel.css("display", "block");
					} else {
						var errorpanel = $("#PurchaseSuccess");
						errorpanel.css("display", "block");
					}
				});

				return false;
			}
		</script>
	</head>
	<body>
		<form name="aspnetForm" method="post" action="/Item.aspx?ID=<?= $item_id ?>" id="aspnetForm">
			<div id="Container">
				<div id="AdvertisingLeaderboard"></div>
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_header.php"; ?>
				<div id="Body">
					<div id="ItemContainer">
						<div id="Item">
							<h2><?= $item->name ?></h2>
							<div id="Details">
								<div id="Summary">
									<h3>GAMMA <?= $category ?></h3>
									
									<?php if($item instanceof BuyableAsset && $item->onsale): ?>
									<div id="ctl00_cphRoblox_TicketsPurchasePanel">	
										<?php if($item->tux != 0): ?>
										<div id="TicketsPurchase">
											<div id="PriceInTickets">Tux: <?= $item->tux ?></div>
											<div id="BuyWithTickets">
												<a id="ctl00_cphRoblox_PurchaseWithTicketsButton" class="Button" <?php if(!$asset_owned): ?>href="javascript:openPurchasePanel()">Buy with Tux<?php endif ?><?php if($asset_owned): ?>>Owned<?php endif ?></a>
											</div>
										</div>
										<?php endif ?>
										<?php if($item->bux != 0): ?>
										<div id="RobuxPurchase">
											<div id="PriceInRobux">R$: <?= $item->bux ?></div>
											<div id="BuyWithRobux">
												<a id="ctl00_cphRoblox_PurchaseWithRobuxButton" class="Button" href="javascript:__doPostBack('ctl00$cphRoblox$PurchaseWithRobuxButton','')">Buy with R$</a>
											</div>
										</div>
										<?php endif ?>
										<?php if($item->tux == 0 && $item->bux == 0): ?>
										<div id="RobuxPurchase">
											<div id="PriceInRobux"></div>
											<div id="BuyWithRobux">
												<a id="ctl00_cphRoblox_PurchaseWithRobuxButton" class="Button" <?php if(!$asset_owned): ?>href="javascript:openPurchasePanel()">Take One!<?php endif ?><?php if($asset_owned): ?>>Owned<?php endif ?></a>
											</div>
										</div>
										<?php endif ?>
									</div>
									<?php endif ?>
									<div id="Creator" class="Creator">
										<div class="Avatar">
											<a id="ctl00_cphRoblox_AvatarImage" title="<?= $creator->name ?>" href="/User.aspx?ID=<?= $creator->id ?>" style="display:inline-block;cursor:pointer;height:100px;text-align:center;">
												<img src="/thumbs/player?id=<?= $creator->id ?>&type=100" border="0" alt="<?= $creator->name ?>" blankurl="http://t6.roblox.com:80/blank-100x100.gif" style="height:100px;">
											</a>
										</div>
										Creator: <a id="ctl00_cphRoblox_CreatorHyperLink" href="User.aspx?ID=<?= $creator->id ?>"><?= $creator->name ?></a>
									</div>
									<div id="LastUpdate">Updated: <?= humanTiming($item->GetLastUpdateTimestamp()); ?></div>
									<div id="Favorited">Favorited: <?= $item->favourites ?> times</div>
									<?php if(strlen(trim($item->description))): ?>
									<div id="ctl00_cphRoblox_DescriptionPanel">
										<div id="DescriptionLabel">Description:</div>
										<div id="Description"><?= $item->description ?></div>
									</div>
									<?php endif ?>
									<div id="ReportAbuse">
										<div id="ctl00_cphRoblox_AbuseReportButton1_AbuseReportPanel" class="ReportAbusePanel">
											<span class="AbuseIcon"><a id="ctl00_cphRoblox_AbuseReportButton1_ReportAbuseIconHyperLink" href="AbuseReport/AssetVersion.aspx?ID=2299151&amp;ReturnUrl=%2fItem.aspx%3fID%3d2018209"><img src="images/abuse.PNG" alt="Report Abuse" border="0"></a></span>
											<span class="AbuseButton"><a id="ctl00_cphRoblox_AbuseReportButton1_ReportAbuseTextHyperLink" href="AbuseReport/AssetVersion.aspx?ID=2299151&amp;ReturnUrl=%2fItem.aspx%3fID%3d2018209">Report Abuse</a></span>
										</div>
									</div>
								</div>
								
								<?php if($item->type != Asset::PLACE): ?>
								<div id="Thumbnail">
									<a id="ctl00_cphRoblox_AssetThumbnailImage" disabled="disabled" title="<?= $item->name ?>" onclick="return false" style="display:inline-block;">
										<img src="<?= $asset_thumburl ?>" style="width:250px;height:250px;" border="0" alt="<?= $item->name ?>" blankurl="http://t6.roblox.com:80/blank-250x250.gif">
									</a>
								</div>
								<?php if($item_owner || $admin_view): ?>
								<div id="Configuration">
									<a href="/My/Item.aspx?ID=<?= $item_id ?>">Configure this <?= $category ?></a>
									<?php if($admin_view): ?>
									
									<?php if($item->status == Asset::PENDING): ?>
									<br><br><a href="javascript:accept()">Approve this <?= $category ?></a><br>
									<?php endif ?>

									<br><a href="javascript:render()">Re-render this <?= $category ?></a><br>
									
									<?php if($item->status == Asset::PENDING): ?>
									<br><a href="javascript:reject()">Reject this <?= $category ?></a><br>
									<?php endif ?>

									<?php if($item->status == Asset::ACCEPTED): ?>
									<br><a href="javascript:item_delete()">Delete this <?= $category ?></a><br>
									<?php endif ?>

									<?php endif ?>
								</div>
								<?php endif ?>
								<div id="Actions">
									<a id="ctl00_cphRoblox_FavoriteThisButton" 
										<?php if($user != null && !$favourite_status): ?>href="javascript:__doPostBack('ctl00$cphRoblox$FavoriteThisButton','');"<?php endif ?>
										<?php if($user != null && $favourite_status): ?>href="#" style="color:#555" <?php endif ?>  >Favorite
									</a>
								</div>
								
								<?php endif ?>
								<?php if($item instanceof Place && $item->type == Asset::PLACE): ?>
									<div id="Thumbnail_Place">
										<a id="ctl00_cphRoblox_AssetThumbnailImage_Place" disabled="disabled" title="<?= $item->name ?>" onclick="return false" style="display:inline-block;">
											<img src="<?= $asset_thumburl ?>" border="0" alt="<?= $item->name ?>" blankurl="http://t1.roblox.com:80/blank-420x230.gif">
										</a>
									</div>
									<div id="Actions_Place">
										<a id="ctl00_cphRoblox_FavoriteThisButton" 
											<?php if($user != null && !$favourite_status): ?>href="javascript:__doPostBack('ctl00$cphRoblox$FavoriteThisButton','');"<?php endif ?>
											<?php if($user != null && $favourite_status): ?>href="#" style="color:#555" <?php endif ?>  >Favorite
										</a>
									</div>
									<?php if($item_owner || $admin_view): ?>
									<div id="Configuration">
										<a href="/My/Item.aspx?ID=<?= $item_id ?>">Configure this <?= $category ?></a>
										<?php if($admin_view): ?>
										
										<?php if($item->status == Asset::PENDING): ?>
										<br><br><a href="javascript:accept()">Approve this <?= $category ?></a><br>
										<?php endif ?>

										<br><a href="javascript:render()">Re-render this <?= $category ?></a><br>
										
										<?php if($item->status == Asset::PENDING): ?>
										<br><a href="javascript:reject()">Reject this <?= $category ?></a><br>
										<?php endif ?>

										<?php endif ?>
									</div>
									<?php endif ?>
									
							
									<div id="ctl00_cphRoblox_PlayGames" class="PlayGames">
										<div style="text-align: center; margin: 1em 5px;">
											<?php if($item->friends_only): ?>
											<?php if(!$friends_with): ?>
											<span style="display:inline"><img src="images/locked.png" alt="Locked" border="0">&nbsp;Friends-only</span>
											<?php endif ?>
											<?php if($friends_with): ?>
											<span style="display:inline"><img src="images/unlocked.png" alt="Unlocked" border="0">&nbsp;Friends-only: You have access</span>
											<?php endif ?>
											<?php endif ?>
											<?php if(!$item->friends_only): ?>
											<span style="display:inline"><img src="images/public.png" alt="Public" border="0">&nbsp;Public</span>
											<?php endif ?>
											<img src="images/<?= $copylabel ?>.png" alt="<?= $copylabel ?>" border="0">
											Copy Protection: <?= $copylabel ?>
										</div>
										<div id="ctl00_cphRoblox_VisitButtons_VisitMPButton" style="display:inline">
											<?php if((!$item->friends_only || ($item->friends_only) && $friends_with) || $item->friends_only && $friends_with): ?>
											<button class="Button" onclick="Gamma.PlaceLauncher.JoinGame(<?= $item_id ?>); return false;">Visit Online</button>
											<?php else: ?>
											<button class="Button" disabled>Visit Online</button>
											<?php endif ?>
										</div>
										<div id="ctl00_cphRoblox_VisitButtons_VisitButton" style="display:inline">
											&nbsp;&nbsp;&nbsp;
											<?php if(!$item->copylocked && (!$item->friends_only || ($item->friends_only) && $friends_with) || $item_owner ): ?>
											<button class="Button" onclick="Gamma.PlaceLauncher.VisitPlace(<?= $item->id ?>); return false;">Visit Solo</button>
											<?php endif ?>

											<?php if($item->copylocked && $item->friends_only && $friends_with && !$item_owner): ?>
											<button class="Button" onclick="Gamma.PlaceLauncher.VisitPlace(<?= $item->id ?>); return false;">Visit Solo</button>
											<?php endif ?>

											<?php if($item->copylocked && !($item->friends_only && $friends_with) && !$item_owner): ?>
											<button class="Button" disabled>Visit Solo</button>
											<?php endif ?>
											
										</div>
										<?php if($item_owner): ?>
										<div id="ctl00_cphRoblox_VisitButtons_EditButton" style="display:inline">
											&nbsp;&nbsp;&nbsp;
											<button class="Button" onclick="Gamma.PlaceLauncher.EditPlace(<?= $item->id ?>); return false;">Edit</button>
										</div>
										<?php endif ?>
									</div>
								<?php endif ?>	
								
								
								<?php if($item_owner): ?>
									<div id="ctl00_cphRoblox_ItemOwnershipPanel">
										<div id="Ownership">
											<a id="ctl00_cphRoblox_RemoveFromInventoryButton" class="Button" href="javascript:__doPostBack('ctl00$cphRoblox$RemoveFromInventoryButton','')">Delete from My Stuff</a>
										</div>
									</div>
								<?php endif ?>
								<div style="clear: both;"></div>
							</div>
							
							<div style="margin: 10px; width: 703px;">
								<div class="ajax__tab_xp ajax__tab_container ajax__tab_default " id="ctl00_cphRoblox_TabbedInfo">
									
									<!--<span class="ajax__tab_outer">
										<span class="ajax__tab_inner">
											<div id="ctl00_cphRoblox_TabbedInfo_header">
												<span class="ajax__tab_tab" id="__tab_ctl00_cphRoblox_TabbedInfo_CommentaryTab"></span>
											</div>
										</span>
									</span>-->
									<script>
										$(function() {
											$('.ajax__tab_header .ajax__tab').click(function(){
												var t = $(this).attr('id');
												if(!$(this).hasClass('ajax__tab_active')){ //this is the start of our condition 
													$('.ajax__tab_header .ajax__tab').attr('class', 'ajax__tab');
													$(this).addClass('ajax__tab_active');

													$('.ajax__tab_panel').hide();
													$('#'+ t + '_panel').show();
												}
											});
											Gamma.PlaceLauncher.LoadServers(<?= $item_id ?>);
										});
									</script>
									<div class="ajax__tab_header" style="height: 21px;">
										<?php if($item->type == Asset::PLACE): ?>
										<span id="games" class="ajax__tab ajax__tab_active" style="display: inline-block;">
											<span class="ajax__tab_outer">
												<span class="ajax__tab_inner">
													<span class="ajax__tab_tab" id="__tab_ctl00_SampleContent_Tabs_Panel1"><h3>Games</h3></span>
												</span>
											</span>
										</span>
										<?php endif ?>
										
										<span id="comments" class="ajax__tab <?php if($item->type != Asset::PLACE): ?>ajax__tab_active<?php endif ?>" style="display: inline-block;">
											<span class="ajax__tab_outer">
												<span class="ajax__tab_inner">
													<span class="ajax__tab_tab" id="__tab_ctl00_SampleContent_Tabs_Panel1"><h3>Commentary</h3></span>
												</span>
											</span>
										</span>
										
									</div>
									
									<div id="server_template" aria-disabled="true" style="display:none">
										<tr>
											<div id="joinpanel">
												<p>0 of 8 players max</p>
												<p><button class="Button" onclick="return false;">Join</button></p>
												<br>
											</div>
											<div id="playerspanel">
												<br>
											</div>
										</tr>
									</div>

									<div id="ctl00_cphRoblox_TabbedInfo_body" class="ajax__tab_body">
										<?php if($item->type == Asset::PLACE): ?>
										<div id="games_panel" class="ajax__tab_panel">
											<div id="ctl00_cphRoblox_TabbedInfo_GamesTab_RunningGamesUpdatePanel">
												<table>
													<tbody>
														<tr>
															<td>
																<p>No games</p>
															</td>
														</tr>
													</tbody>
												</table>
												<div class="FooterPager" style="text-align: center;">
													<span id="ctl00_cphRoblox_TabbedInfo_GamesTab_RunningGamesDataPager_Footer"><a disabled="disabled">First</a>&nbsp;<a disabled="disabled">Previous</a>&nbsp;<span>1</span>&nbsp;<a disabled="disabled">Next</a>&nbsp;<a disabled="disabled">Last</a>&nbsp;</span>
												</div>
												<div class="RefreshRunningGames">
													<input type="submit" value="Refresh" id="ctl00_cphRoblox_TabbedInfo_GamesTab_RefreshRunningGamesButton" onclick="Gamma.PlaceLauncher.LoadServers(<?= $item_id ?>); return false;" class="Button">
												</div>
											</div>
										</div>
										<?php endif ?>

										<?php if($user != null && $item->comments_enabled): ?>
										<div id="comments_panel" class="ajax__tab_panel" <?php if($item->type == Asset::PLACE): ?>style="display:none"<?php endif ?>>
											<div id="ctl00_cphRoblox_TabbedInfo_CommentaryTab_CommentsPane_CommentsUpdatePanel">
												<div class="CommentsContainer">
													<!-- 10 comments per page -->
													 <?php if($user != null): ?>
													<h3>Comments (<?= $allcomments_count ?>)</h3>
													<?php if($allcomments_count > 10): ?>
													<div id="ctl00_cphRoblox_TabbedInfo_CommentaryTab_CommentsPane_CommentsRepeater_ctl00_HeaderPagerPanel" class="HeaderPager">
														<?php if($com_page != 1): ?><a href="javascript:__doPostBack('ctl00$cphRoblox$TabbedInfo$CommentaryTab$CommentsPane$CommentsRepeater$ctl11$PageSelector_Next','<?= $com_page-1 ?>')"><span class="NavigationIndicators">&lt;&lt;</span> Back</a><?php endif ?>	
														<span id="ctl00_cphRoblox_TabbedInfo_CommentaryTab_CommentsPane_CommentsRepeater_ctl00_HeaderPagerLabel">Page <?= $com_page ?> of <?= $total_pages ?></span>
														<?php if($com_page < $total_pages): ?><a href="javascript:__doPostBack('ctl00$cphRoblox$TabbedInfo$CommentaryTab$CommentsPane$CommentsRepeater$ctl11$PageSelector_Next','<?= $com_page+1 ?>')">Next <span class="NavigationIndicators">&gt;&gt;</span></a><?php endif ?>	
														
													</div>
													<?php endif ?>
													<?php 
														if($comments_count != 0) {
															$counter = 0;
															foreach($comments as $com) {
																$com_poster = User::FromID($com['comment_poster']);
																$poster_id = $com_poster->id;
																$poster_name = $com_poster->name;
																
																$comment_content = str_replace(PHP_EOL, '', trim($com['comment_body']));
																$alt = "AlternateComment";
																if($counter % 2 == 0) {
																	$alt = "Comment";
																}

																$comment_date = humanTiming(strtotime($com['comment_timesent']));

																echo <<<EOT
																<div class="Comments">
																	<div class="$alt">
																		<div class="Commenter">
																			<div class="Avatar">
																				<a title="$poster_name" href="/User.aspx?ID=$poster_id" style="display:inline-block;cursor:pointer;">
																					<img src="/thumbs/player?id=$poster_id&type=64" width="64" height="64" border="0" alt="$poster_name" blankurl="http://t6.roblox.com:80/blank-64x64.gif">
																				</a>
																			</div>
																		</div>
																		<div class="Post">
																			<div class="Audit">
																				Posted $comment_date by
																				<a href="User.aspx?ID=$poster_id">$poster_name</a>
																			</div>
																			<div class="Content">$comment_content</div>
																			<div class="ReportAbusePanel">
																				<span class="AbuseIcon"><a href="AbuseReport/AssetVersion.aspx?ID=2299151&amp;ReturnUrl=%2fItem.aspx%3fID%3d2018209"><img src="images/abuse.PNG" alt="Report Abuse" border="0"></a></span>
																				<span class="AbuseButton"><a href="AbuseReport/AssetVersion.aspx?ID=2299151&amp;ReturnUrl=%2fItem.aspx%3fID%3d2018209">Report Abuse</a></span>
																			</div>
																		</div>
																		<div style="clear: both;"></div>
																	</div>
																</div>
																EOT;
																$counter += 1;
															}
														}
													?>
													<?php if($allcomments_count > 10): ?>
													<div id="ctl00_cphRoblox_TabbedInfo_CommentaryTab_CommentsPane_CommentsRepeater_ctl11_FooterPagerPanel" class="FooterPager">
														<?php if($com_page != 1): ?><a href="javascript:__doPostBack('ctl00$cphRoblox$TabbedInfo$CommentaryTab$CommentsPane$CommentsRepeater$ctl11$PageSelector_Next','<?= $com_page-1 ?>')"><span class="NavigationIndicators">&lt;&lt;</span> Back</a><?php endif ?>	
														<span id="ctl00_cphRoblox_TabbedInfo_CommentaryTab_CommentsPane_CommentsRepeater_ctl11_FooterPagerLabel">Page <?= $com_page ?> of <?= $total_pages ?></span>
														<?php if($com_page < $total_pages): ?><a href="javascript:__doPostBack('ctl00$cphRoblox$TabbedInfo$CommentaryTab$CommentsPane$CommentsRepeater$ctl11$PageSelector_Next','<?= $com_page+1 ?>')">Next <span class="NavigationIndicators">&gt;&gt;</span></a><?php endif ?>
													</div>
													<?php endif ?>
													<?php if(isset($com_error)): ?>
														<div style="color:red"><?= $com_error ?></div>
													<?php endif ?>
													<div class="PostAComment">
														<h3>Comment on this <?= $category ?></h3>
														<textarea name="ctl00$cphRoblox$TabbedInfo$CommentaryTab$CommentsPane$txtBody" id="ctl00_cphRoblox_rbxMessageEditor_txtBody" class="MultilineTextBox" style="height:80px"></textarea>
														<div class="Buttons">
															<a class="Button" href="javascript:__doPostBack(&quot;ctl00$cphRoblox$TabbedInfo$CommentaryTab$CommentsPane$PostComment&quot;, &quot;&quot;)">Post Comment</a>
														</div>
													</div>
													<?php endif ?>
												</div>
												
											</div>
										</div>
										<?php endif ?>
									</div>
								</div>
							</div>
							
						</div>
					</div>
					<div class="Ads_WideSkyscraper"></div> <!-- 160x600 -->
					<div style="clear: both;"></div>
					<?php if($item instanceof BuyableAsset): ?>
					<div id="ctl00_cphRoblox_ItemPurchasePopupPanel" class="modalPopup" style="width:27em;display: none">
						<?php if($item->tux == 0 && $item->bux == 0): ?>
						<div id="ctl00_cphRoblox_ItemPurchasePopupUpdatePanel">
							<div id="VerifyPurchase_Free" style="margin: 1.5em;">
								<h3>Free Item:</h3>
								<p>
									<?= ucwords($category) ?> "<?= $item->name ?>" from <?= $creator->name ?> is available in the Public Domain. 
									Would you like to add it to your inventory for free?
								</p>
								<p>
									<input type="submit" name="ctl00$cphRoblox$ProceedWithFreePurchaseButton" value="Add it!" onclick="purchase(); return false;" id="ctl00_cphRoblox_ProceedWithFreePurchaseButton" class="MediumButton" style="width:100%;" />
								</p>
								<p>
									<input type="submit" name="ctl00$cphRoblox$CancelFreePurchaseButton" value="Cancel" onclick="$.modal.close();return false;" id="ctl00_cphRoblox_CancelFreePurchaseButton" class="MediumButton" style="width:100%;" />
								</p>
							</div>
							<div id="ProcessPurchase_Free" style="display:none">
								<div id="Processing_Free">
									<img id="ctl00_cphRoblox_ProcessingFreePurchaseIconImage" src="/images/ProgressIndicator2.gif" alt="Processing..." align="middle" style="border-width:0px;" />&nbsp&nbsp;
									Processing transaction ...
								</div>
							</div>
						</div>
						<div id="PurchaseFailed" style="display:none;margin: 1.5em;">
							<h3>Purchase Failed</h3>
							<p style="color:red;font-weight:bold;">
							</p>
							<p>
								<a href="/Catalog.aspx?c=<?= $item->type ?>">Continue Shopping</a>
							</p>
							<p>
								<a href="/My/Character.aspx">Customize Character</a>
							</p>
						</div>
						<div id="PurchaseSuccess" style="display:none;margin: 1.5em;">
							<h3>Purchase Complete</h3>
							<p>
								You have added <?= ucwords($category) ?> "<?= $item->name ?>" from <?= $creator->name ?> in to your inventory! 
							</p>
							<p>
								<a href="/Catalog.aspx?c=<?= $item->type ?>">Continue Shopping</a>
							</p>
							<p>
								<a href="/My/Character.aspx">Customize Character</a>
							</p>
						</div>
						<?php endif ?>
						<?php if($item->tux != 0): ?>
						<div id="ctl00_cphRoblox_ItemPurchasePopupUpdatePanel">
							<div id="VerifyPurchase_Free" style="margin: 1.5em;">
								<h3>Purchase Item:</h3>
								<p>
									Would you like to purchase <?= ucwords($category) ?> "<?= $item->name ?>" from <?= $creator->name ?> for Tux: <?= $item->tux ?>? 
								</p>
								<p>
									Your balance after this transaction will be Tux: <?= TransactionUtils::GetNetTicketsFromUser($user->id) - $item->tux ?>.
								</p>
								<p>
									<input type="submit" name="ctl00$cphRoblox$ProceedWithPurchaseButton" value="Buy now!" onclick="purchase(); return false;" id="ctl00_cphRoblox_ProceedWithFreePurchaseButton" class="MediumButton" style="width:100%;" />
								</p>
								<p>
									<input type="submit" name="ctl00$cphRoblox$CancelPurchaseButton" value="Cancel" onclick="$.modal.close();return false;" id="ctl00_cphRoblox_CancelFreePurchaseButton" class="MediumButton" style="width:100%;" />
								</p>
							</div>
							<div id="ProcessPurchase_Free" style="display:none;margin: 1.5em;">
								<div id="Processing_Free">
									<img id="ctl00_cphRoblox_ProcessingFreePurchaseIconImage" src="/images/ProgressIndicator2.gif" alt="Processing..." align="middle" style="border-width:0px;" />&nbsp&nbsp;
									Processing transaction ...
								</div>
							</div>
							<div id="PurchaseSuccess" style="display:none;margin: 1.5em;">
								<h3>Purchase Complete</h3>
								<p>
									You have successfully purchased <?= ucwords($category) ?> "<?= $item->name ?>" from <?= $creator->name ?> for Tux: <?= $item->tux ?>. 
								</p>
								<p>
									<a href="/Catalog.aspx?c=<?= $item->type ?>">Continue Shopping</a>
								</p>
								<p>
									<a href="/My/Character.aspx">Customize Character</a>
								</p>
							</div>
							<div id="PurchaseFailed" style="display:none;margin: 1.5em;">
								<h3>Purchase Failed</h3>
								<p style="color:red;font-weight:bold;">
								</p>
								<p>
									<a href="/Catalog.aspx?c=<?= $item->type ?>">Continue Shopping</a>
								</p>
								<p>
									<a href="/My/Character.aspx">Customize Character</a>
								</p>
							</div>
						</div>
						<?php endif ?>
					</div>
					<?php endif ?>
				</div>
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_footer.php"; ?>
			</div>
			<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/formvars.php"; ?>
		</form>
	</body>
</html>
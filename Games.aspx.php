<?php
	session_start();
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/assetutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/gameutils.php";

	UserUtils::LockOutUserIfNotLoggedIn();
	
	/**
	 * Returns human friendly time ago
	 * @param mixed $time
	 * @return string
	 */
	function humanTiming($i_time) {
		$time = time() - $i_time; // to get the time since that moment

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
	$domain = $_SERVER['SERVER_NAME'];
	if(isset($_GET['feed']) && $_GET['feed'] == "rss") {
		header("Content-Type: application/rss+xml");
		$assets = AssetUtils::GetAssetsPagedByName(9, "", 1, 12, "Now", "MostPopular");
		echo <<<EOT
		<?xml version="1.0" encoding="utf-8"?>
		<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:fh="http://purl.org/syndication/history/1.0">
			<channel>
				<atom:link href="http://$domain/Games.aspx?feed=rss" rel="self" type="application/rss+xml" />
				<title>GAMMA Games - Most Popular (Now)</title>
				<link>http://$domain/Games.aspx</link>
				<description>A feed of GAMMA Games</description>
				<copyright>Copyright 2008, GAMMA Corporation</copyright>
				<generator>GAMMA RSS</generator>
				<pubDate>Thu, 01 May 2008 08:22:06 GMT</pubDate>
				<docs>http://cyber.law.harvard.edu/rss/rss.html</docs>
				<fh:complete />
				<image>
					<url>http://$domain/images/logo_rss.PNG</url>
					<title>GAMMA Games - Most Popular (Now)</title>
					<link>http://$domain/Games.aspx</link>
					<width>118</width>
					<height>31</height>
				</image>
		EOT;
		if(count($assets) != 0) {
			foreach($assets as $asset) {
				$asset_id = $asset->id;
				$asset_name = $asset->name;
				$asset_description = $asset->description;
				$date = $asset->last_updated;
				$asset_lastupdate = $date->format('m/d/Y h:i:s A');
				$creator = $asset->creator;
				$creator_id = $creator->id;
				$creator_name = $creator->name;
				$asset_favcount = $asset->favourites;

				switch($asset->status) {
					case 0:
						$asset_thumburl = "/thumbs/?id=$asset_id&type=420";
						break;
					case 1:
						$asset_thumburl = "/images/review-pending.png";
						break;
					case -1:
						$asset_thumburl = "/images/unavail-120x120.png";
						break;
				}

				echo <<<EOT
					<item>
						<title>$asset_name</title>
						<link>http://$domain/Item.aspx?ID=$asset_id</link>
						<guid>http://$domain/Item.aspx?ID=$asset_id</guid>
						<pubDate>$asset_lastupdate</pubDate>
						<description>
							&lt;p&gt;
								&lt;a href="http://$domain/Item.aspx?ID=$asset_id" title="$asset_name"&gt;
									&lt;img src="http://$domain/thumbs/?id=$asset_id&type=420" width="160" height="100" autocomplete="$asset_name" /&gt;
								&lt;/a&gt;
							&lt;/p&gt;
							&lt;p&gt;
								$asset_description
							&lt;/p&gt;
						</description>
					</item>
				EOT;
			}
		}
		echo <<<EOT

			</channel>
		</rss>
		EOT;
		die();
	}

	$mode = $_GET['m'] ?? 'MostPopular'; // similar to lua, this or that
	$time_period = $_GET['t'] ?? 'Now';
	$page = intval($_GET['p'] ?? '1');
	
	$modes = array("MostPopular", "RecentlyUpdated");

	if(!in_array($mode, $modes)) {
		die(header("Location: Games.aspx?m=MostPopular&t=$time_period"));
	}

	//15 assets at once
	$all_assets = AssetUtils::GetAllAssetsByName(9, "", $time_period, $mode);
	$page_count = ceil(intval(count($all_assets)/15));

	$assets = AssetUtils::GetAssetsPagedByName(9, "", $page, 15, $time_period, $mode);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" id="<?= str_replace(".", "-", $domain) ?>">
	<head>
		<title>GAMMA Games - <?php 
			$splitwords = preg_split("/(?<=[a-z])(?=[A-Z])/", $mode);
			foreach($splitwords as $word) {
				echo $word." ";
			}
			
			$splitwords = preg_split("/(?<=[a-z])(?=[A-Z])/", $time_period);
			$joined = "";
			foreach($splitwords as $word) {
				$joined .= $word." ";
			}
			echo "(".trim($joined).")";
		?></title>
		<link rel="stylesheet" type="text/css" href="/CSS/AllCSS.css">
		<link rel="Shortcut Icon" type="image/ico" href="/favicon.ico">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="author" content="Zlysie">
		<meta name="description" content="GAMMA is a FREE (invite only) casual virtual world with fully constructible/desctructible 3D environments and immersive physics. Build, battle, chat, or just hang out.">
		<meta name="keywords" content="game, video game, building game, construction game, online game, LEGO game, LEGO, MMO, MMORPG, gammablox, gamma roblox, old roblox">
		<link rel="alternate" type="application/rss+xml" title="GAMMA Games - Most Popular (Now)" href="http://<?= $domain ?>/Games.aspx?feed=rss">
		<script src="/js/WebResource.js" type="text/javascript"></script>
	</head>
	<body>
		<form name="aspnetForm" method="post" action="Games.aspx" id="aspnetForm">
			<div id="Container">
				<div id="AdvertisingLeaderboard">
					<script type="text/javascript" src=""></script>
					<noscript>
						<a href="" target="_blank">
							<img src="" width="728" height="90" border="1" />
						</a>
					</noscript>
				</div>
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_header.php"; ?>
				<div id="Body">
					<div id="GamesContainer">
						<div id="ctl00_cphRoblox_rbxGames_GamesContainerPanel">
							<div class="DisplayFilters">
								<h2>Games&nbsp;<a href="/Games.aspx?feed=rss"><img src="images/feed-icons/feed-icon-14x14.png" alt="RSS" border="0"></a></h2>
								<div id="BrowseMode">
									<h4>Browse</h4>
									<ul>
										<li>
											<?php if($mode == "MostPopular"): ?><img class="GamesBullet" src="images/games_bullet.png" border="0"><?php endif ?>
											<a href="Games.aspx?m=MostPopular&amp;t=Now">
												<?php if($mode == "MostPopular"): ?><b><?php endif ?>
													Most Popular
												<?php if($mode == "MostPopular"): ?></b><?php endif ?>
											</a>
										</li>
										<li>
											<?php if($mode == "RecentlyUpdated"): ?><img class="GamesBullet" src="images/games_bullet.png" border="0"><?php endif ?>
											<a href="Games.aspx?m=RecentlyUpdated">
												<?php if($mode == "RecentlyUpdated"): ?><b><?php endif ?>
													Recently Updated
												<?php if($mode == "RecentlyUpdated"): ?></b><?php endif ?>
											</a>
										</li>
										<li><a href="User.aspx?ID=1">Featured Games</a></li>
									</ul>
								</div>
								<div id="ctl00_cphRoblox_rbxCatalog_Timespan">
									<h4>Time</h4>
									<ul>
										<li>
											<?php if($time_period == "Now"): ?><img class="GamesBullet" src="images/games_bullet.png" border="0"><?php endif ?>
											<a href="Games.aspx?m=<?= $mode ?>&amp;t=PastDay">
												<?php if($time_period == "Now"): ?><b><?php endif ?>
													Now
												<?php if($time_period == "Now"): ?></b><?php endif ?>
											</a>
										</li>
										<li>
											<?php if($time_period == "PastDay"): ?><img class="GamesBullet" src="images/games_bullet.png" border="0"><?php endif ?>
											<a href="Games.aspx?m=<?= $mode ?>&amp;t=PastDay">
												<?php if($time_period == "PastDay"): ?><b><?php endif ?>
													Past Day
												<?php if($time_period == "PastDay"): ?></b><?php endif ?>
											</a>
										</li>
										<li>
											<?php if($time_period == "PastWeek"): ?><img class="GamesBullet" src="images/games_bullet.png" border="0"><?php endif ?>
											<a href="Games.aspx?m=<?= $mode ?>&amp;t=PastWeek">
												<?php if($time_period == "PastWeek"): ?><b><?php endif ?>
													Past Week
												<?php if($time_period == "PastWeek"): ?></b><?php endif ?>
											</a>
										</li>
										<li>
											<?php if($time_period == "PastMonth"): ?><img class="GamesBullet" src="images/games_bullet.png" border="0"><?php endif ?>	
											<a href="Games.aspx?m=<?= $mode ?>&amp;t=PastMonth">
												<?php if($time_period == "PastMonth"): ?><b><?php endif ?>
													Past Month
												<?php if($time_period == "PastMonth"): ?></b><?php endif ?></a>
										</li>
										<li>
											<?php if($time_period == "AllTime"): ?><img class="GamesBullet" src="images/games_bullet.png" border="0"><?php endif ?>
											<a href="Games.aspx?m=<?= $mode ?>&amp;t=AllTime">
												<?php if($time_period == "AllTime"): ?><b><?php endif ?>
													All-time
												<?php if($time_period == "AllTime"): ?></b><?php endif ?></a>
											</a>
										</li>
									</ul>
								</div>
							</div>
							<div id="Games">
								<span class="GamesDisplaySet">
									<?php 
										$splitwords = preg_split("/(?<=[a-z])(?=[A-Z])/", $mode);
										foreach($splitwords as $word) {
											echo $word." ";
										}
										
										$splitwords = preg_split("/(?<=[a-z])(?=[A-Z])/", $time_period);
										$joined = "";
										foreach($splitwords as $word) {
											$joined .= $word." ";
										}
										echo "(".trim($joined).")";
									?>
								</span>
								<?php if(count($all_assets) > 15): ?>
								<div id="ctl00_cphRoblox_rbxCatalog_HeaderPagerPanel" class="HeaderPager">
									<?php if($page > 1): ?>
									<a id="ctl00_cphRoblox_rbxCatalog_HeaderPagerHyperLink_Back" href="Games.aspx?m=<?= $mode ?>&amp;t=<?= $time_period ?>&amp;p=<?= $page-1 ?>"><span class="NavigationIndicators">&lt;&lt;</span> Back</a>
									<?php endif ?>
									<span id="ctl00_cphRoblox_rbxCatalog_HeaderPagerLabel">Page <?= $page ?> of <?= $page_count ?></span>
									<?php if($page != $page_count): ?>
									<a id="ctl00_cphRoblox_rbxCatalog_HeaderPagerHyperLink_Next" href="Games.aspx?m=<?= $mode ?>&amp;t=<?= $time_period ?>&amp;p=<?= $page+1 ?>">Next <span class="NavigationIndicators">&gt;&gt;</span></a>
									<?php endif ?>
								</div>
								<?php endif?>
								<table id="ctl00_cphRoblox_rbxGames_dlGames" cellspacing="0" align="Center" border="0" width="550">
									<tbody>
										<?php 
											if(count($assets) != 0) {
												$asset_iterator_count = 0;
												$asset_count = 0;
												$asset_totalcount = count($assets);
												foreach($assets as $asset) {
													if($asset instanceof Place) {
														if($asset_iterator_count == 0) {
															echo "<tr>";
														}
														
														$asset_id = $asset->id;
														$asset_name = $asset->name;
														$creator = $asset->creator;
														$creator_id = $creator->id;
														$creator_name = $creator->name;
														$asset_lastupdate = humanTiming($asset->GetLastUpdateTimestamp());
														$asset_favcount = $asset->favourites;
	
														$place_visitcount = $asset->visits;
	
														switch($asset->status) {
															case 0:
																$asset_thumburl = "/thumbs/?id=$asset_id&type=420";
																break;
															case 1:
																$asset_thumburl = "/images/review-pending.png";
																break;
															case -1:
																$asset_thumburl = "/images/unavail-120x120.png";
																break;
														}
	
														$asset_playersonline = $asset->currently_playing;
	
														echo <<<EOT
														<td class="Game" valign="top">
															<div style="padding-bottom:5px">
																<div class="GameThumbnail">
																	<a title="$asset_name" href="Item.aspx?ID=$asset_id" style="display:inline-block;cursor:pointer;">
																		<img src="$asset_thumburl" border="0" alt="$asset_name" width="160" height="100">
																	</a>
																</div>
																<div class="GameDetails">
																	<div class="GameName"><a href="Item.aspx?ID=$asset_id">$asset_name</a></div>
																	<div class="GameLastUpdate"><span class="Label">Updated:</span> <span class="Detail">$asset_lastupdate</span></div>
																	<div class="GameCreator"><span class="Label">Creator:</span> <span class="Detail"><a href="User.aspx?ID=$creator_id">$creator_name</a></span></div>
																	<div class="GamePlays"><span class="Label">Played:</span> <span class="Detail">$place_visitcount times</span></div>
																	<div id="GameCurrentPlayers">
																		<div class="GameCurrentPlayers"><span class="DetailHighlighted">$asset_playersonline players online</span></div>
																	</div>
																</div>
															</div>
														</td>
														EOT;
														$asset_iterator_count = ($asset_iterator_count+1)%3;
														$asset_count += 1;
	
														if($asset_totalcount == $asset_count && $asset_iterator_count < 3) {
															$iterations = 3 - $asset_iterator_count;
															for ($i = 0; $i < $iterations; $i++) {
																echo "<td valign=\"top\"><div class=\"Asset\"></div></td>";
															}
															
														}
														if($asset_iterator_count == 3) {
															echo "</tr>";
														}
													}
												}
											} else {
												echo <<<EOT
													<tr>
														<td>
															<div>Looks like theres no places to gawk at...</div>
														</td>
													</tr>
												EOT;
											}
											
										?>
									</tbody>
								</table>
								<?php if(count($all_assets) > 15): ?>
								<div id="ctl00_cphRoblox_rbxCatalog_FooterPagerPanel" class="FooterPager">
									<?php if($page > 1): ?>
									<a id="ctl00_cphRoblox_rbxCatalog_FooterPagerHyperLink_Back" href="Games.aspx?m=<?= $mode ?>&amp;t=<?= $time_period ?>&amp;p=<?= $page-1 ?>"><span class="NavigationIndicators">&lt;&lt;</span> Back</a>
									<?php endif ?>
									<span id="ctl00_cphRoblox_rbxCatalog_FooterPagerLabel">Page <?= $page ?> of <?= $page_count ?></span>
									<?php if($page != $page_count): ?>
									<a id="ctl00_cphRoblox_rbxCatalog_FooterPagerHyperLink_Next" href="Games.aspx?m=<?= $mode ?>&amp;t=<?= $time_period ?>&amp;p=<?= $page+1 ?>">Next <span class="NavigationIndicators">&gt;&gt;</span></a>
									<?php endif ?>
								</div>
								<?php endif?>
								
							</div>
						</div>
						<div class="Ads_WideSkyscraper">
							<a href="" target="_blank">
								<img src="/images/ads/jerma_sky160x600.png" width="160" height="600" border="1" />
							</a>
						</div>
						<div style="clear: both;"></div>
					</div>
				</div>
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_footer.php"; ?>
			</div>
			<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/formvars.php"; ?>
		</form>
	</body>
</html>
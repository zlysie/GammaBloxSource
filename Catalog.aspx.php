<?php
	session_start();
	
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

	$mode = $_GET['m'] ?? 'TopFavorites'; // similar to lua, this or that
	$category = intval($_GET['c'] ?? '8');
	$time_period = $_GET['t'] ?? 'PastWeek';
	$page = intval($_GET['p'] ?? '1');
	$query = urldecode($_GET['q']) ?? '';

	$modes = array("TopFavorites", "BestSelling", "RecentlyUpdated", "ForSale", "PublicDomain");

	if(!in_array($mode, $modes)) {
		die(header("Location: Catalog.aspx?m=TopFavorites&c=$category&t=$time_period&d=All&q=$query"));
	}

	//20 assets at once
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/assetutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";

	UserUtils::LockOutUserIfNotLoggedIn();

	if(isset($_POST['ctl00$cphRoblox$rbxCatalog$SearchButton']) && isset($_POST['ctl00$cphRoblox$rbxCatalog$SearchTextBox']) && !empty(trim($_POST['ctl00$cphRoblox$rbxCatalog$SearchTextBox']))) {
		$query = urlencode($_POST['ctl00$cphRoblox$rbxCatalog$SearchTextBox']);
		die(header("Location: Catalog.aspx?m=$mode&c=$category&t=$time_period&d=All&q=$query"));
	}
	$all_assets = AssetUtils::GetAllAssetsByName($category, $query, $time_period, $mode);
	$assets = AssetUtils::GetAssetsPagedByName($category, $query, $page, 20, $time_period, $mode);

	$page_count = intval(count($all_assets)/20);
	if(count($all_assets) - ($page_count*20) != 0) {
		$page_count += 1;
	}

	if($page > $page_count && $page_count != 0) {
		die(header("Location: Catalog.aspx?m=$mode&c=$category&t=$time_period&d=All&q=$query"));
	}

	switch($category) {
		case 2:
			$category_label = "T-Shirt";
			break;
		case 8:
			$category_label = "Hat";
			break;
		case 9:
			$category_label = "Place";
			break;
		case 10:
			$category_label = "Model";
			break;
		case 11:
			$category_label = "Shirt";
			break;
		case 12:
			$category_label = "Pant";
			break;
		case 13:
			$category_label = "Decal";
			break;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" id="gamma-lambda-cam"><!-- MachineID: App1 -->
	<head>
		<title>Gamma - Catalog</title>
		<link rel="stylesheet" type="text/css" href="/CSS/AllCSS.css">
		<link rel="Shortcut Icon" type="image/ico" href="/favicon.ico">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="author" content="Zlysie">
		<meta name="description" content="GAMMA is a FREE (invite only) casual virtual world with fully constructible/desctructible 3D environments and immersive physics. Build, battle, chat, or just hang out.">
		<meta name="keywords" content="game, video game, building game, construction game, online game, LEGO game, LEGO, MMO, MMORPG, gammablox, gamma roblox, old roblox">
		<script src="/js/WebResource.js" type="text/javascript"></script>
	</head>
	<body>
		<form name="aspnetForm" method="post" action="Catalog.aspx?m=<?= $mode ?>&amp;c=<?= $category ?>" id="aspnetForm">
			<div id="Container">
				<div id="AdvertisingLeaderboard"><!-- 728x90 --></div>
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_header.php"; ?>
				<div id="Body">
					<div id="CatalogContainer">
						<div id="SearchBar" class="SearchBar">
							<span class="SearchBox"><input name="ctl00$cphRoblox$rbxCatalog$SearchTextBox" type="text" maxlength="100" id="ctl00_cphRoblox_rbxCatalog_SearchTextBox" class="TextBox"></span>
							<span class="SearchButton"><input type="submit" name="ctl00$cphRoblox$rbxCatalog$SearchButton" value="Search" id="ctl00_cphRoblox_rbxCatalog_SearchButton"></span>
						</div>
						<div class="DisplayFilters">
							<h2>Catalog</h2>
							<div id="BrowseMode">
								<h4>Browse</h4>
								<ul>
									<li>
										<?php if($mode == "TopFavorites"): ?><img class="GamesBullet" src="images/games_bullet.png" border="0"><?php endif ?>
										<a href="Catalog.aspx?m=TopFavorites&amp;c=<?= $category ?>&amp;t=AllTime&amp;d=All">
											<?php if($mode == "TopFavorites"): ?><b><?php endif ?>
												Top Favorites
											<?php if($mode == "TopFavorites"): ?></b><?php endif ?>
										</a>
									</li>
									<li>
										<?php if($mode == "BestSelling"): ?><img class="GamesBullet" src="images/games_bullet.png" border="0"><?php endif ?>
										<a href="Catalog.aspx?m=BestSelling&amp;c=<?= $category ?>&amp;t=AllTime&amp;d=All">
											<?php if($mode == "BestSelling"): ?><b><?php endif ?>
												Best Selling
											<?php if($mode == "BestSelling"): ?></b><?php endif ?>
										</a>
									</li>
									<li>
										<?php if($mode == "RecentlyUpdated"): ?><img class="GamesBullet" src="images/games_bullet.png" border="0"><?php endif ?>
										<a href="Catalog.aspx?m=RecentlyUpdated&amp;c=<?= $category ?>">
											<?php if($mode == "RecentlyUpdated"): ?><b><?php endif ?>
												Recently Updated
											<?php if($mode == "RecentlyUpdated"): ?></b><?php endif ?>
										</a>
									</li>
									<li>
										<?php if($mode == "ForSale"): ?><img class="GamesBullet" src="images/games_bullet.png" border="0"><?php endif ?>
										<a href="Catalog.aspx?m=ForSale&amp;c=<?= $category ?>&amp;d=All">
											<?php if($mode == "ForSale"): ?><b><?php endif ?>
												For Sale
											<?php if($mode == "ForSale"): ?></b><?php endif ?>
										</a>
									</li>
									<li>
										<?php if($mode == "PublicDomain"): ?><img class="GamesBullet" src="images/games_bullet.png" border="0"><?php endif ?>
										<a href="Catalog.aspx?m=PublicDomain&amp;c=<?= $category ?>">
											<?php if($mode == "PublicDomain"): ?><b><?php endif ?>
												Public Domain
											<?php if($mode == "PublicDomain"): ?></b><?php endif ?>
										</a>
									</li>
								</ul>
							</div>
							<div id="Category">
								<h4>Category</h4>
								<ul>
									<li>
										<?php if($category == 2): ?><img class="GamesBullet" src="images/games_bullet.png" border="0"><?php endif ?>
										<a href="Catalog.aspx?m=<?= $mode ?>&amp;c=2&amp;t=<?= $time_period ?>&amp;d=All">T-Shirts</a>
									</li>
									<li>
										<?php if($category == 11): ?><img class="GamesBullet" src="images/games_bullet.png" border="0"><?php endif ?>
										<a href="Catalog.aspx?m=<?= $mode ?>&amp;c=11&amp;t=<?= $time_period ?>&amp;d=All">Shirts</a>
									</li>
									<li>
										<?php if($category == 12): ?><img class="GamesBullet" src="images/games_bullet.png" border="0"><?php endif ?>
										<a href="Catalog.aspx?m=<?= $mode ?>&amp;c=12&amp;t=<?= $time_period ?>&amp;d=All">Pants</a>
									</li>
									<li>
										<?php if($category == 8): ?><img class="GamesBullet" src="images/games_bullet.png" border="0"><?php endif ?>
										<a href="Catalog.aspx?m=<?= $mode ?>&amp;c=8&amp;t=<?= $time_period ?>&amp;d=All">Hats</a>
									</li>
									<li>
										<?php if($category == 13): ?><img class="GamesBullet" src="images/games_bullet.png" border="0"><?php endif ?>
										<a href="Catalog.aspx?m=<?= $mode ?>&amp;c=13&amp;t=<?= $time_period ?>&amp;d=All">Decals</a>
									</li>
									<li>
										<?php if($category == 10): ?><img class="GamesBullet" src="images/games_bullet.png" border="0"><?php endif ?>
										<a href="Catalog.aspx?m=<?= $mode ?>&amp;c=10&amp;t=<?= $time_period ?>&amp;d=All">Models</a>
									</li>
									<li>
										<?php if($category == 9): ?><img class="GamesBullet" src="images/games_bullet.png" border="0"><?php endif ?>
										<a href="Catalog.aspx?m=<?= $mode ?>&amp;c=9&amp;t=<?= $time_period ?>&amp;d=All">Places</a>
									</li>
								</ul>
							</div>
							<div id="ctl00_cphRoblox_rbxCatalog_Timespan">
								<h4>Time</h4>
								<ul>
									<li>
										<?php if($time_period == "PastHour"): ?><img class="GamesBullet" src="images/games_bullet.png" border="0"><?php endif ?>
										<a href="Catalog.aspx?m=<?= $mode ?>&amp;c=<?= $category ?>&amp;t=PastHour&amp;d=All">
											<?php if($time_period == "PastHour"): ?><b><?php endif ?>
												Past Hour
											<?php if($time_period == "PastHour"): ?></b><?php endif ?>
										</a>
									</li>
									<li>
										<?php if($time_period == "PastDay"): ?><img class="GamesBullet" src="images/games_bullet.png" border="0"><?php endif ?>
										<a href="Catalog.aspx?m=<?= $mode ?>&amp;c=<?= $category ?>&amp;t=PastDay&amp;d=All">
											<?php if($time_period == "PastDay"): ?><b><?php endif ?>
												Past Day
											<?php if($time_period == "PastDay"): ?></b><?php endif ?>
										</a>
									</li>
									<li>
										<?php if($time_period == "PastWeek"): ?><img class="GamesBullet" src="images/games_bullet.png" border="0"><?php endif ?>
										<a href="Catalog.aspx?m=<?= $mode ?>&amp;c=<?= $category ?>&amp;t=PastWeek&amp;d=All">
											<?php if($time_period == "PastWeek"): ?><b><?php endif ?>
												Past Week
											<?php if($time_period == "PastWeek"): ?></b><?php endif ?>
										</a>
									</li>
									<li>
										<?php if($time_period == "PastMonth"): ?><img class="GamesBullet" src="images/games_bullet.png" border="0"><?php endif ?>	
										<a href="Catalog.aspx?m=<?= $mode ?>&amp;c=<?= $category ?>&amp;t=PastMonth&amp;d=All">
											<?php if($time_period == "PastMonth"): ?><b><?php endif ?>
												Past Month
											<?php if($time_period == "PastMonth"): ?></b><?php endif ?></a>
									</li>
									<li>
										<?php if($time_period == "AllTime"): ?><img class="GamesBullet" src="images/games_bullet.png" border="0"><?php endif ?>
										<a href="Catalog.aspx?m=<?= $mode ?>&amp;c=<?= $category ?>&amp;t=AllTime&amp;d=All">
											<?php if($time_period == "AllTime"): ?><b><?php endif ?>
												All-time
											<?php if($time_period == "AllTime"): ?></b><?php endif ?></a>
										</a>
									</li>
								</ul>
							</div>
						</div>
						<div class="Assets">
							<span id="ctl00_cphRoblox_rbxCatalog_AssetsDisplaySetLabel" class="AssetsDisplaySet">
								<?php 
									$splitwords = preg_split("/(?<=[a-z])(?=[A-Z])/", $mode);
									foreach($splitwords as $word) {
										echo $word." ";
									}
								?>
								<?= $category_label."s" ?>
							</span>
							<?php if(count($all_assets) > 20): ?>
							<div id="ctl00_cphRoblox_rbxCatalog_HeaderPagerPanel" class="HeaderPager">
								<?php if($page > 1): ?>
								<a id="ctl00_cphRoblox_rbxCatalog_HeaderPagerHyperLink_Back" href="Catalog.aspx?m=<?= $mode ?>&amp;c=<?= $category ?>&amp;t=<?= $time_period ?>&amp;q=<?= $query ?>&amp;p=<?= $page-1 ?>"><span class="NavigationIndicators">&lt;&lt;</span> Back</a>
								<?php endif ?>
								<span id="ctl00_cphRoblox_rbxCatalog_HeaderPagerLabel">Page <?= $page ?> of <?= $page_count ?></span>
								<?php if($page != $page_count): ?>
								<a id="ctl00_cphRoblox_rbxCatalog_HeaderPagerHyperLink_Next" href="Catalog.aspx?m=<?= $mode ?>&amp;c=<?= $category ?>&amp;t=<?= $time_period ?>&amp;q=<?= $query ?>&amp;p=<?= $page+1 ?>">Next <span class="NavigationIndicators">&gt;&gt;</span></a>
								<?php endif ?>
							</div>
							<?php endif?>
							<table id="ctl00_cphRoblox_rbxCatalog_AssetsDataList" cellspacing="0" align="Center" border="0" width="735">
								<tbody>
										<?php 
											if(count($assets) != 0) {
												$asset_iterator_count = 0;
												$asset_count = 0;
												$asset_totalcount = count($assets);
												foreach($assets as $asset) {
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
													if($asset instanceof BuyableAsset) {
														$asset_salecount = $asset->sales;
													} else {
														$asset_salecount = 0;
													}
													

													switch($asset->status) {
														case 0:
															$asset_thumburl = "/thumbs/?id=$asset_id&type=120";
															break;
														case 1:
															$asset_thumburl = "/images/review-pending.png";
															break;
														case -1:
															$asset_thumburl = "/images/unavail-120x120.png";
															break;
													}

													echo <<<EOT
													<td valign="top">
														<div class="Asset">
															<div class="AssetThumbnail">
																<a id="ctl00_cphRoblox_rbxCatalog_AssetsDataList_ctl00_AssetThumbnailHyperLink" title="$asset_name" href="/Item.aspx?ID=$asset_id" style="display:inline-block;cursor:pointer;"><img src="$asset_thumburl" border="0" alt="$asset_name" blankurl="http://t6.roblox.com:80/blank-120x120.gif"></a>
															</div>
															<div class="AssetDetails">
																<div class="AssetName"><a href="Item.aspx?ID=$asset_id">$asset_name</a></div>
																<div class="AssetLastUpdate"><span class="Label">Updated:</span> <span class="Detail">$asset_lastupdate</span></div>
																<div class="AssetCreator"><span class="Label">Creator:</span> <span class="Detail"><a href="/User.aspx?ID=$creator_id">$creator_name</a></span></div>
																<div class="AssetsSold"><span class="Label">Number Sold:</span> <span class="Detail">$asset_salecount</span></div>
																<div class="AssetFavorites"><span class="Label">Favorited:</span> <span class="Detail">$asset_favcount times</span></div>
													EOT;
													if($asset instanceof BuyableAsset) {
														if($asset->onsale && $asset->tux != 0) {
															$price = $asset->tux;
															echo "<div class=\"AssetPrice\"><span class=\"PriceInTickets\">Tux: $price</span></div>";
														}
													}
													echo <<<EOT
															</div>
														</div>
													</td>
													EOT;
													$asset_iterator_count = ($asset_iterator_count+1)%5;
													$asset_count += 1;

													if($asset_totalcount == $asset_count && $asset_iterator_count < 5 && $asset_iterator_count != 0) {
														$iterations = 5 - $asset_iterator_count;
														for ($i = 0; $i < $iterations; $i++) {
															echo "<td valign=\"top\"><div class=\"Asset\"></div></td>";
														}
													}
													if($asset_iterator_count == 5) {
														echo "</tr>";
													}
												}
											} else {
												echo <<<EOT
													<tr>
														<td>
															<div>Looks like theres no catalog items to gawk at...</div>
														</td>
													</tr>
												EOT;
											}
											
										?>
								</tbody>
							</table>
							<?php if(count($all_assets) > 20): ?>
							<div id="ctl00_cphRoblox_rbxCatalog_FooterPagerPanel" class="HeaderPager">
								<?php if($page > 1): ?>
								<a id="ctl00_cphRoblox_rbxCatalog_FooterPagerHyperLink_Back" href="Catalog.aspx?m=<?= $mode ?>&amp;c=<?= $category ?>&amp;t=<?= $time_period ?>&amp;q=<?= $query ?>&amp;p=<?= $page-1 ?>"><span class="NavigationIndicators">&lt;&lt;</span> Back</a>
								<?php endif ?>
								<span id="ctl00_cphRoblox_rbxCatalog_FooterPagerLabel">Page <?= $page ?> of <?= $page_count ?></span>
								<?php if($page != $page_count): ?>
								<a id="ctl00_cphRoblox_rbxCatalog_FooterPagerHyperLink_Next" href="Catalog.aspx?m=<?= $mode ?>&amp;c=<?= $category ?>&amp;t=<?= $time_period ?>&amp;q=<?= $query ?>&amp;p=<?= $page+1 ?>">Next <span class="NavigationIndicators">&gt;&gt;</span></a>
								<?php endif ?>
							</div>
							<?php endif?>
						</div>
						<div style="clear: both;"></div>
					</div>
					<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_footer.php"; ?>
				</div>
				<script type="text/javascript">
				//<![CDATA[
				/*Sys.Application.add_init(function() {
				$create(Roblox.Thumbs.AssetImage, {"assetVersionID":8122623,"fileExtension":"Png","spinnerUrl":"/Thumbs/ProgressIndicator.gif"}, null, null, $get("ctl00_cphRoblox_rbxCatalog_AssetsDataList_ctl00_AssetThumbnailHyperLink"));
				});
				Sys.Application.add_init(function() {
				$create(Roblox.Thumbs.AssetImage, {"assetVersionID":8122611,"fileExtension":"Png","spinnerUrl":"/Thumbs/ProgressIndicator.gif"}, null, null, $get("ctl00_cphRoblox_rbxCatalog_AssetsDataList_ctl01_AssetThumbnailHyperLink"));
				});
				Sys.Application.add_init(function() {
				$create(Roblox.Thumbs.AssetImage, {"assetVersionID":8122589,"fileExtension":"Png","spinnerUrl":"/Thumbs/ProgressIndicator.gif"}, null, null, $get("ctl00_cphRoblox_rbxCatalog_AssetsDataList_ctl02_AssetThumbnailHyperLink"));
				});
				Sys.Application.add_init(function() {
				$create(Roblox.Thumbs.AssetImage, {"assetVersionID":8122568,"fileExtension":"Png","spinnerUrl":"/Thumbs/ProgressIndicator.gif"}, null, null, $get("ctl00_cphRoblox_rbxCatalog_AssetsDataList_ctl03_AssetThumbnailHyperLink"));
				});
				Sys.Application.add_init(function() {
				$create(Roblox.Thumbs.AssetImage, {"assetVersionID":8122562,"fileExtension":"Png","spinnerUrl":"/Thumbs/ProgressIndicator.gif"}, null, null, $get("ctl00_cphRoblox_rbxCatalog_AssetsDataList_ctl04_AssetThumbnailHyperLink"));
				});
				Sys.Application.add_init(function() {
				$create(Roblox.Thumbs.AssetImage, {"assetVersionID":8122386,"fileExtension":"Png","spinnerUrl":"/Thumbs/ProgressIndicator.gif"}, null, null, $get("ctl00_cphRoblox_rbxCatalog_AssetsDataList_ctl05_AssetThumbnailHyperLink"));
				});
				Sys.Application.add_init(function() {
				$create(Roblox.Thumbs.AssetImage, {"assetVersionID":8122177,"fileExtension":"Png","spinnerUrl":"/Thumbs/ProgressIndicator.gif"}, null, null, $get("ctl00_cphRoblox_rbxCatalog_AssetsDataList_ctl07_AssetThumbnailHyperLink"));
				});
				Sys.Application.add_init(function() {
				$create(Roblox.Thumbs.AssetImage, {"assetVersionID":8122052,"fileExtension":"Png","spinnerUrl":"/Thumbs/ProgressIndicator.gif"}, null, null, $get("ctl00_cphRoblox_rbxCatalog_AssetsDataList_ctl08_AssetThumbnailHyperLink"));
				});*/
				//]]>
				</script>
			</div>
			<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/formvars.php"; ?>
		</form>
	</body>
</html>
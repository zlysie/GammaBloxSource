<?php
    require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	UserUtils::LockOutUserIfNotLoggedIn();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" id="gamma-lambda-cam">
	<head>
		<title>GAMMA Download</title>
		<link rel="stylesheet" type="text/css" href="/CSS/AllCSS.css">
		<link rel="Shortcut Icon" type="image/ico" href="/favicon.ico">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="author" content="GAMMA Corporation">
		<meta name="description" content="GAMMA is SAFE for kids! GAMMA is a FREE casual virtual world with fully constructible/desctructible environments and immersive physics. Build, battle, chat, or just hang out.">
		<meta name="keywords" content="game, video game, building game, contstruction game, online game, LEGO game, LEGO, MMO, MMORPG, virtual world, avatar chat">
		<meta name="robots" content="none">

		<script src="Service.asmx/js" type="text/javascript"></script>
	</head>
	<body>
		<form name="aspnetForm" method="post" action="Default.aspx" id="aspnetForm">
			<div id="Container">
				<?php include $_SERVER['DOCUMENT_ROOT'].'/core/rbx_header_nonav.php'; ?>
				<div id="Body">
					<p id="ctl00_cphRoblox_SystemRequirements1_OS" align="center" style="color: red">Currently, GAMMA is only available on PCs running the Windows® operating system</p>
					<div style="margin-top: 12px; margin-bottom: 12px">
						<div id="AlreadyInstalled" style="display: none">
							<p>
								GAMMA is already installed on this computer. 
								If you want to try installing it again then follow the instructions below. 
								Otherwise, you can just <a href="javascript:goBack()">continue</a>.
							</p>
						</div>
						<img id="ctl00_cphRoblox_Image3" class="Bullet" src="/images/BuildIcon.png" border="0">
						<div id="InstallStep1" style="padding-left: 60px">
							<h2>Download GAMMA</h2>
							<pdownload roblox<="" h2="">
								<p>
									<input type="submit" name="ctl00$cphRoblox$ButtonDownload" value="Install GAMMA" id="ctl00_cphRoblox_ButtonDownload" class="BigButton" onclick="Install(); return false;">
									&nbsp;(Total download about 15Mb)
								</p>
							</pdownload>
						</div>
						<img id="ctl00_cphRoblox_Image4" class="Bullet" src="/images/FriendsIcon.png" border="0">
						<div id="InstallStep2" style="padding-left: 60px">
							<h2>
								Run the Installer    A window will open asking what you want to do with a file called Setup.exe.
								<p></p>
								<p>Click 'Run'. You might see a confirmation message, asking if you're sure you want to run this software. Click 'Run' again.</p>
								<p>
									<img id="ctl00_cphRoblox_Image1" src="/images/Install/DownloadPrompt.PNG" border="0">
								</p>
							</h2>
						</div>
						<img id="ctl00_cphRoblox_Image5" class="Bullet" src="/images/BattleIcon.png" border="0">
						<div id="InstallStep3" style="padding-left: 60px">
							<h2>Follow the Setup Wizard</h2>
							<p>When the download has finished, the GAMMA Setup Wizard will appear and guide you through the rest of the installation.</p>
							<p>
								<img id="ctl00_cphRoblox_Image2" src="/images/Install/Wizard.PNG" border="0">
							</p>
						</div>
					</div>
					<script>
						function Install() {
							window.open("/download/GammaSetup_1.0.3.783.22.exe");
						}
						function isInstalled() {
							try { 
								var robloxClient = new ActiveXObject("Roblox.App"); 
								return true;
							} catch (e) { 
								return false;
							} 
						}
						function goBack() {
							window.history.back();
						}
						function checkInstall() {
							if (isInstalled()) { 
								// If we didn't fail, then we can move on
								document.getElementById("ctl00_cphRoblox_ButtonDownload").disabled = true;
								Roblox.Install.Service.InstallSucceeded();
								goBack();
							} else { 
								// Try again later 
								window.setTimeout("checkInstall()", 2000); 
							} 
						} 
					</script>
					<script type="text/javascript">
						if (isInstalled()) {
							AlreadyInstalled.style.display="block";
						} else {
							window.setTimeout("checkInstall()", 1000);
						}
					</script>
				</div>
				<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/rbx_footer.php"; ?>
			</div>
			<script type="text/javascript"></script>
			<?php include_once $_SERVER["DOCUMENT_ROOT"]."/core/formvars.php"; ?>
		</form>
	</body>
</html>

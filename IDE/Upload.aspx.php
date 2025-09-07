<?php
	$domain = $_SERVER['SERVER_NAME'];

	require_once $_SERVER["DOCUMENT_ROOT"]."/core/assetutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
	UserUtils::LockOutUserIfNotLoggedIn();

	$user = UserUtils::GetLoggedInUser();

	$places = array();

	$stmt_assetinfo = $con->prepare('SELECT * FROM `assets` WHERE `asset_creator` = ? AND `asset_type` = 9 ORDER BY `asset_lastupdate` DESC');
	$stmt_assetinfo->bind_param('i', $user->id);
	$stmt_assetinfo->execute();
	$result = $stmt_assetinfo->get_result();
	$num_rows = $result->num_rows;

	if($num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$asset = Place::FromID($row['asset_id']);
			array_push($places, $asset);
		}
	} else {
		$places = array();
	}

	$ie6 = strpos($_SERVER["HTTP_USER_AGENT"], 'MSIE 6.0') ? true : false;
	$ie7 = strpos($_SERVER["HTTP_USER_AGENT"], 'MSIE 7.0') ? true : false;
	$ie8 = strpos($_SERVER["HTTP_USER_AGENT"], 'MSIE 8.0') ? true : false;

	if(!($ie6 || $ie7 || $ie8)) {
		die("You are not authorised to view this page.");
	}
?>
<?php if(!isset($_POST['ChoosePublishContentModificationButton']) && !isset($_POST['ChoosePublishContentCreateButton']) && !isset($_POST['PublishNewContentButton']) && !isset($_POST['__EVENTTARGET'])): ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" >
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Upload</title>
		<link href="/CSS/RobloxOld.css" rel="stylesheet" type="text/css" />
	</head>
	<body bgcolor="buttonface" scroll="no">
		<form name="PublishContent" method="post" action="/IDE/Upload.aspx" id="PublishContent">
		<pre style="margin:0">Array ( )</pre>
			<input id="DialogResult" type="hidden" />		    
			<table height="100%" cellpadding="12" width="100%">
				<tr valign="top">
					<td colspan="2">
					  <p>You are about to publish this Place to GAMMA.  Please choose how you would like to save your work:</p>
					</td>
				</tr>
				<tr>
					<td width="120" valign="top"><input type="submit" name="ChoosePublishContentCreateButton" value="Create" id="ChoosePublishContentCreateButton" class="OKCancelButton" style="width:100%;" /></td>
					<td valign="top"><strong>Create a new Place on GAMMA.</strong><br />Choose this to create a new Place!</td>
				</tr>
				<tr>
					<td width="120" valign="top"><input type="submit" name="ChoosePublishContentModificationButton" value="Update" id="ChoosePublishContentModificationButton" class="OKCancelButton" style="width:100%;" /></td>
					<td valign="top"><strong>Update an existing Place on GAMMA.</strong><br />Choose this to make changes to a Place you have previously created.  You will have the opportunity to select which Place you wish to update.</td>
				</tr>
				<tr>
					<td width="120" valign="top"><input class="OKCancelButton" onclick="DialogResult.value='2'; window.close(); return false" style="WIDTH: 100%" type="button" value="Cancel" /></td>
					<td valign="top"><strong>Keep playing and exit later.</strong></t>
				</tr>
			</table>
		</form>
	</body>
</html>
<?php endif ?>
<?php if(isset($_POST['ChoosePublishContentModificationButton']) && !isset($_POST['__EVENTTARGET'])): ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" >
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Upload</title>
		<link href="/CSS/RobloxOld.css" rel="stylesheet" type="text/css" />
	</head>
	<body bgcolor="buttonface" scroll="no">
		<form name="PublishContent" method="post" action="Upload.aspx" id="PublishContent">
			<div>
				<input type="hidden" name="__EVENTTARGET" id="__EVENTTARGET" value="" />
				<input type="hidden" name="__EVENTARGUMENT" id="__EVENTARGUMENT" value="" />
			</div>
			<script type="text/javascript">
				//<![CDATA[
				var theForm = document.forms['PublishContent'];
				if (!theForm) {
					theForm = document.PublishContent;
				}
				function __doPostBack(eventTarget, eventArgument) {
					if (!theForm.onsubmit || (theForm.onsubmit() != false)) {
						theForm.__EVENTTARGET.value = eventTarget;
						theForm.__EVENTARGUMENT.value = eventArgument;
						theForm.submit();
					}
				}
				//]]>
			</script>
			<input id="DialogResult" type="hidden" />		    
			<p>Select the Place you wish to update:</p>
			<div id="CreationsPanel" class="CreationsPanel" style="overflow:auto;">
				<div class="Creations">
					<?php
						foreach($places as $place) {
							$place_id = $place->id;
							$place_name = $place->name;
							echo <<<EOT
								<div class="Creation">
									<span class="Selector"><a id="CreationsRepeater_ctl01_CreationSelector" href="javascript:__doPostBack('CreationsRepeater\$ctl01\$CreationSelector','$place_id')">Select</a></span>
									<span class="Name">$place_name</span>
								</div>
							EOT;
						}
					?>
				</div>
			</div>
		</form>
	</body>
</html>
<?php endif ?>
<?php if(isset($_POST['ChoosePublishContentCreateButton']) && !isset($_POST['__EVENTTARGET'])): ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Upload</title>
		<link href="/CSS/RobloxOld.css" rel="stylesheet" type="text/css" />
	</head>
	<body bgcolor="buttonface" scroll="no">
		<form name="PublishContent" method="post" action="Upload.aspx" id="PublishContent">
			<input id="DialogResult" type="hidden" />
			<table border="0" cellpadding="1" cellspacing="1" height="100%" width="100%">
				<tr>
					<td height="100%">
						<table border="0" height="100%" width="100%">
							<tr>
								<td colspan="2"></td>
							</tr>
							<tr>
								<td align="right" style="height: 26px" valign="top">Name:</td>
								<td style="height: 26px" width="100%">
									<input name="NameTextBox" type="text" value="" id="NameTextBox" style="width:100%;" />
								</td>
							</tr>
							<tr>
								<td align="right" height="100%" valign="top">Description:</td>
								<td height="100%">
									<textarea name="DescriptionTextBox" rows="2" cols="20" id="DescriptionTextBox" style="height:100%;width:100%;"></textarea>
								</td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td width="100%">
									<input id="IsFriendsOnlyCheckBox" type="checkbox" name="IsFriendsOnlyCheckBox" /><label for="IsFriendsOnlyCheckBox">Publish for the public domain.</label>
								</td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td width="100%">
									<input id="IsCopylockedCheckBox" type="checkbox" name="IsCopylockedCheckBox" /><label for="IsCopylockedCheckBox">Publish as copylocked.</label>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td align="right">
						<table border="0" cellpadding="0" cellspacing="5">
							<tr>
								<td><input type="submit" name="PublishNewContentButton" value="Publish" id="PublishNewContentButton" class="OKCancelButton" /></td>
								<td><input onclick="window.close(); return false" type="button" value="Cancel" class="OKCancelButton" /></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</form>
	</body>
</html>
<?php endif ?>
<?php if(isset($_POST['PublishNewContentButton'])): 
	$blockedchars = array('𒐫', '‮', '﷽', '𒈙', '⸻ ', '꧅');
	$name = trim($_POST['NameTextBox']);
	$name = str_replace($blockedchars, '', $name);
	$name = substr($name, 0, 64);
	$name = urlencode($name);

	$desc = trim($_POST['DescriptionTextBox']);
	$desc = str_replace($blockedchars, '', $desc);
	$desc = substr($desc, 0, 512);
	$desc = urlencode($desc);

	$ispublic = isset($_POST['IsFriendsOnlyCheckBox']) ? "0" : "1";
	$iscopylocked = isset($_POST['IsCopylockedCheckBox']) ? "1" : "0";
	$error = false;
	$errormsg .= "";
	if(strlen(trim($name)) < 3) {
		$error = true;
		$errormsg = "Place name was too short!<br>";
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" >
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Upload</title>
		<link href="/CSS/RobloxOld.css" rel="stylesheet" type="text/css" />
	</head>
	<body bgcolor="buttonface" scroll="no">
		<form name="PublishContent" method="post" action="Upload.aspx" id="PublishContent">
			<input id="DialogResult" type="hidden" />		    
			<?php if(!$error): ?>
			<div id="Uploading" style="DISPLAY: block; FONT-WEIGHT: bold; COLOR: royalblue">Uploading. Please wait...</div>
			<div id="Confirmation" style="display: none;">
				<table height="100%" width="100%">
					<tr valign="top" height="100%">
						<td>The upload has completed!</td>
					</tr>
					<tr>
						<td align="right">
							<table cellspacing="5" cellpadding="0" border="0">
								<tr>
									<td><input class="OKCancelButton" onclick="window.close(); return false" type="button" value="Close" /></td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>
			<div id="Failure" style="display: none;">
				<p>The upload has failed.</p>
			</div>
			<script type="text/javascript">
				function uploadData() {
					try {
						window.external.Write().Upload("http://<?= $domain ?>/Data/Upload.ashx?assetid=0&type=Place&name=<?= $name ?>&description=<?= $desc ?>&ispublic=<?= $ispublic ?>&iscopylocked=<?= $iscopylocked ?>&creds_id=<?= $user->id; ?>&creds_pw=<?= urlencode($user->GetPassword()); ?>");
						document.getElementById("Uploading").style.display='none';
						document.getElementById("Confirmation").style.display='block';
					} catch (ex) {
						window.alert(ex.message);
						try {
							window.external.Write().Upload("http://<?= $domain ?>/Data/Upload.ashx?assetid=0&type=Place&name=<?= $name ?>&description=<?= $desc ?>&ispublic=<?= $ispublic ?>&iscopylocked=<?= $iscopylocked ?>&creds_id=<?= $user->id; ?>&creds_pw=<?= urlencode($user->GetPassword()); ?>");
							document.getElementById("Uploading").style.display='none';
							document.getElementById("Confirmation").style.display='block';
						} catch (ex2) {
							document.getElementById("Uploading").style.display='none';
							document.getElementById("Failure").style.display='block';
							window.alert(ex2.message);
						}
					}
				}
				window.setTimeout("uploadData()", 1000);
			</script>
			<?php endif ?>
			<?php if(!$error): ?>
			<div id="Failure">
				<p><?= $error ?></p>
			</div>
			<?php endif ?>
		</form>
	</body>
</html>
<?php endif ?>
<?php if(isset($_POST['__EVENTTARGET']) && isset($_POST['__EVENTARGUMENT'])): 

	$creation = $_POST['__EVENTTARGET'] == 'CreationsRepeater$ctl01$CreationSelector';
	$placeid = intval($_POST['__EVENTARGUMENT']);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" >
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Upload</title>
		<link href="/CSS/RobloxOld.css" rel="stylesheet" type="text/css" />
	</head>
	<body bgcolor="buttonface" scroll="no">
		<form name="PublishContent" method="post" action="Upload.aspx" id="PublishContent">
			<input id="DialogResult" type="hidden" />		    
			<div id="Uploading" style="DISPLAY: block; FONT-WEIGHT: bold; COLOR: royalblue">Uploading. Please wait...</div>
			<div id="Confirmation" style="display: none;">
				<table height="100%" width="100%">
					<tr valign="top" height="100%">
						<td>The upload has completed!</td>
					</tr>
					<tr>
						<td align="right">
							<table cellspacing="5" cellpadding="0" border="0">
								<tr>
									<td><input class="OKCancelButton" onclick="window.close(); return false" type="button" value="Close" /></td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>
			<div id="Failure" style="display: none;">
				<p>The upload has failed.</p>
			</div>
			<script type="text/javascript">
				function uploadData() {
					try {
						window.external.Write().Upload("http://<?= $domain ?>/Data/Upload.ashx?assetid=<?= $placeid ?>&type=Place&name=&description=&ispublic=False&creds_id=<?= $user->id; ?>&creds_pw=<?= urlencode($user->GetPassword()); ?>");
						document.getElementById("Uploading").style.display='none';
						document.getElementById("Confirmation").style.display='block';
					} catch (ex) {
						window.alert(ex.message);
						try {
							window.external.Write().Upload("http://<?= $domain ?>/Data/Upload.ashx?assetid=<?= $placeid ?>&type=Place&name=&description=&ispublic=False&creds_id=<?= $user->id; ?>&creds_pw=<?= urlencode($user->GetPassword()); ?>");
							document.getElementById("Uploading").style.display='none';
							document.getElementById("Confirmation").style.display='block';
						} catch (ex2) {
							document.getElementById("Uploading").style.display='none';
							document.getElementById("Failure").style.display='block';
							window.alert(ex2.message);
						}
					}
				}
				window.setTimeout("uploadData()", 1000);
			</script>
		</form>
	</body>
</html>
<?php endif ?>
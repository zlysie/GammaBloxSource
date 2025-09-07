<?php 
	session_start();
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/assetutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	
	$user = UserUtils::GetLoggedInUser();

	if(!$user->IsAdmin()) {
		http_response_code(401);
		die("Not authorised");
	}

	$users = UserUtils::GetAllUsers();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<style>
			@font-face {
				font-family: 'Comic Sans MS Web';
				src: url('/CSS/COMIC.ttf');
			}

			body {
				font:normal 10pt/normal 'Comic Sans MS','Comic Sans MS Web',Verdana,sans-serif;
				background-color: lightgray;
				margin:15px;
				width:100%;
			}
			
			#ModBox {
				border:1px solid black;
				background-color: rgb(230, 230, 230);
				padding:15px;
				width:100%;
			}

			table, td, th {
				border-collapse: collapse;
				border: 1px solid black;
			}

			#ModBox h3 {
				margin-top:0;
			}
		</style>
		<script src="/js/jquery.js" type="text/javascript"></script>
	</head>
	<body>
		<div id="ModBox">
			<h3>Create Invite Key</h3>
			<div>
				<input type="text" id="keyarea">
				<input type="button" value="Generate" onclick="generateKey()">
			</div>
		</div>
		<br>
		<div id="ModBox">
			<h3>Give Hat</h3>
			<div>
				<label>Asset ID: </label><input type="text" id="giver_assetid"><br>
				<label>User ID: </label><input type="text" id="giver_userid"><br>
				<input type="button" value="Give Hat" onclick="giveHatToUser()">
			</div>
		</div>
		<br>
		<div id="ModBox">
			<h3>Give Tux</h3>
			<div>
				<label>Amount of Tux: </label><input type="text" id="tuxxer_tux"><br>
				<label>User ID: </label><input type="text" id="tuxxer_userid"><br>
				<input type="button" value="Give Hat" onclick="giveTuxToUser()">
			</div>
		</div>
		<br>
		<div id="ModBox">
			<h3>Users</h3>
			<table>
				<tr>
					<th style="width: 200px;">Username</th>
				</tr>
				<?php
					if(count($users) != 0) {
						foreach($users as $user) {
							$username = $user->name;
							$userid = $user->id;
							echo <<<EOT
								<tr>
									<td><a href="/User.aspx?ID=$userid" target="_blank">$username</a></td>
									<td>
										<a href="/User.aspx?ID=$userid" target="_blank">View</a>&nbsp;&nbsp;
										<a href="javascript:copyUserID($userid)">Copy ID</a>&nbsp;&nbsp;
										<a href="">Edit</a>
									</td>
								</tr>
							EOT;
						}
					}
				?>
				
			</table>
		</div>

		<script>

			function giveTuxToUser() {
				var tux = $("#tuxxer_tux").val();
				var userid = $("#tuxxer_userid").val();

				$.post( "/Admin/postadmin", { type: "givetux", tux: tux, userid: userid }).done(function( data ) {
					alert(data);
				});
			}


			function giveHatToUser() {
				var assetid = $("#giver_assetid").val();
				var userid = $("#giver_userid").val();

				$.post( "/Admin/postadmin", { type: "giverhat", assetid: assetid, userid: userid }).done(function( data ) {
					alert(data);
				});
			}

			function copyUserID(id) {
				navigator.clipboard.writeText(id);
				alert("Copied ID of " + id)
			}

			function generateKey() {
				$.post( "/Admin/postadmin", { type: "generatekey" }).done(function( data ) {
					$("#keyarea").val("https://gamma.lambda.cam/Login/New.aspx?thatkey="+data);
					$("#keyarea").select();
				});
			}

			$("#keyarea").click(function() {
				$(this).select();
			});
		</script>
	</body>
</html>
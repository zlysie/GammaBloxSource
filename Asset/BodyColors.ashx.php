<?php
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	require $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";

	$user_id = intval($_GET['userID']);

	if($user_id <= 0) {
		$head = 24;
		$torso = 23;
		$left_arm = 24;
		$right_arm = 24;
		$left_leg = 119;
		$right_leg = 119;
	} else {
		$user_exists = User::Exists($user_id);
		if(!$user_exists) {
			die();
		} else {
			$stmt_getbodycolors = $con->prepare('SELECT * FROM `bodycolors` WHERE `userid` = ?');
			$stmt_getbodycolors->bind_param('i', $user_id);
			$stmt_getbodycolors->execute();

			$res = $stmt_getbodycolors->get_result();
			if($res->num_rows == 0) {
				$stmt_insertbodycolor = $con->prepare('INSERT INTO `bodycolors`(`userid`) VALUES (?)');
				$stmt_insertbodycolor->bind_param('i', $user_id);
				$stmt_insertbodycolor->execute();

				$head = 24;
				$torso = 23;
				$left_arm = 24;
				$right_arm = 24;
				$left_leg = 119;
				$right_leg = 119;
			} else {
				$row = $res->fetch_assoc();

				$head = $row['head'];
				$torso = $row['torso'];
				$left_arm = $row['leftarm'];
				$right_arm = $row['rightarm'];
				$left_leg = $row['leftleg'];
				$right_leg = $row['rightleg'];
			}
		}
	}

	$domain = $_SERVER['SERVER_NAME'];
	header("Content-Type: text/plain");
?>
<roblox xmlns:xmime="http://www.w3.org/2005/05/xmlmime" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="http://www.roblox.com/roblox.xsd" version="4">
	<External>null</External>
	<External>nil</External>
	<Item class="BodyColors" referent="RBX0">
		<Properties>
			<int name="HeadColor"><?= $head ?></int>
			<int name="LeftArmColor"><?= $left_arm ?></int>
			<int name="LeftLegColor"><?= $right_leg ?></int>
			<string name="Name">Body Colors</string>
			<int name="RightArmColor"><?= $right_arm ?></int>
			<int name="RightLegColor"><?= $left_leg ?></int>
			<int name="TorsoColor"><?= $torso ?></int>
			<bool name="archivable">true</bool>
		</Properties>
	</Item>
</roblox>
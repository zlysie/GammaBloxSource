
<?php
	session_start();
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/assetutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	$user = UserUtils::GetLoggedInUser();
	
	header("content-type: application/json"); 
	if($user == null) {
		echo "{\"Error\": \"User is not logged in!\"}";
		die();
	}

	$page = intval($_GET['page']);
	$rows = intval($_GET['msgsPerPage']); //20
	$type = $_GET['type'];
	$timeget = intval($_GET['_']);

	$msgcount = 0;
	if($type == "GetInbox") {
		$stmt_assetinfo = $con->prepare('SELECT * FROM `messages` WHERE `message_recipient` = ? ORDER BY `message_timesent` DESC;');
		$stmt_assetinfo->bind_param('i', $user->id);
		$stmt_assetinfo->execute();
		
		$result = $stmt_assetinfo->get_result();
		$msgcount = $result->num_rows;

		$stmt_assetinfo = $con->prepare('SELECT * FROM `messages` WHERE `message_recipient` = ? ORDER BY `message_timesent` DESC LIMIT ?, ?');
		$calc_page = ($page-1)*$rows;
		$stmt_assetinfo->bind_param('iii', $user->id, $calc_page, $rows);
		$stmt_assetinfo->execute();
		
		$result = $stmt_assetinfo->get_result();
	} else if($type == "DeleteButton") {
		$selected = explode(",", $_GET['selected']);
		foreach($selected as $select) {
			$stmt_updatearchivedstate = $con->prepare('DELETE FROM `messages` WHERE `message_id` = ? AND `message_recipient` = ?');
			$stmt_updatearchivedstate->bind_param('ii', $select, $user->id);
			$stmt_updatearchivedstate->execute();
		}
		$stmt_assetinfo = $con->prepare('SELECT * FROM `messages` WHERE `message_recipient` = ? ORDER BY `message_timesent` DESC');
		$stmt_assetinfo->bind_param('i', $user->id);
		$stmt_assetinfo->execute();
		$result = $stmt_assetinfo->get_result();
		$msgcount = $result->num_rows;

		$stmt_assetinfo = $con->prepare('SELECT * FROM `messages` WHERE `message_recipient` = ? ORDER BY `message_timesent` DESC LIMIT ?, ?');
		$calc_page = ($page-1)*$rows;
		$stmt_assetinfo->bind_param('iii', $user->id, $calc_page, $rows);
		$stmt_assetinfo->execute();
		
		$result = $stmt_assetinfo->get_result();
	}
?>

{
	"Messages": [
		<?php
			if(isset($result)) {
				$total_msg ="";
				while($message = $result->fetch_assoc()) {
					$author = User::FromID($message['message_sender']);
					$author_name = $author->name;
					$author_id = $author->id;
					$subject = $message['message_subject'];
					$subject = str_replace( "\"", "\\\"", $subject);
					$msg_id = $message['message_id'];
					$msg_isread = $message['message_read'] == 0 ? "true" : "false";
					$date = new DateTime(datetime: $message['message_timesent']);
					$msg_date = $date->format('m/d/Y h:i:s A');
					$total_msg .= <<<EOT
						[
							"$author_name",
							"$subject",
							"$msg_date",
							"$msg_id",
							"$author_id",
							"$msg_isread"
						],
					EOT;
				}
				echo substr($total_msg, 0, strlen($total_msg)-1);
			}
		?>
	],
	"numMessages": <?= $msgcount ?>
}

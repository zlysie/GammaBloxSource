<?php
	session_start();
	
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";

	UserUtils::LockOutUserIfNotLoggedIn();
	die(header("Location: /User.aspx"));
?>

<?php
	session_start();
	
	require_once $_SERVER["DOCUMENT_ROOT"]."/core/userutils.php";
	
	UserUtils::LockOutUserIfNotLoggedIn();

	die(header("Location: /Games.aspx"));
?>
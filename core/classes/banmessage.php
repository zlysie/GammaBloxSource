<?php

class BanMessage {
	public User $user;
	public User $issuer;
	public string $reason;
	public string $message;
	public DateTime $enddate;
	public DateTime $issueddate;
	public bool $terminated = false;

	function __construct($rowdata) {
		include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
		
		$this->user = User::FromID($rowdata['suspended_user']);
		$this->issuer = User::FromID($rowdata['suspended_issuer']);
		$this->reason = $rowdata['suspended_reason'];
		$this->message = $rowdata['suspended_message'];
		if($rowdata['suspended_enddate'] != null) {
			$this->enddate = DateTime::createFromFormat("Y-m-d H:i:s", $rowdata['suspended_enddate']);
		} else {
			$this->terminated = true;
		}
		$this->issueddate = DateTime::createFromFormat("Y-m-d H:i:s", $rowdata['suspended_date']);
	}

	function GetBanDuration(): string {
		$interval = $this->enddate->diff($this->issueddate);
		list($days, $hours, $minutes, $seconds) = explode(",", $interval->format('%d,%H,%I,%S'));
		$days = intval($days);
		$hours = intval($hours);
		$minutes = intval($minutes);
		$seconds = intval($seconds);

		if($days != 0) {
			if($days == 1) {
				return "$days Day";
			}
			return "$days Days";
		} else if($hours != 0) {
			if($hours == 1) {
				return "$hours Hour";
			}
			return "$hours Hours";
		} else if($minutes != 0) {
			if($minutes == 1) {
				return "$minutes Minute";
			}
			return "$minutes Minutes";
		} else if($seconds != 0) {
			if($seconds == 1) {
				return "$seconds Seconds";
			}
			return "$seconds Seconds";
		}
	}
}

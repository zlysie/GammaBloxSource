<?php
	class BuyableAsset extends Asset {
		/**
		 * Is the item on sale?
		 * @var bool
		 */
		public bool $onsale;
		/**
		 * How much does it cost in Tux?
		 * @var int
		 */
		public int $tux;
		/**
		 * How much does it cost in Robux?
		 * @var int
		 */
		public int $bux;
		/**
		 * Number of sales
		 * @var int
		 */
		public int $sales;

		public static function FromID($asset_id): BuyableAsset|null {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			$stmt_getuser = $con->prepare("SELECT * FROM `assets` WHERE `asset_id` = ?");
			$stmt_getuser->bind_param('i', $asset_id);
			$stmt_getuser->execute();
			$result = $stmt_getuser->get_result();
	
			if($result->num_rows == 1) {
				return new self($result->fetch_assoc());
			} else {
				return null;
			}
		}

		function __construct($rowdata) {
			parent::__construct($rowdata);
			$this->onsale = boolval($rowdata['asset_onsale']);
			$this->tux = intval($rowdata['asset_tixcost']);
			$this->bux = intval($rowdata['asset_robuxcost']);
			$this->sales = intval($rowdata['asset_salecount']);
		}
	}
?>
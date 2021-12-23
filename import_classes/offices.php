<?php

	class offices extends base
	{
		public $table_name = 'offices';
        public $SQL_truncate = "TRUNCATE TABLE `shop_offices`";
        public $SQL_insert = "INSERT INTO `shop_offices`(`id`, `caption`, `country`, `region`, `city`, `address`, `phone`, `email`, `coordinates`, `description`, `users`, `timetable`, `pay_system_id`, `pay_system_parameters`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

	}

?>
<?php

	class offices_storages extends base
	{
		public $table_name = 'offices_storages';
        public $SQL_insert = "INSERT INTO `shop_offices_storages_map`( `storage_id`,  `additional_time`,`office_id`) VALUES (?,?,?)";
	}

?>
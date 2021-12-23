<?php 

	$dictionary = [];
	
	$dictionary["notifications"] = 
	[
		"params" => [ 'email_subject', 'email_body', 'sms_body', 'email_on', 'sms_on', 'name' ],
	];
	
	$dictionary["sms"] = 
	[
		"params" => [ 'parameter_values', 'handler'],
	];
	
	$dictionary["geo"] = 
	[
		"params" => [ 'id', 'count', 'level', 'value', 'parent', 'order' ],
	];
	
	$dictionary["offices"] =
	[
		"params" => [
                'id', 'caption', 'country', 'region', 'city', 'address', 'phone', 'email', 'coordinates', 'description', 'users', 'timetable', 'pay_system_id', 'pay_system_parameters',
                 "geo" => [
                     "params" => [ "geo_id" ],
                     "parent_params" => [ "id" ]
                 ],
                "storages" =>[
                    "params" => [ "storage_id", "additional_time",
                        "groups" =>[
                            "params" => [ "group_id", "markups" =>
                                                        [
                                                            "params" => [ "min_point", "max_point", "markup" ],
                                                            "parent_params" => [ "storage_id", "additional_time", "id", "group_id" ]
                                                        ],
                            ],
                            "parent_params" => [ "storage_id", "additional_time",  "id"],
                        ],
                    ],
                    "parent_params" => [ "id" ],
                ],
            ],
	];



?>
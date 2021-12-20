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
                'id', 'caption', 'country', 'region', 'city', 'address', 'phone', 'email', 'coordinates', 'description', 'users', 'timetable', 'pay_system_id', 'pay_system_parameters', 'arr_geo_id',
                'geo' => []
            ],

	];


    $default_update = true;

    $dictionary = [
        "offices" => [
            "update" => $default_update,
            "params" => [
                "geo" => [
                    "params" => [],
                    "sub_params" => ["id"],
                ]
            ]
        ],
        "sms" => []
    ];

?>
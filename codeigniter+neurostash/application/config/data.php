<?php
$data['home'] = array(
	//'incoming'	=>	array(
			//when all post data belongs to an entity
			//'post'		=>	array('data'),
			
			//when post data will go to multiple entities
			'post'		=>	array(
					//'required'	=>	false,
					'data'		=>	array(
						//'action'	=>	'update',
						'action'	=>	'post',
						'source'	=>	'data-form',
						'mapping'	=>	false,
						/* Place the fields to be posted.
						 * If form fields have different names
						 * than fields in your class, use an
						 * associative array to map
						 * from_field => class_field. Otherwise,
						 * just place form fields in the array.
						 */
						'fields'	=>	array('date', 'pas', 'pad', 'pulse'),

						//data that needs to be queried before post and will
						//added to the list of fields
						'fillFirst'	=>	array(
							'user_id'	=>	array(
									'email'	=>	'session'
									//'email'	=>	'uri'
									//'email'	=>	'some_parameter'
								)
							)
						//'search_by'	=>	'session'
						//'search_by'	=>	'uri'
						//'search_by'	=>	'parameters'
						//'query'	=>	array(
						//'where'/condition	=>	pulse > 10
						//)
						//specify the query parameter(s) to use like
						//pulse > 10
						)
				),
			//would be nice to be able to get just few fields
			//this also leads to restricting access to some fields
			'get'		=>	array(
				'user'		=>	array(
					//'source'	=>	'data-form',
					'fields'	=>	array('name', 'lastname', 'age', 'sex'),
					'search_by'	=>	'session'
					//'search_by'	=>	'uri'
					//'search_by'	=>	'parameters'
					//specify the parameter(s) to use
					),
				'data'		=>	array(
					'fields'	=>	array('date', 'pas', 'pad', 'pulse'),
					'search_by'	=>	'session'
					)
				)
	//	),
	//seems to be only for forms that post to the same page
	//may not need it at all. Deprecated for now
	/*'outgoing'	=>	array(
			'get'		=>	null,
			'post'		=>	null,
			'put'		=>	null,
			'delete'	=>	null
		)*/
	);

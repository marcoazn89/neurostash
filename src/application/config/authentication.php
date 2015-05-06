<?php
/**
 * This is a configuration file for authentication used in the app.
 * To add authentication to a page just create a new index in the
 * $auth array with the name of the page that needs to be authenticated.
 * Then, assign an array to it with the type of users that can
 * access such page.
 *
 * Example:
 * $auth['home'] = array('user', 'admin') 
 */

$auth['test'] = array(
	'user' => array('email', 'password')
	);

$auth['home'] = array(
	'user' => array('email', 'password')
	);

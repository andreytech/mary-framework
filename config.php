<?php

$config['default_controller'] = 'home';

$config['dbhost'] = 'localhost';
$config['dbname'] = 'db';
$config['dbuser'] = 'user';
$config['dbpass'] = 'pass';

$config['base_url'] = 'http://'.$_SERVER["HTTP_HOST"].'/';

$config['max_upload_file_size_mb'] = 50;

/*
|--------------------------------------------------------------------------
| SEF variables
|--------------------------------------------------------------------------
*/

$config['is_use_sef_urls'] = false;
$config['urls_db_table'] = 'routes';

/*
|--------------------------------------------------------------------------
| Admins Emails
|--------------------------------------------------------------------------
*/

$config['admin_email'] = 'yourwebwizard@gmail.com';

/*
|--------------------------------------------------------------------------
| Session Variables
|--------------------------------------------------------------------------
*/
$config['sess_db_table']		= 'sessions';
$config['sess_cookie_name']		= 'sess_id';
$config['sess_check_expiration']	= false;
$config['sess_expiration']		= 9999999;
$config['sess_match_ip']		= false;
$config['sess_match_useragent']	= false;
$config['sess_time_to_update'] 	= 300;
$params['encryption_key'] = 'REPLACE_WITH_RANDOM_TEXT';

/*
|--------------------------------------------------------------------------
| Cookie Related Variables
|--------------------------------------------------------------------------
*/
$config['cookie_prefix']	= "";
$config['cookie_domain']	= ""; //Set to .your-domain.com for site-wide cookies
$config['cookie_path']		= "/";

/*
|--------------------------------------------------------------------------
| Reverse Proxy IPs
|--------------------------------------------------------------------------
| If your server is behind a reverse proxy, you must whitelist the proxy IP
| addresses from which CMS should trust the HTTP_X_FORWARDED_FOR
| header in order to properly identify the visitor's IP address.
| Comma-delimited, e.g. '10.0.1.200,10.0.1.201'
|
*/
$config['proxy_ips'] = '';




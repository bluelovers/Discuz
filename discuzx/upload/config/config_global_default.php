<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: config_global_default.php 23921 2011-08-16 09:18:28Z cnteacher $
 *      English by Valery Votintsev at sources.ru
 */

$_config = array();

// ----------------------------  CONFIG DB  ----------------------------- //
// Database server settings

/**
 * Set the primary database server to support multiple sets of server settings, when set to multiple servers, distributed strategy is to use a server based on
 * @example
 * $_config['db']['1']['dbhost'] = 'localhost'; // Server Address
 * $_config['db']['1']['dbuser'] = 'root'; // User
 * $_config['db']['1']['dbpw'] = 'root';// Password
 * $_config['db']['1']['dbcharset'] = 'gbk';// Character Set
 * $_config['db']['1']['pconnect'] = '0';// persistent connection
 * $_config['db']['1']['dbname'] = 'x1';// Database name
 * $_config['db']['1']['tablepre'] = 'pre_';// Table prefix
 *
 * $_config['db']['2']['dbhost'] = 'localhost';
 * ...
 *
 */
$_config['db'][1]['dbhost']  	= 'localhost';	// DB Server address
$_config['db'][1]['dbuser']  	= 'root';		// DB User Name
$_config['db'][1]['dbpw'] 	 	= 'root';		// DB User Password
$_config['db'][1]['dbcharset'] 	= 'utf8';		// DB Charset
$_config['db'][1]['pconnect'] 	= 0;			// Enable DB persistent connection
$_config['db'][1]['dbname']  	= 'ultrax';		// DB Name
$_config['db'][1]['tablepre'] 	= 'pre_';		// DB Table Prefix

/**
 * Database from the server settings (slave, read-only), support for multiple sets of server settings, when set to multiple servers, the system using each random
 * @example
 * $_config['db']['slave']['1']['dbhost'] = 'localhost';
 * $_config['db']['slave']['1']['dbuser'] = 'root';
 * $_config['db']['slave']['1']['dbpw'] = 'root';
 * $_config['db']['slave']['1']['dbcharset'] = 'gbk';
 * $_config['db']['slave']['1']['pconnect'] = '0';
 * $_config['db']['slave']['1']['dbname'] = 'x1';
 * $_config['db']['slave']['1']['tablepre'] = 'pre_';
 *
 * $_config['db']['slave']['2']['dbhost'] = 'localhost';
 * ...
 * 
 */
$_config['db']['slave'] = array();

/**
 * Distributed database deployment policy setting
 *
 * @example Will common_member Deployed to the second server, common_session Deployed in the third server, Is set to
 * $_config['db']['map']['common_member'] = 2;
 * $_config['db']['map']['common_session'] = 3;
 *
 * Server for the table is not explicitly stated, it will be deployed in the first server, the default
 *
 */
$_config['db']['map'] = array();

/**
 * Database of public settings, such settings are usually deployed on the server for each
 */
$_config['db']['common'] = array();

/**
 *  Disable the data from the database tables, table names separated by commas between
 *
 * @example common_session, common_member These two tables to read and write only from the master server, do not use from the server
 * $_config['db']['common']['slave_except_table'] = 'common_session, common_member';
 *
 */
$_config['db']['common']['slave_except_table'] = '';

/**
* memory server optimization settings
 * The following settings need to be PHP extension support component, which memcache priority over other settings,
 * can not be enabled when the memcache automatically when you open the other two optimization models* when the memcache automatically when you open the other two optimized mode)
*/

//Memory variable prefix, change, to avoid reference to the same server process disorder
$_config['memory']['prefix'] = 'discuz_';

$_config['memory']['eaccelerator'] = 1;				// Start the support for eaccelerator
$_config['memory']['apc'] = 1;							// Start support for apc
$_config['memory']['xcache'] = 1;					// Start the support for xcache
$_config['memory']['memcache']['server'] = '';		// memcache server address
$_config['memory']['memcache']['port'] = 11211;		// memcache server port
$_config['memory']['memcache']['pconnect'] = 1;		// memcache persistent connection
$_config['memory']['memcache']['timeout'] = 1;		// memcache server connection timeout

// Server-related settings
$_config['server']['id']		= 1;	// Server ID, when  more webservers used this ID to identify the current server

// Download attachments
//
// local file reading mode; Mode 2 means the most to save memory, but does not support multi-threaded download
// 1=fread, 2=readfile, 3=fpassthru, 4=fpassthru+multiple
$_config['download']['readmod'] = 2;

// Enable X-Sendfile feature(required server support) 0=disable, 1=nginx, 2=lighttpd, 3=apache
$_config['download']['xsendfile']['type'] = 0;

// Enable nginx X-sendfile, the forum attachment directory path to the virtual map, use the "/" at the end
$_config['download']['xsendfile']['dir'] = '/down/';

//  CONFIG CACHE
$_config['cache']['type'] 			= 'sql';	// Cache type: file = file cache, sql = database cache

// Page output settings
$_config['output']['charset'] 		= 'utf-8';	// Page character set
$_config['output']['forceheader']	= 1;		// Force the output in defined character set, used to avoid page content garbled
$_config['output']['gzip'] 			= 0;		// Use Gzip compression for output
$_config['output']['tplrefresh'] 	= 1;		// Automatically refresh templates: 0 = off, 1 = On
$_config['output']['language'] 		= 'en';		// Page language en/zh_cn/zh_tw
$_config['output']['staticurl'] 	= 'static/';	// Path to the site static files, use "/" at the end
$_config['output']['ajaxvalidate']	= 0;		// Strictly verify the authenticity for Ajax pages: 0 = off, 1 = On
$_config['output']['iecompatible']	= 0;		// IE compatibility mode

// COOKIE settings
$_config['cookie']['cookiepre'] 		= 'uchome_'; 	// COOKIE prefix
$_config['cookie']['cookiedomain'] 		= ''; 		// COOKIE domain
$_config['cookie']['cookiepath'] 		= '/'; 		// COOKIE path

// Site Security Settings
$_config['security']['authkey']			= 'asdfasfas';	// Site encryption key
$_config['security']['urlxssdefend']	= true;		// Use own URL XSS defense
$_config['security']['attackevasive']	= 0;		// CC Attack Defense 1 | 2 | 4

$_config['security']['querysafe']['status']	= 1;		// Enable the SQL security detection, prevent the SQL injection attacks automatically
$_config['security']['querysafe']['dfunction']	= array('load_file','hex','substring','if','ord','char');
$_config['security']['querysafe']['daction']	= array('intooutfile','intodumpfile','unionselect','(select');
$_config['security']['querysafe']['dnote']	= array('/*','*/','#','--','"');
$_config['security']['querysafe']['dlikehex']	= 1;
$_config['security']['querysafe']['afullnote']	= 0;

$_config['admincp']['founder']		= '1';	// Site Founder: site management background with the highest authority, each site can be set to one or more founders
											// You can use the user uid ore user name. Separate multiple users with a comma;
$_config['admincp']['forcesecques']	= 0;	// Force managers to set the security question for access to the system settings: 0 = no, 1 = yes [secure]
$_config['admincp']['checkip']		= 1;	// Back office operations are verified administrator IP, 1 = yes [secure], 0 = no. Only the administrator can not log in from time to set 0.
$_config['admincp']['runquery']		= 1;	// Allow to run SQL statements in the background: 1 = yes, 0 = no [secure]
$_config['admincp']['dbimport']		= 1;	// Allow the data recovery in the background: 1 = yes, 0 = no [secure]

/**
 * Remote call function module system
 */

// Remote call: master switch 0 = off, 1 = On
$_config['remote']['on'] = 0;

// remote call: the program directory name. For security reasons, you can change the directory name, change is completed, the actual directory manually modify the program
$_config['remote']['dir'] = 'remote';

// remote call: Communication key. for the client and the server communication encryption. length of not less than 32
//          default value is $ _config ['security'] ['authkey'] of md5, you can also manually specify the$_config['remote']['appkey'] = md5($_config['security']['authkey']);
$_config['remote']['appkey'] = md5($_config['security']['authkey']);

// Remote call: Open external cron task. within the system no longer running cron, cron task activated by an external program
$_config['remote']['cron'] = 0;

?>
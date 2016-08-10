<?php
// PiVMUGc - Version 2.1.1
// Load 3rd party dependencies
require('vendor/autoload.php');
// Kickstart the framework
$f3= Base::instance();
$f3->set('DEBUG',1);
if ((float)PCRE_VERSION<7.9) {
	trigger_error('PCRE version is out of date');
}
// load configuration
$f3->config('config.ini');
$f3->set('AUTOLOAD','app/');
$f3->set('db', new DB\SQL('sqlite:'.$f3->DB_PATH));
// base index landing page
$f3->route('GET /','Index->DefaultDisplay');
// checkin
$f3->route('GET /checkin','Checkin->DefaultDisplay');
$f3->route('POST /checkin','Checkin->ProcessPOST');
// register
$f3->route('GET /register','Register->DefaultDisplay');
$f3->route('POST /register','Register->ProcessPOST');
// reprint
$f3->route('GET /reprint','Reprint->DefaultDisplay');
$f3->route('POST /reprint','Reprint->ProcessPOST');
// admin
$f3->route('GET /admin','Admin->DefaultDisplay');
$f3->route('POST /admin/database/import','Admin->ImportDatabase');
$f3->route('POST /admin/database/export','Admin->ExportDatabase');
$f3->route('POST /admin/random/name','Admin->RandomName');
$f3->route('POST /admin/database/optimize','Admin->OptimizeDatabase');
$f3->route('POST /admin/database/truncate','Admin->TruncateDatabase');
$f3->route('POST /admin/shutdown','Admin->Shutdown');
$f3->route('POST /admin/print/test','Admin->PrintTestLabel');
// set error pages
$f3->set('ONERROR',function($f3){
	if ($f3->get('ERROR.code') == '404') {
		echo \Template::instance()->render('layout-error-404.htm');
	} else {
		echo \Template::instance()->render('layout-error.htm');
	}
	// clear just in case for any stale messages
	$f3->clear('SESSION.message_type');
	$f3->clear('SESSION.message');
});
// run this shit!
$f3->run();

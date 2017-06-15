<?php

class Admin {

  private $title = 'PiVMUGc Administration';
  private $content = 'admin.htm';
  private $layout = 'layout-default.htm';

  function DefaultDisplay($f3) {

    $mapper = new DB\SQL\Mapper($f3->get('db'), 'admins');
		$auth = new Auth($mapper, array('id'=>'username','pw'=>'password'));

		// callback function because password is stored as an md5 hash.
		function chkauth($pw) {
		    return md5($pw);
		}

    if($auth->basic('chkauth')) {
        $f3->set('title',$this->title);

        if ($f3->get('SESSION.message_type') == 'success') {
            $f3->set('jsnoty','jsnoty-success.htm');
            $f3->set('jsnotymsg',$f3->get('SESSION.message'));
        }
        elseif ($f3->get('SESSION.message_type') == 'failure') {
            $f3->set('jsnoty','jsnoty-error.htm');
            $f3->set('jsnotymsg',$f3->get('SESSION.message'));
        }

        $f3->clear('SESSION.message_type');
        $f3->clear('SESSION.message');

        $f3->set('content',$this->content);
        echo \Template::instance()->render($this->layout);
      }

  }

  function ImportDatabase($f3) {
    $ExcelImporter = new ExcelUtility();
    $result = $ExcelImporter->ImportDatabase();

    if ($result) {
      $f3->set('SESSION.message_type','success');
      $f3->set('SESSION.message','Successful data import!');
    } else {
      $f3->set('SESSION.message_type','failure');
      $f3->set('SESSION.message','Failure to import! Please verify your file that was uploaded.');
    }

    $f3->reroute('/admin');
  }

  function RandomName($f3) {
      // get the checked in user information
      $result = $f3->get('db')->exec('SELECT * FROM guests WHERE timestamp NOT NULL ORDER BY Random()  LIMIT 1')[0];
      $f3->set('SESSION.message_type','success');
      $f3->set('SESSION.message',$result['firstname'].' '.$result['lastname'].' from: '.$result['company']);
      $f3->reroute('/admin');
  }

  function ExportDatabase($f3) {
    $ExcelExporter = new ExcelUtility();
    $ExcelExporter->ExportDatabase();
  }

  function TruncateDatabase($f3) {
    $result = $f3->get('db')->exec('DELETE FROM guests;');

    if ($result > 0) {
      $f3->set('SESSION.message_type','success');
      $f3->set('SESSION.message','The guests table has been truncated!');
    } else {
      $f3->set('SESSION.message_type','failure');
      $f3->set('SESSION.message','Failure to truncate! Big time unknown error! Cleveland, we have a problem!');
    }

    $f3->reroute('/admin');
  }

  function Shutdown($f3) {
    echo \Template::instance()->render('shutdown.htm');
    $cmdout = shell_exec('sudo shutdown -h now');
  }

  function PrintTestLabel($f3) {
    $lblPrinter = new LabelPrinter();
    $lblPrinter->PrintNameTagLabel("John Doe","Corporate America");

    $f3->reroute('/admin');
  }

}

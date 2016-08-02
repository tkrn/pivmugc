<?php

class Reprint {

  // define page specifics
  private $title = 'Reprint Name Tag - PiVMUGc';
  private $sqlcmd = 'SELECT * FROM view_all_checked_in';
  private $content = 'reprint.htm';
  private $layout = 'layout-default.htm';

  function DefaultDisplay($f3) {
    $f3->set('title',$this->title);
		$f3->set('result',$f3->get('db')->exec($this->sqlcmd));

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

  function ProcessPOST($f3) {
    // get the id field from post
    $id = (int)$f3->get('POST.id');

    // get the checked in user information
    $result = $f3->get('db')->exec(
      'SELECT firstname,lastname,company FROM guests WHERE id = :id', $id);

    // if successful update do more work, 1 indicates one row was modified
    if (count($result) == 1) {

      // select the only 'record'
      $result = $result[0];

      // craft the fullname
      $fullname = "{$result['firstname']} {$result['lastname']}";

      // create the name tag label
      $lblPrinter = new LabelPrinter();
      $lblPrinter->PrintNameTagLabel($fullname,$result['company']);

      // set successful jsnoty
      $f3->set('SESSION.message_type','success');
      $f3->set('SESSION.message','Successful Registration! Thank you!');
    } else {
      // set failure jsnoty
      $f3->set('SESSION.message_type','failure');
      $f3->set('SESSION.message','Failed to reprint the name tag! `
        Please contact a volunteer!');
    }

    $f3->reroute('/reprint');

  }

}

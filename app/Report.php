<?php

class Report {

  // define page specifics
  private $title = 'Quick Report - PiVMUGc';
  private $guestssqlcmd = 'SELECT * FROM view_all_guests';
  private $statssqlcmd = 'SELECT * FROM view_stats';
  private $content = 'report.htm';
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
      $f3->set('guests',$f3->get('db')->exec($this->guestssqlcmd));
      $f3->set('stats',$f3->get('db')->exec($this->statssqlcmd));
      $f3->set('content',$this->content);
      echo \Template::instance()->render($this->layout);
      }

  }

}

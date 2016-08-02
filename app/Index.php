<?php

class Index {

  // define page specifics
  private $title = 'Welcome to the VMUG Meetup! - PiVMUGc';
  private $content = 'index.htm';
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

}

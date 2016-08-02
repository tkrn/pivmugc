<?php

class Register {

  // define page specifics
  private $title = 'Walk-on Registration - PiVMUGc';
  private $content = 'register.htm';
  private $layout = 'layout-register.htm';

  function DefaultDisplay($f3) {
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

  function ProcessPOST($f3) {

    // save these variables for later use if succesful insert
    $firstname = $f3->get('POST.firstname');
    $lastname = $f3->get('POST.lastname');
    $company = $f3->get('POST.company');

    // insert the row for the walkon individual
    $result = $f3->get('db')->exec(
      'INSERT INTO guests ("lastname","firstname","company","company_type","email","pre_registration","timestamp")
        VALUES (:lastname,:firstname,:company,:company_type,:email,:pre_registration,:timestamp)',
        array(':lastname'=>$lastname,
        ':firstname'=>$firstname,
        ':company'=>$company,
        ':company_type'=>$f3->get('POST.company_type'),
        ':email'=>$f3->get('POST.email'),
        ':pre_registration'=>'0',
        ':timestamp'=> 25569 + time() / 86400
      )
    );

    // if successful update do more work, 1 indicates one row was added
    if ($result == 1) {

      // craft the fullname
      $fullname = "{$firstname} {$lastname}";

      // create the name tag label
      $lblPrinter = new LabelPrinter();
      $lblPrinter->PrintNameTagLabel($fullname,$company);

      $f3->set('SESSION.message_type','success');
      $f3->set('SESSION.message','Successful Registration! Thank you!');
    } else {
      $f3->set('SESSION.message_type','failure');
      $f3->set('SESSION.message','Failed to Register! Please contact a volunteer!');
    }

    $f3->reroute('/');
  }

}

?>

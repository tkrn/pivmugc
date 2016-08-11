<?php

// define ExcelFunctions class
class ExcelUtility {
  
  function ExportDatabase() {

    // get base instance
    $f3 = Base::instance();

    // query the database
    $view_export_table = $f3->get('db')->exec('SELECT * FROM view_export_table');

    // create phpexcel object
    $objPHPExcel = new PHPExcel();

    // create 1 sheet
    $objPHPExcel->createSheet(1);

    // set sheet 0 active, rename sheet
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->setTitle('Guests');

    // populate header
    $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'LAST NAME');
    $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'FIRST NAME');
    $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'COMPANY');
    $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'COMPANY TYPE');
    $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'EMAIL ADDRESS');
    $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'PRE-REGISTRATION');
    $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'TIMESTAMP (UTC)');

    // set boldness
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);

    // iterate through database dumping to excel
    foreach ($view_export_table as $row) {
      $rownum = $objPHPExcel->getActiveSheet()->getHighestRow() + 1;
      $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rownum, $row['lastname']);
      $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rownum, $row['firstname']);
      $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rownum, $row['company']);
      $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rownum, $row['company_type']);
      $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rownum, $row['email']);
      $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rownum, $row['pre_registration']);
      $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rownum, $row['timestamp']);

      // set datetime format
      $objPHPExcel->getActiveSheet()->getStyle('G'.$rownum)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_DATETIME);
    }

    // autosize columns
    foreach(range('A','G') as $cid) {
      $objPHPExcel->getActiveSheet()->getColumnDimension($cid)->setAutoSize(true);
    }

    // set the filename
    $fileName = date("Ymd_His") . "_pivmugc_export.xlsx";

    // redirect output to a client web browser (Excel5)
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename='.$fileName);
    header('Cache-Control: max-age=0');

    // save the file in phpexcel object
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');

    // ensure we are disconnected from any worksheets
    $objPHPExcel->disconnectWorksheets();

  }

  function ImportDatabase() {

    // get base instance
    $f3 = Base::instance();
    $web = Web::instance();

    $f3->set('UPLOADS',$f3->RAM_DISK . '/'); // set as RAM_DISK for best performance

    // set allowed mime file types
    $allowedTypes = ['text/csv',
      'application/vnd.ms-excel',
      'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ];

    $overwrite = true; // overwrite an existing file though it will never happen

    $files = $web->receive(function($file,$formFieldName) {

        if($file['size'] > (5 * 1024 * 1024)) // if bigger than 5 MB
            return false; // this file is not valid, return false will skip moving it

        $allowedFile = false; // do not trust mime type until proven trust worthy

        for ($i=0; $i < count($allowedTypes); $i++) {
           if ($file['type'] == $allowedTypes[$i]) {
             $allowedFile = true; // trusted type found!
           }
        }

        // return true if it the file meets requirements
        ($allowedFile ? true : false);

      },
      $overwrite,
      function($fileBaseName, $formFieldName) {
        $ext = pathinfo($fileBaseName, PATHINFO_EXTENSION);

        if ($ext) {
          // custom file name (md5) + ext
          return (md5(spl_object_hash(new DateTime('NOW'))) . '.' . $ext);
        } else {
          // custom file name (md5)
          return md5(spl_object_hash(new DateTime('NOW')));
        }

    	}
    );

    foreach ($files as $file => $array) {

      // create PHPExcel readonly object
      $objReader = PHPExcel_IOFactory::createReaderForFile($file);
      $objReader->setReadDataOnly(true);

      // load file
      $objPHPExcel = $objReader->load($file);

      // set active sheet, 0 = first sheet
      $objPHPExcel->setActiveSheetIndex(0);

      // field range to grab
      $focusRange = 'A1:C' . $objPHPExcel->getActiveSheet()->getHighestRow();

      // get sheet data, sheet 0 are attendees
      // see documentation http://bit.ly/1qKWJnF
      $sheetData = $objPHPExcel->getActiveSheet()->rangeToArray($focusRange,null,false,false,true);

      // do a large insert, performance is better on large insert than individual on rpi
      // see http://bit.ly/1rmtYNx and http://bit.ly/1DrbGj5
      if (count($sheetData) > 0) {

        // begin the bulk insert
        $f3->get('db')->exec('BEGIN;');

        // go through each row in excel spreadsheet
        foreach ($sheetData as $row) {

          // grab data from cells of the current row
          $firstname = $row['A'];
          $lastname = $row['B'];
          $company = $row['C'];

          $result = $f3->get('db')->exec(
            'INSERT INTO guests ("lastname","firstname","company","pre_registration")
              VALUES (:lastname,:firstname,:company,:pre_registration)',
              array(':lastname'=>$lastname,
              ':firstname'=>$firstname,
              ':company'=>$company,
              ':pre_registration'=>'1'
              )
          );

        }

        // commit the bulk insert
        $f3->get('db')->exec('COMMIT;');
      }

      // ensure we are disconnected from any worksheets
      $objPHPExcel->disconnectWorksheets();

      return true;
    }
  }
}

?>

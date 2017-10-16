<?php

// define LabelPrinter class
class LabelPrinter {

  function PrintNameTagLabel($name, $company) {

    // get base instance
    $f3 = Base::instance();

      // create pdf obj with 2.25" x 4" dimensions
    $pdf = new FPDF('L','pt',array(162,288));

		$pdf->AddPage(); // add a page to the pdf obj
		$pdf->SetFont('Arial','B',22); // name font style
		$pdf->Cell(0,22,$name,0,1,'C'); // name
		$pdf->Cell(0,10,'',0,1,'C'); // blank line
		$pdf->SetFont('Arial','I',16); // company font style
		$pdf->Cell(0,16,$company,0,1,'C'); // company name
		$pdf->Cell(0,28,'',0,1,'C'); // blank line

    // set logo at bottom right
		$pdf->Image('ui/images/' . $f3->LABEL_LOGO, $pdf->GetX() + 175, $pdf->GetY(), 65);

    // specify pdf location, ideally a tmpfs filesystem in ram for the RPi to
    // prevent writes to the sdcard and better performance

    $pdf_fullpath = $f3->RAM_DISK . '/' . md5(spl_object_hash($pdf)) . '.pdf';

    $pdf->Output($pdf_fullpath, 'F'); // write the pdf

    $pdf->Close(); // close the pdf

    $cmd = 'lp -o fit-to-page ' . $pdf_fullpath; // construct commmand
    $cmdout = shell_exec($cmd); // execute command

  }

}

?>

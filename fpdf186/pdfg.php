<?php
include "dbconnect.php";
include_once("fpdf.php");


class PDF2 extends TFPDF
{
    function Header()
    {
    // Logo
    $this->Image('logo/conestogalogo.png',10,10,20);
    //$this is an object
    $this->SetFont('Arial','B',13);
    // Move to the right
    $this->Cell(80);
    // Title
    $this->Cell(80,10,'Employee List',1,0,'C');
    // Line break
    $this->Ln(20);
    }
    

}
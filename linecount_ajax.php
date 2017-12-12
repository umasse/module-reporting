<?php
require_once "function.php";
require_once $_SERVER['DOCUMENT_ROOT']."/lib/tcpdf-6.2/tcpdf.php";

$pdf = new TCPDF (PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$comment = $_POST['comment'];
$fontSize = $_POST['fontSize'];
$width = $_POST['width'];

 // show length of comment
echo numLine($pdf, $comment, $fontSize, $width)
?>

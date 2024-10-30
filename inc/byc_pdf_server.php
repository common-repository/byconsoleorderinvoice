<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
//header("Content-Type: application/octet-stream");
//$file = get_site_url().'/wp-content/plugins/ByconsoleOrderInvoice/PDF_FILES/149009_invoice_files.pdf';

$wpbycuploaddir=wp_upload_dir();
global $byconsoleorderinvoicedir;
$byconsoleorderinvoicedir= $wpbycuploaddir['basedir'].'/BYC_PDF_FILES/';

$file = $byconsoleorderinvoicedir.'/'.$_GET["bycpdffile"] .".pdf";

//$file = $pdffile.".pdf";

header("Content-Disposition: attachment; filename=" . urlencode($file));   
header("Content-Type: application/octet-stream");
header("Content-Type: application/download");
header("Content-Description: File Transfer");            
header("Content-Length: " . filesize($file));
flush(); // this doesn't really matter.
$fp = fopen($file, "r");
while (!feof($fp))
{
    echo fread($fp, 65536);
    flush(); // this is essential for large downloads
} 
fclose($fp);
?>
<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$wpbycuploaddir=wp_upload_dir();
global $byconsoleorderinvoicedir;
$byconsoleorderinvoicedir= $wpbycuploaddir['basedir'].'/BYC_PDF_FILES/';

		$invoice_started_no_option = get_option('invoice_started_no_option');
		$bycorderinvoice_invoice_generation = get_option('bycorderinvoice_invoice_generation');		
		$bycorderinvoice_next_invoice_number = get_option('bycorderinvoice_next_invoice_number');
		
		$autometic_invoice_number_started_value = 0;
		
		if($bycorderinvoice_invoice_generation == 'automatic')
		{	
			if($invoice_started_no_option == '')
			{
				$invoice_started_with_numeric_value = 0;
			}
			else
			{
				if($invoice_started_no_option <= $autometic_invoice_number_started_value)
				{
					$invoice_started_with_numeric_value = $autometic_invoice_number_started_value;
				}
				else
				{
					$invoice_started_with_numeric_value = get_option('invoice_started_no_option');
				}
				
			}			
		}
		
		
		
		if($bycorderinvoice_invoice_generation == 'manual')
		{
			if($invoice_started_no_option == '')
			{
				if(!empty(get_option('bycorderinvoice_next_invoice_number')))
				{
					$invoice_started_with_numeric_value = get_option('bycorderinvoice_next_invoice_number');
				}
				else
				{
					$invoice_started_with_numeric_value = 0;
				}
			}
			
			else
			{
				$invoice_started_with_numeric_value = get_option('bycorderinvoice_next_invoice_number');
			}			
			
		}
				
		$invoice_started_no_option_addition = $invoice_started_with_numeric_value + 1;		
		
		update_option( 'invoice_started_no_option', $invoice_started_no_option_addition );		
		update_option( 'bycorderinvoice_next_invoice_number', $invoice_started_no_option_addition);
	
	
$order = new WC_Order( $_REQUEST['create_order_invoice'] );
$items = $order->get_items();
$item_for_loop_var = '';
foreach ( $items as $item ) {
	$item_for_loop_var .= '<tr>';    
	$item_for_loop_var .='<td colspan="2" style="padding:20px; width: 60%;">'.$item['name'].'</td>';
	$item_for_loop_var .='<td style="width: 10%;">'.$item['quantity'].'</td>';
	$item_for_loop_var .='<td style="width: 10%;">'.get_woocommerce_currency_symbol().' &nbsp; '.$item['subtotal'] .'</td>';
	$item_for_loop_var .='<td style="width: 10%;">'.get_woocommerce_currency_symbol().'&nbsp; '.$item['subtotal'] .'</td>';
	$item_for_loop_var .='<td style="width: 10%;">'.$item['total_tax'].'</td>';	
	$item_for_loop_var .='</tr>';   
}

$byc_product_order_date = substr($order->order_date,0,10);

$byc_product_order_date_explode = explode("-",$byc_product_order_date);

$byc_product_order_date_explode_val = $byc_product_order_date_explode[2].'-'.$byc_product_order_date_explode[1].'-'.$byc_product_order_date_explode[0];



$byconsolewooodt_delivery_type = get_post_meta( $order->id, 'byconsolewooodt_delivery_type', true );

if($byconsolewooodt_delivery_type == 'take_away')
{
	$byconsolewooodt_delivery_type_val = 'Pickup';
	$pickup_location_get_option_array_value = get_option('byconsolewooodt_pickup_location');
	$byconsolewooodt_location_index = get_post_meta( $order->id, 'byconsolewooodt_pickup_location', true );
	$byconsolewooodt_location_name = $pickup_location_get_option_array_value[$byconsolewooodt_location_index]['location'];
}

if($byconsolewooodt_delivery_type == 'levering')
{	
	$byconsolewooodt_delivery_type_val = 'Delivery';		
	$delivery_location_get_option_array_value = get_option('byconsolewooodt_delivery_location');
	$byconsolewooodt_location_index = get_post_meta( $order->id, 'byconsolewooodt_delivery_location', true );
    $byconsolewooodt_location_name = $delivery_location_get_option_array_value[$byconsolewooodt_location_index]['location'];

}



$byconsolewooodt_delivery_date = get_post_meta( $order->id, 'byconsolewooodt_delivery_date', true );
$byconsolewooodt_delivery_time = get_post_meta( $order->id, 'byconsolewooodt_delivery_time', true );

$bycorderinvoice_print_delivery_or_pickup_date_and_time = get_option('bycorderinvoice_print_delivery_or_pickup_date_and_time');

if($bycorderinvoice_print_delivery_or_pickup_date_and_time == 'yes')
{
$woodt_field = '<td style="width: 35%;">';
		
		if($byconsolewooodt_delivery_type != '')
		{			
			$woodt_field .='<b>Order type:</b> '.$byconsolewooodt_delivery_type_val.'<br /><br />';
		}		
		if($byconsolewooodt_location_name != '')
		{
			$woodt_field .='<b>'.$byconsolewooodt_delivery_type_val . ' Location:</b> '.$byconsolewooodt_location_name.'<br /><br />';
		}
		if($byconsolewooodt_delivery_date != '')
		{		
			$woodt_field .='<b>'.$byconsolewooodt_delivery_type_val . ' date:</b> '.get_post_meta( $order_id, 'byconsolewooodt_delivery_date', true ).'<br /><br />';
		}
		if($byconsolewooodt_delivery_time != '')
		{
			$woodt_field .='<b>'.$byconsolewooodt_delivery_type_val . ' time:</b> '.get_post_meta( $order_id, 'byconsolewooodt_delivery_time', true );
		}
$woodt_field .='</td>';
}

if($bycorderinvoice_print_delivery_or_pickup_date_and_time == 'no')
{
	$woodt_field = '<td style="width: 35%;"></td>';
}

$bycorderinvoice_invoice_prefix = get_option('bycorderinvoice_invoice_prefix');
$bycorderinvoice_invoice_suffix = get_option('bycorderinvoice_invoice_suffix');
$invoice_started_no_option = get_option('invoice_started_no_option');
	
	
$full_details_with_pre_suf = $bycorderinvoice_invoice_prefix.' / '.$invoice_started_no_option.' / '.$bycorderinvoice_invoice_suffix;

//$html2 = '<pre>'.$order.'</pre>'; 

$html = '<table width="100%" border="0" cellspacing="0" cellpadding="3">
	
    
	<tr>
	<td style="width: 25%;"><img src="'.get_option('bycorderinvoice_company_logo').'" alt="" /></td>
	<td style="width: 20%;"></td>
    <td style="width: 10%;"></td>
	<td style="width: 10%;"></td>
	<td style="width: 35%;font-size: 70px;color: #6d6d6d;font-weight: bold;">Invoice</td>            
	</tr>
    
    
	<tr>
	<td style="width: 25%;">'.get_option('bycorderinvoice_store_address').'<br />
    '.get_option('bycorderinvoice_store_city').' , '.get_option('bycorderinvoice_store_state').'<br />
    '.get_option('bycorderinvoice_store_country_name').' , '.get_option('bycorderinvoice_store_zipcode').'</td>
    
   
	<td style="width: 20%;"></td>
    <td style="width: 10%;"></td>
	<td style="width: 10%;"></td>
	<td style="width: 35%;">
    <table width="100%" border="0" cellspacing="0" cellpadding="4">
            	<tr><td>
                	<tr><td>Invoice No :</td></tr>
                    <tr><td align="left">'.$full_details_with_pre_suf.'</td></tr>
                </td></tr>				
                <tr>
                	<td style="width: 45%;">Order No. :</td>
                    <td align="left">'.$_REQUEST['create_order_invoice'].'</td>
                </tr>
				
				
                <tr>
                	<td style="width: 45%;">Date :</td>
                    <td align="left">'.$byc_product_order_date_explode_val.'</td>
                </tr>
				
                <tr>
                	<td style="width: 45%;">Amount :</td>
                    <td align="left">'.get_woocommerce_currency_symbol() .' &nbsp; '.$order_total = $order->get_total().'</td>
                </tr>
				
                
            </table>
	</td>

            
	</tr>
	
    <tr><td colspan="5" height="8px;">&nbsp;</td></tr>
    
    <tr>
	<td style="width: 45%;"><b>Bill To:</b><br />'.
    $order->billing_first_name.' '.$order->billing_last_name.', '.
	$order->billing_company.', '.
	$order->billing_address_1.', '.
	$order->billing_address_2.', '.
	$order->billing_city.', '.
	$order->billing_state.', '.
	$order->billing_postcode.', '.
	$order->billing_country.'</td>
    <td style="width: 10%;"></td>
	<td style="width: 10%;"></td>
	'.$woodt_field.'

            
	</tr>
    <tr>
    <td colspan="5" height="15px;">&nbsp;</td>
  	</tr>
	<tr bgcolor="#ddd">
        <td colspan="2" style="padding:20px; width: 60%;"><b>Product</b></td>
        <td style="width: 10%;"><b>Qty</b></td>
        <td style="width: 10%;"><b>Price</b></td>
        <td style="width: 10%;"><b>Line total</b></td>
        <td style="width: 10%;"><b>Tax</b></td>
	</tr>
   
   <tr>
    <td colspan="2">&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>'.$item_for_loop_var.'<tr>
    <td colspan="2">&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  
  <tr>
    <td colspan="6"><hr style="height: 3px;background-color: #ccc;" /></td>
   
  </tr>
	<tr>
		<td colspan="2"></td>
        <td colspan="4">
        	<table width="100%" border="0" cellspacing="6" cellpadding="4">
            	<tr>
                	<td>Subtotal</td>
                    <td align="right">'.get_woocommerce_currency_symbol().' &nbsp; '.$order_subtotal = $order->get_subtotal().'</td>
                </tr>
				
				<tr>
                	<td>Tax</td>
                    <td align="right">'.get_woocommerce_currency_symbol().' &nbsp; '.$order_total_tax = $order->get_total_tax().'</td>
                </tr>
				
				                
                <tr>
                	<td colspan="2"><hr style="height:2px; background-color:#000;" /></td>
                    
                </tr>
                <tr>
                	<td>Total</td>
                    <td align="right">'.get_woocommerce_currency_symbol() .' &nbsp; '.$order_total = $order->get_total().'</td>
                </tr>
            </table></td>
		
	</tr>
	
</table>';
		
		
		//echo '<Ayan..>'.$order_id.'<Paul>';
	
	/********************** PDF CODE START *********************************/
	
	

$randnumber_of_card_pdf = rand(0,999999);


// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

	//Page header
	public function Header() {
		// Logo
		//$image_file = K_PATH_IMAGES.'logo_example.jpg';
		//$this->Image($image_file, 10, 10, 15, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
		// Set font
		//$this->SetFont('helvetica', 'B', 20);
		// Title
		//$this->Cell(0, 15, 'Order No:'.$_REQUEST['create_order_invoice'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
	}

	// Page footer
	public function Footer() {
		// Position at 15 mm from bottom
		$this->SetY(-15);
		// Set font
		$this->SetFont('helvetica', 'I', 8);
		// Page number
		$this->Cell(0, 10, '', 0, false, 'C', 0, '', 0, false, 'T', 'M');
	}
}

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor(site_url());
$pdf->SetTitle('Invoice');
$pdf->SetSubject('Invoice');
//$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, 15, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(15);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

//set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

//set some language-dependent strings
//$pdf->setLanguageArray($l);

// ---------------------------------------------------------

// set font
//$pdf->SetFont('times', 'BI', 12);
$pdf->SetFont('times', 'I', 12);

// add a page
$pdf->AddPage();

// set some text to print
/*$txt = <<<EOD
TCPDF Example 003

Custom page header and footer are defined by extending the TCPDF class and overriding the Header() and Footer() methods.
EOD;*/


	
		


$pdf->writeHTML($html, true, false, true, false, '');

// print a block of text using Write()
//$pdf->Write($h=0, $txt, $link='', $fill=0, $align='C', $ln=true, $stretch=0, $firstline=false, $firstblock=false, $maxh=0);

// ---------------------------------------------------------
//ob_end_clean();
//Close and output PDF document
//$pdf->Output(__dir__.'/PDF_FILES/'.$randnumber_of_card_pdf.'_invoice_files.pdf', 'F');
//update_post_meta( $order_id, '_byconsolewooodt_created_invoice', __dir__.'/PDF_FILES/'.$randnumber_of_card_pdf.'_invoice_files.pdf');

$pdf->Output($byconsoleorderinvoicedir.$randnumber_of_card_pdf.'_invoice_files.pdf', 'F');
update_post_meta( $order_id, '_byconsolewooodt_created_invoice', $byconsoleorderinvoicedir.'/'.$randnumber_of_card_pdf.'_invoice_files.pdf');
// Close and output PDF document
// This method has several options, check the source code documentation for more information.


	
	
	/*********************** PDF CODE END ********************************/
?>	
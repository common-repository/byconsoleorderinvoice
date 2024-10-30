<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
/*
* Plugin Name: Another PDF invoices and Packing slips addon for WC
* Plugin URI: https://www.plugins.byconsole.com/ 
* Description: Create invoices and packaging slips in PDF format optionally include delivery/pickup date time, need to have ODT plugin(<a href="https://wordpress.org/plugins/byconsole-woo-order-delivery-time/" target="_blank">free</a>/<a href="https://www.plugins.byconsole.com/product/byconsole-wooodt-extended/" target="_blank">paid</a>) installed to print delivery/pickup date time 
* Version: 1.0.1
* Author: ByConsole 
* Author URI: https://www.plugins.byconsole.com 
* Text Domain: byconsoleorderinvoice
* Domain Path: /languages
* License: GPL2 
*/

include('inc/admin.php');

	$wpbycuploaddir=wp_upload_dir();
	global $byconsoleorderinvoicedir;
	$byconsoleorderinvoicedir= $wpbycuploaddir['basedir'].'/BYC_PDF_FILES/';

function ByconsoleOrderInvoice_plugin_activation() {
	global $byconsoleorderinvoicedir;
	
	//create required options and set to default value	
	if(!get_option('bycorderinvoice_invoice_date_format')){
		update_option('bycorderinvoice_invoice_date_format','m-d-Y');
	}
	
	if(!get_option('bycorderinvoice_invoice_generation')){
		update_option('bycorderinvoice_invoice_generation','automatic');
	}
	
	if(!get_option('bycorderinvoice_pdf_invoice_button_behaviour')){
		update_option('bycorderinvoice_pdf_invoice_button_behaviour','view');
	}
	
	if(!get_option('bycorderinvoice_print_delivery_or_pickup_date_and_time')){
		update_option('bycorderinvoice_print_delivery_or_pickup_date_and_time','no');
	}
	
	if(!file_exists($byconsoleorderinvoicedir)){
		wp_mkdir_p($byconsoleorderinvoicedir);
		}


}
register_activation_hook( __FILE__, 'ByconsoleOrderInvoice_plugin_activation' );


function byconsoleinvoice_image_upload_admin_scripts()
{
	
	wp_enqueue_script('media-upload');
	wp_enqueue_script('thickbox');	
	wp_register_script('my-upload',plugins_url( 'js/admin_image_upload_custom.js', __FILE__ ), array('jquery','media-upload','thickbox'));
	wp_enqueue_script('my-upload');
}

add_action('admin_print_scripts', 'byconsoleinvoice_image_upload_admin_scripts');

function byconsoleinvoice_image_upload_admin_styles() {
	wp_enqueue_style('thickbox');
}

add_action('admin_print_styles', 'byconsoleinvoice_image_upload_admin_styles');


include("pdfgenerator/config/lang/eng.php");
include("pdfgenerator/tcpdf.php");


/***************** Invoice pdf creation start ******************/

add_action( 'woocommerce_admin_order_actions_end', 'byconsoleinvoice_invoice_woocommerce_admin_order_actions_end'); 
function byconsoleinvoice_invoice_woocommerce_admin_order_actions_end($order) { 
global $byconsoleorderinvoicedir;		
global $woocommerce;
global $wpdb;

$order_data = $order->get_data(); // The Order data
$order_id = $order_data['id'];

$bycorderinvoice_pdf_invoice_button_behaviour = get_option('bycorderinvoice_pdf_invoice_button_behaviour');												

if(!empty($_REQUEST['create_order_invoice']))
{
	
	if($_REQUEST['create_order_invoice'] == $order_id)
	{		
	
		$invoice_started_no_option = get_option('invoice_started_no_option');
		$bycorderinvoice_invoice_generation = get_option('bycorderinvoice_invoice_generation');		
		$bycorderinvoice_next_invoice_number = get_option('bycorderinvoice_next_invoice_number');
		
		$autometic_invoice_number_started_value = 0;
		
		if($bycorderinvoice_invoice_generation == 'automatic')
		{	
			if($invoice_started_no_option == '')
			{
				$invoice_started_with_numeric_value = '0';
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
					$invoice_started_with_numeric_value = '0';
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
	
	
$order = new WC_Order( intval($_REQUEST['create_order_invoice']) );
$items = $order->get_items();
$item_for_loop_var = '';
foreach ( $items as $item ) {
	$item_for_loop_var .= '<tr>';    
	$item_for_loop_var .='<td colspan="2" style="padding:20px; width: 50%;">'.$item['name'].'</td>';
	$item_for_loop_var .='<td style="width: 10%;">'.$item['quantity'].'</td>';
	$item_for_loop_var .='<td style="width: 12%;">'.get_woocommerce_currency_symbol().'&nbsp;'.number_format($item['subtotal'], 2, '.', '').'</td>';
	$item_for_loop_var .='<td style="width: 12%;">'.get_woocommerce_currency_symbol().'&nbsp;'.number_format($item['subtotal'], 2, '.', '').'</td>';
	$item_for_loop_var .='<td style="width: 13%;">'.get_woocommerce_currency_symbol().'&nbsp;'.number_format($item['total_tax'], 2, '.', '').'</td>';	
	$item_for_loop_var .='</tr>';   
}

$byc_product_order_date = substr($order->get_date_created(),0,10);

$byc_product_order_date_explode = explode("-",$byc_product_order_date);

$byc_product_order_date_explode_val = $byc_product_order_date_explode[2].'-'.$byc_product_order_date_explode[1].'-'.$byc_product_order_date_explode[0];



$byconsolewooodt_delivery_type = get_post_meta( $order->get_id(), 'byconsolewooodt_delivery_type', true );

$order_unique_id= $order->get_id();

if($byconsolewooodt_delivery_type == 'take_away')
{
	$byconsolewooodt_delivery_type_val = 'Pickup';
	$pickup_location_get_option_array_value = get_option('byconsolewooodt_pickup_location');
	$byconsolewooodt_location_index = get_post_meta( $order_unique_id, 'byconsolewooodt_pickup_location', true );
	if(!empty($byconsolewooodt_location_index))
	{
		$byconsolewooodt_location_name = $pickup_location_get_option_array_value[$byconsolewooodt_location_index]['location'];
	}
	else
	{
		$byconsolewooodt_location_name = '';
	}
}

if($byconsolewooodt_delivery_type == 'levering')
{	
	$byconsolewooodt_delivery_type_val = 'Delivery';		
	$delivery_location_get_option_array_value = get_option('byconsolewooodt_delivery_location');
	$byconsolewooodt_location_index = get_post_meta( $order_unique_id, 'byconsolewooodt_delivery_location', true );
	if(!empty($byconsolewooodt_location_index))
	{
    	$byconsolewooodt_location_name = $delivery_location_get_option_array_value[$byconsolewooodt_location_index]['location'];
	}
	else
	{
		$byconsolewooodt_location_name = '';
	}
	

}



 $byconsolewooodt_delivery_date = get_post_meta( $order_unique_id, 'byconsolewooodt_delivery_date', true );
 $byconsolewooodt_delivery_time = get_post_meta( $order_unique_id, 'byconsolewooodt_delivery_time', true );

$bycorderinvoice_print_delivery_or_pickup_date_and_time = get_option('bycorderinvoice_print_delivery_or_pickup_date_and_time');

if($bycorderinvoice_print_delivery_or_pickup_date_and_time == 'yes')
{
$woodt_field = '<td style="width: 35%;">';
		
		if($byconsolewooodt_delivery_type != '')
		{			
			$woodt_field .='<b>Order Type:</b> '.$byconsolewooodt_delivery_type_val.'<br /><br />';
		}		
		if($byconsolewooodt_location_name != '')
		{
			$woodt_field .='<b>'.$byconsolewooodt_delivery_type_val . ' Location:</b> '.$byconsolewooodt_location_name.'<br /><br />';
		}
		if($byconsolewooodt_delivery_date != '')
		{		
			$woodt_field .='<b>'.$byconsolewooodt_delivery_type_val . ' Date:</b> '.get_post_meta( $order_id, 'byconsolewooodt_delivery_date', true ).'<br /><br />';
		}
		if($byconsolewooodt_delivery_time != '')
		{
			$woodt_field .='<b>'.$byconsolewooodt_delivery_type_val . ' Time:</b> '.get_post_meta( $order_id, 'byconsolewooodt_delivery_time', true );
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

$order_subtotal = $order->get_subtotal();

$order_total_tax = $order->get_total_tax();

$order_total = $order->get_total();

$order_shipping_total = $order->get_total_shipping();

if(!empty($order_shipping_total))
{
	$order_shipping_total_continer = '<tr><td>Shipping Charges</td><td align="right">'.esc_html(get_woocommerce_currency_symbol()).'&nbsp;'.number_format($order_shipping_total, 2, '.', '').'</td></tr>';
}
else
{
	$order_shipping_total_continer = '';
}

$bycorderinvoice_company_logo = get_option('bycorderinvoice_company_logo');
if(!empty($bycorderinvoice_company_logo))
{
	$byc_company_logo = '<img src="'.get_option('bycorderinvoice_company_logo').'" alt="" />';
}
else
{
	$byc_company_logo = '';
}

$store_field = '';
if(!empty(get_option('bycorderinvoice_store_address')))
{
	$store_field .= get_option('bycorderinvoice_store_address').'<br />';
}
if(!empty(get_option('bycorderinvoice_store_city')))
{
	$store_field .= get_option('bycorderinvoice_store_city').'<br />';
}

if(!empty(get_option('bycorderinvoice_store_state')))
{
	$store_field .= get_option('bycorderinvoice_store_state').',';
}

if(!empty(get_option('bycorderinvoice_store_country_name')))
{
	$store_field .= get_option('bycorderinvoice_store_country_name').',';
}

if(!empty(get_option('bycorderinvoice_store_zipcode')))
{
	$store_field .= get_option('bycorderinvoice_store_zipcode').'<br />';
}
	
	
//$html2 = '<pre>'.$order.'</pre>'; 

$html = '<table width="100%" border="0" cellspacing="0" cellpadding="3">
	
    
	<tr>
	<td style="width: 25%;">'.$byc_company_logo.'</td>
	<td style="width: 20%;"></td>
    <td style="width: 10%;"></td>
	<td style="width: 10%;"></td>
	<td style="width: 35%;font-size: 70px;color: #6d6d6d;font-weight: bold;">Invoice</td>            
	</tr>
    
    
	<tr>
	<td style="width: 25%;">'.$store_field.'</td>
    
   
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
                    <td align="left">'.intval($_REQUEST['create_order_invoice']).'</td>
                </tr>
				
				
                <tr>
                	<td style="width: 45%;">Date :</td>
                    <td align="left">'.$byc_product_order_date_explode_val.'</td>
                </tr>
				
                <tr>
                	<td style="width: 45%;">Amount :</td>
                    <td align="left">'.get_woocommerce_currency_symbol().'&nbsp;'.number_format($order_total, 2, '.', '').'</td>
                </tr>
				
                
            </table>
	</td>

            
	</tr>
	
    <tr><td colspan="5" height="8px;">&nbsp;</td></tr>
    
    <tr>
	<td style="width: 45%;"><b>Bill To:</b><br />'.
    $order->get_billing_first_name().' '.$order->get_billing_last_name().', '.
	$order->get_billing_company().', '.
	$order->get_billing_address_1().', '.
	$order->get_billing_address_2().', '.
	$order->get_billing_city().', '.
	$order->get_billing_state().', '.
	$order->get_billing_postcode().', '.
	$order->get_billing_country().'</td>
    <td style="width: 10%;"></td>
	<td style="width: 10%;"></td>
	'.$woodt_field.'

            
	</tr>
    <tr>
    <td colspan="5" height="15px;">&nbsp;</td>
  	</tr>
	<tr bgcolor="#ddd">
        <td colspan="2" style="padding:20px; width: 50%;"><b>Product</b></td>
        <td style="width: 10%;"><b>Qty</b></td>
        <td style="width: 12%;"><b>Price</b></td>
        <td style="width: 12%;"><b>Line total</b></td>
        <td style="width: 13%;"><b>Tax</b></td>
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
                    <td align="right">'.get_woocommerce_currency_symbol().'&nbsp;'.number_format($order_subtotal, 2, '.', '').'</td>
                </tr>
				
				<tr>
                	<td>Tax</td>
                    <td align="right">'.get_woocommerce_currency_symbol().'&nbsp;'.number_format($order_total_tax, 2, '.', '').'</td>
                </tr>
				'.$order_shipping_total_continer.'				                
                <tr>
                	<td colspan="2"><hr style="height:2px; background-color:#000;" /></td>
                    
                </tr>
                <tr>
                	<td>Total</td>
                    <td align="right">'.get_woocommerce_currency_symbol() .'&nbsp;'.number_format($order_total, 2, '.', '').'</td>
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
//$pdf->SetAuthor('Nicola Asuni');
$pdf->SetTitle('Invoice');
$pdf->SetSubject('Invoice');
$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

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
$pdf->SetFont('times', '', 12);

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
$pdf->Output($byconsoleorderinvoicedir.$randnumber_of_card_pdf.'_invoice_files.pdf', 'F');


update_post_meta( $order_id, '_byconsolewooodt_created_invoice', $byconsoleorderinvoicedir.$randnumber_of_card_pdf.'_invoice_files.pdf');

// Close and output PDF document
// This method has several options, check the source code documentation for more information.


	
	
	/*********************** PDF CODE END ********************************/
			
			
			$url   = wp_nonce_url( admin_url( 'edit.php?post_type=shop_order&action='.$bycorderinvoice_pdf_invoice_button_behaviour.'&view_order_invoice='.$order_id ), 'woocommerce-mark-order-status' );
			$name  = __( 'View invoice', 'woocommerce' );
			$text  = __( "View invoice", 'byconsole-delivery-slip-pdf-invoice' );
			$class = "byconsole_view_invoice";
			
			$paged_id = intval($_REQUEST['paged']);
			if($paged_id == '')
			{
				$back_url = admin_url('edit.php?post_type=shop_order');	
			}
			else
			{
				$back_url = admin_url('edit.php?post_type=shop_order&paged='.$paged_id);	
			}
			header('location:'.$back_url);
			

			//echo '<a href="' . $url . '" class="button tips bycinvoice_buttons ' . $class . '" data-tip="' . $text . '" title="' . $text . '" target="'.$target.'">' . $text . '</a>';
	}
	else
	{
		
		
		$url   = wp_nonce_url( admin_url( 'edit.php?post_type=shop_order&create_order_invoice='.$order_id.'&aaa=abc' ), 'woocommerce-mark-order-status' );
		$text  = __( "Create invoice", 'byconsole-delivery-slip-pdf-invoice' );
		$class = "byconsole_create_invoice";

		echo '<a href="' . $url . '" class="button tips bycinvoice_buttons ' . $class . '" data-tip="' . $text . '" title="' . $text . '">' . $text . '</a>';
		
	}
		
		
}else{
	
		$byconsolewooodt_created_invoice =  get_post_meta( $order_id, '_byconsolewooodt_created_invoice', true );
		
		if(!empty($byconsolewooodt_created_invoice))
		{
			$invoice_pdf_link = strrchr($byconsolewooodt_created_invoice,$byconsoleorderinvoicedir);
			
			$invoice_pdf_link_explode = explode("/",$invoice_pdf_link);
			
			$invoice_pdf_link_explode_last_val = explode(".",$invoice_pdf_link_explode[1]);
						
			
			$url   = wp_nonce_url(get_site_url().'/wp-admin/admin.php?action='.$bycorderinvoice_pdf_invoice_button_behaviour.'&bycpdffile='.$invoice_pdf_link_explode_last_val[0].'&byc_invoice_no='.$order_id.'&pdffor=invoiceslip', 'woocommerce-mark-order-status' );
			$name  = __( 'View invoice', 'woocommerce' );
			$text  = __( "View invoice", 'byconsole-delivery-slip-pdf-invoice' );
			$class = "byconsole_view_invoice";
			$target= "_blank";

			echo '<a href="' . $url . '" class="button tips bycinvoice_buttons ' . $class . '" data-tip="' . $text . '" title="' . $text . '" target="'.$target.'">' . $text . '</a>';
						
		}
		else
		{		
						
			$url   = wp_nonce_url($_SERVER['REQUEST_URI'].'&create_order_invoice='.$order_id, 'woocommerce-mark-order-status' );
			$text  = __( "Create invoice", 'byconsole-delivery-slip-pdf-invoice' );
			$class = "byconsole_create_invoice";

			echo '<a href="' . $url . '" class="button tips bycinvoice_buttons ' . $class . '" data-tip="' . $text . '" title="' . $text . '">' . $text . '</a>';
			
			
			
			
		}
		
    }
	
	
			
			
}; 
         
/***************** Invoice pdf creation end ******************/



/***************** Shipping slip pdf creation start ******************/

add_action( 'woocommerce_admin_order_actions_end', 'byconsoleinvoice_shipping_slip_woocommerce_admin_order_actions_end');
function byconsoleinvoice_shipping_slip_woocommerce_admin_order_actions_end( $order ) { 
		// make action magic happen here... 
global $byconsoleorderinvoicedir;	
global $woocommerce;
global $wpdb;
	

$order_data = $order->get_data(); // The Order data
$order_id = $order_data['id'];

$bycorderinvoice_pdf_invoice_button_behaviour = get_option('bycorderinvoice_pdf_invoice_button_behaviour');


if(!empty($_REQUEST['byc_create_shipping_slip_invoice']))
{
	
	if($_REQUEST['byc_create_shipping_slip_invoice'] == $order_id)
	{		
		
	
$order = new WC_Order( intval($_REQUEST['byc_create_shipping_slip_invoice']) );
$items = $order->get_items();
$item_for_loop_var = '';
foreach ( $items as $item ) {
	$item_for_loop_var .= '<tr>';    
	$item_for_loop_var .='<td colspan="2" style="padding:20px; width: 50%;">'.$item['name'].'</td>';
	$item_for_loop_var .='<td style="width: 10%;">'.$item['quantity'].'</td>';
	$item_for_loop_var .='<td style="width: 12%;">'.get_woocommerce_currency_symbol().'&nbsp;'.number_format($item['subtotal'], 2, '.', '').'</td>';
	$item_for_loop_var .='<td style="width: 12%;">'.get_woocommerce_currency_symbol().'&nbsp;'.number_format($item['subtotal'], 2, '.', '').'</td>';
	$item_for_loop_var .='<td style="width: 13%;">'.get_woocommerce_currency_symbol().'&nbsp;'.number_format($item['total_tax'], 2, '.', '').'</td>';	
	$item_for_loop_var .='</tr>';   
}

$byc_product_order_date = substr($order->get_date_created(),0,10);

$byc_product_order_date_explode = explode("-",$byc_product_order_date);

$byc_product_order_date_explode_val = $byc_product_order_date_explode[2].'-'.$byc_product_order_date_explode[1].'-'.$byc_product_order_date_explode[0];

$byconsolewooodt_delivery_type = get_post_meta( $order_id, 'byconsolewooodt_delivery_type', true );
$byconsolewooodt_pickup_location = get_post_meta( $order_id, 'byconsolewooodt_pickup_location', true );
$byconsolewooodt_delivery_date = get_post_meta( $order_id, 'byconsolewooodt_delivery_date', true );
$byconsolewooodt_delivery_time = get_post_meta( $order_id, 'byconsolewooodt_delivery_time', true );


	
$order_subtotal = $order->get_subtotal();

$order_total_tax = $order->get_total_tax();

$order_total = $order->get_total();	

$order_shipping_total = $order->get_total_shipping();

if(!empty($order_shipping_total))
{
	$order_shipping_total_continer = '<tr><td>Shipping Charges</td><td align="right">'.get_woocommerce_currency_symbol().'&nbsp;'.number_format($order_shipping_total, 2, '.', '').'</td></tr>';
}
else
{
	$order_shipping_total_continer = '';
}
	
$store_field = '';
if(!empty(get_option('bycorderinvoice_store_address')))
{
	$store_field .='<b>Store Address:</b><br />';
	
	$store_field .= get_option('bycorderinvoice_store_address').'<br />';
}
if(!empty(get_option('bycorderinvoice_store_city')))
{
	$store_field .= get_option('bycorderinvoice_store_city').'<br />';
}

if(!empty(get_option('bycorderinvoice_store_state')))
{
	$store_field .= get_option('bycorderinvoice_store_state').'<br />';
}

if(!empty(get_option('bycorderinvoice_store_country_name')))
{
	$store_field .= get_option('bycorderinvoice_store_country_name').'<br />';
}

if(!empty(get_option('bycorderinvoice_store_zipcode')))
{
	$store_field .= get_option('bycorderinvoice_store_zipcode').'<br />';
}
	
	

$shipping_slip_html = '<table width="100%" border="0" cellspacing="0" cellpadding="3">
	
    
	<tr>
	<td style="width: 35%;font-size: 70px;color: #6d6d6d;font-weight: bold;">Shipping slip</td>
	<td style="width: 20%;"></td>
    <td style="width: 10%;"></td>
	<td style="width: 10%;"></td>
	<td style="width: 25%;"></td>            
	</tr>
    
    
	<tr>
	<td style="width: 25%;">'.$store_field.'</td>
    
   
	<td style="width: 20%;"></td>
    <td style="width: 10%;"></td>
	<td style="width: 10%;"></td>
	<td style="width: 35%;">
    <table width="100%" border="0" cellspacing="0" cellpadding="4">
            					
                <tr>
                	<td>Order</td>
                    <td style="width: 45%;float: left;" align="left">'.intval($_REQUEST['byc_create_shipping_slip_invoice']).'</td>
                </tr>
				
				
                <tr>
                	<td style="">Date</td>
                    <td style="width: 45%;float: left;" align="left">'.$byc_product_order_date_explode_val.'</td>
                </tr>
				
                <tr>
                	<td style="">Order Amount</td>
                    <td style="width: 45%;float: left;" align="left">'.get_woocommerce_currency_symbol().'&nbsp;'.number_format($order_total, 2, '.', '').'</td>
                </tr>
				
                
            </table>
	</td>

            
	</tr>
	
    <tr><td colspan="5" height="8px;">&nbsp;</td></tr>
    
    <tr>
	<td style="width: 45%;"><b>Ship To:</b><br />'.
    $order->get_shipping_first_name().' '.$order->get_shipping_last_name().', '.
	$order->get_shipping_company().', '.
	$order->get_shipping_address_1().'  '.$order->get_shipping_address_2().', '.
	$order->get_shipping_city().', '.
	$order->get_shipping_state().', '.
	$order->get_shipping_postcode().', '.
	$order->get_shipping_country().'</td>
    <td style="width: 10%;"></td>
	<td style="width: 10%;"></td>
	</tr>
    <tr>
    <td colspan="5" height="15px;">&nbsp;</td>
  	</tr>
	<tr bgcolor="#ddd">
        <td colspan="2" style="padding:20px; width: 50%;"><b>Product</b></td>
        <td style="width: 10%;"><b>Qty</b></td>
        <td style="width: 12%;"><b>Price</b></td>
        <td style="width: 12%;"><b>Line total</b></td>
        <td style="width: 13%;"><b>Tax</b></td>
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
                    <td align="right">'.get_woocommerce_currency_symbol().'&nbsp;'.number_format($order_subtotal, 2, '.', '').'</td>
                </tr>
				
				<tr>
                	<td>Tax</td>
                    <td align="right">'.get_woocommerce_currency_symbol().'&nbsp;'.number_format($order_total_tax, 2, '.', '').'</td>
                </tr>
				'.$order_shipping_total_continer.'
				                
                <tr>
                	<td colspan="2"><hr style="height:2px; background-color:#000;" /></td>
                    
                </tr>
                <tr>
                	<td>Total</td>
                    <td align="right">'.get_woocommerce_currency_symbol().'&nbsp;'.number_format($order_total, 2, '.', '').'</td>
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
//$pdf->SetAuthor('Nicola Asuni');
$pdf->SetTitle('Shipping slip');
$pdf->SetSubject('Shipping slip');
$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

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
$pdf->SetFont('times', '', 12);

// add a page
$pdf->AddPage();

// set some text to print
/*$txt = <<<EOD
TCPDF Example 003

Custom page header and footer are defined by extending the TCPDF class and overriding the Header() and Footer() methods.
EOD;*/


	
		


$pdf->writeHTML($shipping_slip_html, true, false, true, false, '');

// print a block of text using Write()
//$pdf->Write($h=0, $txt, $link='', $fill=0, $align='C', $ln=true, $stretch=0, $firstline=false, $firstblock=false, $maxh=0);

// ---------------------------------------------------------
//ob_end_clean();
//Close and output PDF document
$pdf->Output($byconsoleorderinvoicedir.$randnumber_of_card_pdf.'_shipping_slip_invoice_files.pdf', 'F');


update_post_meta( $order_id, '_byconsolewooodt_created_shipping_slip_invoice', $byconsoleorderinvoicedir.$randnumber_of_card_pdf.'_shipping_slip_invoice_files.pdf');

// Close and output PDF document
// This method has several options, check the source code documentation for more information.


	
	
	/*********************** PDF CODE END ********************************/
		
					
		$url   = wp_nonce_url( admin_url( 'edit.php?post_type=shop_order?action='.$bycorderinvoice_pdf_invoice_button_behaviour.'&byc_view_shipping_slip_invoice_no='.$order_id), 'woocommerce-mark-order-status' );	
		$name  = __( 'View shipping slip invoice', 'woocommerce' );
		$text  = __( "View shipping slip invoice", 'byconsole-delivery-slip-pdf-invoice' );
		$class = "byconsole_view_shipping_slip_invoice";
		
				
		$paged_id = intval($_REQUEST['paged']);
		$back_url = admin_url('edit.php?post_type=shop_order&paged='.$paged_id);	
		header('location:'.$back_url);	
			

			//echo '<a href="' . $url . '" class="button tips bycinvoice_buttons ' . $class . '" data-tip="' . $text . '" title="' . $text . '" target="'.$target.'">' . $text . '</a>';
	}
	else
	{
		$url = wp_nonce_url( admin_url( 'edit.php?post_type=shop_order&byc_create_shipping_slip_invoice='.$order_id ), 'woocommerce-mark-order-status' );
		$text  = __( "Create shipping slip", 'byconsole-shipping-slip-pdf-invoice' );
		$class = "byconsole_create_shipping_slip_invoice";

		echo '<a href="' . $url . '" class="button tips bycinvoice_buttons ' . $class . '" data-tip="' . $text . '" title="' . $text . '">' . $text . '</a>';
		
	}
		
		
}else{
	
		$byconsolewooodt_created_shipping_slip_invoice =  get_post_meta( $order_id, '_byconsolewooodt_created_shipping_slip_invoice', true );
		
		if(!empty($byconsolewooodt_created_shipping_slip_invoice))
		{
			$shipping_slip_invoice_pdf_link = strrchr($byconsolewooodt_created_shipping_slip_invoice,$byconsoleorderinvoicedir);
			
			$shipping_slip_invoice_pdf_link_explode = explode("/",$shipping_slip_invoice_pdf_link);
			$shipping_slip_invoice_pdf_link_explode_last_val = explode(".",$shipping_slip_invoice_pdf_link_explode[1]);
			
						
		$url   = wp_nonce_url(get_site_url().'/wp-admin/admin.php?action='.$bycorderinvoice_pdf_invoice_button_behaviour.'&bycpdffile='.$shipping_slip_invoice_pdf_link_explode_last_val[0].'&byc_view_shipping_slip_invoice_no='.$order_id.'&pdffor=shippingslip', 'woocommerce-mark-order-status' );
		$name  = __( 'View shipping slip', 'byconsole-shipping-slip-pdf-invoice' );
		$text  = __( "View shipping slip", 'byconsole-shipping-slip-pdf-invoice' );
		$class = "byconsole_view_shipping_slip_invoice";
		$target= "_blank";
		
		echo '<a href="' . $url . '" class="button tips bycinvoice_buttons ' . $class . '" data-tip="' . $text . '" title="' . $text . '" target="'.$target.'">' . $text . '</a>';	

			
		}
		else
		{	
		
					
		$url = wp_nonce_url($_SERVER['REQUEST_URI'].'&byc_create_shipping_slip_invoice='.$order_id, 'woocommerce-mark-order-status' );
		$text  = __( "Create shipping slip", 'byconsole-shipping-slip-pdf-invoice' );
		$class = "byconsole_create_shipping_slip_invoice";

		echo '<a href="' . $url . '" class="button tips bycinvoice_buttons ' . $class . '" data-tip="' . $text . '" title="' . $text . '">' . $text . '</a>';
			
			
			
			
		}
		
    }
		
		
			
}; 
         
/***************** Shipping slip pdf creation end ******************/


/***************** admin_init Start ******************/
add_action('admin_init', 'byconsoleinvoice_set_header');

function byconsoleinvoice_set_header()
{
global $byconsoleorderinvoicedir;
	
	
	if(!empty($_REQUEST['action']))
	{
		if(!empty($_REQUEST["bycpdffile"]))	{ $bycpdffile = $_REQUEST["bycpdffile"]; }	else  {	$bycpdffile = ''; }
				
		$byc_invoice_file = sanitize_text_field($byconsoleorderinvoicedir).sanitize_text_field($bycpdffile).'.pdf';
	
		if($_REQUEST['action'] == 'view')
		{
		
			if($_REQUEST['pdffor']=='invoiceslip')	
			{
				if($_REQUEST['bycpdffile'] !='' && $_REQUEST['byc_invoice_no'] !='')
				{
					header("Content-type:application/pdf");
					header("Content-disposition: inline; filename=invoice_" .intval($_REQUEST["byc_invoice_no"]).'.pdf');
					header("content-Transfer-Encoding:binary");
					header('Content-Length: ' . filesize($byc_invoice_file));
					header('Accept-Ranges: bytes');
					@readfile($byc_invoice_file);
				
				}
			}
			
			if($_REQUEST['pdffor']=='shippingslip')	
			{			
				if($_REQUEST['bycpdffile'] !='' && $_REQUEST['byc_view_shipping_slip_invoice_no'] !='')
				{
					header("Content-type:application/pdf");
					header("Content-disposition: inline; filename=shipping_slip_" .intval($_REQUEST['byc_view_shipping_slip_invoice_no']).'.pdf');
					header("content-Transfer-Encoding:binary");
					header('Content-Length: ' . filesize($byc_invoice_file));
					header('Accept-Ranges: bytes');
					@readfile($byc_invoice_file);
		
				}
			}
		}
	
		if($_REQUEST['action'] == 'download')
		{		
			if($_REQUEST['pdffor']=='invoiceslip')	
			{	
				if($_REQUEST['bycpdffile'] !='' && $_REQUEST['byc_invoice_no'] !='')
				{		
			
					header("Content-Disposition: attachment; filename=invoice_" .intval($_REQUEST['byc_invoice_no']).'.pdf');   
					header("Content-Type: application/octet-stream");
					header("Content-Type: application/download");
					header("Content-Description: File Transfer");            
					header("Content-Length: " . filesize($byc_invoice_file));
					flush(); // this doesn't really matter.
					$fp = fopen($byc_invoice_file, "r");
					while (!feof($fp))
					{
						echo fread($fp, filesize($byc_invoice_file));
						flush(); // this is essential for large downloads
					} 
					fclose($fp);
			
				}
			}
			
			if($_REQUEST['pdffor']=='shippingslip')	
			{
				if($_REQUEST['bycpdffile'] !='' && $_REQUEST['byc_view_shipping_slip_invoice_no'] !='')
				{
					
			
					header("Content-Disposition: attachment; filename=shipping_slip_" .intval($_REQUEST['byc_view_shipping_slip_invoice_no']).'.pdf');   
					header("Content-Type: application/octet-stream");
					header("Content-Type: application/download");
					header("Content-Description: File Transfer");            
					header("Content-Length: " . filesize($byc_invoice_file));
					flush(); // this doesn't really matter.
					$fp = fopen($byc_invoice_file, "r");
					while (!feof($fp))
					{
						echo fread($fp, filesize($byc_invoice_file));
						flush(); // this is essential for large downloads
					} 
					fclose($fp);
			
				}
			}
		}
	}
}
/***************** admin_init End ******************/


/***************** Right Side Metabox Start ******************/
add_action( 'add_meta_boxes', 'byconsoleinvoice_add_meta_boxes',1 );

function byconsoleinvoice_add_meta_boxes($order)
{
	
    add_meta_box( 
        'woocommerce-order-byconsoleinvoice', 
        __( 'PDF Invoice And Shipping Slip' ), 
        'byconsoleinvoice_order_pdf_invoice_and_shipping_slip', 
        'shop_order', 
        'side', 
        'default' 
    );
}
function byconsoleinvoice_order_pdf_invoice_and_shipping_slip()
{
global $byconsoleorderinvoicedir;
	
	$order_id = intval($_REQUEST['post']);
	$bycorderinvoice_pdf_invoice_button_behaviour = sanitize_text_field(get_option('bycorderinvoice_pdf_invoice_button_behaviour'));			
	$byconsolewooodt_created_invoice_invoice =  get_post_meta( $order_id, '_byconsolewooodt_created_invoice', true );
	$byconsolewooodt_created_shipping_slip_invoice =  get_post_meta( $order_id, '_byconsolewooodt_created_shipping_slip_invoice', true );
	
	
	echo '<div class="byconsole_create_invoice_meta_box">
		  <h2>Invoice</h2>';
		
	if(!empty($byconsolewooodt_created_invoice_invoice))
	{
		$invoice_pdf_link = strrchr($byconsolewooodt_created_invoice_invoice,$byconsoleorderinvoicedir);
		
		$invoice_pdf_link_explode = explode("/",$invoice_pdf_link);
		$invoice_pdf_link_explode_last_val = explode(".",$invoice_pdf_link_explode[1]);
		
		
		echo '<div class="byconsole_invoice_remove_and_view_action_button_container">
		<a href="'.get_site_url().'/wp-admin/post.php?post='.$order_id.'&action=edit&byc_remove_order_invoice='.$order_id.'&remove_pdf_name='.$invoice_pdf_link_explode_last_val[0].'" class="byconsole_pdf_remove_button">Remove</a>&nbsp;&nbsp;';
		
		echo '<a href="'.get_site_url().'/wp-admin/admin.php?action='.$bycorderinvoice_pdf_invoice_button_behaviour.'&bycpdffile='.$invoice_pdf_link_explode_last_val[0].'&byc_invoice_no='.$order_id.'&pdffor=invoiceslip" target="_new" class="byconsole_pdf_view_button">View</a></div>';
		
	}
	else
	{
		echo '<div class="byconsole_invoice_create_action_button_container"><a href="'.get_site_url().'/wp-admin/post.php?post='.$order_id.'&action=edit&create_order_invoice='.$order_id.'" class="byconsole_pdf_create_button">Create</a></div>';
	}
	
	echo '</div>';

	
	

	if(!empty($_REQUEST['create_order_invoice']))
	{
	
		if($_REQUEST['create_order_invoice'] == $order_id)
		{		
		
			$invoice_started_no_option = get_option('invoice_started_no_option');
			$bycorderinvoice_invoice_generation = get_option('bycorderinvoice_invoice_generation');		
			$bycorderinvoice_next_invoice_number = get_option('bycorderinvoice_next_invoice_number');
			
			$autometic_invoice_number_started_value = 0;
			
			if($bycorderinvoice_invoice_generation == 'automatic')
			{	
				if($invoice_started_no_option == '')
				{
					$invoice_started_with_numeric_value = '0';
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
						$invoice_started_with_numeric_value = '0';
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
		
		
	$order = new WC_Order( intval($_REQUEST['create_order_invoice']) );
	$items = $order->get_items();
	$item_for_loop_var = '';
	foreach ( $items as $item ) {
		$item_for_loop_var .= '<tr>';    
		$item_for_loop_var .='<td colspan="2" style="padding:20px; width: 50%;">'.sanitize_text_field($item['name']).'</td>';
		$item_for_loop_var .='<td style="width: 10%;">'.$item['quantity'].'</td>';
		$item_for_loop_var .='<td style="width: 12%;">'.get_woocommerce_currency_symbol().'&nbsp;'.sanitize_text_field($item['subtotal']) .'</td>';
		$item_for_loop_var .='<td style="width: 12%;">'.get_woocommerce_currency_symbol().'&nbsp;'.sanitize_text_field($item['subtotal']) .'</td>';
		$item_for_loop_var .='<td style="width: 13%;">'.get_woocommerce_currency_symbol().'&nbsp;'.number_format(sanitize_text_field($item['total_tax']),2, '.', '').'</td>';	
		$item_for_loop_var .='</tr>';   
	}
	
	$byc_product_order_date = substr($order->get_date_created(),0,10);
	
	$byc_product_order_date_explode = explode("-",$byc_product_order_date);
	
	$byc_product_order_date_explode_val = $byc_product_order_date_explode[2].'-'.$byc_product_order_date_explode[1].'-'.$byc_product_order_date_explode[0];
	
	
	
	$byconsolewooodt_delivery_type = get_post_meta( $order->get_id(), 'byconsolewooodt_delivery_type', true );
	
	if($byconsolewooodt_delivery_type == 'take_away')
	{
		$byconsolewooodt_delivery_type_val = 'Pickup';
		$pickup_location_get_option_array_value = get_option('byconsolewooodt_pickup_location');
		$byconsolewooodt_location_index = get_post_meta( $order->get_id(), 'byconsolewooodt_pickup_location', true );
		if(!empty($byconsolewooodt_location_index))
		{
			$byconsolewooodt_location_name = $pickup_location_get_option_array_value[$byconsolewooodt_location_index]['location'];
		}
		else
		{
			$byconsolewooodt_location_name = '';
		}
	}
	
	if($byconsolewooodt_delivery_type == 'levering')
	{	
		$byconsolewooodt_delivery_type_val = 'Delivery';		
		$delivery_location_get_option_array_value = get_option('byconsolewooodt_delivery_location');
		$byconsolewooodt_location_index = get_post_meta( $order->get_id(), 'byconsolewooodt_delivery_location', true );
		if(!empty($byconsolewooodt_location_index))
		{
			$byconsolewooodt_location_name = $delivery_location_get_option_array_value[$byconsolewooodt_location_index]['location'];
		}
		else
		{
			$byconsolewooodt_location_name = '';
		}
	
	}
	
	
	
	$byconsolewooodt_delivery_date = get_post_meta( $order->get_id(), 'byconsolewooodt_delivery_date', true );
	$byconsolewooodt_delivery_time = get_post_meta( $order->get_id(), 'byconsolewooodt_delivery_time', true );
	
	$bycorderinvoice_print_delivery_or_pickup_date_and_time = get_option('bycorderinvoice_print_delivery_or_pickup_date_and_time');
	
	if($bycorderinvoice_print_delivery_or_pickup_date_and_time == 'yes')
	{
	$woodt_field = '<td style="width: 35%;">';
			
			if($byconsolewooodt_delivery_type != '')
			{			
				$woodt_field .='<b>Order Type:</b> '.esc_html($byconsolewooodt_delivery_type_val).'<br /><br />';
			}		
			if($byconsolewooodt_location_name != '')
			{
				$woodt_field .='<b>'.$byconsolewooodt_delivery_type_val . ' Location:</b> '.esc_html($byconsolewooodt_location_name).'<br /><br />';
			}
			if($byconsolewooodt_delivery_date != '')
			{		
				$woodt_field .='<b>'.$byconsolewooodt_delivery_type_val . ' Date:</b> '.esc_html(get_post_meta( $order_id, 'byconsolewooodt_delivery_date', true )).'<br /><br />';
			}
			if($byconsolewooodt_delivery_time != '')
			{
				$woodt_field .='<b>'.$byconsolewooodt_delivery_type_val . ' Time:</b> '.esc_html(get_post_meta( $order_id, 'byconsolewooodt_delivery_time', true ));
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
	
	$order_subtotal = $order->get_subtotal();

	$order_total_tax = $order->get_total_tax();

	$order_total = $order->get_total();
	
	$order_shipping_total = $order->get_total_shipping();

	if(!empty($order_shipping_total))
	{
		$order_shipping_total_continer = '<tr><td>Shipping Charges</td><td align="right">'.esc_html(get_woocommerce_currency_symbol()).'&nbsp;'.number_format($order_shipping_total, 2, '.', '').'</td></tr>';
	}
	else
	{
		$order_shipping_total_continer = '';
	}
	
	
	$bycorderinvoice_company_logo = get_option('bycorderinvoice_company_logo');
	if(!empty($bycorderinvoice_company_logo))
	{
		$byc_company_logo = '<img src="'.esc_url(get_option('bycorderinvoice_company_logo')).'" alt="" />';
	}
	else
	{
		$byc_company_logo = '';
	}
	
	
		
$store_field = '';
if(!empty(get_option('bycorderinvoice_store_address')))
{
	$store_field .= get_option('bycorderinvoice_store_address').'<br />';
}
if(!empty(get_option('bycorderinvoice_store_city')))
{
	$store_field .= get_option('bycorderinvoice_store_city').'<br />';
}

if(!empty(get_option('bycorderinvoice_store_state')))
{
	$store_field .= get_option('bycorderinvoice_store_state').',';
}

if(!empty(get_option('bycorderinvoice_store_country_name')))
{
	$store_field .= get_option('bycorderinvoice_store_country_name').',';
}

if(!empty(get_option('bycorderinvoice_store_zipcode')))
{
	$store_field .= get_option('bycorderinvoice_store_zipcode').'<br />';
}
	
	
	$html = '<table width="100%" border="0" cellspacing="0" cellpadding="3">
		
		
		<tr>
		<td style="width: 25%;">'.$byc_company_logo.'</td>
		<td style="width: 20%;"></td>
		<td style="width: 10%;"></td>
		<td style="width: 10%;"></td>
		<td style="width: 35%;font-size: 70px;color: #6d6d6d;font-weight: bold;">Invoice</td>            
		</tr>
		
		
		<tr>
		<td style="width: 25%;">'.esc_html($store_field).'</td>
		
	   
		<td style="width: 20%;"></td>
		<td style="width: 10%;"></td>
		<td style="width: 10%;"></td>
		<td style="width: 35%;">
		<table width="100%" border="0" cellspacing="0" cellpadding="4">
					<tr><td>
						<tr><td>Invoice No :</td></tr>
						<tr><td align="left">'.esc_html($full_details_with_pre_suf).'</td></tr>
					</td></tr>				
					<tr>
						<td style="width: 45%;">Order No. :</td>
						<td align="left">'.intval($_REQUEST['create_order_invoice']).'</td>
					</tr>
					
					
					<tr>
						<td style="width: 45%;">Date :</td>
						<td align="left">'.esc_html($byc_product_order_date_explode_val).'</td>
					</tr>
					
					<tr>
						<td style="width: 45%;">Amount :</td>
						<td align="left">'.esc_html(get_woocommerce_currency_symbol()) .'&nbsp;'.number_format($order_total, 2, '.', '').'</td>
					</tr>
					
					
				</table>
		</td>
	
				
		</tr>
		
		<tr><td colspan="5" height="8px;">&nbsp;</td></tr>
		
		<tr>
		<td style="width: 45%;"><b>Bill To:</b><br />'.
		esc_html($order->get_billing_first_name()).' '.esc_html($order->get_billing_last_name()).', '.
		esc_html($order->get_billing_company()).', '.
		esc_html($order->get_billing_address_1()).', '.
		esc_html($order->get_billing_address_2()).', '.
		esc_html($order->get_billing_city()).', '.
		esc_html($order->get_billing_state()).', '.
		esc_html($order->get_billing_postcode()).', '.
		esc_html($order->get_billing_country()).'</td>
		<td style="width: 10%;"></td>
		<td style="width: 10%;"></td>
		'.esc_html($woodt_field).'
	
				
		</tr>
		<tr>
		<td colspan="5" height="15px;">&nbsp;</td>
		</tr>
		<tr bgcolor="#ddd">
			<td colspan="2" style="padding:20px; width: 50%;"><b>Product</b></td>
			<td style="width: 10%;"><b>Qty</b></td>
			<td style="width: 12%;"><b>Price</b></td>
			<td style="width: 12%;"><b>Line total</b></td>
			<td style="width: 13%;"><b>Tax</b></td>
		</tr>
	   
	   <tr>
		<td colspan="2">&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	  </tr>'.esc_html($item_for_loop_var).'<tr>
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
						<td align="right">'.esc_html(get_woocommerce_currency_symbol()).'&nbsp;'.number_format($order_subtotal, 2, '.', '').'</td>
					</tr>
					
					<tr>
						<td>Tax</td>
						<td align="right">'.(get_woocommerce_currency_symbol()).'&nbsp;'.number_format($order_total_tax, 2, '.', '').'</td>
					</tr>
					'.$order_shipping_total_continer.'
									
					<tr>
						<td colspan="2"><hr style="height:2px; background-color:#000;" /></td>
						
					</tr>
					<tr>
						<td>Total</td>
						<td align="right">'.esc_html(get_woocommerce_currency_symbol()) .'&nbsp;'.number_format($order_total, 2, '.', '').'</td>
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
	$pdf->SetAuthor('Nicola Asuni');
	$pdf->SetTitle('Invoice');
	$pdf->SetSubject('Invoice');
	$pdf->SetKeywords('TCPDF, PDF, example, test, guide');
	
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
	$pdf->SetFont('times', '0', 12);
	
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
	$pdf->Output($byconsoleorderinvoicedir.$randnumber_of_card_pdf.'_invoice_files.pdf', 'F');
	
	
	update_post_meta( $order_id, '_byconsolewooodt_created_invoice', $byconsoleorderinvoicedir.$randnumber_of_card_pdf.'_invoice_files.pdf');
	
	// Close and output PDF document
	// This method has several options, check the source code documentation for more information.
	
	
		
		
		/*********************** PDF CODE END ********************************/
			/*$order_id = $_REQUEST['post'];*/
			$back_url = admin_url('post.php?post='.$order_id.'&action=edit');	
			header('location:'.$back_url);
				
		}
	
	}
	
	if(!empty($_REQUEST['byc_remove_order_invoice']))
	{
		
		$byconsole_invoice_path = $byconsoleorderinvoicedir.esc_html($_REQUEST['remove_pdf_name']).'.pdf';

		chown($byconsole_invoice_path, 666);
		
		if (unlink($byconsole_invoice_path)) {
			//echo 'success';
		} else {
			//echo 'fail';
		}
		
		
		update_post_meta( intval($order_id), '_byconsolewooodt_created_invoice', '');			
		
		$back_url = admin_url('post.php?post='.$order_id.'&action=edit');	
		header('location:'.$back_url);
		
		
	}
	
	echo '<div class="byconsole_create_invoice_meta_box">
		  <h2>Shipping Slip</h2>
		  <div class="byconsole_invoice_action_button_container">';
		  
		if(!empty($byconsolewooodt_created_shipping_slip_invoice))
		{
			
			$shipping_slip_invoice_pdf_link = strrchr($byconsolewooodt_created_shipping_slip_invoice,$byconsoleorderinvoicedir);			
			$shipping_slip_invoice_pdf_link_explode = explode("/",$shipping_slip_invoice_pdf_link);
			$shipping_slip_invoice_pdf_link_explode_last_val = explode(".",$shipping_slip_invoice_pdf_link_explode[1]);
			
			
			echo '<div class="byconsole_invoice_remove_and_view_action_button_container"><a href="'.get_site_url().'/wp-admin/post.php?post='.intval($order_id).'&action=edit&byc_remove_shipping_slip='.intval($order_id).'&remove_pdf_name='.$shipping_slip_invoice_pdf_link_explode_last_val[0].'"  class="byconsole_pdf_remove_button">Remove</a>&nbsp;&nbsp;';
			
			echo '<a href="'.get_site_url().'/wp-admin/admin.php?action='.$bycorderinvoice_pdf_invoice_button_behaviour.'&bycpdffile='.$shipping_slip_invoice_pdf_link_explode_last_val[0].'&byc_view_shipping_slip_invoice_no='.intval($order_id).'&pdffor=shippingslip" target="_new" class="byconsole_pdf_view_button">View</a></div>';
			
		}
		else
		{
			echo '<div class="byconsole_invoice_create_action_button_container"><a href="'.get_site_url().'/wp-admin/post.php?post='.$order_id.'&action=edit&byc_create_shipping_slip_invoice='.intval($order_id).'" class="byconsole_pdf_create_button">Create</a></div>';
		}	
		  
	echo '</div></div>';  
	
	
	if(!empty($_REQUEST['byc_create_shipping_slip_invoice']))
	{
		
		if($_REQUEST['byc_create_shipping_slip_invoice'] == $order_id)
		{		
		
	$order = new WC_Order( $_REQUEST['byc_create_shipping_slip_invoice'] );
	$items = $order->get_items();
	$item_for_loop_var = '';
	foreach ( $items as $item ) {
		$item_for_loop_var .= '<tr>';    
		$item_for_loop_var .='<td colspan="2" style="padding:20px; width: 50%;">'.esc_html($item['name']).'</td>';
		$item_for_loop_var .='<td style="width: 10%;">'.intval($item['quantity']).'</td>';
		$item_for_loop_var .='<td style="width: 12%;">'.esc_html(get_woocommerce_currency_symbol()).'&nbsp;'.number_format($item['subtotal'], 2, '.', '').'</td>';
		$item_for_loop_var .='<td style="width: 12%;">'.esc_html(get_woocommerce_currency_symbol()).'&nbsp;'.number_format($item['subtotal'], 2, '.', '').'</td>';
		$item_for_loop_var .='<td style="width: 13%;">'.esc_html(get_woocommerce_currency_symbol()).'&nbsp;'.number_format($item['total_tax'], 2, '.', '').'</td>';	
		$item_for_loop_var .='</tr>';   
	}
	
	$byc_product_order_date = substr($order->get_date_created(),0,10);
	
	$byc_product_order_date_explode = explode("-",$byc_product_order_date);
	
	$byc_product_order_date_explode_val = $byc_product_order_date_explode[2].'-'.$byc_product_order_date_explode[1].'-'.$byc_product_order_date_explode[0];
	
	$byconsolewooodt_delivery_type = get_post_meta( $order_id, 'byconsolewooodt_delivery_type', true );
	$byconsolewooodt_pickup_location = get_post_meta( $order_id, 'byconsolewooodt_pickup_location', true );
	$byconsolewooodt_delivery_date = get_post_meta( $order_id, 'byconsolewooodt_delivery_date', true );
	$byconsolewooodt_delivery_time = get_post_meta( $order_id, 'byconsolewooodt_delivery_time', true );
	
	
	$order_subtotal = $order->get_subtotal();

	$order_total_tax = $order->get_total_tax();

	$order_total = $order->get_total();	
	
		
	$order_shipping_total = $order->get_total_shipping();

	if(!empty($order_shipping_total))
	{
		$order_shipping_total_continer = '<tr><td>Shipping Charges</td><td align="right">'.esc_html(get_woocommerce_currency_symbol()).'&nbsp;'.number_format($order_shipping_total, 2, '.', '').'</td></tr>';
	}
	else
	{
		$order_shipping_total_continer = '';
	}

	
	//$html2 = '<pre>'.$order.'</pre>'; 
	
$store_field = '';
if(!empty(get_option('bycorderinvoice_store_address')))
{
	$store_field .='<b>Store Address:</b><br />';
	
	$store_field .= get_option('bycorderinvoice_store_address').'<br />';
}
if(!empty(get_option('bycorderinvoice_store_city')))
{
	$store_field .= get_option('bycorderinvoice_store_city').'<br />';
}

if(!empty(get_option('bycorderinvoice_store_state')))
{
	$store_field .= get_option('bycorderinvoice_store_state').'<br />';
}

if(!empty(get_option('bycorderinvoice_store_country_name')))
{
	$store_field .= get_option('bycorderinvoice_store_country_name').'<br />';
}

if(!empty(get_option('bycorderinvoice_store_zipcode')))
{
	$store_field .= get_option('bycorderinvoice_store_zipcode').'<br />';
}
	
	
	$shipping_slip_html = '<table width="100%" border="0" cellspacing="0" cellpadding="3">
		
		
		<tr>
		<td style="width: 35%;font-size: 70px;color: #6d6d6d;font-weight: bold;">Shipping slip</td>
		<td style="width: 20%;"></td>
		<td style="width: 10%;"></td>
		<td style="width: 10%;"></td>
		<td style="width: 25%;"></td>            
		</tr>
		
		
		<tr>
		<td style="width: 25%;">'.esc_html($store_field).'</td>
		
	   
		<td style="width: 20%;"></td>
		<td style="width: 10%;"></td>
		<td style="width: 10%;"></td>
		<td style="width: 35%;">
		<table width="100%" border="0" cellspacing="0" cellpadding="4">
									
					<tr>
						<td>Order</td>
						<td style="width: 45%;float: left;" align="left">'.intval($_REQUEST['byc_create_shipping_slip_invoice']).'</td>
					</tr>
					
					
					<tr>
						<td style="">Date</td>
						<td style="width: 45%;float: left;" align="left">'.esc_html($byc_product_order_date_explode_val).'</td>
					</tr>
					
					<tr>
						<td style="">Order Amount</td>
						<td style="width: 45%;float: left;" align="left">'.esc_html(get_woocommerce_currency_symbol()).'&nbsp;'.number_format($order_total, 2, '.', '').'</td>
					</tr>
					
					
				</table>
		</td>
	
				
		</tr>
		
		<tr><td colspan="5" height="8px;">&nbsp;</td></tr>
		
		<tr>
		<td style="width: 45%;"><b>Ship To:</b><br />'.
		esc_html($order->get_shipping_first_name()).' '.esc_html($order->get_shipping_last_name()).', '.
		esc_html($order->get_shipping_company()).', '.
		esc_html($order->get_shipping_address_1()).'  '.esc_html($order->get_shipping_address_2()).', '.
		esc_html($order->get_shipping_city()).', '.
		esc_html($order->get_shipping_state()).', '.
		esc_html($order->get_shipping_postcode()).', '.
		esc_html($order->get_shipping_country()).'</td>
		<td style="width: 10%;"></td>
		<td style="width: 10%;"></td>
		</tr>
		<tr>
		<td colspan="5" height="15px;">&nbsp;</td>
		</tr>
		<tr bgcolor="#ddd">
			<td colspan="2" style="padding:20px; width: 50%;"><b>Product</b></td>
			<td style="width: 10%;"><b>Qty</b></td>
			<td style="width: 12%;"><b>Price</b></td>
			<td style="width: 12%;"><b>Line total</b></td>
			<td style="width: 13%;"><b>Tax</b></td>
		</tr>
	   
	   <tr>
		<td colspan="2">&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	  </tr>'.esc_html($item_for_loop_var).'<tr>
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
						<td align="right">'.esc_html(get_woocommerce_currency_symbol()).'&nbsp;'.number_format($order_subtotal, 2, '.', '').'</td>
					</tr>
					
					<tr>
						<td>Tax</td>
						<td align="right">'.esc_html(get_woocommerce_currency_symbol()).'&nbsp;'.number_format($order_total_tax, 2, '.', '').'</td>
					</tr>
					'.$order_shipping_total_continer.'
									
					<tr>
						<td colspan="2"><hr style="height:2px; background-color:#000;" /></td>
						
					</tr>
					<tr>
						<td>Total</td>
						<td align="right">'.esc_html(get_woocommerce_currency_symbol()).'&nbsp;'.number_format($order_total, 2, '.', '').'</td>
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
	$pdf->SetAuthor('Nicola Asuni');
	$pdf->SetTitle('Shipping slip');
	$pdf->SetSubject('Shipping slip');
	$pdf->SetKeywords('TCPDF, PDF, example, test, guide');
	
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
	$pdf->SetFont('times', '', 12);
	
	// add a page
	$pdf->AddPage();
	
	// set some text to print
	/*$txt = <<<EOD
	TCPDF Example 003
	
	Custom page header and footer are defined by extending the TCPDF class and overriding the Header() and Footer() methods.
	EOD;*/
	
	
		
			
	
	
	$pdf->writeHTML($shipping_slip_html, true, false, true, false, '');
	
	// print a block of text using Write()
	//$pdf->Write($h=0, $txt, $link='', $fill=0, $align='C', $ln=true, $stretch=0, $firstline=false, $firstblock=false, $maxh=0);
	
	// ---------------------------------------------------------
	//ob_end_clean();
	//Close and output PDF document
	$pdf->Output($byconsoleorderinvoicedir.$randnumber_of_card_pdf.'_shipping_slip_invoice_files.pdf', 'F');
	
	
	update_post_meta( intval($order_id), '_byconsolewooodt_created_shipping_slip_invoice', esc_html($byconsoleorderinvoicedir).esc_html($randnumber_of_card_pdf).'_shipping_slip_invoice_files.pdf');
	
	// Close and output PDF document
	// This method has several options, check the source code documentation for more information.
	
	
		
		
		/*********************** PDF CODE END ********************************/
			
						
			$back_url = admin_url('post.php?post='.intval($order_id).'&action=edit');	
			header('location:'.esc_url($back_url));
				
		}
		
		
			
			
	}
	
	
	if(!empty($_REQUEST['byc_remove_shipping_slip']))
	{
		
		$byconsole_shipping_slip_path = $byconsoleorderinvoicedir.esc_html($_REQUEST['remove_pdf_name']).'.pdf';

		chown($byconsole_shipping_slip_path, 666);
		
		if (unlink($byconsole_shipping_slip_path)) {
			//echo 'success';
		} else {
			//echo 'fail';
		}
		
		
		update_post_meta( intval($order_id), '_byconsolewooodt_created_shipping_slip_invoice', '');			
		
		$back_url = admin_url('post.php?post='.intval($order_id).'&action=edit');	
		header('location:'.esc_url($back_url));
		
		
	}
	
	
    
}
/***************** Right Side Metabox End ******************/



/***************** Footer Script Start ******************/
add_action('admin_footer','byconsoleinvoice_footer_script',9999);

function byconsoleinvoice_footer_script()
{
	global $woocommerce;
	
	$byconsole_invoice_plugin_url = plugins_url();
	
	$version = "2.6";

if (version_compare($woocommerce->version, $version, ">=")) {?>
<style>
a.button.byconsole_create_invoice, a.button.byconsole_create_invoice:hover {
    background-image: url(<?php echo $byconsole_invoice_plugin_url ?>/byconsoleorderinvoice/images/bycinvoice_create_invoice20x20.png);
    background-repeat: no-repeat;
    background-position: center; 	
}
a.button.byconsole_view_invoice, a.button.byconsole_view_invoice:hover {
    background-image: url(<?php echo $byconsole_invoice_plugin_url ?>/byconsoleorderinvoice/images/bycinvoice_view_invoice20x20.png);
    background-repeat: no-repeat;
    background-position: center; 
}
a.button.byconsole_create_shipping_slip_invoice, a.button.byconsole_create_shipping_slip_invoice:hover {
    background-image: url(<?php echo $byconsole_invoice_plugin_url ?>/byconsoleorderinvoice/images/bycinvoice_create_shipping_slip20x20.png);
    background-repeat: no-repeat;
    background-position: center; 
}
a.button.byconsole_view_shipping_slip_invoice, a.button.byconsole_view_shipping_slip_invoice:hover {
    background-image: url(<?php echo $byconsole_invoice_plugin_url ?>/byconsoleorderinvoice/images/bycinvoice_view_shipping_slip20x20.png);
    background-repeat: no-repeat;
    background-position: center; 
}

</style>
<?php
}
else
{
?>
<style>

a.button.byconsole_create_invoice, a.button.byconsole_create_invoice:hover {
    background-image: url(<?php echo $byconsole_invoice_plugin_url ?>/byconsoleorderinvoice/images/bycinvoice_create_invoice20x20.png);
    background-repeat: no-repeat;
    background-position: center; 	
}
a.button.byconsole_view_invoice, a.button.byconsole_view_invoice:hover {
    background-image: url(<?php echo $byconsole_invoice_plugin_url ?>/byconsoleorderinvoice/images/bycinvoice_view_invoice20x20.png);
    background-repeat: no-repeat;
    background-position: center; 
}
a.button.byconsole_create_shipping_slip_invoice, a.button.byconsole_create_shipping_slip_invoice:hover {
    background-image: url(<?php echo $byconsole_invoice_plugin_url ?>/byconsoleorderinvoice/images/bycinvoice_create_shipping_slip20x20.png);
    background-repeat: no-repeat;
    background-position: center; 
}
a.button.byconsole_view_shipping_slip_invoice, a.button.byconsole_view_shipping_slip_invoice:hover {
    background-image: url(<?php echo $byconsole_invoice_plugin_url ?>/byconsoleorderinvoice/images/bycinvoice_view_shipping_slip20x20.png);
    background-repeat: no-repeat;
    background-position: center; 
}
</style>	
<?php
	
}
?>
<style>
.widefat .column-order_actions a.button.bycinvoice_buttons {
    display: block;
    text-indent: -9999px;
    position: relative;
    padding: 0 !important;
    height: 2em !important;
    width: 2em;
    position: relative;
}

#woocommerce-order-byconsoleinvoice .inside{margin:10px 0px;}

.byconsole_create_invoice_meta_box{border: 1px solid #ccc; min-height: 86px;margin-bottom: 20px;}
.byconsole_create_invoice_meta_box h2{padding: 2px !important;margin-bottom: 20px !important;background-color:#ffa500;color: #000;text-align: center;text-transform: uppercase;margin-bottom: 12px !important;}

.byconsole_invoice_remove_and_view_action_button_container{width:100%;}

.byconsole_invoice_create_action_button_container{width:80%; margin:0 auto;}
.byconsole_invoice_create_action_button_container a{margin: 0 auto;display: block;width: 30%;text-align: center;margin-bottom: 20px;}

.byconsole_pdf_remove_button{color: #000;border-color: #ccc;-webkit-box-shadow: 0 1px 0 #ccc;box-shadow: 0 1px 0 #ccc;vertical-align: top;padding: 6px;text-decoration: none;border-radius: 4px;font-weight: 600;margin: 0 auto;display: block;width: 20%;float: left;margin-left: 50px;text-align: center;border: 1px solid #ccc;background-color: #f7f7f7;font-weight: 400;}
.byconsole_pdf_view_button{color: #000;border-color: #ccc;-webkit-box-shadow: 0 1px 0 #ccc;box-shadow: 0 1px 0 #ccc;vertical-align: top;padding: 6px;text-decoration: none;border-radius: 4px;font-weight: 600;margin: 0 auto;display: block;width: 20%;float: right;margin-right: 50px;text-align: center;border: 1px solid #ccc;background-color: #f7f7f7;font-weight: 400;}
.byconsole_pdf_create_button{color: #000;border-color: #ccc;-webkit-box-shadow: 0 1px 0 #ccc;box-shadow: 0 1px 0 #ccc;vertical-align: top;padding: 6px;text-decoration: none; border-radius: 4px;font-weight: 600;border: 1px solid #ccc;background-color: #f7f7f7;font-weight: 400;}
</style>
<?php
}
/***************** Footer Script End ******************/
?>
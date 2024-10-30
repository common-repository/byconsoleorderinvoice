<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
// add mmenu
add_action('admin_menu','byconsoleorderinvoice_add_plugin_menu');

function byconsoleorderinvoice_add_plugin_menu(){
add_menu_page( 'BYC Order Invoice', 'BYC Order Invoice', 'manage_options', 'byconsoleorderinvoice_general_settings', 'byconsoleorderinvoice_admin_general_settings_form' );

add_submenu_page('byconsoleorderinvoice_general_settings','Store Address','Store Address','manage_options','ByconsoleOrderInvoice_add_store_address_setting','ByconsoleOrderInvoice_add_store_address');


	}
/******/
function ByconsoleOrderInvoice_add_store_address()
{
?>
	<div class="wrap">

<h1>Another WooCommerce PDF invoices and Packing slips plugin Store Address Settings Panel</h1>

<form method="post" class="form_byconsolewooodt_plugin_settings" action="options.php">

<?php

settings_fields("orderinvoicestoresection");

do_settings_sections("byconsoleorderinvoice_store_setting_options");      

submit_button(); 

?>          

</form>

</div>

<?php
}
/******/	
function byconsoleorderinvoice_admin_general_settings_form()
{
?>
<div class="wrap">

<h1>Another WooCommerce PDF invoices and Packing slips plugin Settings Panel</h1>

<form method="post" class="form_byconsolewooodt_plugin_settings" action="options.php">

<?php

settings_fields("orderinvoicesection");

do_settings_sections("byconsoleorderinvoice_setting_options");      

submit_button(); 

?>          

</form>

</div>
<?php 
} 

function bycorderinvoice_store_country_name()
{
	$bycorderinvoice_store_country_name=get_option('bycorderinvoice_store_country_name');
?>
	<input type="text" name="bycorderinvoice_store_country_name" id="bycorderinvoice_store_country_name" value="<?php echo $bycorderinvoice_store_country_name;?>" placeholder="Country Name" size="53" />
<?php
}

function bycorderinvoice_store_address()
{
	$bycorderinvoice_store_address=get_option('bycorderinvoice_store_address');
?>
<textarea name="bycorderinvoice_store_address" id="bycorderinvoice_store_address"  style="width: 350px;height: 165px;max-width: 350px;max-height: 80px;"><?php echo $bycorderinvoice_store_address;?></textarea>
<?php
}

function bycorderinvoice_store_city()
{
	$bycorderinvoice_store_city=get_option('bycorderinvoice_store_city');
?>
<input type="text" name="bycorderinvoice_store_city" id="bycorderinvoice_store_city" value="<?php echo $bycorderinvoice_store_city;?>" placeholder="Town / City"  size="53" />
<?php
}

function bycorderinvoice_store_state()
{
	$bycorderinvoice_store_state=get_option('bycorderinvoice_store_state');
?>
<input type="text" name="bycorderinvoice_store_state" id="bycorderinvoice_store_state" value="<?php echo $bycorderinvoice_store_state;?>" placeholder="State" size="53" />
<?php
}


function bycorderinvoice_store_zipcode()
{
	$bycorderinvoice_store_zipcode=get_option('bycorderinvoice_store_zipcode');
?>
<input type="text" name="bycorderinvoice_store_zipcode" id="bycorderinvoice_store_zipcode" value="<?php echo $bycorderinvoice_store_zipcode;?>" placeholder="Zip code" size="30" />
<?php
}



function bycorderinvoice_company_logo()
{
?>
<img src="<?php echo get_option('bycorderinvoice_company_logo');?>" alt="" /><br />

<input type="text" id="bycorderinvoice_company_logo" name="bycorderinvoice_company_logo" value="<?php echo get_option('bycorderinvoice_company_logo');?>" style="width: 60%;float: left;"/>
<input id="_btn" class="upload_image_button" type="button" value="Upload Image" style="width: 14%;padding: 4px;font-size: 12px;" />

<?php }

function bycorderinvoice_next_invoice_number()
{
?>
<input type="text" name="bycorderinvoice_next_invoice_number" id="bycorderinvoice_next_invoice_number" value="<?php echo get_option('bycorderinvoice_next_invoice_number')?>" />	
<?php }

function bycorderinvoice_invoice_prefix()
{
?>
<input type="text" name="bycorderinvoice_invoice_prefix" id="bycorderinvoice_invoice_prefix" value="<?php echo get_option('bycorderinvoice_invoice_prefix')?>" />	
<?php }

function bycorderinvoice_invoice_suffix()
{
?>
<input type="text" name="bycorderinvoice_invoice_suffix" id="bycorderinvoice_invoice_suffix" value="<?php echo get_option('bycorderinvoice_invoice_suffix')?>" />	
<?php }

function bycorderinvoice_invoice_date_format()
{
?>
<input type="radio" name="bycorderinvoice_invoice_date_format" id="bycorderinvoice_invoice_date_format" value="Y-m-d" <?php if(get_option('bycorderinvoice_invoice_date_format') == 'Y-m-d') {?> checked="checked" <?php } ?> />Y-m-d<br />
<input type="radio" name="bycorderinvoice_invoice_date_format" id="bycorderinvoice_invoice_date_format" value="d-m-Y" <?php if(get_option('bycorderinvoice_invoice_date_format') == 'd-m-Y') {?> checked="checked" <?php } ?> />d-m-Y<br />
<input type="radio" name="bycorderinvoice_invoice_date_format" id="bycorderinvoice_invoice_date_format" value="m-d-Y" <?php if(get_option('bycorderinvoice_invoice_date_format') == 'm-d-Y') {?> checked="checked" <?php } ?> />m-d-Y<br />

<?php }

function bycorderinvoice_invoice_generation()
{
?>
<input type="radio" name="bycorderinvoice_invoice_generation" id="bycorderinvoice_invoice_generation" value="automatic" <?php if(get_option('bycorderinvoice_invoice_generation') == 'automatic'){ ?> checked="checked" <?php } ?> />Automatic increment
<input type="radio" name="bycorderinvoice_invoice_generation" id="bycorderinvoice_invoice_generation" value="manual" <?php if(get_option('bycorderinvoice_invoice_generation') == 'manual'){ ?> checked="checked" <?php } ?>/>Manual(start from here)	
<?php }

function bycorderinvoice_generate_invoice_automatically()
{
?>
<input type="radio" name="bycorderinvoice_generate_invoice_automatically" id="bycorderinvoice_generate_invoice_automatically" value="new" <?php if(get_option('bycorderinvoice_generate_invoice_automatically') == 'new'){ ?> checked="checked" <?php } ?> />For new order.

<input type="radio" name="bycorderinvoice_generate_invoice_automatically" id="bycorderinvoice_generate_invoice_automatically" value="processing" <?php if(get_option('bycorderinvoice_generate_invoice_automatically') == 'processing'){ ?> checked="checked" <?php } ?>/>For processing order.

<input type="radio" name="bycorderinvoice_generate_invoice_automatically" id="bycorderinvoice_generate_invoice_automatically" value="completed" <?php if(get_option('bycorderinvoice_generate_invoice_automatically') == 'completed'){ ?> checked="checked" <?php } ?>/>For completed order.
	
<?php }

function bycorderinvoice_pdf_invoice_button_behavior()
{
?>
<input type="radio" name="bycorderinvoice_pdf_invoice_button_behavior" id="bycorderinvoice_pdf_invoice_button_behavior" value="download" <?php if(get_option('bycorderinvoice_pdf_invoice_button_behavior') == 'download'){ ?> checked="checked" <?php } ?> />Download PDF

<input type="radio" name="bycorderinvoice_pdf_invoice_button_behavior" id="bycorderinvoice_pdf_invoice_button_behavior" value="view" <?php if(get_option('bycorderinvoice_pdf_invoice_button_behavior') == 'view'){ ?> checked="checked" <?php } ?>/>Open PDF on browser	
<?php }


function bycorderinvoice_print_delivery_or_pickup_date_and_time()
{
?>
<input type="radio" name="bycorderinvoice_print_delivery_or_pickup_date_and_time" id="bycorderinvoice_print_delivery_or_pickup_date_and_time" value="yes" <?php if(get_option('bycorderinvoice_print_delivery_or_pickup_date_and_time') == 'yes'){ ?> checked="checked" <?php } ?> />Yes &nbsp;

<input type="radio" name="bycorderinvoice_print_delivery_or_pickup_date_and_time" id="bycorderinvoice_print_delivery_or_pickup_date_and_time" value="no" <?php if(get_option('bycorderinvoice_print_delivery_or_pickup_date_and_time') == 'no'){ ?> checked="checked" <?php } ?>/>No<br />

<span style="color:#a0a5aa">( You need to have WooODT Extended (<a href="https://www.plugins.byconsole.com/product/byconsole-wooodt-extended"  target="_blank">Paid </a> / <a href="https://wordpress.org/plugins/byconsole-woo-order-delivery-time" target="_blank">Free</a>) plugin to print delivery / pickup date time )</span>
<?php }


add_action('admin_init', 'bycorderinvoice_plugin_settings_fields');

function bycorderinvoice_plugin_settings_fields()
{

	add_settings_section("orderinvoicesection", "All Settings", null, "byconsoleorderinvoice_setting_options");	
	
	add_settings_field("bycorderinvoice_company_logo", "Company Logo :", "bycorderinvoice_company_logo", "byconsoleorderinvoice_setting_options", "orderinvoicesection");		
	
	add_settings_field("bycorderinvoice_invoice_prefix", "Invoice number prefix :", "bycorderinvoice_invoice_prefix", "byconsoleorderinvoice_setting_options", "orderinvoicesection");	
	
	add_settings_field("bycorderinvoice_invoice_suffix", "Invoice number suffix :", "bycorderinvoice_invoice_suffix", "byconsoleorderinvoice_setting_options", "orderinvoicesection");
	
	add_settings_field("bycorderinvoice_invoice_date_format", "Invoice date format :", "bycorderinvoice_invoice_date_format", "byconsoleorderinvoice_setting_options", "orderinvoicesection");
	
	add_settings_field("bycorderinvoice_invoice_generation", "Next invoice number :", "bycorderinvoice_invoice_generation", "byconsoleorderinvoice_setting_options", "orderinvoicesection");
	
	add_settings_field("bycorderinvoice_next_invoice_number", "Next invoice number :", "bycorderinvoice_next_invoice_number", "byconsoleorderinvoice_setting_options", "orderinvoicesection");	
	
	//add_settings_field("bycorderinvoice_generate_invoice_automatically", "Generate invoice automatically:", "bycorderinvoice_generate_invoice_automatically", "byconsoleorderinvoice_setting_options", "orderinvoicesection");
	
	add_settings_field("bycorderinvoice_pdf_invoice_button_behavior", "PDF invoice button behavior:", "bycorderinvoice_pdf_invoice_button_behavior", "byconsoleorderinvoice_setting_options", "orderinvoicesection");	
	
	add_settings_field("bycorderinvoice_print_delivery_or_pickup_date_and_time", "Print delivery / pickup date time :", "bycorderinvoice_print_delivery_or_pickup_date_and_time", "byconsoleorderinvoice_setting_options", "orderinvoicesection");
	
	
	
	
	register_setting("orderinvoicesection", "bycorderinvoice_company_logo");
	register_setting("orderinvoicesection", "bycorderinvoice_next_invoice_number");
	register_setting("orderinvoicesection", "bycorderinvoice_invoice_prefix");
	register_setting("orderinvoicesection", "bycorderinvoice_invoice_suffix");
	register_setting("orderinvoicesection", "bycorderinvoice_invoice_date_format");	
	register_setting("orderinvoicesection", "bycorderinvoice_invoice_generation");
	//register_setting("orderinvoicesection", "bycorderinvoice_generate_invoice_automatically");
	register_setting("orderinvoicesection", "bycorderinvoice_pdf_invoice_button_behavior");
	register_setting("orderinvoicesection", "bycorderinvoice_print_delivery_or_pickup_date_and_time");
		
	
	
	
}



add_action('admin_init', 'bycorderinvoice_plugin_store_settings_fields');

function bycorderinvoice_plugin_store_settings_fields()
{
	

	add_settings_section("orderinvoicestoresection", "All Settings", null, "byconsoleorderinvoice_store_setting_options");	
	
	add_settings_field("bycorderinvoice_store_country_name", "Country :", "bycorderinvoice_store_country_name", "byconsoleorderinvoice_store_setting_options", "orderinvoicestoresection");	
	
	add_settings_field("bycorderinvoice_store_address", "Address :", "bycorderinvoice_store_address", "byconsoleorderinvoice_store_setting_options", "orderinvoicestoresection");	
	
	add_settings_field("bycorderinvoice_store_city", "City :", "bycorderinvoice_store_city", "byconsoleorderinvoice_store_setting_options", "orderinvoicestoresection");	
	
	add_settings_field("bycorderinvoice_store_state", "State :", "bycorderinvoice_store_state", "byconsoleorderinvoice_store_setting_options", "orderinvoicestoresection");
	
	add_settings_field("bycorderinvoice_store_zipcode", "Zipcode :", "bycorderinvoice_store_zipcode", "byconsoleorderinvoice_store_setting_options", "orderinvoicestoresection");	
	
	
	register_setting("orderinvoicestoresection", "bycorderinvoice_store_country_name");
	register_setting("orderinvoicestoresection", "bycorderinvoice_store_address");
	register_setting("orderinvoicestoresection", "bycorderinvoice_store_city");
	register_setting("orderinvoicestoresection", "bycorderinvoice_store_state");
	register_setting("orderinvoicestoresection", "bycorderinvoice_store_zipcode");	
	
}
?>
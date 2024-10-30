=== Another PDF invoices and Packing slips addon for WC ===
Contributors: mdalabar
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=MZZCSP2WRCYT2
Tags: woocommerce, pdf, invoices, invoice with delivery date, invoice with delivery date time, invoice with delivery time, packing slips, print, delivery notes, invoice, packing slip, export, email, bulk, Delivery date, print delivery date, packing slip with delivery date time, packing slip with delivery date, packing slip with pickup date, packing slip with pickup date time, delivery date slip, delivery time slip, delivery date time slip, pickup date slip. pickup time slip, pickup date time slip. 
Requires at least: 3.5
Tested up to: 5.2
Requires PHP: 5.3 
WC requires at least: 2.6
WC tested up to: 3.6.3
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create, print, download invoices and packing slips with delivery date time for WooCommerce orders.

== Description ==

Add delivery/pickup date time in your order invoices and packing slips, this plugin add a delivery/pickup date time in your invoices and packing slip when you have this [Woocommerce order delivery or pickup with date and time](https://wordpress.org/plugins/byconsole-woo-order-delivery-time/) pugin installed and activated.    

= Features =

* Create invoice for your WooCommerce order
* Optionally include delivery/pickup date time in invoice
* Create packing slip for your WooCommerce order
* Optionally include delivery/pickup date time in packing slip
* Add your logo in invoices and packing slips
* Autometic serialize invoice numbers
* Manually input next invoice number when required and invoice number will be autometically serially increased from there
* Optionally add prefix and suffinx to your invlice number
* Option to choose date format
* Option to show the invoice in browser or direct download in admin -dashboard order listing page

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly by uploading zip through "Upload Plugin" button in "Plugins" -> "Add New" screen of wp-admin area.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Wp admin menu -> BYC Order Invice screen to configure the plugin
4. Use the Wp admin menu -> Another PDF invoices and Packing slips addon for WC -> Store Address

OR

Search for "Another PDF invoices and Packing slips addon for WC" in "Plugins" -> "Add New" screen within your wp-admin area and click install button and then activate uninstallation complete.


== Frequently Asked Questions ==

= Will this plugin work if I dont have [Woocommerce order delivery or pickup with date and time](https://wordpress.org/plugins/byconsole-woo-order-delivery-time/) plugin =

Yes it will work and you will be able to create print and download invoices and packign slips and you need to uncheck the checkbox on settings page that says "Print delivery / pickup date time :" 


= How can I include delivery/pickup date time in invoices and packing slips =

To include a delivery/pickup date time in your invoices and packing slips you need to have [Woocommerce order delivery or pickup with date and time](https://wordpress.org/plugins/byconsole-woo-order-delivery-time/) plugin installed and activated. Also in setings page you need to check this checkbox that says "Print delivery / pickup date time :"


= My company logo path is not appearing on settings page it remains blank! =

When you click on "Upload image" button on plugin settings page, the media upload popup window appers and when you try to use image from media library that is already uploaded previously, it shows a list of images just click on the "show" link beside of the chosen image then on new screen make sure you clicked the button that says "File URL" on  "Link URL" option, then finally clcik on "Insert into post" button at ythe bottom of the media upload window.

== Screenshots ==

1. PDF invoice and packing slip creation settings page

2. Store address settings page

3. PDF invoice and packaging slip geneartion buttons in wp-admin order listing page  

4. Download or view in browser buttons on wp-admin order listing page

5. Create PDF invoice and packaging slip from order edit page

6. View/remove PDF invoice and packaging slip from order edit page

7. Sample Invoice created by this plugin

8. Sample packaging/shipping slip created by this plugin


== Changelog ==

= 1.0.0 =
Initial version released

= 1.0.1 =
Icon display issue fixed


== Upgrade Notice ==

= 1.0.1 =
Icon display issue fixed, previously sometimes the create/view/download invoice icons was not showing in order list page of admin side, fixed in v1.0.1

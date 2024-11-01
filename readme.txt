==  Quikly Payment Gateway  ==

Contributors: Quikly
Tags: woocommerce payments, products
Requires PHP: 5.6
Requires at least: 6.0
Tested up to: 6.6.1
Stable tag: 2.3.4
Plugin URI: https://pay.quikly.app/web/developer
Version: 3
License: GPL2
License URI: http://www.gnu.org/licenses/gpl-2.0.html


 == Description ==

This plugin integrates Woocommerce with Quikly Payment Facilitator button, which you can use as a Payment gateway for all major credit cards and even Paypal accounts. Quikly has its headquarters in London, United Kingdom and is expanding its services in Latin America. It has the lowest fees in the Latin America market, for more information you can ckeckout the official website. https://pay.quikly.app 

 = Minimum Requirements =

* PHP version 5.2.4 or greater (PHP 5.6 or greater is recommended)
* MySQL version 5.0 or greater (MySQL 5.6 or greater is recommended)
* Wordpress version 6.0 onwards
* WooCommerce version 5.6.0


== Installation ==

1. Install via FTP. Go to your domain's root folder and go to wp-content/plugins/ and upload the Quikly folder on this route. Later go to your domain.com/wp-content/ And go to the Inactive Plugins area and press activate plugin. By the way woocommerce has to be ACTIVATED before this plugin in order for it to work. 

2. If the plugin folder is zipped, go to wp-admin area go to Plugins-> Add New -> Upload Plugin-> Select File and press Install now

==  Plugin Configuration ==

Once installed, go to Woocommerce -> Settings Pick Checkout Tab and once on that tab go to Payment gateways and click on Quikly, once on Quikly, click on Enable Quikly, and put your Quikly account data. Also, we put the default wordpress, checkout requirred fields as mandatory before the Quikly Modal pop ups like, #billing_email" if you add your custom requirred fields, please add them on the Form Validation Fields area in Quikly tab, on this format: Ex: #billing_email", "#my_custom_field", "#my_custom_field2" always use the #id css format. If requirred fields are not filled then Quikly Modal is not going to PopUp. So check this detail before implementing.

==  Security Tips ==

Before using Quikly, enable the sandbox option, to test the gateway. Only the paranoid survive as Andy Grove, CEO of Intel once said. So test before implementing, here is some sandbox credit card numbers and data to test, https://pay.quikly.app/web/developer Once you see that the order has been marked on the checkout page as processed (not really its, sandbox), disable sandbox.  By the way, on sandbox, for security reasons, payments and are not processed in Quikly and the order is not recorded in woocommerce. If you wish to test, that order processing please contact us to guide you on how to do that on the code. For security reasons, sandbox testing is limited. 
Also remember once the plugin is deleted all Quikly data will be erased.

== Changelog ==

= 1.0.0 = Released on Aug 24, 2019

* New: Add sandbox PayBox

== Upgrade notice ==

= 1.0.0 = Released on Aug 24, 2019

* New: Add sandbox PayBox

= 1.1.0 = Released on Nov 21, 2018

* New: Total price added

= 1.2.0 = Released on Nov 21, 2018

* New: Fix All Errors

= 1.3.0 = Released on Apr 07, 2020

* New: Fix All Errors

= 1.3.1 = Released on Apr 07, 2020

* New: Change IMG

= 1.3.2 = Released on Sep 06, 2021

* New: Change name plugin

= 2 = Released on Sep 10, 2021

* New: Plugin update for Wordpress V5.8

= 2.0.1 = Released on Sep 10, 2021

* New: Production plugin for new version

== Screenshots ==

1. The popup with compare table.
2. The button compare.
3. The settings of plugin


==  More information ==

For more information or doubts contact at https://pay.quikly.app, support@quikly.app









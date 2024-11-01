<?php

/*********** PART 3. External Script Area out of the main WC_PAYMENT_GATEWAY CLASS  *************************/

// Grab Admin and Payment Data for Xchange Gateway

function xchange_data_script()
{
	if (!wp_script_is('jquery', 'done')) {
		wp_enqueue_script('jquery');
	}
	//wp_enqueue_script('xchange_paybox_script_index', "https://paybox.quikly.app/paybox/transactionConfirm/transactionmain.js", array("jquery"), '1.0', false);
	wp_enqueue_script('xchange_paybox_script', plugins_url() . "/woo-xchange-payments/XChange.js", array("jquery", "xchange_paybox_script_index"), '1.0', false);

	// x	

	// Asegúrate de que el dominio sea correcto
	// $domain = $_SERVER['HTTP_HOST']; // Usa el dominio actual

	// // Establecer una cookie con SameSite=None para navegadores que requieren cookies de terceros
	// setcookie('mi_cookie', 'valor', [
	// 	'expires' => time() + 3600,  // 1 hora
	// 	'path' => '/',
	// 	'domain' => $domain,
	// 	'secure' => true,            // Requiere HTTPS
	// 	'httponly' => true,
	// 	'samesite' => 'None'         // Para cookies de terceros
	// ]);

	// $data_to_pass = array(
	// 	'order_id' => WC()->session->get('current_order_id')
	// );

	// Pasa los datos al script
	//wp_localize_script('xchange_paybox_script', 'payboxData', $data_to_pass);

	// Cargar el CSS de Rebilly
    wp_enqueue_style('rebilly_framepay_css', 'https://framepay.rebilly.com/rebilly.css');

    // Cargar el script de Rebilly
    wp_enqueue_script('rebilly_framepay_js', 'https://framepay.rebilly.com/rebilly.js', array('jquery'), null, true);


	$data_to_pass = array(
		'order_id' => WC()->session->get('current_order_id')
	);

	// Pasa los datos al script
	wp_localize_script('xchange_paybox_script', 'payboxData', $data_to_pass);

}






function xchange_object_script_shipping()
{

	global $woocommerce;

	if (!wp_script_is('jquery', 'done')) {
		wp_enqueue_script('jquery');
	}
	?>

	<script type="text/javascript">

		var payboxAmount = "<?php echo number_format($woocommerce->cart->total, 2); ?>",
		console.log("payboxAmount", payboxAmount);
		

		//XChange.Reload(data);
	</script>
	<?php
}


function xchange_object_script()
{

	global $woocommerce;
	// Get product data, in this case product names
	$items = $woocommerce->cart->get_cart();
	$product_names = array();

	foreach ($items as $item => $values) {
		$_product = $values['data']->post;
		$product_names[] = $_product->post_title;
	}   // End Product Names

	//To get Xchange user email
	$adb_xchange_settings = get_option('woocommerce_xchange_settings');
	$adb_xchange_usermail = $adb_xchange_settings['adb_username_email'];
	$adb_xchange_username = $adb_xchange_settings['adb_username'];
	$adb_xchange_sandbox_state = $adb_xchange_settings['sandbox'];
	$adb_xchange_customer_firstname = $adb_xchange_settings['customer_firstname'];
	$adb_xchange_customer_lastname = $adb_xchange_settings['customer_lastname'];
	$adb_xchange_customer_email = $adb_xchange_settings['customer_email'];

	$user_info = wp_get_current_user();
	//validation fields array
	$adb_validation_fields_lista = array();
	$adb_validation_fields_lista = $adb_xchange_settings['validation'];
	$arrlength = count($product_names);
	$description = "";

	for ($x = 0; $x < $arrlength; $x++) {

		$description .= $product_names[$x];

		if (($x + 1) < $arrlength) {
			$description .= ", ";
		} else {
			$description .= ".";
		}
	}

	wp_enqueue_style("xchange-preloader", "https://cdn.quikly.app/css/preloader_api.css");
	wp_add_inline_style("xchange-preloader", "
		div.payment_method_xchange::before {
			display: none !important;
		};
		.woocommerce-checkout #payment div.payment_box {
		    padding-top: 0px !important;
		};
	");

	$paybox_production = false;

	if ($adb_xchange_sandbox_state !== "yes") {
		$paybox_production = true;
	}

	if (!wp_script_is('jquery', 'done')) {
		wp_enqueue_script('jquery');
	}


	?>
	<script type="text/javascript">
		var data = {
			PayboxRemail: "<?php echo $adb_xchange_usermail; ?>",
			PayboxSendmail: "<?php echo $adb_xchange_customer_email; ?>",
			PayboxRename: "<?php echo $adb_xchange_username; ?>",
			PayboxSendname: "<?php echo $adb_xchange_customer_firstname; ?>" + "," + "<?php echo $adb_xchange_customer_lastname; ?>",
			PayboxAmount: "<?php echo number_format($woocommerce->cart->total, 2); ?>",
			PayboxDescription: "<?php echo $description; ?>",
			PayboxProduction: "<?php echo $paybox_production ?>",
			PayboxRequired: [
				<?php echo $adb_validation_fields_lista; ?>
			],
		}
		//XChange.init(data);
	</script>
	<?php
}



add_action('wp_enqueue_scripts', 'xchange_data_script');
add_action('wp_head', 'xchange_object_script');
add_action('woocommerce_after_shipping_rate', 'xchange_object_script_shipping');

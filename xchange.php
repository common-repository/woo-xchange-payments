<?php
/*
 * Plugin Name: WooCommerce Quikly Payments
 * Plugin URI: https://pay.quikly.app/developer
 * Description: Sell in your ecommerce with any credit / debit card through our plugin.

 * Version: 3
 * Author: QUIKLY GROUP UK LTD
 * Author URI: https://pay.quikly.app
 * License: GPL2
 */



if (!defined('ABSPATH')) {
	exit; /* Exit if accessed directly. Security measure to avoid direct access to plugin under Wordpress Security Guidelines*/
}

/*
Content Index

Part 1. WP admin area set up and creation of the main class WC_Xchange_Gateway extends WC_Payment_Gateway 

Part 2. Process Payment

Part 3. External Script Area

*/

/*********** PART 1. wp-admin Area Scripts and start of the WC_Payment_Gateway which is the Woocommerce API Gateway Plugin Creation Class *************************/


/*
 * This action hook registers our PHP class as a WooCommerce payment gateway
 */




function xchange_add_message_listener_script()
{
	?>
	<script type="text/javascript">
		// Escucha los mensajes entrantes
		window.addEventListener('message', function (event) {
			// Valida el origen para asegurarte de que es seguro
			// if (event.origin !== "https://tu-dominio-de-iframe.com") { // Cambia a tu dominio de iframe
			// 	console.warn('Origen no válido:', event.origin);
			// 	return;
			// }

			// Manejar el mensaje de éxito
			if (event.data.status === 'success') {
				console.log('Pago exitoso recibido en WordPress:', event);

				// Llama a una función de PHP para procesar el pago exitoso
				jQuery.ajax({
					url: "<?php echo admin_url('admin-ajax.php'); ?>",
					type: "POST",
					data: {
						action: 'procesar_pago_exitoso',
					},
					success: function (response) {
						console.log('Pago procesado con éxito en el servidor:', response);
						window.location.href = response.data.redirect;
					}
				});
			} else if (event.data.status === 'false') {
				jQuery.ajax({
					url: "<?php echo admin_url('admin-ajax.php'); ?>",
					type: "POST",
					data: {
						action: 'procesar_pago_fallido',
					},
					success: function (response) {
						console.log('Pago no fue procesado:', response);
					}
				});
			}
		});
	</script>
	<?php
}
add_action('wp_footer', 'xchange_add_message_listener_script');


// Añade la función para manejar el AJAX
function procesar_pago_exitoso()
{ // Elimina $order_id aquí

	// Obtén los datos de la transacción
	$order_id = WC()->session->get('current_order_id');

	// Obtener la orden
	$order = wc_get_order($order_id);

	if ($order) {
		// Actualiza el estado del pedido a "procesando" o cualquier otro estado deseado
		$order->update_status('completed', __('Payment received, order completed', 'woocommerce'));

		// Reduce el inventario de productos
		wc_reduce_stock_levels($order_id);

		// Vacía el carrito
		WC()->cart->empty_cart();

		// Envía una respuesta JSON
		// Obtiene la URL de retorno
		$redirect_url = $order->get_checkout_order_received_url();

		// Envía una respuesta JSON
		wp_send_json_success(
			array(
				'message' => 'Pago procesado correctamente.',
				'orderId' => $order_id,
				'redirect' => $redirect_url  // Incluye la URL de redirección
			)
		);

		// Retorna el resultado y redirige a la página de agradecimiento
		return array(
			'result' => 'success',
			'redirect' => get_return_url($order)
		);
	} else {
		wp_send_json_error('No se encontró la orden.');
	}
	wp_die();
}
add_action('wp_ajax_procesar_pago_exitoso', 'procesar_pago_exitoso');
add_action('wp_ajax_nopriv_procesar_pago_exitoso', 'procesar_pago_exitoso');


function procesar_pago_fallido()
{

	// Obtén los datos de la transacción

	$order_id = WC()->session->get('current_order_id');

	// Obtener la orden
	$order = wc_get_order($order_id);

	// Obtener la orden
	$order = wc_get_order($order_id);

	if ($order) {
		// Actualiza el estado del pedido a "failed"
		$order->update_status('failed', __('Payment failed, order canceled', 'woocommerce'));

		// Envía una respuesta JSON
		wp_send_json_success(
			array(
				'message' => 'Pago fallido, estado de la orden actualizado.',
				'orderId' => $order_id // Incluye el orderId en la respuesta
			)
		);

		// Retorna el resultado y redirige a la página de agradecimiento
		return array(
			'result' => 'success',
			'redirect' => get_return_url($order)
		);
	} else {
		wp_send_json_error('No se encontró la orden.');
	}

	wp_die();
}
add_action('wp_ajax_procesar_pago_fallido', 'procesar_pago_fallido');
add_action('wp_ajax_nopriv_procesar_pago_fallido', 'procesar_pago_fallido');





add_filter('woocommerce_payment_gateways', 'xchange_add_gateway_class');
function xchange_add_gateway_class($gateways)
{
	$gateways[] = 'WC_Xchange_Gateway'; // your class name is here
	return $gateways;
}

/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action('plugins_loaded', 'xchange_init_gateway_class');

function xchange_init_gateway_class()
{

	class WC_Xchange_Gateway extends WC_Payment_Gateway
	{

		/**
		 * Class constructor
		 */
		public function __construct()
		{

			$this->id = 'xchange'; // payment gateway plugin ID
			$this->icon = 'https://cdn.quikly.app/img/logo_cms.png'; // URL of the icon that will be displayed on checkout page near your gateway name
			$this->has_fields = true; // in case you need a custom credit card form
			// Supports the default credit card form
			$this->supports = array('default_credit_card_form');
			$this->method_title = 'Quikly Pay';
			$this->method_description = 'Quikly pay is a method for pay'; // will be displayed on the options page
			//$this->method_adb_username_email = 'XChange Username';

			// gateways can support subscriptions, refunds, saved payment methods, this case Products

			//OJO!!!! SEE if we can open to ALL comment by ADB. Product just testing man. 
			$this->supports = array(
				'products'
			);

			// Supports the default credit card form
			$this->supports = array('default_credit_card_form');

			// This basically defines your settings which are then loaded with init_settings()
			$this->init_form_fields();
			// After init_settings() is called, you can get the settings and load them into variables, e.g:
			// $this->title = $this->get_option( 'title' );
			$this->init_settings();

			// Turn these settings into variables we can use
			foreach ($this->settings as $setting_key => $value) {
				$this->$setting_key = $value;
			}

			$this->title = $this->get_option('title');
			$this->description = $this->get_option('description');
			$this->enabled = $this->get_option('enabled');
			$this->sandbox = $this->get_option('sandbox');
			$this->validation = $this->get_option('validation');
			$this->customer_firstname = $this->get_option('customer_firstname');
			$this->customer_lastname = $this->get_option('customer_lastname');
			$this->customer_email = $this->get_option('customer_email');

			// Turn these settings into variables we can use
			foreach ($this->settings as $setting_key => $value) {
				$this->$setting_key = $value;
			}

			// Turn these settings into variables we can use
			/*foreach ( $this->settings as $setting_key => $value ) {
																																																																																							 $this->$setting_key = $value;
																																																																																						 }*/
			///End

			// This action hook saves the settings
			add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));


			// We need custom JavaScript to obtain a token
			//add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );

			// You can also register a webhook here
			// add_action( 'woocommerce_api_{webhook name}', array( $this, 'webhook' ) );

		}  // End Constructor

		function admin_options()
		{

			$data = '
 				jQuery(document).on("ready", function(){
	 				if (jQuery("#woocommerce_xchange_advanced_options")[0].checked) { 
	 					jQuery("#woocommerce_xchange_customer_firstname").parent().parent().parent().css("display", "");
		 				jQuery("#woocommerce_xchange_customer_lastname").parent().parent().parent().css("display", "");
		 				jQuery("#woocommerce_xchange_customer_email").parent().parent().parent().css("display", "");
		 				jQuery("#woocommerce_xchange_validation").parent().parent().parent().css("display", "");
	 				} else {
	 					jQuery("#woocommerce_xchange_customer_firstname").parent().parent().parent().css("display", "none");
		 				jQuery("#woocommerce_xchange_customer_lastname").parent().parent().parent().css("display", "none");
		 				jQuery("#woocommerce_xchange_customer_email").parent().parent().parent().css("display", "none");
		 				jQuery("#woocommerce_xchange_validation").parent().parent().parent().css("display", "none");
	 				}

	 				jQuery("#woocommerce_xchange_advanced_options").on("click", function(){
	 					if (jQuery("#woocommerce_xchange_advanced_options")[0].checked) {
	 						jQuery("#woocommerce_xchange_customer_firstname").parent().parent().parent().css("display", "");
			 				jQuery("#woocommerce_xchange_customer_lastname").parent().parent().parent().css("display", "");
			 				jQuery("#woocommerce_xchange_customer_email").parent().parent().parent().css("display", "");
			 				jQuery("#woocommerce_xchange_validation").parent().parent().parent().css("display", "");
	 					} else {
			 				jQuery("#woocommerce_xchange_customer_firstname").parent().parent().parent().css("display", "none");
			 				jQuery("#woocommerce_xchange_customer_lastname").parent().parent().parent().css("display", "none");
			 				jQuery("#woocommerce_xchange_customer_email").parent().parent().parent().css("display", "none");
			 				jQuery("#woocommerce_xchange_validation").parent().parent().parent().css("display", "none");
	 					}
	 				});
	 			});
 			';

			wp_enqueue_script('xchange_admin_script', 'https://paybox.quikly.app/js/data.js', array(), '1.0');
			wp_add_inline_script('xchange_admin_script', $data);
			?>
			<h2>
				<?php
				_e($this->method_title, 'woocommerce');
				wc_back_link(__(null, 'woocommerce'), admin_url('admin.php?page=wc-settings&tab=checkout'));
				?>
			</h2>
			<?php
			echo wpautop(wp_kses_post($this->method_description));
			?>
			<table class="form-table">
				<?php
				if (empty($form_fields)) {
					$form_fields = $this->get_form_fields();
				}
				$html = '';
				foreach ($form_fields as $k => $v) {
					$type = $this->get_field_type($v);
					if (method_exists($this, 'generate_' . $type . '_html')) {
						$html .= $this->{'generate_' . $type . '_html'}($k, $v);
					} else {
						$html .= $this->generate_text_html($k, $v);
					}
				}
				if ($echo) {
					echo $html;
				} else {
					echo $html;
				}
				?>
			</table>
			<?php
		}

		/**
		 * Admin Settings on the wp-admin/woocommerce/settings/xchange
		 */
		public function init_form_fields()
		{

			$this->form_fields = array(
				'enabled' => array(
					'title' => 'Enable/Disable',
					'label' => 'Enable Quikly pay',
					'type' => 'checkbox',
					'description' => '',
					'default' => 'no'
				),

				'sandbox' => array(
					'title' => 'Quikly pay Sandbox',
					'label' => 'Enable SandBox',
					'type' => 'checkbox',
					'description' => 'Quikly pay sandbox can be used to test payments. Recommended before using it in production environment.',
					'default' => 'yes'
				),

				'title' => array(
					'title' => 'Title',
					'type' => 'text',
					'description' => 'This controls the title which the user sees during checkout.',
					'default' => 'Quikly Pay',
					'desc_tip' => true,
				),
				'description' => array(
					'title' => 'Description',
					'type' => 'textarea',
					'description' => 'This controls the description which the user sees during checkout.',
					'default' => 'Buy with your credit card with our payment method.',
				),

				'adb_username_email' => array(
					'title' => 'User Email',
					'type' => 'text',
					'description' => 'Your Quikly pay Email',
				),

				'adb_username' => array(
					'title' => 'Username',
					'type' => 'text',
					'description' => 'Your Quikly pay Username'

				),

				'advanced_options' => array(
					'title' => 'Advanced Options',
					'label' => 'Advanced Options',
					'type' => 'checkbox',
					'description' => '',
				),

				'customer_firstname' => array(
					'title' => 'Customer Firstname',
					'type' => 'text',
					'description' => "Indicate the id of the element that contains the customer's firstname. <b>Default: #billing_first_name</b>",
					'default' => '#billing_first_name',
				),

				'customer_lastname' => array(
					'title' => 'Customer Lastname',
					'type' => 'text',
					'description' => "Indicate the id of the item that contains the customer's lastname. <b>Default: #billing_last_name</b>",
					'default' => '#billing_last_name',
				),

				'customer_email' => array(
					'title' => 'Customer Email',
					'type' => 'text',
					'description' => "Indicate the id of the item that contains the customer's email. <b>Default: #billing_email</b>",
					'default' => '#billing_email',
				),

				'validation' => array(
					'title' => 'Form Validation Fields',
					'type' => 'textarea',
					'description' => 'Required fields to be validated on Checkout Form before XChange pops up (if not filled XChange Modal wont pop up), leave your custom fields, between "", Ex: "#billing_email" , "#my_custom_field"',
					'default' =>
						'"#billing_first_name",
		            	"#billing_last_name",
		            	"#billing_country",
		            	"#billing_address_1",
		            	"#billing_city",
		            	"#billing_state", 
		            	"#billing_postcode", 
		            	"#billing_phone", 
		            	"#billing_email"',
				),
			);
		}

		/*********** PART 2. Process Payment functions from API and custom AJAX function to process the payment via JS not PHP as usually natively done  *************************/

		// Process Payment Woocommerce API - Funcion para Procesar pedido de Wordpress / Woocommerce

		public function process_payment($order_id)
		{

			$order = wc_get_order($order_id);

			if ($order) {


				WC()->session->set('current_order_id', $order_id);

				// Guarda el order_key en la sesión
				// $order_key = $order->get_order_key();
				// WC()->session->set('current_order_key', $order_key);

				// No hacer más acciones después de enviar la respuesta JSON

				//wc_add_notice(__('Pago ha sido procesado exitosamente !', 'woothemes'), 'success');

				return array(
					'result' => 'success'
				);

				wp_die();





				// Encola los scripts y pasa los datos a los scripts
				// add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

				// // Retorna el resultado y redirige a la página de agradecimiento
				// return array(
				// 	'result' => 'success',
				// 	'redirect' => $this->get_return_url($order)
				// );

			}
		}

		// Validate form fields
		public function validate_fields()
		{
			return true;
		}

		// To add the Xchange Button on the Xchange payment box

		public function payment_fields()
		{
			global $woocommerce;
			$viewIframe = false;

			// Obtén los datos de descripción desde las configuraciones
			$adb_xchange_settings2 = get_option('woocommerce_xchange_settings');
			$adb_xchange_description_admin_area = $adb_xchange_settings2['description'];

			// Inicia el formulario de tarjeta de crédito
			do_action('woocommerce_credit_card_form_start');

			// Muestra la descripción y el div ButtonPaybox
			echo '<p>' . $adb_xchange_description_admin_area . '
				<form id="payment-form">
					<p id="card-error" role="alert" style="color: red !important; text-align: center;"></p>
					<input class="name-input" id="cardname" data-rebilly="fullName" name="cardholdername" type="text"
						placeholder="Nombre en la tarjeta" required />
					<input class="email-input" id="cardemail" type="text" placeholder="Email" required />
					<input class="id-input" id="cedula" type="text" placeholder="Cédula" required />
					<div class="field">
						<div style="height: 45px !important;" id="mounting-point"></div>
						<p id="error"></p>
					</div>
					
        		</form>';

			// Agrega el div que contendrá el iframe
			echo '<div id="divIframe" style="display: none;"></div>';


			// Termina el formulario de tarjeta de crédito
			do_action('woocommerce_credit_card_form_end');


			// Inserta el código de inicialización de Rebilly aquí
			?>
			<script type="text/javascript">


				function getPayment() {

				}


				function generateUUID() {
					let d = new Date().getTime();
					if (typeof performance !== 'undefined' && typeof performance.now === 'function') {
						d += performance.now();
					}
					return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
						const r = (d + Math.random() * 16) % 16 | 0;
						d = Math.floor(d / 16);
						return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
					});
				}

				var payboxAmount = "<?php echo number_format($woocommerce->cart->total, 2); ?>";
				console.log("amount", payboxAmount);

				<?php
				$adb_xchange_settings = get_option('woocommerce_xchange_settings');
				$adb_xchange_usermail = $adb_xchange_settings['adb_username_email'];
				$adb_xchange_username = $adb_xchange_settings['adb_username'];
				?>

				var destinationEmail = "<?php echo $adb_xchange_usermail; ?>";
				var destinationName = "<?php echo $adb_xchange_username; ?>"
				console.log("Email destino:", destinationEmail);
				console.log("Nombre", destinationName)


				Rebilly.initialize({
					publishableKey: 'pk_live_wuoLby1FyygWxxcjbR0i1D8GZ8UGHn1afNiW-tg',
					icon: {
						color: "#888da8",
					},
					style: {
						base: {
							fontSize: "16px",
							boxShadow: "none",
							color: "#888da8",
							padding: "45px",
							height: "45px",
						},
					},
				});

				Rebilly.on("ready", function () {
					var card = Rebilly.card.mount("#mounting-point");
					card.on('change', function (data) {
						document.getElementById('error').innerText = data.valid ? '' : data.error.message;
						document.getElementById('error').style.color = data.valid ? '' : 'red';
					});
					$mountedFrames.push(card);
					console.log("mounting", $mountedFrames);
				});


				var payWithCard = function (billingAddress, paymenToken, amount) {
					var sendMail = document.getElementById("cardemail").value;
					var transactionId = generateUUID();
					var rename = billingAddress.firstName + " " + billingAddress.lastName;
					console.log("transacion idGenerado", transactionId);
					let urlCreateTransaction = "https://mdjc7112pd.execute-api.us-east-1.amazonaws.com/v1/xchange/dlocal/create-transaction";


					jQuery.ajax({
						type: "POST",
						url: urlCreateTransaction,
						dataType: "json",
						data: {
							idReference: transactionId,
							address: billingAddress,
							token: paymenToken,
							amount: amount,
							redirectUrl: `https://paybox.quikly.app/paybox/transactionConfirm/transaction.html?idReference=${transactionId}&wordpress=true`,
							callbackUrl: "https://paybox.quikly.app/paybox/transactionConfirm/transaction.html",
							destinationEmail: destinationEmail,
							destinationName: destinationName,
							senderEmail: sendMail,
							senderName: rename,
							method: "4",
							exchangeRate: amount,
						},
						success: function (message, text, response) {
							const { checkout_url, isSpecialClient } = response.responseJSON;
							console.log("response.responseJSON", response.responseJSON);

							if (isSpecialClient) {
								window.location.href = `https://paybox.quikly.app/paybox/transactionConfirm/transaction.html?isSpecialClient=${isSpecialClient}&idReference=${transactionId}`;
							}

							if (checkout_url) {
								console.log("checkout_url", checkout_url._links);
								let approvalLink = checkout_url._links.find(function (link) {
									return link.rel === "approvalUrl";
								});

								let redirect = checkout_url._links.find(function (link) {
									return link.rel === "redirectUrl";
								});


								var approvalUrl = approvalLink ? approvalLink.href : redirect.href;
								if (approvalUrl) {
									// Ocultar el formulario de pago
									document.getElementById('payment-form').style.display = 'none';

									// Mostrar el iframe
									var element = document.getElementById('divIframe');
									if (element) {
										element.style.display = 'block';
										element.innerHTML = '<iframe src="' + approvalUrl + '" width="100%" height="500px" frameborder="0"></iframe>';
									} else {
										console.error('El elemento no existe en el DOM');
									}
								}

							}
						},
						error: function (jqXHR, textStatus, errorThrown) {
							console.error("Error en la solicitud AJAX:", textStatus, errorThrown);
							loading(false);
						}
					});
				};
			</script>
			<?php

			echo "<script>
			jQuery(document).ready(function($) {

				if (!window.hasPaymentScriptRun) {
                window.hasPaymentScriptRun = true;
				function handleSubmit(event) {
					event.preventDefault();
					console.log('El formulario ha sido enviado.');

					try {
						const form = event.target;
						Rebilly.createToken(form).then(function(result) {
							var paymentToken = result.id;
							var billingAddress = result.billingAddress;

							 // Asignar valores personalizados a billingAddress
							billingAddress.country = 'EC';  // Establecer el país
							billingAddress.cedula = document.getElementById('cedula').value;  // Obtener el valor de Cédula

							var amount = payboxAmount;

							console.log('Token generado:', paymentToken);
							console.log('Dirección de facturación:', billingAddress, amount);

							payWithCard(billingAddress,paymentToken,amount);
						}).catch(function(error) {
							console.log('Error al generar el token:', error);
							document.getElementById('error').innerText = 'Error: ' + error.message;
						});
					} catch (error) {
						console.log('Error en el manejo del submit:', error);
					}
				}

					$('form.checkout').on('submit', function(event) {
						setTimeout(function() {
							if ($('ul.woocommerce-error').length > 0) {
								console.log('Hay errores en el formulario, no se ejecutará el script.');
								return; // Si hay errores, no ejecuta handleSubmit
							} else {
							 	$('div.woocommerce-error:contains(\"There was an error processing your order\")').hide();
								
								console.log('No hay errores, se procede a ejecutar handleSubmit.');
								var paymentForm = document.getElementById('payment-form');
								if (paymentForm) {

								setTimeout(function() {
									paymentForm.scrollIntoView({
										behavior: 'smooth',
										block: 'center'
									});
								}, 3000);
								handleSubmit(event);
									
								} else {
									console.error('El elemento con ID  no se encontró.');
								}
								
							}
						}, 1000);
					});
			}
				
		});
		</script>";

		}



		// End Add custom button
	}  //End of Class WC_Xchange_Gateway extends WC_Payment_Gateway
} // End of function xchange_init_gateway_class


/*********** Included file PART 3. External Script Area out of the main WC_PAYMENT_GATEWAY CLASS  *************************/
include 'helpers.php';

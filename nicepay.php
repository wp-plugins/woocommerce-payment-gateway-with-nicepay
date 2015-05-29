<?php
/*
Plugin Name: WooCommerce Payment Gateway with NicePay
Plugin URI: http://www.planet8.co/
Description: Korean Payment Gateway integrated with NicePay for WooCommerce.
Version: 1.0.0
Author: Planet8
Author URI: http://www.planet8.co/
Copyright : Planet8 proprietary.
Developer : Thomas Jang ( thomas@planet8.co )
*/
if ( ! defined( 'ABSPATH' ) ) exit;

// Define
define( PRODUCT_ID, 'wordpress-nicepay' );
define( PRODUCT_VERSION, '1.0.0' );

add_action( 'plugins_loaded', 'woocommerce_nicepay_init', 0 );

function woocommerce_nicepay_init() {
 
if ( !class_exists( 'WC_Payment_Gateway' ) ) return;

// Localization
load_plugin_textdomain( 'wc-gateway-nicepay', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );

// Settings Link
function woocommerce_nicepay_kr_plugin_settings_link( $links ) {
	$url = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_gateway_nicepay_credit', is_ssl() ? 'https' : 'http' );

	$settings_link = '<a href="' . $url . '">' . __( 'Settings', 'wc-gateway-nicepay' ) . '</a>'; 
	array_unshift($links, $settings_link); 
	return $links; 
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'woocommerce_nicepay_kr_plugin_settings_link' );

// Show Virtual Bank Information
function nicepay_vbank_information( $order ) {
	$order = new WC_Order( $order );

	$order_id				= $order->id;
	$payment_method			= get_post_meta( $order->id, '_payment_method', true );

	if ( $payment_method == 'nicepay_virtual' ) {
		$vbankBankName		= get_post_meta( $order_id, '_nicepay_bankname', true );
		$vbankNum			= get_post_meta( $order_id, '_nicepay_bankaccount', true );
		$vbankExpDate		= get_post_meta( $order_id, '_nicepay_expiry_date',true);

		echo  '<h2>' . __( 'Virtual Account Information', 'wc-gateway-nicepay' ) . '</h2>';
		echo  '<div class="clear"></div>';
		echo  '<ul class="order_details">';
		echo  '<li class="order">' . __( 'Virtual Account Bank Name', 'wc-gateway-nicepay' ) . '<strong>' . $vbankBankName . '</strong></li>';
		echo  '<li class="order">' . __( 'Virtual Account No', 'wc-gateway-nicepay' ) . '<strong>' . $vbankNum . '</strong></li>';
		echo  '<li class="order">' . __( 'Incoming Due Date', 'wc-gateway-nicepay' ) . '<strong>' . $vbankExpDate . '</strong></li>';
		echo  '</ul>';
		echo  '<div class="clear"></div>';
	}
}
add_action( 'woocommerce_view_order', 'nicepay_vbank_information', 9 );

// For Planet8 Order Status
if ( ! function_exists( 'register_planet8_order_status' ) ) {
	function register_planet8_order_status() {
		register_post_status( 'wc-on-delivery', array(
			'label'                     => __( 'On Delivery', 'wc-gateway-nicepay' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( __( 'On delivery <span class="count">(%s)</span>', 'wc-gateway-nicepay' ), __( 'On delivery <span class="count">(%s)</span>', 'wc-gateway-nicepay' ) )
		) );
		register_post_status( 'wc-decline', array(
			'label'                     => __( 'Decline Delivery', 'wc-gateway-nicepay' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( __( 'Decline delivery <span class="count">(%s)</span>', 'wc-gateway-nicepay' ), __( 'Decline delivery <span class="count">(%s)</span>', 'wc-gateway-nicepay' ) )
		) );
		register_post_status( 'wc-awaiting', array(
			'label'                     => __( 'Awaiting Payment', 'wc-gateway-nicepay' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( __( 'Awaiting payment <span class="count">(%s)</span>', 'wc-gateway-nicepay' ), __( 'Awaiting payment <span class="count">(%s)</span>', 'wc-gateway-nicepay' ) )
		) );
	}
}
add_action( 'init', 'register_planet8_order_status' );

if ( ! function_exists( 'add_planet8_order_statuses' ) ) {
	function add_planet8_order_statuses( $order_statuses ) {
		$new_order_statuses = array();

		foreach ( $order_statuses as $key => $status ) {
			$new_order_statuses[ $key ] = $status;

			if ( 'wc-processing' === $key ) {
				$new_order_statuses[ 'wc-on-delivery' ]			= __( 'On Delivery', 'wc-gateway-nicepay' );
				$new_order_statuses[ 'wc-decline' ]				= __( 'Decline Delivery', 'wc-gateway-nicepay' );
				$new_order_statuses[ 'wc-awaiting' ]			= __( 'Awaiting Payment', 'wc-gateway-nicepay' );
			}
		}
		return $new_order_statuses;
	}
}
add_filter( 'wc_order_statuses', 'add_planet8_order_statuses' );

if ( ! function_exists( 'add_planet8_order_status_styling' ) ) {
	function add_planet8_order_status_styling() {
		echo '
		<style>
		/* On Delivery */
		.widefat .column-order_status mark.on-delivery:after {
			font-family: WooCommerce;
			speak: none;
			font-weight: 400;
			font-variant: normal;
			text-transform: none;
			line-height: 1;
			-webkit-font-smoothing: antialiased;
			margin: 0;
			text-indent: 0;
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			text-align: center;
			color: #73a724;
			content: "\e019";
		}
		/* Decline Delivery */
		.widefat .column-order_status mark.decline:after {
			font-family: WooCommerce;
			speak: none;
			font-weight: 400;
			font-variant: normal;
			text-transform: none;
			line-height: 1;
			-webkit-font-smoothing: antialiased;
			margin: 0;
			text-indent: 0;
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			text-align: center;
			color: #a72424;
			content: "\e019";
		}
		/* Awaiting Payment */
		.widefat .column-order_status mark.awaiting:after {
			font-family: WooCommerce;
			speak: none;
			font-weight: 400;
			font-variant: normal;
			text-transform: none;
			line-height: 1;
			-webkit-font-smoothing: antialiased;
			margin: 0;
			text-indent: 0;
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			text-align: center;
			color: #999;
			content: "\e012";
		}
		</style>
		';
	}
}
add_action( 'admin_head', 'add_planet8_order_status_styling' );

// For API Callback
class WC_Gateway_NicePay_Response extends WC_Payment_Gateway {
	public function __construct() {
		global $woocommerce;

		add_action( 'woocommerce_api_wc_gateway_nicepay_response', array( $this, 'check_response' ) );
	}

	function check_response() {
		$class = new WC_Gateway_NicePay();
		$class->check_response();
	}
}

class WC_Gateway_NicePay_Mobile_Return extends WC_Payment_Gateway {
	public function __construct() {
		global $woocommerce;

		add_action( 'woocommerce_api_wc_gateway_nicepay_mobile_return', array( $this, 'check_mobile_return' ) );
	}

	function check_mobile_return() {
		$class = new WC_Gateway_NicePay();
		$class->check_mobile_return();
	}
}

class WC_Gateway_NicePay_Mobile_Response extends WC_Payment_Gateway {
	public function __construct() {
		global $woocommerce;

		add_action( 'woocommerce_api_wc_gateway_nicepay_mobile_response', array( $this, 'check_mobile_response' ) );
	}

	function check_mobile_response() {
		$class = new WC_Gateway_NicePay();
		$class->check_mobile_response();
	}
}

class WC_Gateway_NicePay_CAS_Response extends WC_Payment_Gateway {
	public function __construct() {
		global $woocommerce;

		add_action( 'woocommerce_api_wc_gateway_nicepay_cas_response', array( $this, 'check_cas_response' ) );
	}

	function check_cas_response() {
		$class = new WC_Gateway_NicePay();
		$class->check_cas_response();
	}
}

class WC_Gateway_NicePay_Refund_Request extends WC_Payment_Gateway {
	public function __construct() {
		global $woocommerce;

		add_action( 'woocommerce_api_wc_gateway_nicepay_refund_request', array( $this, 'check_refund_request' ) );
	}

	function check_refund_request() {
		$class = new WC_Gateway_NicePay();
		$class->check_refund_request();
	}
}

class WC_Gateway_NicePay_Escrow_Request extends WC_Payment_Gateway {
	public function __construct() {
		global $woocommerce;

		add_action( 'woocommerce_api_wc_gateway_nicepay_escrow_request', array( $this, 'check_escrow_request' ) );
	}

	function check_escrow_request() {
		$class = new WC_Gateway_NicePay();
		$class->check_escrow_request();
	}
}

class WC_Gateway_Nicepay_Meta_Box extends WC_Payment_Gateway {
	public function __construct() {
		global $woocommerce;

		// For Admin Refund
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 30 );

		// For Customer Refund and Order Complete
		add_filter( 'woocommerce_my_account_my_orders_actions',  array( $this, 'woocommerce_my_account_my_orders_actions' ), 10, 2 );
	}

	function add_meta_boxes() {
		add_meta_box( 'wc-gateway-nicepay-refund', __( 'Refund Order', 'wc-gateway-nicepay' ), array( $this, 'meta_box_refund' ), 'shop_order', 'side', 'default' );
		add_meta_box( 'wc-gateway-nicepay-escrow', __( 'Order Delivery (Escrow)', 'wc-gateway-nicepay' ), array( $this, 'meta_box_escrow' ), 'shop_order', 'side', 'default' );
	}

	function meta_box_refund() {
		global $woocommerce, $post;

		$order = new WC_Order( $post->ID );

		$order_id				= $order->id;
		$TID					= get_post_meta( $order_id, '_nicepay_tid', true );
		$order_refund			= get_post_meta( $order_id, '_nicepay_refund', true );
		$order_escw				= get_post_meta( $order_id, '_nicepay_escw', true );

		$class = new WC_Gateway_Nicepay();

		$this->id = get_post_meta( $order->id, '_payment_method', true );
		$this->init_settings();
		$settings = get_option( 'woocommerce_' . $this->id . '_settings' );
		$this->admin_refund = $settings[ 'admin_refund' ];

		$admin_refund_array		= $this->admin_refund;

		if ( is_array( $admin_refund_array ) ) {
			for ( $i=0; $i<sizeof( $admin_refund_array ); $i++ ) {
				if ( $i+1 != sizeof( $admin_refund_array ) ) {
					$admin_refund_string .= $this->get_status( $admin_refund_array[$i] ) . ', ';
				} else {
					$admin_refund_string .= $this->get_status( $admin_refund_array[$i] );
				}
			}
			if ( in_array( 'wc-' . $order->status, $admin_refund_array ) ) {
				$is_refundable = true;
			} else {
				$is_refundable = false;
			}
		} else {
			$admin_refund_string = __( $admin_refund_string, 'wc-gateway-nicepay' );
			if ( $order->status == $admin_refund_array ) {
				$is_refundable = true;
			} else {
				$is_refundable = false;
			}
		}

		if ( $order_refund == 'yes' || $order_escw == 'yes' ) {
			$is_refundable = false;
		}

		if ( $order->status == 'decline' ) $is_refundable = true;

		if ( $is_refundable ) {
			wp_register_script( 'nicepay_admin_script', untrailingslashit( plugins_url( '/', __FILE__ ) ) . '/assets/js/admin.js' );
			wp_enqueue_script( 'nicepay_admin_script' );

			$ask_msg = __( 'Are you sure you want to continue the refund?', 'wc-gateway-nicepay' );

			echo __( 'By clicking the \'Refund\' button below, this order will be refunded and cancelled.', 'wc-gateway-nicepay' );
			echo "<br><br>";
			echo "<input class='button button-primary' type='button' onclick='javascript:doRefund(\"" . $ask_msg . "\", \"" . home_url( '/wc-api/wc_gateway_nicepay_refund_request', is_ssl() ? 'https' : 'http' ) ."\", \"" . $order_id . "\", \"" . $TID . "\");' value='";
			echo __( 'Refund', 'wc-gateway-nicepay' );
			echo "'>";
		} else {
			if ( $order_refund == 'yes' ) {
				echo __( 'This order has already been refunded.', 'wc-gateway-nicepay' );
			} elseif ( $order_escw == 'yes' && $order->status != 'decline' ) {
				echo __( 'This order cannot be refunded since delivery information has already been sent. Please contact the customer to decline the delivery first. You must \'enable\' the customer decline delivery settings.', 'wc-gateway-nicepay' );
			} else {
				if ( $admin_refund_string ) {
					echo sprintf( __( 'This order cannot be refunded. Refundable order status is(are): %s', 'wc-gateway-nicepay' ), $admin_refund_string );
				} else {
					$url = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_gateway_' . $this->id, is_ssl() ? 'https' : 'http' );

					echo sprintf( __( 'There are no refundable order status. Please set them first <a href=\'%s\'>here</a>.', 'wc-gateway-nicepay' ), $url );
				}
			}
		}
	}

	function meta_box_escrow() {
		global $woocommerce, $post;

		$order = new WC_Order( $post->ID );

		$order_id				= $order->id;
		$TID					= get_post_meta( $order_id, '_nicepay_tid', true );
		$order_escw				= get_post_meta( $order_id, '_nicepay_escw', true );

		$class = new WC_Gateway_Nicepay();

		$this->id = get_post_meta( $order->id, '_payment_method', true );

		$this->init_settings();
		$settings = get_option( 'woocommerce_' . $this->id . '_settings' );

		$this->escw_yn = $settings[ 'escw_yn' ];

		if ( $this->escw_yn == 'yes' ) {
			wp_register_script( 'nicepay_admin_script', untrailingslashit( plugins_url( '/', __FILE__ ) ) . '/assets/js/admin.js' );
			wp_enqueue_script( 'nicepay_admin_script' );

			$ask_msg = __( 'Are you sure you want to continue sending delivery information?', 'wc-gateway-nicepay' );
			if ( $order_escw == 'yes' ) {
				echo __( 'Delivery information has already been sent.', 'wc-gateway-nicepay' );
			} else {
				switch ( $order->status ) {
					case 'processing' :
						echo __( 'Fill in the form and click the button below to send delivery information.', 'wc-gateway-nicepay' );
						$show_btn = true;
						$ReqType = '03';
						break;
					case 'on-delivery' :
						echo __( 'This order is on delivery, but delivery information has not been sent. If you want to send delivery information, please fill in the form and click the button below.', 'wc-gateway-nicepay' );
						$show_btn = true;
						$ReqType = '03';
						break;
					default :
						echo __( 'You cannot send any delivery information regarding this order beacuse of the order status.', 'wc-gateway-nicepay' );
				}
			}

			if ( $show_btn ) {
				if ( class_exists( 'Woocommerce_PL_Delivery_Tracking' ) ) {
					Woocommerce_PL_Delivery_Tracking::instance();
					$delivery_array = (array)pl_get_delivery( $order_id, '' );

					$company_array = array();
					$tracking_array = array();

					foreach ( $delivery_array as $delivery ) {
						$company_array[] = $delivery->company_id;
						$tracking_array[] = $delivery->tracking_number;
					}

					$company_array = array_unique( $company_array );
					$tracking_array = array_unique( $tracking_array );

					if ( ! is_null( $company_array[0] ) && is_array( $company_array ) ) {
						echo "<br><br>";
						echo __( 'Company: ', 'wc-gateway-nicepay' );
						echo pl_get_delivery_company_name( $company_array[ 0 ] );
						echo "<input type='hidden' name='company_name' id='nicepay_company_name' value='" . pl_get_delivery_company_name( $company_array[ 0 ] ) . "'>";
						echo "<br>";
						echo __( 'Tracking No.: ', 'wc-gateway-nicepay' );
						echo $tracking_array[ 0 ];
						echo "<input type='hidden' name='tracking_no' id='nicepay_tracking_no' value='" . $tracking_array[ 0 ] . "'>";
						echo "<br><br>";
					} else {
						echo "<br><br>";
						echo __( 'Company', 'wc-gateway-nicepay' );
						echo "<br>" . "<select name='company_name' class='wc-enhanced-select' id='nicepay_company_name'>";
						echo "<option value=''>" . __( 'Select a company...', 'wc-gateway-nicepay' ) . "</option>";
						foreach( pl_get_delivery_company() as $key => $value ) {
							echo "<option value='" . $value . "'>" . $value . "</option>";
						}
						echo "</select>";
						echo "<br>";
						echo __( 'Tracking No.', 'wc-gateway-nicepay' );
						echo "<br>" . "<input type='text' style='width: 100%;' name='tracking_no' id='nicepay_tracking_no' value=''>";
						echo "<br><br>";
					};
				} else {
					echo "<br><br>";
					echo __( 'Company', 'wc-gateway-nicepay' );
					echo "<br>" . "<input type='text' name='company_name' id='nicepay_company_name'>";
					echo "<br>";
					echo __( 'Tracking No.', 'wc-gateway-nicepay' );
					echo "<br>" . "<input type='text' name='tracking_no' id='nicepay_tracking_no' value=''>";
					echo "<br><br>";
				}

				echo "<input class='button button-primary' type='button' onclick='javascript:doEscrow(\"" . $ask_msg . "\", \"" . home_url( '/wc-api/wc_gateway_nicepay_escrow_request', is_ssl() ? 'https' : 'http' ) ."\", \"" . $order_id . "\", \"" . $TID . "\", \"" . $ReqType . "\");' value='";
				echo __( 'Send Information', 'wc-gateway-nicepay' );
				echo "'>";
			}
		} else {
			$url = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_gateway_' . $this->id, is_ssl() ? 'https' : 'http' );

			echo sprintf( __( 'Escrow settings are not set for this order\'s payment method. Please set them first <a href=\'%s\'>here</a>.', 'wc-gateway-nicepay' ), $url );
		}
	}

	function woocommerce_my_account_my_orders_actions( $actions, $order ){
		wp_register_script( 'nicepay_frontend_script', untrailingslashit( plugins_url( '/', __FILE__ ) ) . '/assets/js/frontend.js' );
		wp_enqueue_script( 'nicepay_frontend_script' );

		echo "<input type='hidden' id='ask-refund-msg' value='" . __( 'Are you sure you want to continue the refund?', 'wc-gateway-nicepay' ) . "'>";
		echo "<input type='hidden' id='ask-decline-msg' value='" . __( 'Are you sure you want to decline the delivery?', 'wc-gateway-nicepay' ) . "'>";
		echo "<input type='hidden' id='prompt-confirm-msg' value='" . __( 'Enter your auth num.', 'wc-gateway-nicepay' ) . "'>";

		$payment_method = get_post_meta( $order->id, '_payment_method', true );

		$order_id		= $order->id;
		$TID			= get_post_meta( $order->id, '_nicepay_tid', true );

		$class = new WC_Gateway_Nicepay();

		$this->id = get_post_meta( $order->id, '_payment_method', true );
		$this->init_settings();
		$settings = get_option( 'woocommerce_' . $this->id . '_settings' );
		$this->customer_refund = $settings[ 'customer_refund' ];
		$this->customer_decline = $settings[ 'customer_decline' ];
		$this->refund_btn_txt = $settings[ 'refund_btn_txt' ];

		$refund_btn_txt = $this->refund_btn_txt;
		if ( $refund_btn_txt == '' ) {
			$refund_btn_txt = __( 'Refund', 'wc-gateway-nicepay' );
		}

		$customer_refund_array		= $this->customer_refund;

		if ( is_array ( $customer_refund_array ) ) {
			if ( in_array( 'wc-' . $order->status, $customer_refund_array ) ) {
				$cancel_api = home_url( '/wc-api/wc_gateway_nicepay_refund_request', is_ssl() ? 'https' : 'http' );
				$redirect = get_permalink( wc_get_page_id( 'myaccount' ) );

				$actions[ 'nicepay-cancel' ] = array(
					'url'  => wp_nonce_url( add_query_arg( array( 'customer-cancel' => 'true', 'TID' => $TID, 'order_id' => $order_id, 'redirect' => $redirect ), $cancel_api ), 'nicepay_customer_refund' ),
					'name' => $refund_btn_txt
				);
			}
		}

		if ( $order->status == 'on-delivery' ) {
			if ( is_array( $customer_refund_array ) ) {
				if ( in_array( 'wc-' . $order->status, $customer_refund_array ) ) {
					if ( isset ( $actions[ 'nicepay-cancel' ] ) ) unset( $actions[ 'nicepay-cancel' ] );

					if ( $this->customer_decline == 'yes' ) {
						$confirm_api = home_url( '/wc-api/wc_gateway_nicepay_escrow_request', is_ssl() ? 'https' : 'http' );
						$redirect = get_permalink( wc_get_page_id( 'myaccount' ) );

						$actions[ 'nicepay-escrow-cancel' ] = array(
							'url'  => wp_nonce_url( add_query_arg( array( 'customer-confirm' => 'true', 'order_id' => $order_id, 'TID' => $TID, 'order_id' => $order_id, 'ReqType' => '02', 'DeliveryCoNm' => '', 'InvoiceNum' => '', 'redirect' => $redirect ), $confirm_api ), 'nicepay_customer_confirm' ),
							'name' => __( 'Decline Delivery', 'wc-gateway-nicepay' )
						);
					}
				}

				$confirm_api = home_url( '/wc-api/wc_gateway_nicepay_escrow_request', is_ssl() ? 'https' : 'http' );
				$redirect = get_permalink( wc_get_page_id( 'myaccount' ) );

				$actions[ 'nicepay-confirm-delivery' ] = array(
					'url'  => wp_nonce_url( add_query_arg( array( 'customer-confirm' => 'true', 'order_id' => $order_id, 'TID' => $TID, 'order_id' => $order_id, 'ReqType' => '01', 'DeliveryCoNm' => '', 'InvoiceNum' => '', 'redirect' => $redirect ), $confirm_api ), 'nicepay_customer_confirm' ),
					'name' => __( 'Confirm Delivery', 'wc-gateway-nicepay' )
				);
			}
		}

		return $actions;
	}

	function get_status( $status ) {
		$order_statuses = wc_get_order_statuses();

		return $order_statuses[ $status ];
	}
}

new WC_Gateway_Nicepay_Meta_Box();

// Gateway Class
class WC_Gateway_NicePay extends WC_Payment_Gateway {

	public function __construct() {
		global $woocommerce;
		$this->init_form_fields();
		$this->init_settings();
		$this->add_extra_form_fields();

		$this->method_title		= __( $this->method_title, 'wc-gateway-nicepay' );

		// General Settings
		$this->enabled			= $this->get_option( 'enabled' );
		$this->testmode			= $this->get_option( 'testmode' );
		$this->title			= $this->get_option( 'title' );
		$this->description		= $this->get_option( 'description' );
		$this->MID				= $this->get_option( 'MID' );
		$this->MerchantKey		= $this->get_option( 'MerchantKey' );
		$this->CancelKey		= $this->get_option( 'CancelKey' );
		$this->escw_yn			= $this->get_option( 'escw_yn' );
		$this->ConfirmMail		= $this->get_option( 'ConfirmMail' );
		$this->expiry_time		= $this->get_option( 'expiry_time' );

		// Design Settings
		$this->LogoImage		= $this->get_option( 'LogoImage' );
		$this->BgImage			= $this->get_option( 'BgImage' );
		$this->skintype			= $this->get_option( 'skintype' );
		$this->checkout_txt		= $this->get_option( 'checkout_txt' );
		$this->checkout_img		= $this->get_option( 'checkout_img' );
		$this->show_chrome_msg	= $this->get_option( 'show_chrome_msg' );

		// Refund Settings
		$this->refund_btn_txt	= $this->get_option( 'refund_btn_txt' );
		$this->admin_refund		= $this->get_option( 'admin_refund' );
		$this->cusomter_refund	= $this->get_option( 'cusomter_refund' );

		// Actions
		add_action( 'wp_head', array( $this, 'check' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'nicepay_scripts' ) );
		add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thanks_custom_vbank' ) );
		add_action( 'admin_print_scripts', array( $this, 'p8_admin_scripts' ) );
		add_action( 'admin_print_styles', array( $this, 'p8_admin_styles' ) );

		//For API Callback
		add_action( 'nicepay_process_response', array( $this, 'process_response' ) );
		add_action( 'nicepay_process_mobile_response', array( $this, 'process_mobile_response' ) );
		add_action( 'nicepay_process_mobile_return', array( $this, 'process_mobile_return' ) );
		add_action( 'nicepay_process_cas_response', array( $this, 'process_cas_response' ) );
		add_action( 'nicepay_process_refund_request', array( $this, 'process_refund_request' ) );
		add_action( 'nicepay_process_escrow_request', array( $this, 'process_escrow_request' ) );
		
		if ( ! $this->is_valid_for_use( $this->allowed_currency ) ) {
			if ( ! $this->allow_other_currency ) {
				$this->enabled = 'no';
			}
		}

		if ( $this->testmode == 'yes' ) {
		} else {
			if ( $this->MID == '' ) {
				$this->enabled = 'no';
			} elseif ( $this->MerchantKey == '' ) {
				$this->enabled = 'no';
			} elseif ( $this->CancelKey == '' ) {
				$this->enabled = 'no';
			}
		}


	}

    function init_form_fields() {
		// General Settings
		$general_array = array(
			'general_title' => array(
				'title' => __( 'General Settings', 'wc-gateway-nicepay' ),
				'type' => 'title',
			),
			'enabled' => array(
				'title' => __( 'Enable/Disable', 'wc-gateway-nicepay' ),
				'type' => 'checkbox',
				'label' => __( 'Enable this method.', 'wc-gateway-nicepay' ),
				'default' => 'yes'
			),
			'testmode' => array(
				'title' => __( 'Enable/Disable Test Mode', 'wc-gateway-nicepay' ),
				'type' => 'checkbox',
				'label' => __( 'Enable test mode.', 'wc-gateway-nicepay' ),
				'description' => '',
				'default' => 'no'
			),
			'title' => array(
				'title' => __( 'Title', 'wc-gateway-nicepay' ),
				'type' => 'txt_info',
				'description' => __( 'Title that users will see during checkout. You can customize this in the PRO version.', 'wc-gateway-nicepay' ),
				'txt' => __( $this->title_default, 'wc-gateway-nicepay' ),
			),
			'description' => array(
				'title' => __( 'Description', 'wc-gateway-nicepay' ),
				'type' => 'txt_info',
				'description' => __( 'Description that users will see during checkout. You can customize this in the PRO version.', 'wc-gateway-nicepay' ),
				'txt' => __( $this->desc_default, 'wc-gateway-nicepay' )
			),
			'MID' => array(
				'title' => __( 'MID', 'wc-gateway-nicepay' ),
				'type' => 'text',
				'description' => __( 'Please enter your NicePay MID.', 'wc-gateway-nicepay' ),
				'default' => ''
			),
			'MerchantKey' => array(
				'title' => __( 'Merchant Key', 'wc-gateway-nicepay' ),
				'type' => 'text',
				'description' => __( 'Please enter your NicePay Merchant Key.', 'wc-gateway-nicepay' ),
				'default' => ''
			),
			'CancelKey' => array(
				'title' => __( 'Cancel Key', 'wc-gateway-nicepay' ),
				'type' => 'text',
				'description' => __( 'Please enter your NicePay Cancel Key.', 'wc-gateway-nicepay' ),
				'default' => ''
			),
			'expiry_time' => array(
				'title' => __( 'Expiry time in days', 'wc-gateway-nicepay' ),
				'type'=> 'select',
				'description' => __( 'Select the virtual account transfer expiry time in days.', 'wc-gateway-nicepay' ),
				'options'	=> array(
					'1'			=> __( '1 day', 'wc-gateway-nicepay' ),
					'2'			=> __( '2 days', 'wc-gateway-nicepay' ),
					'3'			=> __( '3 days', 'wc-gateway-nicepay' ),
					'4'			=> __( '4 days', 'wc-gateway-nicepay' ),
					'5'			=> __( '5 days', 'wc-gateway-nicepay' ),
					'6'			=> __( '6 days', 'wc-gateway-nicepay' ),
					'7'			=> __( '7 days', 'wc-gateway-nicepay' ),
					'8'			=> __( '8 days', 'wc-gateway-nicepay' ),
					'9'			=> __( '9 days', 'wc-gateway-nicepay' ),
					'10'		=> __( '10 days', 'wc-gateway-nicepay' ),
				),
				'default' => ( '5' ),
			),
			'escw_yn' => array (
				'title' => __( 'Escrow Settings', 'wc-gateway-nicepay' ),
				'type' => 'txt_info_empty',
				'txt' => __( 'This feature is only available in the PRO version.', 'wc-gateway-nicepay' ),
				'description' => __( 'Force escrow settings.', 'wc-gateway-nicepay' ),
			),
			'ConfirmMail' => array (
				'title' => __( 'Send Confirmation Mail', 'wc-gateway-nicepay' ),
				'type' => 'txt_info_empty',
				'txt' => __( 'This feature is only available in the PRO version.', 'wc-gateway-nicepay' ),
				'description' => __( 'Send confirmation mail to customer.', 'wc-gateway-nicepay' ),
			),
			'customer_decline' => array (
				'title' => __( 'Enable Customer Decline Delivery', 'wc-gateway-nicepay' ),
				'type' => 'txt_info_empty',
				'txt' => __( 'This feature is only available in the PRO version.', 'wc-gateway-nicepay' ),
				'description' => __( 'Show decline delivery button to customer.', 'wc-gateway-nicepay' ),
			)
		);

		// Refund Settings
		$refund_array = array(
			'refund_title' => array(
				'title' => __( 'Refund Settings', 'wc-gateway-nicepay' ),
				'type' => 'title',
			),
			'refund_btn_txt' => array(
				'title' => __( 'Refund Button Text', 'wc-gateway-nicepay' ),
				'type' => 'txt_info_empty',
				'txt' => __( 'This feature is only available in the PRO version.', 'wc-gateway-nicepay' ),
				'description' => __( 'Text for refund button that users will see.', 'wc-gateway-nicepay' ),
			),
			'admin_refund' => array (
				'title' => __( 'Refundable Status for Administrator', 'wc-gateway-nicepay' ),
				'type' => 'multiselect',
				'class' => 'chosen_select',
				'description' => __( 'Select the order status for allowing refund.', 'wc-gateway-nicepay' ),
				'options' => $this->get_status_array(),
			),
			'customer_refund' => array (
				'title' => __( 'Refundable Satus for Customer', 'wc-gateway-nicepay' ),
				'type' => 'txt_info_empty',
				'txt' => __( 'This feature is only available in the PRO version.', 'wc-gateway-nicepay' ),
				'description' => __( 'Select the order status for allowing refund.', 'wc-gateway-nicepay' ),
			),
		);

		// Design Settings
		$design_array = array(
			'design_title' => array(
				'title' => __( 'Design Settings', 'wc-gateway-nicepay' ),
				'type' => 'title',
			),
			'skintype' => array(
				'title' => __( 'Skin Type', 'wc-gateway-nicepay' ),
				'type' => 'select',
				'description' => __( 'Select the skin type for your NicePay form.', 'wc-gateway-nicepay' ),
				'options' => array(
					'BLUE' => 'Blue',
					'RED' => 'Red',
					'PURPLE' => 'Purple',
					'GREEN' => 'Green',
				)
			),
			'LogoImage' => array(
				'title' => __( 'Logo Image', 'wc-gateway-nicepay' ),
				'type' => 'txt_info_empty',
				'txt' => __( 'This feature is only available in the PRO version.', 'wc-gateway-nicepay' ),
				'description' => __( 'Please select or upload your logo. The size should be 95*35. You can use GIF/JPG/PNG.', 'wc-gateway-nicepay' ),
			),	
			'BgImage' => array(
				'title' => __( 'Background Image', 'wc-gateway-nicepay' ),
				'type' => 'txt_info_empty',
				'txt' => __( 'This feature is only available in the PRO version.', 'wc-gateway-nicepay' ),
				'description' => __( 'Please select or upload your image for the background of the payment window. The size should be 505*512. You can use GIF/JPG/PNG.', 'wc-gateway-nicepay' ),
			),	
			'checkout_img' => array(
				'title' => __( 'Checkout Processing Image', 'wc-gateway-nicepay' ),
				'type' => 'txt_info_empty',
				'txt' => __( 'This feature is only available in the PRO version.', 'wc-gateway-nicepay' ),
				'description' => __( 'Please select or upload your image for the checkout processing page. Leave blank to show no image.', 'wc-gateway-nicepay' ),
			),
			'checkout_txt' => array(
				'title' => __( 'Checkout Processing Text', 'wc-gateway-nicepay' ),
				'type' => 'txt_info_empty',
				'txt' => __( 'This feature is only available in the PRO version.', 'wc-gateway-nicepay' ),
				'description' => __( 'Text that users will see on the checkout processing page. You can use some HTML tags as well.', 'wc-gateway-nicepay' ),
			),
			'show_chrome_msg' => array(
				'title' => __( 'Chrome Message', 'wc-gateway-nicepay' ),
				'type' => 'txt_info_empty',
				'txt' => __( 'This feature is only available in the PRO version.', 'wc-gateway-nicepay' ),
				'description' => __( 'Show steps to enable NPAPI for Chrome users.', 'wc-gateway-nicepay' ),
			)
		);

		if ( $this->id == 'nicepay_virtual' ) {
			$general_array = array_merge( $general_array,
				array(
					'callback_url' => array(
						'title' => __( 'Callback URL', 'wc-gateway-nicepay' ),
						'type' => 'txt_info_empty',
						'txt' => home_url( '/wc-api/wc_gateway_nicepay_cas_response' ),
						'description' => __( 'Callback URL used for payment notice from NicePay.', 'wc-gateway-nicepay' )
					)
				)
			);
		}

		if ( $this->id == 'nicepay_mobile' ) {
			$general_array[ 'testmode' ] = array(
				'title' => __( 'Enable/Disable Test Mode', 'wc-gateway-nicepay' ),
				'type' => 'txt_info_empty',
				'txt' => __( 'You cannot test this payment method.', 'wc-gateway-nicepay' ),
				'description' => '',
			);

			unset( $general_array[ 'escw_yn' ] );
			unset( $general_array[ 'ConfirmMail' ] );
			unset( $general_array[ 'customer_decline' ] );
		}

		if ( $this->id != 'nicepay_virtual' ) {
			unset( $general_array[ 'expiry_time' ] );
		}


		$form_array = array_merge( $general_array, $refund_array );
		$form_array = array_merge( $form_array, $design_array );

		$this->form_fields = $form_array;
	}

	function add_extra_form_fields() {

	}

	function is_valid_for_use( $allowed_currency ) {
		if ( is_array ( $allowed_currency ) ) {
			if ( in_array ( get_woocommerce_currency(), $allowed_currency ) ) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	function p8_admin_scripts() {
		wp_enqueue_script( 'media-upload' );
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_script( 'jquery' );
	}

	function p8_admin_styles() {
		wp_enqueue_style( 'thickbox' );
	}

	function generate_txt_info_html( $key, $value ) {
		ob_start();
		?>
		<tr valign="top">
			<th class="titledesc" scope="row">
				<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value[ 'title' ] ); ?></label>
			</th>
			<td class="forminp"><?php echo esc_html( $value[ 'txt' ] ); ?> 
				<p class="description">
				<?php echo esc_html( $value[ 'description' ] ); ?>
				<input type="hidden" name="woocommerce_<?php echo $this->id; ?>_<?php echo esc_attr( $key ); ?>" id="woocommerce_<?php echo $this->id; ?>_<?php echo esc_attr( $key ); ?>" value="<?php echo esc_html( $value[ 'txt' ] ); ?>">
				</p>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}

	function generate_txt_info_empty_html( $key, $value ) {
		ob_start();
		?>
		<tr valign="top">
			<th class="titledesc" scope="row">
				<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value[ 'title' ] ); ?></label>
			</th>
			<td class="forminp"><?php echo esc_html( $value[ 'txt' ] ); ?> 
				<p class="description">
				<?php echo esc_html( $value[ 'description' ] ); ?>
				</p>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}

	function generate_img_upload_html( $key, $value ) {
		ob_start();
		$img_url = $this->$key;
		?>
		<script language="JavaScript">
		jQuery( document ).ready( function() {
			jQuery('#<?php echo esc_attr( $key ); ?>_button').click(function() {
				formfield = jQuery( '#<?php echo esc_attr( $key ); ?>' ).attr( 'name' );

				tb_show( '<?php echo esc_attr( $value[ "title" ] ); ?>', 'media-upload.php?type=image&TB_iframe=true' );

				window.send_to_editor = function( html ) {
					imgurl = jQuery( 'img', html ).attr( 'src' );
					jQuery( '#<?php echo esc_attr( $key ); ?>' ).val( imgurl );
					jQuery( '#<?php echo esc_attr( $key ); ?>_preview_img' ).attr( 'src', imgurl );
					jQuery( '#<?php echo esc_attr( $key ); ?>_preview_tr' ).show();
					jQuery( '#<?php echo esc_attr( $key ); ?>_remove_button' ).show();
					tb_remove();
				}
				return false;
			});

			jQuery( '#<?php echo esc_attr( $key ); ?>_remove_button' ).click(function() {
				jQuery( '#<?php echo esc_attr( $key ); ?>' ).val( '' );
				jQuery( '#<?php echo esc_attr( $key ); ?>_preview_tr' ).hide();
				jQuery( '#<?php echo esc_attr( $key ); ?>_remove_button' ).hide();
				return false;
			});

			jQuery( '#<?php echo esc_attr( $key ); ?>_default_button' ).click(function() {
				jQuery( '#<?php echo esc_attr( $key ); ?>' ).val( '<?php echo $value[ "default_btn_url" ]; ?>' );
				jQuery( '#<?php echo esc_attr( $key ); ?>_preview_img' ).attr( 'src', '<?php echo $value[ "default_btn_url" ]; ?>' );
				jQuery( '#<?php echo esc_attr( $key ); ?>_preview_tr' ).show();
				jQuery( '#<?php echo esc_attr( $key ); ?>_remove_button' ).show();
				return false;
			});
		});
		</script>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value[ 'title' ] ); ?></label>
			</th>
			<td>
				<input id="<?php echo esc_attr( $key ); ?>" type="text" size="36" name="<?php echo esc_attr( $key ); ?>" value="<?php echo $img_url; ?>" />
				<input id="<?php echo esc_attr( $key ); ?>_button" type="button" value="<?php echo esc_attr( $value[ 'btn_name' ] ); ?>" />
				<input id="<?php echo esc_attr( $key ); ?>_remove_button" type="button" value="<?php echo esc_attr( $value[ 'remove_btn_name' ] ); ?>" <?php echo ( $img_url ) ? '' : 'style="display:none;"'; ?> />
				<?php
				if ( $key == 'checkout_img' ) {
				?>
				<input id="<?php echo esc_attr( $key ); ?>_default_button" type="button" value="<?php echo esc_attr( $value[ 'default_btn_name' ] ); ?>" />
				<?php
				}
				?>
				<p class="description"><?php echo esc_html( $value[ 'description' ] ); ?></p>
			</td>
		</tr>
		<tr valign="top" id="<?php echo esc_attr( $key ); ?>_preview_tr" <?php echo ( $img_url ) ? '' : 'style="display:none;"'; ?>>
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $key ); ?>_preview"><?php echo __( 'Preview', 'wc-gateway-nicepay' ); ?></label>
			</th>
			<td>
				<img id="<?php echo esc_attr( $key ); ?>_preview_img" src="<?php echo $img_url; ?>">
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}

	function validate_img_upload_field( $key ) {
		global $woocommerce;

		if ( isset( $_REQUEST[ esc_attr( $key ) ] ) ) {
			return $_REQUEST[ esc_attr( $key ) ];
		} else {
			return false;
		}
	}

	function admin_options() {
		$currency_str = $this->get_currency_str( $this->allowed_currency );

		echo '<h3>' . $this->method_title . '</h3>';

		echo '<div class="inline notice notice-info"><p><strong>' . __( 'Want the PRO version?', 'wc-gateway-nicepay' ) . '</strong> ' . __ ( 'Visit <a href="http://planet8.co" target="_blank">http://planet8.co</a>! You can remove our watermarks and customize many options!', 'wc-gateway-nicepay' ) . '</p></div>';

		if ( $this->get_option( 'testmode' ) == 'yes' ) {
			echo '<div class="inline error"><p><strong>' . __( 'Test mode is enabled!', 'wc-gateway-nicepay' ) . '</strong> ' . __( 'Please disable test mode if you aren\'t testing anything.', 'wc-gateway-nicepay' ) . '</p></div>';
		} else {
			if ( $this->get_option( 'MID' ) == '' ) {
				echo '<div class="inline error"><p><strong>' . __( 'Gateway Disabled', 'wc-gateway-nicepay' ) . '</strong>: ' . __( 'You haven\'t entered a Merchant ID yet!', 'wc-gateway-nicepay' ) . '</p></div>';
			} else if ( $this->get_option( 'MerchantKey' ) == '' ) {
				echo '<div class="inline error"><p><strong>' . __( 'Gateway Disabled', 'wc-gateway-nicepay' ) . '</strong>: ' . __( 'You haven\'t entered a Merchant Key yet!', 'wc-gateway-nicepay' ) . '</p></div>';
			} else if ( $this->get_option( 'CancelKey' ) == '' ) {
				echo '<div class="inline error"><p><strong>' . __( 'Gateway Disabled', 'wc-gateway-nicepay' ) . '</strong>: ' . __( 'You haven\'t entered a Cancel Key yet!', 'wc-gateway-nicepay' ) . '</p></div>';
			}
		}

		if ( ! $this->is_valid_for_use( $this->allowed_currency ) ) {
			if ( ! $this->allow_other_currency ) {
				echo '<div class="inline error"><p><strong>' . __( 'Gateway Disabled', 'wc-gateway-nicepay' ) .'</strong>: ' . sprintf( __( 'Your currency (%s) is not supported by this payment method. This payment method only supports: %s.', 'wc-gateway-nicepay' ), get_woocommerce_currency(), $currency_str ) . '</p></div>';
			} else {
				echo '<div class="inline notice notice-info"><p><strong>' . __( 'Please Note', 'wc-gateway-nicepay' ) .'</strong>: ' . sprintf( __( 'Your currency (%s) is not recommended by this payment method. This payment method recommeds the following currency: %s.', 'wc-gateway-nicepay' ), get_woocommerce_currency(), $currency_str ) . '</p></div>';
			}
		}

		echo '<table class="form-table">';
		$this->generate_settings_html();
		echo '</table>';
	}

	function thanks_custom_vbank( $order_id ) {
		if ( $this->method == 'VBANK' ) {
			$vbankBankName		= get_post_meta( $order_id, '_nicepay_bankname', true );
			$vbankNum			= get_post_meta( $order_id, '_nicepay_bankaccount', true );
			$vbankExpDate		= get_post_meta( $order_id, '_nicepay_expiry_date',true);

			echo  '<p>' . __( 'Virtual Account Information', 'wc-gateway-nicepay' ) . '</p>';
			echo  '<div class="clear"></div>';
			echo  '<ul class="order_details">';
			echo  '<li class="order">' . __( 'Virtual Account Bank Name', 'wc-gateway-nicepay' ) . '<strong>' . $vbankBankName . '</strong></li>';
			echo  '<li class="order">' . __( 'Virtual Account No', 'wc-gateway-nicepay' ) . '<strong>' . $vbankNum . '</strong></li>';
			echo  '<li class="order">' . __( 'Incoming Due Date', 'wc-gateway-nicepay' ) . '<strong>' . $vbankExpDate . '</strong></li>';
			echo  '</ul>';
			echo  '<div class="clear"></div>';
		}
	}

	function receipt( $order_id ) {
		$order = new WC_Order( $order_id );

		echo '<div class="p8-checkout-img"><img src="' . untrailingslashit( plugins_url( '/', __FILE__ ) ) . '/assets/images/checkout_img.png' . '"></div>';

		echo '<div class="p8-checkout-txt">' . __( 'Please wait while your payment is being processed.', 'wc-gateway-nicepay' ) . '</div>';

		echo '<div class="p8-checkout-txt">' . __( 'Powered by <a href="http://planet8.co" target="_blank">Planet8</a>.', 'wc-gateway-nicepay' ) . '</div>';

		require_once dirname( __FILE__ ) . '/bin/lib/Version.php';

		$currency_check = $this->currency_check( $order, $this->allowed_currency );

		if ( $currency_check ) {
			echo $this->nicepay_form( $order_id );
		} else {
			$currency_str = $this->get_currency_str( $this->allowed_currency );

			echo sprintf( __( 'Your currency (%s) is not supported by this payment method. This payment method only supports: %s.', 'wc-gateway-nicepay' ), get_post_meta( $order->id, '_order_currency', true ), $currency_str );
		}
	}

	function get_nicepay_args( $order ) {
		global $woocommerce;

		$order_id = $order->id;

		$this->billing_phone = $order->billing_phone;

		if ( sizeof( $order->get_items() ) > 0 ) {
			foreach ( $order->get_items() as $item ) {
				if ( $item[ 'qty' ] ) {
					$item_name = $item[ 'name' ];
				}
			}
		}

		$timestamp = $this->get_timestamp();

		$Amt			= (int)$order->order_total;
		$MID			= ( $this->testmode=='yes' ) ? 'nictest00m' : $this->MID;
		$MerchantKey	= ( $this->testmode=='yes' ) ? '33F49GnCMS1mFYlGXisbUDzVf2ATWCl9k3R++d5hDd3Frmuos/XLx8XhXpe+LDYAbpGKZYSwtlyyLOtS/8aD7A==' : $this->MerchantKey;

		if ( ! $this->check_mobile() ) {
			$nicepay_args =
				array(
					'PayMethod'				=> $this->method,
					'GoodsCnt'				=> '1',
					'GoodsName'				=> sanitize_text_field( $item_name ),
					'Amt'					=> $Amt,
					'MID'		 			=> $MID,
					'MerchantKey'			=> $MerchantKey,
					'BuyerName'				=> $this->get_name_lang( $order->billing_first_name, $order->billing_last_name ),
					'BuyerTel'				=> $order->billing_phone,
					'UserIP'				=> $this->get_client_ip(),
					'MallIP'				=> $this->get_mall_ip(),
					'EncodeParameters'		=> '',
					'SocketYN'				=> 'Y',
					'EdiDate'				=> $this->get_hashdata( $MerchantKey, $MID, $Amt, 'date' ),
					'EncryptData'			=> $this->get_hashdata( $MerchantKey, $MID, $Amt, 'encrypt' ),
					'GoodsCl'				=> '',
					'Moid'					=> $order->id,
					'BuyerAuthNum'			=> '',
					'BuyerEmail'			=> $order->billing_email,
					'ParentEmail'			=> '',
					'BuyerAddr'				=> $order->billing_address_1,
					'BuyerPostNo'			=> '',
					'SUB_ID'				=> '',
					'MallUserID'			=> '',
					'VbankExpDate'			=> $this->get_expirytime( $this->expiry_time ),
					'SkinType'				=> $this->skintype,
					'TrKey'					=> '',
					'TransType'				=> ( $this->escw_yn=='yes' ) ? '1' : '0',
					'SelectQuota'			=> '00',
					'LogoImage'				=> $this->LogoImage,
					'BgImage'				=> $this->BgImage,
				);
		} else {
			$nicepay_args =
				array(
					'PayMethod'				=> $this->method,
					'GoodsCnt'				=> '1',
					'Moid'					=> $order->id,
					'BuyerTel'				=> $order->billing_phone,
					'BuyerEmail'			=> $order->billing_email,
					'BuyerAddr'				=> $order->billing_address_1,
					'VbankExpDate'			=> $this->get_expirytime( $this->expiry_time ),
					'MallReserved'			=> '',
					'ReturnURL'				=> home_url( '/wc-api/wc_gateway_nicepay_mobile_return', is_ssl() ? 'https' : 'http' ),
					'RetryURL'				=> home_url( '/wc-api/wc_gateway_nicepay_mobile_response', is_ssl() ? 'https' : 'http' ),
					'GoodsCl'				=> '1',
					'CharSet'				=> 'utf-8',
					'MerchantKey'			=> $MerchantKey,
					'Amt'					=> $Amt,
					'GoodsName'				=> sanitize_text_field( $item_name ),
					'BuyerName'				=> $this->get_name_lang( $order->billing_first_name, $order->billing_last_name ),
					'MID'		 			=> $MID,
					'EncryptData'			=> $this->get_hashdata( $MerchantKey, $MID, $Amt, 'encrypt' ),
					'EdiDate'				=> $this->get_hashdata( $MerchantKey, $MID, $Amt, 'date' ),
					'MallUserID'			=> '',
					'SelectQuota'			=> '00',
				);
		}

		$nicepay_args = apply_filters( 'woocommerce_nicepay_args', $nicepay_args );

		return $nicepay_args;
	}

	function nicepay_scripts() {
		if ( ! $this->check_mobile() ) {
			$script_url = 'https://web.nicepay.co.kr/flex/js/nicepay_tr_utf.js';
			wp_register_script( 'nicepay_script', $script_url, array( 'jquery' ), '1.0.0', false );
			wp_enqueue_script( 'nicepay_script' );
		}
	}

	function currency_check( $order, $allowed_currency ) {
		$currency = get_post_meta( $order->id, '_order_currency', true );

		if ( in_array( $currency, $allowed_currency ) ) {
			return true;
		} else {
			return false;
		}
	}

    function nicepay_form( $order_id ) {
		global $woocommerce;

		wp_register_style( 'NicePayFormStylesheet', plugins_url( 'assets/css/frontend.css', __FILE__ ) );
		wp_enqueue_style( 'NicePayFormStylesheet' );

		$order = new WC_Order( $order_id );

		$nicepay_args = $this->get_nicepay_args( $order );

		$nicepay_args_array = array();

		foreach ( $nicepay_args as $key => $value ) {
			//$nicepay_args_array[] = esc_attr( $key ).'<input type="text" style="width:150px;" id="'.esc_attr( $key ).'" name="'.esc_attr( $key ).'" value="'.esc_attr( $value ).'" /><br>';
			$nicepay_args_array[] = '<input type="hidden" name="'.esc_attr( $key ).'" id="'.esc_attr( $key ).'" value="'.esc_attr( $value ).'" />';
		}

		$nicepay_form = "<div id='p8-logo-bg' class='p8-logo-bg'></div>
		<div id='p8-logo-div' class='p8-logo-div'><div class='p8-logo-container'><img src='" . untrailingslashit( plugins_url( '/', __FILE__ ) ) . '/assets/images/p8_logo_white.png' . "'></div></div>";

		if ( ! $this->check_mobile() ) {
			$nicepay_form .= "<form method='post' id='order_info' name='order_info'>" . implode( '', $nicepay_args_array ) . "</form>
			<script type='text/javascript'>
				var payForm = document.order_info;
				
				function hideP8Logo() {
					jQuery('#p8-logo-bg').css('display', 'none');
					jQuery('#p8-logo-div').css('display', 'none');
				}

				function showP8Logo() {
					jQuery('#p8-logo-bg').css('display', 'block');
					jQuery('#p8-logo-div').css('display', 'table');
				}

				function startPayment() {
					hideP8Logo();
					setTimeout('nicepay();', 1000);
				}
				";

				if ( $this->method == 'CARD' || $this->method == 'VBANK' ) {
				$nicepay_form .= "
				if ( payForm.Amt.value <= 500 ) {
					alert('". sprintf( __( 'You cannot use this payment method (%s) for amounts less than 500 Won.', 'wc-gateway-nicepay' ), $this->get_paymethod_txt( $this->method ) )."');
					returnToCheckout();
				} else if ( payForm.BuyerName.value.length < 2 ) {
					alert('". __( 'Please enter more than 2 characters for your name.', 'wc-gateway-nicepay' ) ."');
					returnToCheckout();
				} else {
					NicePayUpdate();
					showP8Logo();
					setTimeout('startPayment();', 3000);
				}
				";
				} else {
				$nicepay_form .= "
				NicePayUpdate();

				jQuery(document).ready(function($){
					showP8Logo();
					setTimeout('startPayment();', 3000);
				});
				";
				}

				$nicepay_form .= "
				function nicepay() {
					payForm.action = '" . home_url( '/wc-api/wc_gateway_nicepay_response', is_ssl() ? 'https' : 'http' ) . "';
					goPay(payForm);
				}
				
				function nicepaySubmit() {
					payForm.action = '" . home_url( '/wc-api/wc_gateway_nicepay_response', is_ssl() ? 'https' : 'http' ) . "';
					payForm.submit();
				}

				function nicepayClose() {
					alert('" . __( 'Your transaction has been cancelled.', 'wc-gateway-nicepay' ) . "');
					payForm.action = '" . $woocommerce->cart->get_checkout_url() . "';
					payForm.submit();
				}

				function returnToCheckout() {
					payForm.action = '" . $woocommerce->cart->get_checkout_url() . "';
					payForm.submit();
				}	
			</script>";
		} else {
			$nicepay_form .= "<form method='post' id='order_info' name='order_info' accept-charset='EUC-KR'>" . implode( '', $nicepay_args_array ) . "</form>
			<script type='text/javascript'>
				var payForm = document.order_info;
				
				function hideP8Logo() {
					jQuery('#p8-logo-bg').css('display', 'none');
					jQuery('#p8-logo-div').css('display', 'none');
				}

				function showP8Logo() {
					jQuery('#p8-logo-bg').css('display', 'block');
					jQuery('#p8-logo-div').css('display', 'table');
				}

				function startPayment() {
					hideP8Logo();
					setTimeout('smart_nicepay();', 1000);
				}
				";

				if ( $this->method == 'CARD' || $this->method == 'VBANK' ) {
				$nicepay_form .= "
				if ( payForm.Amt.value <= 500 ) {
					alert('" . sprintf( __( 'You cannot use this payment method (%s) for amounts less than 500 Won.', 'wc-gateway-nicepay' ), $this->get_paymethod_txt( $this->method ) ) . "');
					returnToCheckout();
				} else if ( payForm.BuyerName.value.length < 2 ) {
					alert('" . __( 'Please enter more than 2 characters for your name.', 'wc-gateway-nicepay' ) . "');
					returnToCheckout();
				} else {
					showP8Logo();
					setTimeout('startPayment();', 3000);
				}
				";
				} else {
				$nicepay_form .= "
				jQuery(document).ready(function($){
					showP8Logo();
					setTimeout('startPayment();', 3000);
				});
				";
				}

				$nicepay_form .= "
				function smart_nicepay() {
					document.charset = 'euc-kr';
					payForm.method = 'post';
					payForm.action = 'https://web.nicepay.co.kr/smart/interfaceURL.jsp';
					document.charset = 'utf-8';
					payForm.submit();
				}

				function returnToCheckout() {
					payForm.action = '" . $woocommerce->cart->get_checkout_url() . "';
					payForm.submit();
				}	
			</script>
			";
		}
		
		return $nicepay_form;
	}

	function process_payment( $order_id ) {
		global $woocommerce;

		$order = new WC_Order( $order_id );
		$order->update_status( 'pending' );
		$order->add_order_note( sprintf( __( 'Starting payment process. Timestamp: %s.', 'wc-gateway-nicepay' ), $this->get_timestamp() ) );

		if ( $this->check_mobile() ) {
			$add_mobile_meta = get_post_meta( $order->id, '_payment_method_title', true );
			if ( ! stripos( $add_mobile_meta, __( ' (Mobile)', 'wc-gateway-nicepay' ) ) ) {
				$add_mobile_meta = $add_mobile_meta . __( ' (Mobile)', 'wc-gateway-nicepay' );
			}
			update_post_meta( $order->id, '_payment_method_title', $add_mobile_meta );
		} else {
			$add_mobile_meta = get_post_meta( $order->id, '_payment_method_title', true );
			if ( stripos( $add_mobile_meta, __( ' (Mobile)', 'wc-gateway-nicepay' ) ) ) {
				$add_mobile_meta = str_replace( __( ' (Mobile)', 'wc-gateway-nicepay' ), '', $add_mobile_meta );
			}
			update_post_meta( $order->id, '_payment_method_title', $add_mobile_meta );

			$nicepay_args = $this->get_nicepay_args( $order );

			session_start();

			require_once dirname( __FILE__ ) . '/bin/lib/NicepayLite.php';

			$nicepay = new NicepayLite;

			$MID						= ( $this->testmode=='yes' ) ? 'nictest00m' : $this->MID;
			$MerchantKey				= ( $this->testmode=='yes' ) ? '33F49GnCMS1mFYlGXisbUDzVf2ATWCl9k3R++d5hDd3Frmuos/XLx8XhXpe+LDYAbpGKZYSwtlyyLOtS/8aD7A==' : $this->MerchantKey;

			$nicepay->m_MID				= $MID;
			$nicepay->m_MerchantKey		= $MerchantKey;
			$nicepay->m_EdiDate			= $this->get_timestamp();
			$nicepay->m_Price			= $nicepay_args[ 'price' ];

			$nicepay->requestProcess();

			$_SESSION[ 'EdiDate' ]		= $nicepay->m_EdiDate;
			$_SESSION[ 'EncryptData' ]	= $nicepay->m_HashedString;
		}

		if ( version_compare( WOOCOMMERCE_VERSION, '2.1', '<' ) ) {
			return array(
				'result' 	=> 'success',
				'redirect'	=> add_query_arg( 'key', $order->order_key, add_query_arg( 'order', $order_id, get_permalink( woocommerce_get_page_id( 'pay' ) ) ) )
			);
		} else {
			return array(
				'result' 	=> 'success',
				'redirect'	=> $order->get_checkout_payment_url( true )
			);
		}
	}

    function check() {
		if ( ! empty( $_POST ) && in_array( $_POST[ 'pay_method' ], $this->require ) ) {
			header( 'HTTP/1.1 200 OK' );
			$_POST = stripslashes_deep( $_POST );
			do_action( 'valid_' . $this->id, $_POST );
		}
	}

	function check_response() {
		global $woocommerce;
		@ob_clean();
		header( 'HTTP/1.1 200 OK' );
		do_action( 'nicepay_process_response', $_REQUEST );
		break;
	}

	function check_mobile_response() {
		global $woocommerce;
		@ob_clean();
		header( 'HTTP/1.1 200 OK' );
		do_action( 'nicepay_process_mobile_response', $_REQUEST );
		break;
	}

	function check_mobile_return() {
		global $woocommerce;
		@ob_clean();
		header( 'HTTP/1.1 200 OK' );
		do_action( 'nicepay_process_mobile_return', $_REQUEST );
	}

	function check_cas_response() {
		global $woocommerce;
		@ob_clean();
		header( 'HTTP/1.1 200 OK' );
		do_action( 'nicepay_process_cas_response', $_REQUEST );
	}

	function check_refund_request() {
		global $woocommerce;
		@ob_clean();
		header( 'HTTP/1.1 200 OK' );
		do_action( 'nicepay_process_refund_request', $_REQUEST );
	}

	function check_escrow_request() {
		global $woocommerce;
		@ob_clean();
		header( 'HTTP/1.1 200 OK' );
		do_action( 'nicepay_process_escrow_request', $_REQUEST );
	}

	function process_response( $params ) {
		global $woocommerce;

		if ( ! empty( $params[ 'TrKey' ] ) ) {
			$order_id = $this->get_orderid( $params[ 'Moid' ] );
			$order = new WC_Order( $order_id );

			if ( $order == null ) {
				wp_die( 'NicePay Payment Request Failure. Order does not exist.' );
			} else {
				$this->id = get_post_meta( $order->id, '_payment_method', true );
				$this->init_settings();
				$settings = get_option( 'woocommerce_' . $this->id . '_settings' );
				$this->testmode = $settings[ 'testmode' ];
				$this->MerchantKey = $settings[ 'MerchantKey' ];
			}

			require_once dirname( __FILE__ ) . '/bin/lib/NicepayLite.php';

			$nicepay = new NicepayLite;

			$nicepay->m_NicepayHome = dirname( __FILE__ ) . '/bin/log';

			$GoodsName		= $params[ 'GoodsName' ];
			$GoodsCnt		= $params[ 'GoodsCnt' ];
			$Amt			= $params[ 'Amt' ];
			$Moid			= $params[ 'Moid' ];
			$BuyerName		= $params[ 'BuyerName' ];
			$BuyerEmail		= $params[ 'BuyerEmail' ];
			$BuyerTel		= $params[ 'BuyerTel' ];
			$MallUserID		= $params[ 'MallUserID' ];
			$GoodsCl		= $params[ 'GoodsCl' ];
			$MID			= $params[ 'MID' ];
			$MallIP			= $params[ 'MallIP' ];
			$TrKey			= $params[ 'TrKey' ];
			$EncryptData	= $params[ 'EncryptData' ];
			$PayMethod		= $params[ 'PayMethod' ];
			$TransType		= $params[ 'TransType' ];

			$nicepay->m_GoodsName		= $GoodsName;
			$nicepay->m_GoodsCnt		= $GoodsCnt;
			$nicepay->m_Price			= $Amt;
			$nicepay->m_Moid			= $Moid;
			$nicepay->m_BuyerName		= $BuyerName;
			$nicepay->m_BuyerEmail		= $BuyerEmail;
			$nicepay->m_BuyerTel		= $BuyerTel;
			$nicepay->m_MallUserID		= $MallUserID;
			$nicepay->m_GoodsCl			= $GoodsCl; 
			$nicepay->m_MID				= $MID;
			$nicepay->m_MallIP			= $MallIP;
			$nicepay->m_TrKey			= $TrKey;
			$nicepay->m_EncryptedData	= $EncryptData;
			$nicepay->m_PayMethod		= $PayMethod;
			$nicepay->m_TransType		= $TransType;
			$nicepay->m_ActionType		= 'PYO';

			$nicepay->m_LicenseKey		= ( $this->testmode=='yes' ) ? '33F49GnCMS1mFYlGXisbUDzVf2ATWCl9k3R++d5hDd3Frmuos/XLx8XhXpe+LDYAbpGKZYSwtlyyLOtS/8aD7A==' : $this->MerchantKey;

			$nicepay->m_charSet			= 'UTF8';

			$nicepay->m_NetCancelAmt	= $Amt;
			$nicepay->m_NetCancelPW		= $this->CancelKey;

			$nicepay->m_ssl				= 'true';

			$nicepay->m_log				= ( $this->testmode=='yes' ) ? true : false;

			$nicepay->startAction();

			$resultCode					= $nicepay->m_ResultData[ 'ResultCode' ];
			$resultMsg					= $nicepay->m_ResultData[ 'ResultMsg' ];

			$tid						= $nicepay->m_ResultData[ 'TID' ];
			$vbankBankName				= $nicepay->m_ResultData[ 'VbankBankName' ];
			$vbankNum					= $nicepay->m_ResultData[ 'VbankNum' ];
			$vbankExpDate				= $nicepay->m_ResultData[ 'VbankExpDate' ];

			$paySuccess = false;

			if ( $PayMethod == 'CARD' ) {
				if ( $resultCode == '3001' ) $paySuccess = true;
			} elseif ( $PayMethod == 'BANK' ) {
				if ( $resultCode == '4000') $paySuccess = true;
			} elseif ( $PayMethod == 'CELLPHONE' ) {
				if ( $resultCode == 'A000' ) $paySuccess = true;
			} elseif ( $PayMethod == 'VBANK' ) {
				if ( $resultCode == '4100' ) $paySuccess = true;
			}

			if ( (int)$Amt != (int)$order->get_total() ) {
				$paySuccess = false;
				$order->update_status( 'on-hold', sprintf( __( 'Failed to verify integrity of payment. Timestamp: %s.', 'wc-gateway-nicepay' ), $this->get_timestamp() ) );
				$cart_url = $woocommerce->cart->get_cart_url();
				wp_redirect($cart_url);
				exit;
			}

			if ( $paySuccess == true ) {
				update_post_meta( $order->id, '_nicepay_tid', $tid );

				if ( $PayMethod == 'VBANK' ) {
					$order->update_status( 'awaiting' );
					update_post_meta( $order->id, '_nicepay_bankname', $vbankBankName );
					update_post_meta( $order->id, '_nicepay_bankaccount', $vbankNum );
					update_post_meta( $order->id, '_nicepay_expiry_date', $vbankExpDate );

					$order->add_order_note( sprintf( __( 'Waiting for payment. Payment method: %s. Bank Name: %s. Bank Account: %s. NicePay TID: %s. Timestamp: %s.', 'wc-gateway-nicepay' ), $this->get_paymethod_txt( $PayMethod ), $vbankBankName, $vbankNum, $tid, $this->get_timestamp() ) );
				} else {
					$order->payment_complete();
					$order->add_order_note( sprintf( __( 'Payment is complete. Payment method: %s. NicePay TID: %s. Timestamp: %s.', 'wc-gateway-nicepay' ), $this->get_paymethod_txt( $PayMethod ), $tid, $this->get_timestamp() ) );
				}

				if ( version_compare( WOOCOMMERCE_VERSION, '2.1', '<' ) ) {
					$return = array(
						'result' 	=> 'success',
						'redirect'	=> add_query_arg( 'key', $order->order_key, add_query_arg( 'order', $order_id, get_permalink( woocommerce_get_page_id( 'thanks' ) ) ) )
						);
				} else {
					$return = array(
						'result' 	=> 'success',
						'redirect'	=> $this->get_return_url( $order )
						);
				}

				$woocommerce->cart->empty_cart();
				wp_redirect( $return[ 'redirect' ] );
				exit;
			} else {
				if ( $order->status == 'completed' ) {
					$order->add_order_note( sprintf( __( 'Payment request received but order is already completed. Timestamp: %s.', 'wc-gateway-nicepay' ), $this->get_timestamp() ) );
					$cart_url = $woocommerce->cart->get_cart_url();
					wp_redirect($cart_url);
					exit;
				} elseif ( $order->status == 'processing' ) {
					$order->add_order_note( sprintf( __( 'Payment request received but order is in processing. Timestamp: %s.', 'wc-gateway-nicepay' ), $this->get_timestamp() ) );
					$cart_url = $woocommerce->cart->get_cart_url();
					wp_redirect($cart_url);
					exit;
				} else {
					$order->update_status( 'failed', sprintf( __( 'Payment failed. Response message: %s. Timestamp: %s.', 'wc-gateway-nicepay' ), $resultMsg, $this->get_timestamp() ) );
					$cart_url = $woocommerce->cart->get_cart_url();
					wp_redirect($cart_url);
					exit;
				}
			}
		} else {
			wp_die( 'NicePay Payment Request Failure' );
		}
	}

	function process_mobile_return( $params ) {
		global $woocommerce;

		if ( ! empty( $params[ 'Moid' ] ) ) {
			$order_id = $this->get_orderid( $params[ 'Moid' ] );
			$order = new WC_Order( $order_id );

			if ( $order->status == 'pending' || $order->status == 'processing' || $order->status == 'awaiting' ) {
				if ( version_compare( WOOCOMMERCE_VERSION, '2.1', '<' ) ) {
					$return = array(
						'result' 	=> 'success',
						'redirect'	=> add_query_arg( 'key', $order->order_key, add_query_arg( 'order', $order_id, get_permalink( woocommerce_get_page_id( 'thanks' ) ) ) )
						);
				} else {
					$return = array(
						'result' 	=> 'success',
						'redirect'	=> $this->get_return_url( $order )
						);
				}

				$woocommerce->cart->empty_cart();
				wp_redirect( $return[ 'redirect' ] );
			} else {
				$cart_url = $woocommerce->cart->get_cart_url();
				wp_redirect( $cart_url );
			}
		}
	}

	function process_mobile_response( $params ) {
		global $woocommerce;

		if ( ! empty( $params[ 'Moid' ] ) ) {
			$order_id = $this->get_orderid( $params[ 'Moid' ] );
			$order = new WC_Order( $order_id );

			$tid				= $params[ 'TID' ];
			$amt				= $params[ 'Amt' ];
			$PayMethod			= $params[ 'PayMethod' ];
			$MallUserID			= $params[ 'MallUserID' ];
			$GoodsName			= $params[ 'GoodsName' ];
			$Moid				= $params[ 'Moid' ];
			$BuyerName			= $params[ 'BuyerName' ];
			$BuyerTel			= $params[ 'BuyerTel' ];
			$BuyerEmail			= $params[ 'BuyerEmail' ];
			$resultCode			= $params[ 'ResultCode' ];
			$resultMsg			= $params[ 'ResultMsg' ];
			$DstAddr			= $params[ 'DstAddr' ];
			$vbankBankName		= $params[ 'VbankBankName' ];
			$vbankNum			= $params[ 'VbankNum' ];
			$vbankExpDate		= $params[ 'VbankExpDate' ];

			$paySuccess = false;
			$result = 'FAIL';

			if ( $PayMethod == 'CARD' ) {
				if ( $resultCode == '3001' ) $paySuccess = true;
			} elseif ( $PayMethod == 'BANK' ) {
				if ( $resultCode == '4000') $paySuccess = true;
			} elseif ( $PayMethod == 'CELLPHONE' ) {
				if ( $resultCode == 'A000' ) $paySuccess = true;
			} elseif ( $PayMethod == 'VBANK' ) {
				if ( $resultCode == '4100' ) $paySuccess = true;
			}

			if ( (int)$amt != (int)$order->get_total() ) {
				$paySuccess = false;
				$order->update_status( 'on-hold', sprintf( __( 'Failed to verify integrity of payment. Timestamp: %s.', 'wc-gateway-nicepay' ), $this->get_timestamp() ) );
				echo $result;
				exit;
			}

			if ( $paySuccess == true ) {
				update_post_meta( $order->id, '_nicepay_tid', $tid );

				if ( $PayMethod == 'VBANK' ) {
					$order->update_status( 'awaiting' );
					update_post_meta( $order->id, '_nicepay_bankname', $vbankBankName );
					update_post_meta( $order->id, '_nicepay_bankaccount', $vbankNum );
					update_post_meta( $order->id, '_nicepay_expiry_date', $vbankExpDate );

					$order->add_order_note( sprintf( __( 'Waiting for payment. Payment method: %s. Bank Name: %s. Bank Account: %s. NicePay TID: %s. Timestamp: %s.', 'wc-gateway-nicepay' ), $this->get_paymethod_txt( $PayMethod ), $vbankBankName, $vbankNum, $tid, $this->get_timestamp() ) );
					$result = 'OK';
				} else {
					$order->payment_complete();
					$order->add_order_note( sprintf( __( 'Payment is complete. Payment method: %s. NicePay TID: %s. Timestamp: %s.', 'wc-gateway-nicepay' ), $this->get_paymethod_txt( $PayMethod ), $tid, $this->get_timestamp() ) );
					$result = 'OK';
				}
				echo $result;
				exit;
			} else {
				if ( $order->status == 'completed' ) {
					$order->add_order_note( sprintf( __( 'Payment request received but order is already completed. Timestamp: %s.', 'wc-gateway-nicepay' ), $this->get_timestamp() ) );
				} elseif ( $order->status == 'processing' ) {
					$order->add_order_note( sprintf( __( 'Payment request received but order is in processing. Timestamp: %s.', 'wc-gateway-nicepay' ), $this->get_timestamp() ) );
				} else {
					$order->update_status( 'failed', sprintf( __( 'Payment failed. Response message: %s. Timestamp: %s.', 'wc-gateway-nicepay' ), $resultMsg, $this->get_timestamp() ) );
				}
				echo $result;
				exit;
			}
			echo $result;
			exit;
		} else {
			wp_die( 'NicePay Mobile Payment Request Failure' );
		}
	}

	function process_cas_response( $params ) {
		global $woocommerce;

		$tid = 	$params[ 'TID' ];
		$moid = $params[ 'MOID' ];
		$resultCode = $params[ 'ResultCode' ];
		$resultMsg  = $params[ 'ResultMsg' ];

		if ( $resultCode == '4110' ) {
			$order_id = $this->get_orderid( $moid );
			$order = new WC_Order( $order_id );

			$order->update_status( 'pending' );

			$order->payment_complete();
			$order->add_order_note( sprintf( __( 'CAS notification received. Payment method: %s. NicePay TID: %s. Timestamp: %s.', 'wc-gateway-nicepay' ), $this->get_paymethod_txt( 'VBANK' ), $tid, $this->get_timestamp() ) );

			echo 'OK';
			exit;
		} else {
			echo 'FAIL';
			exit;
		}
	}

	function process_refund_request( $params ) {
		global $woocommerce;

		if ( ! empty( $params[ 'order_id' ] ) ) {
			if ( ! empty( $params[ 'TID' ] ) ) {

				$order_id = $this->get_orderid( $params[ 'order_id' ] );
				$order = new WC_Order( $order_id );

				if ( $order == null ) {
					$return = array(
						'result'	=> 'failure',
						'message'	=> __( 'Order does not exist. Refund failed.', 'wc-gateway-nicepay' )
					);
				} else {
					$this->id = get_post_meta( $order->id, '_payment_method', true );
					$this->init_settings();
					$settings = get_option( 'woocommerce_' . $this->id . '_settings' );
					$this->testmode = $settings[ 'testmode' ];
					$this->MerchantKey = $settings[ 'MerchantKey' ];
					$this->CancelKey = $settings[ 'CancelKey' ];
				}


				require_once dirname( __FILE__ ) . '/bin/lib/NicepayLite.php';

				$nicepay = new NicepayLite;

				$nicepay->m_NicepayHome = dirname( __FILE__ ) . '/bin/log';

				if ( $params[ 'customer-cancel' ] ) {
					$CancelMsg = __( 'Customer cancel', 'wc-gateway-nicepay' );
				} else {
					$CancelMsg = __( 'Administrator cancel', 'wc-gateway-nicepay' );
				}

				$nicepay->m_ssl = 'true';	

				$nicepay->m_ActionType			= 'CLO';
				$nicepay->m_CancelAmt			= $order->get_total();
				$nicepay->m_TID					= $params[ 'TID' ];
				$nicepay->m_CancelMsg			= $CancelMsg;
				$nicepay->m_PartialCancelCode	= '0';
				$nicepay->m_CancelPwd			= ( $this->testmode == 'yes' ) ? '123456' : $this->CancelKey;

				$nicepay->m_log				= ( $this->testmode=='yes' ) ? true : false;

				$nicepay->startAction();

				$resultCode		= $nicepay->m_ResultData[ 'ResultCode' ];
				$resultMsg		= iconv( 'EUC-KR', 'UTF-8', $nicepay->m_ResultData[ 'ResultMsg' ] );

				if ( $resultCode == '2001' || $resultCode == '2211' ) {
					update_post_meta( $order->id, '_nicepay_refund', 'yes' );

					$order->update_status( 'refunded' );

					if ( $params[ 'customer-cancel' ] ) {
						$order->add_order_note( sprintf( __( 'Order has been refunded by customer. Timestamp: %s.', 'wc-gateway-nicepay' ), $this->get_timestamp() ) );
					} else {
						$order->add_order_note( sprintf( __( 'Order has been refunded by administrator. Timestamp: %s.', 'wc-gateway-nicepay' ), $this->get_timestamp() ) );
					}

					$return = array(
						'result'	=> 'success',
						'message'	=> __( 'Order has been refunded.', 'wc-gateway-nicepay' )
					);
				} else {
					$order->add_order_note( sprintf( __( 'Refund request received but an error has occurred. Message: %s. Timestamp: %s.', 'wc-gateway-nicepay' ), $resultMsg, $this->get_timestamp() ) );

					$return = array(
						'result'	=> 'failure',
						'message'	=> sprintf( __( 'Refund request received but an error has occurred. Message: %s.', 'wc-gateway-nicepay' ), $resultMsg )
					);
				}
			} else {
				$return = array(
					'result'	=> 'failure',
					'message'	=> __( 'TID does not exist. Refund failed.', 'wc-gateway-nicepay' )
				);
			}
		} else {
			$return = array(
				'result'	=> 'failure',
				'message'	=> __( 'Order does not exist. Refund failed.', 'wc-gateway-nicepay' )
			);
		}

		if ( $params[ 'customer-cancel' ] ) {
			if ( $return[ 'result' ] == 'success' ) {
				wp_redirect( $params[ 'redirect' ] );
				exit;
			} else {
				wp_redirect( $params[ 'redirect' ] );
				exit;
			}
		} else {
			echo json_encode( $return );
			exit;
		}
	}

	function process_escrow_request( $params ) {
		global $woocommerce;

		if ( $params[ 'ReqType' ] != '03' ) {
			$params[ 'DeliveryCoNm' ] = "-";
			$params[ 'InvoiceNum' ] = "-";
		}

		if ( ! empty( $params[ 'DeliveryCoNm' ] ) ) {
			if ( ! empty( $params[ 'InvoiceNum' ] ) ) {
				if ( ! empty( $params[ 'order_id' ] ) ) {
					if ( ! empty( $params[ 'TID' ] ) ) {
						if ( ! empty( $params[ 'ReqType' ] ) ) {

							$order_id = $this->get_orderid( $params[ 'order_id' ] );
							$order = new WC_Order( $order_id );

							if ( $order == null ) {
								$return = array(
									'result'	=> 'failure',
									'message'	=> __( 'Order does not exist. Sending escrow information failed.', 'wc-gateway-nicepay' )
								);
							} else {
								$this->id = get_post_meta( $order->id, '_payment_method', true );
								$this->init_settings();
								$settings = get_option( 'woocommerce_' . $this->id . '_settings' );
								$this->testmode = $settings[ 'testmode' ];
								$this->MID = $settings[ 'MID' ];
								$this->MerchantKey = $settings[ 'MerchantKey' ];
								$this->ConfirmMail = $settings[ 'ConfirmMail' ];
							}

							require_once dirname( __FILE__ ) . '/bin/lib/NicepayLite.php';

							$nicepay = new NicepayLite;

							$nicepay->requestProcess();

							$nicepay->m_NicepayHome = dirname( __FILE__ ) . '/bin/log';

							$nicepay->m_MID = ( $this->testmode=='yes' ) ? 'nictest00m' : $this->MID;
							$nicepay->m_TID = $params[ 'TID' ];

							if ( $params[ 'ReqType' ] == '03' ) {
								$nicepay->m_DeliveryCoNm = $params[ 'DeliveryCoNm' ];
								$nicepay->m_InvoiceNum = $params[ 'InvoiceNum' ];
							}

							$BuyerAddr				= $order->shipping_address_1;
							$RegisterName			= $this->get_name_lang( $order->shipping_first_name, $order->shipping_last_name );
							$BuyerAuthNum			= $params[ 'BuyerAuthNum' ];

							$nicepay->m_BuyerAddr = $BuyerAddr;
							$nicepay->m_RegisterName = $RegisterName;
							$nicepay->m_BuyerAuthNum = $BuyerAuthNum;
							$nicepay->m_PayMethod = 'ESCROW';
							$nicepay->m_ReqType = $params[ 'ReqType' ];
							$nicepay->m_ConfirmMail = ( $this->ConfirmMail == 'yes' ) ? "1" : "0";
							$nicepay->m_ActionType = 'PYO';

							$nicepay->m_LicenseKey		= ( $this->testmode=='yes' ) ? '33F49GnCMS1mFYlGXisbUDzVf2ATWCl9k3R++d5hDd3Frmuos/XLx8XhXpe+LDYAbpGKZYSwtlyyLOtS/8aD7A==' : $this->MerchantKey;

							$nicepay->m_log				= ( $this->testmode=='yes' ) ? true : false;

							$nicepay->startAction();

							$resultCode		= $nicepay->m_ResultData[ 'ResultCode' ];
							$resultMsg		= iconv( 'EUC-KR', 'UTF-8', $nicepay->m_ResultData[ 'ResultMsg' ] );

							$escrowSuccess = false;

							if ( $ReqType == '01' ) {
								if ( $resultCode == 'D000' ) $escrowSuccess = true;
							} else if ( $ReqType == '02' ) {
								if ( $resultCode == 'E000' ) $escrowSuccess = true;
							} else if ( $ReqType == '03' ) {
								if ( $resultCode == 'C000' ) $escrowSuccess = true;
							}

							if ( $escrowSuccess ) {
								if ( $ReqType == '01' ) {
									update_post_meta( $order->id, '_nicepay_escw', 'yes' );

									$order->update_status( 'completed', sprintf( __( 'Escrow request has been successfully sent. Timestamp: %s.', 'wc-gateway-nicepay' ), $this->get_timestamp() ) );

									$return = array(
										'result'	=> 'success',
										'message'	=> __( 'Escrow request has been successfully sent.', 'wc-gateway-nicepay' )
									);
								} else if ( $ReqType == '02' ) {
									update_post_meta( $order->id, '_nicepay_escw', 'yes' );

									$order->update_status( 'decline', sprintf( __( 'Escrow refund request has been successfully sent. Order has been refunded. Timestamp: %s.', 'wc-gateway-nicepay' ), $this->get_timestamp() ) );

									$return = array(
										'result'	=> 'success',
										'message'	=> __( 'Escrow request has been successfully sent.', 'wc-gateway-nicepay' )
									);

								} else if ( $ReqType == '03' ) {
									update_post_meta( $order->id, '_nicepay_escw', 'yes' );

									$order->update_status( 'on-delivery', sprintf( __( 'Escrow request has been successfully sent. Timestamp: %s.', 'wc-gateway-nicepay' ), $this->get_timestamp() ) );

									$return = array(
										'result'	=> 'success',
										'message'	=> __( 'Escrow request has been successfully sent.', 'wc-gateway-nicepay' )
									);
								}
							} else {
								$order->add_order_note( sprintf( __( 'Escrow request received but an error has occurred. Message: %s. Timestamp: %s.', 'wc-gateway-nicepay' ), $resultMsg, $this->get_timestamp() ) );

								$return = array(
									'result'	=> 'failure',
									'message'	=> sprintf( __( 'Escrow request received but an error has occurred. Message: %s.', 'wc-gateway-nicepay' ), $resultMsg )
								);
							}
						} else {
							$return = array(
								'result'	=> 'failure',
								'message'	=> __( 'Request type does not exist. Escrow request failed.', 'wc-gateway-nicepay' )
							);
						}
					} else {
						$return = array(
							'result'	=> 'failure',
							'message'	=> __( 'TID does not exist. Escrow request failed.', 'wc-gateway-nicepay' )
						);
					}
				} else {
					$return = array(
						'result'	=> 'failure',
						'message'	=> __( 'Order does not exist. Escrow request failed.', 'wc-gateway-nicepay' )
					);
				}
			} else {
				$return = array(
					'result'	=> 'failure',
					'message'	=> __( 'Tracking number does not exist. Escrow request failed.', 'wc-gateway-nicepay' )
				);
			}
		} else {
			$return = array(
				'result'	=> 'failure',
				'message'	=> __( 'Delivery company name does not exist. Escrow request failed.', 'wc-gateway-nicepay' )
			);
		}

		if ( $params[ 'customer-confirm' ] ) {
			if ( $resultCode == 'D007' || $resultCode == 'D009' || $resultCode == 'E007' || $resultCode == 'E009' ) {
				$message = __( 'The authorization number you have entered is incorrect.', 'wc-gateway-nicepay' );
				wc_add_notice( $message, $notice_type = 'error' );
			} else {
				if ( $return[ 'result' ] == 'failure' ) {
					$message = __( 'There was a problem while processing your request. Please contact the administrator.', 'wc-gateway-nicepay' );
					wc_add_notice( $message, $notice_type = 'error' );
				}
			}

			if ( $return[ 'result' ] == 'success' ) {
				wp_redirect( $params[ 'redirect' ] );
				exit;
			} else {
				wp_redirect( $params[ 'redirect' ] );
				exit;
			}
		} else {
			echo json_encode( $return );
			exit;
		}
	}

	function get_name_lang( $first_name, $last_name ) {
		if ( get_locale() == 'ko_KR' ) {
			return $last_name.$first_name;
		} else {
			return $first_name.' '.$last_name;
		}
	}

	function get_orderid( $oid ) {
		$order_id = $oid;
		return $order_id;
	}

	function check_mobile() {
		$agent = $_SERVER[ 'HTTP_USER_AGENT' ];
		
		if( stripos ( $agent, 'iPod' ) || stripos( $agent, 'iPhone' ) || stripos( $agent, 'iPad' ) || stripos( $agent, 'Android' ) ) {
			return true;
		} else {
			return false;
		}
	}

	function get_paymethod_txt( $pay_type ) {
		switch ( $pay_type ) {
			case 'CARD' :
				return __( 'Credit Card', 'wc-gateway-nicepay' );
			break;
			case 'BANK' :
				return __( 'Account Transfer', 'wc-gateway-nicepay' );
			break;
			case 'VBANK' :
				return __( 'Virtual Account', 'wc-gateway-nicepay' );
			break;
			case 'CELLPHONE' :
				return __( 'Mobile Payment', 'wc-gateway-nicepay' );
			break;
			default :
				return '';
		}
	}

	function get_timestamp() {
		global $wpdb;
		$query = ' SELECT DATE_FORMAT( NOW( ) , \'%Y%m%d%H%i%s\' ) AS TIMESTAMP ; ';
		$row = $wpdb->get_results( $query );
		$_time = date( 'YmdHis', strtotime( $row[0]->TIMESTAMP ) );
		return $_time;
	}

	function get_expirytime( $interval ) {
		global $wpdb;

		if ( $interval == '' ) {
			$query = ' SELECT DATE_FORMAT( NOW( ) , \'%Y%m%d%H%i%s\' ) AS TIMESTAMP ; ';
			$row = $wpdb->get_results( $query );
			$_time = date( 'YmdHis', strtotime( $row[0]->TIMESTAMP ) );
		} else {
			$query = ' SELECT DATE_FORMAT( DATE_ADD( NOW( ) , INTERVAL ' . $interval . ' DAY ) , \'%Y-%m-%d\' ) AS TODAY_DATE ; ';
			$row = $wpdb->get_results( $query );
			$_time = date( 'YmdHis', strtotime( $row[0]->TODAY_DATE ) );
		}

		return $_time;
	}

	function get_client_ip() {
		if ( ! empty( $_SERVER[ 'HTTP_CLIENT_IP' ] ) ) {
			$ip = $_SERVER[ 'HTTP_CLIENT_IP' ];
		} elseif ( ! empty( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ) ) {
			$ip = $_SERVER[ 'HTTP_X_FORWARDED_FOR' ];
		} else {
			$ip = $_SERVER[ 'REMOTE_ADDR' ];
		}
		return $ip;
	}

	function get_mall_ip() {
		return $_SERVER[ 'SERVER_ADDR' ];
	}

	function get_hashdata( $MerchantKey, $MID, $Amt, $arg ) {
		$date = $this->get_timestamp();

		if ( $arg == 'date' ) {
			return $date;
		} elseif ( $arg == 'encrypt' ) {
			return base64_encode( md5( $date . $MID . $Amt . $MerchantKey ) );
		} else {
			return "";
		}
	}

	function get_status_array() {
		$order_statuses = wc_get_order_statuses();

		return $order_statuses;
	}

	function get_currency_str( $currency ) {
		$i = 0;
		foreach ( $currency as $key => $value ) {
			$currency_str .= ( ( $i > 0 ) ? ", " : "" ) . $value;
			$i++;
		}

		return $currency_str;
	}

	function get_chrome_version() { 
		$u_agent = $_SERVER['HTTP_USER_AGENT']; 
		if ( preg_match( '/Chrome/i', $u_agent) ) { 
			$bname		= 'Google Chrome'; 
			$ub			= 'Chrome';
			$known		= array( 'Version', $ub, 'other' );
			$pattern	= '#(?<browser>' . join( '|', $known ) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';

			if ( ! preg_match_all( $pattern, $u_agent, $matches ) ) {
			}

			$version	= $matches[ 'version' ][0];

			$version	= explode( '.', $version );

			$version	= $version[0];
		}

		return $version;
	}

	function for_translation_purposes() {
		$translation_array = array(
			__( 'NicePay Credit Card', 'wc-gateway-nicepay' ),
			__( 'NicePay Mobile Payment', 'wc-gateway-nicepay' ),
			__( 'NicePay Account Transfer', 'wc-gateway-nicepay' ),
			__( 'NicePay Virtual Account', 'wc-gateway-nicepay' ),
			__( 'Credit Card - Powered by Planet8', 'wc-gateway-nicepay' ),
			__( 'Mobile Payment - Powered by Planet8', 'wc-gateway-nicepay' ),
			__( 'Account Transfer - Powered by Planet8', 'wc-gateway-nicepay' ),
			__( 'Virtual Account - Powered by Planet8', 'wc-gateway-nicepay' ),
			__( 'Payment via credit card - Powered by Planet8', 'wc-gateway-nicepay' ),
			__( 'Payment via mobile phone - Powered by Planet8', 'wc-gateway-nicepay' ),
			__( 'Payment via real time account transfer - Powered by Planet8', 'wc-gateway-nicepay' ),
			__( 'Payment via virtual account transfer - Powered by Planet8', 'wc-gateway-nicepay' ),
		);
	}
}

require_once dirname( __FILE__ ) . '/includes/card.php';
require_once dirname( __FILE__ ) . '/includes/transfer.php';
require_once dirname( __FILE__ ) . '/includes/virtual.php';
require_once dirname( __FILE__ ) . '/includes/mobile.php';
}
?>
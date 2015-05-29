<?php


if ( !defined( 'ABSPATH' ) ) exit;


////////////////////////////// Mobile Payment


class WC_Gateway_NicePay_Mobile extends WC_Gateway_NicePay{

	public $method 					= 'CELLPHONE';

	public $id	 					= 'nicepay_mobile';

	public $method_title 			= 'NicePay Mobile Payment';

	public $title_default 			= 'Mobile Payment - Powered by Planet8';

	public $desc_default  			= 'Payment via mobile phone - Powered by Planet8';

	public $require 				= array( 'CELLPHONE' );

	public $allowed_currency		= array( 'KRW' );

	public $default_checkout_img	= 'mobile';
}

/**
* Add the Gateway to WooCommerce
**/
function woocommerce_add_NicePay_Mobile( $methods ) {
	$methods[] = 'WC_Gateway_NicePay_Mobile';
	return $methods;
}

add_filter( 'woocommerce_payment_gateways', 'woocommerce_add_NicePay_Mobile' );

?>
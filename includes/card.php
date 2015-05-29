<?php


if ( !defined( 'ABSPATH' ) ) exit;


////////////////////////////// Credit Card


class WC_Gateway_NicePay_Credit extends WC_Gateway_NicePay{

	public $method 					= 'CARD';

	public $id	 					= 'nicepay_credit';

	public $method_title 			= 'NicePay Credit Card';

	public $title_default 			= 'Credit Card - Powered by Planet8';

	public $desc_default  			= 'Payment via credit card - Powered by Planet8';

	public $require 				= array( 'CARD' );

	public $allowed_currency		= array( 'KRW' );

	public $default_checkout_img	= 'card';
}

/**
* Add the Gateway to WooCommerce
**/
function woocommerce_add_NicePay_Credit( $methods ) {
	$methods[] = 'WC_Gateway_NicePay_Credit';
	return $methods;
}

add_filter( 'woocommerce_payment_gateways', 'woocommerce_add_NicePay_Credit' );
?>
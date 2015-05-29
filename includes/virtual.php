<?php


if ( !defined( 'ABSPATH' ) ) exit;


////////////////////////////// Virtual Account


class WC_Gateway_NicePay_Virtual extends WC_Gateway_NicePay{

	public $method 					= 'VBANK';

	public $id	 					= 'nicepay_virtual';

	public $method_title 			= 'NicePay Virtual Account';

	public $title_default 			= 'Virtual Account - Powered by Planet8';

	public $desc_default  			= 'Payment via virtual account transfer - Powered by Planet8';

	public $require 				= array( 'VBANK' );

	public $allowed_currency		= array( 'KRW' );

	public $default_checkout_img	= 'bank';
}

/**
* Add the Gateway to WooCommerce
**/
function woocommerce_add_NicePay_Virtual( $methods ) {
	$methods[] = 'WC_Gateway_NicePay_Virtual';
	return $methods;
}

add_filter( 'woocommerce_payment_gateways', 'woocommerce_add_NicePay_Virtual' );

?>
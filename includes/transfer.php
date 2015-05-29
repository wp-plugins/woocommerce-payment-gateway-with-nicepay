<?php


if ( !defined( 'ABSPATH' ) ) exit;


////////////////////////////// Account Transfer


class WC_Gateway_NicePay_Account extends WC_Gateway_NicePay{

	public $method 					= 'BANK';

	public $id	 					= 'nicepay_account';

	public $method_title 			= 'NicePay Account Transfer';

	public $title_default 			= 'Account Transfer - Powered by Planet8';

	public $desc_default  			= 'Payment via real time account transfer - Powered by Planet8';

	public $require 				= array( 'BANK' );

	public $allowed_currency		= array( 'KRW' );

	public $default_checkout_img	= 'bank';
}

/**
* Add the Gateway to WooCommerce
**/
function woocommerce_add_NicePay_Account( $methods ) {
	$methods[] = 'WC_Gateway_NicePay_Account';
	return $methods;
}

add_filter( 'woocommerce_payment_gateways', 'woocommerce_add_NicePay_Account' );

?>
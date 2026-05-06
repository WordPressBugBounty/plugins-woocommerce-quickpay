<?php

use QuickpayPSP\Admin\ViewRenderer;
use QuickpayPSP\Plugin;
use QuickpayPSP\QuickPay\Api\ApiClientFactory;
use QuickpayPSP\QuickPay\Api\Exceptions\QuickPayApiException;
use QuickpayPSP\QuickPay\Api\PaymentClient;
use QuickpayPSP\QuickPay\Api\SubscriptionClient;
use QuickpayPSP\WooCommerce\Subscriptions\SubscriptionsFacade;
use QuickpayPSP\WooCommerce\Support\OrderPaymentsMeta;
use QuickpayPSP\WooCommerce\Support\OrderUtils;
use QuickpayPSP\WooCommerce\Support\WooCommerceContext;

if ( ! function_exists( 'woocommerce_quickpay_get_template' ) ) {
	/**
	 * Convenience wrapper based on the wc_get_template method
	 *
	 * @param        $template_name
	 * @param array $args
	 * @param string $template_path
	 * @param string $default_path
	 */
	function woocommerce_quickpay_get_template( $template_name, $args = [] ) {
		$template_path = 'woocommerce-quickpay/';
		$default_path  = QUICKPAY_PLUGIN_PATH . 'templates/';

		wc_get_template( $template_name, $args, $template_path, $default_path );
	}
}

function woocommerce_quickpay_get_view( $path, $args = [] ) {
	$view_renderer = Plugin::services()->get_as( ViewRenderer::class, 'wordpress/view' );
	$view_renderer->render( $path, $args );
}

/**
 * Returns the proper transaction instance type
 *
 * @param mixed $order
 *
 * @return PaymentClient|SubscriptionClient
 */
function woocommerce_quickpay_get_transaction_instance_by_order( $order ) {

	$order = woocommerce_quickpay_get_order( $order );

	// Instantiate a new transaction
	$services        = Plugin::services();
	$factory         = $services->get_as( ApiClientFactory::class, 'quickpay/api/factory' );
	$api_transaction = $factory->payment();

	// If the order is a subscription or an attempt of updating the payment method
	if ( OrderUtils::contains_subscription( $order ) || WooCommerceContext::is_request_to_change_payment() ) {
		$subscriptions_facade = Plugin::services()->get_as( SubscriptionsFacade::class, 'woocommerce/subscriptions' );
		if ( $subscriptions_facade->cart_contains_switches() ) {
			$subscription   = $subscriptions_facade->get_last_subscription_for_order( $order );
			$transaction_id = $subscription ? OrderUtils::get_transaction_id( $subscription ) : null;

			if ( ! $transaction_id && $order->needs_payment() ) {
				// Instantiate a subscription transaction instead of a payment transaction
				$api_transaction = $factory->subscription();
			}
		} else {
			// Instantiate a subscription transaction instead of a payment transaction
			$api_transaction = $factory->subscription();
		}
	}

	return $api_transaction;
}

/**
 * Creates a new transaction based on the order and persists the transaction ID on the object.
 *
 * @param mixed $order
 *
 * @return int
 * @throws QuickPayApiException
 */
function woocommerce_quickpay_create_order_transaction( $order ): int {
	$order = woocommerce_quickpay_get_order( $order );

	$transaction = woocommerce_quickpay_get_transaction_instance_by_order( $order );
	$result      = $transaction->create( $order );

	OrderPaymentsMeta::set_payment_id( $order, $result->id );

	return (int) $result->id;
}

/**
 * Returns an existing payment link if available or creates a new one.
 *
 * @param WC_Order|int $order
 * @param bool $force_update
 *
 * @return string|null
 * @throws Exception
 */
function woocommerce_quickpay_create_payment_link( $order, bool $force_update = true ): ?string {
	$order = woocommerce_quickpay_get_order( $order );

	/** @var \QuickpayPSP\Application\Services\PaymentLinkService $service */
	$service = Plugin::services()->get( \QuickpayPSP\Application\Services\PaymentLinkService::class );

	return $service->create_payment_link( $order, $force_update );
}

/**
 * Returns a WC_Order object.
 *
 * @param mixed $order
 *
 * @return WC_Order
 */
function woocommerce_quickpay_get_order( $order ): ?WC_Order {

	if ( ! is_object( $order ) ) {
		return wc_get_order( $order ) ?: null;
	}

	if ( $order instanceof WC_Order ) {
		return $order;
	}

	if ( $order instanceof WP_Post ) {
		return wc_get_order( $order->ID ) ?: null;
	}

	return $order;
}

/**
 * Returns a WC_Subscription object.
 *
 * @param mixed $subscription
 *
 * @return WC_Subscription|null
 */
function woocommerce_quickpay_get_subscription( $subscription ) {
	if ( ! function_exists( 'wcs_get_subscription' ) ) {
		return null;
	}

	if ( ! is_object( $subscription ) ) {
		return wcs_get_subscription( $subscription ) ?: null;
	}

	if ( $subscription instanceof WP_Post ) {
		return wcs_get_subscription( $subscription->ID ) ?: null;
	}

	return $subscription;
}

/**
 * Returns the locale used in the payment window
 * @return string
 */
function woocommerce_quickpay_get_language(): string {
	[ $language ] = explode( '_', get_locale() );

	return apply_filters( 'woocommerce_quickpay_language', $language );
}

// Load notices functions
require_once __DIR__ . '/notices.php';

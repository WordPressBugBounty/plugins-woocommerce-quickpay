<?php

namespace QuickpayPSP\Admin\Updates;

use QuickpayPSP\QuickPay\Api\ApiClientFactory;
use QuickpayPSP\QuickPay\Api\Exceptions\QuickPayApiException;
use QuickpayPSP\WooCommerce\Logging\WooCommerceLogger;
use QuickpayPSP\WooCommerce\Support\OrderPaymentsUtils;
use QuickpayPSP\WooCommerce\Support\OrderUtils;
use QuickpayPSP\WooCommerce\Support\WooCommerceContext;
use WC_Data_Exception;

class Update46 {

	private ApiClientFactory $api_client_factory;
	private WooCommerceLogger $logger;

	public function __construct( ApiClientFactory $api_client_factory, WooCommerceLogger $logger ) {
		$this->api_client_factory = $api_client_factory;
		$this->logger             = $logger;
	}

	public function __invoke() {
		// Ignore user aborts and allow the script to run forever if supported
		if ( function_exists( 'ignore_user_abort' ) ) {
			@ignore_user_abort( true );
		}
		if ( function_exists( 'set_time_limit' ) ) {
			@set_time_limit( 0 );
		}

		global $wpdb;

  if ( WooCommerceContext::is_hpos_enabled() ) {
			$subscriptions = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wc_orders p WHERE p.type = 'shop_subscription' AND p.status NOT IN ('draft', 'trash') AND NOT EXISTS(SELECT 1 FROM {$wpdb->prefix}wc_orders_meta pm WHERE p.id=pm.order_id AND pm.meta_key IN ('_transaction_id', 'TRANSACTION_ID'))", OBJECT );
		} else {
			$subscriptions = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} p WHERE p.post_type = 'shop_subscription' AND p.post_status NOT IN ('draft', 'trash') AND NOT EXISTS(SELECT 1 FROM {$wpdb->postmeta} pm WHERE p.ID=pm.post_id AND pm.meta_key IN ('_transaction_id', 'TRANSACTION_ID'))", OBJECT );
		}

		if ( empty( $subscriptions ) ) {
			return;
		}

		foreach ( $subscriptions as $subscription_post ) {
			$subscription = wcs_get_subscription( $subscription_post );
			if ( ! $subscription ) {
				continue;
			}

			// Create order object
			$order          = wc_get_order( $subscription->get_parent_id() );
			$transaction_id = $order ? OrderUtils::get_transaction_id( $order ) : null;

			if ( $order && ! empty( $transaction_id ) && OrderPaymentsUtils::is_order_using_quickpay( $order ) ) {
				$subscription_client = $this->api_client_factory->subscription();

				try {
					// Check if the transaction ID is actually a transaction of type subscription. If not, an exception will be thrown.
					$subscription_client->get( $transaction_id );

					// Set the transaction ID on the parent order
					$subscription->set_transaction_id( $transaction_id );
					$subscription->save();

					$order->delete_meta_data( '_transaction_id' );
					$order->delete_meta_data( 'TRANSACTION_ID' );
					$order->save_meta_data();

					$this->logger->info( sprintf( 'Migrated transaction (%1$s) from parent order ID: %2$d to subscription order ID: %3$d', $transaction_id, $order->get_id(), $subscription->get_id() ) );
				} catch ( WC_Data_Exception | QuickPayApiException $e ) {
					$this->logger->info( sprintf( 'Failed migration of transaction (%1$s) from parent order ID: %2$d to subscription order ID: %3$d. Error: %4$s', $transaction_id, $order->get_id(), $subscription->get_id(), $e->getMessage() ) );
				}
			}
		}
	}
}

<?php
declare(strict_types=1);
namespace QuickpayPSP\WooCommerce\Registrars;

use QuickpayPSP\WooCommerce\Controllers\OrderController;
use QuickpayPSP\WooCommerce\Controllers\SubscriptionEarlyRenewalsController;
use QuickpayPSP\WooCommerce\Controllers\SubscriptionChangePaymentMethodController;
use QuickpayPSP\WooCommerce\Gateways\GatewayRegistry;

class SubscriptionsRegistrar {
	private SubscriptionEarlyRenewalsController $controller;
	private SubscriptionChangePaymentMethodController $payment_method_controller;
	private OrderController $order_controller;
	private GatewayRegistry $gateway_registry;

	public function __construct(
		SubscriptionEarlyRenewalsController $controller,
		SubscriptionChangePaymentMethodController $payment_method_controller,
		OrderController $order_controller,
		GatewayRegistry $gateway_registry
	) {
		$this->controller                = $controller;
		$this->payment_method_controller = $payment_method_controller;
		$this->order_controller          = $order_controller;
		$this->gateway_registry          = $gateway_registry;
	}

	public function register(): void {
		foreach ( $this->gateway_registry->ids() as $gateway_id ) {
			// Register scheduled renewal payment handler for each QuickPay gateway.
			add_action(
				'woocommerce_scheduled_subscription_payment_' . $gateway_id,
				[ $this->order_controller, 'scheduled_subscription_payment' ],
				10,
				2
			);

			// Subscription cancellation — cancel transaction when subscription is cancelled.
			add_action(
				'woocommerce_subscription_cancelled_' . $gateway_id,
				[ $this->order_controller, 'subscription_cancellation' ]
			);

			// Track payment method changes to QuickPay.
			add_action(
				'woocommerce_subscription_payment_method_updated_to_' . $gateway_id,
				[ $this->order_controller, 'on_subscription_payment_method_updated' ],
				10,
				2
			);

			// Validate payment meta when admin manually changes subscription payment meta.
			add_action(
				'woocommerce_subscription_validate_payment_meta_' . $gateway_id,
				[ $this->order_controller, 'validate_subscription_payment_meta' ],
				10,
				2
			);
		}

		// Remove meta keys that should not be copied to renewal orders.
		add_filter( 'wc_subscriptions_renewal_order_data', [ $this->order_controller, 'remove_renewal_meta_data' ], 10 );

		// Expose payment meta for manual admin changes.
		add_filter( 'woocommerce_subscription_payment_meta', [ $this->order_controller, 'subscription_payment_meta' ], 10, 2 );

		add_action(
			'woocommerce_quickpay_scheduled_subscription_payment_after',
			[ $this->controller, 'maybe_check_payment_status' ],
			10,
			3
		);

		add_filter(
			'woocommerce_gateway_description',
			[ $this->payment_method_controller, 'maybe_apply_description_notice' ],
			10,
			2
		);
	}
}

<?php

namespace QuickpayPSP\WooCommerce\Controllers;

use QuickpayPSP\Support\Config\GatewaySettingsProvider;
use QuickpayPSP\WooCommerce\Subscriptions\SubscriptionsFacade;

/**
 * Controller for subscription-related payment method changes.
 */
final class SubscriptionChangePaymentMethodController {

	private GatewaySettingsProvider $settings;
	private SubscriptionsFacade $subscriptions_facade;

	public function __construct( GatewaySettingsProvider $settings, SubscriptionsFacade $subscriptions_facade ) {
		$this->settings             = $settings;
		$this->subscriptions_facade = $subscriptions_facade;
	}

	/**
	 * Applies a text description in the payment fields area on checkout when changing payment gateway.
	 *
	 * @param string|null $description
	 * @param string $gateway_id
	 *
	 * @return string|null
	 */
	public function maybe_apply_description_notice( ?string $description, string $gateway_id ): ?string {
		if ( $gateway_id === 'quickpay' && is_checkout() && $this->should_show_notice() ) {
			$description .= sprintf(
				'<p><strong>%s</strong> %s</p>',
				esc_html__( 'NB:', 'woocommerce-quickpay' ),
				esc_html__( 'This will pay your order and update the credit card on your subscription for future payments.', 'woocommerce-quickpay' )
			);
		}

		return $description;
	}

	/**
	 * @return bool
	 */
	private function should_show_notice(): bool {
		$enabled = wc_string_to_bool( $this->settings->get( 'subscription_update_card_on_manual_renewal_payment' ) );

		return $enabled && $this->subscriptions_facade->cart_contains_renewal();
	}
}

<?php

namespace QuickpayPSP\WooCommerce\Gateways;

use QuickpayPSP\WooCommerce\Settings\GatewaySettingsSchema;

class VippsMobilePaySubscriptionsGateway extends BaseGateway {

	public const ID = 'mobilepay-subscriptions';

	public function __construct() {
		$this->method_title = 'Quickpay - Vipps MobilePay Subscriptions';

		$this->supports_products();
		$this->supports_subscriptions();

		parent::__construct();
	}

	public function init_form_fields(): void {
		$this->form_fields = array_merge(GatewaySettingsSchema::base_gateway_fields( $this->method_title ), [
			[
				'type'  => 'title',
				'title' => 'Checkout'
			],
			'checkout_instant_activation'         => [
				'title'       => esc_html__( 'Activate subscriptions immediately.', 'woocommerce-quickpay' ),
				'type'        => 'checkbox',
				'label'       => esc_html__( 'Enable', 'woocommerce-quickpay' ),
				'default'     => 'no',
				'description' => esc_html__( 'Activates the subscription after the customer authorizes an agreement. <strong>Not suitable for membership pages selling virtual products</strong> as the first payment might take up to 48 hours to either succeed or fail. Read more <a href="https://learn.quickpay.net/helpdesk/da/articles/payment-methods/mobilepay-subscriptions/#oprettelse-af-abonnement" target="_blank">here</a>', 'woocommerce-quickpay' ),
			],
			'checkout_prefill_phone_number'       => [
				'title'       => esc_html__( 'Pre-fill phone number', 'woocommerce-quickpay' ),
				'type'        => 'checkbox',
				'label'       => esc_html__( 'Enable', 'woocommerce-quickpay' ),
				'default'     => 'yes',
				'description' => esc_html__( 'When enabled the customer\'s phone number will be used on the MobilePay payment page.', 'woocommerce-quickpay' ),
			],
			[
				'type'  => 'title',
				'title' => 'Renewals'
			],
			'renewal_keep_active'                 => [
				'title'       => esc_html__( 'Keep subscription active', 'woocommerce-quickpay' ),
				'type'        => 'checkbox',
				'label'       => esc_html__( 'Enable', 'woocommerce-quickpay' ),
				'default'     => 'no',
				'description' => esc_html__( 'When enabled the subscription will automatically be activated after scheduling the renewal payment. If the payment fails the subscription will be put on-hold.', 'woocommerce-quickpay' ),
			],
			[
				'type'  => 'title',
				'title' => esc_html__( 'Agreements', 'woocommerce-quickpay' )
			],
			'mps_transaction_cancellation_status' => [
				'title'             => esc_html__( 'Cancelled agreements status', 'woocommerce-quickpay' ),
				'type'              => 'select',
				'class'             => 'wc-enhanced-select',
				'css'               => 'width: 450px;',
				'default'           => 'none',
				'description'       => esc_html__( 'Changes subscription status in case of cancelled payment agreement from either the QuickPay manager or the customer\'s MobilePay app', 'woocommerce-quickpay' ),
				'options'           => $this->get_mps_cancel_agreement_status_options(),
				'custom_attributes' => [
					'data-placeholder' => esc_html__( 'Select status', 'woocommerce-quickpay' )
				]
			],
		]);
	}

	private function get_mps_cancel_agreement_status_options() {
		return apply_filters( 'woocommerce_quickpay_mps_cancel_agreement_status_options', [
			'none'      => esc_html__( 'Do nothing', 'woocommerce-quickpay' ),
			'on-hold'   => wc_get_order_status_name( 'on-hold' ),
			'cancelled' => wc_get_order_status_name( 'cancelled' ),
		], $this );
	}

	public function default_card_type_lock(): string {
		return 'mobilepay-subscriptions';
	}

	protected function icon_slugs(): array {
		return [ 'mobilepay' ];
	}
}

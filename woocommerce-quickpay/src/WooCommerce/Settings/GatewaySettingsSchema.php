<?php

namespace QuickpayPSP\WooCommerce\Settings;

use QuickpayPSP\Support\Dependencies;
use QuickpayPSP\WooCommerce\Gateways\GatewayIcons;

class GatewaySettingsSchema {
	public function fields(): array {
		$fields = [
			'enabled' => [
				'title'   => esc_html__( 'Enable', 'woocommerce-quickpay' ),
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Enable Quickpay Payment', 'woocommerce-quickpay' ),
				'default' => 'yes'
			],
			'_Account_setup'               => [
				'type'  => 'title',
				'title' => esc_html__( 'API - Integration', 'woocommerce-quickpay' ),
			],
			'quickpay_apikey'              => [
				'title'       => esc_html__( 'Api User key', 'woocommerce-quickpay' ) . $this->get_required_symbol(),
				'type'        => 'text',
				'description' => esc_html__( 'Your API User\'s key. Create a separate API user in the "Users" tab inside the Quickpay manager.', 'woocommerce-quickpay' ),
				'desc_tip'    => true,
			],
			'quickpay_privatekey'          => [
				'title'       => esc_html__( 'Private key', 'woocommerce-quickpay' ) . $this->get_required_symbol(),
				'type'        => 'text',
				'description' => esc_html__( 'Your agreement private key. Found in the "Integration" tab inside the Quickpay manager.', 'woocommerce-quickpay' ),
				'desc_tip'    => true,
			],
			'_Autocapture'                 => [
				'type'  => 'title',
				'title' => esc_html__( 'Autocapture settings', 'woocommerce-quickpay' )
			],
			'quickpay_autocapture'         => [
				'title'       => esc_html__( 'Physical products (default)', 'woocommerce-quickpay' ),
				'type'        => 'checkbox',
				'label'       => esc_html__( 'Enable', 'woocommerce-quickpay' ),
				'description' => esc_html__( 'Automatically capture payments on physical products.', 'woocommerce-quickpay' ),
				'default'     => 'no',
				'desc_tip'    => false,
			],
			'quickpay_autocapture_virtual' => [
				'title'       => esc_html__( 'Virtual products', 'woocommerce-quickpay' ),
				'type'        => 'checkbox',
				'label'       => esc_html__( 'Enable', 'woocommerce-quickpay' ),
				'description' => esc_html__( 'Automatically capture payments on virtual products. If the order contains both physical and virtual products, this setting will be overwritten by the default setting above.', 'woocommerce-quickpay' ),
				'default'     => 'no',
				'desc_tip'    => false,
			],
			'_caching'                     => [
				'type'  => 'title',
				'title' => esc_html__( 'Transaction Cache', 'woocommerce-quickpay' )
			],
			'quickpay_caching_enabled'     => [
				'title'       => esc_html__( 'Enable Caching', 'woocommerce-quickpay' ),
				'type'        => 'checkbox',
				'description' => wp_kses( __( 'Caches transaction data to improve application and web-server performance. <strong>Recommended.</strong>', 'woocommerce-quickpay' ), [ 'strong' => [] ] ),
				'default'     => 'yes',
				'desc_tip'    => false,
			],
			'quickpay_caching_expiration'  => [
				'title'       => esc_html__( 'Cache Expiration', 'woocommerce-quickpay' ),
				'label'       => esc_html__( 'Cache Expiration', 'woocommerce-quickpay' ),
				'type'        => 'number',
				'description' => wp_kses_post( '<strong>Time in seconds</strong> for how long a transaction should be cached. <strong>Default: 604800 (7 days).</strong>', 'woocommerce-quickpay' ),
				'default'     => 7 * DAY_IN_SECONDS,
				'desc_tip'    => false,
			],

			'_Extra_gateway_settings' => [
				'type'  => 'title',
				'title' => esc_html__( 'Extra gateway settings', 'woocommerce-quickpay' )
			],
			'quickpay_cardtypelock'   => [
				'title'       => esc_html__( 'Payment methods', 'woocommerce-quickpay' ),
				'type'        => 'text',
				'description' => wp_kses_post( 'Default: creditcard. Type in the cards you wish to accept (comma separated). See the valid payment types here: <b>https://learn.quickpay.net/tech-talk/appendixes/payment-methods/#payment-methods</b>', 'woocommerce-quickpay' ),
				'default'     => 'creditcard',
			],
			'quickpay_branding_id'    => [
				'title'       => esc_html__( 'Branding ID', 'woocommerce-quickpay' ),
				'type'        => 'text',
				'description' => esc_html__( 'Leave empty if you have no custom branding options', 'woocommerce-quickpay' ),
				'default'     => '',
				'desc_tip'    => true,
			],
			'quickpay_payment_window_timeout'    => [
				'title'       => esc_html__( 'Payment window timeout', 'woocommerce-quickpay' ),
				'type'        => 'number',
				'description' => esc_html__( 'Set a time limit in seconds on how long a customer has to complete the payment in the payment window.', 'woocommerce-quickpay' ),
				'default'     => '',
				'desc_tip'    => true,
			],

			'quickpay_autofee'                                   => [
				'title'       => esc_html__( 'Enable autofee', 'woocommerce-quickpay' ),
				'type'        => 'checkbox',
				'label'       => esc_html__( 'Enable', 'woocommerce-quickpay' ),
				'description' => esc_html__( 'Fees are charged according to the applicable rules for card fees, contact your redeemer for more information.', 'woocommerce-quickpay' ),
				'default'     => 'no',
				'desc_tip'    => true,
			],
			'quickpay_captureoncomplete'                         => [
				'title'       => esc_html__( 'Capture on complete', 'woocommerce-quickpay' ),
				'type'        => 'checkbox',
				'label'       => esc_html__( 'Enable', 'woocommerce-quickpay' ),
				'description' => esc_html__( 'When enabled quickpay payments will automatically be captured when order state is set to "Complete".', 'woocommerce-quickpay' ),
				'default'     => 'no',
				'desc_tip'    => true,
			],
			'quickpay_complete_on_capture'                       => [
				'title'       => esc_html__( 'Complete order on capture callbacks', 'woocommerce-quickpay' ),
				'type'        => 'checkbox',
				'label'       => esc_html__( 'Enable', 'woocommerce-quickpay' ),
				'description' => esc_html__( 'When enabled, an order will be automatically completed when capture callbacks are sent to WooCommerce. Callbacks are sent by Quickpay when the payment is captured from either the shop or the Quickpay manager. Keep disabled to manually complete orders. ', 'woocommerce-quickpay' ),
				'default'     => 'no',
			],
			'quickpay_payment_cancelled_order_transition_status' => [
				'title'       => esc_html__( 'Order status update on payment cancellation', 'woocommerce-quickpay' ),
				'type'        => 'select',
				'options'     => $this->get_payment_cancelled_order_transition_statuses(),
				'label'       => esc_html__( 'Enable', 'woocommerce-quickpay' ),
				'description' => esc_html__( 'When activated, orders linked to payments will change to the chosen status if the merchant cancels the payment.', 'woocommerce-quickpay' ),
				'default'     => 'no',
			],
			'quickpay_cancel_transaction_on_cancel'              => [
				'title'       => esc_html__( 'Cancel payments on order cancellation', 'woocommerce-quickpay' ),
				'type'        => 'checkbox',
				'label'       => esc_html__( 'Enable', 'woocommerce-quickpay' ),
				'description' => esc_html__( 'Automatically cancel payments via the API when an order\'s status changes to cancelled.', 'woocommerce-quickpay' ),
				'default'     => 'no',
			],
			'quickpay_text_on_statement'                         => [
				'title'             => esc_html__( 'Text on statement', 'woocommerce-quickpay' ),
				'type'              => 'text',
				'description'       => esc_html__( 'Text that will be placed on cardholder’s bank statement (MAX 22 ASCII characters. Must match the values defined in your agreement with Clearhaus. Custom values are not allowed).', 'woocommerce-quickpay' ),
				'default'           => '',
				'desc_tip'          => false,
				'custom_attributes' => [
					'maxlength' => 22,
				],
			],


			'_Shop_setup'                           => [
				'type'  => 'title',
				'title' => esc_html__( 'Shop setup', 'woocommerce-quickpay' ),
			],
			'title'                                 => [
				'title'       => esc_html__( 'Title', 'woocommerce-quickpay' ),
				'type'        => 'text',
				'description' => esc_html__( 'This controls the title which the user sees during checkout.', 'woocommerce-quickpay' ),
				'default'     => esc_html__( 'Quickpay', 'woocommerce-quickpay' ),
				'desc_tip'    => true,
			],
			'description'                           => [
				'title'       => esc_html__( 'Customer Message', 'woocommerce-quickpay' ),
				'type'        => 'textarea',
				'description' => esc_html__( 'This controls the description which the user sees during checkout.', 'woocommerce-quickpay' ),
				'default'     => esc_html__( 'Pay via Quickpay. Allows you to pay with your credit card via Quickpay.', 'woocommerce-quickpay' ),
				'desc_tip'    => true,
			],
			'checkout_button_text'                  => [
				'title'       => esc_html__( 'Order button text', 'woocommerce-quickpay' ),
				'type'        => 'text',
				'description' => esc_html__( 'Text shown on the submit button when choosing payment method.', 'woocommerce-quickpay' ),
				'default'     => esc_html__( 'Go to payment', 'woocommerce-quickpay' ),
				'desc_tip'    => true,
			],
			'instructions'                          => [
				'title'       => esc_html__( 'Email instructions', 'woocommerce-quickpay' ),
				'type'        => 'textarea',
				'description' => esc_html__( 'Instructions that will be added to emails.', 'woocommerce-quickpay' ),
				'default'     => '',
				'desc_tip'    => true,
			],
			'quickpay_icons'                        => [
				'title'             => esc_html__( 'Credit card icons', 'woocommerce-quickpay' ),
				'type'              => 'multiselect',
				'description'       => esc_html__( 'Choose the card icons you wish to show next to the Quickpay payment option in your shop.', 'woocommerce-quickpay' ),
				'desc_tip'          => true,
				'class'             => 'wc-enhanced-select',
				'css'               => 'width: 450px;',
				'custom_attributes' => [
					'data-placeholder' => esc_html__( 'Select icons', 'woocommerce-quickpay' )
				],
				'default'           => '',
				'options'           => GatewayIcons::all(),
			],
			'quickpay_icons_maxheight'              => [
				'title'       => esc_html__( 'Credit card icons maximum height', 'woocommerce-quickpay' ),
				'type'        => 'number',
				'description' => esc_html__( 'Set the maximum pixel height of the credit card icons shown on the frontend.', 'woocommerce-quickpay' ),
				'default'     => 20,
				'desc_tip'    => true,
			],
			'Google Analytics'                      => [
				'type'  => 'title',
				'title' => esc_html__( 'Google Analytics', 'woocommerce-quickpay' ),
			],
			'quickpay_google_analytics_tracking_id' => [
				'title'       => esc_html__( 'Tracking ID', 'woocommerce-quickpay' ),
				'type'        => 'text',
				'description' => esc_html__( 'Your Google Analytics tracking ID. I.E: UA-XXXXXXXXX-X', 'woocommerce-quickpay' ),
				'default'     => '',
				'desc_tip'    => true,
			],
			'ShopAdminSetup'                        => [
				'type'  => 'title',
				'title' => esc_html__( 'Shop Admin Setup', 'woocommerce-quickpay' ),
			],

			'quickpay_orders_transaction_info' => [
				'title'       => esc_html__( 'Fetch Transaction Info', 'woocommerce-quickpay' ),
				'type'        => 'checkbox',
				'label'       => esc_html__( 'Enable', 'woocommerce-quickpay' ),
				'description' => esc_html__( 'Show transaction information in the order overview.', 'woocommerce-quickpay' ),
				'default'     => 'yes',
				'desc_tip'    => false,
			],

			'CustomVariables'           => [
				'type'  => 'title',
				'title' => esc_html__( 'Custom Variables', 'woocommerce-quickpay' ),
			],
			'quickpay_custom_variables' => [
				'title'             => esc_html__( 'Select Information', 'woocommerce-quickpay' ),
				'type'              => 'multiselect',
				'class'             => 'wc-enhanced-select',
				'css'               => 'width: 450px;',
				'default'           => '',
				'description'       => esc_html__( 'Selected options will store the specific data on your transaction inside your Quickpay Manager.', 'woocommerce-quickpay' ),
				'options'           => $this->custom_variable_options(),
				'desc_tip'          => true,
				'custom_attributes' => [
					'data-placeholder' => esc_html__( 'Select order data', 'woocommerce-quickpay' )
				]
			],
		];

		if ( Dependencies::is_woo_subscriptions_active() ) {
			$fields += $this->subscription_fields();
		}

		return $fields;
	}

	public static function base_gateway_fields( string $gateway_title ): array {
		return [
			'enabled'     => [
				'title'   => esc_html__( 'Enable', 'woocommerce-quickpay' ),
				'type'    => 'checkbox',
				'label'   => sprintf( esc_html__( 'Enable %s payment', 'woocommerce-quickpay' ), $gateway_title ),
				'default' => 'no',
			],
			'_Shop_setup' => [
				'type'  => 'title',
				'title' => esc_html__( 'Shop setup', 'woocommerce-quickpay' ),
			],
			'title'       => [
				'title'       => esc_html__( 'Title', 'woocommerce-quickpay' ),
				'type'        => 'text',
				'description' => esc_html__( 'This controls the title which the user sees during checkout.', 'woocommerce-quickpay' ),
				'default'     => $gateway_title,
			],
			'description' => [
				'title'       => esc_html__( 'Customer Message', 'woocommerce-quickpay' ),
				'type'        => 'textarea',
				'description' => esc_html__( 'This controls the description which the user sees during checkout.', 'woocommerce-quickpay' ),
			]
		];
	}

	private function subscription_fields(): array {
		return [
			'woocommerce-subscriptions'                          => [
				'type'  => 'title',
				'title' => 'Subscriptions'
			],
			'subscription_autocomplete_renewal_orders'           => [
				'title'       => esc_html__( 'Complete renewal orders', 'woocommerce-quickpay' ),
				'type'        => 'checkbox',
				'label'       => esc_html__( 'Enable', 'woocommerce-quickpay' ),
				'description' => esc_html__( 'Automatically mark a renewal order as complete on successful recurring payments.', 'woocommerce-quickpay' ),
				'default'     => 'no',
				'desc_tip'    => true,
			],
			'subscription_update_card_on_manual_renewal_payment' => [
				'title'       => esc_html__( 'Update card on manual renewal payment', 'woocommerce-quickpay' ),
				'type'        => 'checkbox',
				'label'       => esc_html__( 'Enable', 'woocommerce-quickpay' ),
				'description' => esc_html__( 'When paying failed renewals, the payment link will authorize a new subscription transaction which will be saved on the customer\'s subscription. On callback, a payment transaction related to the actual renewal order will be created.', 'woocommerce-quickpay' ),
				'default'     => 'no',
				'desc_tip'    => true,
			]
		];
	}

	/**
	 * @return string
	 */
	private function get_required_symbol(): string {
		return '<span style="color: red;">*</span>';
	}

	/**
	 * Get the array of payment cancelled order transition statuses.
	 *
	 * This method retrieves the order statuses that are considered as cancellation transitions
	 * for a payment. It includes the order statuses 'wc-failed', 'wc-pending', 'wc-on-hold',
	 * and 'wc-cancelled' by default. Additional statuses can be added or modified through the
	 * 'woocommerce_quickpay_payment_cancelled_order_transition_statuses' filter.
	 *
	 * @return array The array of payment cancelled order transition statuses.
	 */
	private function get_payment_cancelled_order_transition_statuses(): array {
		$statuses       = wc_get_order_statuses();
		$allowed_status = apply_filters( 'woocommerce_quickpay_payment_cancelled_order_transition_statuses', [
			'wc-failed',
			'wc-pending',
			'wc-on-hold',
			'wc-cancelled'
		], $statuses );

		$filtered_statuses = array_filter( $statuses, static fn( $status ) => in_array( $status, $allowed_status, true ), ARRAY_FILTER_USE_KEY );

		return array_merge( [ '' => esc_html__( '-- Select (optional) --', 'woocommerce-quickpay' ) ], $filtered_statuses );
	}

	/**
	 * custom_variable_options function.
	 *
	 * Provides a list of custom variable options used in the settings
	 *
	 * @access private
	 * @return array
	 */
	private function custom_variable_options(): array {
		$options = [
			'billing_all_data'  => __( 'Billing: Complete Customer Details', 'woocommerce-quickpay' ),
			'browser_useragent' => __( 'Browser: User Agent', 'woocommerce-quickpay' ),
			'customer_email'    => __( 'Customer: Email Address', 'woocommerce-quickpay' ),
			'customer_phone'    => __( 'Customer: Phone Number', 'woocommerce-quickpay' ),
			'shipping_all_data' => __( 'Shipping: Complete Customer Details', 'woocommerce-quickpay' ),
			'shipping_method'   => __( 'Shipping: Shipping Method', 'woocommerce-quickpay' ),
		];

		asort( $options );

		return $options;
	}
}

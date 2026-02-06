<?php

class WC_QuickPay_PayPal extends WC_QuickPay_Instance {

	public $main_settings = null;

	public function __construct() {
		parent::__construct();

		// Get gateway variables
		$this->id = 'quickpay_paypal';

		$this->method_title = 'Quickpay - PayPal';

		$this->setup();

		$this->title       = $this->s( 'title' );
		$this->description = $this->s( 'description' );

		add_filter( 'woocommerce_quickpay_cardtypelock_quickpay_paypal', [ $this, 'filter_cardtypelock' ] );
		add_filter( 'woocommerce_quickpay_transaction_params_basket', [ $this, '_return_empty_array' ], 30, 2 );
		add_filter( 'woocommerce_quickpay_transaction_params_shipping_row', [ $this, '_return_empty_array' ], 30, 2 );
	}


	/**
	 * init_form_fields function.
	 *
	 * Initiates the plugin settings form fields
	 *
	 * @access public
	 * @return array
	 */
	public function init_form_fields(): void {
		$this->form_fields = [
			'enabled'     => [
				'title'   => esc_html__( 'Enable', 'woocommerce-quickpay' ),
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Enable PayPal payment', 'woocommerce-quickpay' ),
				'default' => 'no'
			],
			'_Shop_setup' => [
				'type'  => 'title',
				'title' => esc_html__( 'Shop setup', 'woocommerce-quickpay' ),
			],
			'title'       => [
				'title'       => esc_html__( 'Title', 'woocommerce-quickpay' ),
				'type'        => 'text',
				'description' => esc_html__( 'This controls the title which the user sees during checkout.', 'woocommerce-quickpay' ),
				'default'     => esc_html__( 'PayPal', 'woocommerce-quickpay' )
			],
			'description' => [
				'title'       => esc_html__( 'Customer Message', 'woocommerce-quickpay' ),
				'type'        => 'textarea',
				'description' => esc_html__( 'This controls the description which the user sees during checkout.', 'woocommerce-quickpay' ),
				'default'     => esc_html__( 'Pay with PayPal', 'woocommerce-quickpay' )
			],
		];
	}


	/**
	 * filter_cardtypelock function.
	 *
	 * Sets the cardtypelock
	 *
	 * @access public
	 * @return string
	 */
	public function filter_cardtypelock(): string {
		return 'paypal';
	}

	/**
	 * @param array $items
	 * @param WC_Order $order
	 *
	 * @return array
	 */
	public function _return_empty_array( array $items, WC_Order $order ): array {
		if ( $order->get_payment_method() === $this->id ) {
			$items = [];
		}

		return $items;
	}

	/**
	 * Sets gateway icons on frontend
	 *
	 * @param $icon
	 * @param $id
	 *
	 * @return string
	 */
	public function apply_gateway_icons( $icon, $id ) {
		if ( $id === $this->id ) {
			$icon = $this->gateway_icon_create( 'paypal', $this->gateway_icon_size() );
		}

		return $icon;
	}
}

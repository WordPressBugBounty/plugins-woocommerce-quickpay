<?php

class WC_QuickPay_Sofort extends WC_QuickPay_Instance {

	public $main_settings = null;

	public function __construct() {
		parent::__construct();

		// Get gateway variables
		$this->id = 'sofort';

		$this->method_title = 'Quickpay - Sofort';

		$this->setup();

		$this->title       = $this->s( 'title' );
		$this->description = $this->s( 'description' );

		add_filter( 'woocommerce_quickpay_cardtypelock_sofort', [ $this, 'filter_cardtypelock' ] );
		add_action( 'woocommerce_quickpay_accepted_callback_status_capture', [ $this, 'additional_callback_handler' ], 10, 2 );
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
				'label'   => esc_html__( 'Enable Sofort payment', 'woocommerce-quickpay' ),
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
				'default'     => esc_html__( 'Sofort', 'woocommerce-quickpay' )
			],
			'description' => [
				'title'       => esc_html__( 'Customer Message', 'woocommerce-quickpay' ),
				'type'        => 'textarea',
				'description' => esc_html__( 'This controls the description which the user sees during checkout.', 'woocommerce-quickpay' ),
				'default'     => esc_html__( 'Pay with your mobile phone', 'woocommerce-quickpay' )
			],
		];
	}

	/**
	 * Sofort payments are not sending authorized callbacks. Instead, a capture callback is sent. We will perform
	 * gateway specific logic here to handle the payment properly.
	 *
	 * @param WC_Order $order
	 * @param stdClass $transaction
	 */
	public function additional_callback_handler( WC_Order $order, $transaction ): void {
		if ( $order->get_payment_method() === $this->id ) {
			WC_QuickPay_Callbacks::authorized( $order, $transaction );
			WC_QuickPay_Callbacks::payment_authorized( $order, $transaction );
		}
	}


	/**
	 * filter_cardtypelock function.
	 *
	 * Sets the cardtypelock
	 *
	 * @access public
	 * @return string
	 */
	public function filter_cardtypelock() {
		return 'sofort';
	}
}

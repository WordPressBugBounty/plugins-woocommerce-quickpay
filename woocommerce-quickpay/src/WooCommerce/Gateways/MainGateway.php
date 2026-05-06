<?php

namespace QuickpayPSP\WooCommerce\Gateways;

use QuickpayPSP\Plugin;
use QuickpayPSP\WooCommerce\Logging\WooCommerceLogger;
use QuickpayPSP\WooCommerce\Settings\GatewaySettingsSchema;

class MainGateway extends BaseGateway {

	public const ID = 'quickpay';

	public function __construct() {
		$this->method_title       = 'Quickpay';
		$this->method_description = 'Allow payments via Quickpay';

		$this->has_fields = true;

		$this->supports_products();
		$this->supports_preorders();
		$this->supports_subscriptions();

		parent::__construct();

		$this->order_button_text = $this->settings['checkout_button_text'];
	}

	public function init_form_fields(): void {
		$settings          = Plugin::services()->get_as( GatewaySettingsSchema::class, 'gateway/settings_schema' );
		$this->form_fields = $settings->fields();
	}

	/**
	 * @return string[]
	 */
	protected function icon_slugs(): array {
		$icons = $this->get_option( 'quickpay_icons' );

		if ( ! empty( $icons ) ) {
			return array_values( $icons );
		}

		return [ 'visa', 'mastercard' ];

	}

	public function generate_settings_html( $form_fields = array(), $echo = true ) {
		$html = sprintf( "<p><small>Version: %s</small>", WCQP_VERSION );
		/* translators: 1: payment method title */
		$html .= "<p>" . sprintf( __( 'Allows you to receive payments via %s', 'woocommerce-quickpay' ), $this->get_method_title() ) . "</p>";
		$html .= $this->clear_logs_section();

		ob_start();
		do_action( 'woocommerce_quickpay_settings_table_before' );
		$html .= ob_get_clean();

		$html .= parent::generate_settings_html( $form_fields, $echo );

		ob_start();
		do_action( 'woocommerce_quickpay_settings_table_after' );
		$html .= ob_get_clean();

		if ( $echo ) {
			echo wp_kses_post( $html ); // WPCS: XSS ok.
		} else {
			return $html;
		}
	}

	/**
	 * Clears the log file.
	 *
	 * @return string
	 */
	private function clear_logs_section() {
		$html = sprintf( '<h3 class="wc-settings-sub-title">%s</h3>', esc_html__( 'Debug', 'woocommerce-quickpay' ) );
		$html .= sprintf( '<a id="wcqp_wiki" class="wcqp-debug-button button button-primary" href="%s" target="_blank">%s</a>', 'http://quickpay.perfect-solution.dk', esc_html__( 'Got problems? Check out the Wiki.', 'woocommerce-quickpay' ) );
		$html .= sprintf( '<a id="wcqp_logs" class="wcqp-debug-button button" href="%s">%s</a>', Plugin::services()->get_as( WooCommerceLogger::class, 'logger' )->get_admin_link(), esc_html__( 'View debug logs', 'woocommerce-quickpay' ) );

		if ( woocommerce_quickpay_can_user_empty_logs() ) {
			$html .= sprintf( '<button role="button" id="wcqp_logs_clear" class="wcqp-debug-button button">%s</button>', esc_html__( 'Empty debug logs', 'woocommerce-quickpay' ) );
		}

		if ( woocommerce_quickpay_can_user_flush_cache() ) {
			$html .= sprintf( '<button role="button" id="wcqp_flush_cache" class="wcqp-debug-button button">%s</button>', esc_html__( 'Empty transaction cache', 'woocommerce-quickpay' ) );
		}

		$html .= sprintf( '<br/>' );
		$html .= sprintf( '<h3 class="wc-settings-sub-title">%s</h3>', esc_html__( 'Enable', 'woocommerce-quickpay' ) );

		return $html;
	}

}

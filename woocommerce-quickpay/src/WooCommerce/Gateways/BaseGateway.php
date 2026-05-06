<?php

namespace QuickpayPSP\WooCommerce\Gateways;

use QuickpayPSP\Plugin;
use QuickpayPSP\WooCommerce\Controllers\PaymentController;
use QuickpayPSP\WooCommerce\Controllers\RefundController;
use WC_Payment_Gateway;
use WP_Error;

abstract class BaseGateway extends WC_Payment_Gateway {

	public const ID = '';

	protected ?string $cached_card_type_lock = null;

	public function __construct() {
		$this->id          = static::ID;
		$this->title       = $this->get_option( 'title', $this->method_title );
		$this->description = $this->get_option( 'description' );

		$this->supports_products();

		$this->init_form_fields();
		$this->init_settings();

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'process_admin_options' ] );
	}

	/**
	 * Processing payments on checkout.
	 *
	 * @param int $order_id
	 *
	 * @return array|null
	 */
	public function process_payment( $order_id ): ?array {
		return Plugin::services()->get_as( PaymentController::class, 'woocommerce/payment/controller' )->process_payment( (int) $order_id );
	}

	/**
	 * Process refunds.
	 *
	 * @param int        $order_id
	 * @param float|null $amount
	 * @param string     $reason
	 *
	 * @return bool|WP_Error
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		return Plugin::services()->get_as( RefundController::class, 'woocommerce/refund/controller' )->process_refund( (int) $order_id, $amount !== null ? (float) $amount : null, (string) $reason );
	}

	/**
	 * Clear cart on thank-you page in case it's not already done.
	 */
	public function thankyou_page(): void {
		if ( WC()->cart ) {
			WC()->cart->empty_cart();
		}
	}

	protected function supports_products(): void {
		$this->supports = array_unique( array_merge( $this->supports, [
			'products',
			'refunds',
		] ) );
	}

	protected function supports_subscriptions(): void {
		$this->supports = array_merge( $this->supports, [
			'subscriptions',
			'multiple_subscriptions',
			'subscription_cancellation',
			'subscription_reactivation',
			'subscription_suspension',
			'subscription_amount_changes',
			'subscription_date_changes',
			'subscription_payment_method_change_admin',
			'subscription_payment_method_change_customer',
		] );
	}

	protected function supports_preorders(): void {
		$this->supports = array_merge( $this->supports, [
			'pre-orders',
		] );
	}

	/**
	 * @return string
	 */
	public function default_card_type_lock(): string {
		return (string) $this->get_option( 'quickpay_cardtypelock', 'creditcard' );
	}

	/**
	 * WooCommerce calls this to render the icon on frontend.
	 */
	public function get_icon(): string {
		$maxHeight = $this->icon_max_height();

		// Let each gateway decide its default icon slug(s)
		$slugs = $this->icon_slugs();

		$html = '';
		foreach ( $slugs as $slug ) {
			$html .= $this->render_icon_img( $slug, $maxHeight );
		}

		return $html;
	}

	/**
	 * Default max height for icons (can be overridden per gateway/settings).
	 */
	protected function icon_max_height(): int {
		// If you have a shared setting like quickpay_icons_maxheight in MAIN gateway,
		// override this method there. For method gateways, 20 is usually fine.
		return 20;
	}

	/**
	 * Icon slugs to render for this gateway.
	 * Return one slug (e.g. ['apple-pay']) or multiple (e.g. ['visa','mastercard']).
	 *
	 * IMPORTANT: these are "logical" slugs, not file names.
	 *
	 * @return string[]
	 */
	protected function icon_slugs(): array {
		// Default: no icons
		return [];
	}

	/**
	 * Render one icon <img>.
	 */
	protected function render_icon_img( string $icon, int $maxHeight ): string {
		// BC: legacy behavior removed "quickpay_" prefix
		$icon = str_replace( 'quickpay_', '', $icon );

		// BC: allow old filter to rewrite the slug
		$icon = apply_filters( 'woocommerce_quickpay_checkout_gateway_icon', $icon );

		$iconUrl = $this->resolve_icon_url( $icon );

		// BC: allow old filter to rewrite URL
		$iconUrl = apply_filters( 'woocommerce_quickpay_checkout_gateway_icon_url', $iconUrl, $icon, $this );

		if ( ! $iconUrl ) {
			return '';
		}

		return sprintf(
			'<img src="%s" alt="%s" style="max-height:%dpx;" />',
			esc_url( $iconUrl ),
			esc_attr( $this->get_title() ),
			$maxHeight
		);
	}

	/**
	 * Find SVG first, then PNG. Returns empty string if nothing exists.
	 */
	protected function resolve_icon_url( string $icon ): string {
		// Point this at your plugin root file constant if you have it.
		// Example: define('QUICKPAY_PLUGIN_FILE', __FILE__) in main plugin file.
		$plugin_file = defined( 'QUICKPAY_PLUGIN_FILE' ) ? QUICKPAY_PLUGIN_FILE : __FILE__;

		$base_rel = 'assets/images/cards/' . $icon;
		$svg_abs  = plugin_dir_path( $plugin_file ) . $base_rel . '.svg';
		$png_abs  = plugin_dir_path( $plugin_file ) . $base_rel . '.png';

		if ( is_file( $svg_abs ) ) {
			$url = plugins_url( $base_rel . '.svg', $plugin_file );

			return \WC_HTTPS::force_https_url( $url );
		}

		if ( is_file( $png_abs ) ) {
			$url = plugins_url( $base_rel . '.png', $plugin_file );

			return \WC_HTTPS::force_https_url( $url );
		}

		return '';
	}
}

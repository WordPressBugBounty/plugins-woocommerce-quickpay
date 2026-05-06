<?php

namespace QuickpayPSP\Admin\Updates;

class Update43 {
	public function __invoke() {
		$settings = get_option( 'woocommerce_quickpay_settings' );

		if ( is_array( $settings ) && ! isset( $settings['quickpay_autocapture_virtual'] ) && isset( $settings['quickpay_autocapture'] ) ) {
			$settings['quickpay_autocapture_virtual'] = $settings['quickpay_autocapture'];
			update_option( 'woocommerce_quickpay_settings', $settings );
		}
	}
}

<?php

namespace QuickpayPSP\Admin\Registrars;

use QuickpayPSP\Admin\Controllers\NoticesController;
use QuickpayPSP\Admin\Controllers\InstallController;

class AdminNoticesRegistrar {

	private NoticesController $notices_controller;
	private InstallController $install_controller;

	public function __construct( NoticesController $notices_controller, InstallController $install_controller ) {
		$this->notices_controller = $notices_controller;
		$this->install_controller = $install_controller;
	}

	public function register(): void {
		add_action( 'wp_ajax_woocommerce_quickpay_flush_runtime_errors', 'woocommerce_quickpay_ajax_flush_runtime_errors' );
		add_action( 'admin_notices', 'woocommerce_quickpay_display_dismissible_admin_notices', 100 );
		add_action( 'admin_notices', 'woocommerce_quickpay_display_admin_notices', 100 );
		add_action( 'admin_notices', [ $this->notices_controller, 'maybe_display_mandatory_settings' ], 100 );
		add_action( 'admin_notices', [ $this->notices_controller, 'bulk_action_payment_link_notice' ] );
		add_action( 'admin_notices', [ $this->install_controller, 'show_update_warning' ], 10 );
	}
}

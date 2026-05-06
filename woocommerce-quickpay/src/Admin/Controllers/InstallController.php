<?php

namespace QuickpayPSP\Admin\Controllers;

use QuickpayPSP\Admin\Support\Installer;
use QuickpayPSP\Admin\Support\InstallState;
use QuickpayPSP\Admin\ViewRenderer;

class InstallController {
	/**
	 * @var Installer
	 */
	private $installer;

	/**
	 * @var InstallState
	 */
	private $install_state;

	/**
	 * @var ViewRenderer
	 */
	private $views;

	public function __construct( Installer $installer, InstallState $install_state, ViewRenderer $views ) {
		$this->installer     = $installer;
		$this->install_state = $install_state;
		$this->views         = $views;
	}

	/**
	 * @return void
	 */
	public function show_update_warning() {
		if ( ! $this->installer->is_update_required() ) {
			return;
		}

		if ( ! $this->install_state->is_maintenance_enabled() ) {
			$this->views->render( 'html-notice-update.php', [
				'nonce' => $this->create_run_upgrader_nonce(),
			] );

			return;
		}

		$this->views->render( 'html-notice-upgrading.php' );
	}

	/**
	 * @return void
	 */
	public function ajax_run_upgrader(): void {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

		if (
			empty( $nonce ) ||
			! wp_verify_nonce( $nonce, 'woocommerce-quickpay-run-upgrader-nonce' ) ||
			! current_user_can( 'administrator' )
		) {
			echo wp_json_encode(
				array(
					'status'  => 'error',
					'message' => __( 'You are not authorized to perform this action', 'woocommerce-quickpay' ),
				)
			);
			exit;
		}

		$this->installer->run_updates();

		echo wp_json_encode( array( 'status' => 'success' ) );
		exit;
	}

	/**
	 * @return string
	 */
	public function create_run_upgrader_nonce() {
		return wp_create_nonce( 'woocommerce-quickpay-run-upgrader-nonce' );
	}
}

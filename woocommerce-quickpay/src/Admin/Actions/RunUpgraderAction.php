<?php

namespace QuickpayPSP\Admin\Actions;

use QuickpayPSP\Admin\Support\Installer;

final class RunUpgraderAction implements AdminActionInterface {
	/**
	 * @var Installer
	 */
	private $installer;

	public function __construct( Installer $installer ) {
		$this->installer = $installer;
	}

	public function action(): string {
		return 'quickpay_run_data_upgrader';
	}

	public function register(): void {
		add_action( 'wp_ajax_' . $this->action(), [ $this, 'execute' ] );
	}

	public function execute(): void {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

		if (
			empty( $nonce ) ||
			! wp_verify_nonce( $nonce, 'woocommerce-quickpay-run-upgrader-nonce' ) ||
			! current_user_can( 'administrator' )
		) {
			echo wp_json_encode(
				[
					'status'  => 'error',
					'message' => __( 'You are not authorized to perform this action', 'woocommerce-quickpay' ),
				]
			);
			exit;
		}

		$this->installer->run_updates();

		echo wp_json_encode( [ 'status' => 'success' ] );
		exit;
	}
}

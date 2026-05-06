<?php

namespace QuickpayPSP\Admin;

class ViewRenderer {
	public function render( string $path, array $args = [] ): void {
		if ( is_array( $args ) && ! empty( $args ) ) {
			extract( $args );
		}

		$file = QUICKPAY_PLUGIN_PATH . 'views/' . trim( $path );

		if ( file_exists( $file ) ) {
			include $file;
		}
	}
}

<?php

namespace QuickpayPSP\WooCommerce\Logging;

final class WooCommerceLogger {
	private string $source;
	/** @var object|null */
	private $logger;

	public function __construct( string $source = 'woocommerce-quickpay', $logger = null ) {
		$this->source = $source;

		if ( $logger !== null ) {
			$this->logger = $logger;
		}

		// Logger resolves lazily in resolve_logger() to support early construction
		// before WooCommerce is fully loaded (e.g. during callback handling).
	}

	/**
	 * Lazily resolve the WC_Logger instance.
	 * Falls back to null if WooCommerce is not yet available.
	 */
	private function resolve_logger(): void {
		if ( $this->logger !== null ) {
			return;
		}

		if ( function_exists( 'wc_get_logger' ) ) {
			$this->logger = wc_get_logger();
		} elseif ( class_exists( 'WC_Logger' ) ) {
			$this->logger = new \WC_Logger();
		}
	}

	/**
	 * Log a message or array payload.
	 *
	 * @param string|array $message
	 */
	public function info( $message, ?string $file = null, ?int $line = null ): void {
		$this->write( 'info', $message, $file, $line );
	}

	/**
	 * @param string|array $message
	 */
	public function error( $message, ?string $file = null, ?int $line = null ): void {
		$this->write( 'error', $message, $file, $line );
	}

	/**
	 * @param string $level e.g. 'info','error','debug'
	 * @param string|array $message
	 */
	public function write( string $level, $message, ?string $file = null, ?int $line = null ): void {
		$this->resolve_logger();
		$msg = '';

		if ( $file ) {
			$msg .= sprintf( 'File: %s -> ', $file );
		}
		if ( $line ) {
			$msg .= sprintf( 'Line: %d -> ', $line );
		}

		if ( is_array( $message ) ) {
			$msg .= wp_json_encode( $message, JSON_PRETTY_PRINT );
		} else {
			$msg .= (string) $message;
		}

		// WooCommerce logger expects 'source' in context.
		if ( $this->logger && method_exists( $this->logger, 'log' ) ) {
			$this->logger->log( $level, $msg, [ 'source' => $this->source ] );

			return;
		}

		error_log( sprintf( '[%s] %s', $this->source, $msg ) );
	}

	public function clear(): bool {
		$this->resolve_logger();

		if ( ! $this->logger || ! method_exists( $this->logger, 'clear' ) ) {
			return false;
		}

		return (bool) $this->logger->clear( $this->source );
	}

	public function separator(): void {
		$this->info( '--------------------' );
	}

	public function source(): string {
		return $this->source;
	}

	public function admin_link(): ?string {
		if ( ! defined( 'WC_VERSION' ) ) {
			return null;
		}

		if ( version_compare( WC_VERSION, '8.6', '>=' ) ) {
			$args = [
				'page'   => 'wc-status',
				'tab'    => 'logs',
				'source' => $this->source,
			];
		} else {
			// Fallback
			$log_path = wc_get_log_file_path( $this->source );
			$parts    = explode( '/', (string) $log_path );

			$args = [
				'page'     => 'wc-status',
				'tab'      => 'logs',
				'log_file' => end( $parts ),
			];
		}

		return add_query_arg( $args, admin_url( 'admin.php' ) );
	}

	/**
	 * BC wrapper for older call sites.
	 */
	public function get_admin_link(): ?string {
		return $this->admin_link();
	}
}

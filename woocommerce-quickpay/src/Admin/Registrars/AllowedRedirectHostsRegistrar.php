<?php

namespace QuickpayPSP\Admin\Registrars;

final class AllowedRedirectHostsRegistrar {
	/** @var string[] */
	private array $hosts;

	/**
	 * @param string[] $hosts e.g. ['payment.quickpay.net', 'api.quickpay.net']
	 */
	public function __construct( array $hosts ) {
		$this->hosts = array_values( array_filter( array_map( 'strtolower', $hosts ) ) );
	}

	public function register(): void {
		add_filter( 'allowed_redirect_hosts', [ $this, 'filter_allowed_hosts' ] );
	}

	/**
	 * @param string[]|null $hosts
	 *
	 * @return string[]
	 */
	public function filter_allowed_hosts( ?array $hosts ): array {
		$hosts = $hosts ?? [];
		foreach ( $this->hosts as $h ) {
			if ( ! in_array( $h, $hosts, true ) ) {
				$hosts[] = $h;
			}
		}

		return apply_filters( 'woocommerce_quickpay_allowed_redirect_hosts', $hosts );
	}
}

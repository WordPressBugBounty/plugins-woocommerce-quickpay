<?php

namespace QuickpayPSP\Support;

use RuntimeException;

/**
 * Simple service container/registry
 *
 * Usage:
 *   $s->set('foo', fn(Services $s) => new Foo($s->get('bar')));
 *   $foo = $s->get('foo'); // mixed, but improved with generics phpdoc
 *
 * @psalm-consistent-constructor
 */
final class Services {
	/** @var array<string, callable(self):mixed> */
	private array $factories = [];

	/** @var array<string, mixed> */
	private array $instances = [];

	/** @var array<string, bool> */
	private array $building = [];

	/**
	 * Register a factory (lazy singleton).
	 *
	 * @param string $id
	 * @param callable(self):mixed | string $factory
	 */
	public function set( string $id, $factory ): void {
		$this->factories[ $id ] = is_callable( $factory ) ? $factory : static fn() => new $factory();
		// If you re-register, drop existing instance so it rebuilds
		unset( $this->instances[ $id ] );
	}

	/**
	 * Check if a service is registered.
	 */
	public function has( string $id ): bool {
		return isset( $this->factories[ $id ] ) || array_key_exists( $id, $this->instances );
	}

	/**
	 * Resolve a service.
	 * @template T
	 *
	 * @param string $id
	 *
	 * @return T
	 */
	public function get( string $id ) {
		if ( array_key_exists( $id, $this->instances ) ) {
			return $this->instances[ $id ];
		}

		if ( ! isset( $this->factories[ $id ] ) ) {
			throw new RuntimeException( "Service not found: {$id}" );
		}

		// Circular dependency detection (very helpful)
		if ( isset( $this->building[ $id ] ) ) {
			throw new RuntimeException( "Circular dependency while building service: {$id}" );
		}

		$this->building[ $id ] = true;

		try {
			// Always pass container; factories may ignore it.
			$instance               = ( $this->factories[ $id ] )( $this );
			$this->instances[ $id ] = $instance;

			return $instance;
		} finally {
			unset( $this->building[ $id ] );
		}
	}

	/**
	 * Resolve a service with an expected class/interface.
	 * Gives you runtime safety + better DX in call sites.
	 *
	 * @template T of object
	 * @param class-string<T> $expected
	 * @param string $id
	 *
	 * @return T
	 */
	public function get_as( string $expected, string $id ): object {
		$svc = $this->get( $id );

		if ( ! is_object( $svc ) || ! is_a( $svc, $expected ) ) {
			$got = is_object( $svc ) ? get_class( $svc ) : gettype( $svc );
			throw new RuntimeException( "Service '{$id}' expected {$expected}, got {$got}" );
		}

		return $svc;
	}

	/**
	 * Convenience: register a ready-made instance.
	 */
	public function set_instance( string $id, $instance ): void {
		$this->instances[ $id ] = $instance;
		unset( $this->factories[ $id ] );
	}

	/**
	 * Debug helper: list registered service IDs.
	 *
	 * @return string[]
	 */
	public function ids(): array {
		return array_values( array_unique( array_merge(
			array_keys( $this->factories ),
			array_keys( $this->instances )
		) ) );
	}
}

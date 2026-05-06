<?php

namespace QuickpayPSP\Support\Http;

/**
 * Immutable value object representing a normalised HTTP response.
 *
 * Wraps status code, body, headers, and transport metadata so that
 * higher layers never depend on a specific HTTP transport directly.
 */
final class HttpResponse {
	private int $status;
	private string $body;
	/** @var array<string, array<int, string>> */
	private array $headers;
	/** @var array<string, mixed> */
	private array $info;

	/**
	 * @param array<string, array<int, string>> $headers
	 * @param array<string, mixed> $info
	 */
	public function __construct( int $status, string $body, array $headers = [], array $info = [] ) {
		$this->status  = $status;
		$this->body    = $body;
		$this->headers = $headers;
		$this->info    = $info;
	}

	/**
	 * Returns the HTTP status code.
	 *
	 * @return int
	 */
	public function status(): int {
		return $this->status;
	}

	/**
	 * Returns the raw response body.
	 *
	 * @return string
	 */
	public function body(): string {
		return $this->body;
	}

	/**
	 * Returns the response headers, keyed by lower-cased header name.
	 *
	 * @return array<string, array<int, string>>
	 */
	public function headers(): array {
		return $this->headers;
	}

	/**
	 * Returns transport-level metadata (e.g. url, http_code).
	 *
	 * @return array<string, mixed>
	 */
	public function info(): array {
		return $this->info;
	}
}

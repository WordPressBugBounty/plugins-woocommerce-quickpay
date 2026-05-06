<?php

namespace QuickpayPSP\Support\Http;

/**
 * Contract for HTTP clients used by the QuickPay API layer.
 *
 * Implementations must wrap the underlying transport (e.g. WordPress HTTP API)
 * and return a normalised HttpResponse. Lives in the Infrastructure layer.
 */
interface HttpClientInterface {
	/**
	 * Sends an HTTP request and returns a normalised response.
	 *
	 * @param string $method HTTP verb (GET, POST, PUT, DELETE, …).
	 * @param string $url    Fully-qualified request URL.
	 * @param array  $args   Optional transport arguments (headers, body, timeout, …).
	 *
	 * @return HttpResponse
	 */
	public function request( string $method, string $url, array $args = [] ): HttpResponse;
}

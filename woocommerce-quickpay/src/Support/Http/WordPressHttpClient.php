<?php

namespace QuickpayPSP\Support\Http;

use RuntimeException;
use WP_Error;

/**
 * HttpClientInterface implementation backed by the WordPress HTTP API (wp_remote_request).
 *
 * Normalises the WordPress response array into an HttpResponse value object and
 * converts WP_Error failures into RuntimeExceptions so callers never need to
 * know about WordPress internals. Lives in the Infrastructure layer.
 */
final class WordPressHttpClient implements HttpClientInterface {
	/**
	 * Sends an HTTP request via wp_remote_request and returns a normalised HttpResponse.
	 *
	 * @param string $method HTTP verb (GET, POST, PUT, DELETE, …).
	 * @param string $url    Fully-qualified request URL.
	 * @param array  $args   Optional wp_remote_request arguments (headers, body, timeout, …).
	 *
	 * @return HttpResponse
	 * @throws \RuntimeException When WordPress returns a WP_Error.
	 */
	public function request( string $method, string $url, array $args = [] ): HttpResponse {
		$args['method'] = strtoupper( $method );

		$response = wp_remote_request( $url, $args );

		if ( $response instanceof WP_Error ) {
			throw new RuntimeException( $response->get_error_message(), (int) $response->get_error_code() );
		}

		$status = (int) wp_remote_retrieve_response_code( $response );
		$body   = (string) wp_remote_retrieve_body( $response );

		$raw_headers = wp_remote_retrieve_headers( $response );
		$headers     = [];
		if ( $raw_headers ) {
			foreach ( $raw_headers as $name => $value ) {
				$key = strtolower( (string) $name );
				if ( is_array( $value ) ) {
					$headers[ $key ] = array_values( array_map( 'strval', $value ) );
				} else {
					$headers[ $key ] = [ (string) $value ];
				}
			}
		}

		$info = [
			'url'       => $url,
			'http_code' => $status,
		];

		return new HttpResponse( $status, $body, $headers, $info );
	}
}

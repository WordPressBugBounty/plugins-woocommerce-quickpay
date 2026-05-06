<?php

namespace QuickpayPSP\QuickPay\Api;

use QuickpayPSP\Support\Config\GatewaySettingsProvider;
use QuickpayPSP\Support\Http\HttpClientInterface;
use QuickpayPSP\Support\Http\WordPressHttpClient;
use QuickpayPSP\Plugin;
use QuickpayPSP\QuickPay\Api\Exceptions\QuickPayApiException;
use QuickpayPSP\QuickPay\Api\Exceptions\QuickPayException;
use QuickpayPSP\Utilities\UrlUtils;
use stdClass;

class QuickPayClient {
	protected HttpClientInterface $http;
	protected GatewaySettingsProvider $settings;
	protected string $api_url = 'https://api.quickpay.net/';
	protected ?string $api_key = null;
	protected ?string $private_key = null;
	protected $resource_data;

	public function __construct( ?HttpClientInterface $http = null, ?GatewaySettingsProvider $settings = null, ?string $api_key = null, ?string $private_key = null ) {
		if ( ! $http || ! $settings ) {
			if ( class_exists( Plugin::class ) ) {
				$services = Plugin::services();
				if ( ! $http && $services->has( 'http/client' ) ) {
					$http = $services->get( 'http/client' );
				}
				if ( ! $settings && $services->has( 'gateway/settings_provider' ) ) {
					$settings = $services->get( 'gateway/settings_provider' );
				}
			}
		}

		$this->http     = $http ?: new WordPressHttpClient();
		$this->settings = $settings ?: new GatewaySettingsProvider( 'quickpay' );

		$this->api_key     = $api_key !== null ? $api_key : (string) $this->settings->getApiKey();
		$this->private_key = $private_key !== null ? $private_key : (string) $this->settings->getPrivateKey();

		$this->resource_data = new stdClass();

		$this->api_url = (string) apply_filters( 'woocommerce_quickpay_api_base_url', $this->api_url );
	}

	public function is_authorized_callback( string $response_body ): bool {
		if ( ! isset( $_SERVER['HTTP_QUICKPAY_CHECKSUM_SHA256'] ) ) {
			return false;
		}

		return hash_hmac( 'sha256', $response_body, (string) $this->private_key ) === $_SERVER['HTTP_QUICKPAY_CHECKSUM_SHA256'];
	}

	/**
	 * @throws QuickPayApiException
	 */
	public function get( string $path, bool $return_array = false ) {
		return $this->execute( 'GET', $path, [], $return_array );
	}

	/**
	 * @throws QuickPayApiException
	 */
	public function post( string $path, array $form = [], bool $return_array = false ) {
		return $this->execute( 'POST', $path, $form, $return_array );
	}

	/**
	 * @throws QuickPayApiException
	 */
	public function put( string $path, array $form = [], bool $return_array = false ) {
		return $this->execute( 'PUT', $path, $form, $return_array );
	}

	/**
	 * @throws QuickPayApiException
	 */
	public function patch( string $path, array $form = [], bool $return_array = false ) {
		return $this->execute( 'PATCH', $path, $form, $return_array );
	}

	/**
	 * @throws QuickPayApiException
	 */
	protected function execute( string $request_type, string $path, array $form = [], bool $return_array = false ) {
		$request_url      = $this->build_url( $path );
		$request_form_data = '';
		$post_id          = $this->get_post_id_from_form_object( $form );

		$headers = [
			'Authorization'       => 'Basic ' . base64_encode( ':' . $this->api_key ),
			'Accept-Version'      => 'v10',
			'Accept'              => 'application/json',
			'Content-Type'        => 'application/x-www-form-urlencoded; charset=UTF-8',
		];

		$callback_url = ! apply_filters( 'woocommerce_quickpay_block_callback', false, $post_id )
			? $this->get_callback_url( $post_id )
			: null;

		if ( $callback_url ) {
			$headers['QuickPay-Callback-Url'] = $callback_url;
		}

		$args = [
			'headers'   => $headers,
			'timeout'   => (int) apply_filters( 'woocommerce_quickpay_api_timeout', 45 ),
			'sslverify' => true,
		];

		if ( ! empty( $form ) ) {
			$request_form_data = $this->encode_form_data( $form );
			$args['body']      = $request_form_data;
		}

		try {
			$response = $this->http->request( $request_type, $request_url, $args );
		} catch ( \RuntimeException $e ) {
			throw new QuickPayApiException( $e->getMessage(), 0, $e, $request_url, $request_form_data, '' );
		}

		$response_body = $response->body();
		$decoded       = json_decode( $response_body );
		$this->resource_data = $decoded !== null ? $decoded : $response_body;

		$response_code = $response->status();

		if ( $response_code > 299 ) {
			throw $this->build_api_exception( $response_code, $request_url, $request_form_data, $response_body );
		}

		if ( $return_array ) {
			return [
				$this->resource_data,
				$request_url,
				$request_form_data,
				$response_body,
				$response->info(),
				$response->headers(),
			];
		}

		return $this->resource_data;
	}

	protected function build_url( string $path ): string {
		if ( strpos( $path, 'https://' ) === 0 && strpos( $path, '.quickpay.net/' ) !== false ) {
			return $path;
		}

		return rtrim( $this->api_url, '/' ) . '/' . ltrim( $path, '/' );
	}

	protected function get_post_id_from_form_object( array $form_data ) {
		if ( array_key_exists( 'order_post_id', $form_data ) ) {
			return $form_data['order_post_id'];
		}

		return null;
	}

	protected function encode_form_data( array $form ): string {
		return (string) preg_replace( '/%5B[0-9]+%5D/simU', '%5B%5D', http_build_query( $form, '', '&' ) );
	}

	protected function build_api_exception( int $response_code, string $request_url, string $request_data, string $response_body ): QuickPayApiException {
		$message = (string) $response_body;

		if ( $this->resource_data instanceof \stdClass && isset( $this->resource_data->errors ) ) {
			$errors         = json_decode( wp_json_encode( $this->resource_data ), true );
			$error_messages = [];

			if ( ! empty( $errors['message'] ) ) {
				$error_messages[] = $errors['message'];
			}

			if ( ! empty( $errors['errors'] ) && $errors['error_code'] === null ) {
				foreach ( $errors['errors'] as $field => $field_errors ) {
					foreach ( $field_errors as $field_error ) {
						$error_messages[] = sprintf( '- <strong>%s</strong>: %s', $field, $field_error );
					}
				}
			}

			$message = implode( "\n", $error_messages );
		} elseif ( $this->resource_data instanceof \stdClass && isset( $this->resource_data->message ) ) {
			$message = (string) $this->resource_data->message;
		}

		return new QuickPayApiException( $message, $response_code, null, $request_url, $request_data, $response_body );
	}

	protected function get_callback_url( $post_id = null ): string {
		return UrlUtils::get_callback_url( $post_id );
	}
}

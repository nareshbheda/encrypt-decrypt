<?php
/**
 * Simple PHP encrypt and decrypt using OpenSSL
 *
 */

/**
 * Load .env file and extract environment variables.
 *
 * @param string $file_path .env file path.
 * @throws Exception If the .env file is not found.
 * @return array file data
 */
function load_envkey_file( $file_path ) {
	if ( ! file_exists( $file_path ) ) {
		throw new Exception( 'KeyFile not found!' );
	}
	$vars      = array();
	$file_data = file( $file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
	foreach ( $file_data as $line ) {
		// Skip comment lines.
		if ( strpos( trim( $line ), '#' ) === 0 ) {
			continue;
		}

		list( $name, $value )  = explode( '=', $line, 2 );
		$vars[ trim( $name ) ] = trim( $value );
	}
	return $vars;
}

/**
 * Encrypts data using AES-256-CBC with a unique IV
 *
 * @param string $data Data to encrypt.
 * @param string $key Encryption key.
 * @return string Encrypted data
 */
function encrypt_str( $data, $key ) {
	$iv        = openssl_random_pseudo_bytes( openssl_cipher_iv_length( 'aes-256-cbc' ) );
	$encrypted = openssl_encrypt( $data, 'aes-256-cbc', $key, 0, $iv );
	return $iv . $encrypted;
}

/**
 * Decrypts data using AES-256-CBC with a unique IV
 *
 * @param string $data Data to decrypt.
 * @param string $key Encryption key.
 * @return string Decrypted data
 */
function decrypt_str( $data, $key ) {
	$iv_length      = openssl_cipher_iv_length( 'aes-256-cbc' );
	$iv             = substr( $data, 0, $iv_length );
	$encrypted_data = substr( $data, $iv_length );
	return openssl_decrypt( $encrypted_data, 'aes-256-cbc', $key, 0, $iv );
}

// Load the .env file and retrieve the encryption key.
$env        = load_envkey_file( __DIR__ . '/.env' );
$secret_key = ! empty( $env['SECRET_KEY'] ) ? $env['SECRET_KEY'] : '';

if ( ! $secret_key ) {
	throw new Exception( 'Encryption key not found' );
}

// Example usage.
$str = 'Hello World!';
echo $str . '<br>';
$encrypted_str = encrypt_str( $str, $secret_key );
echo 'Encrypted String: ' . $encrypted_str . '<br>';

$decrypted_str = decrypt_str( $encrypted_str, $secret_key );
echo 'Decrypted String: ' . $decrypted_str . '<br>';

<?php

namespace WebpConverter\Error\Notice;

/**
 * {@inheritdoc}
 */
class BypassingApacheNotice implements ErrorNotice {

	const ERROR_KEY = 'bypassing_apache';

	/**
	 * {@inheritdoc}
	 */
	public function get_key(): string {
		return self::ERROR_KEY;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_message(): array {
		return [
			__( 'Requests to images are processed by your server bypassing Apache. When loading images, rules from the .htaccess file are not executed. Change the server settings to handle the rules in the .htaccess file when loading static files.', 'webp-converter-for-media' ),
			sprintf(
				'%2$s %1$s - %3$s %1$s - %4$s %1$s - %5$s',
				'<br>',
				__( 'Potential settings in the server or hosting configuration (usually you will find them in your hosting control panel) that may be causing this issue:', 'webp-converter-for-media' ),
				__( '"Smart static files processing" and "Serve static files directly by Nginx" or similar in the section related to Apache and Nginx configuration', 'webp-converter-for-media' ),
				__( '"Nginx Direct Delivery" or similar in the section related to speed or caching', 'webp-converter-for-media' ),
				__( '"Nginx Caching" or similar (you can instead of disabling this setting remove the following extensions from the list of saved to the cache: .jpg, .jpeg, .png and .gif)', 'webp-converter-for-media' )
			),
			__( 'If you have any of the above settings active, you must disable them for .htaccess rules to work properly.', 'webp-converter-for-media' ),
			__( 'In this case, please contact your server administrator.', 'webp-converter-for-media' ),
		];
	}
}

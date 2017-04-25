<?php
/**
 * Domainer Registry API
 *
 * @package Domainer
 * @subpackage Tools
 *
 * @since 1.0.0
 */

namespace Domainer;

/**
 * The Registry
 *
 * Stores all the configuration options for the system.
 *
 * @api
 *
 * @since 1.0.0
 */
final class Registry {
	// =========================
	// ! Properties
	// =========================

	/**
	 * The loaded status flag.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	protected static $__loaded = false;

	/**
	 * The domain directory.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private static $domains;

	/**
	 * The options storage array
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected static $options = array();

	/**
	 * The options whitelist/defaults.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected static $options_whitelist = array();

	/**
	 * The deprecated options and their alternatives.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected static $options_deprecated = array();

	/**
	 * The current-state option overrides.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private static $option_overrides = array();

	// =========================
	// ! Property Accessing
	// =========================

	/**
	 * Retrieve the whitelist.
	 *
	 * @internal Used by the Installer.
	 *
	 * @since 1.0.0
	 *
	 * @return array The options whitelist.
	 */
	public static function get_defaults() {
		return self::$options_whitelist;
	}

	/**
	 * Check if an option is supported.
	 *
	 * Will also udpate the option value if it was deprecated
	 * but has a sufficient alternative.
	 *
	 * @since 1.0.0
	 *
	 * @param string &$option The option name.
	 *
	 * @return bool Wether or not the option is supported.
	 */
	public static function has( &$option ) {
		if ( isset( self::$options_deprecated[ $option ] ) ) {
			$option = self::$options_deprecated[ $option ];
		}

		return isset( self::$options_whitelist[ $option ] );
	}

	/**
	 * Retrieve a option value.
	 *
	 * @since 1.0.0
	 *
	 * @param string $option       The option name.
	 * @param mixed  $default      Optional. The default value to return.
	 * @param bool   $true_value   Optional. Get the true value, bypassing any overrides.
	 * @param bool   $has_override Optional. By-reference boolean to identify if an override exists.
	 *
	 * @return mixed The property value.
	 */
	public static function get( $option, $default = null, $true_value = false, &$has_override = null ) {
		// Trigger notice error if trying to set an unsupported option
		if ( ! self::has( $option ) ) {
			trigger_error( "[Domainer] The option '{$option}' is not supported.", E_USER_NOTICE );
		}

		// Check if it's set, return it's value.
		if ( isset( self::$options[ $option ] ) ) {
			// Check if it's been overriden, use that unless otherwise requested
			$has_override = isset( self::$option_overrides[ $option ] );
			if ( $has_override && ! $true_value ) {
				$value = self::$option_overrides[ $option ];
			} else {
				$value = self::$options[ $option ];
			}
		} else {
			$value = $default;
		}

		return $value;
	}

	/**
	 * Override a option value.
	 *
	 * Will not work for $languages, that has it's own method.
	 *
	 * @since 1.0.0
	 *
	 * @param string $option The option name.
	 * @param mixed  $value  The value to assign.
	 */
	public static function set( $option, $value = null ) {
		// Trigger notice error if trying to set an unsupported option
		if ( ! self::has( $option ) ) {
			trigger_error( "[Domainer] The option '{$option}' is not supported", E_USER_NOTICE );
		}

		self::$options[ $option ] = $value;
	}

	/**
	 * Temporarily override an option value.
	 *
	 * These options will be retrieved when using get(), but will not be saved.
	 *
	 * @since 1.0.0
	 *
	 * @param string $option The option name.
	 * @param mixed  $value  The value to override with.
	 */
	public static function override( $option, $value ) {
		// Trigger notice error if trying to set an unsupported option
		if ( ! self::has( $option ) ) {
			trigger_error( "[Domainer] The option '{$option}' is not supported.", E_USER_NOTICE );
		}

		self::$options_override[ $option ] = $value;
	}

	// =========================
	// ! Domain Accessing
	// =========================

	/**
	 * Get the info for a domain.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name  The domain name to fetch.
	 * @param string $field Optional. A specific field to return.
	 *
	 * @return mixed The domain or the value of the domain's field.
	 */
	public static function get_domain( $name, $field = null ) {
		// Sanitize the name
		$name = Domain::sanitize( $name );

		// See if the domain is registered
		if ( isset( $domains[ $name ] ) ) {
			$domain = $this->domains[ $name ];

			if ( is_null( $field ) ) {
				return $domain;
			}

			return $domain->$field;
		}

		return false;
	}

	// =========================
	// ! Setup Method
	// =========================

	/**
	 * Load the relevant options.
	 *
	 * @since 1.0.0
	 *
	 * @see Registry::$__loaded to prevent unnecessary reloading.
	 * @see Registry::$options_whitelist to filter the found options.
	 * @see Registry::set() to actually set the value.
	 *
	 * @param bool $reload Should we reload the options?
	 */
	public static function load( $reload = false ) {
		if ( self::$__loaded && ! $reload ) {
			// Already did this
			return;
		}

		// Load the options
		$options = get_option( 'domainer_options' );
		foreach ( self::$options_whitelist as $option => $default ) {
			$value = $default;
			if ( isset( $options[ $option ] ) ) {
				$value = $options[ $option ];

				// Ensure the value is the same type as the default
				settype( $value, gettype( $default ) );
			}

			self::set( $option, $value );
		}

		$domains = get_option( 'domainer_domains', array() );
		foreach ( $domains as $name => $config ) {
			$config['name'] = $name;
			$this->domains[ $name ] = new Domain( $config );
		}

		// Flag that we've loaded everything
		self::$__loaded = true;
	}

	/**
	 * Save the options and domains to the database.
	 *
	 * @since 1.0.0
	 *
	 * @param string $what Optional. Save just options/domains or both (true)?
	 */
	public static function save( $what = true ) {
		if ( $what == 'options' ) {
			// Save the options
			update_option( 'domainer_options', self::$options );
		}

		if ( $what == 'domains' ) {
			$domains = array();
			foreach ( $this->domains as $domain => $object ) {
				$config = $object->dump();
				unset( $config['name'] );
				$domains[ $domain ] = $config;
			}

			// Save the domains
			update_option( 'domainer_domains', $domains );
		}
	}
}

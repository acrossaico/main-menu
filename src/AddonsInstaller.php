<?php

namespace AcrossAI_Main_Menu;

/**
 * Silent plugin install / activate / deactivate for the Add-ons page.
 *
 * Only ONE install source is supported: `source = 'wordpress.org'`.
 * The download URL is resolved via WordPress core's own `plugins_api()`
 * and handed to `Plugin_Upgrader`. This keeps the install path fully
 * inside WordPress core's trusted flow.
 *
 * Add-ons whose source is anything other than `wordpress.org` (for
 * example `github` or `freemius`) are NOT installable through this page.
 * `AddonsPageRenderer` renders those cards with an external "Get add-on"
 * link that points at the entry's `more_url`, leaving the actual install
 * to WP admin's standard Plugins → Add New → Upload Plugin flow (or to
 * the vendor's own installer).
 *
 * Rationale: WordPress.org detailed plugin guideline #8 prohibits
 * "installing plugins/themes/add-ons from non-WordPress.org servers".
 * Restricting the install code path to `wordpress.org`-sourced add-ons
 * keeps this package compliant when it ships inside a plugin distributed
 * via the WordPress.org plugin directory.
 */
class AddonsInstaller {

	/** @var array<string,array>|null Memoized get_plugins() result. */
	private static $installed_plugins = null;

	/**
	 * Install an add-on from wp.org.
	 *
	 * Returns an error result if the add-on's source is not `wordpress.org`.
	 *
	 * @return array{success:bool, message:string, plugin_file:string}
	 */
	public function install( array $addon ): array {
		$this->load_upgrader_files();

		$download_url = $this->resolve_download_url( $addon );
		if ( is_wp_error( $download_url ) ) {
			return [
				'success'     => false,
				'message'     => $download_url->get_error_message(),
				'plugin_file' => '',
			];
		}

		$skin     = new \WP_Ajax_Upgrader_Skin();
		$upgrader = new \Plugin_Upgrader( $skin );
		$result   = $upgrader->install( $download_url );

		if ( is_wp_error( $result ) ) {
			return [
				'success'     => false,
				'message'     => $result->get_error_message(),
				'plugin_file' => '',
			];
		}

		if ( $skin->get_errors()->has_errors() ) {
			return [
				'success'     => false,
				'message'     => implode( ' ', $skin->get_errors()->get_error_messages() ),
				'plugin_file' => '',
			];
		}

		self::flush_cache();
		$plugin_file = $this->find_plugin_file( $addon );

		return [
			'success'     => true,
			/* translators: %s: add-on name */
			'message'     => sprintf( __( '%s installed.', 'acrossai' ), $addon['name'] ),
			'plugin_file' => $plugin_file ?? '',
		];
	}

	/** @return array{success:bool, message:string} */
	public function activate( string $plugin_file, string $addon_name ): array {
		if ( ! function_exists( 'activate_plugin' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$result = activate_plugin( $plugin_file );
		if ( is_wp_error( $result ) ) {
			return [ 'success' => false, 'message' => $result->get_error_message() ];
		}
		return [
			'success' => true,
			/* translators: %s: add-on name */
			'message' => sprintf( __( '%s activated.', 'acrossai' ), $addon_name ),
		];
	}

	/** @return array{success:bool, message:string} */
	public function deactivate( string $plugin_file, string $addon_name ): array {
		if ( ! function_exists( 'deactivate_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		deactivate_plugins( $plugin_file );
		return [
			'success' => true,
			/* translators: %s: add-on name */
			'message' => sprintf( __( '%s deactivated.', 'acrossai' ), $addon_name ),
		];
	}

	/**
	 * Locate the installed plugin file (e.g. "acme/acme.php") for an add-on.
	 * Exact folder match only — no substring fallback. Add-ons whose extracted
	 * folder differs from the slug can declare 'install_folder' in the registry
	 * entry to override the match target.
	 */
	public function find_plugin_file( array $addon ): ?string {
		$slug = isset( $addon['slug'] ) ? (string) $addon['slug'] : '';
		if ( '' === $slug ) {
			return null;
		}
		$expected = isset( $addon['install_folder'] ) && '' !== $addon['install_folder']
			? (string) $addon['install_folder']
			: $slug;

		foreach ( array_keys( self::plugins() ) as $plugin_file ) {
			if ( explode( '/', $plugin_file )[0] === $expected ) {
				return $plugin_file;
			}
		}
		return null;
	}

	/**
	 * Whether an add-on's source is eligible for in-page install.
	 *
	 * Only `wordpress.org` is installable; every other source (github,
	 * freemius, or anything a consumer registers) is treated as
	 * external-link-only.
	 */
	public static function is_installable_source( array $addon ): bool {
		return isset( $addon['source'] ) && 'wordpress.org' === $addon['source'];
	}

	/** Invalidate the per-request get_plugins() cache after mutation. */
	public static function flush_cache(): void {
		self::$installed_plugins = null;
	}

	// -------------------------------------------------------------------------

	private function load_upgrader_files(): void {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-ajax-upgrader-skin.php';
		require_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		if ( ! class_exists( 'WP_Filesystem_Base' ) ) {
			WP_Filesystem();
		}
	}

	/** @return string|\WP_Error */
	private function resolve_download_url( array $addon ) {
		if ( ! self::is_installable_source( $addon ) ) {
			return new \WP_Error(
				'non_wporg_source',
				__( 'Only WordPress.org-hosted add-ons can be installed from this page. Use the "Get add-on" link on the card to open the add-on\'s home page and install it manually.', 'acrossai' )
			);
		}

		$info = plugins_api( 'plugin_information', [
			'slug'   => $addon['slug'],
			'fields' => [ 'sections' => false, 'reviews' => false ],
		] );
		if ( is_wp_error( $info ) ) {
			return $info;
		}
		if ( empty( $info->download_link ) ) {
			return new \WP_Error( 'no_download_link', sprintf(
				/* translators: %s: plugin slug */
				__( 'Could not retrieve download URL for %s from WordPress.org.', 'acrossai' ),
				$addon['slug']
			) );
		}
		return $info->download_link;
	}

	/** @return array<string,array> */
	private static function plugins(): array {
		if ( null === self::$installed_plugins ) {
			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			self::$installed_plugins = get_plugins();
		}
		return self::$installed_plugins;
	}
}

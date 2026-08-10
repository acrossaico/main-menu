<?php

namespace AcrossAI_Main_Menu;

/**
 * Renderer for the shared "Add-ons" submenu page under the AcrossAI parent.
 *
 * Consumer plugins extend the list by hooking `acrossai_addons`:
 *
 *   add_filter( 'acrossai_addons', function ( array $addons ): array {
 *       // wp.org-hosted add-on — installable in-page.
 *       $addons[] = array(
 *           'slug'        => 'my-free-plugin',
 *           'name'        => 'My Free Plugin',
 *           'description' => 'Short description.',
 *           'icon'        => 'https://ps.w.org/my-free-plugin/assets/icon-128x128.png',
 *           'more_url'    => 'https://wordpress.org/plugins/my-free-plugin/',
 *           'source'      => 'wordpress.org',
 *       );
 *       // Off-directory add-on (GitHub / Freemius / commercial) — renders
 *       // as an external "Get add-on" link, NOT an in-page install button.
 *       $addons[] = array(
 *           'slug'        => 'my-github-plugin',
 *           'name'        => 'My GitHub Plugin',
 *           'description' => 'Short description.',
 *           'icon'        => 'https://example.com/icon-128x128.png',
 *           'more_url'    => 'https://github.com/example/my-github-plugin',
 *           'source'      => 'github',                // anything != 'wordpress.org'
 *       );
 *       return $addons;
 *   } );
 *
 * Card rendering rules:
 *   - source === 'wordpress.org' → Install / Activate / Deactivate button
 *     (AJAX-driven, handled by AddonsAjaxHandlers).
 *   - source !== 'wordpress.org' → external "Get add-on" link pointing at
 *     the entry's `more_url` (target=_blank, rel=noopener noreferrer). No
 *     in-page install action is offered. Users install those add-ons via
 *     Plugins → Add New → Upload Plugin (or via the vendor's own
 *     installer once the paid plugin is activated).
 *
 * This split exists to comply with WordPress.org detailed plugin
 * guideline #8, which forbids "installing plugins/themes/add-ons from
 * non-WordPress.org servers" when the host plugin is distributed via
 * the WordPress.org plugin directory.
 */
class AddonsPageRenderer {

	/**
	 * Hard-coded baseline add-ons list. Consumers add more via the
	 * `acrossai_addons` filter (see class docblock).
	 */
	private const ADDONS = array(
		array(
			'slug'           => 'acrossai-abilities-manager',
			'name'           => 'AcrossAI Abilities Manager',
			'description'    => 'Discover, inspect, and manage WordPress Abilities registered on your site — browse the registry, view schemas, and control which abilities are exposed.',
			'icon'           => 'https://acrossai.co/wp-content/uploads/2026/07/acrossai-logo-2.svg',
			'more_url'       => 'https://wordpress.org/plugins/acrossai-abilities-manager/',
			'learn_more_url' => 'https://acrossai.co/abilities-manager/',
			'source'         => 'wordpress.org',
		),
		array(
			'slug'           => 'acrossai-mcp-manager',
			'name'           => 'AcrossAI MCP Manager',
			'description'    => 'Seamless integration with Model Context Protocol (MCP) servers — lets AI assistants and code editors safely access your WordPress site via secure application passwords.',
			'icon'           => 'https://acrossai.co/wp-content/uploads/2026/07/acrossai-logo-2.svg',
			'more_url'       => 'https://wordpress.org/plugins/acrossai-mcp-manager/',
			'learn_more_url' => 'https://acrossai.co/mcp-manager/',
			'source'         => 'wordpress.org',
		),
		array(
			'slug'           => 'acrossai-pro',
			'name'           => 'AcrossAI Pro',
			'description'    => 'Contributes AI-assistant connector profiles (Claude and future providers) to AcrossAI MCP Manager\'s AI Connectors tab, so operators can wire assistants\' browser custom-connector flows to this WordPress site.',
			'icon'           => 'https://acrossai.co/wp-content/uploads/2026/07/acrossai-logo-2.svg',
			'more_url'       => 'https://acrossai.co/acrossai-pro/#pricing',
			'learn_more_url' => 'https://acrossai.co/acrossai-pro/',
			'source'         => 'external',
			'install_folder' => 'acrossai-pro',
		),
	);

	/** @var AddonsInstaller */
	private $installer;

	public function __construct( AddonsInstaller $installer ) {
		$this->installer = $installer;
	}

	/**
	 * Return the filtered add-ons list. Filter runs once per request but
	 * we memoize per instance to avoid re-running it inside a render loop.
	 *
	 * @return array[]
	 */
	public function get_addons(): array {
		static $cache = null;
		if ( null !== $cache ) {
			return $cache;
		}
		/**
		 * Filter the add-ons list rendered on the AcrossAI Add-ons page.
		 *
		 * @param array $addons Array of add-on entries. See class docblock for schema.
		 */
		$addons     = apply_filters( 'acrossai_addons', self::ADDONS );
		$cache      = is_array( $addons ) ? array_values( $addons ) : [];
		return $cache;
	}

	/** Look up an add-on by slug from the filtered list. */
	public function find_addon( string $slug ): ?array {
		foreach ( $this->get_addons() as $addon ) {
			if ( isset( $addon['slug'] ) && $addon['slug'] === $slug ) {
				return $addon;
			}
		}
		return null;
	}

	/**
	 * Compute the current install/active state for an add-on.
	 *
	 * Only meaningful for `wordpress.org`-sourced add-ons — non-wp.org
	 * cards render as an external "Get add-on" link and never call this.
	 * Callers should gate on `AddonsInstaller::is_installable_source()`
	 * before consulting the return value.
	 *
	 * @return array{action:string, label:string, css_class:string}
	 */
	public function button_state_for( array $addon ): array {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$plugin_file = $this->installer->find_plugin_file( $addon );

		if ( null === $plugin_file ) {
			return [
				'action'    => 'install',
				'label'     => __( 'Install', 'acrossai' ),
				'css_class' => 'button button-primary',
			];
		}
		if ( is_plugin_active( $plugin_file ) ) {
			return [
				'action'    => 'running',
				'label'     => __( 'Running', 'acrossai' ),
				'css_class' => 'acrossai-addons__status acrossai-addons__status--active',
			];
		}
		return [
			'action'    => 'activate',
			'label'     => __( 'Activate', 'acrossai' ),
			'css_class' => 'button button-primary',
		];
	}

	public function render(): void {
		echo '<div class="wrap acrossai-addons">';
		echo '<h1>' . esc_html__( 'Add-ons', 'acrossai' ) . '</h1>';
		echo '<p class="description">' . esc_html__( 'Extend AcrossAI with add-ons. WordPress.org-hosted add-ons install in place; add-ons distributed elsewhere open on the vendor\'s site so you can install them via Plugins → Add New → Upload Plugin.', 'acrossai' ) . '</p>';

		$this->render_styles();

		echo '<div class="acrossai-addons__grid">';
		foreach ( $this->get_addons() as $addon ) {
			$this->render_card( $addon );
		}
		echo '</div>';

		$this->render_script();
		echo '</div>';
	}

	// -------------------------------------------------------------------------

	private function render_card( array $addon ): void {
		$slug = isset( $addon['slug'] ) ? (string) $addon['slug'] : '';

		printf( '<div class="acrossai-addons__card" data-slug="%s">', esc_attr( $slug ) );

		echo '<div class="acrossai-addons__card-head">';
		if ( ! empty( $addon['icon'] ) ) {
			printf(
				'<img class="acrossai-addons__icon" src="%s" alt="" />',
				esc_url( $addon['icon'] )
			);
		} else {
			echo '<div class="acrossai-addons__icon acrossai-addons__icon--placeholder" aria-hidden="true"></div>';
		}
		printf( '<h3 class="acrossai-addons__name">%s</h3>', esc_html( $addon['name'] ?? $slug ) );
		echo '</div>';

		printf( '<p class="acrossai-addons__desc">%s</p>', esc_html( $addon['description'] ?? '' ) );

		echo '<div class="acrossai-addons__actions">';
		$is_installed = null !== $this->installer->find_plugin_file( $addon );
		if ( AddonsInstaller::is_installable_source( $addon ) || $is_installed ) {
			// wp.org source → Install/Activate/Running.
			// Non-wp.org but already installed (e.g. paid add-on uploaded via
			// Plugins → Add New) → Activate/Running, but never Install.
			$this->render_install_button( $addon );
		} else {
			// Non-wp.org and not installed → external "Get add-on ↗" link.
			$this->render_external_link( $addon );
		}
		if ( ! empty( $addon['learn_more_url'] ) ) {
			printf(
				'<a class="acrossai-addons__more" href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
				esc_url( $addon['learn_more_url'] ),
				esc_html__( 'Learn more', 'acrossai' )
			);
		}
		echo '</div>';

		echo '<div class="acrossai-addons__notice" role="status" aria-live="polite"></div>';
		echo '</div>';
	}

	/** Render the AJAX-driven Install / Activate button, OR a non-clickable "Running" badge when the plugin is active (wp.org add-ons only). */
	private function render_install_button( array $addon ): void {
		$state = $this->button_state_for( $addon );
		if ( 'running' === $state['action'] ) {
			printf(
				'<span class="%s" aria-label="%s"><span class="acrossai-addons__status-dot" aria-hidden="true"></span>%s</span>',
				esc_attr( $state['css_class'] ),
				esc_attr__( 'Plugin is active', 'acrossai' ),
				esc_html( $state['label'] )
			);
			return;
		}
		printf(
			'<button type="button" class="acrossai-addons__btn %s" data-action="%s">%s</button>',
			esc_attr( $state['css_class'] ),
			esc_attr( $state['action'] ),
			esc_html( $state['label'] )
		);
	}

	/** Render the external "Get add-on" link (non-wp.org add-ons). */
	private function render_external_link( array $addon ): void {
		$href = ! empty( $addon['more_url'] ) ? (string) $addon['more_url'] : '';
		if ( '' === $href ) {
			// No destination configured — render a disabled affordance so
			// the card doesn't look broken. Consumers should always set
			// `more_url` for non-wp.org sources.
			printf(
				'<span class="acrossai-addons__btn button" aria-disabled="true">%s</span>',
				esc_html__( 'Unavailable', 'acrossai' )
			);
			return;
		}
		printf(
			'<a class="acrossai-addons__btn button button-primary" href="%s" target="_blank" rel="noopener noreferrer">%s <span aria-hidden="true">↗</span></a>',
			esc_url( $href ),
			esc_html__( 'Get add-on', 'acrossai' )
		);
	}

	private function render_styles(): void {
		echo <<<'CSS'
<style>
.acrossai-addons__grid {
	display: grid;
	grid-template-columns: repeat( 3, minmax( 0, 1fr ) );
	gap: 20px;
	margin-top: 20px;
}
@media ( max-width: 1100px ) {
	.acrossai-addons__grid { grid-template-columns: repeat( 2, minmax( 0, 1fr ) ); }
}
@media ( max-width: 720px ) {
	.acrossai-addons__grid { grid-template-columns: minmax( 0, 1fr ); }
}
.acrossai-addons__card {
	background: #fff;
	border: 1px solid #dcdcde;
	border-radius: 6px;
	padding: 20px;
	display: flex;
	flex-direction: column;
	box-shadow: 0 1px 2px rgba( 0, 0, 0, 0.04 );
	transition: box-shadow 0.15s ease-in-out, transform 0.15s ease-in-out;
}
.acrossai-addons__card:hover {
	box-shadow: 0 4px 12px rgba( 0, 0, 0, 0.08 );
	transform: translateY( -1px );
}
.acrossai-addons__card-head {
	display: flex;
	align-items: center;
	gap: 14px;
	margin-bottom: 12px;
}
.acrossai-addons__icon {
	width: 56px;
	height: 56px;
	flex: 0 0 56px;
	border-radius: 6px;
	object-fit: contain;
	background: #f0f0f1;
	padding: 6px;
	box-sizing: border-box;
}
.acrossai-addons__icon--placeholder {
	background: linear-gradient( 135deg, #2271b1 0%, #135e96 100% );
}
.acrossai-addons__name {
	margin: 0;
	font-size: 15px;
	line-height: 1.3;
	color: #1d2327;
}
.acrossai-addons__desc {
	flex: 1;
	color: #50575e;
	font-size: 13px;
	line-height: 1.5;
	margin: 0 0 16px;
}
.acrossai-addons__actions {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 12px;
}
.acrossai-addons__btn[disabled] {
	opacity: 0.6;
	cursor: progress;
}
.acrossai-addons__status {
	display: inline-flex;
	align-items: center;
	gap: 6px;
	padding: 4px 10px;
	border-radius: 999px;
	font-size: 12px;
	font-weight: 600;
	line-height: 1;
	cursor: default;
	user-select: none;
}
.acrossai-addons__status--active {
	background: #edfaf1;
	color: #00733b;
	border: 1px solid #b8e6c8;
}
.acrossai-addons__status-dot {
	width: 8px;
	height: 8px;
	border-radius: 50%;
	background: #00a854;
	box-shadow: 0 0 0 3px rgba( 0, 168, 84, 0.15 );
}
.acrossai-addons__more {
	font-size: 12px;
	text-decoration: none;
}
.acrossai-addons__notice {
	margin-top: 10px;
	font-size: 12px;
	min-height: 0;
}
.acrossai-addons__notice--success { color: #007a3d; }
.acrossai-addons__notice--error   { color: #b32d2e; }
</style>
CSS;
	}

	private function render_script(): void {
		$config = [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( AddonsAjaxHandlers::NONCE_ACTION ),
			'i18n'    => [
				'installing'   => __( 'Installing…', 'acrossai' ),
				'activating'   => __( 'Activating…', 'acrossai' ),
				'deactivating' => __( 'Deactivating…', 'acrossai' ),
				'error'        => __( 'Something went wrong. Please try again.', 'acrossai' ),
			],
		];
		printf(
			'<script id="acrossai-addons-config" type="application/json">%s</script>',
			wp_json_encode( $config )
		);
		echo <<<'JS'
<script>
( function () {
	var cfg = JSON.parse( document.getElementById( 'acrossai-addons-config' ).textContent );

	document.querySelectorAll( '.acrossai-addons__card' ).forEach( function ( card ) {
		var btn = card.querySelector( '.acrossai-addons__btn' );
		if ( ! btn ) {
			return;
		}
		btn.addEventListener( 'click', function () {
			var action = btn.dataset.action;
			var slug   = card.dataset.slug;
			var busy   = { install: cfg.i18n.installing, activate: cfg.i18n.activating, deactivate: cfg.i18n.deactivating }[ action ] || '';
			var notice = card.querySelector( '.acrossai-addons__notice' );

			btn.disabled = true;
			var originalLabel = btn.textContent;
			btn.textContent = busy;
			notice.className = 'acrossai-addons__notice';
			notice.textContent = '';

			var body = new FormData();
			body.append( 'action', 'acrossai_addons_' + action );
			body.append( 'nonce', cfg.nonce );
			body.append( 'slug', slug );

			fetch( cfg.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body } )
				.then( function ( r ) { return r.json(); } )
				.then( function ( json ) {
					if ( ! json || typeof json !== 'object' ) {
						throw new Error( 'invalid response' );
					}
					var msg = ( json.data && json.data.message ) || '';
					if ( json.success ) {
						notice.textContent = msg;
						notice.className = 'acrossai-addons__notice acrossai-addons__notice--success';
						var state = json.data && json.data.state;
						if ( state ) {
							btn.dataset.action = state.action;
							btn.textContent = state.label;
							btn.className = 'acrossai-addons__btn ' + state.css_class;
						} else {
							btn.textContent = originalLabel;
						}
					} else {
						notice.textContent = msg || cfg.i18n.error;
						notice.className = 'acrossai-addons__notice acrossai-addons__notice--error';
						btn.textContent = originalLabel;
					}
				} )
				.catch( function () {
					notice.textContent = cfg.i18n.error;
					notice.className = 'acrossai-addons__notice acrossai-addons__notice--error';
					btn.textContent = originalLabel;
				} )
				.finally( function () {
					btn.disabled = false;
				} );
		} );
	} );
} )();
</script>
JS;
	}
}

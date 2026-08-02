<?php

namespace AcrossAI_Main_Menu;

/**
 * Registers the AcrossAI parent menu and its shared submenus (Add-ons, Settings, Consultations, Notices).
 *
 * Parent menu is registered at the default admin_menu priority (10).
 * Settings is registered at priority 20 so it lands right after the Dashboard.
 * Notices is registered at priority 25. It always registers the page (so
 * direct URL visits render the "All clear" empty state); when no notices
 * exist the sidebar entry is hidden via a small inline CSS block printed
 * to admin_head.
 * Add-ons is registered at priority 1000.
 * Consultations is registered at priority 1010 so it lands after Add-ons.
 */
class MenuRegistrar {

	/** @var string */
	private $parent_slug;

	/** @var string */
	private $addons_slug;

	/** @var string */
	private $settings_slug;

	/** @var string */
	private $consultations_slug;

	/** @var string */
	private $notices_slug;

	/** @var DashboardRenderer */
	private $dashboard_renderer;

	/** @var AddonsPageRenderer */
	private $addons_renderer;

	/** @var TabbedPageRenderer */
	private $settings_renderer;

	/** @var ConsultationsPageRenderer */
	private $consultations_renderer;

	/** @var NoticesPageRenderer */
	private $notices_renderer;

	/** @var Notices */
	private $notices;

	/** @var string|null Hook suffix returned by the Settings add_submenu_page(). */
	private $hook_suffix = null;

	/** @var string|null Hook suffix returned by the Add-ons add_submenu_page(). */
	private $addons_hook_suffix = null;

	/** @var string|null Hook suffix returned by the Consultations add_submenu_page(). */
	private $consultations_hook_suffix = null;

	/** @var string|null Hook suffix returned by the Notices add_submenu_page(). */
	private $notices_hook_suffix = null;

	public function __construct(
		string $parent_slug,
		string $addons_slug,
		string $settings_slug,
		string $consultations_slug,
		string $notices_slug,
		DashboardRenderer $dashboard_renderer,
		AddonsPageRenderer $addons_renderer,
		TabbedPageRenderer $settings_renderer,
		ConsultationsPageRenderer $consultations_renderer,
		NoticesPageRenderer $notices_renderer,
		Notices $notices
	) {
		$this->parent_slug            = $parent_slug;
		$this->addons_slug            = $addons_slug;
		$this->settings_slug          = $settings_slug;
		$this->consultations_slug     = $consultations_slug;
		$this->notices_slug           = $notices_slug;
		$this->dashboard_renderer     = $dashboard_renderer;
		$this->addons_renderer        = $addons_renderer;
		$this->settings_renderer      = $settings_renderer;
		$this->consultations_renderer = $consultations_renderer;
		$this->notices_renderer       = $notices_renderer;
		$this->notices                = $notices;
	}

	public function register_parent(): void {
		add_menu_page(
			__( 'AcrossAI', 'acrossai' ),
			__( 'AcrossAI', 'acrossai' ),
			'manage_options',
			$this->parent_slug,
			[ $this->dashboard_renderer, 'render' ]
		);
	}

	public function register_addons_submenu(): void {
		$this->addons_hook_suffix = add_submenu_page(
			$this->parent_slug,
			__( 'Add-ons', 'acrossai' ),
			__( 'Add-ons', 'acrossai' ),
			'install_plugins',
			$this->addons_slug,
			[ $this->addons_renderer, 'render' ]
		);
	}

	public function register_settings_submenu(): void {
		$this->hook_suffix = add_submenu_page(
			$this->parent_slug,
			__( 'Settings', 'acrossai' ),
			__( 'Settings', 'acrossai' ),
			'manage_options',
			$this->settings_slug,
			[ $this->settings_renderer, 'render' ]
		);
	}

	public function register_consultations_submenu(): void {
		$this->consultations_hook_suffix = add_submenu_page(
			$this->parent_slug,
			__( 'Consultations', 'acrossai' ),
			__( 'Consultations', 'acrossai' ),
			'manage_options',
			$this->consultations_slug,
			[ $this->consultations_renderer, 'render' ]
		);
	}

	/**
	 * Register the Notices submenu. The page is always attached to the
	 * AcrossAI parent so both the sidebar link and a direct visit to
	 * admin.php?page={notices_slug} work. When notices exist, the menu
	 * title includes a count bubble styled the same as WordPress core's
	 * plugin-update badge. When there are no notices, the sidebar row is
	 * hidden via a small inline CSS block printed on admin_head — the page
	 * itself remains reachable and renders the "All clear" empty state.
	 *
	 * We use CSS rather than remove_submenu_page(), because removing the
	 * entry desyncs get_admin_page_parent() / $_registered_pages and causes
	 * user_can_access_admin_page() to reject the request with "not allowed".
	 */
	public function register_notices_submenu(): void {
		$count = $this->notices->count();

		if ( $count > 0 ) {
			$menu_title = sprintf(
				/* translators: %s: notice count HTML bubble */
				__( 'Notices %s', 'acrossai' ),
				'<span class="awaiting-mod acrossai-notices-count count-' . absint( $count ) . '"><span class="acrossai-notices-count-num">'
					. esc_html( number_format_i18n( $count ) )
					. '</span></span>'
			);
		} else {
			$menu_title = __( 'Notices', 'acrossai' );
		}

		$this->notices_hook_suffix = add_submenu_page(
			$this->parent_slug,
			__( 'Notices', 'acrossai' ),
			$menu_title,
			'manage_options',
			$this->notices_slug,
			[ $this->notices_renderer, 'render' ]
		);

		if ( 0 === $count ) {
			add_action( 'admin_head', [ $this, 'print_notices_hide_css' ] );
		}
	}

	/**
	 * Hide the empty Notices row from the AcrossAI submenu. Matches the
	 * sidebar <li> whose anchor points at the notices page and collapses it.
	 * Uses :has() (supported in all evergreen browsers) to hit the <li>
	 * rather than the anchor, so no orphan bullet is left behind.
	 */
	public function print_notices_hide_css(): void {
		$href = 'admin.php?page=' . $this->notices_slug;
		printf(
			"<style id=\"acrossai-hide-empty-notices\">#adminmenu .wp-submenu li:has(> a[href=\"%s\"]){display:none;}</style>\n",
			esc_attr( $href )
		);
	}

	public function get_hook_suffix(): ?string {
		return $this->hook_suffix;
	}

	public function get_addons_hook_suffix(): ?string {
		return $this->addons_hook_suffix;
	}

	public function get_consultations_hook_suffix(): ?string {
		return $this->consultations_hook_suffix;
	}

	public function get_notices_hook_suffix(): ?string {
		return $this->notices_hook_suffix;
	}
}

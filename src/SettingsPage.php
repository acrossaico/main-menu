<?php

namespace AcrossAI_Main_Menu;

/**
 * Public entrypoint for the AcrossAI parent menu + Settings page.
 *
 * Usage from a consumer plugin — preferred:
 *   \AcrossAI_Main_Menu\SettingsPage::instance();
 *
 * Back-compat (still supported):
 *   new \AcrossAI_Main_Menu\SettingsPage();
 *
 * The class is bundled inside every AcrossAI consumer plugin via Composer and
 * deduped process-wide by jetpack-autoloader. Autoloading dedupes the class
 * definition, not construction — every consumer still runs their own boot
 * call. The singleton below ensures that admin_menu hooks (and the shared
 * renderer) are wired exactly once no matter how many consumers boot us.
 *
 * Registers:
 *   - "AcrossAI"      top-level menu (slug: acrossai)               — the Dashboard landing page
 *   - "Settings"      submenu        (slug: acrossai-settings,      admin_menu priority 20)
 *   - "Notices"       submenu        (slug: acrossai-notices,       admin_menu priority 25 — only when notices exist)
 *   - "Add-ons"       submenu        (slug: acrossai-addons,        admin_menu priority 1000)
 *   - "Consultations" submenu        (slug: acrossai-consultations, admin_menu priority 1010)
 *
 * The Settings page renders a standard WordPress Settings API form. Consumer
 * plugins extend it by calling register_setting(), add_settings_section(), and
 * add_settings_field() against the 'acrossai-settings' page slug / option_group,
 * or against a tab-scoped slug obtained from get_settings_renderer()->tab_page_slug().
 * See README.md.
 *
 * Consumer plugins push system notices onto the shared Notices submenu via
 * the `acrossai_notices` filter — see the Notices class docblock for the
 * notice record shape.
 */
class SettingsPage {

	const PARENT_SLUG        = 'acrossai';
	const ADDONS_SLUG        = 'acrossai-addons';
	const SETTINGS_SLUG      = 'acrossai-settings';
	const CONSULTATIONS_SLUG = 'acrossai-consultations';
	const NOTICES_SLUG       = 'acrossai-notices';

	/** @var self|null Shared instance — first construction wins for both `instance()` and `new`. */
	private static $_instance = null;

	/**
	 * Returns the shared SettingsPage instance, constructing it on first call.
	 * Preferred over `new SettingsPage()` — subsequent `new` calls short-circuit
	 * without re-wiring hooks, but calling instance() directly is clearer.
	 */
	public static function instance(): self {
		if ( null === self::$_instance ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Returns the Settings page renderer so consumer plugins can call
	 * $renderer->tab_page_slug( 'my-tab' ) when registering sections for
	 * a specific tab. Returns null if no SettingsPage has been constructed
	 * yet in this request.
	 */
	public static function get_settings_renderer(): ?SettingsPageRenderer {
		return self::$_instance ? self::$_instance->settings_renderer : null;
	}

	/**
	 * Returns the shared Notices registry so consumer plugins can query the
	 * current notice list directly if they need to. Returns null if no
	 * SettingsPage has been constructed yet in this request. In practice
	 * plugins should just add their entries via the `acrossai_notices` filter.
	 */
	public static function get_notices(): ?Notices {
		return self::$_instance ? self::$_instance->notices : null;
	}

	/** @var MenuRegistrar */
	private $menu_registrar;

	/** @var DashboardRenderer */
	private $dashboard_renderer;

	/** @var AddonsPageRenderer */
	private $addons_renderer;

	/** @var SettingsPageRenderer */
	private $settings_renderer;

	/** @var AddonsInstaller */
	private $addons_installer;

	/** @var AddonsAjaxHandlers */
	private $addons_ajax;

	/** @var ConsultationsPageRenderer */
	private $consultations_renderer;

	/** @var Notices */
	private $notices;

	/** @var NoticesPageRenderer */
	private $notices_renderer;

	/** @var NoticesAjaxHandlers */
	private $notices_ajax;

	/** @var SummaryNoticeEmitter */
	private $notices_summary;

	public function __construct() {
		if ( null !== self::$_instance ) {
			// Legacy `new` path from a second consumer — first construction wins.
			return;
		}
		self::$_instance = $this;

		$this->dashboard_renderer = new DashboardRenderer();
		$this->addons_installer   = new AddonsInstaller();
		$this->addons_renderer    = new AddonsPageRenderer( $this->addons_installer );
		$this->addons_ajax        = new AddonsAjaxHandlers( $this->addons_installer, $this->addons_renderer );
		$this->settings_renderer      = new SettingsPageRenderer();
		$this->consultations_renderer = new ConsultationsPageRenderer();
		$this->notices                = new Notices();
		$this->notices_renderer       = new NoticesPageRenderer( $this->notices );
		$this->notices_ajax           = new NoticesAjaxHandlers( $this->notices );
		$this->notices_summary        = new SummaryNoticeEmitter( $this->notices, self::NOTICES_SLUG );
		$this->menu_registrar         = new MenuRegistrar(
			self::PARENT_SLUG,
			self::ADDONS_SLUG,
			self::SETTINGS_SLUG,
			self::CONSULTATIONS_SLUG,
			self::NOTICES_SLUG,
			$this->dashboard_renderer,
			$this->addons_renderer,
			$this->settings_renderer,
			$this->consultations_renderer,
			$this->notices_renderer,
			$this->notices
		);

		add_action( 'admin_menu', [ $this->menu_registrar, 'register_parent' ] );
		add_action( 'admin_menu', [ $this->menu_registrar, 'register_settings_submenu' ], 20 );
		add_action( 'admin_menu', [ $this->menu_registrar, 'register_notices_submenu' ], 25 );
		add_action( 'admin_menu', [ $this->menu_registrar, 'register_addons_submenu' ], 1000 );
		add_action( 'admin_menu', [ $this->menu_registrar, 'register_consultations_submenu' ], 1010 );

		add_action( 'wp_ajax_acrossai_addons_install',    [ $this->addons_ajax, 'install' ] );
		add_action( 'wp_ajax_acrossai_addons_activate',   [ $this->addons_ajax, 'activate' ] );
		add_action( 'wp_ajax_acrossai_addons_deactivate', [ $this->addons_ajax, 'deactivate' ] );

		add_action( 'admin_notices', [ $this->notices_summary, 'render' ] );
		add_action( 'wp_ajax_acrossai_notices_dismiss_summary', [ $this->notices_ajax, 'dismiss_summary' ] );
	}
}

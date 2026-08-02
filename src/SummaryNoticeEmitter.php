<?php

namespace AcrossAI_Main_Menu;

/**
 * Prints a single WordPress-native "AcrossAI needs your attention" summary
 * notice at the top of every admin page whenever at least one notice is
 * registered through the `acrossai_notices` filter.
 *
 * Uses WP core's `.notice.is-dismissible` — WordPress adds the ✕ button and
 * hides the notice for the current page load automatically. We layer a small
 * fingerprint-based persistence on top: on dismiss, an inline JS hook POSTs
 * the current notice-id fingerprint to NoticesAjaxHandlers::dismiss_summary,
 * which stores it in user meta. Next request, if the fingerprint still
 * matches (no new notices, none fixed), we skip rendering entirely.
 *
 * We do NOT render on:
 *   - The Notices submenu itself (the full list is already there)
 *   - Requests without a logged-in user (no admin_notices anyway)
 *   - Users without `manage_options` (no menu access → nothing to click into)
 */
class SummaryNoticeEmitter {

	/** @var Notices */
	private $notices;

	/** @var string Full admin URL of the Notices submenu page. */
	private $notices_url;

	/** @var string Slug of the Notices submenu page — used to suppress on that screen. */
	private $notices_slug;

	public function __construct( Notices $notices, string $notices_slug ) {
		$this->notices      = $notices;
		$this->notices_slug = $notices_slug;
		$this->notices_url  = admin_url( 'admin.php?page=' . rawurlencode( $notices_slug ) );
	}

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Skip on the Notices page itself — the full list is right there.
		// Nonce check not applicable: this is a screen-detection GET param
		// on a top-level admin page load, not a form submission.
		$current_page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( $this->notices_slug === $current_page ) {
			return;
		}

		$count = $this->notices->count();
		if ( 0 === $count ) {
			return;
		}

		$fingerprint = $this->notices->fingerprint();
		$dismissed   = (string) get_user_meta( get_current_user_id(), NoticesAjaxHandlers::USER_META_KEY, true );
		if ( '' !== $dismissed && $dismissed === $fingerprint ) {
			return;
		}

		$this->print_notice( $count, $fingerprint );
	}

	private function print_notice( int $count, string $fingerprint ): void {
		$nonce       = wp_create_nonce( NoticesAjaxHandlers::NONCE_ACTION );
		$count_html  = '<strong>' . esc_html( number_format_i18n( $count ) ) . '</strong>';
		$brand_html  = '<strong>' . esc_html__( 'AcrossAI', 'acrossai' ) . '</strong>';
		$sentence    = sprintf(
			/* translators: 1: brand name (already wrapped in <strong>), 2: notification count (already wrapped in <strong>) */
			_n(
				'%1$s has %2$s notification for your attention.',
				'%1$s has %2$s notifications for your attention.',
				$count,
				'acrossai'
			),
			$brand_html,
			$count_html
		);
		?>
<div class="notice notice-warning is-dismissible acrossai-summary-notice"
	data-acrossai-fp="<?php echo esc_attr( $fingerprint ); ?>"
	data-acrossai-nonce="<?php echo esc_attr( $nonce ); ?>">
	<p>
		<?php echo wp_kses( $sentence, [ 'strong' => [] ] ); ?>
		&nbsp;
		<a href="<?php echo esc_url( $this->notices_url ); ?>">
			<?php esc_html_e( 'View notices', 'acrossai' ); ?> &rarr;
		</a>
	</p>
</div>
<script>
(function () {
	if (window.__acrossaiSummaryDismissBound) { return; }
	window.__acrossaiSummaryDismissBound = true;
	document.addEventListener('click', function (e) {
		var btn = e.target.closest('.acrossai-summary-notice .notice-dismiss');
		if (!btn) return;
		var notice = btn.closest('.acrossai-summary-notice');
		if (!notice) return;
		var fp    = notice.getAttribute('data-acrossai-fp');
		var nonce = notice.getAttribute('data-acrossai-nonce');
		if (!fp || !nonce) return;
		var body = new URLSearchParams();
		body.set('action', 'acrossai_notices_dismiss_summary');
		body.set('nonce', nonce);
		body.set('fp', fp);
		fetch(<?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: body.toString(),
		});
	});
})();
</script>
		<?php
	}
}

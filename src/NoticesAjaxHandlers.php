<?php

namespace AcrossAI_Main_Menu;

/**
 * wp_ajax_* handler for the AcrossAI top-of-page summary notice.
 *
 * The summary notice (SummaryNoticeEmitter) uses WordPress's native
 * `is-dismissible` classes so WP core adds the ✕ button and hides the notice
 * for the current page load. We also want the dismissal to persist until the
 * notice set changes, so the emitter's inline JS calls this endpoint with the
 * current notice-id fingerprint, and we store that fingerprint in a per-user
 * meta key. On subsequent page loads, if the fingerprint still matches, the
 * emitter skips rendering entirely.
 */
class NoticesAjaxHandlers {

	const NONCE_ACTION  = 'acrossai_notices';
	const USER_META_KEY = '_acrossai_notices_summary_fp';

	/** @var Notices */
	private $notices;

	public function __construct( Notices $notices ) {
		$this->notices = $notices;
	}

	/** wp_ajax_acrossai_notices_dismiss_summary */
	public function dismiss_summary(): void {
		check_ajax_referer( self::NONCE_ACTION, 'nonce' );
		// Match the capability SummaryNoticeEmitter checks before rendering.
		// Only users who can actually see the summary should be able to dismiss it.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [
				'message' => __( 'You do not have permission for this action.', 'acrossai' ),
				'code'    => 'forbidden',
			] );
		}

		$fp = isset( $_POST['fp'] ) ? sanitize_key( wp_unslash( $_POST['fp'] ) ) : '';
		if ( '' === $fp ) {
			wp_send_json_error( [
				'message' => __( 'Missing fingerprint.', 'acrossai' ),
				'code'    => 'missing_fp',
			] );
		}

		// Defense-in-depth: only accept a fingerprint that matches the current
		// notice set. A crafted POST with an unrelated hash would otherwise
		// mask a future notice set that happens to hash to the same value.
		if ( $fp !== $this->notices->fingerprint() ) {
			wp_send_json_error( [
				'message' => __( 'Fingerprint does not match the current notice set.', 'acrossai' ),
				'code'    => 'stale_fp',
			] );
		}

		update_user_meta( get_current_user_id(), self::USER_META_KEY, $fp );

		wp_send_json_success( [ 'fp' => $fp ] );
	}
}

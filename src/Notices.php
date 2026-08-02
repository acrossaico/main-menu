<?php

namespace AcrossAI_Main_Menu;

/**
 * Cross-plugin notice registry for the AcrossAI parent menu.
 *
 * Plugins register notices through a single filter:
 *
 *   add_filter( 'acrossai_notices', function ( array $notices ): array {
 *       if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
 *           $notices[] = [
 *               'id'      => 'wp_cron_disabled',
 *               'title'   => __( 'WP-Cron is disabled', 'my-plugin' ),
 *               'message' => __( 'Scheduled tasks will not run until you configure a real system cron to hit wp-cron.php.', 'my-plugin' ),
 *               'type'    => 'warning',           // error | warning | info | success (default: warning)
 *               'source'  => __( 'My Plugin', 'my-plugin' ),  // optional label shown on the notice card
 *               'action'  => [                    // optional CTA
 *                   'label' => __( 'Read the docs', 'my-plugin' ),
 *                   'url'   => 'https://developer.wordpress.org/plugins/cron/',
 *               ],
 *           ];
 *       }
 *       return $notices;
 *   } );
 *
 * The Notices submenu under the AcrossAI parent menu is only registered
 * when at least one notice is present. On every other admin page,
 * SummaryNoticeEmitter prints a single WordPress-native dismissible notice
 * pointing to the Notices page.
 */
class Notices {

	const FILTER = 'acrossai_notices';

	/** Allowed values for the `type` field. First entry is the default. */
	private const TYPES = [ 'warning', 'error', 'info', 'success' ];

	/** @var array<int,array>|null Memoized per-request result. */
	private $cache = null;

	/**
	 * Return the normalized notice list from the filter.
	 *
	 * Each returned entry is guaranteed to have:
	 *   id (string, non-empty), title (string), message (string),
	 *   type (one of TYPES), source (string, may be ''),
	 *   action (array{label:string,url:string}|null)
	 *
	 * @return array<int,array{id:string,title:string,message:string,type:string,source:string,action:?array{label:string,url:string}}>
	 */
	public function all(): array {
		if ( null !== $this->cache ) {
			return $this->cache;
		}

		/**
		 * Filter the AcrossAI notice list.
		 *
		 * @param array $notices Array of notice records. See class docblock for shape.
		 */
		$raw = apply_filters( self::FILTER, [] );
		if ( ! is_array( $raw ) ) {
			$this->cache = [];
			return $this->cache;
		}

		$normalized = [];
		$seen_ids   = [];
		foreach ( $raw as $entry ) {
			$notice = $this->normalize( $entry );
			if ( null === $notice ) {
				continue;
			}
			if ( isset( $seen_ids[ $notice['id'] ] ) ) {
				continue; // First registration of a given id wins.
			}
			$seen_ids[ $notice['id'] ] = true;
			$normalized[]              = $notice;
		}

		$this->cache = $normalized;
		return $this->cache;
	}

	public function count(): int {
		return count( $this->all() );
	}

	public function has_notices(): bool {
		return $this->count() > 0;
	}

	/**
	 * Deterministic fingerprint of the currently-registered notice ids. Used
	 * by SummaryNoticeEmitter to decide whether the user's "dismissed
	 * summary" state still applies: if a new notice arrives or an existing
	 * one is fixed and drops out, the fingerprint changes and the summary
	 * re-appears.
	 *
	 * Returns an empty string when there are no notices (avoids fingerprinting
	 * the empty set — the summary is never shown in that case anyway).
	 */
	public function fingerprint(): string {
		$ids = array_column( $this->all(), 'id' );
		if ( empty( $ids ) ) {
			return '';
		}
		sort( $ids, SORT_STRING );
		return sha1( implode( '|', $ids ) );
	}

	/**
	 * Reset the per-request cache. Useful in tests or when a plugin registers
	 * notices after the first read (rare — the filter runs on admin_menu).
	 */
	public function flush(): void {
		$this->cache = null;
	}

	/**
	 * Coerce a filter entry into the normalized shape, or return null if it
	 * is missing required fields.
	 *
	 * @return array{id:string,title:string,message:string,type:string,source:string,action:?array{label:string,url:string}}|null
	 */
	private function normalize( $entry ): ?array {
		if ( ! is_array( $entry ) ) {
			return null;
		}
		$id = isset( $entry['id'] ) ? (string) $entry['id'] : '';
		if ( '' === $id ) {
			return null;
		}
		$message = isset( $entry['message'] ) ? (string) $entry['message'] : '';
		$title   = isset( $entry['title'] ) ? (string) $entry['title'] : '';
		if ( '' === $message && '' === $title ) {
			return null;
		}
		$type = isset( $entry['type'] ) ? (string) $entry['type'] : self::TYPES[0];
		if ( ! in_array( $type, self::TYPES, true ) ) {
			$type = self::TYPES[0];
		}

		$action = null;
		if ( isset( $entry['action'] ) && is_array( $entry['action'] ) ) {
			$label = isset( $entry['action']['label'] ) ? (string) $entry['action']['label'] : '';
			$url   = isset( $entry['action']['url'] ) ? (string) $entry['action']['url'] : '';
			if ( '' !== $label && '' !== $url ) {
				$action = [ 'label' => $label, 'url' => $url ];
			}
		}

		return [
			'id'      => $id,
			'title'   => $title,
			'message' => $message,
			'type'    => $type,
			'source'  => isset( $entry['source'] ) ? (string) $entry['source'] : '',
			'action'  => $action,
		];
	}
}

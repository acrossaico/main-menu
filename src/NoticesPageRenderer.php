<?php

namespace AcrossAI_Main_Menu;

/**
 * Renderer for the "Notices" submenu page under the AcrossAI parent.
 *
 * Reads normalized notice records from Notices::all() and renders them as
 * styled cards matching the dashboard aesthetic (Space Grotesk + IBM Plex
 * Sans, dotted radial background, per-type accent stripe).
 *
 * This page is the always-visible collector: even if the user dismissed the
 * top-of-page summary emitted by SummaryNoticeEmitter, every current notice
 * still shows here.
 *
 * Self-contained: emits its own <style> block so it has no external CSS
 * dependencies beyond the Google Fonts import (matches DashboardRenderer /
 * ConsultationsPageRenderer).
 */
class NoticesPageRenderer {

	/** @var Notices */
	private $notices;

	public function __construct( Notices $notices ) {
		$this->notices = $notices;
	}

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$this->print_styles();
		$this->print_markup();
	}

	private function print_styles(): void {
		?>
<style>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=IBM+Plex+Sans:wght@400;500;600&display=swap');

.acai-notices, .acai-notices *, .acai-notices *::before, .acai-notices *::after { box-sizing: border-box; }
.acai-notices {
	--accent: #5538EE;
	--accent-hover: #4429D6;
	--type-warning: #B45309;
	--type-warning-soft: #FEF3C7;
	--type-error: #B91C1C;
	--type-error-soft: #FEE2E2;
	--type-info: #1D4ED8;
	--type-info-soft: #DBEAFE;
	--type-success: #047857;
	--type-success-soft: #D1FAE5;
	font-family: 'IBM Plex Sans', system-ui, sans-serif;
	color: #15161B;
	background: #F4F5F7;
	min-height: calc(100vh - 32px);
	-webkit-font-smoothing: antialiased;
	background-image: radial-gradient(circle at 1px 1px, rgba(21,22,27,0.04) 1px, transparent 0);
	background-size: 22px 22px;
	margin: 10px -20px 0 -20px;
}
.acai-notices a { text-decoration: none; }
.acai-notices h1, .acai-notices h2, .acai-notices h3, .acai-notices p { margin: 0; }

.acai-notices__shell { max-width: 980px; margin: 0 auto; padding: 28px 32px 72px; }

.acai-notices__head { text-align: center; padding: 32px 0 30px; }
.acai-notices__pill {
	display: inline-flex; align-items: center; gap: 8px;
	padding: 6px 14px; border-radius: 999px;
	background: #fff; border: 1px solid #E4E6EB;
	font-size: 13px; font-weight: 500; color: #4A4E5A;
	margin-bottom: 18px;
}
.acai-notices__pill-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--accent); }
.acai-notices__head h1 {
	font-family: 'Space Grotesk', sans-serif; font-weight: 700;
	font-size: clamp(28px, 3.4vw, 40px); line-height: 1.08; letter-spacing: -0.02em;
	max-width: 640px; margin: 0 auto 14px; text-wrap: balance;
}
.acai-notices__head p {
	font-size: 15.5px; line-height: 1.6; color: #565B68;
	max-width: 560px; margin: 0 auto;
}

.acai-notices__list { display: flex; flex-direction: column; gap: 14px; margin-top: 8px; }

.acai-notice {
	background: #fff; border: 1px solid #E7E9EE; border-radius: 14px;
	padding: 18px 22px 18px 22px; position: relative;
	display: grid; grid-template-columns: 44px 1fr auto; gap: 16px; align-items: flex-start;
	transition: box-shadow .18s, border-color .18s;
}
.acai-notice:hover { box-shadow: 0 12px 32px -22px rgba(21,22,27,0.35); }

.acai-notice__icon {
	width: 40px; height: 40px; border-radius: 11px;
	display: flex; align-items: center; justify-content: center; flex: 0 0 40px;
}
.acai-notice--warning .acai-notice__icon { background: var(--type-warning-soft); color: var(--type-warning); }
.acai-notice--error   .acai-notice__icon { background: var(--type-error-soft);   color: var(--type-error); }
.acai-notice--info    .acai-notice__icon { background: var(--type-info-soft);    color: var(--type-info); }
.acai-notice--success .acai-notice__icon { background: var(--type-success-soft); color: var(--type-success); }

.acai-notice--warning { border-left: 3px solid var(--type-warning); }
.acai-notice--error   { border-left: 3px solid var(--type-error); }
.acai-notice--info    { border-left: 3px solid var(--type-info); }
.acai-notice--success { border-left: 3px solid var(--type-success); }

.acai-notice__body { min-width: 0; }
.acai-notice__title {
	font-family: 'Space Grotesk', sans-serif; font-weight: 600; font-size: 16.5px;
	color: #15161B; margin: 0 0 4px; letter-spacing: -0.005em;
}
.acai-notice__message { font-size: 14.5px; line-height: 1.6; color: #565B68; text-wrap: pretty; }
.acai-notice__meta {
	display: inline-flex; align-items: center; gap: 6px;
	font-size: 12px; font-weight: 500; color: #6B6F7B;
	margin-top: 8px;
}
.acai-notice__meta-dot { width: 5px; height: 5px; border-radius: 50%; background: #C4C7CE; }

.acai-notice__cta { align-self: center; }
.acai-notice__cta a {
	display: inline-flex; align-items: center; gap: 6px;
	padding: 8px 14px; border-radius: 8px;
	background: var(--accent); color: #fff;
	font-size: 13.5px; font-weight: 600;
	transition: background .15s, transform .15s;
	white-space: nowrap;
}
.acai-notice__cta a:hover { background: var(--accent-hover); transform: translateY(-1px); }
.acai-notice__cta a svg { flex: 0 0 14px; }

.acai-notices__empty {
	background: #fff; border: 1px solid #E7E9EE; border-radius: 14px;
	padding: 48px 24px; text-align: center; color: #6B6F7B;
	font-size: 15px; line-height: 1.6;
}
.acai-notices__empty strong { display: block; color: #15161B; font-size: 17px; margin-bottom: 6px; font-family: 'Space Grotesk', sans-serif; font-weight: 600; }

@media (max-width: 600px) {
	.acai-notice { grid-template-columns: 40px 1fr; }
	.acai-notice__cta { grid-column: 1 / -1; padding-left: 56px; }
}
</style>
		<?php
	}

	private function print_markup(): void {
		$notices = $this->notices->all();
		?>
<div class="acai-notices">
	<div class="acai-notices__shell">

		<header class="acai-notices__head">
			<div class="acai-notices__pill">
				<span class="acai-notices__pill-dot"></span>
				<?php esc_html_e( 'AcrossAI system notices', 'acrossai' ); ?>
			</div>
			<h1><?php esc_html_e( 'Notices', 'acrossai' ); ?></h1>
			<p>
				<?php
				$total    = count( $notices );
				$sentence = sprintf(
					/* translators: %s: number of active notices (already wrapped in <strong>) */
					_n(
						'%s item across your AcrossAI plugins needs your attention.',
						'%s items across your AcrossAI plugins need your attention.',
						$total,
						'acrossai'
					),
					'<strong>' . esc_html( number_format_i18n( $total ) ) . '</strong>'
				);
				echo wp_kses( $sentence, [ 'strong' => [] ] );
				?>
			</p>
		</header>

		<?php if ( empty( $notices ) ) : ?>
			<div class="acai-notices__empty">
				<strong><?php esc_html_e( 'All clear.', 'acrossai' ); ?></strong>
				<?php esc_html_e( 'No plugins are reporting issues right now.', 'acrossai' ); ?>
			</div>
		<?php else : ?>
			<div class="acai-notices__list">
				<?php foreach ( $notices as $notice ) : ?>
					<?php $this->render_notice( $notice ); ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

	</div>
</div>
		<?php
	}

	private function render_notice( array $notice ): void {
		$type_class = 'acai-notice acai-notice--' . sanitize_html_class( $notice['type'] );
		?>
<div class="<?php echo esc_attr( $type_class ); ?>">
	<div class="acai-notice__icon"><?php $this->render_icon( $notice['type'] ); ?></div>
	<div class="acai-notice__body">
		<?php if ( '' !== $notice['title'] ) : ?>
			<h3 class="acai-notice__title"><?php echo esc_html( $notice['title'] ); ?></h3>
		<?php endif; ?>
		<?php if ( '' !== $notice['message'] ) : ?>
			<p class="acai-notice__message"><?php echo wp_kses_post( $notice['message'] ); ?></p>
		<?php endif; ?>
		<?php if ( '' !== $notice['source'] ) : ?>
			<div class="acai-notice__meta">
				<span class="acai-notice__meta-dot"></span>
				<?php echo esc_html( $notice['source'] ); ?>
			</div>
		<?php endif; ?>
	</div>
	<?php if ( null !== $notice['action'] ) : ?>
		<div class="acai-notice__cta">
			<a href="<?php echo esc_url( $notice['action']['url'] ); ?>">
				<?php echo esc_html( $notice['action']['label'] ); ?>
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
			</a>
		</div>
	<?php endif; ?>
</div>
		<?php
	}

	private function render_icon( string $type ): void {
		switch ( $type ) {
			case 'error':
				echo '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6"/><path d="M9 9l6 6"/></svg>';
				break;
			case 'info':
				echo '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01"/><path d="M11 12h1v4h1"/></svg>';
				break;
			case 'success':
				echo '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>';
				break;
			case 'warning':
			default:
				echo '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3 3.7 2 18a2 2 0 0 0 1.7 3h16.6A2 2 0 0 0 22 18L13.7 3.7a2 2 0 0 0-3.4 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>';
		}
	}
}

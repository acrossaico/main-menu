<?php

namespace AcrossAI_Main_Menu;

/**
 * Renderer for the "Consultations" submenu page under the AcrossAI parent.
 *
 * Embeds the Calendly inline widget so admins can book a consultation
 * without leaving WP Admin.
 */
class ConsultationsPageRenderer {

	private const CALENDLY_URL = 'https://calendly.com/acrossai/using-ai-in-wordpress?hide_event_type_details=1&hide_gdpr_banner=1';

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		echo '<div class="wrap acrossai-consultations">';
		echo '<h1>' . esc_html__( 'Consultations', 'acrossai' ) . '</h1>';
		echo '<p class="description">' . esc_html__( 'Book a consultation on using AI in WordPress.', 'acrossai' ) . '</p>';

		printf(
			'<div class="calendly-inline-widget" data-url="%s" style="min-width:320px;height:700px;"></div>',
			esc_url( self::CALENDLY_URL )
		);
		echo '<script type="text/javascript" src="https://assets.calendly.com/assets/external/widget.js" async></script>';

		echo '</div>';
	}
}

<?php

namespace AcrossAI_Main_Menu;

/**
 * Renderer for the "Consultations" submenu page under the AcrossAI parent.
 *
 * Embeds the Calendly inline widget in a marketing-style layout so admins
 * can book a free 30-minute one-on-one consultation without leaving WP Admin.
 *
 * Self-contained styling — matches the DashboardRenderer look (Space Grotesk
 * + IBM Plex Sans, purple accent, dotted radial background).
 */
class ConsultationsPageRenderer {

	private const CALENDLY_URL = 'https://calendly.com/acrossai/using-ai-in-wordpress?hide_event_type_details=1&hide_gdpr_banner=1';

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

.acai-consult, .acai-consult *, .acai-consult *::before, .acai-consult *::after { box-sizing: border-box; }
.acai-consult {
	--accent: #5538EE;
	font-family: 'IBM Plex Sans', system-ui, sans-serif;
	color: #15161B;
	background: #F4F5F7;
	min-height: calc(100vh - 32px);
	-webkit-font-smoothing: antialiased;
	background-image: radial-gradient(circle at 1px 1px, rgba(21,22,27,0.04) 1px, transparent 0);
	background-size: 22px 22px;
	margin: 10px -20px 0 -20px;
}
.acai-consult a { text-decoration: none; }
.acai-consult h1, .acai-consult h2, .acai-consult h3, .acai-consult p { margin: 0; }
.acai-consult__shell { max-width: 1180px; margin: 0 auto; padding: 28px 32px 72px; }

/* Layout */
.acai-consult__layout {
	display: grid;
	grid-template-columns: minmax(0, 1fr) minmax(0, 1.15fr);
	gap: 40px;
	align-items: start;
}
@media (max-width: 1024px) {
	.acai-consult__layout { grid-template-columns: 1fr; gap: 28px; }
}

/* Intro column */
.acai-consult__intro { position: relative; padding: 8px 4px 0; }
.acai-consult__pill {
	display: inline-flex; align-items: center; gap: 8px;
	padding: 6px 14px; border-radius: 999px;
	background: #fff; border: 1px solid #E4E6EB;
	font-size: 13px; font-weight: 500; color: #4A4E5A;
	margin-bottom: 22px;
}
.acai-consult__pill-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--accent); }
.acai-consult h1 {
	font-family: 'Space Grotesk', sans-serif; font-weight: 700;
	font-size: clamp(30px, 3.4vw, 42px); line-height: 1.08; letter-spacing: -0.02em;
	margin: 0 0 18px; max-width: 520px; text-wrap: balance;
}
.acai-consult__lede {
	font-size: 16px; line-height: 1.65; color: #565B68;
	max-width: 520px; margin: 0 0 44px; text-wrap: pretty;
}

.acai-consult__topics { list-style: none; padding: 0; margin: 0 0 28px; display: grid; gap: 10px; }
.acai-consult__topics li {
	display: flex; align-items: flex-start; gap: 12px;
	background: #fff; border: 1px solid #E7E9EE; border-radius: 12px;
	padding: 14px 16px; font-size: 14.5px; line-height: 1.5; color: #2C3038;
}
.acai-consult__topics li svg {
	flex: 0 0 22px; width: 22px; height: 22px;
	color: var(--accent); margin-top: 1px;
}

.acai-consult__host {
	display: flex; align-items: center; gap: 14px;
	background: #15161B; color: #EDEEF1; border-radius: 16px; padding: 18px 20px;
}
.acai-consult__host-avatar {
	width: 52px; height: 52px; border-radius: 12px; flex: 0 0 52px;
	overflow: hidden; background: linear-gradient(135deg, var(--accent), #7A5EFF);
	box-shadow: 0 0 0 2px rgba(255,255,255,0.08);
}
.acai-consult__host-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
.acai-consult__host-name { font-family: 'Space Grotesk', sans-serif; font-weight: 600; font-size: 15px; }
.acai-consult__host-role { font-size: 13px; color: #A6A9B4; margin-top: 2px; }

/* Widget card */
.acai-consult__widget-card {
	background: #fff; border: 1px solid #E7E9EE; border-radius: 18px;
	padding: 8px; overflow: hidden;
	box-shadow: 0 20px 44px -30px rgba(21,22,27,0.35);
	position: sticky; top: 46px;
}
.acai-consult__widget-card .calendly-inline-widget {
	border-radius: 12px; overflow: hidden;
}

.acai-consult__foot {
	text-align: center; margin: 64px auto 24px; padding: 20px 24px;
	font-size: 13.5px; color: #8A8E99; max-width: 640px;
}
.acai-consult__foot a { color: var(--accent); font-weight: 500; }
</style>
		<?php
	}

	private function print_markup(): void {
		?>
<div class="acai-consult">
	<div class="acai-consult__shell">

		<div class="acai-consult__layout">

			<section class="acai-consult__intro">
				<div class="acai-consult__pill">
					<span class="acai-consult__pill-dot"></span>
					<?php esc_html_e( 'Book a free session with the AcrossAI team', 'acrossai' ); ?>
				</div>
				<h1><?php esc_html_e( 'A free 30-minute, one-on-one call about using AI in WordPress.', 'acrossai' ); ?></h1>
				<p class="acai-consult__lede"><?php esc_html_e( 'Bring your questions about the AcrossAI stack — Abilities, MCP, Add-ons — or your broader AI-on-WordPress plans. We\'ll dig into your setup live and leave you with concrete next steps. No sales pitch, no obligation.', 'acrossai' ); ?></p>

				<ul class="acai-consult__topics">
					<li>
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg>
						<span><?php esc_html_e( 'Setting up Abilities, MCP, and Add-ons on your site — and how the three fit together.', 'acrossai' ); ?></span>
					</li>
					<li>
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg>
						<span><?php esc_html_e( 'Exposing your WordPress data safely to AI clients over MCP — permissions, scopes, audit.', 'acrossai' ); ?></span>
					</li>
					<li>
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg>
						<span><?php esc_html_e( 'Debugging a specific integration, plugin, or client project you\'re shipping.', 'acrossai' ); ?></span>
					</li>
				</ul>

				<div class="acai-consult__host">
					<div class="acai-consult__host-avatar">
						<img src="https://acrossai.co/wp-content/uploads/2026/07/WhatsApp20Image202026-07-2320at2016.43.03.jpeg" alt="<?php esc_attr_e( 'Deepak Gupta, CEO of AcrossAI', 'acrossai' ); ?>" />
					</div>
					<div>
						<div class="acai-consult__host-name"><?php esc_html_e( 'Deepak Gupta', 'acrossai' ); ?></div>
						<div class="acai-consult__host-role"><?php esc_html_e( 'CEO, AcrossAI · WordPress engineer for 11+ years', 'acrossai' ); ?></div>
					</div>
				</div>
			</section>

			<aside class="acai-consult__widget-card">
				<div class="calendly-inline-widget" data-url="<?php echo esc_url( self::CALENDLY_URL ); ?>" style="min-width:320px;height:700px;"></div>
			</aside>

		</div>

		<p class="acai-consult__foot">
			<?php
			printf(
				/* translators: %s: email link */
				esc_html__( 'Can\'t find a time that works? Email %s and we\'ll sort something out.', 'acrossai' ),
				'<a href="mailto:deepak@acrossai.co">deepak@acrossai.co</a>'
			);
			?>
		</p>

	</div>
</div>
<script type="text/javascript" src="https://assets.calendly.com/assets/external/widget.js" async></script>
		<?php
	}
}

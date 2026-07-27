<?php

namespace AcrossAI_Main_Menu;

/**
 * Renderer for the "Consultations" submenu page under the AcrossAI parent.
 *
 * Presents a single-page CTA that links out to Calendly in a new tab —
 * no third-party iframe or script is loaded inside wp-admin.
 *
 * Self-contained styling — matches the DashboardRenderer look (Space Grotesk
 * + IBM Plex Sans, purple accent, dotted radial background, hero glow).
 */
class ConsultationsPageRenderer {

	private const CALENDLY_URL = 'https://calendly.com/acrossai/using-ai-in-wordpress';

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
	--accent-hover: #4429D6;
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
.acai-consult h1, .acai-consult h2, .acai-consult h3 { margin: 0; }
.acai-consult p { margin: 0; }
.acai-consult p.acai-consult__lede { margin: 0 auto 44px; }
.acai-consult p.acai-consult__foot { margin: 40px auto 0; }
.acai-consult__shell { max-width: 1080px; margin: 0 auto; padding: 28px 32px 64px; }

/* Hero */
.acai-consult__hero { position: relative; padding: 44px 0 40px; text-align: center; overflow: visible; }
.acai-consult__glow {
	position: absolute; top: -10px; left: 50%; transform: translateX(-50%);
	width: 520px; height: 320px;
	background: radial-gradient(ellipse at center, color-mix(in oklab, var(--accent) 26%, transparent), transparent 70%);
	filter: blur(24px); z-index: 0; pointer-events: none;
	animation: acaiConsultFloat 9s ease-in-out infinite;
}
.acai-consult__hero-inner { position: relative; z-index: 1; }
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
	font-size: clamp(28px, 3.4vw, 40px); line-height: 1.08; letter-spacing: -0.02em;
	margin: 0 auto 18px; max-width: 720px; text-wrap: balance;
}
.acai-consult__lede {
	font-size: clamp(15px, 1.6vw, 17px); line-height: 1.6; color: #565B68;
	max-width: 600px; margin: 0 auto 44px; text-wrap: pretty; text-align: center;
}

.acai-consult__cta {
	display: inline-flex; align-items: center; gap: 10px;
	background: var(--accent); color: #fff;
	font-family: 'Space Grotesk', sans-serif; font-weight: 600; font-size: 15.5px;
	padding: 14px 24px; border-radius: 12px;
	box-shadow: 0 12px 28px -14px rgba(85,56,238,0.55);
	transition: background 0.15s ease, transform 0.15s ease, box-shadow 0.15s ease;
}
.acai-consult__cta:hover { background: var(--accent-hover); color: #fff; transform: translateY(-1px); box-shadow: 0 16px 32px -14px rgba(85,56,238,0.6); }
.acai-consult__cta:focus { outline: none; color: #fff; }
.acai-consult__cta:focus-visible { outline: 2px solid var(--accent); outline-offset: 3px; }
.acai-consult__cta svg { width: 17px; height: 17px; }

.acai-consult__meta {
	display: flex; justify-content: center; gap: 22px; flex-wrap: wrap;
	margin-top: 16px; font-size: 13.5px; color: #6E727C;
}
.acai-consult__meta span { display: inline-flex; align-items: center; gap: 6px; }
.acai-consult__meta svg { width: 15px; height: 15px; color: var(--accent); }

@keyframes acaiConsultFloat { 0%,100% { transform: translate3d(-50%,0,0); } 50% { transform: translate3d(-50%,-14px,0); } }

/* Body — two columns */
.acai-consult__body {
	display: grid; grid-template-columns: minmax(0, 1.35fr) minmax(0, 1fr);
	gap: 24px; margin-top: 12px; align-items: stretch;
}
@media (max-width: 900px) { .acai-consult__body { grid-template-columns: 1fr; } }

.acai-consult__section-head {
	font-family: 'Space Grotesk', sans-serif; font-weight: 600; font-size: 13px;
	letter-spacing: 0.08em; text-transform: uppercase; color: #8A8E99;
	margin-bottom: 14px;
}

.acai-consult__topics { list-style: none; padding: 0; margin: 0; display: grid; gap: 10px; }
.acai-consult__topics li {
	display: flex; align-items: flex-start; gap: 12px;
	background: #fff; border: 1px solid #E7E9EE; border-radius: 14px;
	padding: 14px 16px; font-size: 14.5px; line-height: 1.5; color: #2C3038;
	transition: border-color .15s, transform .15s, box-shadow .15s;
}
.acai-consult__topics li:hover {
	border-color: color-mix(in oklab, var(--accent) 35%, #E7E9EE);
	transform: translateY(-2px);
	box-shadow: 0 12px 28px -22px rgba(21,22,27,0.35);
}
.acai-consult__topics li svg {
	flex: 0 0 22px; width: 22px; height: 22px;
	color: var(--accent); margin-top: 1px;
}

.acai-consult__host-card {
	background: #15161B; color: #EDEEF1; border-radius: 18px; padding: 24px;
	display: flex; flex-direction: column;
	box-shadow: 0 18px 40px -24px rgba(21,22,27,0.45);
	position: relative; overflow: hidden;
}
.acai-consult__host-card::before {
	content: ''; position: absolute; top: -60px; right: -60px;
	width: 200px; height: 200px; border-radius: 50%;
	background: radial-gradient(circle, color-mix(in oklab, var(--accent) 55%, transparent), transparent 70%);
	pointer-events: none;
}
.acai-consult__host-row { display: flex; align-items: center; gap: 14px; position: relative; }
.acai-consult__host-avatar {
	width: 54px; height: 54px; border-radius: 14px; flex: 0 0 54px;
	overflow: hidden; background: linear-gradient(135deg, var(--accent), #7A5EFF);
	box-shadow: 0 0 0 2px rgba(255,255,255,0.08);
}
.acai-consult__host-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
.acai-consult__host-name { font-family: 'Space Grotesk', sans-serif; font-weight: 600; font-size: 16px; }
.acai-consult__host-role { font-size: 13px; color: #A6A9B4; margin-top: 3px; }
.acai-consult__host-quote {
	position: relative; margin-top: 18px; padding-top: 18px;
	border-top: 1px solid rgba(255,255,255,0.08);
	font-size: 14px; line-height: 1.55; color: #C7CAD1; font-style: italic;
}

.acai-consult__foot {
	text-align: center; margin: 40px auto 0; padding: 0 24px;
	font-size: 13.5px; color: #8A8E99; max-width: 560px;
}
.acai-consult__foot a { color: var(--accent); font-weight: 500; }
</style>
		<?php
	}

	private function print_markup(): void {
		?>
<div class="acai-consult">
	<div class="acai-consult__shell">

		<section class="acai-consult__hero">
			<div class="acai-consult__glow" aria-hidden="true"></div>
			<div class="acai-consult__hero-inner">
				<div class="acai-consult__pill">
					<span class="acai-consult__pill-dot"></span>
					<?php esc_html_e( 'Book a free session with the AcrossAI team', 'acrossai' ); ?>
				</div>
				<h1><?php esc_html_e( 'A free 30-minute call about using AI in WordPress.', 'acrossai' ); ?></h1>
				<p class="acai-consult__lede"><?php esc_html_e( 'Bring your questions about the AcrossAI stack — Abilities, MCP, Add-ons — or your broader AI-on-WordPress plans. We\'ll dig into your setup live and leave you with concrete next steps. No sales pitch, no obligation.', 'acrossai' ); ?></p>

				<a class="acai-consult__cta" href="<?php echo esc_url( self::CALENDLY_URL ); ?>" target="_blank" rel="noopener noreferrer">
					<span><?php esc_html_e( 'Book on Calendly', 'acrossai' ); ?></span>
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7"/><path d="M8 7h9v9"/></svg>
				</a>

				<div class="acai-consult__meta">
					<span>
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
						<?php esc_html_e( '30 minutes', 'acrossai' ); ?>
					</span>
					<span>
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 10a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
						<?php esc_html_e( 'One-on-one', 'acrossai' ); ?>
					</span>
					<span>
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></svg>
						<?php esc_html_e( 'Pick any open slot', 'acrossai' ); ?>
					</span>
				</div>
			</div>
		</section>

		<div class="acai-consult__body">
			<div>
				<div class="acai-consult__section-head"><?php esc_html_e( 'What we can cover', 'acrossai' ); ?></div>
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
			</div>

			<aside>
				<div class="acai-consult__section-head"><?php esc_html_e( 'Your host', 'acrossai' ); ?></div>
				<div class="acai-consult__host-card">
					<div class="acai-consult__host-row">
						<div class="acai-consult__host-avatar">
							<img src="https://acrossai.co/wp-content/uploads/2026/07/WhatsApp20Image202026-07-2320at2016.43.03.jpeg" alt="<?php esc_attr_e( 'Deepak Gupta, CEO of AcrossAI', 'acrossai' ); ?>" />
						</div>
						<div>
							<div class="acai-consult__host-name"><?php esc_html_e( 'Deepak Gupta', 'acrossai' ); ?></div>
							<div class="acai-consult__host-role"><?php esc_html_e( 'CEO, AcrossAI · WordPress engineer 11+ years', 'acrossai' ); ?></div>
						</div>
					</div>
					<div class="acai-consult__host-quote">
						<?php esc_html_e( '"Come with a real problem — a stuck integration, a design decision, or just the AI-on-WordPress question you can\'t Google. We\'ll work through it together."', 'acrossai' ); ?>
					</div>
				</div>
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
		<?php
	}
}

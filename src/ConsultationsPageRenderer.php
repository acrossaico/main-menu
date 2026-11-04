<?php

namespace AcrossAI_Main_Menu;

/**
 * Renderer for the "Consultations" submenu page under the AcrossAI parent.
 *
 * Presents the two routes a consultation can take and links out to the
 * matching door on https://acrossai.co/consultations/ in a new tab — no
 * third-party booking widget, iframe or script is loaded inside wp-admin.
 *
 * The two doors are the same page with a query parameter:
 *
 *   ?type=free   a request form, answered by hand, usually in two working days
 *   ?type=paid   a live Google Calendar, self-serve, Pro licence required
 *
 * Both links are shown to everyone. This package cannot tell whether the site
 * holds a Pro licence — the Freemius SDK was retired and nothing here talks to
 * acrossai-pro — so the page states what each route needs and lets the visitor
 * choose, rather than guessing and hiding the wrong one.
 *
 * Self-contained styling — matches the DashboardRenderer look (Space Grotesk
 * + IBM Plex Sans, purple accent, dotted radial background, hero glow).
 */
class ConsultationsPageRenderer {

	private const CONSULTATIONS_URL = 'https://acrossai.co/consultations/';
	private const FREE_URL          = self::CONSULTATIONS_URL . '?type=free';
	private const PAID_URL          = self::CONSULTATIONS_URL . '?type=paid';

	/**
	 * Host portrait. The 225x300 derivative, not the 1200x1600 original —
	 * this renders at 54px and the full-size file is roughly 2 megapixels.
	 */
	private const HOST_AVATAR = 'https://acrossai.co/wp-content/uploads/2026/07/WhatsApp20Image202026-07-2320at2016.43.03-225x300.jpeg.webp';

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
.acai-consult p.acai-consult__lede { margin: 0 auto 40px; }
.acai-consult__shell { max-width: 1080px; margin: 0 auto; padding: 28px 32px 64px; }

/* Hero */
.acai-consult__hero { position: relative; padding: 44px 0 8px; text-align: center; overflow: visible; }
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
	max-width: 620px; margin: 0 auto 40px; text-wrap: pretty; text-align: center;
}

@keyframes acaiConsultFloat { 0%,100% { transform: translate3d(-50%,0,0); } 50% { transform: translate3d(-50%,-14px,0); } }

/* The two doors.
   Free first: it is the one most people reading this can actually use, and an
   upsell placed ahead of a free offer reads as a toll gate. The Pro card still
   carries the visual weight — accent border, filled button — so it does not
   get lost by coming second. */
.acai-consult__doors {
	display: grid; grid-template-columns: repeat(2, minmax(0, 1fr));
	gap: 20px; margin-bottom: 44px; align-items: stretch;
}
@media (max-width: 900px) { .acai-consult__doors { grid-template-columns: 1fr; } }

.acai-consult__door {
	display: flex; flex-direction: column; text-align: left;
	background: #fff; border: 1px solid #E7E9EE; border-radius: 18px;
	padding: 24px;
	transition: border-color .15s, transform .15s, box-shadow .15s;
}
.acai-consult__door:hover {
	transform: translateY(-2px);
	box-shadow: 0 16px 34px -24px rgba(21,22,27,0.4);
}
.acai-consult__door--pro {
	border-color: color-mix(in oklab, var(--accent) 38%, #E7E9EE);
	box-shadow: 0 18px 40px -30px rgba(85,56,238,0.5);
}
.acai-consult__door--pro:hover { border-color: color-mix(in oklab, var(--accent) 55%, #E7E9EE); }

.acai-consult__door-tag {
	display: inline-flex; align-items: center; gap: 7px; align-self: flex-start;
	font-family: 'Space Grotesk', sans-serif; font-size: 11.5px; font-weight: 600;
	letter-spacing: 0.08em; text-transform: uppercase;
	padding: 5px 11px; border-radius: 999px; margin-bottom: 14px;
	background: #F1F2F5; color: #6E727C;
}
.acai-consult__door--pro .acai-consult__door-tag {
	background: color-mix(in oklab, var(--accent) 12%, #fff);
	color: var(--accent);
}
.acai-consult__door-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }

.acai-consult__door h2 {
	font-family: 'Space Grotesk', sans-serif; font-weight: 600;
	font-size: 19px; line-height: 1.25; letter-spacing: -0.01em; margin-bottom: 8px;
}
.acai-consult__door > p {
	font-size: 14.5px; line-height: 1.55; color: #565B68;
}

.acai-consult__door-meta {
	list-style: none; padding: 0; margin: 16px 0 22px;
	display: flex; flex-wrap: wrap; gap: 8px 18px;
	font-size: 13px; color: #6E727C;
}
.acai-consult__door-meta li { display: inline-flex; align-items: center; gap: 6px; }
.acai-consult__door-meta svg { width: 14px; height: 14px; color: var(--accent); flex: none; }

/* margin-top:auto keeps both buttons on the same baseline when the two cards
   carry different amounts of copy.

   The transparent border is load-bearing, not decoration: the ghost variant
   below adds a 1px border, and without a matching one here the filled button
   is 2px shorter. Pushed to the bottom by margin-top:auto, that difference
   shows up as the two buttons sitting 2px out of line. */
.acai-consult__cta {
	display: inline-flex; align-items: center; justify-content: center; gap: 10px;
	margin-top: auto; width: 100%;
	background: var(--accent); color: #fff;
	border: 1px solid transparent;
	font-family: 'Space Grotesk', sans-serif; font-weight: 600; font-size: 15px;
	padding: 13px 22px; border-radius: 12px;
	box-shadow: 0 12px 28px -14px rgba(85,56,238,0.55);
	transition: background 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
}
.acai-consult__cta:hover { background: var(--accent-hover); color: #fff; box-shadow: 0 16px 32px -14px rgba(85,56,238,0.6); }
.acai-consult__cta:focus { outline: none; color: #fff; }
.acai-consult__cta:focus-visible { outline: 2px solid var(--accent); outline-offset: 3px; }
.acai-consult__cta svg { width: 16px; height: 16px; }

.acai-consult__cta--ghost {
	background: #fff; color: var(--accent);
	border: 1px solid color-mix(in oklab, var(--accent) 32%, #E7E9EE);
	box-shadow: none;
}
.acai-consult__cta--ghost:hover {
	background: color-mix(in oklab, var(--accent) 6%, #fff);
	color: var(--accent);
	border-color: color-mix(in oklab, var(--accent) 50%, #E7E9EE);
	box-shadow: none;
}
.acai-consult__cta--ghost:focus { color: var(--accent); }

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
/* object-position is this photograph's focal point: the source is a portrait
   of a figure in a landscape, and a default centre crop loses the face. */
.acai-consult__host-avatar img {
	width: 100%; height: 100%; display: block;
	object-fit: cover; object-position: 50% 38%;
}
.acai-consult__host-name { font-family: 'Space Grotesk', sans-serif; font-weight: 600; font-size: 16px; }
.acai-consult__host-role { font-size: 13px; color: #A6A9B4; margin-top: 3px; }
.acai-consult__host-quote {
	position: relative; margin-top: 18px; padding-top: 18px;
	border-top: 1px solid rgba(255,255,255,0.08);
	font-size: 14px; line-height: 1.55; color: #C7CAD1; font-style: italic;
}

@media (prefers-reduced-motion: reduce) {
	.acai-consult__glow { animation: none; }
	.acai-consult__door, .acai-consult__topics li { transition: none; }
	.acai-consult__door:hover, .acai-consult__topics li:hover { transform: none; }
}
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
					<?php esc_html_e( 'Talk to the AcrossAI team', 'acrossai' ); ?>
				</div>
				<h1><?php esc_html_e( 'Two ways to get a straight answer.', 'acrossai' ); ?></h1>
				<p class="acai-consult__lede"><?php esc_html_e( 'Bring a real problem — a stuck integration, a permissions question, a design decision. Tell us what you are working on and we will come back with a time, or book yourself straight into a live calendar with an AcrossAI Pro licence.', 'acrossai' ); ?></p>
			</div>
		</section>

		<div class="acai-consult__doors">

			<div class="acai-consult__door">
				<span class="acai-consult__door-tag">
					<span class="acai-consult__door-dot"></span>
					<?php esc_html_e( 'Free intro call', 'acrossai' ); ?>
				</span>
				<h2><?php esc_html_e( 'Tell us what you are building', 'acrossai' ); ?></h2>
				<p><?php esc_html_e( 'Send a few details and we will come back with a time that works. Every request is read by a person — no licence needed, no sales pitch.', 'acrossai' ); ?></p>
				<ul class="acai-consult__door-meta">
					<li>
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
						<?php esc_html_e( '30 minutes', 'acrossai' ); ?>
					</li>
					<li>
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 10a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
						<?php esc_html_e( 'One-on-one', 'acrossai' ); ?>
					</li>
					<li>
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5h16v12H8l-4 4z"/></svg>
						<?php esc_html_e( 'Reply within two working days', 'acrossai' ); ?>
					</li>
				</ul>
				<a class="acai-consult__cta acai-consult__cta--ghost" href="<?php echo esc_url( self::FREE_URL ); ?>" target="_blank" rel="noopener noreferrer">
					<span><?php esc_html_e( 'Request a free call', 'acrossai' ); ?></span>
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7"/><path d="M8 7h9v9"/></svg>
				</a>
			</div>

			<div class="acai-consult__door acai-consult__door--pro">
				<span class="acai-consult__door-tag">
					<span class="acai-consult__door-dot"></span>
					<?php esc_html_e( 'Included with AcrossAI Pro', 'acrossai' ); ?>
				</span>
				<h2><?php esc_html_e( 'Book a priority session', 'acrossai' ); ?></h2>
				<p><?php esc_html_e( 'Skip the queue and pick your own slot in a live calendar. Slots open from two hours ahead and confirm instantly, as often as your licence is active.', 'acrossai' ); ?></p>
				<ul class="acai-consult__door-meta">
					<li>
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
						<?php esc_html_e( '30 minutes', 'acrossai' ); ?>
					</li>
					<li>
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="6" width="13" height="12" rx="2"/><path d="m16 11 5-3v8l-5-3"/></svg>
						<?php esc_html_e( 'Google Meet', 'acrossai' ); ?>
					</li>
					<li>
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
						<?php esc_html_e( 'Pro licence required', 'acrossai' ); ?>
					</li>
				</ul>
				<a class="acai-consult__cta" href="<?php echo esc_url( self::PAID_URL ); ?>" target="_blank" rel="noopener noreferrer">
					<span><?php esc_html_e( 'Book a priority session', 'acrossai' ); ?></span>
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7"/><path d="M8 7h9v9"/></svg>
				</a>
			</div>

		</div>

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
							<img src="<?php echo esc_url( self::HOST_AVATAR ); ?>" width="54" height="54" loading="lazy" decoding="async" alt="<?php esc_attr_e( 'Deepak Gupta, CEO of AcrossAI', 'acrossai' ); ?>" />
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

	</div>
</div>
		<?php
	}
}

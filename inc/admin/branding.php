<?php
/**
 * Shared admin branding — dark mode CN brand experience on all theme admin pages.
 *
 * @package cn-starter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue shared admin branding styles on all CN admin pages.
 */
function cn_admin_branding_styles( $hook ) {
	$cn_pages = array(
		'toplevel_page_cn-starter',
		'cn-starter_page_cn-setup-wizard',
		'cn-starter_page_cn-figma-tokens',
		'cn-starter_page_cn-design-tokens',
		'cn-starter_page_cn-block-generator',
		'cn-starter_page_cn-docs',
	);

	// Also match the ACF options page (theme settings).
	$screen = get_current_screen();
	if ( $screen && 'admin_page_cn-theme-settings' === $screen->id ) {
		$cn_pages[] = 'admin_page_cn-theme-settings';
	}

	if ( ! in_array( $hook, $cn_pages, true ) ) {
		return;
	}

	$css = '
	/* ===== CN Dark Mode Admin Shell ===== */

	/* Override WP admin background for CN pages */
body.cn-admin-page #wpbody-content { background: #0d1117; }
body.cn-admin-page #wpbody-content .wrap { background: #0d1117; }

/* Dark page background applied to the content area */
.cn-admin-dark .wrap {
	background: #0d1117;
	color: #e6edf3;
	min-height: calc(100vh - 32px);
	padding: 0;
}

/* Hide the default WP h1 on CN pages (we use custom headers) */
.cn-admin-dark .wrap > h1:first-child {
	display: none;
}

/* ===== Typography ===== */
.cn-admin-dark .wrap h1,
.cn-admin-dark .wrap h2,
.cn-admin-dark .wrap h3 {
	color: #f0f6fc;
}
.cn-admin-dark .wrap h1 { font-size: 22px; font-weight: 700; }
.cn-admin-dark .wrap h2 { font-size: 17px; font-weight: 600; }
.cn-admin-dark .wrap h3 { font-size: 15px; font-weight: 600; }
.cn-admin-dark .wrap p,
.cn-admin-dark .wrap li,
.cn-admin-dark .wrap td,
.cn-admin-dark .wrap th {
	color: #c9d1d9;
}
.cn-admin-dark .wrap a {
	color: #2ea043;
}
.cn-admin-dark .wrap a:hover {
	color: #3fb950;
}

/* ===== Cards ===== */
.cn-admin-dark .cn-card,
.cn-admin-dark .cn-dt__form-card,
.cn-admin-dark .cn-dt__preview-card,
.cn-admin-dark .cn-block-gen__card,
.cn-admin-dark .cn-block-gen__info-card,
.cn-admin-dark .cn-font-panel {
	background: #161b22;
	border: 1px solid #30363d;
	border-radius: 10px;
	box-shadow: 0 1px 3px rgba(0,0,0,.3);
}

/* ===== Forms ===== */
.cn-admin-dark .wrap .form-table th {
	color: #f0f6fc;
}
.cn-admin-dark .wrap input[type="text"],
.cn-admin-dark .wrap input[type="color"],
.cn-admin-dark .wrap textarea,
.cn-admin-dark .wrap select {
	background: #0d1117;
	border: 1px solid #30363d;
	border-radius: 6px;
	color: #e6edf3;
	box-shadow: none;
}
.cn-admin-dark .wrap input[type="text"]:focus,
.cn-admin-dark .wrap input[type="color"]:focus,
.cn-admin-dark .wrap textarea:focus,
.cn-admin-dark .wrap select:focus {
	border-color: #2ea043;
	box-shadow: 0 0 0 3px rgba(46,160,67,.15);
}
.cn-admin-dark .wrap input[type="color"] {
	background: #0d1117;
	padding: 2px;
}
.cn-admin-dark .wrap .description {
	color: #8b949e;
}

/* ===== Buttons ===== */
.cn-admin-dark .wrap .button,
.cn-admin-dark .wrap .button-secondary {
	background: #21262d;
	border-color: #30363d;
	color: #c9d1d9;
	border-radius: 6px;
}
.cn-admin-dark .wrap .button:hover {
	background: #30363d;
	border-color: #8b949e;
	color: #f0f6fc;
}
.cn-admin-dark .wrap .button-primary {
	background: #2ea043;
	border-color: #238636;
	color: #fff;
	text-shadow: none;
	box-shadow: 0 1px 2px rgba(0,0,0,.2);
	border-radius: 6px;
	font-weight: 600;
}
.cn-admin-dark .wrap .button-primary:hover {
	background: #3fb950;
	border-color: #2ea043;
}
.cn-admin-dark .wrap .button-primary:active {
	background: #238636;
}
.cn-admin-dark .wrap .button-hero {
	height: 42px;
	line-height: 42px;
	padding: 0 28px;
	font-size: 14px;
}

/* ===== Tables ===== */
.cn-admin-dark .wrap .widefat,
.cn-admin-dark .wrap .striped > tbody > :nth-child(odd) {
	background: #161b22;
	border-color: #30363d;
}
.cn-admin-dark .wrap .widefat td,
.cn-admin-dark .wrap .widefat th {
	border-color: #21262d;
}
.cn-admin-dark .wrap .striped > tbody > :nth-child(odd) {
	background: #161b22;
}
.cn-admin-dark .wrap .striped > tbody > :nth-child(even) {
	background: #0d1117;
}

/* ===== Settings Errors / Notices ===== */
.cn-admin-dark .wrap .notice,
.cn-admin-dark .wrap .updated,
.cn-admin-dark .wrap .error {
	background: #161b22;
	border-color: #30363d;
	border-radius: 6px;
}
.cn-admin-dark .wrap .notice-success {
	border-left-color: #2ea043;
}
.cn-admin-dark .wrap .notice-error {
	border-left-color: #f85149;
}
.cn-admin-dark .wrap .notice-warning {
	border-left-color: #d29922;
}

/* ===== Code / Pre ===== */
.cn-admin-dark .wrap code,
.cn-admin-dark .wrap pre {
	background: #0d1117;
	border: 1px solid #30363d;
	border-radius: 6px;
	color: #e6edf3;
}
.cn-admin-dark .wrap .cn-block-gen__cli {
	background: #0d1117;
	color: #2ea043;
	border: 1px solid #21262d;
}

/* ===== Scrollbar ===== */
.cn-admin-dark ::-webkit-scrollbar { width: 10px; height: 10px; }
.cn-admin-dark ::-webkit-scrollbar-track { background: #0d1117; }
.cn-admin-dark ::-webkit-scrollbar-thumb { background: #30363d; border-radius: 5px; }
.cn-admin-dark ::-webkit-scrollbar-thumb:hover { background: #484f58; }

/* ===== CN Dashboard ===== */
.cn-dashboard { max-width: 1080px; margin: 20px auto; padding: 0 20px 60px; }
.cn-dashboard__header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 28px 0;
	margin-bottom: 24px;
	border-bottom: 1px solid #30363d;
}
.cn-dashboard__brand { display: flex; align-items: center; gap: 16px; }
.cn-dashboard__logo { width: 36px; height: 40px; display: block; }
.cn-dashboard__brand h1 {
	font-size: 22px;
	font-weight: 700;
	color: #f0f6fc;
	margin: 0;
	line-height: 1.2;
}
.cn-dashboard__brand h1 span {
	font-weight: 400;
	color: #8b949e;
	font-size: 18px;
}
.cn-dashboard__brand p {
	font-size: 13px;
	color: #8b949e;
	margin: 2px 0 0;
}
.cn-dashboard__version {
	font-size: 12px;
	color: #8b949e;
	background: #161b22;
	border: 1px solid #30363d;
	padding: 4px 12px;
	border-radius: 20px;
}

/* ===== Shared CN Page Header (used on all CN admin pages) ===== */
.cn-page-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 28px 0;
	margin-bottom: 24px;
	border-bottom: 1px solid #30363d;
}
.cn-page-header__brand { display: flex; align-items: center; gap: 16px; }
.cn-page-header__logo { width: 36px; height: 40px; display: block; }
.cn-page-header__brand h1 {
	font-size: 22px;
	font-weight: 700;
	color: #f0f6fc;
	margin: 0;
	line-height: 1.2;
}
.cn-page-header__brand p {
	font-size: 13px;
	color: #8b949e;
	margin: 2px 0 0;
}
.cn-page-header__version {
	font-size: 12px;
	color: #8b949e;
	background: #161b22;
	border: 1px solid #30363d;
	padding: 4px 12px;
	border-radius: 20px;
}

.cn-dashboard__grid {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
	gap: 16px;
	margin-bottom: 40px;
}
.cn-card {
	display: flex;
	align-items: flex-start;
	gap: 14px;
	padding: 20px;
	transition: border-color .2s, box-shadow .2s;
}
.cn-card:hover {
	border-color: #2ea043;
	box-shadow: 0 0 0 1px rgba(46,160,67,.15);
}
.cn-card__icon {
	flex-shrink: 0;
	width: 40px;
	height: 40px;
	border-radius: 8px;
	background: rgba(46,160,67,.1);
	display: flex;
	align-items: center;
	justify-content: center;
}
.cn-card__icon .dashicons {
	color: #2ea043;
	font-size: 22px;
	width: 22px;
	height: 22px;
}
.cn-card--status.is-pending .cn-card__icon { background: rgba(210,153,34,.1); }
.cn-card--status.is-pending .cn-card__icon .dashicons { color: #d29922; }
.cn-card__body h3 { margin: 0 0 4px; font-size: 14px; }
.cn-card__body p { margin: 0 0 8px; font-size: 13px; color: #8b949e; }
.cn-card__link { font-size: 13px; font-weight: 600; text-decoration: none; }
.cn-card__link:hover { text-decoration: underline; }
.cn-card__swatches { display: flex; gap: 6px; margin-bottom: 8px; }
.cn-card__swatches span { width: 20px; height: 20px; border-radius: 4px; border: 1px solid rgba(255,255,255,.1); }

.cn-dashboard__quick-links h2 {
	font-size: 16px;
	margin-bottom: 16px;
	color: #f0f6fc;
}
.cn-dashboard__link-grid {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
	gap: 12px;
}
.cn-quick-link {
	display: flex;
	flex-direction: column;
	align-items: center;
	gap: 8px;
	padding: 20px 12px;
	background: #161b22;
	border: 1px solid #30363d;
	border-radius: 10px;
	text-decoration: none;
	color: #c9d1d9;
	font-size: 13px;
	font-weight: 600;
	transition: all .2s;
}
.cn-quick-link:hover {
	border-color: #2ea043;
	color: #3fb950;
	background: #1c2331;
}
.cn-quick-link .dashicons { font-size: 24px; width: 24px; height: 24px; color: #2ea043; }

/* ===== Settings Panel (Theme Settings page) ===== */
.cn-admin-dark .cn-settings-panel {
	background: #161b22;
	border: 1px solid #30363d;
	border-radius: 10px;
	padding: 24px;
	margin: 16px 0;
}
.cn-admin-dark .cn-settings-panel__header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	margin-bottom: 16px;
}
.cn-admin-dark .cn-settings-panel__header h2 { margin: 0; }
.cn-admin-dark .cn-settings-panel__badge {
	font-size: 12px;
	font-weight: 600;
	padding: 3px 10px;
	border-radius: 20px;
}
.cn-admin-dark .cn-settings-panel__badge--blocks { background: rgba(46,160,67,.15); color: #3fb950; }
.cn-admin-dark .cn-settings-panel__badge--flexible { background: rgba(210,153,34,.15); color: #d29922; }
.cn-admin-dark .cn-settings-panel__badge--full { background: rgba(88,166,255,.15); color: #58a6ff; }
.cn-admin-dark .cn-settings-panel__badge--landing { background: rgba(163,113,247,.15); color: #a371f7; }
.cn-admin-dark .cn-settings-panel__desc { font-size: 14px; margin-bottom: 16px; }
.cn-admin-dark .cn-settings-panel__actions { display: flex; gap: 12px; flex-wrap: wrap; }
.cn-admin-dark .cn-settings-panel__danger { margin-top: 20px; padding-top: 20px; border-top: 1px solid #30363d; }
.cn-admin-dark .cn-settings-panel__danger .button-link-delete {
	color: #f85149;
}

/* ===== Convert Content Panel ===== */
.cn-admin-dark .cn-convert-panel {
	background: #161b22;
	border: 1px solid #30363d;
	border-radius: 10px;
	padding: 24px;
	margin: 16px 0;
}
.cn-admin-dark .cn-convert-panel h2 { margin: 0 0 8px; }
.cn-admin-dark .cn-convert-panel p { font-size: 14px; margin-bottom: 16px; }

/* ===== Wizard Dark Mode ===== */
.cn-admin-dark .cn-wizard { max-width: 760px; margin: 40px auto 60px; }
.cn-admin-dark .cn-wizard__card {
	background: #161b22;
	border: 1px solid #30363d;
	border-radius: 12px;
	padding: 0;
	box-shadow: 0 4px 20px rgba(0,0,0,.3);
	overflow: hidden;
}
.cn-admin-dark .cn-wizard__header {
	position: relative;
	padding: 28px 36px;
	background: #0d1117;
	border-bottom: 1px solid #30363d;
}
.cn-admin-dark .cn-wizard__header-gradient {
	position: absolute; top: 0; left: 0; right: 0; height: 3px;
	background: linear-gradient(90deg, #2ea043 0%, #3fb950 100%);
}
.cn-admin-dark .cn-wizard__brand { display: flex; align-items: center; gap: 14px; }
.cn-admin-dark .cn-wizard__brand-mark { width: 32px; height: 36px; display: block; }
.cn-admin-dark .cn-wizard__brand-text { display: flex; flex-direction: column; }
.cn-admin-dark .cn-wizard__brand-name { font-size: 15px; font-weight: 700; letter-spacing: .03em; color: #f0f6fc; }
.cn-admin-dark .cn-wizard__brand-sub { font-size: 11px; letter-spacing: .12em; text-transform: uppercase; color: #8b949e; }

.cn-admin-dark .cn-wizard__steps {
	display: flex; list-style: none; margin: 0; padding: 20px 36px 24px;
	background: #0d1117; border-bottom: 1px solid #30363d; counter-reset: step;
}
.cn-admin-dark .cn-wizard__steps li {
	flex: 1; display: flex; flex-direction: column; align-items: center; gap: 6px;
	font-size: 12px; color: #484f58; position: relative; counter-increment: step; text-align: center;
}
.cn-admin-dark .cn-wizard__steps li::before {
	content: counter(step); width: 30px; height: 30px; border-radius: 50%;
	background: #21262d; color: #8b949e; font-size: 13px; font-weight: 700;
	display: flex; align-items: center; justify-content: center; flex-shrink: 0;
	transition: all .2s; z-index: 1;
}
.cn-admin-dark .cn-wizard__steps li:not(:last-child)::after {
	content: ""; position: absolute; top: 15px; left: 50%; width: 100%; height: 2px;
	background: #21262d; z-index: 0;
}
.cn-admin-dark .cn-wizard__steps li.is-current { color: #3fb950; font-weight: 600; }
.cn-admin-dark .cn-wizard__steps li.is-current::before {
	background: #2ea043; color: #fff; box-shadow: 0 0 0 4px rgba(46,160,67,.15);
}
.cn-admin-dark .cn-wizard__steps li.is-done { color: #2ea043; }
.cn-admin-dark .cn-wizard__steps li.is-done::before { background: #2ea043; color: #fff; content: "\\2713"; }
.cn-admin-dark .cn-wizard__steps li.is-done::after { background: #2ea043; }
.cn-admin-dark .cn-wizard__steps li a { color: inherit; text-decoration: none; display: flex; flex-direction: column; align-items: center; gap: 6px; }
.cn-admin-dark .cn-wizard__steps li.is-done a { cursor: pointer; }
.cn-admin-dark .cn-wizard__steps li.is-done a:hover { color: #3fb950; }
.cn-admin-dark .cn-wizard__steps li.is-done a:hover::before { box-shadow: 0 0 0 4px rgba(46,160,67,.25); }

.cn-admin-dark .cn-wizard h1 { padding: 28px 36px 0; font-size: 20px; font-weight: 700; color: #f0f6fc; }
.cn-admin-dark .cn-wizard p,
.cn-admin-dark .cn-wizard form,
.cn-admin-dark .cn-wizard table { padding: 0 36px; }
.cn-admin-dark .cn-wizard .form-table th { padding-left: 0; padding-right: 0; width: 180px; color: #f0f6fc; }
.cn-admin-dark .cn-wizard .form-table { margin-top: 16px; }
.cn-admin-dark .cn-wizard .form-table td { padding-left: 0; }
.cn-admin-dark .cn-wizard__lead { font-size: 15px; color: #8b949e; margin: 8px 0 24px; line-height: 1.6; }

.cn-admin-dark .cn-wizard__actions { margin-top: 28px; padding: 0 36px 36px; display: flex; align-items: center; gap: 16px; }
.cn-admin-dark .cn-wizard__skip { color: #8b949e; text-decoration: none; font-size: 13px; background: none; border: none; cursor: pointer; padding: 0; }
.cn-admin-dark .cn-wizard__skip:hover { color: #3fb950; }

.cn-admin-dark .cn-wizard__plugins { margin-bottom: 24px; border-radius: 6px; overflow: hidden; }
.cn-admin-dark .cn-wizard__plugin-action { white-space: nowrap; }
.cn-admin-dark .cn-wizard__status--active { color: #3fb950; font-weight: 600; }
.cn-admin-dark .cn-wizard__status--missing { color: #f85149; font-weight: 600; }
.cn-admin-dark .cn-wizard__install-bar { margin-bottom: 16px; }
.cn-admin-dark .cn-wizard__dashboard-link { display: block; margin-top: 4px; font-size: 12px; color: #2ea043; text-decoration: none; }
.cn-admin-dark .cn-wizard__dashboard-link:hover { text-decoration: underline; }
.cn-admin-dark .cn-wizard__install-status { display: block; margin-top: 4px; font-size: 12px; }
.cn-admin-dark .cn-wizard__cli-hint { color: #8b949e; font-size: 13px; margin: 16px 0 0; }
.cn-admin-dark .cn-wizard__cli-hint code { background: #0d1117; padding: 2px 6px; border-radius: 3px; font-size: 12px; border: 1px solid #30363d; }

/* Build mode cards */
.cn-admin-dark .cn-wizard__build-mode { display: flex; gap: 16px; padding: 0 36px; margin-bottom: 24px; }
.cn-admin-dark .cn-wizard__mode-card {
	flex: 1; display: flex; flex-direction: column; align-items: center; text-align: center; gap: 10px;
	padding: 28px 20px; border: 2px solid #30363d; border-radius: 10px; cursor: pointer;
	transition: all .2s; position: relative; background: #0d1117;
}
.cn-admin-dark .cn-wizard__mode-card:hover { border-color: #2ea043; box-shadow: 0 2px 8px rgba(46,160,67,.08); }
.cn-admin-dark .cn-wizard__mode-card.is-selected { border-color: #2ea043; background: #161b22; box-shadow: 0 1px 6px rgba(46,160,67,.1); }
.cn-admin-dark .cn-wizard__mode-card input[type="radio"] { position: absolute; opacity: 0; pointer-events: none; width: 0; height: 0; }
.cn-admin-dark .cn-wizard__mode-icon { font-size: 36px; width: 36px; height: 36px; color: #484f58; transition: color .2s; }
.cn-admin-dark .cn-wizard__mode-card.is-selected .cn-wizard__mode-icon { color: #2ea043; }
.cn-admin-dark .cn-wizard__mode-title { font-size: 15px; font-weight: 700; color: #f0f6fc; }
.cn-admin-dark .cn-wizard__mode-desc { font-size: 13px; color: #8b949e; line-height: 1.5; max-width: 220px; }
.cn-admin-dark .cn-wizard__mode-card.is-selected .cn-wizard__mode-title { color: #3fb950; }

/* Site type cards */
.cn-admin-dark .cn-wizard__site-type { display: flex; gap: 16px; padding: 0 36px; margin-bottom: 24px; }
.cn-admin-dark .cn-wizard__site-type-card {
	flex: 1; display: flex; flex-direction: column; align-items: center; text-align: center; gap: 8px;
	padding: 20px 16px; border: 2px solid #30363d; border-radius: 10px; cursor: pointer;
	transition: all .2s; position: relative; background: #0d1117;
}
.cn-admin-dark .cn-wizard__site-type-card:hover { border-color: #2ea043; box-shadow: 0 2px 8px rgba(46,160,67,.08); }
.cn-admin-dark .cn-wizard__site-type-card.is-selected { border-color: #2ea043; background: #161b22; box-shadow: 0 1px 6px rgba(46,160,67,.1); }
.cn-admin-dark .cn-wizard__site-type-icon { font-size: 28px; width: 28px; height: 28px; color: #484f58; transition: color .2s; }
.cn-admin-dark .cn-wizard__site-type-card.is-selected .cn-wizard__site-type-icon { color: #2ea043; }
.cn-admin-dark .cn-wizard__site-type-title { font-size: 14px; font-weight: 700; color: #f0f6fc; }
.cn-admin-dark .cn-wizard__site-type-desc { font-size: 12px; color: #8b949e; line-height: 1.5; max-width: 200px; }
.cn-admin-dark .cn-wizard__site-type-card.is-selected .cn-wizard__site-type-title { color: #3fb950; }

/* Done step links */
.cn-admin-dark .cn-wizard__links { list-style: none; padding: 0 36px 36px; margin: 0; }
.cn-admin-dark .cn-wizard__links li { padding: 10px 0; border-bottom: 1px solid #21262d; }
.cn-admin-dark .cn-wizard__links li:last-child { border-bottom: none; }
.cn-admin-dark .cn-wizard__links a { display: flex; align-items: center; gap: 8px; text-decoration: none; color: #2ea043; font-weight: 500; font-size: 14px; }
.cn-admin-dark .cn-wizard__links a:hover { color: #3fb950; }
.cn-admin-dark .cn-wizard__links .dashicons { font-size: 18px; width: 18px; height: 18px; }

/* ===== Design Tokens Dark Mode ===== */
.cn-admin-dark .cn-dt__intro { margin-bottom: 20px; max-width: 700px; }
.cn-admin-dark .cn-dt__grid { display: grid; grid-template-columns: 1fr 360px; gap: 24px; align-items: start; }
@media (max-width: 1200px) { .cn-admin-dark .cn-dt__grid { grid-template-columns: 1fr; } }
.cn-admin-dark .cn-dt__form-card { padding: 24px; }
.cn-admin-dark .cn-dt__preview-card { padding: 24px; }
.cn-admin-dark .cn-dt-color { width: 60px; height: 40px; border: 1px solid #30363d; border-radius: 6px; cursor: pointer; vertical-align: middle; background: #0d1117; }
.cn-admin-dark .cn-dt-hex { font-family: monospace; font-size: 14px; margin-left: 8px; vertical-align: middle; color: #e6edf3; }
.cn-admin-dark .cn-dt-actions { margin-top: 24px; display: flex; gap: 12px; align-items: center; }
.cn-admin-dark .cn-dt-swatches { display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px; }
.cn-admin-dark .cn-dt-swatch { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-radius: 8px; color: #fff; font-weight: 700; font-size: 14px; }
.cn-admin-dark .cn-dt-swatch__hex { font-family: monospace; font-size: 12px; opacity: .8; }
.cn-admin-dark .cn-dt-demo { display: flex; gap: 16px; align-items: center; padding-top: 16px; border-top: 1px solid #30363d; }

/* ===== Figma Tokens Dark Mode ===== */
.cn-admin-dark .cn-figma-tokens__intro { margin-bottom: 20px; }
.cn-admin-dark .cn-figma-tokens__grid { display: grid; grid-template-columns: 1fr 320px; gap: 24px; align-items: start; }
@media (max-width: 1200px) { .cn-admin-dark .cn-figma-tokens__grid { grid-template-columns: 1fr; } }
.cn-admin-dark .cn-figma-tokens__form textarea { font-family: monospace; font-size: 13px; border-radius: 6px; }
.cn-admin-dark .cn-figma-tokens__actions { margin-top: 12px; display: flex; gap: 10px; }
.cn-admin-dark .cn-figma-tokens__css { background: #0d1117; border: 1px solid #30363d; border-radius: 6px; padding: 16px; overflow: auto; font-size: 13px; line-height: 1.6; color: #e6edf3; }
.cn-admin-dark .cn-font-panel { padding: 20px; }
.cn-admin-dark .cn-font-panel--empty { opacity: .5; }
.cn-admin-dark .cn-font-panel__header { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
.cn-admin-dark .cn-font-panel__header .dashicons { color: #2ea043; font-size: 20px; width: 20px; height: 20px; }
.cn-admin-dark .cn-font-panel__header h2 { margin: 0; font-size: 15px; }
.cn-admin-dark .cn-font-panel__desc { font-size: 13px; color: #8b949e; margin-bottom: 16px; }
.cn-admin-dark .cn-font-panel__list { list-style: none; margin: 0 0 16px; padding: 0; }
.cn-admin-dark .cn-font-panel__item { padding: 12px 0; border-bottom: 1px solid #21262d; }
.cn-admin-dark .cn-font-panel__item:last-child { border-bottom: none; }
.cn-admin-dark .cn-font-panel__font-name { font-size: 20px; font-weight: 700; color: #f0f6fc; margin-bottom: 4px; }
.cn-admin-dark .cn-font-panel__font-source { font-size: 12px; color: #2ea043; margin-bottom: 6px; }
.cn-admin-dark .cn-font-panel__font-stack { font-size: 11px; color: #8b949e; background: #0d1117; padding: 2px 6px; border-radius: 3px; display: inline-block; border: 1px solid #21262d; }
.cn-admin-dark .cn-font-panel__hint { font-size: 12px; color: #c9d1d9; background: rgba(46,160,67,.08); border: 1px solid rgba(46,160,67,.15); border-radius: 6px; padding: 10px 12px; display: flex; gap: 6px; align-items: flex-start; }
.cn-admin-dark .cn-font-panel__hint .dashicons { font-size: 16px; width: 16px; height: 16px; color: #2ea043; flex-shrink: 0; margin-top: 1px; }

/* ===== Block Generator Dark Mode ===== */
.cn-admin-dark .cn-block-gen__grid { display: grid; grid-template-columns: 1fr 300px; gap: 24px; align-items: start; }
@media (max-width: 1200px) { .cn-admin-dark .cn-block-gen__grid { grid-template-columns: 1fr; } }
.cn-admin-dark .cn-block-gen__card { padding: 24px; }
.cn-admin-dark .cn-block-gen__intro { font-size: 14px; color: #c9d1d9; margin-bottom: 20px; }
.cn-admin-dark .cn-block-gen__submit { margin-top: 20px; }
.cn-admin-dark .cn-block-gen__sidebar { display: flex; flex-direction: column; gap: 16px; }
.cn-admin-dark .cn-block-gen__info-card { padding: 20px; }
.cn-admin-dark .cn-block-gen__info-card h3 { display: flex; align-items: center; gap: 8px; margin: 0 0 12px; font-size: 14px; }
.cn-admin-dark .cn-block-gen__info-card h3 .dashicons { color: #2ea043; font-size: 18px; width: 18px; height: 18px; }
.cn-admin-dark .cn-block-gen__files { list-style: none; margin: 0; padding: 0; }
.cn-admin-dark .cn-block-gen__files li { padding: 4px 0; }
.cn-admin-dark .cn-block-gen__files code { font-size: 12px; background: #0d1117; padding: 2px 6px; border-radius: 3px; border: 1px solid #21262d; }
.cn-admin-dark .cn-block-gen__steps { margin: 0 0 0 20px; padding: 0; color: #c9d1d9; font-size: 13px; }
.cn-admin-dark .cn-block-gen__steps li { margin-bottom: 6px; }
.cn-admin-dark .cn-block-gen__cli { display: block; font-size: 12px; background: #0d1117; color: #2ea043; padding: 10px 12px; border-radius: 6px; word-break: break-all; border: 1px solid #21262d; }

/* ===== Documentation Dark Mode ===== */
.cn-admin-dark .cn-docs-wrap { display: grid; grid-template-columns: 220px 1fr; gap: 24px; max-width: 1200px; margin: 20px auto; padding: 0 20px; }
.cn-admin-dark .cn-docs-nav { background: #161b22; border: 1px solid #30363d; border-radius: 10px; padding: 16px; position: sticky; top: 40px; max-height: calc(100vh - 60px); overflow-y: auto; }
.cn-admin-dark .cn-docs-nav h3 { font-size: 12px; text-transform: uppercase; letter-spacing: .08em; color: #8b949e; margin: 0 0 12px; }
.cn-admin-dark .cn-docs-nav ul { list-style: none; margin: 0; padding: 0; }
.cn-admin-dark .cn-docs-nav li { margin: 0; }
.cn-admin-dark .cn-docs-nav a { display: block; padding: 8px 12px; border-radius: 6px; text-decoration: none; color: #c9d1d9; font-size: 13px; transition: all .15s; }
.cn-admin-dark .cn-docs-nav a:hover { background: #21262d; color: #f0f6fc; }
.cn-admin-dark .cn-docs-nav a.is-active { background: rgba(46,160,67,.12); color: #3fb950; font-weight: 600; }
.cn-admin-dark .cn-docs-content { background: #161b22; border: 1px solid #30363d; border-radius: 10px; padding: 32px 40px; }
.cn-admin-dark .cn-docs-content h1 { font-size: 24px; margin: 0 0 20px; padding-bottom: 16px; border-bottom: 1px solid #30363d; }
.cn-admin-dark .cn-docs-content h2 { font-size: 18px; margin: 28px 0 12px; }
.cn-admin-dark .cn-docs-content h3 { font-size: 15px; margin: 24px 0 8px; }
.cn-admin-dark .cn-docs-content p { line-height: 1.7; margin: 0 0 16px; }
.cn-admin-dark .cn-docs-content ul,
.cn-admin-dark .cn-docs-content ol { padding-left: 24px; }
.cn-admin-dark .cn-docs-content li { margin-bottom: 6px; line-height: 1.6; }
.cn-admin-dark .cn-docs-content code { font-size: 13px; }
.cn-admin-dark .cn-docs-content pre { padding: 16px; overflow: auto; font-size: 13px; line-height: 1.6; margin: 0 0 20px; }
.cn-admin-dark .cn-docs-content pre code { background: none; border: none; padding: 0; }
.cn-admin-dark .cn-docs-content blockquote { border-left: 3px solid #2ea043; margin: 16px 0; padding: 8px 20px; background: #0d1117; border-radius: 0 6px 6px 0; }
.cn-admin-dark .cn-docs-content a { color: #2ea043; }
.cn-admin-dark .cn-docs-content strong { color: #f0f6fc; }
@media (max-width: 900px) { .cn-admin-dark .cn-docs-wrap { grid-template-columns: 1fr; } .cn-admin-dark .cn-docs-nav { position: static; max-height: none; } }

/* ===== ACF Options Page (Theme Settings) Dark Mode ===== */
.cn-admin-dark #acf-form-data .acf-postbox,
.cn-admin-dark .acf-fields {
	background: #161b22;
	border-color: #30363d;
}
.cn-admin-dark .acf-field .acf-label label {
	color: #f0f6fc;
}
.cn-admin-dark .acf-field .acf-input select {
	background: #0d1117;
	border-color: #30363d;
	color: #e6edf3;
}
/* ACF metabox/postbox wrapper */
.cn-admin-dark .postbox {
	background: #161b22;
	border-color: #30363d;
	border-radius: 8px;
}
.cn-admin-dark .postbox .hndle,
.cn-admin-dark .postbox .postbox-header {
	background: #0d1117;
	border-color: #30363d;
	color: #f0f6fc;
}
.cn-admin-dark .postbox .hndle h2,
.cn-admin-dark .postbox .postbox-header h2 {
	color: #f0f6fc;
}
.cn-admin-dark .postbox .inside {
	background: #161b22;
	color: #c9d1d9;
}
/* ACF field group layout */
.cn-admin-dark .acf-field {
	background: #161b22;
	border-color: #21262d;
}
.cn-admin-dark .acf-field .acf-label {
	color: #f0f6fc;
}
.cn-admin-dark .acf-field .acf-label p.description {
	color: #8b949e;
}
.cn-admin-dark .acf-field input[type="text"],
.cn-admin-dark .acf-field input[type="number"],
.cn-admin-dark .acf-field input[type="email"],
.cn-admin-dark .acf-field input[type="url"],
.cn-admin-dark .acf-field textarea,
.cn-admin-dark .acf-field select {
	background: #0d1117;
	border: 1px solid #30363d;
	border-radius: 6px;
	color: #e6edf3;
	box-shadow: none;
}
.cn-admin-dark .acf-field input[type="text"]:focus,
.cn-admin-dark .acf-field input[type="number"]:focus,
.cn-admin-dark .acf-field input[type="email"]:focus,
.cn-admin-dark .acf-field input[type="url"]:focus,
.cn-admin-dark .acf-field textarea:focus,
.cn-admin-dark .acf-field select:focus {
	border-color: #2ea043;
	box-shadow: 0 0 0 3px rgba(46,160,67,.15);
}
/* ACF true/false checkbox */
.cn-admin-dark .acf-field-true_false .acf-true_false {
	background: #0d1117;
	border-color: #30363d;
}
.cn-admin-dark .acf-field-true_false .acf-true_false.-on {
	background: #238636;
	border-color: #2ea043;
}
/* ACF message field */
.cn-admin-dark .acf-field-message .acf-message {
	color: #8b949e;
}
/* ACF submit button */
.cn-admin-dark .acf-form-submit .button {
	background: #2ea043;
	border-color: #238636;
	color: #fff;
}
.cn-admin-dark .acf-form-submit .button:hover {
	background: #3fb950;
	border-color: #2ea043;
}
/* ACF tab navigation */
.cn-admin-dark .acf-tab-wrap {
	background: #0d1117;
	border-color: #30363d;
}
.cn-admin-dark .acf-tab-wrap .acf-tab-button {
	color: #8b949e;
}
.cn-admin-dark .acf-tab-wrap .acf-tab-button.active {
	color: #3fb950;
	border-color: #2ea043;
}
/* ACF relationship/select2 */
.cn-admin-dark .select2-container .select2-selection {
	background: #0d1117;
	border-color: #30363d;
	color: #e6edf3;
}
.cn-admin-dark .select2-dropdown {
	background: #161b22;
	border-color: #30363d;
}
.cn-admin-dark .select2-results__option {
	color: #c9d1d9;
}
.cn-admin-dark .select2-results__option--highlighted {
	background: #21262d;
	color: #f0f6fc;
}
/* ACF image upload */
.cn-admin-dark .acf-field-image .acf-image-uploader {
	background: #0d1117;
	border-color: #30363d;
}
.cn-admin-dark .acf-field-image .acf-image-uploader p {
	color: #8b949e;
}
/* ACF metabox toggle */
.cn-admin-dark .acf-meta-box-toggle {
	color: #8b949e;
}
/* ACF collapsible */
.cn-admin-dark .acf-field-accordion .acf-accordion-item {
	background: #161b22;
	border-color: #30363d;
}
.cn-admin-dark .acf-field-accordion .acf-accordion-item .acf-accordion-item-title {
	background: #0d1117;
	border-color: #30363d;
	color: #f0f6fc;
}
';

	wp_register_style( 'cn-admin-branding', false, array(), CN_THEME_VERSION );
	wp_enqueue_style( 'cn-admin-branding' );
	wp_add_inline_style( 'cn-admin-branding', $css );
}
add_action( 'admin_enqueue_scripts', 'cn_admin_branding_styles' );

/**
 * Add the cn-admin-dark body class on all CN admin pages.
 */
function cn_admin_body_class( $classes ) {
	$screen = get_current_screen();
	if ( ! $screen ) {
		return $classes;
	}

	$cn_screens = array(
		'toplevel_page_cn-starter',
		'cn-starter_page_cn-setup-wizard',
		'cn-starter_page_cn-figma-tokens',
		'cn-starter_page_cn-design-tokens',
		'cn-starter_page_cn-block-generator',
		'cn-starter_page_cn-docs',
		'admin_page_cn-theme-settings',
	);

	if ( in_array( $screen->id, $cn_screens, true ) ) {
		$classes .= ' cn-admin-dark cn-admin-page';
	}

	return $classes;
}
add_filter( 'admin_body_class', 'cn_admin_body_class' );

/**
 * Render a shared branded page header for CN admin pages.
 * Call this at the top of each admin page render function,
 * right after the opening .wrap div.
 *
 * @param string $title    Page title (e.g. "Design Tokens").
 * @param string $subtitle Optional subtitle/description.
 */
function cn_admin_page_header( $title, $subtitle = '' ) {
	?>
	<div class="cn-page-header">
		<div class="cn-page-header__brand">
			<img src="<?php echo esc_url( CN_THEME_URI . '/assets/images/cn-mark.svg' ); ?>" alt="CN" class="cn-page-header__logo">
			<div>
				<h1><?php echo esc_html( $title ); ?></h1>
				<?php if ( $subtitle ) : ?>
					<p><?php echo esc_html( $subtitle ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<div class="cn-page-header__version">v<?php echo esc_html( CN_THEME_VERSION ); ?></div>
	</div>
	<?php
}

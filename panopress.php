<?php
/**
 * Plugin Name: PanoPress
 * Plugin URI:  https://www.panopress.org/
 * Description: Embed HTML5 360° Panoramas & Virtual Tours, 360° Video, Gigapixel Panoramas etc, created using KRPano, Pano2VR, PanoTour Pro, and similar panorama applications on your WordPress site using a simple shortcode.
 * Version:     1.3.0
 * Author:      Omer Calev & Sam Rohn
 * Text Domain: panopress
 * Requires PHP: 8.0
 * Requires at least: 6.0
 *
 * Copyright 2011-2014 by the authors.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * USAGE: [pano file="pano file name or url"]
 *
 * Optional Parameters:
 *   width/w    = "100%"
 *   height/h   = "450px"
 *   title/t    = "title text"
 *   alt/a      = "alt text"
 *   preview/p  = "preview image url"
 *   panobox/b  = "on/off"
 *   button/n   = "on/off"
 */

// Empêche l'accès direct au fichier.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// CONFIG
defined( 'PP_APP_NAME'    ) || define( 'PP_APP_NAME',    'PanoPress' );
defined( 'PP_APP_VERSION' ) || define( 'PP_APP_VERSION', '1.3.0' );
// defaults
defined( 'PP_DEFAULT_WIDTH'         ) || define( 'PP_DEFAULT_WIDTH',         '640px' );
defined( 'PP_DEFAULT_HEIGHT'        ) || define( 'PP_DEFAULT_HEIGHT',        '480px' );
defined( 'PP_DEFAULT_FLASH_VERSION' ) || define( 'PP_DEFAULT_FLASH_VERSION', '9.0.28' );
// options
defined( 'PP_FILE_TYPE_FILTERING'     ) || define( 'PP_FILE_TYPE_FILTERING',     true );
defined( 'PP_ALLOW_UNKNOWN_FILE_TYPES') || define( 'PP_ALLOW_UNKNOWN_FILE_TYPES', false );
defined( 'PP_PANOBOX_IMAGES'          ) || define( 'PP_PANOBOX_IMAGES',           true );
// viewers
defined( 'PP_VIEWER_NAME_KRPANO'  ) || define( 'PP_VIEWER_NAME_KRPANO',  'krpano' );
defined( 'PP_VIEWER_NAME_PANO2VR' ) || define( 'PP_VIEWER_NAME_PANO2VR', 'pano2vr' );
defined( 'PP_VIEWER_NAME_FPP'     ) || define( 'PP_VIEWER_NAME_FPP',     'fpp' );
defined( 'PP_VIEWER_NAME_CUTY'    ) || define( 'PP_VIEWER_NAME_CUTY',    'cuty' );
defined( 'PP_VIEWER_TYPE_FLASH'   ) || define( 'PP_VIEWER_TYPE_FLASH',   'flash' );
defined( 'PP_VIEWER_TYPE_HTML'    ) || define( 'PP_VIEWER_TYPE_HTML',    'html' );
defined( 'PP_VIEWER_TYPE_LINK'    ) || define( 'PP_VIEWER_TYPE_LINK',    'link' );
// file types
defined( 'PP_FILE_TYPE_SWF'     ) || define( 'PP_FILE_TYPE_SWF',     'swf' );
defined( 'PP_FILE_TYPE_XML'     ) || define( 'PP_FILE_TYPE_XML',     'xml' );
defined( 'PP_FILE_TYPE_MOV'     ) || define( 'PP_FILE_TYPE_MOV',     'mov' );
defined( 'PP_FILE_TYPE_HTML'    ) || define( 'PP_FILE_TYPE_HTML',    'html' );
defined( 'PP_FILE_TYPE_UNKNOWN' ) || define( 'PP_FILE_TYPE_UNKNOWN', 'unknown' );
// setting keys, DO NOT EDIT
defined( 'PP_SETTINGS'                ) || define( 'PP_SETTINGS',                'panopress_settings' );
defined( 'PP_SETTINGS_ID'             ) || define( 'PP_SETTINGS_ID',             'id' );
defined( 'PP_SETTINGS_FILE'           ) || define( 'PP_SETTINGS_FILE',           'file' );
defined( 'PP_SETTINGS_PARAMS'         ) || define( 'PP_SETTINGS_PARAMS',         'params' );
defined( 'PP_SETTINGS_VIEWER_NAME'    ) || define( 'PP_SETTINGS_VIEWER_NAME',    'viewer' );
defined( 'PP_SETTINGS_VIEWER_TYPE'    ) || define( 'PP_SETTINGS_VIEWER_TYPE',    'type' );
defined( 'PP_SETTINGS_VIEWER_VRSION'  ) || define( 'PP_SETTINGS_VIEWER_VRSION',  'version' );
defined( 'PP_SETTINGS_TYPE'           ) || define( 'PP_SETTINGS_TYPE',           'filetype' ); // clé extension fichier (swf/xml/mov…)
defined( 'PP_SETTINGS_WIDTH'          ) || define( 'PP_SETTINGS_WIDTH',          'width' );
defined( 'PP_SETTINGS_HEIGHT'         ) || define( 'PP_SETTINGS_HEIGHT',         'height' );
defined( 'PP_SETTINGS_ALT'            ) || define( 'PP_SETTINGS_ALT',            'alt' );
defined( 'PP_SETTINGS_TITLE'          ) || define( 'PP_SETTINGS_TITLE',          'title' );
defined( 'PP_SETTINGS_PREVIEW'        ) || define( 'PP_SETTINGS_PREVIEW',        'preview' );
defined( 'PP_SETTINGS_PLAY_BUTTON'    ) || define( 'PP_SETTINGS_PLAY_BUTTON',    'button' );
defined( 'PP_SETTINGS_UPLOAD_DIR'     ) || define( 'PP_SETTINGS_UPLOAD_DIR',     'upload_dir' );
defined( 'PP_SETTINGS_UPLOAD_WP'      ) || define( 'PP_SETTINGS_UPLOAD_WP',      'upload_wp' );
defined( 'PP_SETTINGS_WMODE'          ) || define( 'PP_SETTINGS_WMODE',          'wmode' );
defined( 'PP_SETTINGS_PANOBOX'        ) || define( 'PP_SETTINGS_PANOBOX',        'panobox' );
defined( 'PP_SETTINGS_PANOBOX_WMODE'  ) || define( 'PP_SETTINGS_PANOBOX_WMODE',  'pbwmode' );
defined( 'PP_SETTINGS_PANOBOX_ACTIVE' ) || define( 'PP_SETTINGS_PANOBOX_ACTIVE', 'pbactive' );
defined( 'PP_SETTINGS_PANOBOX_MOBILE' ) || define( 'PP_SETTINGS_PANOBOX_MOBILE', 'pbmobile' );
defined( 'PP_SETTINGS_VIEWER_DIR'     ) || define( 'PP_SETTINGS_VIEWER_DIR',     'viewer_dir' );
defined( 'PP_SETTINGS_USE_VIEWER_DIR' ) || define( 'PP_SETTINGS_USE_VIEWER_DIR', 'use_viewer_dir' );
defined( 'PP_SETTINGS_OPPP'           ) || define( 'PP_SETTINGS_OPPP',           'oppp' );
defined( 'PP_SETTINGS_CSS'            ) || define( 'PP_SETTINGS_CSS',            'css' );
// panobox
defined( 'PB_SETTINGS_FULLSCREEN'    ) || define( 'PB_SETTINGS_FULLSCREEN',    'fullscreen' );
defined( 'PB_SETTINGS_WIDTH'         ) || define( 'PB_SETTINGS_WIDTH',         'width' );
defined( 'PB_SETTINGS_HEIGHT'        ) || define( 'PB_SETTINGS_HEIGHT',        'height' );
defined( 'PB_SETTINGS_FADE'          ) || define( 'PB_SETTINGS_FADE',          'fade' );
defined( 'PB_SETTINGS_ANIMATE'       ) || define( 'PB_SETTINGS_ANIMATE',       'animate' );
defined( 'PB_SETTINGS_SHADOW'        ) || define( 'PB_SETTINGS_SHADOW',        'shadow' );
defined( 'PB_SETTINGS_RESIZE'        ) || define( 'PB_SETTINGS_RESIZE',        'resize' );
defined( 'PB_SETTINGS_STYLE'         ) || define( 'PB_SETTINGS_STYLE',         'style' );
defined( 'PB_SETTINGS_STYLE_BOX'     ) || define( 'PB_SETTINGS_STYLE_BOX',     'box' );
defined( 'PB_SETTINGS_STYLE_OVERLAY' ) || define( 'PB_SETTINGS_STYLE_OVERLAY', 'overlay' );
defined( 'PB_SETTINGS_GALLERIES'     ) || define( 'PB_SETTINGS_GALLERIES',     'galleries' );
defined( 'PB_SETTINGS_BG_OPACITY'    ) || define( 'PB_SETTINGS_BG_OPACITY',    'bg_opacity' );
// one pano per page
defined( 'PP_OPPP_ALL'      ) || define( 'PP_OPPP_ALL',      'all' );
defined( 'PP_OPPP_MOBILE'   ) || define( 'PP_OPPP_MOBILE',   'mobile' );
defined( 'PP_OPPP_DISABLED' ) || define( 'PP_OPPP_DISABLED', 'disabled' );

/**/
$pp_wp_upload_arr = wp_upload_dir();
$pp_wp_upload_dir = trim( substr( $pp_wp_upload_arr['basedir'], strlen( $_SERVER['DOCUMENT_ROOT'] ?? '' ) ), '/' );
$pp_settings      = get_option( PP_SETTINGS );
$pp_id_counter    = 0;

/**
 * Détecte si le user agent est un appareil mobile (iPhone, iPad, iPod, Android).
 * Remplace les anciennes constantes PP_USER_AGENT_* évaluées à la définition.
 */
function pp_is_mobile(): bool {
	$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
	return str_contains( $ua, 'iPhone' )
		|| str_contains( $ua, 'iPad' )
		|| str_contains( $ua, 'iPod' )
		|| str_contains( $ua, 'Android' );
}

/************************  Réglages par défaut *******************************/
function pp_default_settings(): void {
	global $pp_settings, $pp_wp_upload_dir;
	// panopress
	$pp_settings[PP_SETTINGS_WIDTH]          = PP_DEFAULT_WIDTH;
	$pp_settings[PP_SETTINGS_HEIGHT]         = PP_DEFAULT_HEIGHT;
	$pp_settings[PP_SETTINGS_UPLOAD_WP]      = true;
	$pp_settings[PP_SETTINGS_UPLOAD_DIR]     = $pp_wp_upload_dir;
	$pp_settings[PP_SETTINGS_ALT]            = '';
	$pp_settings[PP_SETTINGS_TITLE]          = '';
	$pp_settings[PP_SETTINGS_PLAY_BUTTON]    = true;
	$pp_settings[PP_SETTINGS_USE_VIEWER_DIR] = false;
	$pp_settings[PP_SETTINGS_WMODE]          = 'auto';
	$pp_settings[PP_SETTINGS_PANOBOX_ACTIVE] = false;
	$pp_settings[PP_SETTINGS_PANOBOX_WMODE]  = 'auto';
	$pp_settings[PP_SETTINGS_PANOBOX_MOBILE] = true;
	$pp_settings[PP_SETTINGS_OPPP]           = PP_OPPP_MOBILE;
	// panobox
	$pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_FULLSCREEN] = true;
	$pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_FADE]       = true;
	$pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_ANIMATE]    = true;
	$pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_STYLE]      = 'light';
	$pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_SHADOW]     = true;
	$pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_BG_OPACITY] = 0.6;
	$pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_GALLERIES]  = false;
}

if ( ! $pp_settings ) {
	$pp_settings = [];
	pp_default_settings();
}

/**
 * Effectue une requête HTTP GET via l'API WordPress (remplace cURL brut).
 *
 * @param string $url       URL à récupérer.
 * @param bool   $allow_ssl Si true, désactive la vérification SSL (héritage — à éviter).
 * @return array{status: int, content: string|null}
 */
function pp_get_url( string $url, bool $allow_ssl = false ): array {
	$response = wp_remote_get( $url, [
		'timeout'   => 10,
		'sslverify' => ! $allow_ssl,
	] );

	if ( is_wp_error( $response ) ) {
		return [ 'status' => 0, 'content' => null ];
	}

	return [
		'status'  => (int) wp_remote_retrieve_response_code( $response ),
		'content' => wp_remote_retrieve_body( $response ),
	];
}

/**
 * Détermine le nom du viewer à partir d'un fichier XML.
 * Résultat mis en cache via transient (1 heure).
 *
 * @param string $xml_url URL du fichier XML.
 * @return array{status: int, content: string}
 */
function pp_get_viewr_name( string $xml_url ): array {
	$cache_key = 'pp_viewer_' . md5( $xml_url );
	$cached    = get_transient( $cache_key );
	if ( false !== $cached ) {
		return $cached;
	}

	$status  = 0;
	$content = '';

	libxml_use_internal_errors( is_user_logged_in() );

	$xml = false;
	if ( ini_get( 'allow_url_fopen' ) ) {
		$xml = @simplexml_load_file( $xml_url );
	} elseif ( function_exists( 'wp_remote_get' ) ) {
		$results = pp_get_url( $xml_url );
		if ( $results['status'] === 200 ) {
			$xml = simplexml_load_string( $results['content'] );
		}
	}

	if ( $xml instanceof SimpleXMLElement ) {
		$root = $xml->getName();
		if ( $root === 'krpano' ) {
			$content = PP_VIEWER_NAME_KRPANO;
			$status  = 1;
		} elseif ( $root === 'panorama' ) {
			$content = PP_VIEWER_NAME_PANO2VR;
			foreach ( $xml->children() as $child ) {
				if ( $child->getName() === 'parameters' ) {
					$content = PP_VIEWER_NAME_FPP;
				}
			}
			$status = 1;
		} elseif ( $root === 'tour' ) {
			foreach ( $xml->children() as $child ) {
				if ( $child->getName() === 'panorama' ) {
					$content = PP_VIEWER_NAME_PANO2VR;
					$status  = 1;
				}
			}
		}
	}

	$result = [ 'status' => $status, 'content' => $content ];
	set_transient( $cache_key, $result, HOUR_IN_SECONDS );
	return $result;
}

/**
 * Enregistre et charge les assets frontend via wp_enqueue_scripts.
 * Remplace l'ancien echo direct dans wp_head.
 */
function pp_enqueue_frontend_assets(): void {
	global $pp_settings;

	wp_enqueue_style(
		'panopress',
		plugins_url( '/css/panopress.css', __FILE__ ),
		[],
		PP_APP_VERSION
	);

	wp_enqueue_script(
		'panopress',
		plugins_url( '/js/panopress.js', __FILE__ ),
		[],
		PP_APP_VERSION,
		false
	);

	$oppp = ( $pp_settings[PP_SETTINGS_OPPP] === PP_OPPP_ALL
		|| ( $pp_settings[PP_SETTINGS_OPPP] === PP_OPPP_MOBILE && pp_is_mobile() ) )
		? 'true' : 'false';

	$pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_RESIZE] = 1;

	$inline = 'var pp_oppp=' . $oppp . ';var pb_options=' . wp_json_encode( $pp_settings[PP_SETTINGS_PANOBOX] ) . ';';
	wp_add_inline_script( 'panopress', $inline, 'before' );

	if ( ! empty( $pp_settings[PP_SETTINGS_CSS] ) ) {
		// Supprime </style> pour éviter l'injection de balise.
		$safe_css = str_replace( '</style>', '', $pp_settings[PP_SETTINGS_CSS] );
		wp_add_inline_style( 'panopress', $safe_css );
	}
}
add_action( 'wp_enqueue_scripts', 'pp_enqueue_frontend_assets' );

/**
 * Injecte l'appel imagebox en pied de page.
 */
function pp_footer(): void {
	global $pp_settings;
	if ( PP_PANOBOX_IMAGES || ! empty( $pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_GALLERIES] ) ) {
		echo '<script>panopress.imagebox();</script>';
	}
}
add_action( 'wp_footer', 'pp_footer' );

/**
 * Surcharge le shortcode [gallery] pour forcer les liens directs (pour Panobox).
 */
function pp_gallery_shortcode( array $attr ): string {
	$attr['link'] = 'file';
	return gallery_shortcode( $attr );
}

global $pp_settings;
if ( ! empty( $pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_GALLERIES] ) ) {
	add_shortcode( 'gallery', 'pp_gallery_shortcode' );
}

// Page d'administration
if ( is_admin() ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/admin.php';
}

// Lien "Réglages" sur la page des plugins
function pp_settings_link( array $links ): array {
	array_unshift(
		$links,
		'<a href="' . esc_url( admin_url( 'options-general.php?page=panopress' ) ) . '">' . esc_html( pp__( 'Settings' ) ) . '</a>'
	);
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'pp_settings_link' );

// Liens meta sur la page des plugins
function pp_set_plugin_meta( array $links, string $file ): array {
	if ( $file === plugin_basename( __FILE__ ) ) {
		$links[] = '<a href="https://www.panopress.org/instructions/" target="_blank">' . esc_html( PP_APP_NAME . ' ' . pp__( 'Instructions' ) ) . '</a>';
		$links[] = '<a href="https://www.panopress.org/forums/" target="_blank">' . esc_html( PP_APP_NAME . ' ' . pp__( 'Forums' ) ) . '</a>';
		$links[] = '<a href="https://wordpress.org/extend/plugins/panopress/" target="_blank">' . esc_html( pp__( 'WordPress Plugin Page' ) ) . '</a>';
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'pp_set_plugin_meta', 10, 2 );

/**
 * Helpers de traduction avec text domain.
 */
function pp__( string $msg ): string {
	return __( $msg, 'panopress' );
}

function pp_e( string $msg ): void {
	echo esc_html( pp__( $msg ) );
}

/**
 * Retourne le HTML d'un message d'erreur (échappé).
 */
function pp_error( string $msg ): string {
	return '<div class="pp-error-msg"><strong>' . esc_html( pp__( 'Error' ) ) . '</strong><br/>' . wp_kses_post( $msg ) . '</div>';
}

/**
 * Valide et formate une valeur CSS de dimension (width/height).
 * Retourne la valeur formatée ou null si invalide.
 *
 * @param string $size Valeur brute saisie par l'utilisateur.
 * @return string|null
 */
function pp_check_size( string $size ): ?string {
	$size = trim( $size );
	if ( $size === '' ) {
		return null;
	}
	// Nombre seul → ajoute px
	if ( preg_match( '/^\d+$/', $size ) ) {
		return $size . 'px';
	}
	// Nombre + unité CSS valide (inclut les unités modernes)
	if ( preg_match( '/^(\d+(?:\.\d+)?)(px|em|ex|rem|%|in|cm|mm|pt|pc|vw|vh)$/', $size, $m ) ) {
		return $m[1] . $m[2];
	}
	return null;
}

/**
 * Génère le HTML d'intégration pour un panorama.
 *
 * @param array       $settings Paramètres PanoPress.
 * @param array|null  $params   Paramètres Flash/HTML supplémentaires.
 * @param string      $type     Type de viewer (flash, html, link).
 * @param string      $version  Version minimale requise.
 * @return string HTML d'intégration.
 */
function pp_embed( array $settings, ?array $params = null, string $type = PP_VIEWER_TYPE_FLASH, string $version = PP_DEFAULT_FLASH_VERSION ): string {
	global $pp_id_counter;
	$id = 'pp_' . $pp_id_counter++;

	if ( pp_is_mobile() && $settings[PP_SETTINGS_PANOBOX_ACTIVE] ) {
		$settings[PP_SETTINGS_PANOBOX_ACTIVE] = $settings[PP_SETTINGS_PANOBOX_MOBILE];
	}

	if ( $type === PP_VIEWER_TYPE_FLASH ) {
		$params['wmode'] = $settings[PP_SETTINGS_PANOBOX_ACTIVE]
			? $settings[PP_SETTINGS_PANOBOX_WMODE]
			: $settings[PP_SETTINGS_WMODE];
	}

	$embed = [
		PP_SETTINGS_ID            => $id,
		PP_SETTINGS_VIEWER_TYPE   => $type,
		PP_SETTINGS_VIEWER_VRSION => $version,
		PP_SETTINGS_VIEWER_NAME   => $settings[PP_SETTINGS_VIEWER_NAME],
		PP_SETTINGS_WIDTH         => $settings[PP_SETTINGS_WIDTH],
		PP_SETTINGS_HEIGHT        => $settings[PP_SETTINGS_HEIGHT],
		PP_SETTINGS_TITLE         => $settings[PP_SETTINGS_TITLE],
		PP_SETTINGS_ALT           => $settings[PP_SETTINGS_ALT],
		PP_SETTINGS_PLAY_BUTTON   => $settings[PP_SETTINGS_PLAY_BUTTON],
		PP_SETTINGS_PANOBOX       => $settings[PP_SETTINGS_PANOBOX_ACTIVE],
		PP_SETTINGS_PREVIEW       => $settings[PP_SETTINGS_PREVIEW],
		PP_SETTINGS_FILE          => $settings[PP_SETTINGS_FILE],
		PP_SETTINGS_PARAMS        => $params,
	];

	$w   = esc_attr( $settings[PP_SETTINGS_WIDTH] );
	$h   = esc_attr( $settings[PP_SETTINGS_HEIGHT] );
	$alt = esc_html( $settings[PP_SETTINGS_ALT] );

	$html = "\n<!-- " . PP_APP_NAME . ' [' . PP_APP_VERSION . "] -->\n";

	if ( empty( $settings[PP_SETTINGS_PREVIEW] ) && $settings[PP_SETTINGS_PANOBOX_ACTIVE] ) {
		$html .= '<div class="pp-embed">' . "\n";
		$html .= '<div id="' . esc_attr( $id ) . '">' . $alt . '</div>' . "\n";
	} else {
		$preview_html = '';
		if ( ! empty( $settings[PP_SETTINGS_PREVIEW] ) ) {
			$preview_html = '<img src="' . esc_url( $settings[PP_SETTINGS_PREVIEW] ) . '" style="width:' . $w . '; height:' . $h . '" alt="' . $alt . '"/>';
		}
		$html .= '<div class="pp-embed" style="position:relative;">' . "\n";
		$html .= '<div id="' . esc_attr( $id ) . '" style="width:' . $w . '; height:' . $h . '">'
			. $preview_html
			. '<p>' . $alt . '</p></div>' . "\n";
	}

	$html .= '<script>panopress.embed(' . wp_json_encode( $embed ) . ')</script>' . "\n";
	$html .= '<noscript>' . pp_error( pp__( 'Javascript not activated' ) ) . '</noscript>' . "\n";
	$html .= '</div>' . "\n";
	$html .= '<!-- /' . PP_APP_NAME . " -->\n";

	return $html;
}

/**
 * Gère les types de fichiers inconnus.
 */
function pp_unknown( array $settings ): string {
	if ( PP_ALLOW_UNKNOWN_FILE_TYPES ) {
		return pp_html( $settings );
	}
	$settings[PP_SETTINGS_PANOBOX_ACTIVE] = false;
	return pp_embed( $settings, null, PP_VIEWER_TYPE_LINK, '0' );
}

/**
 * Intègre un fichier HTML.
 */
function pp_html( array $settings ): string {
	$base = substr( $settings[PP_SETTINGS_FILE], 0, strrpos( $settings[PP_SETTINGS_FILE], '/' ) + 1 );
	return pp_embed( $settings, [ 'base' => $base ], PP_VIEWER_TYPE_HTML, '4.0' );
}

/**
 * Intègre un fichier SWF (Flash).
 */
function pp_swf( array $settings ): string {
	$got_name = pp_get_viewr_name( str_ireplace( '.swf', '.xml', $settings[PP_SETTINGS_FILE] ) );
	$settings[PP_SETTINGS_VIEWER_NAME] = $got_name['status'] === 1 ? $got_name['content'] : 0;
	$base = substr( $settings[PP_SETTINGS_FILE], 0, strrpos( $settings[PP_SETTINGS_FILE], '/' ) + 1 );
	return pp_embed( $settings, [ 'base' => $base ], PP_VIEWER_TYPE_FLASH, '9.0.0' );
}

/**
 * Intègre un fichier MOV via CuTy.
 */
function pp_mov( array $settings ): string {
	$cutyURL = plugins_url( '/flash/cuty.swf', __FILE__ );
	$rq      = pp_get_url( $cutyURL, true );
	if ( $rq['status'] !== 200 ) {
		$cutyURL = site_url( '/' . $settings[PP_SETTINGS_UPLOAD_DIR] . '/cuty.swf' );
		$rq      = pp_get_url( $cutyURL, true );
	}
	if ( $rq['status'] !== 200 ) {
		return is_user_logged_in() ? pp_error( pp__( "Can't find CuTy" ) ) : '';
	}
	$settings[PP_SETTINGS_FILE]        = $cutyURL . '?mov=' . rawurlencode( $settings[PP_SETTINGS_FILE] );
	$settings[PP_SETTINGS_VIEWER_NAME] = PP_VIEWER_NAME_CUTY;
	return pp_embed( $settings, null, PP_VIEWER_TYPE_FLASH, '10.0.0' );
}

/**
 * Intègre un XML Pano2VR.
 */
function pp_xml_pano2vr( array $settings ): string {
	$base = substr( $settings[PP_SETTINGS_FILE], 0, strrpos( $settings[PP_SETTINGS_FILE], '/' ) + 1 );
	if ( pp_is_mobile() ) {
		$xml = $settings[PP_SETTINGS_FILE];
		$xml = substr( $xml, 7 );
		$xml = substr( $xml, strpos( $xml, '/' ) + 1 );
		$settings[PP_SETTINGS_FILE] = plugins_url( 'pano2vr.php', __FILE__ );
		return pp_embed( $settings, [ 'xml' => $xml ], PP_VIEWER_TYPE_HTML, '5.0' );
	}
	$settings[PP_SETTINGS_FILE] = substr( $settings[PP_SETTINGS_FILE], 0, strrpos( $settings[PP_SETTINGS_FILE], '.' ) + 1 ) . 'swf';
	return pp_embed( $settings, [ 'base' => $base ], PP_VIEWER_TYPE_FLASH, '9.0.0' );
}

/**
 * Intègre un XML KRPano.
 */
function pp_xml_krpano( array $settings ): string {
	$xml = $settings[PP_SETTINGS_FILE];
	if ( pp_is_mobile() ) {
		$xml = substr( $xml, 7 );
		$xml = substr( $xml, strpos( $xml, '/' ) + 1 );
	}
	if ( $settings[PP_SETTINGS_USE_VIEWER_DIR] ) {
		$swf = site_url( '/' . $settings[PP_SETTINGS_VIEWER_DIR] . '/krpano.swf' );
	} else {
		$str = substr( $xml, 0, strlen( $settings[PP_SETTINGS_FILE] ) - 3 );
		$swf = $str . 'swf';
	}
	if ( pp_is_mobile() ) {
		$settings[PP_SETTINGS_FILE] = plugins_url( 'krpano.php', __FILE__ );
		return pp_embed( $settings, [ 'xml' => $xml ], PP_VIEWER_TYPE_HTML, '5.0' );
	}
	$settings[PP_SETTINGS_FILE] = $swf;
	return pp_embed( $settings, [ 'flashvars' => [ 'xml' => $xml ] ], PP_VIEWER_TYPE_FLASH, '9.0.28' );
}

/**
 * Intègre un XML FPP (PanoTour Pro).
 */
function pp_xml_fpp( array $settings ): string {
	$xml = $settings[PP_SETTINGS_FILE];
	if ( $settings[PP_SETTINGS_USE_VIEWER_DIR] ) {
		$swf = site_url( '/' . $settings[PP_SETTINGS_VIEWER_DIR] . '/fpp.swf' );
	} else {
		$swf = substr( $xml, 0, strlen( $xml ) - 3 ) . 'swf';
	}
	$settings[PP_SETTINGS_FILE] = $swf;
	return pp_embed(
		$settings,
		[
			'base'      => substr( $xml, 0, strrpos( $xml, '/' ) + 1 ),
			'flashvars' => [ 'xml_file' => $xml ],
		],
		PP_VIEWER_TYPE_FLASH,
		'9.0.0'
	);
}

/**
 * Sélectionne la fonction d'intégration adaptée au type de fichier.
 */
function pp_select( array $settings ): string {
	$settings[PP_SETTINGS_WIDTH]  = pp_check_size( $settings[PP_SETTINGS_WIDTH] ?? '' );
	$settings[PP_SETTINGS_HEIGHT] = pp_check_size( $settings[PP_SETTINGS_HEIGHT] ?? '' );

	if ( $settings[PP_SETTINGS_TYPE] === PP_FILE_TYPE_SWF ) {
		return pp_swf( $settings );
	}

	if ( $settings[PP_SETTINGS_TYPE] === PP_FILE_TYPE_MOV ) {
		return pp_mov( $settings );
	}

	if ( $settings[PP_SETTINGS_TYPE] === PP_FILE_TYPE_XML ) {
		return match ( $settings[PP_SETTINGS_VIEWER_NAME] ) {
			PP_VIEWER_NAME_PANO2VR => pp_xml_pano2vr( $settings ),
			PP_VIEWER_NAME_KRPANO  => pp_xml_krpano( $settings ),
			PP_VIEWER_NAME_FPP     => pp_xml_fpp( $settings ),
			default                => pp_error( pp__( 'Viewer is not supported' ) ),
		};
	}

	if ( ! PP_FILE_TYPE_FILTERING ) {
		$settings[PP_SETTINGS_TYPE] = PP_FILE_TYPE_HTML;
		return pp_html( $settings );
	}

	// Slash final → traité comme HTML
	if ( str_ends_with( $settings[PP_SETTINGS_FILE], '/' ) ) {
		$settings[PP_SETTINGS_TYPE] = PP_FILE_TYPE_HTML;
		return pp_html( $settings );
	}

	// Extensions web courantes → traité comme HTML
	foreach ( [ 'htm', 'php', 'asp', 'jsp', 'cfm', 'cgi', 'pl' ] as $ext ) {
		if ( str_contains( $settings[PP_SETTINGS_TYPE], $ext ) ) {
			$settings[PP_SETTINGS_TYPE] = PP_FILE_TYPE_HTML;
			return pp_html( $settings );
		}
	}

	$settings[PP_SETTINGS_TYPE] = PP_FILE_TYPE_UNKNOWN;
	return pp_unknown( $settings );
}

/**
 * Gestionnaire du shortcode [pano].
 *
 * @param array $attributes Attributs du shortcode.
 * @return string HTML généré.
 */
function pp_sohrtcode_handler( array $attributes ): string {
	global $pp_settings;

	// Correspondances clé courte → clé complète
	$att = [
		'f' => PP_SETTINGS_FILE,
		'w' => PP_SETTINGS_WIDTH,
		'h' => PP_SETTINGS_HEIGHT,
		'a' => PP_SETTINGS_ALT,
		't' => PP_SETTINGS_TITLE,
		'p' => PP_SETTINGS_PREVIEW,
		'b' => PP_SETTINGS_PANOBOX,
		'n' => PP_SETTINGS_PLAY_BUTTON,
	];

	// Récupère et nettoie les attributs en acceptant les clés courtes
	$clean = [];
	foreach ( $att as $short => $full ) {
		if ( array_key_exists( $full, $attributes ) ) {
			$clean[$full] = $attributes[$full];
		} elseif ( array_key_exists( $short, $attributes ) ) {
			$clean[$full] = $attributes[$short];
		}
	}

	// Sanitisation des champs texte
	foreach ( [ PP_SETTINGS_ALT, PP_SETTINGS_TITLE ] as $field ) {
		if ( isset( $clean[$field] ) ) {
			$clean[$field] = sanitize_text_field( $clean[$field] );
		}
	}

	// Sanitisation des URLs
	foreach ( [ PP_SETTINGS_FILE, PP_SETTINGS_PREVIEW ] as $field ) {
		if ( isset( $clean[$field] ) ) {
			$clean[$field] = sanitize_url( $clean[$field] );
		}
	}

	// Bouton lecture
	if ( isset( $clean[PP_SETTINGS_PLAY_BUTTON] ) ) {
		$clean[PP_SETTINGS_PLAY_BUTTON] = pp_bool( $clean[PP_SETTINGS_PLAY_BUTTON] );
	}

	// Panobox
	if ( isset( $clean[PP_SETTINGS_PANOBOX] ) ) {
		$clean[PP_SETTINGS_PANOBOX_ACTIVE] = pp_bool( $clean[PP_SETTINGS_PANOBOX] );
		unset( $clean[PP_SETTINGS_PANOBOX] );
	}

	// Fusionne avec les réglages par défaut
	$settings = array_merge( $pp_settings, $clean );

	if ( empty( $settings[PP_SETTINGS_FILE] ) ) {
		return pp_error( pp__( 'Please enter file name or URL' ) );
	}

	// Détermine le type par l'extension
	$filestr = strtolower( $settings[PP_SETTINGS_FILE] );
	if ( str_contains( $filestr, '?' ) ) {
		$filestr = substr( $filestr, 0, strpos( $filestr, '?' ) );
	}
	$file_name = substr( $filestr, strrpos( $filestr, '/' ) );
	$settings[PP_SETTINGS_TYPE] = substr( $file_name, strrpos( $file_name, '.' ) + 1 );

	// Si pas une URL complète, construit l'URL locale
	if ( strtolower( substr( $settings[PP_SETTINGS_FILE], 0, 4 ) ) !== 'http' ) {
		$settings[PP_SETTINGS_FILE] = site_url( '/' . $pp_settings[PP_SETTINGS_UPLOAD_DIR] . '/' . $settings[PP_SETTINGS_FILE] );
	}
	if ( ! empty( $settings[PP_SETTINGS_PREVIEW] ) && strtolower( substr( $settings[PP_SETTINGS_PREVIEW], 0, 4 ) ) !== 'http' ) {
		$settings[PP_SETTINGS_PREVIEW] = site_url( '/' . $pp_settings[PP_SETTINGS_UPLOAD_DIR] . '/' . $settings[PP_SETTINGS_PREVIEW] );
	}

	// Encode les espaces dans les URLs
	$settings[PP_SETTINGS_PREVIEW] = str_replace( ' ', '%20', $settings[PP_SETTINGS_PREVIEW] );
	$settings[PP_SETTINGS_FILE]    = str_replace( ' ', '%20', $settings[PP_SETTINGS_FILE] );

	// Traitement des fichiers XML
	if ( $settings[PP_SETTINGS_TYPE] === PP_FILE_TYPE_XML ) {
		$got_name = pp_get_viewr_name( $settings[PP_SETTINGS_FILE] );
		if ( $got_name['status'] === 1 ) {
			$settings[PP_SETTINGS_VIEWER_NAME] = $got_name['content'];
		} elseif ( is_user_logged_in() ) {
			return pp_error( esc_html( $got_name['content'] ) );
		}

		libxml_use_internal_errors( is_user_logged_in() );
		$xml = false;

		if ( ini_get( 'allow_url_fopen' ) ) {
			$xml = is_user_logged_in()
				? simplexml_load_file( $settings[PP_SETTINGS_FILE] )
				: @simplexml_load_file( $settings[PP_SETTINGS_FILE] );
		} elseif ( function_exists( 'wp_remote_get' ) ) {
			$results = pp_get_url( $settings[PP_SETTINGS_FILE] );
			if ( $results['status'] === 200 ) {
				$xml = simplexml_load_string( $results['content'] );
			} elseif ( is_user_logged_in() ) {
				return pp_error( pp__( "Can't find XML file" ) . ' ' . esc_url( $settings[PP_SETTINGS_FILE] ) );
			}
		} elseif ( is_user_logged_in() ) {
			return pp_error( '<p>' . pp__( '"allow_url_fopen" is not enabled on this server and the WordPress HTTP API is unavailable.' ) . '</p>' );
		}

		// Erreurs XML (admin uniquement)
		if ( $xml === false && is_user_logged_in() ) {
			$err = '';
			foreach ( libxml_get_errors() as $error ) {
				$err .= esc_html( $error->message ) . ' (line ' . (int) $error->line . ')<br />';
			}
			return pp_error( '<p>' . $err . '</p>' );
		}

		if ( $xml instanceof SimpleXMLElement ) {
			$root = $xml->getName();
			if ( $root === 'krpano' ) {
				$settings[PP_SETTINGS_VIEWER_NAME] = PP_VIEWER_NAME_KRPANO;
			} elseif ( $root === 'panorama' ) {
				$settings[PP_SETTINGS_VIEWER_NAME] = PP_VIEWER_NAME_PANO2VR;
				foreach ( $xml->children() as $child ) {
					if ( $child->getName() === 'parameters' ) {
						$settings[PP_SETTINGS_VIEWER_NAME] = PP_VIEWER_NAME_FPP;
					}
				}
			}
		} else {
			return (string) ( $settings[PP_SETTINGS_ALT] ?? '' );
		}
	}

	return pp_select( $settings );
}

/**
 * Convertit une valeur textuelle en booléen.
 */
function pp_bool( string $subject ): bool {
	return in_array( strtolower( $subject ), [ 'true', 'on', 'yes', '1' ], true );
}

// Enregistre le shortcode [pano]
add_shortcode( 'pano', 'pp_sohrtcode_handler' );

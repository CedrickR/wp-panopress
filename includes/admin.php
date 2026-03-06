<?php
// Empêche l'accès direct au fichier.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pp_option_page = null;

/***********************************************************************
 * Initialisation de l'admin
 **********************************************************************/
function pp_admin_init(): void {
	wp_register_script(
		'pp_admin_js',
		plugins_url( '/js/admin.js', dirname( __FILE__ ) ),
		[],
		PP_APP_VERSION
	);
	pp_add_tynymce_button();
}

/***********************************************************************
 * Ajout du menu d'administration
 **********************************************************************/
function pp_admin_menu(): void {
	global $pp_option_page;
	$pp_option_page = add_options_page(
		PP_APP_NAME . ' ' . pp__( 'Settings' ),
		PP_APP_NAME,
		'manage_options',
		strtolower( PP_APP_NAME ),
		'pp_edit_settings'
	);
	add_action( 'admin_print_scripts-' . $pp_option_page, 'pp_admin_headers' );
	add_action( 'load-' . $pp_option_page, 'pp_add_help_tab' );
}

/***********************************************************************
 * Chargement du script admin
 **********************************************************************/
function pp_admin_headers(): void {
	wp_enqueue_script( 'pp_admin_js' );
}

/***********************************************************************
 * Injection des variables JS en admin (échappées)
 **********************************************************************/
function pp_admin_print_scripts(): void {
	global $pp_settings;
	$upload_url = esc_js( site_url( '/' . $pp_settings[PP_SETTINGS_UPLOAD_DIR] . '/' ) );
	$width      = esc_js( $pp_settings[PP_SETTINGS_WIDTH] ?? '' );
	$height     = esc_js( $pp_settings[PP_SETTINGS_HEIGHT] ?? '' );
	echo '<script>
var PP_SETTINGS_UPLOAD_DIR = "' . $upload_url . '";
var PP_SETTINGS_WIDTH = \'' . $width . '\';
var PP_SETTINGS_HEIGHT = \'' . $height . '\';
</script>';
}

/***********************************************************************
 * Plugin TinyMCE
 **********************************************************************/
function pp_load_tinymce_plugin( array $plugin_array ): array {
	$plugin_array['panopress'] = plugins_url( '/js/tinymce/editor_plugin.js', dirname( __FILE__ ) );
	return $plugin_array;
}

/***********************************************************************
 * Bouton TinyMCE
 **********************************************************************/
function pp_load_tynymce_button( array $buttons ): array {
	array_push( $buttons, 'separator', 'pp_button' );
	return $buttons;
}

/***********************************************************************
 * Ajout du bouton TinyMCE
 **********************************************************************/
function pp_add_tynymce_button(): void {
	if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
		return;
	}
	if ( get_user_option( 'rich_editing' ) === 'true' ) {
		add_filter( 'mce_external_plugins', 'pp_load_tinymce_plugin' );
		add_filter( 'mce_buttons', 'pp_load_tynymce_button' );
	}
}

/***********************************************************************
 * Onglet d'aide contextuelle (remplace le filtre contextual_help déprécié depuis WP 3.3)
 **********************************************************************/
function pp_add_help_tab(): void {
	$screen = get_current_screen();
	if ( ! $screen ) {
		return;
	}
	$screen->add_help_tab( [
		'id'      => 'pp_help',
		'title'   => pp__( 'Documentation' ),
		'content' => '<p><a href="https://www.panopress.org/instructions/" target="_blank">PanoPress ' . esc_html( pp__( 'Documentation' ) ) . '</a></p>',
	] );
}

/***********************************************************************
 * Désinstallation du plugin
 **********************************************************************/
function pp_uninstall(): void {
	delete_option( PP_SETTINGS );
}

/***********************************************************************
 * Enregistrement des actions admin
 **********************************************************************/
if ( is_admin() ) {
	add_action( 'admin_init', 'pp_admin_init' );
	add_action( 'admin_menu', 'pp_admin_menu' );
	add_action( 'admin_print_scripts', 'pp_admin_print_scripts' );
	// Le hook de désinstallation doit pointer vers le fichier principal du plugin.
	register_uninstall_hook( dirname( dirname( __FILE__ ) ) . '/panopress.php', 'pp_uninstall' );
}

/***********************************************************************
 * Retourne le chemin absolu vers la racine WordPress.
 * Utilise ABSPATH (fiable) au lieu de $_SERVER['SCRIPT_FILENAME'].
 *
 * @param string $path Chemin à ajouter à la racine.
 **********************************************************************/
function pp_wp_root( string $path = '' ): string {
	return ABSPATH . ltrim( strtolower( $path ), '/' );
}

/***********************************************************************
 * Page de réglages PanoPress
 **********************************************************************/
function pp_edit_settings(): void {
	global $pp_wp_upload_dir, $pp_settings;

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html( pp__( 'You do not have permission to access this page.' ) ) );
	}

	/* Traitement du formulaire */
	if ( ! empty( $_POST ) && ! empty( $_POST['pp_action'] ) ) {

		// Vérification du nonce CSRF
		if ( ! wp_verify_nonce( $_POST['pp-nonce'] ?? '', 'pp-settings-action' ) ) {
			wp_die( esc_html( pp__( 'Security check failed.' ) ) );
		}

		$pp_action = sanitize_key( $_POST['pp_action'] );

		/* Réinitialisation */
		if ( $pp_action === 'reset' ) {
			delete_option( PP_SETTINGS );
			pp_default_settings();

		} elseif ( $pp_action === 'update' ) {

			// Valeurs autorisées pour les selects
			$allowed_wmode = [ 'auto', 'window', 'opaque', 'transparent' ];
			$allowed_oppp  = [ PP_OPPP_ALL, PP_OPPP_MOBILE, PP_OPPP_DISABLED ];

			// Traitement du style Panobox (format "clé:valeur,clé:valeur")
			$style_raw = sanitize_text_field( $_POST[ PP_SETTINGS_PANOBOX . '_' . PB_SETTINGS_STYLE ] ?? '' );
			$style     = [];
			foreach ( explode( ',', $style_raw ) as $item ) {
				$parts = explode( ':', $item, 2 );
				if ( count( $parts ) === 2 ) {
					$style[ sanitize_key( $parts[0] ) ] = sanitize_text_field( $parts[1] );
				}
			}

			// Réglages PanoPress
			$pp_settings = [];
			$pp_settings[PP_SETTINGS_WIDTH]          = pp_check_size( sanitize_text_field( $_POST[PP_SETTINGS_WIDTH] ?? '' ) );
			$pp_settings[PP_SETTINGS_HEIGHT]         = pp_check_size( sanitize_text_field( $_POST[PP_SETTINGS_HEIGHT] ?? '' ) );
			$pp_settings[PP_SETTINGS_UPLOAD_WP]      = ! empty( $_POST[PP_SETTINGS_UPLOAD_WP] );
			$pp_settings[PP_SETTINGS_UPLOAD_DIR]     = $pp_settings[PP_SETTINGS_UPLOAD_WP]
				? $pp_wp_upload_dir
				: trim( strtolower( sanitize_text_field( $_POST[PP_SETTINGS_UPLOAD_DIR] ?? '' ) ), '/' );
			$pp_settings[PP_SETTINGS_VIEWER_DIR]     = trim( strtolower( sanitize_text_field( $_POST[PP_SETTINGS_VIEWER_DIR] ?? '' ) ), '/' );
			$pp_settings[PP_SETTINGS_USE_VIEWER_DIR] = ! empty( $_POST[PP_SETTINGS_USE_VIEWER_DIR] );

			$wmode = sanitize_key( $_POST[PP_SETTINGS_WMODE] ?? 'auto' );
			$pp_settings[PP_SETTINGS_WMODE] = in_array( $wmode, $allowed_wmode, true ) ? $wmode : 'auto';

			$oppp = sanitize_key( $_POST[PP_SETTINGS_OPPP] ?? PP_OPPP_MOBILE );
			$pp_settings[PP_SETTINGS_OPPP] = in_array( $oppp, $allowed_oppp, true ) ? $oppp : PP_OPPP_MOBILE;

			$pb_wmode = sanitize_key( $_POST[PP_SETTINGS_PANOBOX_WMODE] ?? 'auto' );
			$pp_settings[PP_SETTINGS_PANOBOX_WMODE]  = in_array( $pb_wmode, $allowed_wmode, true ) ? $pb_wmode : 'auto';

			$pp_settings[PP_SETTINGS_PLAY_BUTTON]    = ( ( $_POST[PP_SETTINGS_PLAY_BUTTON] ?? '' ) === '1' );
			$pp_settings[PP_SETTINGS_PANOBOX_ACTIVE] = ( ( $_POST[PP_SETTINGS_PANOBOX_ACTIVE] ?? '' ) === '1' );
			$pp_settings[PP_SETTINGS_PANOBOX_MOBILE] = ( ( $_POST[PP_SETTINGS_PANOBOX_MOBILE] ?? '' ) !== '1' );
			$pp_settings[PP_SETTINGS_CSS]            = sanitize_textarea_field( $_POST[PP_SETTINGS_CSS] ?? '' );

			// Réglages Panobox
			$pp_settings[PP_SETTINGS_PANOBOX] = [];
			$pb_key = PP_SETTINGS_PANOBOX . '_';
			$pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_FULLSCREEN] = ( ( $_POST[ $pb_key . PB_SETTINGS_FULLSCREEN ] ?? '' ) === '1' );
			$pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_FADE]       = ( ( $_POST[ $pb_key . PB_SETTINGS_FADE ]       ?? '' ) === '1' );
			$pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_ANIMATE]    = ( ( $_POST[ $pb_key . PB_SETTINGS_ANIMATE ]    ?? '' ) === '1' );
			$pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_SHADOW]     = ( ( $_POST[ $pb_key . PB_SETTINGS_SHADOW ]     ?? '' ) === '1' );
			$pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_WIDTH]      = pp_check_size( sanitize_text_field( $_POST[ $pb_key . PB_SETTINGS_WIDTH ]  ?? '' ) );
			$pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_HEIGHT]     = pp_check_size( sanitize_text_field( $_POST[ $pb_key . PB_SETTINGS_HEIGHT ] ?? '' ) );
			$pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_STYLE]      = $style;
			$pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_GALLERIES]  = ( ( $_POST[ $pb_key . PB_SETTINGS_GALLERIES ]  ?? '' ) === '1' );

			/* Sauvegarde des réglages */
			if ( get_option( PP_SETTINGS ) !== false ) {
				update_option( PP_SETTINGS, $pp_settings );
			} else {
				add_option( PP_SETTINGS, $pp_settings );
			}
		}
	}

	// État des panneaux repliables (sanitisé)
	$advanced_open = sanitize_key( $_POST['advanced_open'] ?? 'hide' );
	$panobox_open  = sanitize_key( $_POST['panobox_open']  ?? 'hide' );
	$display_adv   = $advanced_open === 'show' ? '' : 'none';
	$display_pb    = $panobox_open  === 'show' ? '' : 'none';

	// Données pour les selects de style Panobox
	$pb_styles = [
		'pb-light'    => [ 'label' => 'Light',    'overlay' => 'pb-light-overlay' ],
		'pb-dark'     => [ 'label' => 'Dark',     'overlay' => 'pb-dark-overlay' ],
		'pb-adaptive' => [ 'label' => 'Adaptive', 'overlay' => 'pb-adaptive-overlay' ],
	];
	$current_box = $pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_STYLE][PB_SETTINGS_STYLE_BOX] ?? '';
	?>
<style type="text/css" media="screen">
.pp-advanced-settings{display:<?php echo esc_attr( $display_adv ); ?>}
th, td{white-space:nowrap}
label{padding-left:4px}
input:disabled{opacity:.5}
</style>
<div class="wrap">
<div style="float:right">Version <?php echo esc_html( PP_APP_VERSION ); ?></div>
<div id="icon-options-general" class="icon32"></div>
<h2><?php echo esc_html( PP_APP_NAME . ' ' . pp__( 'Settings' ) ); ?></h2>
<div id="pp_notify" style="margin-top:6px;font-weight:bold;display:none"></div>
<form method="post" id="pp-settings" name="pp-settings" action="">
<input type="hidden" id="pp_action" name="pp_action" value="update" />
<?php wp_nonce_field( 'pp-settings-action', 'pp-nonce', true ); ?>
<table class="form-table">
	<tr valign="top">
		<th scope="row"><?php pp_e( 'Embed Size' ); ?></th>
		<td colspan="2">
			<?php pp_e( 'Width' ); ?>: <input type="text" name="<?php echo esc_attr( PP_SETTINGS_WIDTH ); ?>" value="<?php echo esc_attr( $pp_settings[PP_SETTINGS_WIDTH] ?: PP_DEFAULT_WIDTH ); ?>" size="6" />
			<?php pp_e( 'Height' ); ?>: <input type="text" name="<?php echo esc_attr( PP_SETTINGS_HEIGHT ); ?>" value="<?php echo esc_attr( $pp_settings[PP_SETTINGS_HEIGHT] ?: PP_DEFAULT_HEIGHT ); ?>" size="6" />
			&nbsp;<span class="description"><?php pp_e( 'you may use px, %, em, or other standard' ); ?> <a href="https://www.w3schools.com/cssref/css_units.asp" target="_blank"><?php pp_e( 'CSS units' ); ?></a>. Examples: 800px, 100%, 2.5em, etc.</span>
		</td>
	</tr>
	<tr valign="top" style="background-color:#eee">
		<th scope="row"><?php pp_e( 'Style' ); ?></th>
		<td colspan="2">
			<input id="play-button" name="<?php echo esc_attr( PP_SETTINGS_PLAY_BUTTON ); ?>" value="1" type="checkbox"<?php checked( $pp_settings[PP_SETTINGS_PLAY_BUTTON] ); ?> />
			<label for="play-button"><?php pp_e( 'Show Play button' ); ?></label>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php pp_e( 'Panobox' ); ?></th>
		<td colspan="2">
			<input id="panobox-active" name="<?php echo esc_attr( PP_SETTINGS_PANOBOX_ACTIVE ); ?>" value="1" type="checkbox"<?php checked( $pp_settings[PP_SETTINGS_PANOBOX_ACTIVE] ); ?> />
			<label for="panobox-active"><?php pp_e( 'Open panoramas in Panobox' ); ?></label>
			<input type="hidden" id="panobox-open" name="panobox_open" value="<?php echo esc_attr( $panobox_open ); ?>" />
			<br />
			<input id="panobox-galleries" name="<?php echo esc_attr( PP_SETTINGS_PANOBOX . '_' . PB_SETTINGS_GALLERIES ); ?>" value="1" type="checkbox"<?php checked( $pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_GALLERIES] ); ?> />
			<label for="panobox-galleries"><?php pp_e( 'Open image galleries in Panobox' ); ?></label>
			<br />
			<a id="panobox-options-label" href="javascript:toggle_panobox_options()">
				<?php echo $panobox_open === 'show' ? esc_html( pp__( 'Customize Panobox' ) ) : esc_html( pp__( 'Customize Panobox...' ) ); ?>
			</a>
			<br/>
			<table id="panobox-options" style="display:<?php echo esc_attr( $display_pb ); ?>">
				<tr>
					<td nowrap valign="top"><?php pp_e( 'Window Size' ); ?>:</td>
					<td>
						<?php
						$pb_w_name = esc_attr( PP_SETTINGS_PANOBOX . '_' . PB_SETTINGS_WIDTH );
						$pb_h_name = esc_attr( PP_SETTINGS_PANOBOX . '_' . PB_SETTINGS_HEIGHT );
						$pb_w_val  = esc_attr( $pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_WIDTH]  ?: PP_DEFAULT_WIDTH );
						$pb_h_val  = esc_attr( $pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_HEIGHT] ?: PP_DEFAULT_HEIGHT );
						$fs_disabled = $pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_FULLSCREEN] ? ' disabled="disabled"' : '';
						?>
						<?php pp_e( 'Width' ); ?>: <input onchange="document.forms[0].<?php echo $pb_w_name; ?>.value = this.value" id="panobox-width" type="text" value="<?php echo $pb_w_val; ?>" size="6"<?php echo $fs_disabled; ?> />
						<?php pp_e( 'Height' ); ?>: <input onchange="document.forms[0].<?php echo $pb_h_name; ?>.value = this.value" id="panobox-height" type="text" value="<?php echo $pb_h_val; ?>" size="6"<?php echo $fs_disabled; ?> />
						<input type="hidden" name="<?php echo $pb_w_name; ?>" value="<?php echo $pb_w_val; ?>" />
						<input type="hidden" name="<?php echo $pb_h_name; ?>" value="<?php echo $pb_h_val; ?>" />
						&nbsp;<span class="description"><?php pp_e( 'in CSS units' ); ?></span>
						<br />
						<input id="panobox-fullscreen" onchange="toggle_panobox_fulscreen(this.checked)" name="<?php echo esc_attr( PP_SETTINGS_PANOBOX . '_' . PB_SETTINGS_FULLSCREEN ); ?>" value="1" type="checkbox"<?php checked( $pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_FULLSCREEN] ); ?> />
						<label for="panobox-fullscreen"><?php pp_e( 'Use Fullscreen' ); ?></label>
					</td>
				</tr>
				<tr>
					<td><?php pp_e( 'Style' ); ?>:</td>
					<td>
						<select name="<?php echo esc_attr( PP_SETTINGS_PANOBOX . '_' . PB_SETTINGS_STYLE ); ?>">
						<?php foreach ( $pb_styles as $box_class => $info ) :
							$val = PB_SETTINGS_STYLE_BOX . ':' . $box_class . ',' . PB_SETTINGS_STYLE_OVERLAY . ':' . $info['overlay'];
						?>
						<option value="<?php echo esc_attr( $val ); ?>"<?php selected( $current_box, $box_class ); ?>>&nbsp;<?php echo esc_html( $info['label'] ); ?>&nbsp;</option>
						<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<td><?php pp_e( 'Effects' ); ?>:</td>
					<td>
						<input id="panobox-shadow" name="<?php echo esc_attr( PP_SETTINGS_PANOBOX . '_' . PB_SETTINGS_SHADOW ); ?>" value="1" type="checkbox"<?php checked( $pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_SHADOW] ); ?> />
						<label for="panobox-shadow"><?php pp_e( 'Drop-shadow' ); ?></label>
						&nbsp;&nbsp;
						<input id="panobox-fade" name="<?php echo esc_attr( PP_SETTINGS_PANOBOX . '_' . PB_SETTINGS_FADE ); ?>" value="1" type="checkbox"<?php checked( $pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_FADE] ); ?> />
						<label for="panobox-fade"><?php pp_e( 'Fade-in/out' ); ?></label>
						&nbsp;&nbsp;
						<input id="panobox-animate" name="<?php echo esc_attr( PP_SETTINGS_PANOBOX . '_' . PB_SETTINGS_ANIMATE ); ?>" value="1" type="checkbox"<?php checked( $pp_settings[PP_SETTINGS_PANOBOX][PB_SETTINGS_ANIMATE] ); ?> />
						<label for="panobox-animate"><?php pp_e( 'Animated window resize' ); ?></label>
					</td>
				</tr>
				<tr>
					<td><?php pp_e( 'Mobile' ); ?>:</td>
					<td>
						<input id="panobox-mobile" name="<?php echo esc_attr( PP_SETTINGS_PANOBOX_MOBILE ); ?>" value="1" type="checkbox"<?php checked( ! $pp_settings[PP_SETTINGS_PANOBOX_MOBILE] ); ?> />
						<label for="panobox-mobile"><?php pp_e( "Don't use Panobox for mobile devices" ); ?></label>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr valign="top" class="pp-advanced-settings" style="background-color:#eee">
		<th scope="row"><?php pp_e( 'Upload Folder' ); ?></th>
		<td colspan="2">
			<input id="upload-sys" onchange="toggle_wp_ul(this.checked, '<?php echo esc_js( $pp_wp_upload_dir ); ?>')" type="checkbox" name="<?php echo esc_attr( PP_SETTINGS_UPLOAD_WP ); ?>" value="true"<?php checked( $pp_settings[PP_SETTINGS_UPLOAD_WP] ); ?> />
			<label for="upload-sys"><?php pp_e( 'Use WordPress upload folder' ); ?></label> (<?php echo esc_html( $pp_wp_upload_dir ); ?>)
			<br />
			Folder Path:&nbsp;<input style="width:320px" id="upload-dir"<?php disabled( $pp_settings[PP_SETTINGS_UPLOAD_WP] ); ?> type="text" name="<?php echo esc_attr( PP_SETTINGS_UPLOAD_DIR ); ?>" value="<?php echo esc_attr( $pp_settings[PP_SETTINGS_UPLOAD_WP] ? $pp_wp_upload_dir : $pp_settings[PP_SETTINGS_UPLOAD_DIR] ); ?>" size="36" />
			<?php if ( ! is_dir( pp_wp_root( $pp_settings[PP_SETTINGS_UPLOAD_DIR] ) ) ) : ?>
			<span class="error"><?php pp_e( 'Folder does not exist' ); ?></span>
			<?php endif; ?>
		</td>
	</tr>
	<tr valign="top" class="pp-advanced-settings">
		<th scope="row"><?php pp_e( 'Global Viewer' ); ?></th>
		<td colspan="2">
			<input id="use-viewer-dir" onchange="toggle_viewer_folder(this.checked)" type="checkbox" name="<?php echo esc_attr( PP_SETTINGS_USE_VIEWER_DIR ); ?>" value="true"<?php checked( $pp_settings[PP_SETTINGS_USE_VIEWER_DIR] ); ?> />
			<label for="use-viewer-dir"><?php pp_e( 'Use Global Viewer' ); ?></label>&nbsp;<span class="description">(<?php pp_e( 'KRPano & FPP only' ); ?> <a target="_blank" href="https://www.panopress.org/krpano-global-swf/"><?php pp_e( 'learn more' ); ?></a>)</span>
			<br />
			Folder Path:&nbsp;<input style="width:320px" id="viewer-dir"<?php disabled( ! $pp_settings[PP_SETTINGS_USE_VIEWER_DIR] ); ?> type="text" value="<?php echo esc_attr( $pp_settings[PP_SETTINGS_VIEWER_DIR] ); ?>" />
			<?php if ( ! is_dir( pp_wp_root( $pp_settings[PP_SETTINGS_VIEWER_DIR] ) ) && $pp_settings[PP_SETTINGS_USE_VIEWER_DIR] ) : ?>
			<span class="error"><?php pp_e( 'Folder does not exist' ); ?></span>
			<?php endif; ?>
			<input type="hidden" id="viewer-dir-hidden" name="<?php echo esc_attr( PP_SETTINGS_VIEWER_DIR ); ?>" value="<?php echo esc_attr( $pp_settings[PP_SETTINGS_VIEWER_DIR] ); ?>" />
		</td>
	</tr>
	<tr valign="top" class="pp-advanced-settings" style="background-color:#eee">
		<th scope="row"><?php pp_e( 'Performance' ); ?></th>
		<td colspan="2">
			<?php pp_e( 'Only one active panorama per page for' ); ?>:
			<select name="<?php echo esc_attr( PP_SETTINGS_OPPP ); ?>">
				<option value="<?php echo esc_attr( PP_OPPP_DISABLED ); ?>"<?php selected( $pp_settings[PP_SETTINGS_OPPP], PP_OPPP_DISABLED ); ?>><?php pp_e( 'None' ); ?></option>
				<option value="<?php echo esc_attr( PP_OPPP_MOBILE ); ?>"<?php selected( $pp_settings[PP_SETTINGS_OPPP], PP_OPPP_MOBILE ); ?>><?php pp_e( 'Mobile devices' ); ?>&nbsp;</option>
				<option value="<?php echo esc_attr( PP_OPPP_ALL ); ?>"<?php selected( $pp_settings[PP_SETTINGS_OPPP], PP_OPPP_ALL ); ?>><?php pp_e( 'All devices' ); ?></option>
			</select>
		</td>
	</tr>
	<tr valign="top" class="pp-advanced-settings">
		<th scope="row"><?php pp_e( 'Flash window mode' ); ?><br/>('wmode')</th>
		<td colspan="2">
		<?php pp_e( 'Embedded panoramas' ); ?>:&nbsp;
		<select name="<?php echo esc_attr( PP_SETTINGS_WMODE ); ?>">
			<?php foreach ( [ 'auto' => 'Auto', 'window' => 'Window', 'opaque' => 'Opaque', 'transparent' => 'Transparent' ] as $val => $label ) : ?>
			<option value="<?php echo esc_attr( $val ); ?>"<?php selected( $pp_settings[PP_SETTINGS_WMODE], $val ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		&nbsp;&nbsp;
		<?php pp_e( 'Panobox' ); ?>:&nbsp;
		<select name="<?php echo esc_attr( PP_SETTINGS_PANOBOX_WMODE ); ?>">
			<?php foreach ( [ 'auto' => 'Auto', 'window' => 'Window', 'opaque' => 'Opaque', 'transparent' => 'Transparent' ] as $val => $label ) : ?>
			<option value="<?php echo esc_attr( $val ); ?>"<?php selected( $pp_settings[PP_SETTINGS_PANOBOX_WMODE], $val ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		</td>
	</tr>
	<tr valign="top" class="pp-advanced-settings" style="background-color:#eee">
		<th scope="row" style="padding-top:20px"><?php pp_e( 'CSS' ); ?></th>
		<td>
			<textarea name="<?php echo esc_attr( PP_SETTINGS_CSS ); ?>" style="margin-top:10px;width:400px;height:80px"><?php echo esc_textarea( $pp_settings[PP_SETTINGS_CSS] ?? '' ); ?></textarea>
			<a href="https://www.panopress.org/css/" target="_blank"><?php pp_e( 'Class reference' ); ?></a>
		</td>
	</tr>
	<tr>
		<td>
			<input type="button" onclick="toggle_advanced()" id="toggle-advanced" class="button-secondary" value="<?php echo esc_attr( $advanced_open === 'show' ? pp__( 'Hide' ) : pp__( 'Show' ) ); ?> <?php pp_e( 'advanced options' ); ?>" />
		</td>
		<td colspan="2">
			<input type="submit" onclick="return submit_form()" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'panopress' ); ?>" />
			&nbsp;&nbsp;
			<input type="button" onclick="reset_form()" class="button-secondary" value="<?php esc_attr_e( 'Reset to defaults', 'panopress' ); ?>" />
		</td>
	</tr>
	<tr>
		<td colspan="3"><a href="https://www.panopress.org/instructions/" target="_blank"><?php echo esc_html( PP_APP_NAME ); ?> <?php pp_e( 'Instructions' ); ?></a></td>
	</tr>
</table>
<input type="hidden" id="advanced-open" name="advanced_open" value="<?php echo esc_attr( $advanced_open ); ?>" />
</form>
</div>
<script>
//<![CDATA[
var $pp2 = jQuery.noConflict();
$pp2(function(){
	if(typeof pp_loaded == 'undefined'){
		$pp2.ajax({
			url: '<?php echo esc_js( plugins_url( '/js/admin.js', dirname( __FILE__ ) ) ); ?>',
			error: function(xhr){
				var msg = '', n = $pp2('#pp_notify');
				switch(xhr.status){
					case 403: msg = '<?php echo esc_js( pp__( 'Error: 403, The access to some of ' . PP_APP_NAME . ' files was forbidden by the server. You may need to change the ' . PP_APP_NAME . ' folder permissions.' ) ); ?>'; break;
					case 404: msg = '<?php echo esc_js( pp__( 'Error: 404, Some of ' . PP_APP_NAME . ' files were not found.' ) ); ?>'; break;
					default:  msg = 'Error: ' + xhr.status + ', ' + xhr.statusText + '.';
				}
				n.html(msg);
				n.addClass('error');
				n.slideDown();
			}
		});
	}
});
//]]>
</script>
<!-- /<?php echo esc_html( PP_APP_NAME ); ?> settings -->
	<?php
}

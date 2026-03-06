<?php
/**
 * Visionneuse KRPano pour appareils mobiles (chargé en iframe).
 * Ce fichier s'exécute en dehors du contexte WordPress.
 */

// Récupère et valide le paramètre GET 'xml'.
$input = filter_input_array( INPUT_GET, FILTER_SANITIZE_SPECIAL_CHARS );
$xml   = isset( $input['xml'] ) ? (string) $input['xml'] : '';

// Refuse les URLs externes (prévention SSRF).
if ( $xml === '' || parse_url( $xml, PHP_URL_HOST ) !== null ) {
	exit;
}

// Normalise le chemin (supprime les traversées de répertoire).
$xml = ltrim( preg_replace( '/\.\.\//', '', $xml ), '/' );

// Détermine le chemin du JS KRPano associé au XML.
$js = '/' . substr( $xml, 0, strlen( $xml ) - 3 ) . 'js';

if ( ! file_exists( $_SERVER['DOCUMENT_ROOT'] . $js ) ) {
	$js = '../../panorama/global/krpano.js';
}

// Prépare les valeurs pour l'affichage HTML (échappement XSS).
$js_attr  = htmlspecialchars( $js,  ENT_QUOTES, 'UTF-8' );
$xml_js   = htmlspecialchars( $xml, ENT_QUOTES, 'UTF-8' );
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<script type="text/javascript" src="<?php echo $js_attr; ?>"></script>
</head>
<body>
<div id="krp" style="width:100%; height:100%; display:block"></div>
<script type="text/javascript">
embedpano({'target':'krp','xml':'/<?php echo $xml_js; ?>'});
document.getElementById('krp').className = 'pp-embed-content';
</script>
</body>
</html>

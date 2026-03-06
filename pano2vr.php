<?php
/**
 * Visionneuse Pano2VR pour appareils mobiles (chargé en iframe).
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
$xml  = ltrim( preg_replace( '/\.\.\//', '', $xml ), '/' );
$base = '/' . substr( $xml, 0, strrpos( $xml, '/' ) + 1 );

// Prépare les valeurs pour l'affichage HTML (échappement XSS).
$base_attr = htmlspecialchars( $base, ENT_QUOTES, 'UTF-8' );
$xml_js    = htmlspecialchars( $xml,  ENT_QUOTES, 'UTF-8' );
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<title></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="black" />
<base href="<?php echo $base_attr; ?>" />
<script type="text/javascript" src="pano2vr_player.js"></script>
<script type="text/javascript" src="skin.js"></script>
<script type="text/javascript">
	// Masque la barre d'URL sur iPhone/iPod
	function hideUrlBar() {
		if (((navigator.userAgent.match(/iPhone/i)) || (navigator.userAgent.match(/iPod/i)))) {
			var container = document.getElementById("container");
			if (container) {
				var cheight;
				switch(window.innerHeight) {
					case 208:cheight=268; break; // paysage
					case 260:cheight=320; break; // paysage, plein écran
					case 336:cheight=396; break; // portrait, barre d'état appel
					case 356:cheight=416; break; // portrait
					case 424:cheight=484; break; // portrait iPhone5, barre d'état appel
					case 444:cheight=504; break; // portrait iPhone5
					default: cheight=window.innerHeight;
				}
				if ((cheight) && ((container.offsetHeight!=cheight) || (window.innerHeight!=cheight))) {
					container.style.height=cheight + "px";
					setTimeout(function() { hideUrlBar(); }, 1000);
				}
			}
		}
		document.getElementsByTagName("body")[0].style.marginTop="1px";
		window.scrollTo(0, 1);
	}
	window.addEventListener("load", hideUrlBar);
	window.addEventListener("resize", hideUrlBar);
	window.addEventListener("orientationchange", hideUrlBar);
</script>

<style type="text/css" title="Default">
	body, div, h1, h2, h3, span, p {
		font-family: Verdana,Arial,Helvetica,sans-serif;
		color: #000000;
	}
	/* plein écran */
	html { height:100%; }
	body {
		height:100%;
		margin: 0px;
		overflow:hidden;
		font-size: 10pt;
		background: #ffffff;
	}
	table,tr,td {
		font-size: 10pt;
		border-color: #777777;
		background: #dddddd;
		color: #000000;
		border-style: solid;
		border-width: 2px;
		padding: 5px;
		border-collapse: collapse;
	}
	h1 { font-size: 18pt; }
	h2 { font-size: 14pt; }
	.warning { font-weight: bold; }
	/* correctif barres de défilement webkit / Mac OS X Lion */
	::-webkit-scrollbar { background-color: rgba(0,0,0,0.5); width: 0.75em; }
	::-webkit-scrollbar-thumb { background-color: rgba(255,255,255,0.5); }
</style>
</head>
<body>
<div id="p2vr" style="width:100%;height:100%;">
This content requires HTML5/CSS3, WebGL, or Adobe Flash Player Version 9 or higher.
</div>
<script type="text/javascript">
	var pano = new pano2vrPlayer('p2vr'), skin = null;
	if( typeof pano2vrSkin !== 'undefined' ){
		skin = new pano2vrSkin(pano);
	}
	pano.readConfigUrl('/<?php echo $xml_js; ?>');
	updateOrientation();
	setTimeout(function() { updateOrientation(); }, 10);
	setTimeout(function() { updateOrientation(); }, 1000);
	document.getElementById('p2vr').className = 'pp-embed-content';
	hideUrlBar();
</script>
<noscript>
	<p><b>Please enable Javascript!</b></p>
</noscript>
</body>
</html>

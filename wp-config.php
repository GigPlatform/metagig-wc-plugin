<?php
/** 
 * Configuración básica de WordPress.
 *
 * Este archivo contiene las siguientes configuraciones: ajustes de MySQL, prefijo de tablas,
 * claves secretas, idioma de WordPress y ABSPATH. Para obtener más información,
 * visita la página del Codex{@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} . Los ajustes de MySQL te los proporcionará tu proveedor de alojamiento web.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** Ajustes de MySQL. Solicita estos datos a tu proveedor de alojamiento web. ** //
/** El nombre de tu base de datos de WordPress */
define('DB_NAME', 'wordpress');

/** Tu nombre de usuario de MySQL */
define('DB_USER', 'root');

/** Tu contraseña de MySQL */
define('DB_PASSWORD', '');

/** Host de MySQL (es muy probable que no necesites cambiarlo) */
define('DB_HOST', 'localhost');

/** Codificación de caracteres para la base de datos. */
define('DB_CHARSET', 'utf8mb4');

/** Cotejamiento de la base de datos. No lo modifiques si tienes dudas. */
define('DB_COLLATE', '');

/**#@+
 * Claves únicas de autentificación.
 *
 * Define cada clave secreta con una frase aleatoria distinta.
 * Puedes generarlas usando el {@link https://api.wordpress.org/secret-key/1.1/salt/ servicio de claves secretas de WordPress}
 * Puedes cambiar las claves en cualquier momento para invalidar todas las cookies existentes. Esto forzará a todos los usuarios a volver a hacer login.
 *
 * @since 2.6.0
 */
define('AUTH_KEY', '8AW][]^yjtDAP90s48!rA[(8kVCfDV4qx0|`ad/qti-hdK>,x.?.SVt4q`n=%F6Y');
define('SECURE_AUTH_KEY', 'ot}Um;YZ[fd}d~z$k04lB.}d+g%V@U{NX]$.As1zvek1YtmOk9Wa5>T7nng;a~vx');
define('LOGGED_IN_KEY', 'ePsAY!rFK%z Ac2_1VcCs&i6)Y1%5K]w7w:*[6?Ag9DD)z>eZ-$ut8X/+.^*FZ+d');
define('NONCE_KEY', 'D:$g-AL&X a*[!szB1xmbok7`WM@r%C?UZQva?Qf_pMQSAY00m5AL+n)l*KP1KC8');
define('AUTH_SALT', '9m~:1/kYB=&Bhfo9s>v:BhP J`eB`=6(6f.l=@Rv(YQcQ2mTLOgP8i;y}r2/N|0r');
define('SECURE_AUTH_SALT', 'a )P/lunQ?+ZDKA[,!CXI$67a7[fqYx{kNGw+_o^ou^MJQN:+rqOilXrE/2Q.2Lb');
define('LOGGED_IN_SALT', 'ua3gIUR6Qsf^~H2Jp!y``;JAERla,8h;&c3,h{nr=|zx`i{2,o?%=K}z3-,S?e1P');
define('NONCE_SALT', '/cBr5<UF#y!,LwdNE)i4-<rl_f2KO$o|/~$y?mzJa`HQjeet :IcqtZk5vOyx2r^');

/**#@-*/

/**
 * Prefijo de la base de datos de WordPress.
 *
 * Cambia el prefijo si deseas instalar multiples blogs en una sola base de datos.
 * Emplea solo números, letras y guión bajo.
 */
$table_prefix  = 'wp_';


/**
 * Para desarrolladores: modo debug de WordPress.
 *
 * Cambia esto a true para activar la muestra de avisos durante el desarrollo.
 * Se recomienda encarecidamente a los desarrolladores de temas y plugins que usen WP_DEBUG
 * en sus entornos de desarrollo.
 */
define('WP_DEBUG', false);

/* ¡Eso es todo, deja de editar! Feliz blogging */

/** WordPress absolute path to the Wordpress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');


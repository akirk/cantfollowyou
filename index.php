<?php

// Include common functions
require_once 'common.php';

// Detect user's preferred language
$lang = 'en';
$browser_lang = substr( $_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2 );
$your_lang = 'en';

// Handle Portuguese variants
$accept_lang = strtolower( $_SERVER['HTTP_ACCEPT_LANGUAGE'] );
if ( strpos( $accept_lang, 'pt-br' ) !== false || strpos( $accept_lang, 'pt_br' ) !== false ) {
	$your_lang = 'pt-br';
} elseif ( $browser_lang === 'pt' ) {
	$your_lang = 'pt';
} elseif ( $browser_lang === 'gl' ) {
	$your_lang = 'gl';
} elseif ( array_key_exists( $browser_lang, get_available_languages() ) ) {
	$your_lang = $browser_lang;
}

if ( isset( $_GET['lang'] ) && ( array_key_exists( $_GET['lang'], get_available_languages() ) || 'en' === $_GET['lang'] ) ) {
	$lang = $_GET['lang'];
} else {
	$lang = $your_lang;
}


// Render the main UI
render_main_ui();
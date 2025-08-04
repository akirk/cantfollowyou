<?php
$username_regex = '(?P<username>\/+[^\/]+)?';

$centralized_platforms = array(
	'X' => array(
		'regex' => 'x(?:\.com)?' . $username_regex,
		'url_part' => 'x',
	),
	'Twitter' => array(
		'regex' => '(?:twitter(?:\.com)?|tw)' . $username_regex,
		'url_part' => 'tw',
	),
	'Instagram' => array(
		'regex' => '(?:instagram(?:\.com)?|ig|insta)' . $username_regex,
		'url_part' => 'insta',
	),
	'Facebook' => array(
		'regex' => '(?:facebook(?:\.com)?|fb)' . $username_regex,
		'url_part' => 'fb',
	),
	'TikTok' => array(
		'regex' => '(?:tiktok(?:\.com)?)' . $username_regex,
		'url_part' => 'tiktok',
	),
	'YouTube' => array(
		'regex' => '(?:youtube(?:\.com)?|yt)' . $username_regex,
		'url_part' => 'yt',
	),
	'Reddit' => array(
		'regex' => 'reddit(?:\.com)?(?:\/user)?' . $username_regex,
		'url_part' => 'reddit',
	),
	'Tumblr' => array(
		'regex' => '(?:tumblr(?:\.com)?)' . $username_regex,
		'url_part' => 'tumblr',
	),
	'Soundcloud' => array(
		'regex' => '(?:soundcloud(?:\.com)?|sc)' . $username_regex,
		'url_part' => 'soundcloud',
	),
	'Bandcamp' => array(
		'regex' => '(?:bandcamp(?:\.com)?)' . $username_regex,
		'url_part' => 'bandcamp',
	),
	'Vimeo' => array(
		'regex' => '(?:vimeo(?:\.com)?)' . $username_regex,
		'url_part' => 'vimeo',
	),
);

// Load translations from JSON files
function load_translations( $lang ) {
	$file_path = __DIR__ . '/translations/' . $lang . '.json';
	if ( file_exists( $file_path ) ) {
		return json_decode( file_get_contents( $file_path ), true );
	}
	return null;
}

// Get available languages
function get_available_languages() {
	$languages = array( 'en' => 'English' );
	$translation_dir = __DIR__ . '/translations/';
	
	if ( is_dir( $translation_dir ) ) {
		$files = glob( $translation_dir . '*.json' );
		foreach ( $files as $file ) {
			$lang_code = basename( $file, '.json' );
			if ( $lang_code !== 'en' ) {
				$translations = load_translations( $lang_code );
				if ( $translations && isset( $translations['language'] ) ) {
					$languages[$lang_code] = $translations['language'];
				}
			}
		}
	}
	
	return $languages;
}

// Translation function with in-place editing for review mode
function _t( $key, $escape_html = true ) {
	global $translations, $lang;
	
	// Get English fallback
	$english_translations = load_translations( 'en' );
	$fallback_text = isset( $english_translations[$key] ) ? $english_translations[$key] : ucfirst( str_replace( '_', ' ', $key ) );
	$translated_text = isset( $translations[$key] ) ? $translations[$key] : $fallback_text;
	
	// For translation review mode, wrap with editable data attributes
	if ( isset( $_GET['translate-review'] ) ) {
		$content = $escape_html ? htmlspecialchars( $translated_text ) : $translated_text;
		
		// Check if this is untranslated (using English fallback) for new language creation
		$available_languages = get_available_languages();
		$is_new_language = ( $_GET['translate-review'] === 'new' || ! array_key_exists( $_GET['translate-review'], $available_languages ) );
		$is_untranslated = $is_new_language && ( ! isset( $translations[$key] ) || $translated_text === $fallback_text );
		$untranslated_class = $is_untranslated ? ' untranslated' : '';

		return '<span class="editable-translation' . $untranslated_class . '"
			      data-key="' . htmlspecialchars( $key ) . '" 
			      data-original="' . htmlspecialchars( $translated_text ) . '"
			      data-escape-html="' . ( $escape_html ? '1' : '0' ) . '"
			      contenteditable="true">' . 
			$content . 
		   '</span>';
	}
	
	return $translated_text;
}

function _t_attr( $key ) {
	global $translations, $lang;
	
	// For attributes, ALWAYS return plain text without any HTML or editing functionality
	$english_translations = load_translations( 'en' );
	$fallback_text = isset( $english_translations[$key] ) ? $english_translations[$key] : ucfirst( str_replace( '_', ' ', $key ) );
	$translated_text = isset( $translations[$key] ) ? $translations[$key] : $fallback_text;
	
	return $translated_text;
}

// Helper function for sprintf with translations - handles review mode properly
function _t_sprintf( $key, ...$args ) {
	global $translations, $lang;
	
	// Get the plain text translation for sprintf
	$english_translations = load_translations( 'en' );
	$fallback_text = isset( $english_translations[$key] ) ? $english_translations[$key] : ucfirst( str_replace( '_', ' ', $key ) );
	$translated_text = isset( $translations[$key] ) ? $translations[$key] : $fallback_text;
	
	// Apply sprintf to the plain text
	$formatted_text = sprintf( $translated_text, ...$args );
	
	// If we're in review mode, wrap the final result
	if ( isset( $_GET['translate-review'] ) ) {
		// Check if this is untranslated for new language creation
		$available_languages = get_available_languages();
		$is_new_language = ( $_GET['translate-review'] === 'new' || ! array_key_exists( $_GET['translate-review'], $available_languages ) );
		$is_untranslated = $is_new_language && ( ! isset( $translations[$key] ) || $translated_text === $fallback_text );
		$untranslated_class = $is_untranslated ? ' untranslated' : '';

		return '<span class="editable-translation' . $untranslated_class . '"
			      data-key="' . htmlspecialchars( $key ) . '" 
			      data-original="' . htmlspecialchars( $translated_text ) . '"
			      data-formatted="' . htmlspecialchars( $formatted_text ) . '"
			      data-escape-html="1"
			      contenteditable="true">' . 
			htmlspecialchars( $formatted_text ) . 
		   '</span>';
	}
	
	return $formatted_text;
}

// Like _t_sprintf but for HTML content (doesn't escape HTML in review mode)
function _t_sprintf_html( $key, ...$args ) {
	global $translations, $lang;
	
	// Get the plain text translation for sprintf
	$english_translations = load_translations( 'en' );
	$fallback_text = isset( $english_translations[$key] ) ? $english_translations[$key] : ucfirst( str_replace( '_', ' ', $key ) );
	$translated_text = isset( $translations[$key] ) ? $translations[$key] : $fallback_text;
	
	// If we're in review mode, we need to handle this carefully to avoid nested spans
	if ( isset( $_GET['translate-review'] ) ) {
		// For HTML sprintf, we need to apply sprintf to the raw translated text first,
		// then create a single editable span around the entire result
		$formatted_text = sprintf( $translated_text, ...$args );
		
		// Check if this is untranslated for new language creation
		$available_languages = get_available_languages();
		$is_new_language = ( $_GET['translate-review'] === 'new' || ! array_key_exists( $_GET['translate-review'], $available_languages ) );
		$is_untranslated = $is_new_language && ( ! isset( $translations[$key] ) || $translated_text === $fallback_text );
		$untranslated_class = $is_untranslated ? ' untranslated' : '';

		// Store the plain text version for data attributes
		$plain_formatted = $formatted_text;
		
		// Remove any existing editable spans from the arguments to get clean display
		$clean_args = array();
		foreach ( $args as $arg ) {
			if ( is_string( $arg ) && strpos( $arg, 'editable-translation' ) !== false ) {
				// Extract just the text content from editable spans
				$clean_arg = preg_replace( '/<span[^>]*class="[^"]*editable-translation[^"]*"[^>]*>(.*?)<\/span>/', '$1', $arg );
				$clean_args[] = $clean_arg;
			} else {
				$clean_args[] = $arg;
			}
		}
		
		// Create the formatted text with clean arguments for display
		$display_formatted = sprintf( $translated_text, ...$clean_args );

		return '<span class="editable-translation' . $untranslated_class . '"
			      data-key="' . htmlspecialchars( $key ) . '" 
			      data-original="' . htmlspecialchars( $translated_text ) . '"
			      data-formatted="' . htmlspecialchars( $plain_formatted ) . '"
			      data-escape-html="0"
			      contenteditable="true">' . 
			$display_formatted . 
		   '</span>';
	}
	
	// Apply sprintf to the plain text
	$formatted_text = sprintf( $translated_text, ...$args );
	return $formatted_text;
}

function render_main_ui( $target_platform = null, $target_username = null ) {
	global $lang, $username, $translations, $centralized_platforms, $your_lang;

	$request_uri = strtok( urldecode( $_SERVER['REQUEST_URI'] ), '?' );
	if ( isset( $_GET['url'] ) ) {
		$request_uri = $_GET['url'];
	}
	$segments = explode( '/', trim( $request_uri, '/' ), 2 );


	// Load current language translations
	$translations = load_translations( $lang );
	if ( ! $translations ) {
		$translations = array(); // Fallback to English keys
	}


	// Language-specific text replacements for context
	$replace_no_technical_reason = array( 'on their platform' => 'on it' );
	$replace_called_fediverse = array( ' and it is part of the Fediverse,' => '' );

	switch ( $lang ) {
		case 'de':
			$replace_no_technical_reason = array( 'von dieser ' => 'dieser ', 'ihrer Plattform' => 'darauf' );
			$replace_called_fediverse = array( ' und ist Teil des Fediverse, das' => ', welches' );
			break;
		case 'fr':
			$replace_no_technical_reason = array( 'sur leur plateforme' => 'dessus' );
			$replace_called_fediverse = array( ' et fait partie du Fédiverse,' => '' );
			break;
		case 'it':
			$replace_no_technical_reason = array( 'sulla loro piattaforma' => 'su di essa' );
			$replace_called_fediverse = array( ' ed è parte del Fediverso,' => '' );
			break;
		case 'es':
			$replace_no_technical_reason = array( 'en su plataforma' => 'en ella' );
			$replace_called_fediverse = array( ' y es parte del Fediverso,' => '' );
			break;
		case 'pt-br':
			$replace_no_technical_reason = array( 'em sua plataforma' => 'nela' );
			$replace_called_fediverse = array( ' e faz parte do Fediverso,' => '' );
			break;
		case 'pt':
			$replace_no_technical_reason = array( 'na sua plataforma' => 'nela' );
			$replace_called_fediverse = array( ' e faz parte do Fediverso,' => '' );
			break;
		case 'gl':
			$replace_no_technical_reason = array( 'na súa plataforma' => 'nela' );
			$replace_called_fediverse = array( ' e forma parte do Fediverso,' => '' );
			break;
	}

	$username = _t( 'friend' );
	if ( count( $segments ) < 2 ) {
		$segments[] = $username;
	}
	$platform = _t( 'a_closed_platform' );
	$this_platform = _t( 'this_closed_platform' );
	$new_platform = _t( 'the_fediverse' );
	$new_platform_url = 'https://jointhefediverse.net/';
	$regex_prefix = '(?:https?:\/\/)?(?:www\.)?';

	$new_platforms = array(
		'Mastodon' => 'https://joinmastodon.org/',
		'Misskey' => 'https://join.misskey.page/',
		'Pleroma' => 'https://pleroma.social/',
		'Pixelfed' => 'https://pixelfed.org/',
		'Friendica' => 'https://friendi.ca/',
		'PeerTube' => 'https://joinpeertube.org/',
		'Lemmy' => 'https://join-lemmy.org/',
		'Castopod' => 'https://castopod.org/',
		'Mobilizon' => 'https://mobilizon.org/',
		'Funkwhale' => 'https://www.funkwhale.audio/',
	);

	$groups = array(
		'group_microblogging' => array(
			array( 'X', 'Twitter', 'Tumblr' ),
			array( 'Mastodon', 'Misskey', 'Pleroma' ),
		),
		'group_image_sharing' => array(
			array( 'Instagram' ),
			array( 'Pixelfed' ),
		),
		'group_video_sharing' => array(
			array( 'YouTube', 'TikTok', 'Vimeo' ),
			array( 'PeerTube' ),
		),
		'group_audio_sharing' => array(
			array( 'Soundcloud', 'Bandcamp' ),
			array( 'Castopod', 'Funkwhale' ),
		),
		'group_forums' => array(
			array( 'Reddit' ),
			array( 'Lemmy' ),
		),
		'group_social_networks' => array(
			array( 'Facebook' ),
			array( 'Friendica' ),
		),
		'group_events' => array(
			array( 'Facebook' ),
			array( 'Mobilizon' ),
		),
	);

	foreach ( $centralized_platforms as $platform_name => $platform_data ) {
		if ( preg_match( '/^' . $regex_prefix . $platform_data['regex'] . '$/i', implode( '/', $segments ), $matches ) ) {
			if ( isset( $matches['username'] ) && ! empty( $matches['username'] ) ) {
				$username = htmlspecialchars( ltrim( $matches['username'], '/@' ) );
			}
			$this_platform = $platform = $platform_name;
			foreach ( $groups as $group_name => $platforms ) {
				if ( in_array( $platform_name, $platforms[0] ) ) {
					$new_platform = reset( $platforms[1] );
					$new_platform_url = $new_platforms[$new_platform];
					break 2; // Break out of both loops
				}
			}
			break;
		}
	}

	if ( ! empty( $segments ) && $new_platform_url == 'https://jointhefediverse.net/' && trim( $segments[0] ) ) {
		$username = $segments[0];
	}

	// Override defaults if provided
	if ( $target_platform ) {
		$platform = $target_platform;
		$this_platform = $target_platform;

		// Find the appropriate alternative
		foreach ( $groups as $group_name => $platform_groups ) {
			if ( in_array( $target_platform, $platform_groups[0] ) ) {
				$new_platform = reset( $platform_groups[1] );
				$new_platform_url = $new_platforms[$new_platform];
				break;
			}
		}
	}

	if ( $target_username ) {
		$username = $target_username;
	}

	// If this is for translator content only, render just the body content without head/html tags
	$translator_mode = isset( $_GET['translator-content-only'] );
	if ( $translator_mode ) {
		echo '<div class="platform-preview"><h3>Platform: ' . htmlspecialchars( $target_platform ) . '</h3>';
	}

	// Regular full page rendering
	if ( ! $translator_mode ) {
	?><!DOCTYPE html>
<html lang="<?php echo htmlspecialchars( $lang ); ?>">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="og:title" content="<?php echo htmlspecialchars( sprintf( _t_attr( 'cant_follow_you_on_use_instead' ), $platform, $new_platform ) ); ?>">
	<meta name="color-scheme" content="light dark">
	<title><?php echo htmlspecialchars( _t_attr( 'title' ) ); ?></title>
	<style>
		body {
			font-family: 'Arial', sans-serif;
			background-color: light-dark( #ffffff, #121212 );
			margin-top: 20px;
			padding: 0;
			display: flex;
			flex-direction: column;
			justify-content: center;
			align-items: center;
			color: light-dark(#333, #ddd);
			text-align: left;
		}
		footer {
			font-size: 0.8em;
			color: #666;
			text-align: center;
			margin-top: 20px;
		}
		.container {
			background: light-dark( rgba(0, 0, 0, 0.05), rgba(255, 255, 255, 0.2) );
			border-radius: 15px;
			box-shadow: 0 4px 20px light-dark( rgba(0, 0, 0, 0.1), rgba(99, 99, 99, 0.5) );
			padding: 30px;
			max-width: 680px;
		}
		h1 {
			font-size: 2.5em;
			margin-bottom: 10px;
			margin-top: 0;
		}
		p {
			font-size: 1.2em;
			line-height: 1.6;
			margin: 10px 0;
		}
		a:any-link {
			color: light-dark( #007bff, #66b3ff );
		}
		p.q {
			margin-top: 1em;
			margin-bottom: 0;
		}
		p.a {
			margin-top: 0;
			font-size: 95%;
		}
		footer a:any-link {
			color: #666;
		}
		footer p.hosting {
			color: #ccc;
			margin-top: 1em;
			font-size: 80%;
		}
		footer p.hosting a:any-link {
			color: #ccc;
		}
		a:any-link.button {
			display: inline-block;
			margin-top: 20px;
			background-color: light-dark( #007bff, #66b3ff );
			color: light-dark( #fff, #000 );
			padding: 12px 25px;
			text-decoration: none;
			border-radius: 5px;
			transition: background-color 0.3s ease;
		}
		a.button:hover {
			background-color: light-dark( #0056b3, #3380cc );
		}
		a.button-secondary:any-link {
			background-color: light-dark( #6c757d, #888 );
			color: light-dark( #fff, #000 );
		}
		a.button-secondary:hover {
			background-color: light-dark( #5a6268, #666 );
		}
		summary {
			cursor: pointer;
		}
		@media (max-width: 600px) {
			body {
				padding: 10px;
			}
			.container {
				background: transparent;
				border: 0;
				box-shadow: none;
			}
			h1 {
				font-size: 2em;
			}
			p {
				font-size: 1em;
			}
			a.button {
				padding: 10px 20px;
			}
			span.newline {
				margin-top: .5em;
				display: block;
			}
		}
		table {
			width: 100%;
			margin-top: 20px;
			border-collapse: collapse;
		}
		th {
			text-align: left;
			padding: 10px;
			padding-bottom: 5px;
		}
		tr:nth-child(even) {
			border: 1px solid #ccc;
		}
		td {
			padding: 10px;
			text-align: left;
		}
		blockquote {
			background: light-dark( #f9f9f9, #222 );
			border-left: 5px solid light-dark( #007bff, #66b3ff );
			padding: 10px 20px;
			margin: 20px 0;
		}
		#language-switcher-holder {
			position: absolute;
			top: 10px;
			right: 10px;
		}
		#dark-mode-toggle {
			background: none;
			border: none;
			cursor: pointer;
			padding: 0;
			vertical-align: bottom;
		}
		#dark-mode-toggle:focus {
			outline: none;
		}
		mark {
			background-color: light-dark( #ff3, #000 );
			color: light-dark( #000, #fff );
			padding: 0 3px;
			border-radius: 3px;
			text-decoration: underline
		}
	</style>
</head>
<body>
<?php } // End non-translator mode head section ?>

<?php if ( ! $translator_mode ) : ?>
<div id="language-switcher-holder">
	<!-- dark/light mode switcher -->
	<button id="dark-mode-toggle">
		<svg id="dark-mode-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentcolor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"></path></svg>
		<svg id="light-mode-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentcolor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
	</button>
	<select id="language-switcher">
		<option value="en" <?php echo $lang === 'en' ? 'selected' : ''; ?>>
			<?php echo htmlspecialchars( _t_attr( 'language' ) ); ?>
		</option>
	<?php foreach ( get_available_languages() as $lang_code => $lang_name ) : ?>
		<?php if ( $lang_code !== 'en' ) : ?>
		<option value="<?php echo htmlspecialchars( $lang_code ); ?>" <?php echo $lang === $lang_code ? 'selected' : ''; ?>>
			<?php echo htmlspecialchars( $lang_name ); ?>
		</option>
		<?php endif; ?>
	<?php endforeach; ?>
	</select>
</div>
<?php endif; // End non-translator mode header ?>

<div class="container">
	<h1><?php echo _t_sprintf( 'hi', htmlspecialchars( $username ) ); ?></h1>
	<p>
		<?php echo _t_sprintf( 'want_to_follow_you', htmlspecialchars( $platform ) ); ?>
	</p>
	<p>
		<?php echo _t_sprintf_html( 'cant_follow_from_elsewhere', '<mark>' . htmlspecialchars( $this_platform ) . '</mark>' ); ?>
	</p>
	<p>
		<?php echo str_replace( array_keys( $replace_no_technical_reason ), array_values( $replace_no_technical_reason ), _t_sprintf( 'no_technical_reason', htmlspecialchars( $this_platform ) ) ); ?>
	</p>
	<p>
		<?php echo _t_sprintf( 'alternative_open_web', htmlspecialchars( $this_platform ) ); ?>
	</p>
	<p>
		<?php 
		// Get plain text version of new_platform for use in sprintf to avoid nested spans
		$new_platform_text = isset( $_GET['translate-review'] ) ? _t_attr( 'the_fediverse' ) : $new_platform;
		echo str_replace( array_keys( $replace_called_fediverse ), array_values( $replace_called_fediverse ), _t_sprintf_html( 'called_fediverse', '<a href="' . $new_platform_url . '">' . htmlspecialchars( $new_platform_text ) . '</a>' ) ); 
		?>
	</p>
	<p>
		<?php echo _t( 'join_us' ); ?>
	</p>
	<details>
		<summary><?php echo _t( 'open_alternative' ); ?></summary>
		<blockquote>
		<p class="q"><?php echo _t( 'what_makes_them_open' ); ?></p>
		<p class="a"><?php 
		if ( isset( $_GET['translate-review'] ) ) {
			// In review mode, get the raw translation and do str_replace, then wrap in editable span
			$english_translations = load_translations( 'en' );
			$fallback_text = isset( $english_translations['based_on_activitypub'] ) ? $english_translations['based_on_activitypub'] : 'based_on_activitypub';
			$translated_text = isset( $translations['based_on_activitypub'] ) ? $translations['based_on_activitypub'] : $fallback_text;
			$processed_text = str_replace( 'ActivityPub', '<a href="https://activitypub.rocks/">ActivityPub</a>', $translated_text );
			
			// Check if this is untranslated for new language creation
			$available_languages = get_available_languages();
			$is_new_language = ( $_GET['translate-review'] === 'new' || ! array_key_exists( $_GET['translate-review'], $available_languages ) );
			$is_untranslated = $is_new_language && ( ! isset( $translations['based_on_activitypub'] ) || $translated_text === $fallback_text );
			$untranslated_class = $is_untranslated ? ' untranslated' : '';
			
			echo '<span class="editable-translation' . $untranslated_class . '"
				      data-key="based_on_activitypub" 
				      data-original="' . htmlspecialchars( $translated_text ) . '"
				      data-escape-html="0"
				      contenteditable="true">' . 
				$processed_text . 
			   '</span>';
		} else {
			// Normal mode, use str_replace
			echo str_replace( 'ActivityPub', '<a href="https://activitypub.rocks/">ActivityPub</a>', _t( 'based_on_activitypub', false ) );
		}
		?></p>
		<p class="q"><?php echo _t( 'what_makes_a_little_harder_to_use' ); ?></p>
		<p class="a"><?php echo _t( 'need_to_choose_a_server' ); ?></p>
		</blockquote>
		<table>
			<?php foreach ( $groups as $group_name => $platform_groups ) : ?>
				<tr>
					<th colspan="3"><?php echo _t( $group_name ); ?></th>
				</tr>
				<tr>
				<?php foreach ( $platform_groups as $k => $platform_group ) : ?>
					<td>
					<?php foreach ( $platform_group as $platform_name ) : ?>
						<?php if ( isset( $new_platforms[$platform_name] ) ) : ?>
							<a href="<?php echo htmlspecialchars( $new_platforms[$platform_name] ); ?>"><?php echo $platform_name; ?></a>
						<?php else : ?>
							<?php echo $platform_name; ?>
						<?php endif; ?>
						<br>
					<?php endforeach; ?>
					</td>
					<?php if ( $k === 0 ) : ?>
						<td>→</td>
					<?php endif; ?>
				<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
		</table>
	</details>

	<a href="https://jointhefediverse.net" class="button"><?php echo _t( 'learn_more' ); ?></a> <span class="newline"><?php echo _t( 'or' ); ?> <a href="https://videos.elenarossini.com/w/64VuNCccZNrP4u9MfgbhkN"><?php echo str_replace( _t( 'video_title', false ), '"' . _t( 'video_title', false ) . '"', _t( 'watch_video', false ) ); ?></a>.</span>
</div>

<?php if ( $translator_mode ) : ?>
</div> <!-- End platform-preview -->
<?php else : ?>

<footer>
	<p><?php echo _t( 'send_to_friend' ); ?> <input type="url" placeholder="<?php echo _t_attr( 'enter_friend_url' ); ?>" id="friend-url" /></p>
	<p class="hosting"><?php echo _t( 'idea_and_hosting' ); ?> <a href="https://alex.kirk.at/">Alex Kirk</a>.
		<a href="https://github.com/akirk/cantfollowyou"><?php echo _t( 'contribute_github' ); ?></a>
	</p>
</footer>

<script>
	document.addEventListener('DOMContentLoaded', function() {
		const input = document.getElementById('friend-url');
		function redirect() {
			let friend_url = input.value.trim();
			if (!friend_url.match(/^https?:\/\//)) {
				friend_url = 'https://' + friend_url;
			}
			const parts = friend_url.split('/');
			const url = [];
			let m;
			if (parts.length >= 3) {
				<?php
				global $centralized_platforms, $regex_prefix;
				foreach ( $centralized_platforms as $platform_data ) : ?>
					if (!url.length) {
						m = friend_url.match(new RegExp('^<?php echo $regex_prefix . str_replace( '(?P<', '(?<', $platform_data['regex'] ); ?>', 'i'));
						if (m) {
							url.push('<?php echo strtolower( $platform_data['url_part'] ); ?>');
							if (m.groups && m.groups['username']) {
								url.push(m.groups['username'].replace(/^[\/@]/, ''));
							}
						}
					}
				<?php endforeach; ?>
			}

			if ( ! url.length && parts.length > 2 && ! parts[2].match( /\.[a-z]+$/ ) ) {
				url.push( parts[2] );
			}

			if (url.length) {
				window.location.href = '/' + url.join('/');
			} else {
				input.setCustomValidity("<?php echo _t_attr( 'sorry_unknown_platform' ); ?>");
				input.reportValidity();
			}
		}

		let redirectTimeout = null;
		let lastLength = 0;

		input.addEventListener('keydown', function(event) {
			if (event.key === 'Enter') {
				event.preventDefault();
				redirect();
				return;
			}
			let timeout = 100;
			if (Math.abs(lastLength - input.value.length) > 0 || !lastLength) {
				timeout = 5000;
			}
			clearTimeout(redirectTimeout);
			redirectTimeout = setTimeout(redirect, timeout);
			lastLength = input.value.length;
		});
		document.getElementById('language-switcher').addEventListener('change', function() {
			const lang = this.value;
			const url = new URL(window.location.href);
			if ( lang === '<?php echo htmlspecialchars( $your_lang ); ?>' ) {
				url.searchParams.delete('lang');
			} else {
				url.searchParams.set('lang', lang);
			}
			window.location.href = url.toString();
		});
		const darkModeIcon = document.getElementById('dark-mode-icon');
		const lightModeIcon = document.getElementById('light-mode-icon');
		const metaColorScheme = document.querySelector('meta[name="color-scheme"]');
		const systemSettingDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

		document.getElementById('dark-mode-toggle').addEventListener('click', function() {

			if ( ( metaColorScheme.content === 'light dark' && systemSettingDark ) || metaColorScheme.content === 'dark' ) {
				metaColorScheme.content = systemSettingDark ? 'light' : 'light dark';
				darkModeIcon.style.display = 'inline';
				lightModeIcon.style.display = 'none';
			} else {
				metaColorScheme.content = systemSettingDark ? 'light dark' : 'dark';
				darkModeIcon.style.display = 'none';
				lightModeIcon.style.display = 'inline';
			}
		});
		if ( systemSettingDark ) {
			darkModeIcon.style.display = 'none';
			lightModeIcon.style.display = 'inline';
		} else {
			darkModeIcon.style.display = 'inline';
			lightModeIcon.style.display = 'none';

		}

	});
</script>

</body>
</html>
<?php endif; // End non-translator mode footer ?>
	<?php
}

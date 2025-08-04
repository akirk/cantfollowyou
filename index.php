<?php

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
		
		return '<span class="editable-translation" 
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
		return '<span class="editable-translation" 
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
	
	// Apply sprintf to the plain text
	$formatted_text = sprintf( $translated_text, ...$args );
	
	// If we're in review mode, wrap the final result (but don't escape HTML)
	if ( isset( $_GET['translate-review'] ) ) {
		return '<span class="editable-translation" 
				      data-key="' . htmlspecialchars( $key ) . '" 
				      data-original="' . htmlspecialchars( $translated_text ) . '"
				      data-formatted="' . htmlspecialchars( $formatted_text ) . '"
				      data-escape-html="0"
				      contenteditable="true">' . 
				$formatted_text . 
			   '</span>';
	}
	
	return $formatted_text;
}

$request_uri = strtok( urldecode( $_SERVER['REQUEST_URI'] ), '?' );
if ( isset( $_GET['url'] ) ) {
	$request_uri = $_GET['url'];
}
$segments = explode( '/', trim( $request_uri, '/' ), 2 );
$username = _t( 'friend' );
if ( count( $segments ) < 2 ) {
	$segments[] = $username;
}
$platform = _t( 'a_closed_platform' );
$this_platform = _t( 'this_closed_platform' );
$new_platform = _t( 'the_fediverse' );
$new_platform_url = 'https://jointhefediverse.net/';
$username_regex = '(?P<username>\/+[^\/]+)?';
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

function render_main_ui( $target_platform = null, $target_username = null ) {
	global $lang, $username, $platform, $this_platform, $new_platform, $new_platform_url, $replace_no_technical_reason, $replace_called_fediverse, $groups, $new_platforms, $your_lang;

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
		.editable-translation {
			background: rgba(255, 255, 0, 0.1);
			border: 1px dashed rgba(255, 193, 7, 0.5);
			border-radius: 3px;
			padding: 2px 4px;
			cursor: text;
			transition: all 0.2s;
			position: relative;
		}
		.editable-translation:hover {
			background: rgba(255, 255, 0, 0.2);
			border-color: rgba(255, 193, 7, 0.8);
		}
		.editable-translation:focus {
			background: rgba(255, 255, 0, 0.3);
			border-color: #ffc107;
			outline: none;
		}
		.editable-translation.modified {
			background: rgba(40, 167, 69, 0.1);
			border-color: rgba(40, 167, 69, 0.6);
		}
		.editable-translation.modified::after {
			content: '✓';
			position: absolute;
			top: -8px;
			right: -8px;
			background: #28a745;
			color: white;
			border-radius: 50%;
			width: 16px;
			height: 16px;
			font-size: 10px;
			display: flex;
			align-items: center;
			justify-content: center;
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
		<?php echo str_replace( array_keys( $replace_called_fediverse ), array_values( $replace_called_fediverse ), _t_sprintf_html( 'called_fediverse', '<a href="' . $new_platform_url . '">' . htmlspecialchars( $new_platform ) . '</a>' ) ); ?>
	</p>
	<p>
		<?php echo _t( 'join_us' ); ?>
	</p>
	<details>
		<summary><?php echo _t( 'open_alternative' ); ?></summary>
		<blockquote>
		<p class="q"><?php echo _t( 'what_makes_them_open' ); ?></p>
		<p class="a"><?php echo str_replace( 'ActivityPub', '<a href="https://activitypub.rocks/">ActivityPub</a>', _t( 'based_on_activitypub', false ) ); ?></p>
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
				<?php foreach ( $centralized_platforms as $platform_data ) : ?>
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

function render_new_language_ui() {
	$english_translations = load_translations( 'en' );
	?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Create New Language - I can't follow you!</title>
	<style>
		body {
			font-family: 'Arial', sans-serif;
			background-color: #f5f5f5;
			margin: 20px;
			color: #333;
		}
		.container {
			max-width: 800px;
			margin: 0 auto;
			background: white;
			padding: 30px;
			border-radius: 10px;
			box-shadow: 0 2px 10px rgba(0,0,0,0.1);
		}
		h1 { color: #007bff; margin-bottom: 30px; }
		.form-group {
			margin-bottom: 20px;
		}
		.form-group label {
			display: block;
			font-weight: bold;
			margin-bottom: 5px;
			color: #495057;
		}
		.form-group input {
			width: 100%;
			padding: 10px;
			border: 1px solid #dee2e6;
			border-radius: 4px;
			font-size: 16px;
		}
		.form-group small {
			color: #6c757d;
			font-size: 14px;
		}
		.button {
			display: inline-block;
			padding: 12px 24px;
			background: #007bff;
			color: white;
			text-decoration: none;
			border: none;
			border-radius: 4px;
			cursor: pointer;
			font-size: 16px;
		}
		.button:hover { background: #0056b3; }
		.button-secondary {
			background: #6c757d;
		}
		.button-secondary:hover { background: #545b62; }
		.success {
			background: #d4edda;
			border: 1px solid #c3e6cb;
			color: #155724;
			padding: 12px;
			border-radius: 4px;
			margin: 20px 0;
		}
	</style>
</head>
<body>
	<div class="container">
		<h1>🌍 Create New Language</h1>
		<p>Create a new translation file based on the English template. All English text will be copied as a starting point for translation.</p>
		
		<form id="new-language-form">
			<div class="form-group">
				<label for="lang-code">Language Code</label>
				<input type="text" id="lang-code" placeholder="e.g., fr, de, es, pt-br" required>
				<small>Use standard language codes (ISO 639-1). For variants, use format like 'pt-br' for Brazilian Portuguese.</small>
			</div>
			
			<div class="form-group">
				<label for="lang-name">Language Name</label>
				<input type="text" id="lang-name" placeholder="e.g., Français, Deutsch, Español" required>
				<small>The native name of the language as it should appear in the language selector.</small>
			</div>
			
			<button type="submit" class="button">📝 Create Language File</button>
			<a href="/" class="button button-secondary">← Back to Main Site</a>
		</form>
		
		<div id="result"></div>
	</div>

	<script>
		document.getElementById('new-language-form').addEventListener('submit', function(e) {
			e.preventDefault();
			
			const langCode = document.getElementById('lang-code').value.trim().toLowerCase();
			const langName = document.getElementById('lang-name').value.trim();
			
			if (!langCode || !langName) {
				alert('Please fill in both fields');
				return;
			}
			
			// Validate language code format
			if (!/^[a-z]{2}(-[a-z]{2})?$/.test(langCode)) {
				alert('Language code should be in format like "fr", "de", or "pt-br"');
				return;
			}
			
			// Create the translation object based on English
			const englishTranslations = <?php echo json_encode( $english_translations ); ?>;
			const newTranslations = {...englishTranslations};
			newTranslations.language = langName;
			
			// Generate JSON
			const json = JSON.stringify(newTranslations, null, 2);
			
			// Create download
			const blob = new Blob([json], { type: 'application/json' });
			const url = URL.createObjectURL(blob);
			
			const a = document.createElement('a');
			a.href = url;
			a.download = langCode + '.json';
			document.body.appendChild(a);
			a.click();
			document.body.removeChild(a);
			URL.revokeObjectURL(url);
			
			// Show success message
			document.getElementById('result').innerHTML = 
				'<div class="success">' +
				'<strong>✅ Success!</strong> Translation file <code>' + langCode + '.json</code> has been downloaded. ' +
				'Upload this file to the <code>translations/</code> directory and ' +
				'<a href="?translate-review=' + encodeURIComponent(langCode) + '">start translating →</a>' +
				'</div>';
		});
	</script>
</body>
</html>
	<?php
}

function render_translator_ui( $review_lang ) {
	$available_languages = get_available_languages();
	$sample_platforms = array( 'Twitter', 'Instagram', 'Facebook', 'TikTok' );
	
	// Handle new language creation
	if ( $review_lang === 'new' ) {
		render_new_language_ui();
		return;
	}
	
	?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Translation Review - I can't follow you!</title>
	<style>
		body {
			font-family: 'Arial', sans-serif;
			background-color: #f5f5f5;
			margin: 20px;
			color: #333;
		}
		.container {
			max-width: 1200px;
			margin: 0 auto;
			background: white;
			padding: 30px;
			border-radius: 10px;
			box-shadow: 0 2px 10px rgba(0,0,0,0.1);
		}
		h1 { color: #007bff; margin-bottom: 30px; }
		h2 { color: #495057; border-bottom: 2px solid #007bff; padding-bottom: 10px; margin-top: 40px; }
		h3 { color: #6c757d; margin-top: 30px; }
		.controls {
			background: #e9ecef;
			border-radius: 8px;
			padding: 20px;
			margin: 20px 0;
		}
		.platform-selector {
			margin: 10px 0;
		}
		.platform-selector select, .platform-selector button {
			padding: 8px 16px;
			margin: 5px;
			border: 1px solid #dee2e6;
			border-radius: 4px;
			background: white;
			cursor: pointer;
		}
		.platform-selector button:hover {
			background: #f8f9fa;
		}
		.platform-content {
			border-radius: 8px;
			margin: 20px 0;
			overflow: hidden;
		}
		.platform-content.active {
			display: block;
		}
		.button {
			display: inline-block;
			padding: 8px 16px;
			margin: 5px;
			background: #007bff;
			color: white;
			text-decoration: none;
			border-radius: 4px;
		}
		.button-secondary {
			background: #6c757d;
		}
		.button-secondary:hover { background: #545b62; }
		.translation-stats {
			background: #f8f9fa;
			border: 1px solid #dee2e6;
			border-radius: 4px;
			padding: 15px;
			margin: 15px 0;
			display: flex;
			align-items: center;
			gap: 10px;
			flex-wrap: wrap;
		}
		.translation-stats span {
			font-weight: bold;
		}
		#modified-count {
			color: #28a745;
		}
		.translation-context {
			background: white;
			border: 1px solid #dee2e6;
			border-radius: 8px;
			padding: 20px;
			margin: 10px 0;
		}
		.translation-section {
			margin-bottom: 25px;
			border-bottom: 1px solid #e9ecef;
			padding-bottom: 15px;
		}
		.translation-section:last-child {
			border-bottom: none;
		}
		.translation-section h4 {
			color: #495057;
			margin-bottom: 15px;
			font-family: monospace;
			background: #f8f9fa;
			padding: 5px 10px;
			border-radius: 4px;
			border-left: 4px solid #007bff;
		}
		.translation-item {
			margin-bottom: 10px;
			padding: 10px;
			background: #f8f9fa;
			border-radius: 4px;
		}
		.translation-item strong {
			color: #6c757d;
			font-size: 0.9em;
		}
		.platform-preview {
			border-radius: 8px;
			padding: 20px;
			margin: 10px 0;
			background: white;
		}
		.platform-preview h3 {
			color: #007bff;
			margin-top: 0;
			text-align: center;
			border-bottom: 1px solid #dee2e6;
			padding-bottom: 10px;
		}
		.placeholder-warning {
			background: #fff3cd;
			border: 1px solid #ffeaa7;
			color: #856404;
			padding: 8px 12px;
			border-radius: 4px;
			font-size: 13px;
			margin-top: 5px;
			display: none;
		}
		.placeholder-warning.show {
			display: block;
		}
		.editable-translation.placeholder-error {
			border-color: #dc3545;
			background: rgba(220, 53, 69, 0.1);
		}
	</style>
</head>
<body>
	<div class="container">
		<h1>🌍 Translation Review<?php if ( $review_lang && $review_lang !== 'all' ) echo ' - ' . htmlspecialchars( $review_lang ); ?></h1>
		<p>This page shows all translations for different platforms with in-place editing. Click on any highlighted text to edit it directly, then export your changes as JSON.</p>

		<?php foreach ( $sample_platforms as $sample_platform ) : ?>
		<div class="platform-content" id="platform-<?php echo htmlspecialchars( strtolower( $sample_platform ) ); ?>">
			<?php
			// Set language to review language or default
			global $lang, $translations;
			$original_lang = $lang;
			$original_translations = $translations;
			if ( $review_lang && $review_lang !== 'all' && array_key_exists( $review_lang, $available_languages ) ) {
				$lang = $review_lang;
				$translations = load_translations( $lang );
				if ( ! $translations ) {
					$translations = array();
				}
			}

			// Use a special flag to render only content for translator UI
			$_GET['translator-content-only'] = true;
			render_main_ui( $sample_platform, 'TestUser' );
			unset( $_GET['translator-content-only'] );

			// Restore original language
			$lang = $original_lang;
			$translations = $original_translations;
			?>
		</div>
		<?php endforeach; ?>

		<div class="controls">
			<div id="json-preview">
				<textarea id="json-textarea" readonly rows="15" style="width: 100%; font-family: monospace; font-size: 12px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 10px;"></textarea>
			</div>

			<div class="translation-stats">
				<span>Modified: <span id="modified-count">0</span> translations</span>
				<button id="export-json" class="button" disabled>📥 Export JSON</button>
				<button id="copy-json" class="button button-secondary" disabled>📋 Copy to Clipboard</button>
				<button id="reset-all" class="button button-secondary">🔄 Reset All Changes</button>
			</div>
		</div>

	</div>

	<script>
		// Track modifications
		let modifications = {};
		
		function showPlatform(platform) {
			// Hide all platform content
			document.querySelectorAll('.platform-content').forEach(el => {
				el.classList.remove('active');
			});

			if (platform) {
				// Show selected platform
				const el = document.getElementById('platform-' + platform);
				if (el) {
					el.classList.add('active');
				}
			} else {
				// Show all platforms
				document.querySelectorAll('.platform-content').forEach(el => {
					el.classList.add('active');
				});
			}
		}

		function updateModificationCount() {
			const count = Object.keys(modifications).length;
			document.getElementById('modified-count').textContent = count;
			
			const exportBtn = document.getElementById('export-json');
			const copyBtn = document.getElementById('copy-json');
			
			if (count > 0) {
				exportBtn.disabled = false;
				copyBtn.disabled = false;
			} else {
				exportBtn.disabled = true;
				copyBtn.disabled = true;
			}

			// Update JSON preview if it's visible
			updateJSONPreview();
		}

		function updateJSONPreview() {
			const textarea = document.getElementById('json-textarea');
			const translations = generateUpdatedJSON();
			const json = JSON.stringify(translations, null, 2);
			textarea.value = json;
		}

		function generateUpdatedJSON() {
			// Start with original translations (we'll get these from the first editable element's data)
			const firstElement = document.querySelector('.editable-translation');
			if (!firstElement) return {};

			// Load the original translations (we need to reconstruct this from the page)
			const originalTranslations = {};
			document.querySelectorAll('.editable-translation').forEach(el => {
				const key = el.dataset.key;
				const original = el.dataset.original;
				originalTranslations[key] = original;
			});

			// Apply modifications
			const updatedTranslations = {...originalTranslations, ...modifications};
			return updatedTranslations;
		}

		function exportJSON() {
			const translations = generateUpdatedJSON();
			const json = JSON.stringify(translations, null, 2);
			
			const blob = new Blob([json], { type: 'application/json' });
			const url = URL.createObjectURL(blob);
			
			const a = document.createElement('a');
			a.href = url;
			a.download = '<?php echo htmlspecialchars( $review_lang ); ?>.json';
			document.body.appendChild(a);
			a.click();
			document.body.removeChild(a);
			URL.revokeObjectURL(url);
		}

		function copyToClipboard() {
			const translations = generateUpdatedJSON();
			const json = JSON.stringify(translations, null, 2);
			
			navigator.clipboard.writeText(json).then(() => {
				const btn = document.getElementById('copy-json');
				const originalText = btn.textContent;
				btn.textContent = '✅ Copied!';
				setTimeout(() => {
					btn.textContent = originalText;
				}, 2000);
			}).catch(err => {
				console.error('Failed to copy: ', err);
				// Fallback for older browsers
				const textarea = document.createElement('textarea');
				textarea.value = json;
				document.body.appendChild(textarea);
				textarea.select();
				document.execCommand('copy');
				document.body.removeChild(textarea);
			});
		}

		function resetAllChanges() {
			if (Object.keys(modifications).length === 0) return;
			
			if (confirm('Are you sure you want to reset all changes? This cannot be undone.')) {
				modifications = {};
				
				// Reset all editable elements
				document.querySelectorAll('.editable-translation.modified').forEach(el => {
					const original = el.dataset.original;
					const escapeHtml = el.dataset.escapeHtml === '1';
					
					if (escapeHtml) {
						el.textContent = original;
					} else {
						el.innerHTML = original;
					}
					
					el.classList.remove('modified');
				});
				
				updateModificationCount();
			}
		}

		// Initialize when page loads
		document.addEventListener('DOMContentLoaded', function() {
			// Set up editable translations
			document.querySelectorAll('.editable-translation').forEach(el => {
				// Add warning div after each editable element
				const warning = document.createElement('div');
				warning.className = 'placeholder-warning';
				warning.innerHTML = '⚠️ Warning: This text should contain placeholders (%s) but none were found. Check your translation.';
				el.parentNode.insertBefore(warning, el.nextSibling);

				el.addEventListener('input', function() {
					const key = this.dataset.key;
					const original = this.dataset.original;
					const formatted = this.dataset.formatted;
					const escapeHtml = this.dataset.escapeHtml === '1';

					// Get current content - preserve HTML for non-escaped elements
					const current = escapeHtml ? (this.textContent || this.innerText) : this.innerHTML;
					
					// Check for placeholder validation
					const hasPlaceholders = formatted && original !== formatted;
					const originalPlaceholderCount = (original.match(/%s/g) || []).length;
					
					// If this has placeholders, we need to convert the edited content back to template format
					let finalValue = current;
					let placeholderError = false;
					
					if (hasPlaceholders) {
						// This is a sprintf template - convert user's edit back to template
						finalValue = convertToTemplate(original, formatted, current);
						
						// Check if the converted template has the right number of placeholders
						const newPlaceholderCount = (finalValue.match(/%s/g) || []).length;
						if (newPlaceholderCount !== originalPlaceholderCount) {
							placeholderError = true;
						}
					}
					
					// Update visual state based on placeholder validation
					const warningEl = this.nextSibling;
					if (placeholderError) {
						this.classList.add('placeholder-error');
						if (warningEl && warningEl.classList.contains('placeholder-warning')) {
							warningEl.classList.add('show');
						}
					} else {
						this.classList.remove('placeholder-error');
						if (warningEl && warningEl.classList.contains('placeholder-warning')) {
							warningEl.classList.remove('show');
						}
					}
					
					// Update the modification tracking
					if (finalValue !== original) {
						modifications[key] = finalValue;
					} else {
						delete modifications[key];
					}
					
					// Sync this change to all other elements with the same key
					syncTranslationKey(key, current, finalValue);
					
					updateModificationCount();
				});

				// Handle Enter key to prevent line breaks in simple text
				el.addEventListener('keydown', function(e) {
					if (e.key === 'Enter' && this.dataset.escapeHtml === '1') {
						e.preventDefault();
					}
				});
			});

			// Function to convert user's edited content back to template format
			function convertToTemplate(originalTemplate, formattedContent, userEditedContent) {
				// Find all %s placeholders in the original template
				const placeholders = originalTemplate.match(/%s/g) || [];
				if (placeholders.length === 0) {
					return userEditedContent; // No placeholders, return as-is
				}

				// Extract the substituted values by comparing the original template with formatted content
				// We need to find what HTML was substituted for each %s
				const substitutedHTMLValues = [];
				let remainingFormatted = formattedContent;
				let remainingTemplate = originalTemplate;

				for (let i = 0; i < placeholders.length; i++) {
					const beforePlaceholder = remainingTemplate.split('%s')[0];
					const afterPlaceholder = remainingTemplate.substring(beforePlaceholder.length + 2); // +2 for '%s'
					
					// Find the before part in the formatted content
					const beforeIndex = remainingFormatted.indexOf(beforePlaceholder);
					if (beforeIndex !== -1) {
						remainingFormatted = remainingFormatted.substring(beforeIndex + beforePlaceholder.length);
					}
					
					// Find where the next part starts
					let valueEnd = remainingFormatted.length;
					if (afterPlaceholder) {
						const nextPartStart = afterPlaceholder.split('%s')[0];
						if (nextPartStart) {
							const nextPartIndex = remainingFormatted.indexOf(nextPartStart);
							if (nextPartIndex !== -1) {
								valueEnd = nextPartIndex;
							}
						}
					}
					
					const substitutedHTMLValue = remainingFormatted.substring(0, valueEnd);
					substitutedHTMLValues.push(substitutedHTMLValue);
					
					// Update remaining strings for next iteration
					remainingFormatted = remainingFormatted.substring(valueEnd);
					remainingTemplate = afterPlaceholder;
				}

				// Now convert the user's edited content back to template format
				// Replace the HTML values with %s placeholders
				let result = userEditedContent;
				for (let i = substitutedHTMLValues.length - 1; i >= 0; i--) { // Reverse order to avoid index issues
					const htmlValue = substitutedHTMLValues[i];
					if (htmlValue && result.includes(htmlValue)) {
						result = result.replace(htmlValue, '%s');
					} else {
						// If exact HTML match fails, try to find by text content
						const tempDiv = document.createElement('div');
						tempDiv.innerHTML = htmlValue;
						const textValue = tempDiv.textContent || tempDiv.innerText || '';
						if (textValue && result.includes(textValue)) {
							result = result.replace(textValue, '%s');
						}
					}
				}

				return result;
			}

			// Function to sync changes across all elements with the same translation key
			function syncTranslationKey(key, displayValue, templateValue) {
				document.querySelectorAll('.editable-translation[data-key="' + key + '"]').forEach(element => {
					// Don't update the element that triggered the change
					if (element === document.activeElement) return;
					
					// Update the content
					const original = element.dataset.original;
					const formatted = element.dataset.formatted;
					
					if (formatted && original !== formatted) {
						// This is a sprintf element - we need to re-format the template with this element's parameters
						const newFormatted = reformatTemplate(templateValue, original, formatted);
						if (element.dataset.escapeHtml === '1') {
							element.textContent = newFormatted;
						} else {
							element.innerHTML = newFormatted;
						}
					} else {
						// Simple translation - just copy the display value
						if (element.dataset.escapeHtml === '1') {
							element.textContent = displayValue;
						} else {
							element.innerHTML = displayValue;
						}
					}
					
					// Update visual state
					if (templateValue !== original) {
						element.classList.add('modified');
					} else {
						element.classList.remove('modified');
					}
					
					// Validate placeholders for synced elements
					const originalPlaceholders = (original.match(/%s/g) || []).length;
					const templatePlaceholders = (templateValue.match(/%s/g) || []).length;
					const placeholderError = originalPlaceholders !== templatePlaceholders;
					
					// Find the warning element for this synced element
					const warningEl = element.nextElementSibling;
					
					if (placeholderError) {
						element.classList.add('placeholder-error');
						if (warningEl && warningEl.classList.contains('placeholder-warning')) {
							warningEl.classList.add('show');
						}
					} else {
						element.classList.remove('placeholder-error');
						if (warningEl && warningEl.classList.contains('placeholder-warning')) {
							warningEl.classList.remove('show');
						}
					}
				});
			}

			// Function to re-format a template with the same parameters as the original
			function reformatTemplate(newTemplate, originalTemplate, originalFormatted) {
				// Extract the parameters from the original formatting
				const placeholders = originalTemplate.match(/%s/g) || [];
				if (placeholders.length === 0) {
					return newTemplate; // No placeholders
				}

				// For HTML content, extract the substituted values from the original formatted HTML
				// by comparing it with the original template
				const substitutedHTMLValues = [];
				let remainingFormatted = originalFormatted;
				let remainingTemplate = originalTemplate;

				for (let i = 0; i < placeholders.length; i++) {
					const beforePlaceholder = remainingTemplate.split('%s')[0];
					const afterPlaceholder = remainingTemplate.substring(beforePlaceholder.length + 2);
					
					// Remove the before part (handle HTML vs text)
					const beforeIndex = remainingFormatted.indexOf(beforePlaceholder);
					if (beforeIndex !== -1) {
						remainingFormatted = remainingFormatted.substring(beforeIndex + beforePlaceholder.length);
					}
					
					// Find where the next part starts
					let valueEnd = remainingFormatted.length;
					if (afterPlaceholder) {
						const nextPartStart = afterPlaceholder.split('%s')[0];
						if (nextPartStart) {
							const nextPartIndex = remainingFormatted.indexOf(nextPartStart);
							if (nextPartIndex !== -1) {
								valueEnd = nextPartIndex;
							}
						}
					}
					
					const substitutedHTMLValue = remainingFormatted.substring(0, valueEnd);
					substitutedHTMLValues.push(substitutedHTMLValue);
					
					remainingFormatted = remainingFormatted.substring(valueEnd);
					remainingTemplate = afterPlaceholder;
				}

				// Apply the same HTML substitutions to the new template
				let result = newTemplate;
				for (let i = 0; i < substitutedHTMLValues.length && i < placeholders.length; i++) {
					result = result.replace('%s', substitutedHTMLValues[i]);
				}
				
				return result;
			}

			// Set up button handlers
			document.getElementById('export-json').addEventListener('click', exportJSON);
			document.getElementById('copy-json').addEventListener('click', copyToClipboard);
			document.getElementById('reset-all').addEventListener('click', resetAllChanges);


			// Initialize JSON preview
			updateJSONPreview();
		});
	</script>
</body>
</html>
	<?php
}

// Translator review page
if ( isset( $_GET['translate-review'] ) ) {
	$review_lang = $_GET['translate-review'];
	render_translator_ui( $review_lang );
	exit;
}

// Render the main UI
render_main_ui();
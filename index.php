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

// Translation function with GitHub edit links for review mode
function _t( $key, $escape_html = true ) {
	global $translations, $lang;
	
	// Get English fallback
	$english_translations = load_translations( 'en' );
	$fallback_text = isset( $english_translations[$key] ) ? $english_translations[$key] : ucfirst( str_replace( '_', ' ', $key ) );
	$translated_text = isset( $translations[$key] ) ? $translations[$key] : $fallback_text;
	
	// For translation review mode, wrap with hover functionality
	if ( isset( $_GET['translate-review'] ) ) {
		$github_url = "https://github.com/akirk/cantfollowyou/edit/main/translations/" . $lang . ".json";
		
		$content = $escape_html ? htmlspecialchars( $translated_text ) : $translated_text;
		
		return '<span class="translation-text" onclick="window.open(\'' . $github_url . '\', \'_blank\')">' .
			   '<span class="github-link">Edit ' . htmlspecialchars( $lang ) . '.json on GitHub</span>' .
			   $content .
			   '</span>';
	}
	
	return $translated_text;
}

function _t_attr( $key ) {
	// For attributes, always return plain text without hover functionality
	return _t( $key, false );
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
		.translation-text {
			position: relative;
			cursor: pointer;
			transition: background-color 0.2s;
		}
		.translation-text:hover {
			background-color: #e3f2fd;
		}
		.github-link {
			position: absolute;
			top: -35px;
			left: 0;
			background: #333;
			color: white;
			padding: 5px 10px;
			border-radius: 4px;
			font-size: 12px;
			white-space: nowrap;
			opacity: 0;
			pointer-events: none;
			transition: opacity 0.2s;
			z-index: 1000;
		}
		.translation-text:hover .github-link {
			opacity: 1;
		}
		.github-link::after {
			content: '';
			position: absolute;
			top: 100%;
			left: 20px;
			border: 5px solid transparent;
			border-top-color: #333;
		}
	</style>
</head>
<body>

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

<div class="container">
	<h1><?php echo sprintf( _t( 'hi' ), htmlspecialchars( $username ) ); ?></h1>
	<p>
		<?php echo sprintf( _t( 'want_to_follow_you' ), htmlspecialchars( $platform ) ); ?>
	</p>
	<p>
		<?php echo sprintf( _t( 'cant_follow_from_elsewhere' ), '<mark>' . htmlspecialchars( $this_platform ) . '</mark>' ); ?>
	</p>
	<p>
		<?php echo str_replace( array_keys( $replace_no_technical_reason ), array_values( $replace_no_technical_reason ), sprintf( _t( 'no_technical_reason' ), htmlspecialchars( $this_platform ) ) ); ?>
	</p>
	<p>
		<?php echo sprintf( _t( 'alternative_open_web' ), htmlspecialchars( $this_platform ) ); ?>
	</p>
	<p>
		<?php echo str_replace( array_keys( $replace_called_fediverse ), array_values( $replace_called_fediverse ), sprintf( _t( 'called_fediverse', false ), '<a href="' . $new_platform_url . '">' . htmlspecialchars( $new_platform ) . '</a>' ) ); ?>
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
	<?php
}

function render_translator_ui( $review_lang ) {
	$available_languages = get_available_languages();
	$sample_platforms = array( 'Twitter', 'Instagram', 'Facebook', 'TikTok' );
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
			display: none;
			border: 2px solid #007bff;
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
	</style>
</head>
<body>
	<div class="container">
		<h1>🌍 Translation Review<?php if ( $review_lang && $review_lang !== 'all' ) echo ' - ' . htmlspecialchars( $review_lang ); ?></h1>
		<p>This page shows all translations for different platforms to help translators review and improve the content. Hover over text to see GitHub edit links.</p>

		<div class="controls">
			<h3>📝 Contribute Translations</h3>
			<p>Want to add or improve translations? You can easily create a pull request on GitHub:</p>
			<a href="https://github.com/akirk/cantfollowyou/compare" class="button" target="_blank">Create Pull Request</a>
			<a href="https://github.com/akirk/cantfollowyou/issues/new?title=Translation%20suggestion&body=Language:%0APlatform:%0ASuggested%20text:%0A" class="button button-secondary" target="_blank">Report Translation Issue</a>

			<div class="platform-selector">
				<label for="platform-select">Select Platform to Review:</label>
				<select id="platform-select">
					<option value="">All Platforms</option>
					<?php foreach ( $sample_platforms as $platform ) : ?>
						<option value="<?php echo htmlspecialchars( strtolower( $platform ) ); ?>"><?php echo htmlspecialchars( $platform ); ?></option>
					<?php endforeach; ?>
				</select>
				<button onclick="showAllPlatforms()">Show All</button>
			</div>
		</div>

		<?php foreach ( $sample_platforms as $sample_platform ) : ?>
		<div class="platform-content" id="platform-<?php echo htmlspecialchars( strtolower( $sample_platform ) ); ?>">
			<?php
			// Capture the main UI output for this platform
			ob_start();

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

			render_main_ui( $sample_platform, 'TestUser' );
			$content = ob_get_clean();

			// Restore original language
			$lang = $original_lang;
			$translations = $original_translations;

			echo $content;
			?>
		</div>
		<?php endforeach; ?>

		<div class="controls">
			<h3>🚀 Quick Actions</h3>
			<p>Ready to contribute? Here are some quick actions:</p>
			<a href="https://github.com/akirk/cantfollowyou/fork" class="button" target="_blank">Fork Repository</a>
			<a href="https://github.com/akirk/cantfollowyou/tree/main/translations" class="button button-secondary" target="_blank">View Translation Files</a>
			<a href="/" class="button button-secondary">← Back to Main Site</a>
		</div>
	</div>

	<script>
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

		function showAllPlatforms() {
			document.getElementById('platform-select').value = '';
			showPlatform('');
		}

		document.getElementById('platform-select').addEventListener('change', function() {
			showPlatform(this.value);
		});

		// Show all platforms by default
		showAllPlatforms();
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
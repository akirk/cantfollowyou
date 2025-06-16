<?php
define( 't_title', "I can't follow you!" );
define( 't_language', 'English' );
define( 't_hi', "Hi %s," );
define( 't_friend', "Friend" );
define( 't_a_closed_platform', 'a closed, centralized network' );
define( 't_the_fediverse', 'the Fediverse' );
define( 't_want_to_follow_you', "I saw that you are on %s and I’d like to follow you!" );
define( 't_cant_follow_from_elsewhere', "But did you know that <strong>because I am not a user on %s</strong>, I cannot follow you from elsewhere?" );
define( 't_no_technical_reason', 'There is no technical reason for this, the restriction is purely %s\'s choice because they want more users on their platform.' );
define( 't_alternative_open_web', 'However, I believe you don\'t need to be on %s because <strong>there is an alternative on the open web</strong>!' );
define( 't_called_fediverse', "It's called <strong>%s</strong> and it is part of the Fediverse, which is not controlled by a central entity and thus doesn't impose such restrictions." );
define( 't_join_us', 'Interested? Join us and discover a better way to socialize online!' );
define( 't_open_alternative', 'Most centralized networks have an open alternative, you can see them here:' );
define( 't_learn_more', 'Learn more about the Fediverse' );
define( 't_or', 'or' );
define( 't_watch_video', 'Watch "Introducing the Fediverse"' );
define( 't_send_to_friend', 'Want to send such a link to your friend?' );
define( 't_enter_friend_url', "Enter your friend's URL" );
define( 't_sorry_unknown_platform', "Sorry, I don't know this platform (yet)" );
define( 't_idea_and_hosting', 'Idea and hosting by' );

$translations = array(
	'de' => array(
		t_title => 'Ich kann dir nicht folgen!',
		t_language => 'Deutsch',
		t_hi => 'Hallo %s,',
		t_friend => 'Freund',
		t_a_closed_platform => 'eine geschlossene, zentralisierte Plattform',
		t_the_fediverse => 'das Fediverse',
		t_want_to_follow_you => 'Ich habe gesehen, dass du auf %s bist und ich möchte dir gerne folgen!',
		t_cant_follow_from_elsewhere => 'Aber wusstest du, dass ich dir, <strong>weil ich nicht auf %s bin</strong>, nicht von anderen Plattformen aus folgen kann?',
		t_no_technical_reason => 'Es gibt keinen technischen Grund dafür. Dies ist eine absichtliche Entscheidung von %s, weil sie mehr Nutzer auf ihrer Plattform haben wollen.',
		t_alternative_open_web => 'Ich glaube aber, dass du nicht auf %s sein musst, weil es <strong>eine Alternative im offenen Web gibt</strong>!',
		t_called_fediverse => 'Sie heißt <strong>%s</strong> und ist Teil des Fediverse, das nicht von einer zentralen Instanz kontrolliert wird und daher keine solche Einschränkungen hat.',
		t_join_us => 'Interessiert? Schließe dich uns an und entdecke eine bessere Art, online zu kommunizieren!',
		t_open_alternative => 'Die meisten zentralisierten Netzwerke haben eine offene Alternative, die du hier sehen kannst:',
		t_learn_more => 'Erfahre mehr über das Fediverse',
		t_or => 'oder',
		t_watch_video => 'Schau dir "Einführung in das Fediverse" an',
		t_send_to_friend => 'Möchtest du so einen Link an deinen Freund senden?',
		t_enter_friend_url => 'Gib die URL deines Freundes ein',
		t_sorry_unknown_platform => 'Entschuldigung, ich kenne diese Plattform (noch) nicht',
		t_idea_and_hosting => 'Idee und Hosting von',
	),
);
$lang = 'en';
if (isset($_GET['lang']) && isset($translations[$_GET['lang']]) || 'en' === $_GET['lang']) {
	$lang = $_GET['lang'];
} else {
	$browser_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
	if (isset($translations[$browser_lang])) {
		$lang = $browser_lang;
	}
}
function __( $string ) {
	global $translations, $lang;
	if ( isset( $translations[$lang][$string] ) ) {
		return $translations[$lang][$string];
	}
	// Fallback to the original string if no translation is found
	return $string;
}

?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo htmlspecialchars(__(t_title)); ?></title>
	<style>
		:root {
			color-scheme: light dark;
		}
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
			max-width: 650px;
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
		footer a:any-link {
			color: #666;
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
		summary {
			cursor: pointer;
		}

		#language-switcher {
			position: absolute;
			top: 10px;
			right: 10px;
		}
		@media (max-width: 600px) {
			body {
				padding: 10px;
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
		}
	</style>
</head>
<body>
<?php
$request_uri = strtok( urldecode( $_SERVER['REQUEST_URI'] ), '?');
if ( isset( $_GET['url'] ) ) {
	$request_uri = $_GET['url'];
}
$segments = explode('/', trim($request_uri, '/'), 2);
$username = __( t_friend );
$platform = __(t_a_closed_platform);
$new_platform = __(t_the_fediverse);
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
);

// Add translations for all platform messages using __() function
$platforms = array(
	array(
		'regex' => 'x(?:\.com)?' . $username_regex,
		'url_part' => 'x',
		'name' => 'X',
		'new_platforms' => array('Mastodon', 'Misskey', 'Pleroma'),
	),
	array(
		'regex' => '(?:twitter\.com(?:\.com)?|tw)' . $username_regex,
		'name' => 'Twitter',
		'url_part' => 'tw',
		'new_platforms' => array('Mastodon', 'Misskey', 'Pleroma'),
	),
	array(
		'regex' => '(?:instagram(?:\.com)?|ig|insta)' . $username_regex,
		'name' => 'Instagram',
		'url_part' => 'insta',
		'new_platforms' => array('Pixelfed'),
	),
	array(
		'regex' => '(?:facebook(?:\.com)?|fb)' . $username_regex,
		'name' => 'Facebook',
		'url_part' => 'fb',
		'new_platforms' => array('Friendica'),
	),
	array(
		'regex' => '(?:tiktok(?:\.com)?)' . $username_regex,
		'name' => 'TikTok',
		'url_part' => 'tiktok',
		'new_platforms' => array('PeerTube'),
	),
	array(
		'regex' => '(?:youtube(?:\.com)?|yt)' . $username_regex,
		'name' => 'YouTube',
		'url_part' => 'yt',
		'new_platforms' => array('PeerTube'),
	),
	array(
		'regex' => 'reddit(?:\.com)?(?:\/u)?' . $username_regex,
		'name' => 'Reddit',
		'url_part' => 'reddit',
		'new_platforms' => array('Lemmy'),
	),
	array(
		'regex' => '(?:tumblr(?:\.com)?)' . $username_regex,
		'name' => 'Tumblr',
		'url_part' => 'tumblr',
		'new_platforms' => array('Mastodon', 'Misskey', 'Pleroma'),
	),
	array(
		'regex' => '(?:snapchat(?:\.com)?|sc)' . $username_regex,
		'name' => 'Snapchat',
		'url_part' => 'sc',
		'new_platforms' => array('Mastodon', 'Misskey', 'Pleroma'),
	),
);

foreach ($platforms as $platform_data) {
	if (preg_match('/^' . $regex_prefix . $platform_data['regex'] . '$/i', implode('/', $segments), $matches)) {
		if (isset($matches['username']) && !empty($matches['username'])) {
			$username = htmlspecialchars(ltrim($matches['username'], '/@'));
		}
		$platform = $platform_data['name'];
		$new_platform = $platform_data['new_platforms'][0];
		$new_platform_url = $new_platforms[$platform_data['new_platforms'][0]];
		break;
	}
}
?>
<select id="language-switcher">
	<option value="en" <?php echo $lang === 'en' ? 'selected' : ''; ?>>
		<?php echo htmlspecialchars(t_language); ?>
	</option>
<?php foreach ( $translations as $lang_code => $trans ) : ?>
	<option value="<?php echo htmlspecialchars($lang_code); ?>" <?php echo $lang === $lang_code ? 'selected' : ''; ?>>
		<?php echo htmlspecialchars($trans[t_language]); ?>
	</option>
<?php endforeach; ?>

</select>

<div class="container">
	<h1><?php echo sprintf(__(t_hi), htmlspecialchars($username)); ?></h1>
	<p>
		<?php echo sprintf(__(t_want_to_follow_you), htmlspecialchars($platform)); ?>
	</p>
	<p>
		<?php echo sprintf(__(t_cant_follow_from_elsewhere), htmlspecialchars($platform)); ?>
	</p>
	<p>
		<?php echo sprintf(__(t_no_technical_reason), htmlspecialchars($platform)); ?>
	</p>
	<p>
		<?php echo sprintf(__(t_alternative_open_web), htmlspecialchars($platform)); ?>
	</p>
	<p>
		<?php echo sprintf(__(t_called_fediverse), htmlspecialchars($new_platform)); ?>
	</p>
	<p>
		<?php echo __(t_join_us); ?>
	</p>
	<details>
		<summary><?php echo __(t_open_alternative); ?></summary>
		<ul>
			<?php foreach ($platforms as $platform_data) : ?>
				<li>
					<strong><?php echo htmlspecialchars($platform_data['name']); ?></strong> →
					<?php foreach ($platform_data['new_platforms'] as $new_platform) : ?>
						<a href="<?php echo htmlspecialchars($new_platforms[$new_platform]); ?>"><?php echo htmlspecialchars($new_platform); ?></a>
						<?php if (end($platform_data['new_platforms']) !== $new_platform) : ?>, <?php endif; ?>
					<?php endforeach; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</details>

	<a href="https://jointhefediverse.net" class="button"><?php echo __(t_learn_more); ?></a> <?php echo __(t_or); ?> <a href="https://videos.elenarossini.com/w/64VuNCccZNrP4u9MfgbhkN"><?php echo __(t_watch_video); ?></a>.
</div>

<footer>
	<p><?php echo __(t_send_to_friend); ?> <input type="url" placeholder="<?php echo __(t_enter_friend_url); ?>" id="friend-url" /></p>
	<p><?php echo __(t_idea_and_hosting); ?> <a href="https://alex.kirk.at/">Alex Kirk</a></p>
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
				<?php foreach ($platforms as $platform_data) : ?>
					if (!url.length) {
						m = friend_url.match(new RegExp('^<?php echo $regex_prefix . str_replace('(?P<', '(?<', $platform_data['regex']); ?>', 'i'));
						if (m) {
							url.push('<?php echo strtolower($platform_data['url_part']); ?>');
							if (m.groups && m.groups['username']) {
								url.push(m.groups['username'].replace(/^[\/@]/, ''));
							}
						}
					}
				<?php endforeach; ?>
			}
			debugger;
			if (url.length) {
				window.location.href = '/' + url.join('/');
			} else {
				input.setCustomValidity("<?php echo __(t_sorry_unknown_platform); ?>");
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
			url.searchParams.set('lang', lang);
			window.location.href = url.toString();
		});
	});
</script>

</body>
</html>

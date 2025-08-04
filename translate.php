<?php

$lang = null;

require_once 'common.php';

$review_lang = $_GET['lang'] ?? null;
$available_languages = get_available_languages();
$sample_platforms = array_merge( array( 'unknown' ), array_keys( $centralized_platforms ) );

// Check if this is a new language being created
$is_new_language = ( $review_lang === 'new' || ( $review_lang && ! array_key_exists( $review_lang, $available_languages ) ) );

// If no language specified, show language selector
if ( ! $review_lang ) {
	?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Translate - I can't follow you!</title>
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
		.language-option {
			display: block;
			padding: 15px 20px;
			margin: 10px 0;
			background: #f8f9fa;
			border: 1px solid #dee2e6;
			border-radius: 8px;
			text-decoration: none;
			color: #495057;
			transition: all 0.2s;
		}
		.language-option:hover {
			background: #e9ecef;
			border-color: #007bff;
		}
		.language-option.new-language {
			background: #e3f2fd;
			border-color: #bbdefb;
			color: #1976d2;
		}
		.language-option.new-language:hover {
			background: #bbdefb;
		}
		.button {
			display: inline-block;
			padding: 12px 24px;
			background: #6c757d;
			color: white;
			text-decoration: none;
			border-radius: 4px;
			margin-top: 20px;
		}
		.button:hover { background: #545b62; }
	</style>
</head>
<body>
	<div class="container">
		<h1>🌍 Translation Review</h1>
		<p>Choose a language to review or create a new translation:</p>
		
		<a href="?lang=new" class="language-option new-language">
			<strong>🆕 Create New Language</strong><br>
			<small>Start a new translation from English template</small>
		</a>
		
		<?php foreach ( $available_languages as $lang_code => $lang_name ) : ?>
			<?php if ( $lang_code !== 'en' ) : ?>
			<a href="?lang=<?php echo urlencode( $lang_code ); ?>" class="language-option">
				<strong><?php echo htmlspecialchars( $lang_name ); ?> (<?php echo htmlspecialchars( $lang_code ); ?>)</strong><br>
				<small>Review and edit existing translation</small>
			</a>
			<?php endif; ?>
		<?php endforeach; ?>
		
		<a href="/" class="button">← Back to Main Site</a>
	</div>
</body>
</html>
		<?php
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
		.editable-translation.placeholder-error {
			border-color: #dc3545;
			background: rgba(220, 53, 69, 0.1);
		}
		.editable-translation.untranslated {
			background: rgba(220, 53, 69, 0.15);
			border-color: #dc3545;
			border-width: 2px;
		}
		.editable-translation.untranslated:focus {
			background: rgba(255, 193, 7, 0.2);
			border-color: #ffc107;
		}
		.new-language-form {
			background: #e3f2fd;
			border: 1px solid #bbdefb;
			border-radius: 8px;
			padding: 20px;
			margin-bottom: 30px;
		}
		.new-language-form h3 {
			margin-top: 0;
			color: #1976d2;
		}
		.form-row {
			display: flex;
			gap: 20px;
			margin-bottom: 15px;
		}
		.form-group {
			flex: 1;
		}
		.form-group label {
			display: block;
			font-weight: bold;
			margin-bottom: 5px;
			color: #495057;
		}
		.form-group input {
			width: 100%;
			padding: 8px 12px;
			border: 1px solid #dee2e6;
			border-radius: 4px;
			font-size: 14px;
		}
		.form-group small {
			color: #6c757d;
			font-size: 12px;
		}
	</style>
</head>
<body>
	<div class="container">
		<?php if ( $is_new_language ) : ?>
		<h1>🌍 Create New Language Translation</h1>
		<p>Create a new language translation by editing the English text below. Red highlighted text indicates untranslated content that changes to yellow once you start editing it.</p>

		<div class="new-language-form">
			<h3>📝 New Language Details</h3>
			<div class="form-row">
				<div class="form-group">
					<label for="new-lang-code">Language Code</label>
					<input type="text" id="new-lang-code" placeholder="e.g., fr, de, es, pt-br" value="<?php echo $review_lang !== 'new' ? htmlspecialchars( $review_lang ) : ''; ?>">
					<small>Use standard language codes (ISO 639-1). For variants, use format like 'pt-br'.</small>
				</div>
				<div class="form-group">
					<label for="new-lang-name">Language Name</label>
					<input type="text" id="new-lang-name" placeholder="e.g., Français, Deutsch, Español">
					<small>The native name of the language as it should appear in the selector.</small>
				</div>
			</div>
		</div>
		<?php else : ?>
		<h1>🌍 Translation Review - <?php echo htmlspecialchars( $review_lang ); ?></h1>
		<p>This page shows all translations for different platforms with in-place editing. Click on any highlighted text to edit it directly, then export your changes as JSON.</p>
		<?php endif; ?>

		<p><a href="?translate-review" class="button button-secondary">← Choose Different Language</a></p>

		<?php foreach ( $sample_platforms as $sample_platform ) : ?>
		<div class="platform-content" id="platform-<?php echo htmlspecialchars( strtolower( $sample_platform ) ); ?>">
			<?php
			// Set language to review language or default
			global $lang, $translations;
			$original_lang = $lang;
			$original_translations = $translations;
			if ( $is_new_language ) {
				// For new languages, show English content
				$lang = 'en';
				$translations = load_translations( 'en' );
			} elseif ( $review_lang && $review_lang !== 'all' && array_key_exists( $review_lang, $available_languages ) ) {
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
			// Start with original translations (we need to reconstruct this from the page)
			const originalTranslations = {};
			document.querySelectorAll('.editable-translation').forEach(el => {
				const key = el.dataset.key;
				const original = el.dataset.original;
				originalTranslations[key] = original;
			});

			// Apply modifications
			const updatedTranslations = {...originalTranslations, ...modifications};

			// For new languages, add the language name if provided
			<?php if ( $is_new_language ) : ?>
			const langName = document.getElementById('new-lang-name').value.trim();
			if (langName) {
				updatedTranslations.language = langName;
			}
			<?php endif; ?>

			return updatedTranslations;
		}

		function exportJSON() {
			const translations = generateUpdatedJSON();

			// For new languages, add the language name from the form
			<?php if ( $is_new_language ) : ?>
			const langName = document.getElementById('new-lang-name').value.trim();
			if (langName) {
				translations.language = langName;
			}
			<?php endif; ?>

			const json = JSON.stringify(translations, null, 2);
			
			const blob = new Blob([json], { type: 'application/json' });
			const url = URL.createObjectURL(blob);
			
			const a = document.createElement('a');
			a.href = url;

			<?php if ( $is_new_language ) : ?>
			const langCode = document.getElementById('new-lang-code').value.trim().toLowerCase();
			a.download = (langCode || 'new-language') + '.json';
			<?php else : ?>
			a.download = '<?php echo htmlspecialchars( $review_lang ); ?>.json';
			<?php endif; ?>

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
						// Remove untranslated class when content is modified
						this.classList.remove('untranslated');
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
						// Remove untranslated class when synced content is modified
						element.classList.remove('untranslated');
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

			<?php if ( $is_new_language ) : ?>
			// For new languages, update JSON preview when language details change
			document.getElementById('new-lang-name').addEventListener('input', updateJSONPreview);
			document.getElementById('new-lang-code').addEventListener('input', updateJSONPreview);
			<?php endif; ?>

			// Initialize JSON preview
			updateJSONPreview();
		});
	</script>
</body>
</html>

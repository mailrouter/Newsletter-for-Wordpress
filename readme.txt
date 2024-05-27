=== Newsletter for WordPress ===
Contributors: Morloi, Bago
Tags: newsletter, nl4wp, email, marketing, newsletter, subscribe, widget, nl4wp, contact form 7, woocommerce, buddypress, newsletter forms, newsletter integrations
Requires at least: 4.1
Tested up to: 6.5.2
Stable tag: 4.5.12
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires PHP: 5.6.20

Newsletter for WordPress, usa il tuo sito Wordpress per raccogliere iscritti per la tua Newsletter!

== Description ==

#### Newsletter for WordPress

*Aggiungere metodi di iscrizione alla tua lista iscritti dovrebbe essere semplice e immediato. Ora lo è.*

Newsletter for WordPress ti aiuta a aumentare il numero degli iscritti alla tua newsletter, integrandosi alla perfezione con l'account del tuo ESP. Potrai creare moduli di ogni tipo, oppure integrare l'iscrizione direttamente nei commenti di Wordpress, nel form di registrazione, oppure integrare l'iscrizione con Woocommerce, Ninja Forms e tanti altri plugin.

#### Alcune delle caratteristiche di Newsletter for Wordpress

- Connetti il tuo account ESP in un secondo.

- I moduli di iscrizione sono belli e pensati per essere responsive. Puoi decidere cosa inserire nel modulo e quali dati mandare al tuo ESP.

- Integrazione completa con i seguenti plugin:
	- Default WordPress Comment Form
	- Default WordPress Registration Form
	- Contact Form 7
	- WooCommerce
	- Gravity Forms
	- Ninja Forms 3
	- WPForms
	- BuddyPress
    - MemberPress
	- Events Manager
	- Easy Digital Downloads
	- Give
	- UltimateMember

#### Che cos'è un ESP e quali ESP sono compatibili con Newsletter for WordPress?

L'ESP, acronimo di Email Service Provider. E' una società che gestisce campagne di email marketing. 
Gli ESP sono nati come risposta alla difficoltà di invio email a grandi numeri di destinatari. Tale gestione include infatti la semplificazione di importazione/esportazione degli iscritti, la fornitura di procedure semplici di iscrizione e disiscrizione, la gestione del contatto con gli inbox providers (Feedback Loop, Whitelist), il monitoring delle blacklist, l'analisi del Bounce, l'integrazione di tecniche di autenticazione della posta elettronica. 
Newsletter for Worpress facilita l'implementazione della procedura di iscrizione degli utenti, collegandosi direttamente al tuo account presso l'ESP, consentendo una integrazione completa fra ESP e sito Wordpress.


== Installation ==

#### Come installare il plugin
1. Scarica il plugin dal tuo account presso l'ESP di fiducia. Otterrai un semplice file Zip.
2. Accedi al pannello di amministrazione di Wordpress, vai su "Plugin", clicca su "Aggiungi nuovo" e successivamente su "Carica Plugin". Seleziona il file dal tuo disco e caricalo nel sito.
2. Attiva il plugin
1. Immetti la tua chiave API nella configurazione del plugin.

#### Come configurare il Modulo di iscrizione
1. Vai su *NL4WP > Modulo*
2. Seleziona una lista in cui andranno a finire gli iscritti.
3. *(Opzionale)* Aggiungi più campi al modulo scegliendoli da quelli proposti.
4. Usa il modulo nelle pagine o negli articoli usando il pulsante "Preleva Shortcode".
5. Mostra un modulo di iscrizione nella tua area widget usando il widget "Iscrizione alla newsletter".
6. Inserisci il modulo di iscrizione tramite i tuoi file di tema, usando la seguente funzione PHP.

`
<?php

if( function_exists( 'nl4wp_show_form' ) ) {
	nl4wp_show_form();
}
`

== Frequently Asked Questions ==

#### Come inserisco il modulo nelle pagine o negli articoli?
Usa il pulsante "Preleva Shortcode" nella pagina *Newsletter for WP > Modulo* e incollalo nella tua pagina o nel tuo articolo. 

#### Dove trovo la mia chiave API?
Normalmente la troverai nell'account del tuo ESP, andando in *Account > Specifiche API*

#### Come aggiungo un checkbox di iscrizione nel mio modulo Contact Form 7?
Usa il seguente shortcode.

`
[nl4wp_checkbox "Iscriviti alla nostra newsletter?"]
`

#### Il modulo mostra la pagina di iscrizione avvenuta, ma non trovo l'iscritto dentro al mio database, come mai?
Se il modulo mostra la pagina di successo, l'iscrizione è avvenuta. Se, come consigliato, usi il metodo di Confirmed OptIn, dovrai aspettare che l'utente riceva l'email e che clicchi sul link di conferma.

#### Come posso cambiare lo stile del modulo?
Se non gradisci i temi offerti dal plugin, puoi usare i CSS per modificare a piacere l'aspetto del modulo; qui sotto trovi i selettori coinvolti.

`
.nl4wp-form { ... } /* il modulo */
.nl4wp-form p { ... } /* i paragrafi del modulo */
.nl4wp-form label { ... } /* etichette */
.nl4wp-form input { ... } /* campi di input */
.nl4wp-form input[type="checkbox"] { ... } /* checkboxe */
.nl4wp-form input[type="submit"] { ... } /* pulsante di invio */
.nl4wp-alert { ... } /* messaggi di successo e errore */
.nl4wp-success { ... } /* messaggi di successo */
.nl4wp-error { ... } /* messaggi di errore */
`

#### Come posso visualizzare il mio form in un popup?

Esistono molti plugin che gestiscono moduli in popup, con comportamenti "smart". All'interno di questi puoi normalmente utilizzare lo Shortcode per il tuo modulo Newsletter for Wordpress.

== Other Notes ==

#### Assistenza

Per ogni questione relativa al plugin e al suo funzionamento, fate riferimento al vostro ESP.

Changelog
=========

#### 4.5.12 - May 27, 2024

**Improvements**

- Multiple modules support.
- Experimental multiple API keys support.
- Better use of UI space (full width views).

**Dev**

- Updated Newsletter service wrapper.php to 1.31 (still includes xmlrpc.inc but shouldn't be used anymore)
- Updated Plugin Update Checker to v5.4
- Use json API and wp methods for HTTP API calls by default when curl and json are available.

**Fixes**

- Translations fixes.
- Cleanup unused classes.
- Fix API key obfuscation.


#### 4.5.11 - May 6, 2024

**Fixes**

- Fix PHP8+ compatibility/warnings/notices.


#### 4.5.10 - Jul 31, 2023

**Fixes**

- Fix PHP8 compatibility (fatal error on user.create).
- Improve debug logging


#### 4.5.9 - Aug 11, 2022

**Improvements**

- Updated plugin-update-checker to 4.13.
- Updated wrapper to 1.30 (improved setup error reporting and compatibility with allow_url_fopen=0 directive).


#### 4.5.8 - May 18, 2022

**Improvements**

- Updated plugin-update-checker to 4.11 with support for automatic updates.


#### 4.5.7 - Mar 18, 2022

**Fixes**

- Fix version number (4.5.6 regression)
- Fix email address handling (we did a lowercase in past)
- Refactored API calls/mapping
- User-Agent changes


#### 4.5.5 - Sep 12, 2019

**Fixes**

- Google reCAPTCHA script was still loading even if no forms have it enabled.


#### 4.5.4 - Sep 11, 2019

**Improvements**

- Removed custom color from menu item for improved accessibility.
- Take birthday field format into account when sending data to Newsletter.
- Print Google reCAPTCHA script in footer.

**Changes**

- Changed plugin name to NL4WP instead of Newsletter for WordPress.


#### 4.5.3 - July 23, 2019

**Fixes**

- Temporarily switch status of pending subscribers to "unsubscribe" versus deleting susbcriber before re-subscribing.
- Deprecation notice for Gravity Forms version 2.4 and higher.

**Improvements**

- Filter out empty tags when applying tags to new subscribers.
- Show all not installed integrations.
- Show notice when form doesn't have a Newsletter list selected to subscribe people to.
- Check function existence for compatibility with WordPress 4.7
- Don't submit form when Google reCAPTCHA is enabled but errors.
- Update third-party JavaScript dependencies.


#### 4.5.2 - May 8, 2019

**Improvements**

- Accept more truthy values in custom integration for improved compatibility with third-party forms.
- Update JavaScript dependencies.
- Load Google reCaptcha script in footer (if needed). 


#### 4.5.1 - April 8, 2019

**Additions**

- Add sign-up integration for [Give](https://wordpress.org/plugins/give/)
- Add sign-up integration for [UltimateMember](https://wordpress.org/plugins/ultimate-member/)

**Improvements**

- Write to debug log if Google reCAPTCHA secret key is incorrect.
- Validate reCAPTCHA keys when savings form settings.
- Allow setting an empty "successfully subscribed" message.


#### 4.5.0 - March 27, 2019

**Additions**

- Built-in integration with Google reCAPTCHA to prevent bots from subscribing to your Newsletter lists.

**Improvements**

- Minor improvements to the JavaScript that is loaded on admin pages.


#### 4.4.0 - March 1, 2019

**Fixes**

- AffiliateWP integration subscribing the wrong user if affiliate ID differs from user ID.

**Improvements**

- Renamed "Newsletter" to "Newsletter" to match Newsletter's new branding.
- More accurate handling of timeouts for accounts with many Newsletter lists.
- UX improvements for integrations overview page.
- Validate Newsletter API key format when it's entered.
- Improved compatibility with Klarna Checkout in the WooCommerce checkout integration.
- Bumped required PHP version to 5.3 (soft requirement for now).

**Additions**

- Added Gutenberg block for easily adding a form to a post or page.
- Added subscriber tags setting to forms.


#### 4.3.3 - December 31, 2018

**Fixes**

- Update WPForms integration to properly detect if the WPForms plugin is activated.

**Improvements**

- Write API request parameters to the debug log in case of connection timeouts. 
- Update JavaScript dependencies.


#### 4.3.2 - December 11, 2018

**Fixes**

- Use of `readonly` function, which is only available in WordPress 4.9 or later.


#### 4.3.1 - November 28, 2018

**Fixes**

- Fatal error on PHP versions older than 5.5


#### 4.3 - November 28, 2018

**Additions**

- Added `NL4WP_API_KEY` PHP constant which can be used to set your Newsletter API key.
- Add `nl4wp_newsletter_list_limit` filter hook to modify the maximum number of Newsletter lists to fetch. Defaults to 200.

**Improvements**

- Apply `nl4wp_integration_gravity-forms_options` filter hook on Gravity Forms integration options so the checkbox can be prechecked and the checkbox label text modified.
- The `updated_subscriber` JS event is now fired forms not using AJAX as well (when applicable).


#### 4.2.5 - Sep 11, 2018

**Improvements**

- Only re-add subscriber to list if we want to re-trigger a double opt-in confirmation email.
- Change Gravity Forms field name to "Newsletter for WordPress"
- Get rid of cached result of Newsletter API connection.


#### 4.2.4 - July 9, 2018

**Improvements**

- Ensure type-safety on some global variables.
- Stop showing trashed forms immediately.
- Pre-check Newsletter list when creating a new form if there is only 1 list.
- Send `null` for unknown values in usage tracking data (only when opted-in).

**Additions**

- Add methods for accessing Newsletter's e-commerce promo code endpoints to API class.


#### 4.2.3 - June 11, 2018

**Fixes**

- Don't wrap "agree to terms" input in hyperlink element.
- Allow [ENTER] key again after field helper overlay is closed.

**Improvements**

- Fallback to meta-refresh if redirect fails because of "headers already sent" error.



#### 4.2.2 - May 22, 2018

**Fixes**

- Events Manager integration was not working with logged-in users.
- Form preview URL should respect admin HTTP(S) scheme.
- Removed use of PHP 5.4 function.

**Improvements**

- Add "agree to terms" checkbox to field helper.

**Additions**

- Add filter `nl4wp_http_request_args`.


#### 4.2.1 - April 11, 2018

**Fixes**

- Namespace usage warning when running PHP 5.2

**Improvements**

- Remove obsolete `type` attribute from all `<script>` tags printed by the plugin.
- Improved tooltips on settings pages.
- Do not pre-check integration checkboxes by default. 
- Add textual warnings to settings that may affect GDPR compliance.
- Update translation files.

#### 4.2 - March 5, 2018

**Additions**

- Live form preview while editing form. 

**Improvements**

- Improved conditional fields logic.
- Debug log now includes request & response data.
- Form JavaScript events are fired in an isolated thread now, to prevent errors in event callbacks from breaking form functionality.
- Don't send empty field values to Newsletter when updating subscribers.
- Show interest grouping ID in list overview on settings page.

**Fixes**

- Ninja Forms export checkbox would always state "checked" when form contained a Newsletter sign-up checkbox.



#### 4.1.15 - February 7, 2018

**Fixes**

- Dropdown fields with special characters were not properly passed to Newsletter.
- Interest groups with an all-numeric ID were not properly passed to Newsletter.

**Improvements**

- Various minor code optimizations
- Do not redirect when showing "already subscribed" warning.
- Improved scroll to form handling after a form is submitted without AJAX.


#### 4.1.14 - January 8, 2018

**Fixes**

- Validate method was incorrectly checking required array fields.

**Improvements**

- Wrap some missing strings in translate calls. Thanks [morloi](https://github.com/morloi).
- Make it clear that redirecting after successful form submissions will not show the "subscribed" message.



#### 4.1.13 - December 28, 2017

**Fixes**

- Array to string conversion in default form messages.

**Additions**

- Allow marking Gravity Forms sign-up checkbox as a required field.


#### 4.1.12 - December 11, 2017

**Fixes**

- Ninja Forms double opt-in setting was incorrectly inversed.

**Improvements**

- Simplified form processing & notice logic.
- Prevent 404 errors by proactively replacing lowercased `name="name"` input attributes.
- Updated JavaScript dependencies.

**Additions**

- Integration for AffiliateWP.


#### 4.1.11 - November 2, 2017

**Fixes**

- Filter out empty array values when overriding selected Newsletter lists via `_nl4wp_lists`. 

**Improvements**

- Updated JavaScript dependencies.

**Additions**

- Link to the [HTML Forms](https://www.htmlforms.io/) from the plugin settings pages.


#### 4.1.10 - October 19, 2017

**Improvements**

- Remove unused options from Ninja Forms integration.

**Additions**

- Added Gravity Forms integration. You can now integrate with Gravity Forms by adding the "Newsletter" field to your forms.


#### 4.1.9 - September 19, 2017

**Improvements**

- Add `<label>` element to sign-up checkbox for WCAG compatibility.
- Custom integration now works with Enfold theme's contact form element.


#### 4.1.7 & 4.1.8 - September 8, 2017

**Fixes**

- Properly escape the return value of `add_query_arg` when it is used in HTML attributes to prevent cross-site scripting. Thanks to [Karim Ouerghemmi of RIPS](https://www.ripstech.com/) for responsibly disclosing.
- Now loading integrations after WPML so that String Translations work properly.

**Additions**

- Add sign-up integration for WPForms forms.

**Improvements**

- Updated internal JS dependencies.
- Form tag `{data key="foo.bar"}` now allows you to access nested array values.


#### 4.1.6 - July 31, 2017

**Fixes**

- Method on API class for retrieving campaign data.

**Improvements**

- Show Akamai reference number when an API request is blocked by Newsletter's firewall.
- Minor output buffering improvements in form previewer.


#### 4.1.5 - June 27, 2017

**Fixes**

- Failsafe against outputting sign-up checkbox twice in registration forms.
- Properly close HTML anchor element in French translation files.
- Fix BuddyPress sign-ups when using WordPress Multisite.

**Improvements**

- Fire action hook `nl4wp_form_updated_subscriber` whenever a form was used to update a subscriber in Newsletter.
- Increase browser timeout for AJAX request when fetching Newsletter lists.

**Additions**

- Added campaign & template methods to API client class.
 


#### 4.1.4 - June 15, 2017

**Fixes**

- Some form specific JS events were not firing due to incorrect event names.
- Registration form integration now works with WooCommerce registration form.
- Notice that asks for a plugin review would re-appear after dismissing it.


#### 4.1.3 - May 24, 2017

**Improvements**

- Randomise time of cron event that renews Newsletter lists.
- Always try to show Newsletter list info when API key is given.


#### 4.1.2 - May 8, 2017

**Fixes**

- Use earlier hook priority for Ninja Forms 3 integration so action is registered on time.

**Improvements**

- Improved Newsletter list fetching & memory usage for accounts with many lists.
- Show error message when fetching lists fails.
- Updated plugin translations.

#### 4.1.1 - April 11, 2017

**Fixes**

- WPML String Translation not working with the checkbox label for sign-up integrations.

**Improvements**

- Use updated order methods when using WooCommerce 3.0, thanks to Liam McArthur.
- Updated JavaScript dependencies.


#### 4.1.0 - March 14, 2017

**Improvements**

- Updated all JavaScript dependencies in the plugin.
- Failsafed filter hooks to prevent invalid variable types.
- Explain that greyed out integrations means that specific plugin is not activated.
- Conditional form elements now uses event delegation, so it works with forms in [Boxzilla pop-ups](https://boxzillaplugin.com/).
- Updated language files.

**Additions**

- Added support for Ninja Forms 3.
- Added `nl4wp_integration_show_checkbox` filter.


#### 4.0.13 - February 8, 2017

**Improvements**

- Ensure fields are HTML decoded before sending to Newsletter.
- Better OptimizePress compatibility.
- Show all address-type fields as required when form contains 1 or more fields of the same address group.


#### 4.0.12 - January 16, 2017

**Fixes**

- Don't call `stripslashes` on POST data twice.

**Improvements**

- Plugin review notice is now dismissible over AJAX.
- Improved formatting of birthday fields.
- Updated Polish translations, thanks to Mateusz Lomber.
- Updated German translations, thanks to Sven de Vries.

**Additions**

- Add `update_ecommerce_store_product` method to API class.
- Throw form specific JavaScript events, like `15.subscribed` to hook into "subscribed" events for form with ID 15.


#### 4.0.11 - December 9, 2016

**Fixes**

- Unescaped request variable on integration settings page, allowing for authenticated XSS. Thanks to [dxwsecurity](https://security.dxw.com/) for responsibly disclosing.


#### 4.0.10 - December 6, 2016

**Improvements**

- You can now enable or disable debug logging from the "Other" settings page.
- No longer using deprecated function in Contact Form 7, thanks to [stodorovic](https://github.com/stodorovic).
- Improved UI for adding hidden interest groupings fields to a form.


#### 4.0.9 - November 23, 2016

**Fixes**

- Issue with escaped HTML when using form tags introduced by previous update.


#### 4.0.8 - November 23, 2016

**Improvements**

- Improved handling of large debug logs.
- Improved error messages when writing exceptions to debug log.
- Show notice when form is missing required Newsletter fields.
- Custom form integration now handles arrays with 1-level depth. Thanks to [Mardari Igor](https://github.com/GarryOne).
- You can now use nested tags in your form code, eg `{data key="utm_source" default="{current_path}"}`

**Additions**

- Add `data-hide-if` attribute logic to forms. See conditionally hide form fields. Thanks to [Kurt Zenisek](http://kurtzenisek.com/).
- Add hooks for delayed BuddyPress sign-up. Thanks to [Christian Wach](https://profiles.wordpress.org/needle).


#### 4.0.7 - October 25, 2016

**Improvements**

- Obfuscate all email addresses in debug log. Thanks [Sauli Lepola](https://twitter.com/SJLfi).
- Ask for confirmation before disabling double opt-in, which we do not recommend.
- Allow vertical resizing of debug log.
- Failsafe against including JavaScript file twice.
- No longer wrapping CF7 checkbox in paragraph tags.

**Additions**

- Added `nl4wp_form_api_error` action hook for API errors encountered by forms.
- Added `element_class` argument to `[nl4wp_form]` shortcode for adding CSS classes.


#### 4.0.6 - October 10, 2016

**Fixes**

- Issue with lists not showing when using W3 Total Cache with APCu object cache enabled.

**Improvements**

- We're no longer stripping newlines from text fields.

**Additions**

- Added missing e-commerce related API methods to API class.


#### 4.0.5 - September 29, 2016

**Fixes**

- Allow checkbox option for the List Choice field (again).

**Improvements**

- Fetch Newsletter lists over AJAX, to speed up perceived performance (especially when your account has many lists).
- Periodically fetch Newsletter lists, so cache is always fresh.
- Improved `<label>` element accessibility for checkbox integrations.
- Stop using double underscore prefix in function names, as these are reserved in PHP 7.
- `{post}` and `{user}` shortcodes now accept a `default` parameter.

**Additions**

- Add [MemberPress](https://www.memberpress.com/) integration.


#### 4.0.4 - September 7, 2016

**Improvements**

- Allow re-running previous migrations by visiting a certain admin URL.
- Do not show checkboxes option for fields that only accept a single value.
- Write field specific errors to debug log when Newsletter denies a sign-up request.
- Write to debug log when custom integrations can not find an EMAIL field.
- Differentiate between connection & authorization errors when testing connection to Newsletter.
- Bump limit of number of Newsletter lists to fetch from 100 to 500.


#### 4.0.3 - August 24, 2016

**Fixes**

- Ninja Forms integration not working when using PayPal integration.

**Improvements**

- Show connection errors on Newsletter settings page.

**Additions**

- Add pre-checked option to Ninja Forms integration.
- You can now conditionally hide fields or elements using the `data-show-if` attribute.


#### 4.0.2 - August 10, 2016

**Fixes**

- Hidden fields which referenced interest groups by name were not sent to Newsletter.
- Adding hidden field to form would reset value on every change.

**Improvements**

- Decrease file size of JavaScript for forms by about 30%.


#### 4.0 & 4.0.1 - August 9, 2016

This release updates the plugin to version 3 of the Newsletter API.

**Changes**

- "Send welcome email" is now handled from your list settings in Newsletter.
- Filter `nl4wp_form_merge_vars` is now called `nl4wp_form_data`.
- Filter `nl4wp_integration_merge_vars` is now called `nl4wp_integration_data`.
- New format for GROUPING fields in forms & filter hooks.
- Value delimiter in hidden fields is now a pipe `|` character.

**Additions**

- New filter: `nl4wp_form_subscriber_data`.
- New filter: `nl4wp_integration_subscriber_data`.
- New form tag: `{cookie name="mycookie"}`

**Improvements**

- The plugin now communicates with the latest & greatest Newsletter API.
- Previously unsubscribed subscribers can now be re-added without errors.
- Add `User-Agent` header to all API requests.
- Available fields in form editor are now split-up by category.
- Birthday fields now accept a broader range of values and delimiters.

**Fixes**

- Issue with only 10 Newsletter lists / fields / interests being returned.
- Incorrect form message showing when double opt-in is disabled.
- Error in upgrade routine when API request fails.
- List fields not fetched when list has just 1 non-default merge field.



<?php
defined( 'ABSPATH' ) or exit;

/** @var NL4WP_Debug_Log $log */
/** @var NL4WP_Debug_Log_Reader $log_reader */

/**
 * @ignore
 * @param array $opts
 */
function _nl4wp_usage_tracking_setting( $opts ) {
	?>
	<div class="medium-margin" >
		<h3><?php _e( 'Miscellaneous settings', 'newsletter-for-wp' ); ?></h3>
		<table class="form-table">
<?php
/* NL_COMMENT - start
* Non mostra usage tracking settings
*/
/*
			<tr>
				<th><?php _e( 'Usage Tracking', 'newsletter-for-wp' ); ?></th>
				<td>
					<label>
						<input type="radio" name="nl4wp[allow_usage_tracking]" value="1" <?php checked( $opts['allow_usage_tracking'], 1 ); ?> />
						<?php _e( 'Yes' ); ?>
					</label> &nbsp;
					<label>
						<input type="radio" name="nl4wp[allow_usage_tracking]" value="0" <?php checked( $opts['allow_usage_tracking'], 0 ); ?>  />
						<?php _e( 'No' ); ?>
					</label>

					<p class="help">
						<?php echo __( 'Allow us to anonymously track how this plugin is used to help us make it better fit your needs.', 'newsletter-for-wp' ); ?>
						<a href="https://kb.mc4wp.com/what-is-usage-tracking/#utm_source=wp-plugin&utm_medium=newsletter-for-wp&utm_campaign=settings-page" target="_blank">
							<?php _e( 'This is what we track.', 'newsletter-for-wp' ); ?>
						</a>
					</p>
				</td>
			</tr>
*/
?>
			<tr>
				<th><?php _e( 'Logging', 'newsletter-for-wp' ); ?></th>
				<td>
					<select name="nl4wp[debug_log_level]">
						<option value="warning" <?php selected( 'warning', $opts['debug_log_level'] ); ?>><?php _e( 'Errors & warnings only', 'newsletter-for-wp' ); ?></option>
						<option value="debug" <?php selected( 'debug', $opts['debug_log_level'] ); ?>><?php _e( 'Everything', 'newsletter-for-wp' ); ?></option>
					</select>
					<p class="help">
						<?php printf( __( 'Determines what events should be written to <a href="%s">the debug log</a> (see below).', 'newsletter-for-wp' ), 'https://kb.mc4wp.com/how-to-enable-log-debugging/#utm_source=wp-plugin&utm_medium=newsletter-for-wp&utm_campaign=settings-page' ); ?>
					</p>
				</td>
			</tr>
		</table>
	</div>
	<?php
}

add_action( 'nl4wp_admin_other_settings', '_nl4wp_usage_tracking_setting', 70 );
?>
<div id="nl4wp-admin" class="wrap nl4wp-settings">

	<p class="breadcrumbs">
		<span class="prefix"><?php echo __( 'You are here: ', 'newsletter-for-wp' ); ?></span>
		<a href="<?php echo admin_url( 'admin.php?page=newsletter-for-wp' ); ?>">Newsletter for WordPress</a> &rsaquo;
		<span class="current-crumb"><strong><?php _e( 'Other Settings', 'newsletter-for-wp' ); ?></strong></span>
	</p>


	<div class="row">

		<!-- Main Content -->
		<div class="main-content col col-4">

			<h1 class="page-title">
				<?php _e( 'Other Settings', 'newsletter-for-wp' ); ?>
			</h1>

			<h2 style="display: none;"></h2>
			<?php settings_errors(); ?>

			<?php
			/**
			 * @ignore
			 */
			do_action( 'nl4wp_admin_before_other_settings', $opts );
			?>

			<!-- Settings -->
			<form action="<?php echo admin_url( 'options.php' ); ?>" method="post">
				<?php settings_fields( 'nl4wp_settings' ); ?>

				<?php
				/**
				 * @ignore
				 */
				do_action( 'nl4wp_admin_other_settings', $opts );
				?>

				<div style="margin-top: -20px;"><?php submit_button(); ?></div>
			</form>

			<!-- Debug Log -->
			<div class="medium-margin">
				<h3><?php _e( 'Debug Log', 'newsletter-for-wp' ); ?> <input type="text" id="debug-log-filter" class="alignright regular-text" placeholder="<?php esc_attr_e( 'Filter..', 'newsletter-for-wp' ); ?>" /></h3>

				<?php
				if( ! $log->test() ) {
					echo '<p>';
					echo __( 'Log file is not writable.', 'newsletter-for-wp' ) . ' ';
					echo  sprintf( __( 'Please ensure %s has the proper <a href="%s">file permissions</a>.', 'newsletter-for-wp' ), '<code>' . $log->file . '</code>', 'https://codex.wordpress.org/Changing_File_Permissions' );
					echo '</p>';

					// hack to hide filter input
					echo '<style type="text/css">#debug-log-filter { display: none; }</style>';
				} else {
					?>
					<div id="debug-log" class="nl4wp-log widefat">
						<?php
						$line = $log_reader->read_as_html();

						if (!empty($line)) {
							while( is_string( $line ) ) {
								echo '<div class="debug-log-line">' . $line . '</div>';
								$line = $log_reader->read_as_html();
							}
						} else {
							echo '<div class="debug-log-empty">';
							echo '-- ' . __('Nothing here. Which means there are no errors!', 'newsletter-for-wp');
							echo '</div>';
						}
						?>
					</div>

					<form method="post">
						<input type="hidden" name="_nl4wp_action" value="empty_debug_log">
						<p>
							<input type="submit" class="button"
								   value="<?php esc_attr_e('Empty Log', 'newsletter-for-wp'); ?>"/>
						</p>
					</form>
					<?php
				} // end if is writable

				if( $log->level >= 300 ) {
					echo '<p>';
					echo __( 'Right now, the plugin is configured to only log errors and warnings.', 'newsletter-for-wp' );
					echo '</p>';
				}
				?>

				<script type="text/javascript">
					(function() {
						'use strict';
						// scroll to bottom of log
						var log = document.getElementById("debug-log"),
							logItems;
						log.scrollTop = log.scrollHeight;
						log.style.minHeight = '';
						log.style.maxHeight = '';
						log.style.height = log.clientHeight + "px";

						// add filter
						var logFilter = document.getElementById('debug-log-filter');
						logFilter.addEventListener('keydown', function(e) {
							if(e.keyCode == 13 ) {
								searchLog(e.target.value.trim());
							}
						});

						// search log for query
						function searchLog(query) {
							if( ! logItems ) {
								logItems = [].map.call(log.children, function(node) {
									return node.cloneNode(true);
								})
							}

							var ri = new RegExp(query.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&"), 'i');
							var newLog = log.cloneNode();
							logItems.forEach(function(node) {
								if( ! node.textContent ) { return ; }
								if( ! query.length || ri.test(node.textContent) ) {
									newLog.appendChild(node);
								}
							});

							log.parentNode.replaceChild(newLog,log);
							log = newLog;
							log.scrollTop = log.scrollHeight;
						}
					})();
				</script>
			</div>
			<!-- / Debug Log -->



			<?php include dirname( __FILE__ ) . '/parts/admin-footer.php'; ?>
		</div>

		<!-- Sidebar -->
		<div class="sidebar col col-2">
			<?php include dirname( __FILE__ ) . '/parts/admin-sidebar.php'; ?>
		</div>


	</div>

</div>


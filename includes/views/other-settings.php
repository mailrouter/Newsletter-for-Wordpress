<?php
defined('ABSPATH') || exit;

/** @var array $opts */
/** @var NL4WP_Debug_Log $log */
/** @var NL4WP_Debug_Log_Reader $log_reader */

?>
<div id="nl4wp-admin" class="wrap nl4wp-settings">

    <p class="nl4wp-breadcrumbs">
        <span class="prefix"><?php echo esc_html__('You are here: ', 'newsletter-for-wp'); ?></span>
        <a href="<?php echo esc_url(admin_url('admin.php?page=newsletter-for-wp')); ?>">Newsletter for WordPress</a> &rsaquo;
        <span class="current-crumb"><strong><?php echo esc_html__('Other Settings', 'newsletter-for-wp'); ?></strong></span>
    </p>


    <div class="nl4wp-row">

        <?php /* main content */ ?>
        <div class="main-content nl4wp-col">

            <h1 class="nl4wp-page-title">
                <?php echo esc_html__('Other Settings', 'newsletter-for-wp'); ?>
            </h1>

            <h2 style="display: none;"></h2>
            <?php settings_errors(); ?>
            <?php do_action('nl4wp_admin_before_other_settings', $opts); ?>

            <form action="<?php echo esc_url(admin_url('options.php')); ?>" method="post">
                <?php settings_fields('nl4wp_settings'); ?>

                <div class="nl4wp-margin-m" >
                    <h3><?php echo esc_html__('Miscellaneous settings', 'newsletter-for-wp'); ?></h3>
                    <table class="form-table">
                        <tr>
                            <th><label for="nl4wp-debug-log-level"><?php echo esc_html__('Logging', 'newsletter-for-wp'); ?></label></th>
                            <td>
                                <select id="nl4wp-debug-log-level" name="nl4wp[debug_log_level]">
                                    <option value="warning" <?php selected('warning', $opts['debug_log_level']); ?>><?php echo esc_html__('Errors & warnings only', 'newsletter-for-wp'); ?></option>
                                    <option value="debug" <?php selected('debug', $opts['debug_log_level']); ?>><?php echo esc_html__('Everything', 'newsletter-for-wp'); ?></option>
                                </select>
                                <p class="description">
                                    <?php
                                    // translators: %s is a URL to the debug log documentation.
                                    printf(wp_kses(__('Determines what events should be written to the debug log (see below).', 'newsletter-for-wp'), [ 'a' => [ 'href' => [] ] ]));
                                    ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                    <table class="form-table">
                        <tr>
                            <th><label for="nl4wp-email-on-error"><?php echo esc_html__('Send email on critical error', 'newsletter-for-wp'); ?></label></th>
                            <td>
                                <input type="email" id="nl4wp-email-on-error" name="nl4wp[email_on_error]" value="<?php echo esc_attr($opts['email_on_error']); ?>" class="regular-text" placeholder="your@email.com" />
                                <p class="description">
                                    <?php echo esc_html__('Enter an email address to receive a notification when a critical error occurs. Max 1 email per 24 hours.', 'newsletter-for-wp'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
<?php do_action('nl4wp_admin_other_settings', $opts); ?>

                <div style="margin-top: -20px;"><?php submit_button(); ?></div>
            </form>

            <!-- Debug Log -->
            <div class="nl4wp-margin-m">
                <h3><?php echo esc_html__('Debug Log', 'newsletter-for-wp'); ?> <input type="text" id="debug-log-filter" class="alignright regular-text" placeholder="<?php echo esc_attr__('Filter..', 'newsletter-for-wp'); ?>" /></h3>

                <?php
                if (! $log->test()) {
                    echo '<p>';
                    echo esc_html__('Log file is not writable.', 'newsletter-for-wp') . ' ';
                    // translators: 1: the log file path, 2: URL to the WordPress file permissions documentation.
                    echo wp_kses(
                        sprintf(
                            /* translators: 1: log file path wrapped in code tags, 2: URL to WordPress file permissions docs. */
                            __('Please ensure %1$s has the proper <a href="%2$s">file permissions</a>.', 'newsletter-for-wp'),
                            '<code>' . esc_html($log->file) . '</code>',
                            esc_url('https://codex.wordpress.org/Changing_File_Permissions')
                        ),
                        [
                            'a'    => [ 'href' => [] ],
                            'code' => [],
                        ]
                    );
                    echo '</p>';

                    // hack to hide filter input
                    echo '<style>#debug-log-filter { display: none; }</style>';
                } else {
                    ?>
                    <div id="debug-log" class="nl4wp-log widefat">
                        <?php
                        $line = $log_reader->read_as_html();

                        if (! empty($line)) {
                            while (is_string($line)) {
                                if (! empty($line)) {
                                    echo '<div class="debug-log-line">' . wp_kses(
                                        $line,
                                        [
                                            'span' => [ 'class' => [] ],
                                        ]
                                    ) . '</div>';
                                }

                                $line = $log_reader->read_as_html();
                            }
                        } else {
                            echo '<div class="debug-log-empty">';
                            echo '-- ', esc_html__('Nothing here. Which means there are no errors!', 'newsletter-for-wp');
                            echo '</div>';
                        }
                        ?>
                    </div>

                    <form method="post">
                        <input type="hidden" name="_nl4wp_action" value="empty_debug_log">
                        <?php wp_nonce_field('_nl4wp_action', '_wpnonce'); ?>
                        <p>
                            <input type="submit" class="button" value="<?php echo esc_attr__('Empty Log', 'newsletter-for-wp'); ?>"/>
                        </p>
                    </form>
                    <?php
                } // end if is writable

                if ($log->level >= 300) {
                    echo '<p>';
                    echo esc_html__('Right now, the plugin is configured to only log errors and warnings.', 'newsletter-for-wp');
                    echo '</p>';
                }
                ?>
                <script>
                    (function() {
                        // scroll to bottom of log
                        let log = document.getElementById("debug-log"),
                            logItems;
                        log.scrollTop = log.scrollHeight;
                        log.style.minHeight = '';
                        log.style.maxHeight = '';
                        log.style.height = log.clientHeight + "px";

                        // add filter
                        document.getElementById('debug-log-filter').addEventListener('keydown', function(evt) {
                            if(evt.keyCode === 13 ) {
                                searchLog(evt.target.value.trim());
                            }
                        });

                        // search log for query
                        function searchLog(query) {
                            if( ! logItems ) {
                                logItems = [].map.call(log.children, function(node) {
                                    return node.cloneNode(true);
                                })
                            }

                            const ri = new RegExp(query.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&"), 'i');
                            const newLog = log.cloneNode(false);
                            logItems.forEach(function(node) {
                                if( ! node.textContent ) { return ; }
                                if( ! query.length || ri.test(node.textContent) ) {
                                    newLog.appendChild(node);
                                }
                            });

                            log.parentNode.replaceChild(newLog, log);
                            log = newLog;
                            log.scrollTop = log.scrollHeight;
                        }
                    })();
                </script>
            </div>
            <?php  ?>
        </div>
    </div>
</div>

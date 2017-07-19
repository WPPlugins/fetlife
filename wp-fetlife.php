<?php
/**
 * Plugin Name: WP-FetLife
 * Plugin URI: https://github.com/meitar/wp-fetlife
 * Description: Add widgets, shortcodes, and more to easily show your FetLife activity on your WordPress blog. <strong>Like this plugin? Please <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&amp;business=TJLPJYXHSRBEE&amp;lc=US&amp;item_name=WP-FetLife&amp;item_number=WP-FetLife&amp;currency_code=USD&amp;bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted" title="Send a donation to the developer of WP-FetLife">donate</a>. &hearts; Thank you!</strong>
 * Version: 0.1.1
 * Author: Meitar Moscovitz <meitar@maymay.net>
 * Author URI: http://maymay.net/
 * Text Domain: wp-fetlife
 * Domain Path: /languages
 */

class WP_FetLife_Plugin {
    private $prefix = 'wp_fetlife_';
    private $FL;                   //< FetLifeUser object
    private $fl_connected = false; //< Whether we are already logged in to FetLife on this invocation.

    public function __construct () {
        if (!class_exists('FetLife')) {
            require_once dirname(__FILE__) . '/lib/FetLife/FetLife.php';
        }
        require_once dirname(__FILE__) . '/widgets/widgets.php';

        add_action('plugins_loaded', array($this, 'registerL10n'));
        add_action('admin_init', array($this, 'registerSettings'));
        add_action('admin_menu', array($this, 'registerAdminMenu'));
        add_action('widgets_init', array($this, 'registerWidgets'));

        add_shortcode($this->prefix . 'widget', array($this, 'callWidgetFromShortcode'));

        $options = get_option($this->prefix . 'settings');
        if (!empty($options['fetlife_username']) && !empty($options['fetlife_password'])) {
            $this->FL = new FetLifeUser($options['fetlife_username'], $options['fetlife_password']);
            if (!empty($options['fetlife_proxyurl'])) {
                if ('auto' === $options['fetlife_proxyurl']) {
                    $this->FL->connection->setProxy('auto');
                } else { // use provided proxyurl value.
                    $p = parse_url($options['fetlife_proxyurl']);
                    $this->FL->connection->setProxy(
                        "{$p['host']}:{$p['port']}",
                        ('socks' === $p['scheme']) ? CURLPROXY_SOCKS5 : CURLPROXY_HTTP
                    );
                }
            }
        } else {
            add_action('admin_notices', array($this, 'showMissingConfigNotice'));
        }
    }

    public function callWidgetFromShortcode ($atts, $content = null) {
        $str = str_replace(' ', '_', ucwords(str_replace('_', ' ', strtolower($atts[0]))));
        $wdgt_class = 'WP_FetLife_' . $str . '_Widget';
        if (class_exists($wdgt_class)) {
            $wdgt = new $wdgt_class;
            $atts = array_merge($atts, shortcode_atts($wdgt->getDefaults(), $atts));
            $x = array();
            foreach ($atts as $k => $v) {
                if (is_int($k)) {
                    $x[$v] = 1;
                }
            }
            $instance = $wdgt->update(array_merge($atts, $x), array());
            ob_start();
            the_widget($wdgt_class, $instance);
            $output = ob_get_clean();
        } else {
            $output = esc_html__('WP FETLIFE SHORTCODE ERROR: No such widget', 'wp-fetlife');
            $output .= ' "' . esc_html($wdgt_class) . '"';
        }
        return $output;
    }

    public function registerL10n () {
        load_plugin_textdomain('wp-fetlife', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    public function registerSettings () {
        register_setting(
            $this->prefix . 'settings',
            $this->prefix . 'settings',
            array($this, 'validateSettings')
        );
    }

    public function registerAdminMenu () {
        add_options_page(
            __('WP-FetLife Settings', 'wp-fetlife'),
            __('WP-FetLife', 'wp-fetlife'),
            'manage_options',
            $this->prefix . 'settings',
            array($this, 'renderOptionsPage')
        );

        add_management_page(
            __('WP-FetLife Cache', 'wp-fetlife'),
            __('WP-FetLife Cache', 'wp-fetlife'),
            'manage_options',
            $this->prefix . 'cache',
            array($this, 'renderToolsPage')
        );
    }

    public function registerWidgets () {
        register_widget('WP_FetLife_Profile_Events_Widget');
        register_widget('WP_FetLife_Profile_Groups_Widget');
        register_widget('WP_FetLife_Event_Participants_Widget');
    }

    public function getPrefix () {
        return $this->prefix;
    }

    public function showMissingConfigNotice () {
        $screen = get_current_screen();
        if ($screen->base === 'plugins') {
?>
<div class="updated">
    <p><a href="<?php print admin_url('options-general.php?page=' . $this->prefix . 'settings');?>" class="button"><?php esc_html_e('Connect to FetLife', 'wp-fetlife');?></a> &mdash; <?php esc_html_e('Almost done! Connect your blog to FetLife.com to begin using WP-FetLife.', 'wp-fetlife');?></p>
</div>
<?php
        }
    } // end public function showMissingConfigNotice

    /**
     * Generic getter method that tests for the existence of a cached
     * copy of a requested object, and returns either that copy or a
     * regenerated copy if the cache has expired.
     *
     * @param string $fl_type The type of FetLife content object to retrieve.
     * @param string $fl_id The ID of the requested content.
     * @return mixed The requested object if successful, false on failure.
     */
    public function getFetLifeObject ($fl_type, $fl_id) {
        $options = get_option($this->prefix . 'settings');
        // First, check to see if we have a transient of that object.
        $transient = $this->getTransientName($fl_type, $fl_id, array($options['fetlife_username']));
        if (false === ($obj = get_transient($transient))) {
            // If we don't, fetch that object.
            $obj = $this->fetchFetLifeObject($fl_type, $fl_id);
            // And save a transient of it.
            set_transient($transient, $obj, 24 * HOUR_IN_SECONDS);
        }
        return $obj;
    }

    public function debugLog ($msg = '') {
        $msg = trim(strtoupper(str_replace('_', ' ', $this->prefix))) . ': ' . $msg;
        $options = get_option($this->prefix . 'settings');
        if (!empty($options['debug'])) {
            return error_log($msg);
        }
    }

    private function clearCachedTransients () {
        global $wpdb;
        return $wpdb->query($wpdb->prepare(
            "
            DELETE FROM {$wpdb->options}
            WHERE option_name LIKE '%s'
            ",
            '_transient_%' . $wpdb->esc_like($this->prefix) . '%'
        ));
    }

    /**
     * Generic data fetcher, like getFetLifeObject but performs the
     * actual fetching from FetLife.com.
     */
    private function fetchFetLifeObject ($fl_type, $fl_id) {
        if (!$this->fl_connected) {
            $this->debugLog('Not connected to FetLife. Reconnecting...');
            $s = $this->logInToFetLife();
            if (!$s) {
                throw new Exception(esc_html__('Failed to connect to FetLife.', 'wp-fetlife'));
            }
        }
        $obj = new stdClass();
        switch ($fl_type) {
            case 'profile':
                $obj = $this->FL->getUserProfile($fl_id);
                break;
            case 'event':
                $obj = $this->FL->getEventById($fl_id, true);
                break;
        }
        return $obj;
    }

    /**
     * Transients are caches from FetLife but the content FetLife returns might
     * be different depending on which user accessed the data. Therefore, we
     * name transients by hashing the accessor data that can result in varying
     * responses by passing those details in a third parameter as an array.
     *
     * @param string $obj_type The name of the class of content we're looking up.
     * @param string $id That content's ID value.
     * @param array $details Any additional information to include to uniquely identify the transient data.
     * @return string A string representing the transient's name, limited to 40 characters in length.
     */
    public function getTransientName ($obj_type, $id, $details = array()) {
        sort($details);
        return substr($this->prefix . hash('sha1', $obj_type . $id . http_build_query($details)), 0, 40);
    }

    private function logInToFetLife () {
        $msg = "Attempting log in to FetLife as {$this->FL->nickname} with password {$this->FL->password}";
        $this->debugLog($msg);
        $this->fl_connected = $this->FL->logIn();
        return $this->fl_connected;
    }

    private function showDonationAppeal () {
?>
<div class="donation-appeal">
    <p style="text-align: center; font-size: larger; width: 70%; margin: 0 auto;"><?php print sprintf(
esc_html__('WP-FetLife is provided as free software, but sadly grocery stores do not offer free food. If you like this plugin, please consider %1$s to its %2$s. &hearts; Thank you!', 'wp-fetlife'),
'<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&amp;business=TJLPJYXHSRBEE&amp;lc=US&amp;item_name=WP-FetLife&amp;item_number=WP-FetLife&amp;currency_code=USD&amp;bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted">' . esc_html__('making a donation', 'wp-fetlife') . '</a>',
'<a href="http://Cyberbusking.org/">' . esc_html__('houseless, jobless, nomadic developer', 'wp-fetlife') . '</a>'
);?></p>
</div>
<?php
    }

    /**
     * @param array $input An array of of our unsanitized options.
     * @return array An array of sanitized options.
     */
    public function validateSettings ($input) {
        $safe_input = array();
        foreach ($input as $k => $v) {
            switch ($k) {
                case 'fetlife_username':
                    if (empty($v)) {
                        $errmsg = __('FetLife username cannot be empty.', 'wp-fetlife');
                        add_settings_error($this->prefix . 'settings', 'empty-fetlife-username', $errmsg);
                    }
                    $safe_input[$k] = sanitize_text_field($v);
                break;
                case 'fetlife_password':
                    if (empty($v)) {
                        $errmsg = __('FetLife password cannot be empty.', 'wp-fetlife');
                        add_settings_error($this->prefix . 'settings', 'empty-fetlife-password', $errmsg);
                    }
                    $safe_input[$k] = sanitize_text_field($v);
                break;
                case 'fetlife_proxyurl':
                    if (false !== filter_var($v, FILTER_VALIDATE_URL) || 'auto' === $v || '' === $v) {
                        $safe_input[$k] = sanitize_text_field($v);
                    } else {
                        $errmsg = __('Unrecognized FetLife proxy URL value.', 'wp-fetlife');
                        add_settings_error($this->prefix . 'settings', 'invalid-fetlife-proxyurl', $errmsg);
                    }
                    break;
                case 'debug':
                    $safe_input[$k] = intval($v);
                    break;
            }
        }
        return $safe_input;
    }

    /**
     * Writes the HTML for the options page, and each setting, as needed.
     */
    // TODO: Add contextual help menu to this page.
    public function renderOptionsPage () {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wp-fetlife'));
        }
        $options = get_option($this->prefix . 'settings');
        if (!isset($options['fetlife_proxyurl'])) { $options['fetlife_proxyurl'] = ''; }
        if (isset($_GET['test-connection']) && wp_verify_nonce($_GET[$this->prefix . 'nonce'], 'test_fetlife_connection')) {
            $connected = $this->logInToFetLife();
        }
?>
<h2><?php esc_html_e('WP-FetLife Settings', 'wp-fetlife');?></h2>
<form method="post" action="options.php">
<?php settings_fields($this->prefix . 'settings');?>
<fieldset><legend><?php esc_html_e('Connection to FetLife', 'wp-fetlife');?></legend>
<table class="form-table" summary="<?php esc_attr_e('Required settings to connect to FetLife.', 'wp-fetlife');?>">
    <tbody>
        <?php if (!empty($options['fetlife_username']) && !empty($options['fetlife_password'])) { ?>
        <tr>
            <th>
                <label for="<?php esc_attr_e($this->prefix);?>test_connection"><?php esc_html_e('Test connection to FetLife', 'wp-fetlife');?></label>
            </th>
            <td>
                <?php if (empty($connected)) { ?>
                <p>
                    <a href="<?php print wp_nonce_url(admin_url('options-general.php?page=' . $this->prefix . 'settings&test-connection'), 'test_fetlife_connection', $this->prefix . 'nonce');?>" class="button"><?php esc_html_e('Test connection', 'wp-fetlife');?></a>
                    <span class="description"><?php esc_html_e('Test your connection settings.', 'wp-fetlife');?></span>
                </p>
                <?php } else { ?>
                <div class="updated">
                    <p><?php print sprintf(esc_html__('Connected to FetLife as "%s".', 'wp-fetlife'), $options['fetlife_username']);?></p>
                    <span class="description"><?php esc_html_e('Success! Your connection to FetLife is functional.', 'wp-fetlife');?></span>
                </div>
                <?php } ?>
            </td>
        </tr>
        <?php } ?>
        <tr>
            <th>
                <label for="<?php esc_attr_e($this->prefix);?>fetlife_username"><?php esc_html_e('FetLife username', 'wp-fetlife');?></label>
            </th>
            <td>
                <input id="<?php esc_attr_e($this->prefix);?>fetlife_username" name="<?php esc_attr_e($this->prefix);?>settings[fetlife_username]" value="<?php esc_attr_e($options['fetlife_username']);?>" placeholder="<?php esc_attr_e('Type your FetLife username here', 'wp-fetlife');?>" required="required" />
                <p class="description">
                    <?php esc_html_e('Your FetLife username is also sometimes called your nickname. If you do not have a FetLife username, create one by registering an account on FetLife.com.', 'wp-fetlife');?>
                </p>
            </td>
        </tr>
        <tr>
            <th>
                <label for="<?php esc_attr_e($this->prefix);?>fetlife_password"><?php esc_html_e('FetLife password', 'wp-fetlife');?></label>
            </th>
            <td>
                <input id="<?php esc_attr_e($this->prefix);?>fetlife_password" name="<?php esc_attr_e($this->prefix);?>settings[fetlife_password]" value="<?php esc_attr_e($options['fetlife_password']);?>" placeholder="<?php esc_attr_e('Type your FetLife password here', 'wp-fetlife');?>" type="password" required="required" />
                <p class="description">
                    <?php esc_html_e('Your FetLife password is needed to retrieve your activity from FetLife. Never share this password with anyone.', 'wp-fetlife');?>
                </p>
            </td>
        </tr>
        <tr>
            <th>
                <label for="<?php esc_attr_e($this->prefix);?>fetlife_proxyurl"><?php esc_html_e('Proxy URL', 'wp-fetlife');?></label>
            </th>
            <td>
                <input id="<?php esc_attr_e($this->prefix);?>fetlife_proxyurl" name="<?php esc_attr_e($this->prefix);?>settings[fetlife_proxyurl]" value="<?php esc_attr_e($options['fetlife_proxyurl']);?>" placeholder="<?php esc_attr_e('socks://127.0.0.1:9050', 'wp-fetlife');?>" />
                <p class="description">
                    <?php esc_html_e('A proxy is a middle-man through which you can reach FetLife. This might be necessary in case direct connections to FetLife.com do not work. Leave this blank to connect directly, without using a proxy.', 'wp-fetlife');?>
                    <?php print sprintf(
                        esc_html__('Use the special value %sauto%s to automatically select a proxy each time a connection is attempted.', 'wp-fetlife'),
                        '<kbd>', '</kbd>'
                    );?>
                </p>
            </td>
        </tr>
    </tbody>
</table>
</fieldset>
        <?php if (!empty($options['fetlife_username']) && !empty($options['fetlife_password'])) { ?>
<fieldset><legend><?php esc_html_e('WP-FetLife defaults', 'wp-fetlife');?></legend>
<table class="form-table" summary="<?php esc_attr_e('Options for setting default behaviors.', 'wp-fetlife');?>">
    <tbody>
        <tr>
            <th>
                <label for="<?php esc_attr_e($this->prefix);?>debug">
                    <?php esc_html_e('Enable detailed debugging information?', 'wp-fetlife');?>
                </label>
            </th>
            <td>
                <input type="checkbox" id="<?php esc_attr_e($this->prefix);?>debug" name="<?php esc_attr_e($this->prefix);?>settings[debug]" value="1" <?php if (isset($options['debug'])) { checked($options['debug'], 1); } ?> />
                <label for="<?php esc_attr_e($this->prefix);?>debug"><span class="description"><?php
        print sprintf(
            esc_html__('Turn this on only if you are experiencing problems using this plugin, or if you were told to do so by someone helping you fix a problem (or if you really know what you are doing). When enabled, extremely detailed technical information is displayed as a WordPress admin notice when you take certain actions. If you have also enabled WordPress\'s built-in debugging (%1$s) and debug log (%2$s) feature, additional information will be sent to a log file (%3$s). This file may contain sensitive information, so turn this off and erase the debug log file when you have resolved the issue.', 'wp-fetlife'),
            '<a href="https://codex.wordpress.org/Debugging_in_WordPress#WP_DEBUG"><code>WP_DEBUG</code></a>',
            '<a href="https://codex.wordpress.org/Debugging_in_WordPress#WP_DEBUG_LOG"><code>WP_DEBUG_LOG</code></a>',
            '<code>' . content_url() . '/debug.log' . '</code>'
        );
                ?></span></label>
            </td>
        </tr>
    </tbody>
</table>
</fieldset>
        <?php } ?>
<?php submit_button();?>
</form>
<?php
        $this->showDonationAppeal();
    } // end public function renderOptionsPage

    /**
     * Writes the HTML for the tools page, and each setting, as needed.
     */
    // TODO: Add contextual help menu to this page.
    public function renderToolsPage () {
        $options = get_option($this->prefix . 'settings');
        if (isset($_GET[$this->prefix . 'nonce']) && wp_verify_nonce($_GET[$this->prefix . 'nonce'], 'clear_cache')) {
            $results = $this->clearCachedTransients();
            if (false !== $results) {
?>
<div class="updated">
    <p><?php esc_html_e('Plugin cache has been cleared.', 'wp-fetlife');?></p>
    <p class="description"><?php print esc_html(sprintf(_n('1 transient cleared.', '%s transients deleted.', $results, 'wp-fetlife'), $results));?></p>
</div>
<?php
            } else {
?>
<div class="error">
    <p><?php esc_html_e('There was an error clearing the plugin cache.', 'wp-fetlife');?></p>
</div>
<?php
            }
        }
?>
<h2><?php esc_html_e('Clear WP-FetLife Cache', 'wp-fetlife');?></h2>
<p><?php esc_html_e('If you regularly experience problems with WP-FetLife widgets, shortcodes, or other features, clearing the plugin cache may help fix the issue. Clearing the plugin cache may significantly slow down your website until the caches are refreshed. If you are experiencing problems with a particular widget or shortcode, try disabling the cache for that widget or shortcode only, instead of clearing all WP-FetLife cache.', 'wp-fetlife');?></p>
<p><a href="<?php print wp_nonce_url(admin_url('tools.php?page=' . $this->prefix . 'cache'), 'clear_cache', $this->prefix . 'nonce');?>" class="button button-primary"><?php esc_html_e('Clear plugin cache', 'wp-fetlife');?></a></p>
<p class="description"><?php esc_html_e('When you clear the plugin cache, all copies of FetLife data this plugin accessed and currently stores in your database will be deleted. This operation cannot be undone. Plugin settings, such as your FetLife.com connection information and plugin-wide defaults, will not be touched.', 'wp-fetlife');?></p>
<?php
        $this->showDonationAppeal();
    } // end public function renderToolsPage
}

$wp_fetlife = new WP_FetLife_Plugin();

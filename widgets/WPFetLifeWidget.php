<?php
/**
 * Base class for all this plugin's widgets.
 */
abstract class WP_FetLife_Widget extends WP_Widget {
    protected $wdgt;     //< The invoking WP_Widget object instance.
    protected $args;     //< The arguments passed to the invoked WP_Widget.
    protected $instance; //< The instance variables passed to the invoked WP_Widget.
    protected $plugin; //< The associated plugin class, to access its public methods.
    protected static $base_id = 'wp_fl_wdgt_';

    private $defaults = array( //< Array of default values for a widget instance.
        'title' => '',
        'length' => 5,
        'clear_cache' => false,
        'fl_id' => ''
    );

    public function __construct($id, $name, $args) {
        global $wp_fetlife;
        $this->plugin = $wp_fetlife;
        parent::__construct($id, $name, $args);
    }

    public function getDefaults () {
        return $this->defaults;
    }

    public function widget ($args, $instance) {
        print $args['before_widget'];

        // For child classes.
        $this->wdgt     = $this;
        $this->args     = $args;
        $this->instance = $instance;

        try {
            if (empty($instance['fl_id'])) {
                $this->error(esc_html__('No FetLife object ID present in widget.', 'wp-fetlife'));
            }
        } catch (Exception $e) {
            print esc_html($e->getMessage());
            return false;
        }

        if (!empty($instance['title'])) {
            print $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        try {
            $obj = $this->plugin->getFetLifeObject($this->getType(), $instance['fl_id']);
            $items = array_slice($this->getItems($obj), 0, $instance['length']);
            print '<ol>';
            foreach ($items as $item) {
                $this->displayItem($item);
            }
            print '</ol>';
        } catch (Exception $e) {
            print esc_html_e($e->getMessage());
        }

        print $args['after_widget'];
    }

    /**
     * Simple wrapper to give us a bit more info when an error occurs.
     *
     * @param string $msg An error description to append to the exception message.
     * @throws Exception
     * @return void
     */
    private function error ($msg) {
        throw new Exception("[{$this->wdgt->name} Widget#{$this->args['widget_id']}] ERROR: " .  $msg);
    }

    public function update ($new_instance, $old_instance) {
        // Validate new values.
        $safe_instance = array();
        foreach ($new_instance as $k => $v) {
            switch ($k) {
                case 'fl_id':
                case 'title':
                    $safe_instance[$k] = (!empty($v)) ? strip_tags($v): '';
                    break;
                case 'clear_cache':
                case 'length':
                    $safe_instance[$k] = intval($v);
                default: // We only validate defaults here, pass the rest through.
                    $safe_instance[$k] = $v;
            }
        }

        // Act on any important changes.
        $options = get_option($this->plugin->getPrefix() . 'settings');
        if (!empty($safe_instance['clear_cache'])) {
            $this->plugin->debugLog('Clearing cache for widget ' . $this->id);
            $safe_instance['clear_cache_success'] = delete_transient(
                $this->plugin->getTransientName($this->getType(), $safe_instance['fl_id'], array($options['fetlife_username']))
            );
        }
        return $safe_instance;
    }

    public function form ($instance) {
        foreach ($this->defaults as $k => $v) {
            if (empty($instance[$k])) {
                $instance[$k] = $v;
            }
        }
        return $instance;
    }

    protected function getWidgetHeader ($instance) {
        ob_start();
?>
<p>
    <label for="<?php esc_attr_e($this->get_field_id('title'));?>">
        <?php esc_html_e('Title:', 'wp-fetlife');?>
        <span class="description"><?php esc_html_e('The widget title will be shown headlining its contents.', 'wp-fetlife');?></span>
    </label>
    <input class="widefat" id="<?php esc_attr_e($this->get_field_id('title'));?>" name="<?php esc_attr_e($this->get_field_name('title'));?>" value="<?php esc_attr_e($instance['title']);?>" />
</p>
<?php
        $str = ob_get_contents();
        ob_end_clean();
        return $str;
    }

    protected function getWidgetFooter ($instance) {
        ob_start();
?>
<p>
    <label for="<?php esc_attr_e($this->get_field_id('length'));?>">
        <?php esc_html_e('Number of items to show:', 'wp-fetlife');?>
    </label>
    <input id="<?php esc_attr_e($this->get_field_id('length'));?>" name="<?php esc_attr_e($this->get_field_name('length'));?>" size="3" value="<?php esc_attr_e($instance['length']);?>" />
</p>
<p>
    <input class="checkbox" id="<?php esc_attr_e($this->get_field_id('clear_cache'));?>" name="<?php esc_attr_e($this->get_field_name('clear_cache'));?>" type="checkbox" value="1" <?php // never checked by default in UI ?>/>
    <label for="<?php esc_attr_e($this->get_field_id('clear_cache'));?>">
        <?php esc_html_e('Clear cache', 'wp-fetlife');?>
        <span class="description"><?php esc_html_e('(next time this widget loads will be slower)', 'wp-fetlife');?></span>
    </label>
</p>
<?php
        if (defined('DOING_AJAX') && !empty($instance['clear_cache'])) {
            ($instance['clear_cache_success']) ? $this->showClearCacheStatus(true) : $this->showClearCacheStatus(false);
        }
        $str = ob_get_contents();
        ob_end_clean();
        return $str;
    }

    private function showClearCacheStatus ($success_or_failure) {
        $msg = ($success_or_failure)
            ? esc_html__('Widget cache successfully cleared.', 'wp-fetlife')
            : esc_html__('Widget cache could not be cleared. (Did you already clear it?)', 'wp-fetlife');
        print '<div class="' . (($success_or_failure) ? 'updated' : 'error') . '"';
        print "<p>$msg</p>";
        print '</div>';
    }

    abstract protected function getType ();
    abstract protected function getItems ($obj);
    abstract protected function displayItem ($item);
}

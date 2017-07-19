<?php
class WP_FetLife_Profile_Events_Widget extends WP_FetLife_Profile_Widget {
    public function __construct () {
        parent::__construct(
            self::$base_id . 'events',
            esc_html__('FetLife Profile - Events', 'wp-fetlife'),
            array(
                'description' => esc_html__('Displays upcoming FetLife events shown on a FetLife profile page in a list.', 'wp-fetlife')
            )
        );
    }

    protected function getItems ($profile) {
        $events = array();
        if (isset($this->instance['show_events_organizing'])) {
            $events = $profile->getEventsOrganizing();
        }
        if (isset($this->instance['show_events_going'])) {
            $events = array_merge($events, $profile->getEventsGoingTo());
        }
        if (isset($this->instance['show_events_maybe_going'])) {
            $events = array_merge($events, $profile->getEventsMaybeGoingTo());
        }
        return $events;
    }

    public function update ($new_instance, $old_instance) {
        // Process defaults.
        $new_instance = parent::update($new_instance, $old_instance);

        // Validate new values.
        $safe_instance = array();
        foreach ($new_instance as $k => $v) {
            switch ($k) {
                case 'show_events_organizing':
                case 'show_events_going':
                case 'show_events_maybe_going':
                    $safe_instance[$k] = intval($v);
                    break;
                default: // We only validate defaults here, pass the rest through.
                    $safe_instance[$k] = $v;
            }
        }
        return $safe_instance;
    }

    public function form ($instance) {
        ob_start(); // The parent's form() method also outputs a few fields.
        $instance = parent::form($instance);
        $str = ob_get_contents();
        ob_end_clean();
        print parent::getWidgetHeader($instance);
        print $str;
?>
<fieldset>
    <legend><?php esc_html_e('Events to show:', 'wp-fetlife');?></legend>
    <ul>
        <li>
            <input id="<?php esc_attr_e($this->get_field_id('show_events_organizing'));?>" name="<?php esc_attr_e($this->get_field_name('show_events_organizing'));?>" type="checkbox" value="1" <?php if (isset($instance['show_events_organizing'])) { checked($instance['show_events_organizing'], 1);}?>/>
            <label for="<?php esc_attr_e($this->get_field_id('show_events_organizing'), 'wp-fetlife');?>">
                <?php esc_html_e('Show events organizing', 'wp-fetlife');?>
            </label>
        </li>
        <li>
            <input id="<?php esc_attr_e($this->get_field_id('show_events_going'));?>" name="<?php esc_attr_e($this->get_field_name('show_events_going'));?>" type="checkbox" value="1" <?php if (isset($instance['show_events_going'])) { checked($instance['show_events_going'], 1);}?>/>
            <label for="<?php esc_attr_e($this->get_field_id('show_events_going'), 'wp-fetlife');?>">
                <?php esc_html_e('Show events going to', 'wp-fetlife');?>
            </label>
        </li>
        <li>
            <input id="<?php esc_attr_e($this->get_field_id('show_events_maybe_going'));?>" name="<?php esc_attr_e($this->get_field_name('show_events_maybe_going'));?>" type="checkbox" value="1" <?php if (isset($instance['show_events_maybe_going'])) { checked($instance['show_events_maybe_going'], 1);}?>/>
            <label for="<?php esc_attr_e($this->get_field_id('show_events_maybe_going'), 'wp-fetlife');?>">
                <?php esc_html_e('Show events maybe going to', 'wp-fetlife');?>
            </label>
        </li>
    </ul>
</fieldset>
<?php
        print parent::getWidgetFooter($instance);
    }
}

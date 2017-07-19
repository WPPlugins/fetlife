<?php
class WP_FetLife_Event_Participants_Widget extends WP_FetLife_Event_Widget {
    public function __construct () {
        parent::__construct(
            self::$base_id . 'participants',
            esc_html__('FetLife Event - Participants', 'wp-fetlife'),
            array(
                'description' => esc_html__('Displays the RSVPs for a FetLife event in a list.', 'wp-fetlife')
            )
        );
    }

    protected function getItems ($event) {
        $participants = array();
        if (isset($this->instance['show_organizer'])) {
            $participants[] = $event->creator;
        }
        if (isset($this->instance['show_going'])) {
            $participants = array_merge($participants, $event->going);
        }
        if (isset($this->instance['show_maybe_going'])) {
            $participants = array_merge($participants, $event->maybegoing);
        }
        return $participants;
    }

    protected function displayItem ($item) {
        print '<li><a href="' . $item->getPermalink() . '">';
        print '<img src="' . $item->avatar_url . '" alt=""/>';
        print  esc_html($item->nickname);
        print '</a></li>';
    }

    public function update ($new_instance, $old_instance) {
        // Process defaults.
        $new_instance = parent::update($new_instance, $old_instance);

        // Validate new values.
        $safe_instance = array();
        foreach ($new_instance as $k => $v) {
            switch ($k) {
                case 'show_organizer':
                case 'show_going':
                case 'show_maybe_going':
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
    <legend><?php esc_html_e('RSVPs to show:', 'wp-fetlife');?></legend>
    <ul>
        <li>
            <input id="<?php esc_attr_e($this->get_field_id('show_organizer'));?>" name="<?php esc_attr_e($this->get_field_name('show_organizer'));?>" type="checkbox" value="1" <?php if (isset($instance['show_organizer'])) { checked($instance['show_organizer'], 1);}?>/>
            <label for="<?php esc_attr_e($this->get_field_id('show_organizer'), 'wp-fetlife');?>">
                <?php esc_html_e('Show organizer', 'wp-fetlife');?>
            </label>
        </li>
        <li>
            <input id="<?php esc_attr_e($this->get_field_id('show_going'));?>" name="<?php esc_attr_e($this->get_field_name('show_going'));?>" type="checkbox" value="1" <?php if (isset($instance['show_going'])) { checked($instance['show_going'], 1);}?>/>
            <label for="<?php esc_attr_e($this->get_field_id('show_going'), 'wp-fetlife');?>">
                <?php esc_html_e('Show "going" RSVPs', 'wp-fetlife');?>
            </label>
        </li>
        <li>
            <input id="<?php esc_attr_e($this->get_field_id('show_maybe_going'));?>" name="<?php esc_attr_e($this->get_field_name('show_maybe_going'));?>" type="checkbox" value="1" <?php if (isset($instance['show_maybe_going'])) { checked($instance['show_maybe_going'], 1);}?>/>
            <label for="<?php esc_attr_e($this->get_field_id('show_maybe_going'), 'wp-fetlife');?>">
                <?php esc_html_e('Show "maybe going" RSVPs', 'wp-fetlife');?>
            </label>
        </li>
    </ul>
</fieldset>
<?php
        print parent::getWidgetFooter($instance);
    }
}

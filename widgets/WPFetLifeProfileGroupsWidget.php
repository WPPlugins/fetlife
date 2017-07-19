<?php
class WP_FetLife_Profile_Groups_Widget extends WP_FetLife_Profile_Widget {
    public function __construct () {
        parent::__construct(
            parent::$base_id . 'groups',
            esc_html__('FetLife Profile - Groups', 'wp-fetlife'),
            array(
                'description' => esc_html__('Displays the FetLife Groups a user belongs to in a list.', 'wp-fetlife')
            )
        );
    }

    protected function getItems ($profile) {
        $groups = array();
        if (isset($this->instance['show_only_groups_lead'])) {
            $groups = $profile->getGroupsLead();
        } else {
            $groups = $profile->getGroups();
        }
        return $groups;
    }

    public function update ($new_instance, $old_instance) {
        // Process defaults.
        $new_instance = parent::update($new_instance, $old_instance);

        // Validate new values.
        $safe_instance = array();
        foreach ($new_instance as $k => $v) {
            switch ($k) {
                case 'show_only_groups_lead':
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
<p>
    <input id="<?php esc_attr_e($this->get_field_id('show_only_groups_lead'));?>" name="<?php esc_attr_e($this->get_field_name('show_only_groups_lead'));?>" type="checkbox" value="1" <?php if (isset($instance['show_only_groups_lead'])) { checked($instance['show_only_groups_lead'], 1);}?>/>
    <label for="<?php esc_attr_e($this->get_field_id('show_only_groups_lead'));?>">
        <?php esc_html_e('Show only groups I lead', 'wp-fetlife');?>
    </label>
</p>
<?php
        print parent::getWidgetFooter($instance);
    }
}

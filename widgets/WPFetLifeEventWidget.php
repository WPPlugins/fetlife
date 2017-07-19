<?php
/**
 * Class for all widgets that read information from a given FetLifeEvent.
 */
abstract class WP_FetLife_Event_Widget extends WP_FetLife_Widget {
    protected function getType () {
        return 'event';
    }

    public function form ($instance) {
        $instance = parent::form($instance);
?>
<p>
    <label for="<?php esc_attr_e($this->get_field_id('fl_id'));?>">
        <?php esc_html_e('FetLife event ID:', 'wp-fetlife');?>
        <span class="description"><?php esc_attr_e('The ID number of a FetLife Event.', 'wp-fetlife');?></span>
    </label>
    <input class="widefat" id="<?php esc_attr_e($this->get_field_id('fl_id'));?>" name="<?php esc_attr_e($this->get_field_name('fl_id'));?>" value="<?php esc_attr_e($instance['fl_id']);?>" required="required" />
</p>
<?php
        return $instance;
    }
}

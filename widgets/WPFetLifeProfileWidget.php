<?php
/**
 * Class for all widgets that read information from a user's FetLifeProfile.
 */
abstract class WP_FetLife_Profile_Widget extends WP_FetLife_Widget {
    protected function getType () {
        return 'profile';
    }

    public function form ($instance) {
        $instance = parent::form($instance);
?>
<p>
    <label for="<?php esc_attr_e($this->get_field_id('fl_id'));?>">
        <?php esc_html_e('FetLife user ID:', 'wp-fetlife');?>
        <span class="description"><?php esc_attr_e('The user ID number of a FetLife user.', 'wp-fetlife');?></span>
    </label>
    <input class="widefat" id="<?php esc_attr_e($this->get_field_id('fl_id'));?>" name="<?php esc_attr_e($this->get_field_name('fl_id'));?>" value="<?php esc_attr_e($instance['fl_id']);?>" required="required" />
</p>
<?php
        return $instance;
    }

    protected function displayItem ($item) {
        print '<li><a href="' . $item->getPermalink() . '">' . esc_html($item->name) . '</a></li>';
    }
}

<?php
/*
Plugin Name: HD Extensible Social Profiles Widget
Plugin URI: https://github.com/TrizahNabwire
Description: Adds the ability to add social profiles to a site and output them as a widget.
Version: 1.0.0
Author: Trizah Nabwire
AUthor URL: https://github.com/TrizahNabwire
License: GNU General Public License v2 or later
Text Domain: hd-extensible-social-profiles-widget

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, 
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the 
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free SOftware
Foundation, Int., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
*/

/* exit/die if directly accessed */
if(! defined('ABSPATH')){
    exit;
}

//define variable for path to this plugin file.
define('HD_ESPW_LOCATION', dirname(__FILE__));
define('HD_ESPW_LOCATION_URL',plugins_url('', __FILE__));

/**
 * Get the registered social profiles
 * 
 * @return array An array of registered social profiles.
 */
function hd_espw_get_social_profiles(){
    //return a filterable social profiles.
    return apply_filters(
        'hd_espw_social_profiles',
        array()
    );
}

/**
 * Registers the default social profiles.
 * 
 * @param array $profiles An array of the current registered social profiles.
 * @return array          The modified array of socila profiles.
 */

 function hd_espw_register_default_social_profiles($profiles){
    //add the facebook profile.
    $profiles['facebook'] = array(
        'id'               => 'hd_espw_facebook_url',
        'label'            => __('Facebook URL', 'hd_extensible-social-profiles-widget'),
        'class'            => 'facebook',
        'description'      => __('Enter your Facebook profile URL', 'hd_extensible-social-profiles-widget'),
        'priority'         => 10,
        'type'             => 'text',
        'default'          => '',
        'sanitize_callback'=> 'sanitize_text_field',
    );

    //add the linkedin profile
    $profiles['linkedin'] = array(
        'id'               => 'hd_espw_linkedin_url',
        'label'            => __('LinkedIn URL', 'hd_extensible-social-profiles-widget'),
        'class'            => 'linkedin',
        'description'      => __('Enter your LinkedIn profile URL', 'hd_extensible-social-profiles-widget'),
        'priority'         => 20,
        'type'             => 'text',
        'default'          => '',
        'sanitize_callback'=> 'sanitize_text_field',
    );

    //add the twitter profile
    $profiles['twitter'] = array(
        'id'               => 'hd_espw_twitter_url',
        'label'            => __('Twitter URL', 'hd_extensible-social-profiles-widget'),
        'class'            => 'twitter',
        'description'      => __('Enter your Twitter profile URL', 'hd_extensible-social-profiles-widget'),
        'priority'         => 40,
        'type'             => 'text',
        'default'          => '',
        'sanitize_callback'=> 'sanitize_text_field',
    );

    //return the modified profiles.
    return $profiles;
 }

 add_filter('hd_espw_social_profiles', 'hd_espw_register_default_social_profiles', 10, 1);

 /**
  * Registers the social profiles with the customizer in WordPress.
  *
  *@param WP_Customizer $wp_customizer The customizer object.
  */

  function hd_espw_register_social_customizer_settings($wp_customize){

    //get the social profiles
    $social_profiles = hd_espw_get_social_profiles();

    //if we have any social profiles.
    if(! empty($social_profiles)){

        //register the customizer section for social profiles.
        $wp_customize->add_section(
            'hd_espw_social',
            array(
                'title'         => __('Social Profiles'),
                'description'   => __('Add social media profiles here.'),
                'priority'      => 160,
                'capability'    => 'edit_theme_options',
            )
            );

            //loop through each profile.
            foreach($social_profiles as $social_profile){

                //add the customizer setting for this profile
                $wp_customize->add_setting(
                    $social_profile['id'],
                    array(
                        'default'           => '',
                        'sanitize_callback' => $social_profile['sanitize_callback'],
                    )
                    );

                    //add the customizer control for this profile
                    $wp_customize->add_control(
                        $social_profile['id'],
                        array(
                            'type'          => $social_profile['type'],
                            'priority'      => $social_profile['priority'],
                            'section'       => 'hd_espw_social',
                            'label'         => $social_profile['label'],
                            'description'   => $social_profile['description'],
                        )
                        );
            }
    }
  }

  add_action('customize_register', 'hd_espw_register_social_customizer_settings');

  /**
   * Register the social icons widget with WordPress.
   */
  function hd_espw_register_social_icons_widget(){
    register_widget('HD_ESPW_Social_Icons_Widget');
  }
  add_action('widget_init', 'hd_espw_register_social_icons_widget');

  /**
   * Extend the widget class for our new social icons widget.
   */
  class HD_ESPW_Social_Icons_Widget extends WP_Widget{
    /**
     * Setup the widget
     */
    public function __construct()
    {
        /*Widget settings. */
        $widget_ops = array(
            'classname'       => 'hd-espw-social-icons',
            'description'     => __('Output your sites social icons, based on the social profiles added to customizer.','
                hd-extensible-social-profiles-widget'),
        );

        /* Widget control settings. */
        $control_ops = array(
            'id_base'        => 'hd_espw_social_icons',
        );

        /*Create the widget. */
        parent::__construct('hd_espw_Social_icons', 'Social Icons', $widget_ops, $control_ops);
        
    }
    /**
     * Output the widget front-end.
     */
    public function widget($args, $instance){

        //output the before widget content
        echo wp_kses_post($args['before_widget']);

        /**
         * Call an action which outputs the widget.
         * 
         * @param $args is an array of the widget arguments eg before_widget.
         * @param $instance is an array of the widget instances.
         * 
         * @hooked hd_espw_social_icons_output_widget_title.- 10
         * @hooked hd_espw_output_social_icons_widget_content - 20
         */
        do_action('hd_espw_social_icons_widget_output', $args, $instance);

        //output the after widget content.
        echo wp_kses_post($args['after_widget']);
    }

    /**
     * Output the backend widget form.
     */
    public function form($instance){

        //get the saved title.
        $title = ! empty($instance['title']) ? $instance['title'] : '';
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_attr_e('Title:' ,'
                 hd-extensible-social-profiles-widget');?></label>
                 <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title'));?>" name="<?php echo esc_attr($this->
                 get_field_name('title'));?>" type="text" value="<?php echo esc_attr($title); ?>">

        </p>
        <p>
        <?php
        printf(
            __('To add social profiles, please use the social profile section in the %1$customizer%2$s.','
            hd-extensible-social-profiles-widget'),
            '<a href="'. admin_url('customize.php').'">',
            '</a>'

        );
        ?>
        </p>
        <?php
    }

    /**
     * Controls the save function when the widget updates.
     * 
     * @param array $new_instance The newly saved widget instance.
     * @param array $old_instance The old widget instance.
     * @return array              The new instance to update.
     */
    public function update($new_instance, $old_instance)
    {
        
        //create an empty array to store new values in.
        $instance = array();

        //add the title to the array, stripping empty tags along the way.
        $instance['title'] = (! empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';

        //return the instance array to be saved.
        return $instance;
    }
  }

  /**
   * Output the widget title for the social icons widget.
   * 
   * @param array $args     An array of widget args.
   * @param array $instance The current instance of widget data.
   */
  function hd_espw_social_icons_output_widget_title($args, $instance){

    //if we have before widget content.
    if(! empty($instance['title'])){

        //if we have before title content.
        if(! empty($args['before_title'])){

            //output the before title content.
            echo wp_kses_post($args['before_title']);
        }

        //output the before widget content.
        echo esc_html($instance['title']);

        //if we have before widget content.
        if(! empty($args['after_title'])){

            //output the after title content.
            echo wp_kses_post($args['after_title']);
        }
    }
  }

  add_action('hd_espw_social_icons_widget_output', 'hd_espw_social_icons_output_widget');

  /**
   * Output the widget content for the social icons widget - the actual icons and links.
   * 
   * @param array $args       An array of widget args.
   * @param array $instance   The current instance of widget data
   */

   function hd_espw_output_social_icon_widget_content($args, $instance){

    //get the array of social profiles
    $social_profiles = hd_espw_get_social_profiles();

    //start the output markup
    ?>
    <ul class="hd-espw-social-icons">
    <?php

    //loop through each profile
    foreach($social_profiles as $social_profile){

        //get the value for this social profile - the profile url.
        $profile_url = get_theme_mod($social_profile['id']);

        //if we have a no vale - url.
        if(empty($profile_url)){
            continue; //continue to the next social profile.
        }

        //if we don't have a specified class.
        if(empty($social_profile['class'])){

            //use the label for form a class
            $social_profile['class'] = strtolower(sanitize_title_with_dashes($social_profile['label']));
        }
        //build the markup for this social profile.
        ?>
        <li class="hd-espw_social_icons__item hd-espw-social-icons__item--<?php echo esc_attr($social_profile['class']);?>">
           <a target="_blank" class="hd-espw-social-icons__item-link" href="<?php echo esc_url($profile_url); ?>">
           <i class="icon-<?php echo esc_attr($social_profile['class']);?>"></i><span><?php echo esc_html($social_profile['label']);?></span>

        </a>
    </li>
        <?php

    }
    //end the output markup.
    ?>
    </ul>
    <?php
   }

add_action('hd_espw_social_icons_widget_output', 'hd_espw_output_social_icons_widget');
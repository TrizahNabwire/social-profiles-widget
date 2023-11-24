
<?php
function test_remove_linkedin_social_profile($profiles){

    //if we have a linkedin profile
    if(! empty(empty($profiles['linkedin']))){
        //remove it
        unset($profile['linkedin']);
    }

     //add the github profile
     $profiles['github'] = array(
        'id'               => 'hd_espw_github_url',
        'label'            => __('Github URL', 'hd_extensible-social-profiles-widget'),
        'class'            => 'github',
        'description'      => __('Enter your Github profile URL', 'hd_extensible-social-profiles-widget'),
        'priority'         => 50,
        'type'             => 'text',
        'default'          => '',
        'sanitize_callback'=> 'sanitize_text_field',
    );

    //return profiles
    return $profiles;

}
add_filter('hd_espw_social_profiles', 'test_remove_linkedin_social_profile', 20, 1);

function test_remove_social_profiles_title(){
    remove_action('hd_espw_social_icons_widget_output', 'hd_espw_social_icons_output');
}
add_action('init', 'test_remove_social_profiles_title');
function test_add_custom_title($args, $instance){
    echo 'Title: '. $instance['title'];
}
add_action('hd_espw_social_icons_widget_output', 'test_add_custom_title', 10, 2);

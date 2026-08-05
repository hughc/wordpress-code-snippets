<?php

/**
 * Module: Disable Admin for Subscribers
 * Description: hide admin UI for non-admins
 */



function check_current_user_role($roles)
{
    /*@ Check user logged-in */
    if (is_user_logged_in()) :
        /*@ Get current logged-in user data */
        $user = wp_get_current_user();
        /*@ Fetch only roles */
        $currentUserRoles = $user->roles;
        /*@ Intersect both array to check any matching value */
        $isMatching = array_intersect($currentUserRoles, $roles);
        $response = false;
        /*@ If any role matched then return true */
        if (!empty($isMatching)) :
            $response = true;
        endif;
        return $response;
    endif;
}
$roles = ['customer', 'subscriber'];
if (check_current_user_role($roles)) :
    add_filter('show_admin_bar', '__return_false');
endif;


// Redirect subscriber accounts from dashboard to homepage
function redirect_subscriber_to_frontend()
{
    $current_user = wp_get_current_user();

    if (count($current_user->roles) == 1 && $current_user->roles[0] == 'subscriber') {
        wp_redirect(site_url('/'));
        exit;
    }
}

add_action('admin_init', 'redirect_subscriber_to_frontend');

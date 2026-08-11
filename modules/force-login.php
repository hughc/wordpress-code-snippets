<?php

/**
 * Module: No anonymous access
 * Description: Users must be logged in to access all URLs.
 */


add_action('template_redirect', 'disable_anonymous_access');
function disable_anonymous_access()
{
    // Check if the user is NOT logged in and is NOT on the login page
    if (! is_user_logged_in() && ! in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'))) {
        // Redirect to the login page and remember the URL they tried to visit
        auth_redirect();
    }
}

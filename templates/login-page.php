<?php
/* Template Name: Login Page */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <h1><?php _e('Login', 'textdomain'); ?></h1>
        <?php
        $args = array(
            'redirect' => site_url('/'), // Change this to the URL you want to redirect to
        );
        wp_login_form($args);
        ?>
    </main><!-- #main -->
</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>

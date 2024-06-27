<?php
/* Template Name: Registration Page */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <h1><?php _e('Register', 'textdomain'); ?></h1>
        <form method="post" action="<?php echo wp_registration_url(); ?>">
            <p>
                <label for="user_login"><?php _e('Username', 'textdomain'); ?></label>
                <input type="text" name="user_login" id="user_login" required>
            </p>
            <p>
                <label for="user_email"><?php _e('Email', 'textdomain'); ?></label>
                <input type="email" name="user_email" id="user_email" required>
            </p>
            <?php do_action('register_form'); ?>
            <p>
                <input type="submit" value="<?php _e('Register', 'textdomain'); ?>">
            </p>
        </form>
    </main><!-- #main -->
</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>

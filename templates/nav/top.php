<?php
/**
 * Top Navigation Menu.
 *
 * @author Usability Dynamics
 * @module wp-escalade
 * @since wp-escalade 0.1.0
 */
?>
<!-- Navigation bar starts -->
<div class="navbar navbar-inverse navbar-top <?php echo ( is_home() || is_front_page() ) ? 'navbar-top-home' : 'navbar-fixed-top'; ?>" role="navigation">
  <div class="container">
    <div class="social-wrap">
      <div class="no-sticky">
        <?php get_template_part( 'templates/aside/social', get_post_type() ); ?>
      </div>
      <div class="sticky">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".social-collapse"><span class="icon icon-plus"></span></button>
        <div class="social-collapse collapse"><?php get_template_part( 'templates/aside/social', get_post_type() ); ?></div>
      </div>
    </div>
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
        <span class="sr-only"><?php _e( 'Toggle navigation', wp_festival( 'domain' ) ); ?></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <span class="navbar-brand">
        <a class="logo sticky" href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><img class="img-responsive" src="<?php echo get_template_directory_uri(); ?>/images/temp/small-logo.png" /></a>
        <a class="btn btn-default" role="button" href="#"><?php _e( 'Buy Tickets', wp_festival( 'domain' ) ); ?></a>
      </span>
    </div>
    <nav class="collapse navbar-collapse bs-navbar-collapse" role="navigation">
      <?php echo wp_festival()->nav( 'primary', 2 ); ?>
    </nav>
  </div>
</div>
<!-- Navigation bar ends -->
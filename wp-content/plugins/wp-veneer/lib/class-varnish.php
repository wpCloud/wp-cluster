<?php
/**
 * Varnish Controller
 *
 * @module Cluster
 * @author potanin@UD
 */
namespace UsabilityDynamics\Veneer {

  if( !class_exists( 'UsabilityDynamics\Veneer\Varnish' ) ) {

    /**
     * Class Varnish
     *
     * Source from http://wordpress.org/extend/plugins/varnish-http-purge/
     *
     * @module Cluster
     */
    class Varnish {

      /**
       * Initialize Varnish
       *
       *
       * @for Varnish
       */
      protected $purgeUrls = array();

      public function __construct() {
        add_action( 'init', array( &$this, 'init' ) );

        // @legacy
        if( !defined( 'helf_vhp' ) ) {
          define( 'helf_vhp', 'helf_vhp' );
        }

        // add_action( 'admin_bar_menu', array( $this, 'varnish_links' ), 100 );
        // add_action( 'rightnow_end', array( $this, 'varnish_rightnow' ) );

      }

      public function init() {
        foreach( $this->getRegisterEvents() as $event ) {
          add_action( $event, array( $this, 'purgePost' ), 10, 2 );
        }

        add_action( 'shutdown', array( $this, 'executePurge' ) );

        if( isset( $_GET[ 'vhp_flush_all' ] ) && current_user_can( 'manage_options' ) && check_admin_referer( 'helf_vhp' ) ) {
          add_action( 'admin_notices', array( $this, 'purgeMessage' ) );
        }

        if( '' == get_option( 'permalink_structure' ) && current_user_can( 'manage_options' ) ) {
          // add_action( 'admin_notices', array( $this, 'prettyPermalinksMessage' ) );
        }

      }

      function purgeMessage() {
        echo "<div id='message' class='updated fade'><p><strong>" . __( 'Varnish purge flushed!', helf_vhp ) . "</strong></p></div>";
      }

      function prettyPermalinksMessage() {
        echo "<div id='message' class='error'><p>" . __( 'Varnish HTTP Purge requires you to use custom permalinks. Please go to the <a href="options-permalink.php">Permalinks Options Page</a> to configure them.', defined( 'helf_vhp' ) ? helf_vhp : null ) . "</p></div>";
      }

      function varnish_rightnow() {

        if( current_user_can( 'activate_plugins' ) ) {
          $url    = wp_nonce_url( admin_url( '?vhp_flush_all' ), 'helf_vhp' );
          $button = sprintf( __( '<p class="button"><a href="%1$s"><strong>Purge Varnish Cache</strong></a></p>', helf_vhp ), $url );
          echo "<p class='varnish-rightnow'>$button</p>\n";
        }

      }

      /**
       * For the not being used at this moment admin bar
       *
       */
      function varnish_links() {
        global $wp_admin_bar;
        if( !is_super_admin() || !is_admin_bar_showing() || !is_admin() )
          return;

        $url = wp_nonce_url( admin_url( '?vhp_flush_all' ), 'helf_vhp' );
        $wp_admin_bar->add_menu( array( 'id' => 'varnish_text', 'title' => __( 'Purge Varnish', helf_vhp ), 'href' => $url ) );
      }

      protected function getRegisterEvents() {
        return array(
          'save_post',
          'deleted_post',
          'trashed_post',
          'edit_post',
          'delete_attachment',
          'switch_theme',
          //'generate_rewrite_rules'
        );
      }

      public function executePurge() {
        $purgeUrls = array_unique( $this->purgeUrls );

        foreach( $purgeUrls as $url ) {
          @$this->purgeUrl( $url );
        }

        if( empty( $purgeUrls ) ) {
          if( isset( $_GET[ 'vhp_flush_all' ] ) && current_user_can( 'manage_options' ) && check_admin_referer( 'helf_vhp' ) ) {
            @$this->purgeUrl( home_url() . '/?vhp=regex' );
          }
        }
      }

      protected function purgeUrl( $url ) {
        // Parse the URL for proxy proxies
        $p = parse_url( $url );

        $pregex = '';

        if( isset( $p[ 'query' ] ) && $p[ 'query' ] == 'vhp=regex' ) {
          $pregex                = '.*';
          $varnish_x_purgemethod = 'regex';
        } else {
          $varnish_x_purgemethod = 'default';
        }

        // Build a varniship
        if( defined( 'WP_VENEER_VARNISH_IP' ) && WP_VENEER_VARNISH_IP ) {
          $varniship = WP_VENEER_VARNISH_IP;
        } else {
          $varniship = get_option( 'vhp_varnish_ip' );
          if( defined( 'WP_VENEER_VARNISH_IP'  ) ) {
            define( 'WP_VENEER_VARNISH_IP', $varniship );
          }
        }

        // If we made varniship, let it sail
        if( isset( $varniship ) ) {
          $purgeme = $p[ 'scheme' ] . '://' . $varniship . isset( $p[ 'path' ] ) ? $p[ 'path' ] : '' . $pregex;
        } else {
          $purgeme = $p[ 'scheme' ] . '://' . $p[ 'host' ] . isset( $p[ 'path' ] ) ? $p[ 'path' ] : '' . $pregex;
        }

        // Cleanup CURL functions to be wp_remote_request and thus better
        // http://wordpress.org/support/topic/incompatability-with-editorial-calendar-plugin
        wp_remote_request( $purgeme, array( 'method' => 'PURGE', 'headers' => array( 'host' => $p[ 'host' ], 'X-Purge-Method' => $varnish_x_purgemethod ) ) );
      }

      public function purgePost( $postId ) {

        // If this is a valid post we want to purge the post, the home page and any associated tags & cats
        // If not, purge everything on the site.

        $validPostStatus = array( "publish", "trash" );
        $thisPostStatus  = get_post_status( $postId );

        if( get_permalink( $postId ) == true && in_array( $thisPostStatus, $validPostStatus ) ) {
          // Category & Tag purge based on Donnacha's work in WP Super Cache
          $categories = get_the_category( $postId );
          if( $categories ) {
            $category_base = get_option( 'category_base' );
            if( $category_base == '' )
              $category_base = '/category/';
            $category_base = trailingslashit( $category_base );
            foreach( $categories as $cat ) {
              array_push( $this->purgeUrls, home_url( $category_base . $cat->slug . '/' ) );
            }
          }
          $tags = get_the_tags( $postId );
          if( $tags ) {
            $tag_base = get_option( 'tag_base' );
            if( $tag_base == '' )
              $tag_base = '/tag/';
            $tag_base = trailingslashit( str_replace( '..', '', $tag_base ) );
            foreach( $tags as $tag ) {
              array_push( $this->purgeUrls, home_url( $tag_base . $tag->slug . '/' ) );
            }
          }
          array_push( $this->purgeUrls, get_permalink( $postId ) );
          array_push( $this->purgeUrls, home_url() );
        } else {
          array_push( $this->purgeUrls, home_url( '?vhp=regex' ) );
        }
      }

    }

  }

}
<?php

namespace DiscoDonniePresents {

  if ( !class_exists( 'DiscoDonniePresents\Artist' ) ) {

    class Artist extends Entity {

      /**
       *
       * @var type
       */
      public $_type = 'artist';

      /**
       *
       * @var type
       */
      public $_meta_key = 'artists';

      /**
       *
       * @var type
       */
      public $_events;

      /**
       *
       * @param type $id
       * @param bool $preload
       */
      public function __construct($id = null, $preload = true) {
        parent::__construct($id);

        if ( $preload ) {
          $this->_events = $this->load_events();
        }

      }

      /**
       *
       * @return type
       */
      public function toElasticFormat() {

        $_object = array();

        $photo = wp_get_attachment_image_src( $this->meta('logo'), 'full' );

        $_object[ 'summary' ] = $this->post( 'post_title' );
        $_object[ 'url' ]     = get_permalink( $this->_id );
        $_object[ 'genre' ]   = $this->taxonomies( 'genre', 'elasticsearch' ) ? $this->taxonomies( 'genre', 'elasticsearch' ) : array();
        $_object[ 'official_url' ] = $this->meta( 'officialLink' ) ? $this->meta( 'officialLink' ) : '';
        $_object[ 'social_urls' ]  = $this->meta( 'socialLinks' ) ? $this->meta( 'socialLinks' ) : array();
        $_object[ 'logo' ]         = $photo[0];

        return $_object;

      }
    }
  }
}
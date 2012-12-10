<?php

class Debug_Console_WP_Query extends Debug_Console_Panel {
	function init() {
		$this->title( __('WP Query', 'debug-bar') );
	}

	function prerender() {
		$this->set_visible( defined('SAVEQUERIES') && SAVEQUERIES );
	}

	function render() {
		global $template, $wp_query;
		$queried_object = get_queried_object();
    $post_type_object = false;
		if( $queried_object && isset( $queried_object->post_type ) )
			$post_type_object = get_post_type_object( $queried_object->post_type );

    // Determine the query type. Follows the template loader order.
		$type = '';
		if ( is_404() )
			$type = '404';
		elseif ( is_search() )
			$type = 'Search';
		elseif ( is_tax() )
			$type = 'Taxonomy';
		elseif ( is_front_page() )
			$type = 'Front Page';
		elseif ( is_home() )
			$type = 'Home';
		elseif ( is_attachment() )
			$type = 'Attachment';
		elseif ( is_single() )
			$type = 'Single';
		elseif ( is_page() )
			$type = 'Page';
		elseif ( is_category() )
			$type = 'Category';
		elseif ( is_tag() )
			$type = 'Tag';
		elseif ( is_author() )
			$type = 'Author';
		elseif ( is_date() )
			$type = 'Date';
		elseif ( is_archive() )
			$type = 'Archive';
		elseif ( is_paged() )
			$type = 'Paged';
?>
  console.group( '<?php echo __('WP Query', 'debug-bar'); ?>' );
    console.info( 'Object ID: <?php echo get_queried_object_id(); ?>' );
    console.info( 'Query Type: <?php echo $type; ?>' );
    console.info( 'Query Template: <?php echo basename( $template ); ?>' );
    console.info( 'Show on Front: <?php echo ( $p = get_option( 'show_on_front' ) ); ?>' );
<?php
    if( $p=='page' ){
?>
    console.info( 'Page for Posts: <?php echo get_option( 'page_for_posts' ); ?>' );
    console.info( 'Page on Front: <?php echo get_option( 'page_on_front' ); ?>' );
<?php
    }
    if( $post_type_object ){
?>
    console.info( 'Post Type: <?php echo $post_type_object->labels->singular_name ; ?>' );
<?php
    }
?>
    console.info( 'Query Arguments: <?php echo ( empty( $wp_query->query ) ? 'None' : http_build_query( $wp_query->query ) ); ?>' );
<?php
    if( !empty( $wp_query->request ) ){
?>
    console.info( 'Query SQL: <?php echo addslashes( $wp_query->request ); ?>' );
<?php
    }
    if( !is_null( $queried_object ) ){
      $o = array();
      foreach( $queried_object as $k => $v ){
				if( is_object( $v ) ){
          $o[] = array( $k , serialize( $v ) );
				}else{
					$o[] = array( $k , $v );
				}
      }
?>
    console.info( '<?php echo number_format( count( $queried_object ) ); ?> Queried Objects' );
    console.table( <?php echo json_encode( $o ); ?> , [{property:"0",label:"Key"},{property:"1",label:"Value"}] );
<?php
		}
?>
  console.groupEnd();
<?php
	}
}

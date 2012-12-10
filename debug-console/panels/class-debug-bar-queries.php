<?php

class Debug_Console_Queries extends Debug_Console_Panel {
	function init() {
		$this->title( __('Queries', 'debug-bar') );
	}

	function prerender() {
		//$this->set_visible( defined('SAVEQUERIES') && SAVEQUERIES || ! empty($GLOBALS['EZSQL_ERROR']) );
	}

	function Debug_Console_classes( $classes ) {
		if ( ! empty($GLOBALS['EZSQL_ERROR']) )
			$classes[] = 'debug-bar-php-warning-summary';
		return $classes;
	}

	function render() {
		global $wpdb, $EZSQL_ERROR;

		$out = '';
		$total_time = 0;
?>
  console.group( '<?php echo number_format( $wpdb->num_queries ).' '.__('Queries', 'debug-bar'); ?>' );
<?php
    if( !defined( 'SAVEQUERIES' ) || !SAVEQUERIES ){
?>
    console.warn( '<?php echo __('SAVEQUERIES must be defined to show the query log.', 'debug-bar'); ?>' );
<?php
    }else{
      if( count( $wpdb->queries ) ){
        $o = array();
        foreach( $wpdb->queries as $k => $q ){
          list( $query , $elapsed , $debug ) = $q;
          $query = trim( preg_replace( array( "/\n/" , "/\s\s+/" ) , array( '' , ' ' ) , $query ) );
          $total_time += $elapsed;
          $debug = explode( ', ', $debug );
          $debug = array_diff( $debug, array( 'require_once', 'require', 'include_once', 'include' ) );
          $debug = implode( ', ', $debug );
          $debug = str_replace( array( 'do_action, call_user_func_array' ), array( 'do_action' ), $debug );
          $o[] = array( $k , $query , number_format( sprintf( '%0.1f' , $elapsed * 1000 ) , 1 , '.' , ',' ) );
        }
?>
    console.info( 'Total Query Time: <?php echo number_format( sprintf( '%0.1f' , $total_time * 1000 ) , 1 ); ?>ms' );
    console.table( <?php echo json_encode( $o ); ?> , [{property:"0",label:"Key"},{property:"1",label:"Query"},{property:"3",label:"Duration"}] );
<?php
      }
      if( !empty( $EZSQL_ERROR ) ){
        $o = array();
        foreach( $EZSQL_ERROR as $e ){
          $o[] = array( nl2br( esc_html( $e['query'] ) ) , $e['error_str'] );
        }
?>
    console.error( '<?php echo number_format( count( $EZSQL_ERROR ) ).' '.__( 'Database Errors', 'debug-bar' ); ?>' );
    console.table( <?php echo json_encode( $o ); ?> , [{property:"0",label:"Query"},{property:"1",label:"Error"}] );
<?php
      }
    }
?>
  console.groupEnd();
<?php
	}
}

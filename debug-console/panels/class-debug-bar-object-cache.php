<?php

class Debug_Console_Object_Cache extends Debug_Console_Panel {
	function init() {
		$this->title( __('Object Cache', 'debug-bar') );
	}

	function prerender() {
		global $wp_object_cache;
		$this->set_visible( is_object($wp_object_cache) && method_exists($wp_object_cache, 'stats') );
	}

	function render() {
		global $wp_object_cache;
		ob_start();
		$wp_object_cache->stats();
		$out = ob_get_clean();
?>
  console.group( '<?php echo __('Object Cache', 'debug-bar'); ?>' );
<?php
    if( preg_match_all( '/<strong>(Cache [^:]+:)<\/strong> (\d+)</' , $out , $s , PREG_SET_ORDER ) ){
      foreach( $s as $v ){
?>
    console.info( '<?php echo $v[1].' '.$v[2]; ?>' );
<?php
      }
    }
    if( preg_match_all( '/<li><strong>Group:<\/strong> ([^\s]+) - \( ([^\s]+) \)<\/li>/' , $out , $s , PREG_SET_ORDER ) ){
      $o = array();
      foreach( $s as $v )
        $o[] = array( $v[1] , $v[2] );
?>
    console.table( <?php echo json_encode( $o ); ?> , [{property:"0", label:"Group"},{property:["1"],label:"Size"}] );
<?php
    }
?>
  console.groupEnd();
<?php
	}
}

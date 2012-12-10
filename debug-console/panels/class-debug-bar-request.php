<?php

class Debug_Console_Request extends Debug_Console_Panel {
	function init() {
		$this->title( __('Request', 'debug-bar') );
	}

	function prerender() {
		$this->set_visible( ! is_admin() );
	}

	function render() {
		global $wp;
    $collapsed = ( empty( $wp->request )
                   && empty( $wp->query_string )
                   && empty( $wp->matched_rule )
                   && empty( $wp->matched_query ) );
?>
  console.group<?php echo ( $collapsed ? 'Collapsed' : '' ); ?>( '<?php echo __('Request', 'debug-bar'); ?>' );
    console.info( 'Request: <?php echo esc_html( empty( $wp->request ) ? 'None' : $wp->request ); ?>' );
    console.info( 'Query String: <?php echo esc_html( empty( $wp->query_string ) ? 'None' : $wp->query_string ); ?>' );
    console.info( 'Matched Rewrite Rule: <?php echo esc_html( empty( $wp->matched_rule ) ? 'None' : $wp->matched_rule ); ?>' );
    console.info( 'Matched Rewrite Query: <?php echo esc_html( empty( $wp->matched_query ) ? 'None' : $wp->matched_query ); ?>' );
  console.groupEnd();
<?php
	}
}

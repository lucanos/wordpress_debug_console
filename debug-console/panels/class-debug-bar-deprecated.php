<?php
// Alot of this code is massaged from Andrew Nacin's log-deprecated-notices plugin

class Debug_Console_Deprecated extends Debug_Console_Panel {
	var $deprecated_functions = array();
	var $deprecated_files = array();
	var $deprecated_arguments = array();

	function init() {
		$this->title( __('Deprecated', 'debug-bar') );

		add_action( 'deprecated_function_run', array( &$this, 'deprecated_function_run' ), 10, 3 );
		add_action( 'deprecated_file_included', array( &$this, 'deprecated_file_included' ), 10, 4 );
		add_action( 'deprecated_argument_run',  array( &$this, 'deprecated_argument_run' ),  10, 3 );

		// Silence E_NOTICE for deprecated usage.
		foreach ( array( 'function', 'file', 'argument' ) as $item )
			add_filter( "deprecated_{$item}_trigger_error", '__return_false' );
	}

	function prerender() {
		$this->set_visible(
			count( $this->deprecated_functions )
			|| count( $this->deprecated_files )
			|| count( $this->deprecated_arguments )
		);
	}

	function render(){
?>
  console.group( '<?php echo __('Deprecated', 'debug-bar'); ?>' );
    console.<?php echo ( count( $this->deprecated_functions ) ? 'warn' : 'info' ); ?>( '<?php echo number_format( count( $this->deprecated_functions ) ); ?> Deprecated Functions' );
<?php
    if( count( $this->deprecated_functions ) ){
      $o = array();
      foreach( $this->deprecated_functions As $k => $v );
        $o[] = array( str_replace( ABSPATH , '' , $k ) , strip_tags( $v ) );
?>
    console.table( <?php echo json_encode( $o ); ?> , [{property:"0",label:"Location"},{property:"1",label:"Message"}] );
<?php
    }
?>
    console.<?php echo ( count( $this->deprecated_arguments ) ? 'warn' : 'info' ); ?>( '<?php echo number_format( count( $this->deprecated_arguments ) ); ?> Deprecated Arguments' );
<?php
    if( count( $this->deprecated_arguments ) ){
      $o = array();
      foreach( $this->deprecated_arguments As $k => $v );
        $o[] = array( str_replace( ABSPATH , '' , $k ) , strip_tags( $v ) );
?>
    console.table( <?php echo json_encode( $o ); ?> , [{property:"0",label:"Location"},{property:"1",label:"Message"}] );
<?php
    }
?>
    console.<?php echo ( count( $this->deprecated_files ) ? 'warn' : 'info' ); ?>( '<?php echo number_format( count( $this->deprecated_files ) ); ?> Deprecated Files' );
<?php
    if( count( $this->deprecated_files ) ){
      $o = array();
      foreach( $this->deprecated_files As $k => $v );
        $o[] = array( str_replace( ABSPATH , '' , $k ) , strip_tags( $v ) );
?>
    console.table( <?php echo json_encode( $o ); ?> , [{property:"0",label:"Location"},{property:"1",label:"Message"}] );
<?php
    }
?>
  console.groupEnd();
<?php
	}

	function deprecated_function_run($function, $replacement, $version) {
		$backtrace = debug_backtrace();
		$bt = 4;
		// Check if we're a hook callback.
		if ( ! isset( $backtrace[4]['file'] ) && 'call_user_func_array' == $backtrace[5]['function'] ) {
			$bt = 6;
		}
		$file = $backtrace[ $bt ]['file'];
		$line = $backtrace[ $bt ]['line'];
		if ( ! is_null($replacement) )
			$message = sprintf( __('%1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.', 'debug-bar'), $function, $version, $replacement );
		else
			$message = sprintf( __('%1$s is <strong>deprecated</strong> since version %2$s with no alternative available.', 'debug-bar'), $function, $version );

		$this->deprecated_functions[$file.':'.$line] = $message;
	}

	function deprecated_file_included( $old_file, $replacement, $version, $message ) {
		$backtrace = debug_backtrace();
		$file = $backtrace[4]['file'];
		$file_abs = str_replace(ABSPATH, '', $file);
		$line = $backtrace[4]['line'];
		$message = empty( $message ) ? '' : ' ' . $message;
		if ( ! is_null( $replacement ) )
			$message = sprintf( __('%1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.', 'debug-bar'), $file_abs, $version, $replacement ) . $message;
		else
			$message = sprintf( __('%1$s is <strong>deprecated</strong> since version %2$s with no alternative available.', 'debug-bar'), $file_abs, $version ) . $message;

		$this->deprecated_files[$file.':'.$line] = $message;
	}

	function deprecated_argument_run( $function, $message, $version) {
		$backtrace = debug_backtrace();
		$bt = 4;
		if ( ! isset( $backtrace[4]['file'] ) && 'call_user_func_array' == $backtrace[5]['function'] ) {
			$bt = 6;
		}
		$file = $backtrace[ $bt ]['file'];
		$line = $backtrace[ $bt ]['line'];

		$this->deprecated_arguments[$file.':'.$line] = $message;
	}
}

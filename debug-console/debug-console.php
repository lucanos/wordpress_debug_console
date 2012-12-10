<?php
/*
 Plugin Name: Debug Console
 Plugin URI: https://github.com/lucanos/wordpress_debug_console
 Description: Adds a debug menu to the admin bar that shows query, cache, and other helpful debugging information.
 Author: https://github.com/lucanos/
 Version: 0.1
 Author URI: http://wordpress.org/
 */

/***
 * Debug Functions
 *
 * When logged in as a super admin, these functions will run to provide
 * debugging information when specific super admin menu items are selected.
 *
 * They are not used when a regular user is logged in.
 */

class Debug_Console {
	var $panels = array();

	function Debug_Console() {
		if ( defined('DOING_AJAX') && DOING_AJAX )
			add_action( 'admin_init', array( &$this, 'init_ajax' ) );
		add_action( 'admin_bar_init', array( &$this, 'init' ) );
	}

	function init() {
		if ( ! is_super_admin() || ! is_admin_bar_showing() || $this->is_wp_login() )
			return;

		add_action( 'wp_after_admin_bar_render',    array( &$this, 'render' ) );
		add_action( 'wp_head',                      array( &$this, 'ensure_ajaxurl' ), 1 );

		$this->requirements();
		$this->enqueue();
		$this->init_panels();
	}

	/* Are we on the wp-login.php page?
	 * We can get here while logged in and break the page as the admin bar isn't shown and otherthings the js relies on aren't available.
	 */
	function is_wp_login() {
		return 'wp-login.php' == basename( $_SERVER['SCRIPT_NAME'] );
	}

	function init_ajax() {
		if ( ! is_super_admin() )
			return;

		$this->requirements();
		$this->init_panels();
	}

	function requirements() {
		$recs = array( 'panel', 'php', 'queries', 'request', 'wp-query', 'object-cache', 'deprecated', 'js' );
		foreach ( $recs as $rec )
			require_once "panels/class-debug-bar-$rec.php";
	}

	function enqueue() {
		$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '.dev' : '';

		wp_enqueue_style( 'debug-bar', plugins_url( "css/debug-bar$suffix.css", __FILE__ ), array(), '20111209' );

		wp_enqueue_script( 'debug-bar', plugins_url( "js/debug-bar$suffix.js", __FILE__ ), array( 'jquery' ), '20111209', true );

		do_action('Debug_Console_enqueue_scripts');
	}

	function init_panels() {
		$classes = array(
			'Debug_Console_PHP',
			'Debug_Console_Queries',
			'Debug_Console_WP_Query',
			'Debug_Console_Deprecated',
			'Debug_Console_Request',
			'Debug_Console_Object_Cache',
		);

		foreach ( $classes as $class ) {
			$this->panels[] = new $class;
		}

		$this->panels = apply_filters( 'Debug_Console_panels', $this->panels );
	}

	function ensure_ajaxurl() { ?>
		<script type="text/javascript">
		//<![CDATA[
		var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
		//]]>
		</script>
		<?php
	}

	// memory_get_peak_usage is PHP >= 5.2.0 only
	function safe_memory_get_peak_usage() {
		if ( function_exists( 'memory_get_peak_usage' ) ) {
			$usage = memory_get_peak_usage();
		} else {
			$usage = memory_get_usage();
		}
		return $usage;
	}

	function render() {
		global $wpdb;

		if ( empty( $this->panels ) )
			return;

		foreach ( $this->panels as $panel_key => $panel ) {
			$panel->prerender();
			if ( ! $panel->is_visible() )
				unset( $this->panels[ $panel_key ] );
		}

		?>
<script type="text/javascript">
console.group( 'Wordpress Debug Console' );
<?php if( !WP_DEBUG ){ ?>
  console.error( '<?php echo __('Please Enable', 'debug-bar').' WP_DEBUG'; ?> ');
<?php } ?>
  console.groupCollapsed( 'Status Information' );
    console.info( 'Site: <?php echo php_uname( 'n' ).' '.sprintf( __( '#%d', 'debug-bar' ), get_current_blog_id() ); ?>' );
    console.info( 'PHP Version: <?php echo phpversion(); ?>' );
    console.info( '<?php echo ( empty( $wpdb->is_mysql ) ? __( 'DB', 'debug-bar' ) : 'MySQL' ); ?> Version: <?php echo $wpdb->db_version(); ?>' );
    console.info( '<?php echo __('Memory Usage', 'debug-bar').': '.sprintf( __('%s bytes', 'debug-bar'), number_format_i18n( $this->safe_memory_get_peak_usage() ) ); ?>' );
  console.groupEnd();
<?php
      foreach( $this->panels as $panel ){
		    $panel->render();
      }
?>
<?php do_action( 'Debug_Console' ); ?>
</script>
	<?php
	}
}

$GLOBALS['Debug_Console'] = new Debug_Console();

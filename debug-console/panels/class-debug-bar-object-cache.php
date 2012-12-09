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
		echo "<div id='object-cache-stats'>";
		$wp_object_cache->stats();
		echo "</div>";
		$out = ob_get_contents();
		ob_end_clean();

		echo $out;
	}
}

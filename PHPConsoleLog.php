<?php

class PHPConsole {
	
	//Public propertyies:
	public static $m_stEnabled = true;
	
	public static function enable()
	{
		self::$m_stEnabled = true;
	}
	public static function disable()
	{
		self::$m_stEnabled = false;
	}
	public static function log($_msg)
	{
		if (self::$m_stEnabled) {
			echo "<script>console.log('" . $_msg . "')</script>";
		}
	}
	
}

//PHPConsole::disable();
//PHPConsole::log("desabilitado");

//PHPConsole::enable();
//PHPConsole::log("habilitado");

?>
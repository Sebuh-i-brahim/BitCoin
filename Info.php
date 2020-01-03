<?php


class Info
{	
	private static $_info = null;

	function __construct($message = null)
	{

		$check = (isset($GLOBALS['info_cache']))? $GLOBALS['info_cache'] : false;

		$info_file_path = $GLOBALS['info_file_path'];

		if($check):

			$data = debug_backtrace()[0];

			$delete = false;

			if (file_exists($info_file_path)) {
	    		$filetime = date ("F d Y", filemtime($info_file_path));
	    		$now = date ("F d Y", time());
	    		if ($now != $filetime) {
	    			$delete = true;
	    		}
			}

			$file_data = "=======================================================\r\n";

			$file_data .= ">>> Date: ".date("Y-m-d H:i:s"). "\r\n";

			$file_data .= ">>> File: ".$data["file"]. "\r\n";

			$file_data .= ">>> Line: ".$data["line"]."\r\n"; //$this->getLine($data)

			$file_data .= ">>> Message: ".$data["args"][0]. "\r\n";

			$file_data .= "=======================================================\r\n\r\n";

			
			if ($delete) {
				$file = fopen($info_file_path, 'wbt');
			}else{
				$file = fopen($info_file_path, 'abt');
			}

			fwrite($file, $file_data);

			fclose($file);

		endif;
	}

	static public function write($message = null)
	{
		self::$_null = new Info($message);
	}
}
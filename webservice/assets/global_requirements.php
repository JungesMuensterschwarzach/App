<?php

    /*
                        - File system -
        This should theoretically be put into config-*.php,
        but the root path needs to be declared right at the
        beginning to work properly.
    */
	if (defined("ROOT_LOCAL") === false) {
		define("ROOT_LOCAL", dirname(__FILE__) . "/..");
	}
	if (defined("ROOT_PUBLIC") === false) {
		define("ROOT_PUBLIC", "/");
	}


    require_once(ROOT_LOCAL."/assets/config.php");
    require_once(ROOT_LOCAL."/assets/dict.php");
    require_once(ROOT_LOCAL."/assets/alert.php");
    require_once(ROOT_LOCAL."/assets/global_functions.php");
    require_once(ROOT_LOCAL."/modules/CookieModule.php");
?>
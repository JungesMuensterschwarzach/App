<?php
    if (ROOT_LOCAL === null) {
		require_once("../assets/global_requirements.php");
    }

    class CookieModule {

        public static function set($name, $value, $httpOnly = false, $expire = 2147483647/* expire 2038 */): void {
            if (is_object($value)) {
                $value = json_encode($value);
            }
            $name = self::getFullName($name);

            setcookie(
                $name,
                $value,
                array(
                    "expires" => $expire,
                    "path" => "/",
                    "domain" => "junges-muensterschwarzach.roth-familie.eu",
                    "secure" => GlobalFunctions::isSSLRequest(),
                    "httponly" => $httpOnly,
                    "samesite"=> "Lax"
                )
            );
            $_COOKIE[$name] = $value;
        }

        public static function get($name): mixed {
            $name = self::getFullName($name);

            $value = $_COOKIE[$name] ?? null;
            if ($value === null) {
                return null;
            }

            $json = json_decode($value);
            return json_last_error() === JSON_ERROR_NONE ? $json : $value;
        }

        public static function remove($name): void {
            unset($_COOKIE[self::getFullName($name)]);
            self::set($name, "", true, time() - 3600);
        }

        public static function getFullName($nameWOPrefix): string {
            return GlobalFunctions::isSSLRequest() ? 
                "__Secure-" . $nameWOPrefix : $nameWOPrefix;
        }
        
    }
?>
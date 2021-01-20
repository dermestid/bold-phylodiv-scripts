<?php

// groups functions for getting configured executables (PAUP and Clustal)

class Executable 
{
    public const PAUP = 'PAUP';
    public const CLUSTAL = 'Clustal';

    public static function exists($executable) {
        if (($path = self::path($executable)) === false)
            return false;

        if (stripos(PHP_OS, 'WIN') === 0) {
            // This could be expanded to read the PATHEXT variable
            // but no real need: clustal is an exe, paup is a cmd, that's all we need to know
            $exec_file =   
                file_exists("$path.exe") 
            ||  file_exists("$path.cmd")
            ||  file_exists("$path.bat");
            return $exec_file;
        } else {
            // needs testing
            $path = glob($path)[0];
            return is_executable($path);
        } 
    }

    public static function path($executable) {
        if ($executable === self::PAUP)
            return self::paup_path();
        else if ($executable === self::CLUSTAL)
            return self::clustal_path();
        else
            return false;
    }


    private const PAUP_PATH_ENV = "PAUP_PATH";
    private const CLUSTAL_PATH_ENV = "CLUSTAL_PATH";

    private static function paup_path() {
        return getenv(self::PAUP_PATH_ENV);
    }
    private static function clustal_path() {
        return getenv(self::CLUSTAL_PATH_ENV);
    }
}

?>

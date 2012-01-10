<?php

if (!file_exists(dirname(__file__) . "/config.inc.php"))
{
    echo "No config.inc.php found!";
    exit(-1);
}

include_once ("config.inc.php");
include_once ("../LIBUI.inc");
include_once ("include/format.inc.php");
include_once ("db_init.inc.php");

function verify_env()
{
    include_once ("include/syscheck.inc.php");

    $ret = system_check();
    if ($ret !== true)
    {
        echo $ret;
        return false;
    }

    $log_dirs = array("log", "pushlog", "synclog", "tmp", "etc");
    foreach ($log_dirs as $log_dir)
    {
        $dir = LOCAL_CONFIG_DIR . "/" . $log_dir;
        if (file_exists($dir) && !is_writable($dir))
        {
            echo "<font color=red>{$dir} is not writable! Please run clean.sh and prepare.sh first! </font>";
            return false;
        }
        elseif (!file_exists($dir))
        {
            if (!mkdir($dir))
            {
                echo "<font color=red>Filed to create directory {$dir}, please run clean.sh and prepare.sh first! </font>";
                return false;
            }
        }
    }
    if (file_exists(CUSTOM_CONFIG))
    {
        if (!is_writable(CUSTOM_CONFIG))
        {
            echo "File " . CUSTOM_CONFIG . " is not writable!";
            return false;
        }
    }
    else
    {
        if (!touch(CUSTOM_CONFIG))
        {
            echo "Failed to create " . CUSTOM_CONFIG . " is not writable!";
            return false;
        }
    }
    return true;
}

session_start();

if (empty($_SESSION['_user']))
{

    if (!isset($_admin_password))
    {
        if (verify_env())
        {
            $_SESSION['_state'] = "raw_config";
            include "write_custom_config.php";
        }
    }
    else
    {
        include "login.php";
    }

}
else
{
    if (empty($_menu))
    {
        $_menu = $_default_menu;
    }

    print_title($_menu);

    include_once(dirname(__FILE__)."/pages/".$_menu_config[$_menu]["PAGE"]);

}

print_footnote();

?>

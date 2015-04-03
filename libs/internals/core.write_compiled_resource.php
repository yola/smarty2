<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Check to see if the path is writable on a Windows machine
 * Copied from http://php.net/manual/en/function.is-writable.php comment by
 * legolas558
 *
 * Added 04/03/2015 by Yola to address is_writable PHP bug that does not
 * correctly handle ACL on Windows platforms.
 */
function is_writable_win($path) {
//will work in despite of Windows ACLs bug
//NOTE: use a trailing slash for folders!!!
//see http://bugs.php.net/bug.php?id=27609
//see http://bugs.php.net/bug.php?id=30931

    if ($path{strlen($path)-1}=='/') // recursively return a temporary file path
        return is__writable($path.uniqid(mt_rand()).'.tmp');
    else if (is_dir($path))
        return is__writable($path.'/'.uniqid(mt_rand()).'.tmp');
    // check tmp file for read/write capabilities
    $rm = file_exists($path);
    $f = @fopen($path, 'a');
    if ($f===false)
        return false;
    fclose($f);
    if (!$rm)
        unlink($path);
    return true;
}

/**
 * write the compiled resource
 *
 * @param string $compile_path
 * @param string $compiled_content
 * @return true
 */
function smarty_core_write_compiled_resource($params, &$smarty)
{
    /**
     * Detect if running on a Windows server
     * Added 04/03/2015 by Yola. Necessary to avoid bug in PHP with
     * is_writable() not working correctly with ACL permissions.
     */
    $isWin = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    /**
     * $isWin related checks and is_writable_win() added 04/03/2015 by Yola
     * Circumvent PHP bug with is_writable not handling ACLs correctly on
     * Windows platfrom.
     */
    if((!$isWin && !@is_writable($smarty->compile_dir)) || ($isWin && !is_writable_win($smarty->compile_dir))) {
        // compile_dir not writable, see if it exists
        if(!@is_dir($smarty->compile_dir)) {
            $smarty->trigger_error('the $compile_dir \'' . $smarty->compile_dir . '\' does not exist, or is not a directory.', E_USER_ERROR);
            return false;
        }
        $smarty->trigger_error('unable to write to $compile_dir \'' . realpath($smarty->compile_dir) . '\'. Be sure $compile_dir is writable by the web server user.', E_USER_ERROR);
        return false;
    }

    $_params = array('filename' => $params['compile_path'], 'contents' => $params['compiled_content'], 'create_dirs' => true);
    require_once(SMARTY_CORE_DIR . 'core.write_file.php');
    smarty_core_write_file($_params, $smarty);
    return true;
}

/* vim: set expandtab: */

?>

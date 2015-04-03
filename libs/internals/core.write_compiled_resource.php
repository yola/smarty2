<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Check to see if the path is writable, works on Windows platforms
 * Based from http://php.net/manual/en/function.is-writable.php comment by
 * legolas558
 *
 * Added 04/03/2015 by Yola to address is_writable PHP bug that does not
 * correctly handle ACL on Windows platforms.
 */
function isWritable($path)
{
    $isWin = strtoupper(substr(php_uname('s'), 0, 3)) === 'WIN';

    if($isWin) {
        if ($path{strlen($path) - 1} == '/') {
            return isWritable($path.uniqid(mt_rand()) . '.tmp');
        } elseif (is_dir($path)) {
            return isWritable($path . '/' . uniqid(mt_rand()) . 'tmp');
        }

        // check tmp file for read/write capabilities
        $rm = file_exists($path);
        $f = @fopen($path, 'a');

        if ($f === false) {
            return false;
        }

        fclose($f);

        if (!$rm) {
            unlink($path);
        }

        return true;
    }

    return is_writable($path);
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
     * Replaced `is_writable` with `isWritable` 04/03/2015 by Yola
     * Avoids PHP bug with `is_writable` on Windows platform
     */
    if(!@isWritable($smarty->compile_dir)) {
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

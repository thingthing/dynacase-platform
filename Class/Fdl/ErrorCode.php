<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Created by JetBrains PhpStorm.
 * User: eric
 * Date: 01/12/11
 * Time: 18:27
 * To change this template use File | Settings | File Templates.
 */
class ErrorCode
{
    /**
     * @static
     * @param string $code error code
     * @param ... $args
     * @return string the composed message
     */
    public static function getError($code, $args = null)
    {
        $msg = '';
        if ($code) {
            if (preg_match('/^([A-Z]+)([0-9]+)$/', $code, $reg)) {
                $class = sprintf("ErrorCode%s", $reg[1]);
                try {
                    $rc = new ReflectionClass($class);
                    $fmt = $rc->getConstant($code);
                    if ($fmt) {
                        
                        $nargs = func_num_args();
                        $sp = array();
                        for ($ip = 1; $ip < $nargs; $ip++) {
                            $sp[] = func_get_arg($ip);
                        }
                        $label = @vsprintf($fmt, $sp);
                        if ($label) {
                            $msg = sprintf("{%s} %s", $code, $label);
                        } else {
                            $gl = error_get_last();
                            $msg = sprintf("{%s} %s", $code, $gl['message']);
                        }
                    } else {
                        $msg = sprintf("unknow error code %s", $code);
                    }
                }
                catch(Exception $e) {
                    $msg = sprintf("cannot find class error for %s : %s", $code, $e->getMessage());
                }
            } else {
                $msg = sprintf("wrong error code %s", $code);
            }
        }
        if ($msg) error_log("ERROR:" . $msg);
        return $msg;
    }
}
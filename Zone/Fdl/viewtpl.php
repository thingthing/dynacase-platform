<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Edit special template
 *
 * @author Anakeen
 * @version $Id: fdl_card.php,v 1.42 2008/12/02 15:20:52 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Dir.php");
/**
 * View a document
 * @param Action &$action current action
 * @global id Http var : document identifier to see
 * @global famid Http var :
 * @global zone Http var : zone
 */
function viewtpl(Action & $action)
{
    $zone = $action->getArgument("zone");
    $docid = $action->getArgument("id");
    $target = $action->getArgument("target", "_self");
    $ulink = $action->getArgument("ulink", true);
    $abstract = $action->getArgument("abstract", true);
    if (!$docid) $docid = $action->getArgument("famid");
    if (!$zone) $action->addWarningMsg(_("no template defined"));
    if (!$docid) $action->addWarningMsg(sprintf(_("template %s no document found") , $zone));
    $dbaccess = $action->getParam("FREEDOM_DB");
    
    $reg = Doc::parseZone($zone);
    if ($reg === false) {
        return sprintf(_("error in pzone format %s") , $zone);
    }
    
    if (array_key_exists('argv', $reg)) {
        foreach ($reg['argv'] as $k => $v) {
            setHttpVar($k, $v);
        }
    }
    
    $appname = $reg['app'];
    $doc = new_doc($dbaccess, $docid);
    if ($doc->isAffected()) {
        $action->lay = new Layout($doc->getZoneFile($zone) , $action);
        $doc->lay = & $action->lay;
        
        $method = strtok(strtolower($reg['layout']) , '.');
        if (method_exists($doc, $method)) {
            $doc->$method();
        } else {
            $doc->viewdefaultcard($target);
        }
    }
}
?>
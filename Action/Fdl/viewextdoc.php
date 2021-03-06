<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * View Document
 *
 * @author Anakeen
 * @version $Id: fdl_card.php,v 1.42 2008/12/02 15:20:52 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/fdl_card.php");

include_once ("FDL/popupdocdetail.php");
include_once ("FDL/popupfamdetail.php");
/**
 * View a extjs document
 * @param Action &$action current action
 * @global id Http var : document identifier to see
 * @global latest Http var : (Y|N|L|P) if Y force view latest revision, L : latest fixed revision, P : previous revision
 * @global state Http var : to view document in latest fixed state (only if revision > 0)
 * @global abstract Http var : (Y|N) if Y view only abstract attribute
 * @global props Http var : (Y|N) if Y view properties also
 * @global zonebodycard Http var : if set, view other specific representation
 * @global vid Http var : if set, view represention describe in view control (can be use only if doc has controlled view)
 * @global ulink Http var : (Y|N)if N hyperlink are disabled
 * @global target Http var : is set target of hyperlink can change (default _self)
 * @global inline Http var : (Y|N) set to Y for binary template. View in navigator
 * @global reload Http var : (Y|N) if Y update freedom folders in client navigator
 * @global dochead Http var :  (Y|N) if N don't see head of document (not title and icon)
 */
function viewextdoc(&$action)
{
    if (!file_exists('lib/ui/freedom-extui.js')) {
        $err = _("This action requires the installation of Dynacase Extui module");
        $action->ExitError($err);
    }
    
    $ec = getHttpVars("extconfig");
    if ($ec) {
        $ec = json_decode($ec);
        foreach ($ec as $k => $v) {
            setHttpVar("ext:$k", $v);
            //$action->register("ext:$k",$v);
            
        }
    }
    
    fdl_card($action);
    $docid = GetHttpVars("id");
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $doc = new_Doc($dbaccess, $docid);
    
    if ($doc->doctype == 'C') $popup = getpopupfamdetail($action, $docid);
    else $popup = getpopupdocdetail($action, $docid);
    // rewrite for api 3.0
    $im = array();
    foreach ($popup as $k => $v) {
        if ($v["visibility"] != POPUP_INVISIBLE) {
            if (!isset($v["jsfunction"])) $v["jsfunction"] = '';
            if (!isset($v["title"])) $v["title"] = '';
            if (!isset($v["color"])) $v["color"] = '';
            if (!isset($v["url"])) $v["url"] = '';
            if (!isset($v["icon"])) $v["icon"] = '';
            if (preg_match("/zone=.*:pdf/", $v["url"])) $url = $v["url"];
            else $url = str_replace(array(
                "FDL_CARD",
                'app=GENERIC&action=GENERIC_EDIT',
                "&action="
            ) , array(
                "VIEWEXTDOC",
                'app=FDL&action=EDITEXTDOC',
                '&viewext=yes&action='
            ) , $v["url"]);
            $imenu = array(
                "url" => $url,
                "javascript" => str_replace(array(
                    "FDL_CARD",
                    'app=GENERIC&action=GENERIC_EDIT',
                    "&action="
                ) , array(
                    "VIEWEXTDOC",
                    'app=FDL&action=EDITEXTDOC',
                    '&viewext=yes&action='
                ) , $v["jsfunction"]) ,
                "visibility" => $v["visibility"],
                "label" => $v["descr"],
                "type" => "item",
                "target" => $v["target"],
                "description" => $v["title"],
                "backgroundColor" => $v["color"],
                "icon" => $v["icon"],
                "confirm" => (($v["confirm"] && $v["confirm"] != 'false') ? array(
                    "label" => $v["tconfirm"],
                    "continue" => _("yes") ,
                    "cancel" => _("no")
                ) : null)
            );
            if (!$v["submenu"]) {
                $mainmenu = ($doc->doctype == 'C') ? _("Family") : $v["submenu"] = $doc->fromtitle;
                $v["submenu"] = $mainmenu;
            }
            if ($v["submenu"]) {
                if (empty($im[$v["submenu"]])) {
                    $im[$v["submenu"]] = array(
                        "type" => "menu",
                        "label" => _($v["submenu"]) ,
                        "items" => array()
                    );
                    if ($v["submenu"] == $doc->fromtitle) $im[$v["submenu"]]["icon"] = $doc->getIcon();
                }
                $im[$v["submenu"]]["items"][$k] = $imenu;
            } else $im[$k] = $imenu;
        }
    }
    $im[$mainmenu]["items"]["histo"] = array(
        "script" => array(
            "file" => "lib/ui/fdl-interface-action-common.js",
            "class" => "Fdl.InterfaceAction.Historic"
        ) ,
        "label" => _("Historic") ,
        "visibility" => isset($im[$mainmenu]["items"]["histo"]["visibility"]) ? $im[$mainmenu]["items"]["histo"]["visibility"] : ''
    );
    $fnote = new_doc($doc->dbaccess, "SIMPLENOTE");
    if ($fnote->control("icreate") == "") {
        $im[$mainmenu]["items"]["addpostit"] = array(
            "script" => array(
                "file" => "lib/ui/fdl-interface-action-common.js",
                "class" => "Fdl.InterfaceAction.SimpleNote"
            ) ,
            "label" => _("Add a note") ,
            "visibility" => $im[$mainmenu]["items"]["addpostit"]["visibility"]
        );
    } else {
        $im[$mainmenu]["items"]["addpostit"]["visibility"] = POPUP_INVISIBLE;
    }
    if ($doc->control("send") == "") {
        $im[$mainmenu]["items"]["sendmail"] = array(
            "url" => "?app=FDL&action=EDITMAIL&viewext=yes&mid=" . $doc->id,
            "label" => _("Send document") ,
            "visibility" => POPUP_ACTIVE
        );
    }
    $im[$mainmenu]["items"]["viewprint"] = array(
        "javascript" => "window.open('?app=FDL&action=IMPCARD&view=print&id=" . $doc->id . "','_blank')",
        "label" => _("View print version") ,
        "visibility" => POPUP_ACTIVE
    );
    $im[$mainmenu]["items"]["reload"] = array(
        "script" => array(
            "file" => "lib/ui/fdl-interface-action-common.js",
            "class" => "Fdl.InterfaceAction.Reload"
        ) ,
        "label" => _("Reload document") ,
        "visibility" => POPUP_ACTIVE
    );
    $action->lay->set("documentMenu", json_encode($im));
    
    $style = $action->parent->getParam("STYLE");
    
    $action->parent->AddCssRef("STYLE/DEFAULT/Layout/EXT-ADAPTER-SYSTEM.css");
    if (file_exists($action->parent->rootdir . "/STYLE/$style/Layout/EXT-ADAPTER-USER.css")) {
        $action->parent->AddCssRef("STYLE/$style/Layout/EXT-ADAPTER-USER.css");
    } else {
        $action->parent->AddCssRef("STYLE/DEFAULT/Layout/EXT-ADAPTER-USER.css");
    }
}
?>

<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * return dynamic enum from family parameters
 * @return string
 */
function helppageenumlang()
{
    
    $dbaccess = getParam("FREEDOM_DB");
    
    $doc = new_Doc($dbaccess, 'HELPPAGE');
    if ($doc->isAlive()) {
        $all_lang_keys = $doc->rawValueToArray($doc->getFamilyParameterValue('help_p_lang_key'));
        $all_lang_texts = $doc->rawValueToArray($doc->getFamilyParameterValue('help_p_lang_name'));
        $langs = array();
        foreach ($all_lang_keys as $i => $key) {
            $langs[] = $key . '|' . $all_lang_texts[$i];
        }
        return implode(',', $langs);
    }
    return 'fr_FR|' . _('french');
}

function helppage_editsection(Action & $action, $dbaccess, $docid)
{
    
    include_once ('FDL/editutil.php');
    editmode($action);
    /**
     * @var _HELPPAGE $doc
     */
    $doc = new_Doc($dbaccess, $docid);
    $action->lay->set('DOCID', $docid);
    $action->lay->set('DOCTITLE', $doc->getTitle());
    
    $helps = $doc->getHelpByLang();
    $langs = $doc->getFamilyLangs();
    
    $action->lay->set('JSONLANGS', json_encode($langs));
    
    $section_key = GetHttpVars('edit_section_key', '');
    $action->lay->set('SECTIONKEY', $section_key);
    
    $langitems = array();
    foreach ($langs as $lang_key => $lang_name) {
        $langitems[] = array(
            'LANGKEY' => $lang_key,
            'LANGISO' => strtolower(substr($lang_key, -2)) ,
            'LANGNAME' => $lang_name,
        );
    }
    
    $action->lay->SetBlockData('LEGENDCSSLANGS', $langitems);
    $action->lay->SetBlockData('LEGENDLANGS', $langitems);
    $action->lay->SetBlockData('MENULANGS', $langitems);
}

function helppage_edithelp(Action & $action, $dbaccess, $docid)
{
    
    include_once ('FDL/editutil.php');
    editmode($action);
    /**
     * @var _HELPPAGE $doc
     */
    $doc = new_Doc($dbaccess, $docid);
    $action->lay->set('DOCID', $docid);
    $action->lay->set('DOCTITLE', $doc->getTitle());
    
    $helps = $doc->getHelpByLang();
    $langs = $doc->getFamilyLangs();
    
    $action->lay->set('JSONLANGS', json_encode($langs));
    
    $helplangs = array();
    $index = 0;
    $displayed = false;
    foreach ($langs as $lang_key => $lang_name) {
        $item = array();
        $item['LANGKEY'] = $lang_key;
        $item['LANGISO'] = strtolower(substr($lang_key, -2));
        $item['LANGNAME'] = $lang_name;
        $item['LANGDISPLAY'] = 'none';
        $oa1 = $doc->getAttribute('help_name');
        $oa2 = $doc->getAttribute('help_description');
        if (array_key_exists($lang_key, $helps)) {
            if (!$displayed) {
                $item['LANGDISPLAY'] = 'block';
                $displayed = true;
            }
            $item['INPUTLANGNAME'] = getHtmlInput($doc, $oa1, $helps[$lang_key]['help_name'], $index, '', true);
            $item['INPUTLANGDESCRIPTION'] = getHtmlInput($doc, $oa2, $helps[$lang_key]['help_description'], $index, '', true);
        } else {
            $item['INPUTLANGNAME'] = getHtmlInput($doc, $oa1, '', $index, '', true);
            $item['INPUTLANGDESCRIPTION'] = getHtmlInput($doc, $oa2, '', $index, '', true);
        }
        $index++;
        $helplangs[] = $item;
    }
    if (!empty($helplangs) && !$displayed) {
        $helplangs[0]['LANGDISPLAY'] = 'block';
    }
    $action->lay->SetBlockData('HELPLANGS1', $helplangs);
    $action->lay->SetBlockData('HELPLANGS2', $helplangs);
}
?>
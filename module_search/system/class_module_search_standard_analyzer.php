<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

/**
 * A default text-analyzer. Transforms the passed text into single tokens.
 *
 * @package module_search
 * @author tim.kiefer@kojikui.de
 * @since 4.4
 */
class class_module_search_standard_analyzer {

    private $arrResults = array();
    private $strText;

    /**
     * Parses, normalizes and tokens the passed string
     * @param string $strText
     *
     * @return string[]
     */
    public function analyze($strText) {
        $this->setText($strText);

        $this->clearmarks();
        $this->lowerize();
        $this->clearShortText();
        $this->tokenize();
        $this->blacklisting();
        $this->stemming();

        return $this->getResults();
    }

    /**
     * @param mixed $strText
     * @return void
     */
    public function setText($strText) {
        $this->strText = $strText;
    }

    /**
     * Make it lower, baby...
     * @return void
     */
    private function lowerize() {
        $this->setText(strtolower($this->getText()));
    }

    /**
     * @return mixed
     */
    public function getText() {
        return $this->strText;
    }

    /**
     * Removed all blacklisted tokens from the current set of tokens.
     * Processed after the tokenizing.
     * @return void
     */
    private function blacklisting() {

        // take care of white spaces
        $arrBlacklist = class_config::getInstance("search_blacklist.php")->getConfig("black_list");
        foreach($this->arrResults as $strToken => $intScore)
            if(in_array($strToken, $arrBlacklist))
                unset($this->arrResults[$strToken]);
    }

    /**
     * Splits the current text into several tokens
     * @return void
     */
    private function tokenize() {
        $arrResults = array();
        preg_match_all('/\w{1,}/u', $this->getText(), $arrResults);
        $this->setResults($arrResults[0]);
        $this->setResults(array_count_values($this->getResults()));
    }

    /**
     * @param array $arrResults
     * @return void
     */
    public function setResults($arrResults) {
        $this->arrResults = $arrResults;
    }

    /**
     * @return void
     */
    private function stemming() {
        //TODO: implement stemming maybe later :-)
    }

    /**
     * @return string[]
     */
    public function getResults() {
        return $this->arrResults;
    }

    /**
     * Removes marks from the text
     * @return void
     */
    private function clearmarks() {
        $arrMarks = class_config::getInstance("search_blacklist.php")->getConfig("marks_list");
        $this->setText(uniStrReplace($arrMarks, "", html_entity_decode($this->getText())));
    }

    /**
     * analyze only text with more than 2 characters
     * @return void
     */
    private function clearShortText() {
        $this->setText(preg_replace('/\b[\w]{1,2}\b/u', "", $this->getText()));
    }

}

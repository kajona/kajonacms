<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                        *
********************************************************************************************************/


/**
 * This is the common exception to inherit or to throw in the code.
 * Please DO NOT throw a "plain" exception, otherwise logging and error-handling
 * will not work properly!
 *
 * @package module_system
 */
class class_exception extends Exception {

    /**
     * This level is for common errors happening from time to time ;)
     *
     * @var int
     * @static
     */
    public static $level_ERROR = 1;

    /**
     * Level for really heavy errors. Hopefully not happening that often...
     *
     * @var int
     * @static
     */
    public static $level_FATALERROR = 2;

    private $intErrorlevel;

    public function __construct($strError, $intErrorlevel) {
        parent::__construct($strError);
        $this->intErrorlevel = $intErrorlevel;
    }


    /**
     * Used to handle the current exception.
     * Decides, if the execution should be stopped, or continued.
     * Therefore the errorlevel defines the "weight" of the exception
     *
     */
    public function processException() {
        //decide, what to print --> get config-value
        $intConfigDebuglevel = class_carrier::getInstance()->getObjConfig()->getDebug("debuglevel");
        // 0: fatal errors will be displayed
        // 1: fatal and regular errors will be displayed

        //set which POST parameters should read out
        $arrPostParams = array("module", "action", "page", "systemid");

        //send an email to the admin?
        if(defined("_system_admin_email_") && _system_admin_email_ != "") {
            $strMailtext = "";
            $strMailtext .= "The system installed at "._webpath_." registered an error!\n\n";
            $strMailtext .= "The error message was:\n";
            $strMailtext .= "\t".$this->getMessage()."\n\n";
            $strMailtext .= "The level of this error was:\n";
            $strMailtext .= "\t";
            if($this->getErrorlevel() == self::$level_FATALERROR)
                $strMailtext .= "FATAL ERROR";
            if($this->getErrorlevel() == self::$level_ERROR)
                $strMailtext .= "REGULAR ERROR";
            $strMailtext .= "\n\n";
            $strMailtext .= "File and line number the error was thrown:\n";
            $strMailtext .= "\t".basename($this->getFile()) ." in line ".$this->getLine()."\n\n";
            $strMailtext .= "Callstack / Backtrace:\n\n";
            $strMailtext .= $this->getTraceAsString();
            $strMailtext .= "\n\n";
            $strMailtext .= "User: ".  class_carrier::getInstance()->getObjSession()->getUserID()." (".class_carrier::getInstance()->getObjSession()->getUsername().")\n";
            $strMailtext .= "Source host: ".getServer("REMOTE_ADDR")." (".gethostbyaddr(getServer("REMOTE_ADDR")).")\n";
            $strMailtext .= "Query string: ".getServer("REQUEST_URI")."\n";
			$strMailtext .= "POST data (selective):\n";
            foreach ($arrPostParams as $strParam) {
            	if (getPost($strParam) != "") {
            		$strMailtext .= "\t".$strParam.": ".getPost($strParam)."\n";
            	}
            }
            $strMailtext .= "\n\n";
            $strMailtext .= "Last actions called:\n";
            $strMailtext .= "Admin:\n";
            $arrHistory = class_carrier::getInstance()->getObjSession()->getSession("adminHistory");
            if(is_array($arrHistory))
                foreach($arrHistory as $intIndex => $strOneUrl)
                    $strMailtext .= " #".$intIndex.": ".$strOneUrl."\n";
            $strMailtext .= "Portal:\n";
            $arrHistory = class_carrier::getInstance()->getObjSession()->getSession("portalHistory");
            if(is_array($arrHistory))
                foreach($arrHistory as $intIndex => $strOneUrl)
                    $strMailtext .= " #".$intIndex.": ".$strOneUrl."\n";
            $strMailtext .= "\n\n";
            $strMailtext .= "If you don't know what to do, feel free to open a ticket.\n\n";
            $strMailtext .= "For more help visit http://www.kajona.de.\n\n";

            $objMail = new class_mail();
            $objMail->setSubject("Error on website "._webpath_." occured!");
            $objMail->setSender(_system_admin_email_);
            $objMail->setText($strMailtext);
            $objMail->addTo(_system_admin_email_);
            $objMail->sendMail();
        }

        if($this->intErrorlevel == class_exception::$level_FATALERROR) {
            //Handle fatal errors.
            $strErrormessage = "";
            $strLogMessage = basename($this->getFile()).":".$this->getLine(). " -- ".$this->getMessage();
            class_logger::getInstance()->addLogRow($strLogMessage, class_logger::$levelError);

            //fatal errors are displayed in every case
            if(_xmlLoader_ === true) {
                $strErrormessage = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
                $strErrormessage .= "<error>".xmlSafeString($this->getMessage())."</error>";
            }
            else {
                $strErrormessage = "<html><head></head><body><div style=\"border: 1px solid red; padding: 5px; margin: 20px; font-family: arial,verdana; font-size: 12px;  \">\n";
    		    $strErrormessage .= "<div style=\"background-color: #cccccc; color: #000000; font-weight: bold; \">A fatal error occured:</div>\n";
    		    $strErrormessage .= $this->getMessage()."<br />";

                $strErrormessage .= "Please inform the administration about the error above.";
    	        $strErrormessage .= "</div></body></html>";

            }
            print $strErrormessage;
	        //close remaining txs
	        class_carrier::getInstance()->getObjDB()->__destruct();
	        //Execution has to be stopped here!
	        die();
        }
        elseif ($this->intErrorlevel == class_exception::$level_ERROR) {
            //handle regular errors
            $strErrormessage = "";
            $strLogMessage = basename($this->getFile()).":".$this->getLine(). " -- ".$this->getMessage();
            class_logger::getInstance()->addLogRow($strLogMessage, class_logger::$levelWarning);

            //check, if regular errors should be displayed:
            if($intConfigDebuglevel >= 1) {
                if(_xmlLoader_ === true) {
                    $strErrormessage = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
                    $strErrormessage .= "<error>".xmlSafeString($this->getMessage())."</error>";
                }
                else {
                    $strErrormessage = "<html><head></head><body><div style=\"border: 1px solid red; padding: 5px; margin: 20px; font-family: arial,verdana; font-size: 12px; \">\n";
        		    $strErrormessage .= "<div style=\"background-color: #cccccc; color: #000000; font-weight: bold; \">An error occured:</div>\n";
        		    $strErrormessage .= $this->getMessage()."<br />";
        		    //$strErrormessage .= basename($this->getFile()) ." in Line ".$this->getLine();

    	            $strErrormessage .= "Please inform the administration about the error above.";
        	        $strErrormessage .= "</div></body></html>";
                }
    	        print $strErrormessage;
    	        //close remaining txs
                class_carrier::getInstance()->getObjDB()->__destruct();
    	        //if error was displayed, stop execution
    	        die();
            }
        }

    }


    /**
     * This method is called, if an exception was thrown in the code but not caught
     * by an try-catch block.
     *
     * @param class_excpetion $objException
     */
    public static function globalExceptionHandler($objException) {
        if (!($objException instanceof class_exception))
            $objException = new class_exception((string)$objException, class_exception::$level_FATALERROR);
        $objException->processException();
    }

    public function getErrorlevel() {
        return $this->intErrorlevel;
    }

    public function setErrorlevel($intErrorlevel) {
        $this->intErrorlevel = $intErrorlevel;
    }
}


//bad coding-style, but define a few more specific exceptions for special cases
class class_authentication_exception extends class_exception {

}

class class_io_exception extends class_exception {

}
?>
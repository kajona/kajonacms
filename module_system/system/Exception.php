<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                        *
********************************************************************************************************/

namespace Kajona\System\System;

use Kajona\System\System\Messageproviders\MessageproviderExceptions;


/**
 * This is the common exception to inherit or to throw in the code.
 * Please DO NOT throw a "plain" exception, otherwise logging and error-handling
 * will not work properly!
 *
 * @package module_system
 */
class Exception extends \Exception
{

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
    private $intDebuglevel;

    /**
     * @param string $strError
     * @param int $intErrorlevel
     */
    public function __construct($strError, $intErrorlevel)
    {
        parent::__construct($strError);
        $this->intErrorlevel = $intErrorlevel;

        //decide, what to print --> get config-value
        // 0: fatal errors will be displayed
        // 1: fatal and regular errors will be displayed
        $this->intDebuglevel = Carrier::getInstance()->getObjConfig()->getDebug("debuglevel");
    }


    /**
     * Used to handle the current exception.
     * Decides, if the execution should be stopped, or continued.
     * Therefore the errorlevel defines the "weight" of the exception
     *
     * @return void
     */
    public function processException()
    {

        //set which POST parameters should read out
        $arrPostParams = array("module", "action", "page", "systemid");

        $objHistory = new History();

        //send an email to the admin?
        $strAdminMail = "";
        try {
            if (Database::getInstance()->getBitConnected()) {
                $strAdminMail = SystemSetting::getConfigValue("_system_admin_email_");
            }
        }
        catch (Exception $objEx) {
        }

        if ($strAdminMail != "") {
            $strMailtext = "";
            $strMailtext .= "The system installed at "._webpath_." registered an error!\n\n";
            $strMailtext .= "The error message was:\n";
            $strMailtext .= "\t".$this->getMessage()."\n\n";
            $strMailtext .= "The level of this error was:\n";
            $strMailtext .= "\t";
            if ($this->getErrorlevel() == self::$level_FATALERROR) {
                $strMailtext .= "FATAL ERROR";
            }
            if ($this->getErrorlevel() == self::$level_ERROR) {
                $strMailtext .= "REGULAR ERROR";
            }
            $strMailtext .= "\n\n";
            $strMailtext .= "File and line number the error was thrown:\n";
            $strMailtext .= "\t".basename($this->getFile())." in line ".$this->getLine()."\n\n";
            $strMailtext .= "Callstack / Backtrace:\n\n";
            $strMailtext .= $this->getTraceAsString();
            $strMailtext .= "\n\n";
            $strMailtext .= "User: ".Carrier::getInstance()->getObjSession()->getUserID()." (".Carrier::getInstance()->getObjSession()->getUsername().")\n";
            $strMailtext .= "Source host: ".getServer("REMOTE_ADDR")." (".@gethostbyaddr(getServer("REMOTE_ADDR")).")\n";
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
            $arrHistory = $objHistory->getArrAdminHistory();
            if (is_array($arrHistory)) {
                foreach ($arrHistory as $intIndex => $strOneUrl) {
                    $strMailtext .= " #".$intIndex.": ".$strOneUrl."\n";
                }
            }
            $strMailtext .= "Portal:\n";
            $arrHistory = $objHistory->getArrPortalHistory();
            if (is_array($arrHistory)) {
                foreach ($arrHistory as $intIndex => $strOneUrl) {
                    $strMailtext .= " #".$intIndex.": ".$strOneUrl."\n";
                }
            }
            $strMailtext .= "\n\n";
            $strMailtext .= "If you don't know what to do, feel free to open a ticket.\n\n";
            $strMailtext .= "For more help visit http://www.kajona.de.\n\n";

            $objMail = new Mail();
            $objMail->setSubject("Error on website "._webpath_." occured!");
            $objMail->setSender($strAdminMail);
            $objMail->setText($strMailtext);
            $objMail->addTo($strAdminMail);
            $objMail->sendMail();


            $objMessageHandler = new MessagingMessagehandler();
            $objMessage = new MessagingMessage();
            $objMessage->setStrBody($strMailtext);
            $objMessage->setObjMessageProvider(new MessageproviderExceptions());
            $objMessageHandler->sendMessageObject($objMessage, new UserGroup(SystemSetting::getConfigValue("_admins_group_id_")));
        }

        if ($this->intErrorlevel == Exception::$level_FATALERROR) {
            //Handle fatal errors.
            $strLogMessage = basename($this->getFile()).":".$this->getLine()." -- ".$this->getMessage();
            Logger::getInstance()->addLogRow($strLogMessage, Logger::$levelError);

            //fatal errors are displayed in every case
            if (_xmlLoader_ === true) {
                $strErrormessage = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
                $strErrormessage .= "<error>".xmlSafeString($this->getMessage())."</error>";
            }
            else {
                $strErrormessage = "<html><head></head><body><div style=\"border: 1px solid red; padding: 5px; margin: 20px; font-family: arial,verdana,sans-serif; font-size: 12px;  \">\n";
                $strErrormessage .= "<div style=\"background-color: #cccccc; color: #000000; font-weight: bold; \">A fatal error occurred:</div>\n";
                $strErrormessage .= "<pre>".(htmlspecialchars($this->getMessage(), ENT_QUOTES, "UTF-8", false))."</pre><br />";

                $strErrormessage .= "Please inform the administration about the error above.";
                $strErrormessage .= "</div></body></html>";

            }
            print $strErrormessage;
            //Execution has to be stopped here!
            if (ResponseObject::getInstance()->getStrStatusCode() == "" || ResponseObject::getInstance()->getStrStatusCode() == HttpStatuscodes::SC_OK) {
                ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_INTERNAL_SERVER_ERROR);
            }

            ResponseObject::getInstance()->sendHeaders();
            die();
        }
        elseif ($this->intErrorlevel == Exception::$level_ERROR) {
            //handle regular errors
            $strLogMessage = basename($this->getFile()).":".$this->getLine()." -- ".$this->getMessage();
            Logger::getInstance()->addLogRow($strLogMessage, Logger::$levelWarning);

            //check, if regular errors should be displayed:
            if ($this->intDebuglevel >= 1) {
                if (_xmlLoader_ === true) {
                    $strErrormessage = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
                    $strErrormessage .= "<error>".xmlSafeString($this->getMessage())."</error>";
                }
                else {
                    $strErrormessage = "<html><head></head><body><div style=\"border: 1px solid red; padding: 5px; margin: 20px; font-family: arial,verdana,sans-serif; font-size: 12px; \">\n";
                    $strErrormessage .= "<div style=\"background-color: #cccccc; color: #000000; font-weight: bold; \">An error occurred:</div>\n";
                    $strErrormessage .= "<pre>".(htmlspecialchars($this->getMessage(), ENT_QUOTES, "UTF-8", false))."</pre><br />";
                    //$strErrormessage .= basename($this->getFile()) ." in Line ".$this->getLine();

                    $strErrormessage .= "Please inform the administration about the error above.";
                    $strErrormessage .= "</div></body></html>";
                }
                print $strErrormessage;
                //if error was displayed, stop execution
                //die();
            }
        }

    }


    /**
     * This method is called, if an exception was thrown in the code but not caught
     * by an try-catch block.
     *
     * @param Exception $objException
     *
     * @return void
     */
    public static function globalExceptionHandler($objException)
    {
        if (!($objException instanceof Exception)) {
            $objException = new Exception((string)$objException, Exception::$level_FATALERROR);
        }
        $objException->processException();
        ResponseObject::getInstance()->sendHeaders();
    }

    /**
     * @return int
     */
    public function getErrorlevel()
    {
        return $this->intErrorlevel;
    }

    /**
     * @param int $intErrorlevel
     *
     * @return void
     */
    public function setErrorlevel($intErrorlevel)
    {
        $this->intErrorlevel = $intErrorlevel;
    }

    /**
     * @param string $intDebuglevel
     *
     * @return void
     */
    public function setIntDebuglevel($intDebuglevel)
    {
        $this->intDebuglevel = $intDebuglevel;
    }

    /**
     * @return string
     */
    public function getIntDebuglevel()
    {
        return $this->intDebuglevel;
    }


}


//bad coding-style, but define a few more specific exceptions for special cases

/**
 * Class class_authentication_exception
 */
class AuthenticationException extends Exception
{

}

/**
 * Class class_io_exception
 */
class IoException extends Exception
{

}

/**
 * @deprecated
 */
class class_authentication_exception extends AuthenticationException
{

}

/**
 * @deprecated
 */
class class_io_exception extends IoException
{

}

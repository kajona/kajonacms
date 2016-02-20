<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Admin;

use Kajona\System\System\AbstractController;
use Kajona\System\System\HttpStatuscodes;
use Kajona\System\System\ResponseObject;
use Kajona\System\System\Wadlgenerator;


/**
 * The login-xml part is able to fire logins or logouts via the xml-interface (e.g. to be used by a REST client).
 * In order to login, create a request schemed like xml.php?admin=1&module=login&action=login
 * Attach the params username, password either via GET params, or even better by POST params).
 *
 * @package module_system
 * @author sidler@mulchprod.de
 *
 * @module login
 * @moduleId _user_modul_id_
 */
class LoginAdminXml extends AdminController implements XmlAdminInterface {

    public function __construct() {
        parent::__construct();

        if($this->getAction() == "list") {
            $this->setAction("login");
        }
    }


    /**
     * This method is just a placeholder to avoid error-flooding of the admins.
     * If the session expires, the browser tries one last time to
     * fetch the number of messages for the user. Since the user is "logged out" by the server,
     * an "not authorized" exception is called - what is correct, but not really required right here.
     *
     * @return string
     *
     */
    protected function actionGetRecentMessages() {
        ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_UNAUTHORIZED);
        return "<error>".$this->getLang("commons_error_permissions")."</error>";
    }


    /**
     * Logs the current user into the system
     *
     * @return string
     */
    protected function actionLogin() {

        if($this->objSession->login($this->getParam("username"), $this->getParam("password"))) {
            //user allowed to access admin?
            if(!$this->objSession->isAdmin()) {
                //no, reset session
                $this->objSession->logout();
            }

            return "<message><success>" . xmlSafeString($this->getLang("login_xml_succeess", "system")) . "</success></message>";
        }
        else {
            ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_UNAUTHORIZED);
            return "<message><error>" . xmlSafeString($this->getLang("login_xml_error", "system")) . "</error></message>";
        }
    }

    /**
     * Ends the session of the current user
     *
     * @return string
     */
    protected function actionLogout() {
        $this->objSession->logout();
        return "<message><success>" . xmlSafeString($this->getLang("logout_xml", "system")) . "</success></message>";
    }


    /**
     * Generates the wadl file for the current module
     *
     * @return string
     * @xml
     */
    protected function actionWADL() {
        $objWadl = new Wadlgenerator("admin", "login");
        $objWadl->addIncludeGrammars("http://apidocs.kajona.de/xsd/message.xsd");

        $objWadl->addMethod(
            true,
            "login",
            array(
                array("username", "xsd:string", true),
                array("password", "xsd:string", true)
            ),
            array(),
            array(
                array("application/xml", "message")
            )
        );

        $objWadl->addMethod(true, "logout", array());
        return $objWadl->getDocument();
    }

}

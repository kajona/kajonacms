<?php
/*"******************************************************************************************************
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_socket.php 3081 2010-01-03 10:14:41Z sidler $                                             *
********************************************************************************************************/


require_once(dirname(__FILE__)."/includes.php");


/**
 * The class_testbase is the common baseclass for all testcases.
 * Triggers the methods required to run proper PHPUnit tests
 *
 * @package modul_system
 * @since 3.4
 * @author sidler@mulchprod.de
 */
class class_testbase extends PHPUnit_Framework_TestCase {

    protected function setUp() {


        $objCarrier = class_carrier::getInstance();
   
        
    }

    /**
     * For the saje of phpunit
     */
    public function testTest() {
        
    }


}

?>
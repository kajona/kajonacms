<?php
/*"******************************************************************************************************
*   (c) 2013-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;

/**
 * Simple dto to hold all relevant params required to open a db connection
 *
 * @author sidler@mulchprod.de
 * @since 5.1
 */
class DbConnectionParams
{

    private $strHost = "";
    private $strUsername = "";
    private $strPass = "";
    private $strDbName = "";
    private $intPort = 0;

    /**
     * DbConnectionParams constructor.
     *
     * @param string $strHost
     * @param string $strUsername
     * @param string $strPass
     * @param string $strDbName
     * @param int $intPort
     */
    public function __construct($strHost, $strUsername, $strPass, $strDbName, $intPort)
    {
        $this->strHost = $strHost;
        $this->strUsername = $strUsername;
        $this->strPass = $strPass;
        $this->strDbName = $strDbName;
        $this->intPort = $intPort;
    }

    /**
     * @return string
     */
    public function getStrHost()
    {
        return $this->strHost;
    }

    /**
     * @return string
     */
    public function getStrUsername()
    {
        return $this->strUsername;
    }

    /**
     * @return string
     */
    public function getStrPass()
    {
        return $this->strPass;
    }

    /**
     * @return string
     */
    public function getStrDbName()
    {
        return $this->strDbName;
    }

    /**
     * @return int
     */
    public function getIntPort()
    {
        return $this->intPort;
    }

    /**
     * @param int $intPort
     */
    public function setIntPort($intPort)
    {
        $this->intPort = $intPort;
    }




}

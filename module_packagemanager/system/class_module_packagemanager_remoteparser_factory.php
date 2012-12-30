<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_packagemanager_remoteparser_factory.php 5154 2012-10-26 08:17:12Z sidler          *
********************************************************************************************************/

/**
 * This factory inspects a remote API result and finds the remote parser implementation being
 * able to handle the result.
 *
 * @package module_packagemanager
 * @author flo@mediaskills.org
 * @since 4.0
 */
class class_module_packagemanager_remoteparser_factory {

    /**
     * Returns the according remote parser implementation for an API result.
     *
     * @param array $remoteResponse The remote API response as returned by <i>json_decode</i>.
     * @param int $intPageNumber
     * @param int $intStart
     * @param int $intEnd
     * @param string $strProviderName
     * @param string $strPagerAddon
     *
     * @return interface_packagemanager_remoteparser
     */
    public static function getRemoteParser(array $remoteResponse, $intPageNumber, $intStart, $intEnd, $strProviderName, $strPagerAddon = "") {

        if(array_key_exists('protocolVersion', $remoteResponse)) {
            if($remoteResponse["protocolVersion"] == 4) {
                return new class_module_packagemanager_remoteparser_v4(
                    $remoteResponse,
                    $intPageNumber,
                    $intStart,
                    $intEnd,
                    $strProviderName,
                    $strPagerAddon
                );
            }
        }

        if (array_key_exists('numberOfTotalItems', $remoteResponse) && array_key_exists('items', $remoteResponse)) {
            return new class_module_packagemanager_remoteparser_v4(
                $remoteResponse,
                $intPageNumber,
                $intStart,
                $intEnd,
                $strProviderName,
                $strPagerAddon
            );
        }

        //fallback: the v4 parser
        return new class_module_packagemanager_remoteparser_v3($remoteResponse);
    }

}
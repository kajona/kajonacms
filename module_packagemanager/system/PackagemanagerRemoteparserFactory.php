<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: PackagemanagerRemoteparserFactory.php 5154 2012-10-26 08:17:12Z sidler          *
********************************************************************************************************/

namespace Kajona\Packagemanager\System;


/**
 * This factory inspects a remote API result and finds the remote parser implementation being
 * able to handle the result.
 *
 * @package module_packagemanager
 * @author flo@mediaskills.org
 * @since 4.0
 */
class PackagemanagerRemoteparserFactory
{

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
     * @return PackagemanagerRemoteparserInterface
     */
    public static function getRemoteParser(array $remoteResponse, $intPageNumber, $intStart, $intEnd, $strProviderName, $strPagerAddon = "")
    {

        if (array_key_exists('protocolVersion', $remoteResponse)) {
            if ($remoteResponse["protocolVersion"] == 4) {
                return new PackagemanagerRemoteparserV4(
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
            return new PackagemanagerRemoteparserV4(
                $remoteResponse,
                $intPageNumber,
                $intStart,
                $intEnd,
                $strProviderName,
                $strPagerAddon
            );
        }

        //fallback: the v4 parser
        return new PackagemanagerRemoteparserV3($remoteResponse);
    }

}
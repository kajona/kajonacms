<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/


namespace Kajona\Guestbook\System;

use Kajona\System\System\FilterBase;

/**
 * Class GuestbookFilter
 *
 * @package Kajona\Guestbook\System
 * @module guestbook
 */
class GuestbookPostFilter extends FilterBase
{
    /**
     * @var string
     * @tableColumn guestbook_post.guestbook_post_name
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     * @fieldLabel form_guestbook_guestbookpostname
     */
    private $strGuestbookPostName = "";

    /**
     * @var string
     * @tableColumn guestbook_post.guestbook_post_email
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     * @fieldLabel form_guestbook_guestbookpostemail
     */
    private $strGuestbookPostEmail = "";

    /**
     * @var string
     * @tableColumn guestbook_post.guestbook_post_page
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     * @fieldLabel form_guestbook_guestbookpostpage
     */
    private $strGuestbookPostPage = "";

    /**
     * @var string
     * @tableColumn guestbook_post.guestbook_post_text
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     * @fieldLabel form_guestbook_guestbookposttext
     */
    private $strGuestbookPostText = "";

    /**
     * @var string
     * @tableColumn system.system_status
     * @fieldType Kajona\System\Admin\Formentries\FormentryDropdown
     * @fieldDDValues [0 => post_status_0],[1 => post_status_1]
     * @fieldLabel form_guestbook_guestbookpoststatus
     */
    private $intStatus;

    /**
     * @return string
     */
    public function getStrGuestbookPostEmail()
    {
        return $this->strGuestbookPostEmail;
    }

    /**
     * @param string $strGuestbookPostEmail
     */
    public function setStrGuestbookPostEmail($strGuestbookPostEmail)
    {
        $this->strGuestbookPostEmail = $strGuestbookPostEmail;
    }

    /**
     * @return string
     */
    public function getStrGuestbookPostName()
    {
        return $this->strGuestbookPostName;
    }

    /**
     * @param string $strGuestbookPostName
     */
    public function setStrGuestbookPostName($strGuestbookPostName)
    {
        $this->strGuestbookPostName = $strGuestbookPostName;
    }

    /**
     * @return string
     */
    public function getStrGuestbookPostPage()
    {
        return $this->strGuestbookPostPage;
    }

    /**
     * @param string $strGuestbookPostPage
     */
    public function setStrGuestbookPostPage($strGuestbookPostPage)
    {
        $this->strGuestbookPostPage = $strGuestbookPostPage;
    }

    /**
     * @return string
     */
    public function getStrGuestbookPostText()
    {
        return $this->strGuestbookPostText;
    }

    /**
     * @param string $strGuestbookPostText
     */
    public function setStrGuestbookPostText($strGuestbookPostText)
    {
        $this->strGuestbookPostText = $strGuestbookPostText;
    }

    /**
     * @return string
     */
    public function getIntStatus()
    {
        return $this->intStatus;
    }

    /**
     * @param string $intStatus
     */
    public function setIntStatus($intStatus)
    {
        $this->intStatus = $intStatus;
    }

    
    
}
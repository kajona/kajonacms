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
     * @fieldType text
     * @fieldLabel form_guestbook_guestbookpostname
     */
    private $strGuestbookPostName = "";

    /**
     * @var string
     * @tableColumn guestbook_post.guestbook_post_email
     * @fieldType text
     * @fieldLabel form_guestbook_guestbookpostemail
     */
    private $strGuestbookPostEmail = "";

    /**
     * @var string
     * @tableColumn guestbook_post.guestbook_post_page
     * @fieldType text
     * @fieldLabel form_guestbook_guestbookpostpage
     */
    private $strGuestbookPostPage = "";

    /**
     * @var string
     * @tableColumn guestbook_post.guestbook_post_text
     * @fieldType text
     * @fieldLabel form_guestbook_guestbookposttext
     */
    private $strGuestbookPostText = "";



    public function getArrModule($strKey = "")
    {
        return "guestbook";
    }

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

    
    
}
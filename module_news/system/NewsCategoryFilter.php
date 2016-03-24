<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/


namespace Kajona\News\System;

use Kajona\System\System\FilterBase;

class NewsCategoryFilter extends FilterBase
{
    /**
     * @tableColumn news_category.news_cat_title
     * @fieldType text
     */
    private $strTitle;


    public function getArrModule($strKey = "")
    {
        return "news";
    }

    /**
     * @return mixed
     */
    public function getStrTitle()
    {
        return $this->strTitle;
    }

    /**
     * @param mixed $strTitle
     */
    public function setStrTitle($strTitle)
    {
        $this->strTitle = $strTitle;
    }
}
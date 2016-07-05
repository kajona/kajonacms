<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;

use RuntimeException;


/**
 * Class to calculate date ranges
 *
 * @package module_system
 * @author christoph.kappestein@gmail.com
 * @since 4.7
 */
class DateRange
{
    /**
     * The method generates an array containing start and enddates. Basically the method start at the startdate and
     * adds the interval to the startdate until the enddate is reached. In the following an example:
     *
     * 01.01         02.01         03.01         04.01         05.01
     * |-------------|-------------|-------------|-------------|
     *        | <----------------------------------> |
     *        01.01 12:00                            04.01 08:00
     *
     * In this case we get the following array
     *
     * array(
     *   array(new Date("2015-01-01 12:00:00"), new Date("2015-02-01 11:59:59"))
     *   array(new Date("2015-02-01 12:00:00"), new Date("2015-03-01 11:59:59"))
     * )
     *
     * The last period 2015-03-01 12:00:00 - 2015-04-01 11:59:59 is not included since the date 2015-04-01 11:59:59 is
     * greater then the enddate
     *
     * @param Date $objStartDate
     * @param Date $objEndDate
     * @param DatePeriodEnum $objInterval
     * @return array
     */
    public static function getDateRange(Date $objStartDate, Date $objEndDate, DatePeriodEnum $objInterval)
    {
        $objDateHelper = new DateHelper();
        $objTmpDate = clone $objStartDate;
        $arrResult = array();

        while ($objTmpDate->getTimeInOldStyle() <= $objEndDate->getTimeInOldStyle()) {
            $objRangeStart = $objTmpDate;
            $objRangeEnd = $objDateHelper->calcDateRelativeFormatString(self::addInterval($objRangeStart, $objInterval), "-1 second");

            // add only if the end date is inside the global date range
            if ($objRangeEnd->getTimeInOldStyle() <= $objEndDate->getTimeInOldStyle()) {
                $arrResult[] = array($objRangeStart, $objRangeEnd);
            }

            $objTmpDate = self::addInterval($objTmpDate, $objInterval);
        }

        return $arrResult;
    }

    /**
     * Returns only complete weeks, months and years. The method modifies the start and end date borders to match
     * a complete period. Also the start date is set to 00:00:00 and the end date to 23:59:59. For example:
     *
     * 06.07         07.07         ...           15.07         16.07
     * |-------------|-------------|-------------|-------------|
     *        | <----------------------------------> |
     *        06.07 12:00                            15.07 08:00
     *
     * In this case we get the following array
     *
     * array(
     *   array(new Date("2016-07-04 00:00:00"), new Date("2016-07-10 23:59:59"))
     *   array(new Date("2016-07-11 00:00:00"), new Date("2016-07-17 23:59:59"))
     * )
     *
     * @param Date $objStartDate
     * @param Date $objEndDate
     * @param DatePeriodEnum $objInterval
     * @return array
     */
    public static function getDateRangeComplete(Date $objStartDate, Date $objEndDate, DatePeriodEnum $objInterval)
    {
        $objTmpStartDate = clone $objStartDate;
        $objTmpEndDate = clone $objEndDate;

        if ($objInterval->equals(DatePeriodEnum::WEEK())) {
            $objTmpStartDate->setBeginningOfDay();
            $intCurrentWeek = date('W', $objTmpStartDate->getTimeInOldStyle());
            while ($intCurrentWeek == date('W', $objTmpStartDate->getTimeInOldStyle())) {
                $objTmpStartDate->setPreviousDay();
            }
            $objTmpStartDate->setNextDay();

            $objTmpEndDate->setEndOfDay();
            $intCurrentWeek = date('W', $objTmpEndDate->getTimeInOldStyle());
            while ($intCurrentWeek == date('W', $objTmpEndDate->getTimeInOldStyle())) {
                $objTmpEndDate->setNextDay();
            }
            $objTmpEndDate->setPreviousDay();
        } elseif ($objInterval->equals(DatePeriodEnum::MONTH())) {
            $objTmpStartDate->setBeginningOfDay()->setIntDay(1);
            $objTmpEndDate->setEndOfDay()->setIntDay(1)->setNextMonth()->setPreviousDay();
        } elseif ($objInterval->equals(DatePeriodEnum::YEAR())) {
            $objTmpStartDate->setBeginningOfDay()->setIntDay(1)->setIntMonth(1);
            $objTmpEndDate->setEndOfDay()->setIntDay(1)->setIntMonth(1)->setNextYear()->setPreviousDay();
        }

        return self::getDateRange($objTmpStartDate, $objTmpEndDate, $objInterval);
    }

    /**
     * Transforms the result of the getDateRange format to another format
     *
     * @param array $arrRanges
     * @return array
     */
    public static function transformToOldFormat(array $arrRanges)
    {
        $strDateFormat = Carrier::getInstance()->getObjLang()->getLang("dateStyleLong", "system");
        $arrResult = array(
            'start_dates' => array(),
            'end_dates' => array(),
        );

        foreach ($arrRanges as $arrRange) {
            list($objStartDate, $objEndDate) = $arrRange;

            $arrResult['start_dates'][] = date($strDateFormat, $objStartDate->getTimeInOldStyle());
            $arrResult['end_dates'][] = date($strDateFormat, $objEndDate->getTimeInOldStyle());
        }

        return $arrResult;
    }

    public static function getIntervalByString($strInterval)
    {
        return call_user_func(array('Kajona\System\System\DatePeriodEnum', strtoupper($strInterval)));
    }

    /**
     * Adds a specific interval to the provided date. Returns a new date object
     *
     * @param Date $objDate
     * @param DatePeriodEnum $objInterval
     * @return Date
     */
    private static function addInterval(Date $objDate, DatePeriodEnum $objInterval)
    {
        $objDate = clone $objDate;
        if ($objInterval->equals(DatePeriodEnum::DAY())) {
            $objDate->setNextDay();
        } elseif ($objInterval->equals(DatePeriodEnum::WEEK())) {
            $objDate->setNextWeek();
        } elseif ($objInterval->equals(DatePeriodEnum::MONTH())) {
            $objDate->setNextMonth();
        } elseif ($objInterval->equals(DatePeriodEnum::QUARTER())) {
            for ($intI = 0; $intI < 3; $intI++) {
                $objDate->setNextMonth();
            }
        } elseif ($objInterval->equals(DatePeriodEnum::HALFYEAR())) {
            for ($intI = 0; $intI < 6; $intI++) {
                $objDate->setNextMonth();
            }
        } elseif ($objInterval->equals(DatePeriodEnum::YEAR())) {
            $objDate->setNextYear();
        } else {
            throw new RuntimeException('Invalid interval');
        }

        return $objDate;
    }
}

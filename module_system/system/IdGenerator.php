<?php
/*"******************************************************************************************************
*   (c) 2010-2016 ARTEMEON                                                                              *
********************************************************************************************************/

declare(strict_types=1);

namespace Kajona\System\System;


/**
 * Model for a idgenerator record object itself
 *
 * @package module_agp_commons
 * @author christoph.kappestein@artemeon.de
 * @targetTable idgenerator.id
 *
 * @module system
 * @moduleId _system_modul_id_
 */
class IdGenerator extends Model implements ModelInterface
{

    /**
     * @var string
     * @tableColumn idgenerator.generator_key
     * @tableColumnDatatype char20
     * @tableColumnIndex
     */
    private $strKey = "";

    /**
     * @var integer
     * @tableColumn idgenerator.generator_count
     * @tableColumnDatatype int
     */
    private $intCount = "";

    /**
     * Generates an id for an specific key. Creates a new entry if the key does not exist
     *
     * @param string $strKey
     *
     * @return integer
     */
    public static function generateNextId(string $strKey): int
    {
        $objORM = new OrmObjectlist();
        $objORM->addWhereRestriction(new OrmPropertyCondition("strKey", OrmComparatorEnum::Equal(), $strKey));

        $arrResult = $objORM->getObjectList(get_called_class());

        if (empty($arrResult)) {
            $intId = 1;

            $objIdGenerator = new IdGenerator();
            $objIdGenerator->setStrKey($strKey);
            $objIdGenerator->setIntCount($intId);
            $objIdGenerator->updateObjectToDb();
        } else {
            /* @var IdGenerator $objIdGenerator */
            $objIdGenerator = current($arrResult);
            $intId = $objIdGenerator->getIntCount() + 1;
            $objIdGenerator->setIntCount($intId);
            $objIdGenerator->updateObjectToDb();
        }

        return $intId;
    }

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin(). Alternatively, you may return an array containing
     *         [the image name, the alt-title]
     */
    public function getStrIcon(): string
    {
        return "icon_workflow";
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription(): string
    {
        return $this->intCount."";
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName(): string
    {
        return StringUtil::truncate($this->strKey, 150);
    }

    /**
     * @return string
     */
    public function getStrKey(): string
    {
        return $this->strKey;
    }

    /**
     * @param string $strKey
     */
    public function setStrKey(string $strKey)
    {
        $this->strKey = $strKey;
    }

    /**
     * @return integer
     */
    public function getIntCount(): int
    {
        return $this->intCount;
    }

    /**
     * @param integer $intCount
     */
    public function setIntCount(int $intCount)
    {
        $this->intCount = $intCount;
    }

}

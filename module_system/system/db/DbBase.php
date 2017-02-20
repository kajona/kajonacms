<?php
/*"******************************************************************************************************
*   (c) 2013-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System\Db;

use Kajona\System\System\Database;


/**
 * Base class for all database-drivers, holds methods to be used by all drivers
 *
 * @package module_system
 * @since 4.5
 * @author sidler@mulchprod.de
 */
abstract class DbBase implements DbDriverInterface
{

    protected $arrStatementsCache = array();

    /**
     * @var int
     */
    protected $intAffectedRows = 0;


    /**
     * Detects if the current installation runs on win or unix
     * @return bool
     */
    protected function isWinOs()
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    /**
     * Default implementation to detect if a driver handles compression.
     * By default, db-drivers us a piped gzip / gunzip command when creating / restoring dumps on unix.
     * If running on windows, the Database class handles the compression / decompression.
     *
     * @return bool
     */
    public function handlesDumpCompression()
    {
        return !$this->isWinOs();
    }

    /**
     * Renames a table
     *
     * @param $strOldName
     * @param $strNewName
     *
     * @return bool
     * @since 4.6
     */
    public function renameTable($strOldName, $strNewName)
    {
        return $this->_pQuery("ALTER TABLE ".($this->encloseTableName($strOldName))." RENAME TO ".($this->encloseTableName($strNewName)), array());
    }

    /**
     * Renames a single column of the table
     *
     * @param $strTable
     * @param $strOldColumnName
     * @param $strNewColumnName
     * @param $strNewDatatype
     *
     * @return bool
     * @since 4.6
     */
    public function changeColumn($strTable, $strOldColumnName, $strNewColumnName, $strNewDatatype)
    {
        return $this->_pQuery("ALTER TABLE ".($this->encloseTableName($strTable))." CHANGE COLUMN ".($this->encloseColumnName($strOldColumnName)." ".$this->encloseColumnName($strNewColumnName)." ".$this->getDatatype($strNewDatatype)), array());
    }

    /**
     * Adds a column to a table
     *
     * @param $strTable
     * @param $strColumn
     * @param $strDatatype
     *
     * @return bool
     * @since 4.6
     */
    public function addColumn($strTable, $strColumn, $strDatatype, $bitNull = null, $strDefault = null)
    {
        $strQuery = "ALTER TABLE ".($this->encloseTableName($strTable))." ADD ".($this->encloseColumnName($strColumn)." ".$this->getDatatype($strDatatype));

        if ($bitNull !== null) {
            $strQuery .= $bitNull ? " NULL" : " NOT NULL";
        }

        if ($strDefault !== null) {
            $strQuery .= " DEFAULT ".$strDefault;
        }

        return $this->_pQuery($strQuery, array());
    }

    /**
     * Removes a column from a table
     *
     * @param $strTable
     * @param $strColumn
     *
     * @return bool
     * @since 4.6
     */
    public function removeColumn($strTable, $strColumn)
    {
        return $this->_pQuery("ALTER TABLE ".($this->encloseTableName($strTable))." DROP COLUMN ".($this->encloseColumnName($strColumn)), array());
    }


    /**
     * Creates a single query in order to insert multiple rows at one time.
     * For most databases, this will create s.th. like
     * INSERT INTO $strTable ($arrColumns) VALUES (?, ?), (?, ?)...
     * Please note that this method is used to create the query itself, based on the Kajona-internal syntax.
     * The query is fired to the database by Database
     *
     * @param string $strTable
     * @param string[] $arrColumns
     * @param array $arrValueSets
     * @param Database $objDb
     *
     * @return bool
     */
    public function triggerMultiInsert($strTable, $arrColumns, $arrValueSets, Database $objDb)
    {

        $arrPlaceholder = array();
        $arrSafeColumns = array();

        foreach ($arrColumns as $strOneColumn) {
            $arrSafeColumns[] = $this->encloseColumnName($strOneColumn);
            $arrPlaceholder[] = "?";
        }
        $strPlaceholder = "(".implode(",", $arrPlaceholder).")";

        $arrPlaceholderSets = array();
        $arrParams = array();

        foreach ($arrValueSets as $arrOneSet) {
            $arrPlaceholderSets[] = $strPlaceholder;
            $arrParams = array_merge($arrParams, array_values($arrOneSet));
        }

        $strQuery = "INSERT INTO ".$this->encloseTableName($strTable)." (".implode(",", $arrSafeColumns).") VALUES ".implode(",", $arrPlaceholderSets);

        return $objDb->_pQuery($strQuery, $arrParams);
    }


    /**
     * Dummy implementation, using a select & insert combination. This is not threadsafe, so the
     * make sure to implement it in each driver specifically.
     * Nevertheless, this may be used as a fallback.
     *
     * @inheritDoc
     */
    public function insertOrUpdate($strTable, $arrColumns, $arrValues, $arrPrimaryColumns)
    {

        $arrPlaceholder = array();
        $arrMappedColumns = array();

        $arrUpdateKeyValue = array();
        $arrUpdateKeyValueKey = array();
        $arrUpdateParams = array();
        $arrUpdateKeyParams = array();

        $arrPrimaryCompares = array();
        $arrPrimaryValues = array();

        foreach ($arrColumns as $intKey => $strOneCol) {
            $arrPlaceholder[] = "?";
            $arrMappedColumns[] = $this->encloseColumnName($strOneCol);

            if (in_array($strOneCol, $arrPrimaryColumns)) {
                $arrPrimaryCompares[] = $strOneCol." = ? ";
                $arrPrimaryValues[] = $arrValues[$intKey];

                $arrUpdateKeyValueKey[] = $strOneCol." = ? ";
                $arrUpdateKeyParams[] = $arrValues[$intKey];
            } else {
                $arrUpdateKeyValue[] = $strOneCol." = ? ";
                $arrUpdateParams[] = $arrValues[$intKey];
            }
        }

        $arrRow = $this->getPArraySection("SELECT COUNT(*) FROM ".$this->encloseTableName(_dbprefix_.$strTable)." WHERE ".implode(" AND ", $arrPrimaryCompares), $arrPrimaryValues, 0, 1);

        if ($arrRow === false) {
            return false;
        }

        $arrSingleRow = isset($arrRow[0]) ? $arrRow[0] : null;

        if ($arrSingleRow === null || $arrSingleRow["COUNT(*)"] == "0") {
            $strQuery = "INSERT INTO ".$this->encloseTableName(_dbprefix_.$strTable)." (".implode(", ", $arrMappedColumns).") VALUES (".implode(", ", $arrPlaceholder).")";
            return $this->_pQuery($strQuery, $arrValues);
        } else {
            $strQuery = "UPDATE ".$this->encloseTableName(_dbprefix_.$strTable)." SET ".implode(", ", $arrUpdateKeyValue)." WHERE ".implode(" AND ", $arrUpdateKeyValueKey);
            return $this->_pQuery($strQuery, array_merge($arrUpdateParams, $arrUpdateKeyParams));
        }
    }

    /**
     * Returns just a part of a recordset, defined by the start- and the end-rows,
     * defined by the params. Makes use of prepared statements.
     * <b>Note:</b> Use array-like counters, so the first row is startRow 0 whereas
     * the n-th row is the (n-1)th key!!!
     *
     * @param string $strQuery
     * @param array $arrParams
     * @param int $intStart
     * @param int $intEnd
     *
     * @return array
     * @since 3.4
     */
    public function getPArraySection($strQuery, $arrParams, $intStart, $intEnd)
    {
        return $this->getPArray($this->appendLimitExpression($strQuery, $intStart, $intEnd), $arrParams);
    }

    /**
     * Allows the db-driver to add database-specific surrounding to column-names.
     * E.g. needed by the mysql-drivers
     *
     * @param string $strColumn
     *
     * @return string
     */
    public function encloseColumnName($strColumn)
    {
        return $strColumn;
    }

    /**
     * Allows the db-driver to add database-specific surrounding to table-names.
     *
     * @param string $strTable
     *
     * @return string
     */
    public function encloseTableName($strTable)
    {
        return $strTable;
    }

    /**
     * A method triggered in special cases in order to
     * have even the caches stored at the db-driver being flushed.
     * This could get important in case of schema updates since precompiled queries may get invalid due
     * to updated table definitions.
     *
     * @return void
     */
    public function flushQueryCache()
    {
        $this->arrStatementsCache = array();
    }

    /**
     * @param string $strValue
     *
     * @return string
     */
    public function escape($strValue)
    {
        return $strValue;
    }

    /**
     * @inheritdoc
     */
    public function getIntAffectedRows()
    {
        return $this->intAffectedRows;
    }

    /**
     * @inheritdoc
     */
    public function appendLimitExpression($strQuery, $intStart, $intEnd)
    {
        //calculate the end-value: mysql limit: start, nr of records, so:
        $intEnd = $intEnd - $intStart + 1;
        //add the limits to the query

        return $strQuery." LIMIT ".$intStart.", ".$intEnd;
    }
}


<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System\Db;

use Kajona\System\System\Database;
use Kajona\System\System\DbConnectionParams;
use Kajona\System\System\DbDatatypes;
use Kajona\System\System\Exception;
use Kajona\System\System\Logger;
use Kajona\System\System\StringUtil;

/**
 * DbSqlsrv
 *
 * @package module_system
 * @author christoph.kappestein@gmail.com
 * @since 3.4.1
 */
class DbSqlsrv extends DbBase
{

    private $linkDB; //DB-Link
    /** @var DbConnectionParams  */
    private $objCfg = null;

    private $strDumpBin = "exp"; // Binary to dump db (if not in path, add the path here)
    // /usr/lib/oracle/xe/app/oracle/product/10.2.0/server/bin/
    private $strRestoreBin = "imp"; //Binary to restore db (if not in path, add the path here)

    private $bitTxOpen = false;

    /**
     * @inheritdoc
     */
    public function dbconnect(DbConnectionParams $objParams)
    {
        if ($objParams->getIntPort() == "" || $objParams->getIntPort() == 0) {
            $objParams->setIntPort(1433);
        }

        $this->objCfg = $objParams;

        $this->linkDB = sqlsrv_connect($this->objCfg->getStrHost(), [
            "UID" => $this->objCfg->getStrUsername(),
            "PWD" => $this->objCfg->getStrPass(),
            "Database" => $this->objCfg->getStrDbName(),
            "CharacterSet" => "UTF-8",
        ]);

        if ($this->linkDB === false) {
            throw new Exception("Error connecting to database", Exception::$level_FATALERROR);
        }
    }

    /**
     * Closes the connection to the database
     *
     * @return void
     */
    public function dbclose()
    {
        sqlsrv_close($this->linkDB);
    }

    /**
     * Sends a prepared statement to the database. All params must be represented by the ? char.
     * The params themself are stored using the second params using the matching order.
     *
     * @param string $strQuery
     * @param array $arrParams
     *
     * @return bool
     * @since 3.4
     */
    public function _pQuery($strQuery, $arrParams)
    {
        $objStatement = sqlsrv_prepare($this->linkDB, $strQuery, array_values($arrParams));
        if ($objStatement === false) {
            return false;
        }


        $bitResult = sqlsrv_execute($objStatement);

        if (!$bitResult) {
            return false;
        }

        $this->intAffectedRows = sqlsrv_num_rows($objStatement);

        sqlsrv_free_stmt($objStatement);
        return $bitResult;
    }

    /**
     * This method is used to retrieve an array of resultsets from the database using
     * a prepared statement
     *
     * @param string $strQuery
     * @param array $arrParams
     *
     * @since 3.4
     * @return array
     */
    public function getPArray($strQuery, $arrParams)
    {
        $arrReturn = array();
        $intCounter = 0;

        $objStatement = sqlsrv_query($this->linkDB, $strQuery, $arrParams);

        if ($objStatement === false) {
            return false;
        }

        while ($arrRow = sqlsrv_fetch_array($objStatement, SQLSRV_FETCH_ASSOC)) {
            $arrRow = $this->parseResultRow($arrRow);
            $arrReturn[$intCounter++] = $arrRow;
        }

        sqlsrv_free_stmt($objStatement);

        return $arrReturn;
    }

    /**
     * Returns the last error reported by the database.
     * Is being called after unsuccessful queries
     *
     * @return string
     */
    public function getError()
    {
        $arrErrors = sqlsrv_errors();
        return print_r($arrErrors, true);
    }

    /**
     * Returns ALL tables in the database currently connected to
     *
     * @return mixed
     */
    public function getTables()
    {
        $arrTemp = $this->getPArray("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE='BASE TABLE'", array());

        foreach ($arrTemp as $intKey => $strValue) {
            $arrTemp[$intKey]["name"] = StringUtil::toLowerCase($strValue["table_name"]);
        }
        return $arrTemp;
    }

    /**
     * Looks up the columns of the given table.
     * Should return an array for each row consting of:
     * array ("columnName", "columnType")
     *
     * @param string $strTableName
     *
     * @return array
     */
    public function getColumnsOfTable($strTableName)
    {
        $arrReturn = array();
        $arrTemp = $this->getPArray("SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ?", array(strtoupper($strTableName)));

        if (empty($arrTemp)) {
            return array();
        }

        foreach ($arrTemp as $arrOneColumn) {
            $arrReturn[] = array(
                "columnName" => strtolower($arrOneColumn["column_name"]),
                "columnType" => ($arrOneColumn["data_type"] == "integer" ? "int" : strtolower($arrOneColumn["data_type"])),
            );

        }

        return $arrReturn;
    }

    /**
     * Returns the db-specific datatype for the kajona internal datatype.
     * Currently, this are
     *      int
     *      long
     *      double
     *      char10
     *      char20
     *      char100
     *      char254
     *      char500
     *      text
     *      longtext
     *
     * @param string $strType
     *
     * @return string
     */
    public function getDatatype($strType)
    {
        $strReturn = "";

        if ($strType == DbDatatypes::STR_TYPE_INT) {
            $strReturn .= " INT ";
        } elseif ($strType == DbDatatypes::STR_TYPE_LONG) {
            $strReturn .= " BIGINT ";
        } elseif ($strType == DbDatatypes::STR_TYPE_DOUBLE) {
            $strReturn .= " FLOAT( 24 ) ";
        } elseif ($strType == DbDatatypes::STR_TYPE_CHAR10) {
            $strReturn .= " VARCHAR( 10 ) ";
        } elseif ($strType == DbDatatypes::STR_TYPE_CHAR20) {
            $strReturn .= " VARCHAR( 20 ) ";
        } elseif ($strType == DbDatatypes::STR_TYPE_CHAR100) {
            $strReturn .= " VARCHAR( 100 ) ";
        } elseif ($strType == DbDatatypes::STR_TYPE_CHAR254) {
            $strReturn .= " VARCHAR( 280 ) ";
        } elseif ($strType == DbDatatypes::STR_TYPE_CHAR500) {
            $strReturn .= " VARCHAR( 500 ) ";
        } elseif ($strType == DbDatatypes::STR_TYPE_TEXT) {
            $strReturn .= " TEXT ";
        } elseif ($strType == DbDatatypes::STR_TYPE_LONGTEXT) {
            $strReturn .= " TEXT ";
        } else {
            $strReturn .= " VARCHAR( 254 ) ";
        }

        return $strReturn;
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
        $bitReturn = $this->_pQuery("ALTER TABLE ".($this->encloseTableName($strTable))." RENAME COLUMN ".($this->encloseColumnName($strOldColumnName)." TO ".$this->encloseColumnName($strNewColumnName)), array());
        return $bitReturn && $this->_pQuery("ALTER TABLE ".$this->encloseTableName($strTable)." MODIFY ( ".$this->encloseColumnName($strNewColumnName)." ".$this->getDatatype($strNewDatatype)." )", array());
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

        if ($strDefault !== null) {
            $strQuery .= " DEFAULT ".$strDefault;
        }

        if ($bitNull !== null) {
            $strQuery .= $bitNull ? " NULL" : " NOT NULL";
        }

        return $this->_pQuery($strQuery, array());
    }

    /**
     * Used to send a create table statement to the database
     * By passing the query through this method, the driver can
     * add db-specific commands.
     * The array of fields should have the following structure
     * $array[string columnName] = array(string datatype, boolean isNull [, default (only if not null)])
     * whereas datatype is one of the following:
     *         int
     *         long
     *         double
     *         char10
     *         char20
     *         char100
     *         char254
     *      char500
     *         text
     *      longtext
     *
     * @param string $strName
     * @param array $arrFields array of fields / columns
     * @param array $arrKeys array of primary keys
     * @param array $arrIndices array of additional indices
     * @param bool $bitTxSafe Should the table support transactions?
     *
     * @return bool
     */
    public function createTable($strName, $arrFields, $arrKeys, $arrIndices = array(), $bitTxSafe = true)
    {
        $strQuery = "";

        //loop over existing tables to check, if the table already exists
        $arrTables = $this->getTables();
        foreach ($arrTables as $arrOneTable) {
            if ($arrOneTable["name"] == $strName) {
                return true;
            }
        }

        //build the oracle code
        $strQuery .= "CREATE TABLE ".$strName." ( \n";

        //loop the fields
        foreach ($arrFields as $strFieldName => $arrColumnSettings) {
            $strQuery .= " ".$strFieldName." ";

            $strQuery .= $this->getDatatype($arrColumnSettings[0]);

            //any default?
            if (isset($arrColumnSettings[2])) {
                $strQuery .= "DEFAULT ".$arrColumnSettings[2]." ";
            }

            //nullable?
            if ($arrColumnSettings[1] === true) {
                $strQuery .= " NULL ";
            } else {
                $strQuery .= " NOT NULL ";
            }

            $strQuery .= " , \n";

        }

        //primary keys
        $strQuery .= " CONSTRAINT pk_".generateSystemid()." primary key ( ".implode(" , ", $arrKeys)." ) \n";
        $strQuery .= ") ";

        $bitCreate = $this->_pQuery($strQuery, array());

        if ($bitCreate && count($arrIndices) > 0) {
            foreach ($arrIndices as $strOneIndex) {
                if (is_array($strOneIndex)) {
                    $strQuery = "CREATE INDEX ix_".generateSystemid()." ON ".$strName." ( ".implode(", ", $strOneIndex).") ";
                } else {
                    $strQuery = "CREATE INDEX ix_".generateSystemid()." ON ".$strName." ( ".$strOneIndex.") ";
                }
                $bitCreate = $bitCreate && $this->_pQuery($strQuery, array());
            }
        }

        return $bitCreate;
    }

    /**
     * Starts a transaction
     *
     * @return void
     */
    public function transactionBegin()
    {
        sqlsrv_begin_transaction($this->linkDB);
        $this->bitTxOpen = true;
    }

    /**
     * Ends a successful operation by committing the transaction
     *
     * @return void
     */
    public function transactionCommit()
    {
        sqlsrv_commit($this->linkDB);
        $this->bitTxOpen = false;
    }

    /**
     * Ends a non-successful transaction by using a rollback
     *
     * @return void
     */
    public function transactionRollback()
    {
        sqlsrv_rollback($this->linkDB);
        $this->bitTxOpen = false;
    }

    /**
     * @return array|mixed
     */
    public function getDbInfo()
    {
        return sqlsrv_server_info($this->linkDB);
    }


    //--- DUMP & RESTORE ------------------------------------------------------------------------------------


    /**
     * @inheritdoc
     */
    public function handlesDumpCompression()
    {
        return false;
    }

    /**
     * Dumps the current db
     *
     * @param string $strFilename
     * @param array $arrTables
     *
     * @return bool
     */
    public function dbExport(&$strFilename, $arrTables)
    {
        // @TODO implement
        return false;
    }

    /**
     * Imports the given db-dump to the database
     *
     * @param string $strFilename
     *
     * @return bool
     */
    public function dbImport($strFilename)
    {
        // @TODO implement
        return false;
    }

    /**
     * converts a result-row. changes all keys to lower-case keys again
     *
     * @param array $arrRow
     * @return array
     */
    private function parseResultRow(array $arrRow)
    {
        $arrRow = array_change_key_case($arrRow, CASE_LOWER);
        if (isset($arrRow["count(*)"])) {
            $arrRow["COUNT(*)"] = $arrRow["count(*)"];
        }

        return $arrRow;
    }

    /**
     * @inheritdoc
     */
    public function appendLimitExpression($strQuery, $intStart, $intEnd)
    {
        $intLength = $intEnd - $intStart + 1;

        return $strQuery." OFFSET {$intStart} ROWS FETCH NEXT {$intLength} ROWS ONLY";
    }
}


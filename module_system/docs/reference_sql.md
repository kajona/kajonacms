#Reference: SQL

While working with different database vendors we have gathered some experience
about the SQL difference of each vendor. This guide contains common tips to help 
writing SQL which is compatible to the following database vendors: sqlite, mysql, 
postgres, oracle, mssql.

* Oracle: The table name length must not exceed 30 characters. Keep in mind that
  `_dbprefix_` adds also some characters to the table name so it is recommended that 
  the actual table name should not exceed 20 characters.
* Mssql: When using aggregate functions you must specify an alias otherwise the result 
  of the function is placed in the result array as empty key
* Mssql: Spalten vom Typ `text` können nicht sortiert werden. Ein Workaround ist: 
  `ORDER BY CAST(TEXT_COLUMN as VARCHAR(100))`


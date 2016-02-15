#Reference: ORM

Introduced in Kajona V4.6, the framework comes with a new object relational mapper.
The mapper is used to abstract the database from the business object. In other words, the OR mapper is used to generate SQL code on the fly or based on annotations instead of writing boilerplate sql-code (SELECT, INSERT, UPDATE, CREATE TABLE).

The OR mapper is split into several components, based on the responsibility:

* A set of annotations
Used to parameterize an object
 
* The object list handler
Building queries to fetch a list of objects or to count the resultset of a query.
 
* The object init handler
Responsible of initializing objects based on values from the database, so mapping the database values to object properties.
 
* The object update handler
Takes care of synchronizing an objects’ state back to the database, so mapping the object properties to database values.
 
* The schema-manager
Building a database-schema (aka table) out an objects properties an annotations
 
* The orm-rowcache

Used internally to cache database-requests, mainly to boost performance.
The OR mapper and nearly all components of the mapper are controlled and initialized based on an objects properties. So whenever you want to work with the mapper, you need to parameterize the object with annotations.


##ORM Annotations

In order to enable a class for the OR mapper, you only need to setup a few params.
Firstly, the properties of the class should map to a target-table. Therefore, the @targetTable annotation is used at class-level:

	/**
	* @targetTable object.object_id
	*/
	class class_basic_object {
	
	}
	
The syntax is ``tablename.primary_key``. Please make sure to name both, the primary key will be used by the OR mapper to build queries and joins. There’s no need to add a property for your primary key, this is all handled internally.
 
Still missing are the properties themselves. A single property is mapped to a single column of the linked target-table. Let’s have a look at an example:

	/**
	* @targetTable object.object_id
	*/
	class class_basic_object {
	
	/**
	* @tableColumn object.property1
	* @tableColumnDatatype text
	*/
	private $strProperty1 = "";
	
	
	/**
	* @tableColumn object.anotherproperty
	* @tableColumnDatatype int
	*/
	private $intProperty2 = 0;
	}

The are two mandatory annotations:

``@tableColumn``: The name of the column the value is written to and read from

``@tableColumnDatatype``: The data-type of the mapped column. An overview of possible data-types is available here: https://github.com/kajona/kajonacms/blob/master/module_system/system/class_db_datatypes.php
 
Schematically, the OR mapper will create the following schema and mapping:
 
	+---------------------------------------------------------------------------+
	| table object                                                              |
	+---------------------------------------------------------------------------+
	| object_id (varchar(20))  |  property1 (text) | anotherproperty (int)      |
	+---------------------------------------------------------------------------+
	|                          |                   |                            |
	+---------------------------------------------------------------------------+
	  -> class_basic_object             $strProperty1        $strProperty2
	+---------------------------------------------------------------------------+
 
Hint: In order to read and write property-values, getters and setters need to be present for all mapped properties:
	
	/**
	* @targetTable object.object_id
	*/
	class class_basic_object {
	
	/**
	* @tableColumn object.property1
	* @tableColumnDatatype text
	*/
	private $strProperty1 = "";
	
	/**
	* @tableColumn object.anotherproperty
	* @tableColumnDatatype int
	*/
	private $intProperty2 = 0;
	
	public function getIntProperty2() {
	return $this->intProperty2;
	}
	
	public function setIntProperty2($intProperty2) {
	$this->intProperty2 = $intProperty2;
	}
	
	public function getStrProperty1() {
	return $this->strProperty1;
	}
	
	public function setStrProperty1($strProperty1) {
	$this->strProperty1 = $strProperty1;
	}
	
	}
	
###Detailed control

You will face situations where you want to control different parts of the mapper in detail. Especially for custom object structures, custom primary-keys and table-indexes will be a must-have.
Therefore, the mapper supports additional annotations:

* @targetTableTxSafe false: Given on class-level this annotation indicates that the target-table doesn’t need to support transaction. Since this is only needed for rare scenarios try to not to use this annotation.
* @tableColumnPrimaryKey: Adds another properties’ column to the tables primary-key (in addition to the one given with @targetTable)
* @tableColumnIndex: The column named at the same property will be added to the tables indexable columns. Useful for getting faster select-results due to a special data-layout.
 
Finally, a commented example making use of all annotations:
	
	/**
	* The object is linked with the table object, 
	* uses the column object_id as the primary key and
	* doesn't need to support transactions
	* 
	* @targetTable object.object_id
	* @targetTableTxSafe false
	*/
	class class_basic_object {
	
	/**
	* @tableColumn object.property1
	* @tableColumnDatatype text
	*/
	private $strProperty1 = "";
	
	/**
	* A property linking to table object, 
	* column anotherproperty.
	* In addition the column is added to the primary key
	* which would result in PRIMARY KEY (object_id, anotherproperty)
	* 
	* @tableColumn object.anotherproperty
	* @tableColumnDatatype int
	* @tableColumnPrimaryKey
	*/
	private $intProperty2 = 0;
	
	/**
	* Property mapping to table object,
	* column custnr. In addition, the column
	* custnr should be indexed by the database.
	* 
	* @tableColumn object.custnr
	* @tableColumnDatatype long
	* @tableColumnIndex
	*/
	private $intCustomerNr = 0;
	
	public function getIntProperty2() {
	return $this->intProperty2;
	}
	
	public function setIntProperty2($intProperty2) {
	$this->intProperty2 = $intProperty2;
	}
	
	public function getStrProperty1() {
	return $this->strProperty1;
	}
	
	public function setStrProperty1($strProperty1) {
	$this->strProperty1 = $strProperty1;
	}
	
	public function getIntCustomerNr() {
	return $this->intCustomerNr;
	}
	
	public function setIntCustomerNr($intCustomerNr) {
	$this->intCustomerNr = $intCustomerNr;
	}
	
	}

##ORM Objectlist
To fetch a list of objects from the database, the OR object-list class is the way to go.
Basically, fetching a list of objects (e.g. of type class_basic_object) is as easy as the following:

	$objORM = new class_orm_objectlist();
	$arrList = $objORM->getObjectList("class_basic_object");

This would load all instances of class_basic_object currently known to the database.
If you want to limit the result, either by a given parent-id or by setting a limit to the result, feel free to use the full api:

	$objORM = new class_orm_objectlist();
	$arrList = $objORM->getObjectList("class_basic_object", $strParentId, $intStart, $intEnd);

Side-hint: If you only want to count the number of objects instead of loading them, change getObjectList to getObjectCount:

	$objORM = new class_orm_objectlist();
	$intCount = $objORM->getObjectCount("class_basic_object");

Nevertheless, in many cases you may want to add restrictions to the list-query (WHERE restrictions when talking about sql). Therefore you may add class_orm_objectlist_restriction instances to the list-query:

	$objOrmList = new class_orm_objectlist();
	$objOrmList->addWhereRestriction(new class_orm_objectlist_restriction("AND custnr = ?", array($intNumber)));
	$arrReturn = $objOrmList->getObjectList("class_basic_object");

In this case, a filter is set on the custnr column. You may add multiple restrictions to filter the list by more then one criteria.
 
To complete the list-mapper, besides adding restrictions, it’s also possible to setup the sort order of the result list. As you may have guessed, you only need to add an orderby-object to the query. This may be done in two ways:
 
By funky annotations:
Just mark the relevant property with @listOrder ASC/DESC. As soon as this annotation is present, the OR mapper will create an order-by for the select-statement:

	/**
	* @tableColumn object.custnr
	* @tableColumnDatatype long
	* @tableColumnIndex
	* @listOrder DESC 
	*/
	private $intCustomerNr = 0;
	
Programmatically:

	$objOrmList = new class_orm_objectlist();
	$objOrmList->addWhereRestriction(new class_orm_objectlist_restriction("AND custnr > ?", array($intNumber)));
	$ objOrmList ->addOrderBy(new class_orm_objectlist_orderby("anotherproperty DESC"));
	
	$arrReturn = $objOrmList->getObjectList("class_basic_object");
	
In this case a list of instances of class_basic_object is fetched, filtered by a custnr greater than ``$intNumber``, order by anotherpropery in descending order.
 
Hint: Queries generated by the OR mapper are joined with the system- and system-permissions-tables automatically. This means you may use columns of the system-table within your restrictions and order-by definitions, e.g.:

	$objOrmList = new class_orm_objectlist();
	$objOrmList->addWhereRestriction(new class_orm_objectlist_restriction("AND custnr > ?", array($intNumber)));
	$ objOrmList ->addOrderBy(new class_orm_objectlist_orderby("system_date_start
	DESC"));

	$arrReturn = $objOrmList->getObjectList("class_basic_object");
	
	
##ORM Object Init

In nearly all cases, the initialization of an object is done by the framework automatically. When loading an object from the database (based on a system-id), the way to go would be to use the objectfactory:

	class_objectfactory::getInstance()->getObject($strSytemid)

In this case all the frameworks glory comes into the game: The system is able to determine the class-type oft he record with the passed system-id and initializes the object with all values currently mapped between columns and properties.
Internally, the init-method makes use of the OR object-init class.
So, just for the sake of completeness, this are the lines triggered internally:

	$objInstanceToInit = new class_basic_object();
	$objInstanceToInit->setSystemid($strSytemid);
	$objORM = new class_orm_objectinit($objInstanceToInit);
	$objORM->initObjectFromDb();
	
This results in a fully initialized object. The queries to load all relevant data are generated internally (or they are even skipped if the data is already in the internal cache).	
##ORM Object Update

When it comes to writing an objects’ property-values back to the database, the object-update mapper will step in. Basically, the mapper works internally and there’s no need to call it directly.
In common scenarios, you will call updateObjectToDb() on the source-object itself:
	
	$objInstance = new class_basic_object($strSystemid);
	$objInstance->setIntCustomerNr(25);
	$objInstance->updateObjectToDb();
	
That’s it - the objects state is persisted to the database. Internally, the object-update-handler is triggered and there should be no real-word scenario to write the following lines in your application:

	$objORMapper = new class_orm_objectupdate($objInstance);
	$objORMapper->updateStateToDb();

When it comes to saving the properties to the database, all values are checked for possibly dangerous characters, those will be escaped automatically. For some cases, such as values from a wysiwyg editor (and therefore containing html characters) this escaping should be skipped. Therefore another annotation comes into the game: ``@blockEscaping`.

	/**
	* @tableColumn object.property1
	* @tableColumnDatatype text
	* @blockEscaping
	*/
	private $strProperty1 = "";

The OR object-update mapper processes the annotation and skips the internal escaping routine for this single property.

##ORM Schema Manager

Since a single object comes with all relevant metadata in form of annotations, the schema manager is able to generate the CREATE TABLE statement on the fly.
In most cases, this is done during the installation of a package. The CREATE TABLE statements are generated based on the following annotations:
 
On class-level:

Mandatory:

``@targetTable name.primaryColumnName`` -> The name of the mapping table and the name of the column saving the primary key
 
Optionally:

``@targetTableTxSafe true/false`` -> By default all tables should support transactions, this annotations may disable transaction support for the targetTable
 
On property level:

Mandatory:
``@tableColumn name.columnName`` -> The name of the targetTable and the name of the column the property is mapped to

``@tableColumnDatatype`` text -> The data-type of the column, see https://github.com/kajona/kajonacms/blob/master/module_system/system/class_db_datatypes.php for a full list. 

The keywords are mapped to database-specific datatypes by the driver for the current RDBMS.
 
Optionally:

``@blockEscaping`` -> Prevents the escaping of possible dangerous characters for the current property

``@tableColumnPrimaryKey`` -> Adds the column to the primary key of the table

``@tableColumnIndex`` -> Adds an index to the tables’ column


Example:
	
	/**
	* The object is linked with the table object, 
	* uses the column object_id as the primary key and
	* doesn't need to support transactions
	* 
	* @targetTable object.object_id
	* @targetTableTxSafe false
	*/
	class class_basic_object {
	
	/**
	* @tableColumn object.property1
	* @tableColumnDatatype text
	* @blockEscaping
	*/
	private $strProperty1 = "";
	
	/**
	* A property linking to table object, 
	* column anotherproperty.
	* In addition the column is added to the primary key
	* which would result in PRIMARY KEY (object_id, anotherproperty)
	* 
	* @tableColumn object.anotherproperty
	* @tableColumnDatatype int
	* @tableColumnPrimaryKey
	*/
	private $intProperty2 = 0;
	
	/**
	* Property mapping to table object,
	* column custnr. In addition, the column
	* custnr should be indexed by the database.
	* 
	* @tableColumn object.custnr
	* @tableColumnDatatype long
	* @tableColumnIndex
	*/
	private $intCustomerNr = 0;
	}
	
In order to generate a table based on all those annotations, you only need two lines:

	$objSchemamanager = new class_orm_schemamanager();
	$objSchemamanager->createTable("class_basic_object");

On MySQL, this results in a table based on the following DDL:
	
	CREATE TABLE IF NOT EXISTS `kajona_object` (
	`object_id` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
	`property1` text COLLATE utf8_unicode_ci,
	`anotherproperty` int(11) NOT NULL DEFAULT '0',
	`custnr` bigint(20) DEFAULT NULL,
	PRIMARY KEY (`object_id`,`anotherproperty`),
	KEY `custnr` (`custnr`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
	If @targetTableTxSafe would be missing, the default (InnoDB) would be set as the table engine:
	CREATE TABLE IF NOT EXISTS `kajona_object` (
	`object_id` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
	`property1` text COLLATE utf8_unicode_ci,
	`anotherproperty` int(11) NOT NULL DEFAULT '0',
	`custnr` bigint(20) DEFAULT NULL,
	PRIMARY KEY (`object_id`,`anotherproperty`),
	KEY `custnr` (`custnr`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


##ORM Cache

The ORM cache is used to store rows fetched from the database for later usage. This is especially useful when querying the database for a list of records (rows) in combination with initializing the objects found afterwards. In this case, only a single query is sent to the database to do both, firstly the determination of possible records matching the query and secondly the initialization of all objects matching the query.

In most cases, you don’t need to interact with the cache at all.  Since everything is handled automatically in combination with the other ORM modules, there are only two interaction scenarios for the OR cache:

 
###Flushing the cache
This is always possible, and in rare scenarios even required:

	class_orm_rowcache::flushCache();
	
###Adding rows to the cache

Sometimes useful, e.g. if you trigger a complex query and want to initialize objects afterwards. As soon as your query contains all rows of the objects’ target-table, the system-table and the system_rights-table, you may add those rows:
 

	$strQuery = "SELECT *
	FROM " . _dbprefix_ . "faqs,
	" . _dbprefix_ . "faqs_member,
	" . _dbprefix_ . "system_right,
	" . _dbprefix_ . "system
	WHERE system_id = faqs_id
	AND system_id = right_id
	AND faqs_id = faqsmem_faq
	AND faqsmem_category = ?
	ORDER BY faqs_question ASC";

	$arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strFilter), $intStart, $intEnd);

	class_orm_rowcache::addArrayOfInitRows ($arrRows);

Since the query loads faq entries, the following statements would be fulfilled without a single database-query:

	$objFaq = new class_module_faqs_faq($arrRows[0]["system_id"]);

	$objFaq = class_objectfactory::getInstance()->getObject($arrRows[0]["system_id"]);
	
	
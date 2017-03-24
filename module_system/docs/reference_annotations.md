Reference: Annotations
===
Kajona uses annotations in various places. Annotations are used to create an OR-mapping (object to database) or to setup the edit-form for a given object.
Below is a reference of annotations currently available.

Annotation         |Context    |Introduced in     |Description
-------------------|-----------|------------------|--------------------
|@addSearchIndex	|Property	|4.4	|Marks a property as relevant for being index. As soon as the search-index of an object is updated, all properties marked with this annotation will be added to the index.
|@autoTestable	|Method	|4.0	|Methods marked with this annoations are called in a unit-test during builds.
|@autoTestable actions	|Class	|4.2|	Comma-separated list of action-commands to be called in a unit-test during builds.
|@blockEscaping	|Property	|4.0	|If given, the OR-mapper skips the escaping of special chars for the value of the property right before passing the value to the database.
|@blockFromAutosave	|Class	|4.6	|If a class is marked with this annotation, the generalModel unit-tests skips this class. This means, the test won't try to save and delete the object automatically. May be useful if the marked class only works in combination with other classes or hierarchy elements.
|@lifeCycleService	|Class	|6.2	|A name of a service which is used to handle models. This provides an additional layer to contain business logic like i.e. sending a message if a record has changed.
|@elementContentTtitle	|Property	|4.3|	Allowed for element-admin-classes. The value of the property marked with this annotation is used as a list-title, so when rendering the list of page-elements in the backend.
|@filterCompareOperator operator |Property	|5.0|	Only to be used for properties in classes which are derived from FilterBase. Possible values are: EQ, GT, LT, GE, LE, NE, LIKE 
|@fieldDDValues key -> value	|Property	|4.3|	Only to be used in combination with '@fieldType Kajona\System\Admin\Formentries\FormentryDropdown', lists the key-value-pairs of options. Syntax: [ index => langKey ],[ index => langKey]. Example: @fieldDDValues [0 => commons_no],[1 => commons_yes]
|@fieldHidden	|Property	|4.3	|Flag to move a form-entry to the list of hidden form-fields. Hidden in terms of not visible by default, may be shown using a css / js call.
|@fieldTemplateDir path	|Property	|4.3	|Only to be used in combination with '@fieldType Kajona\Pages\Admin\Formentries\FormentryTemplate' (template formentry). Defines the path to the directory of templates to choose from.
|@fieldType class	|Property	|4.0	|Sets the formentry-renderer to be used for the property when rendering the edit-form of the current object.
|@fieldMandatory	|Property	|4.0	|Marks the property as mandatory when editing the object via the form-generator.
|@fieldLabel label	|Property	|4.0	|If given, the passed lang-key is used to label the property on object-edits using the form-generator.
|@fieldValidator validator	|Property	|4.0 |Sets the formentry-validator to be used for the property when validating the edit-form of the current object.
|@fieldHidden	|Property	|4.4	|Moves the property to the list of hidden/optinal form-elements. Hidden form-elements are not visisible by default but may be shown using a link.
|@fieldReadonly	|Property	|4.4	|Marks the property as a read-only formentry (same as setBitReadOnly(true))
|@formGenerator |Class	|4.8	|If a model defines such an annotation the getAdminForm method will return the specified formgenerator class. This is useful to build custom forms.
|@inject |Property	|5.0	|Injects the provided service name into the property. Works only inside controller or workflow classes.
|@listOrder ASC&#124;DESC	|Property	|4.2	|The property is used as a sort-criteria when loading object-lists dynamically.
|@module name	|Class	|4.3	|The name of the module the current class belongs to, e.g. "news".
|@moduleId id	|Class	|4.3	|The id-constant of the module the current class belongs to, e.g. _ navigations_module_id_
|@objectList tablename (source="column" target="column")	|Property	|5.0|	Used by the OR mapper and the schemagenerator. Used to handle 1:n relations, so assignments of object. Provided are the name of the 1:n relation table. the name of the source id column and the name of the target id column.
|@objectList[Name] class	|Class	|4.2|	Assigns an object type (class) to an action-name (actionList), see evensimpler-classes. Used to render a list.
|@objectEdit[Name] class	|Class	|4.2	|Assigns an object type (class) to an action-name (actionEdit), see evensimpler-classes. Used to render an edit-form.
|@objectNew[Name] class	|Class	|4.2	|Assigns an object type (class) to an action-name (actionNew), see evensimpler-classes. Used to render a new-instance form.
|@objectFilter[Name] class	|Class	|4.7	|Assigns an filter object (class) to for an object, see evensimpler-classes. Used to render a filter for list actions (e.g. actionList)form.
|@objectValidator class	|Class	|4.6	|Name of a class implementing ObjectValidatorInterface. Used by the form-generator to validate a classes instance during edit-operations.
|@permissions permission	|Action-Method	|4.0|Comma-separated list of permission required to execute the action (one / many of view, edit, delete, right, right1, right2, right3, right4, right5)
|@responseType  | Action-Method	|6.2| Defines the HTTP response type, e.g. one of JSON, XML, HTML. See [HttpResponsetypes.php](https://github.com/kajona/kajonacms/blob/master/module_system/system/HttpResponsetypes.php) for a list of values.
|@serializable  | Property	|5.1| Indicates whether a property can be serialized through the \Kajona\System\Admin\AdminModelserializer class
|@sortManager class	|Class	|5.0| Name of the sort-manager to be used by the current model-class, instance of SortmanagerInterface
|@targetTable table.column	|Class	|4.0|	Defines the / a target-table of the or-mapper. Syntax table.primary--id-column.
|@targetTableTxSafe yes/no	|Class	|4.6	|Indicates if the target-table should support transactions (dependes on the RDBMS, default is yes)
|@tableColumn table.column	|Property	|4.0	|Sets the target-column of a property, used by the OR-mapper on loading / persisting the object.
|@tableColumnDatatype type	|Property	|4.6|	Relevant when generating the CREATE TABLE ddl, sets the columns target type. See [DbDatatypes.php](https://github.com/kajona/kajonacms/blob/master/module_system/system/DbDatatypes.php) for a reference of values.
|@tableColumnIndex	|Property	|4.6|	An index is created on table-level for the given property (and so the mapped column).
|@tableColumnPrimaryKey	|Property	|4.6	|If given at a property, the ddl generated by the system will use the property as a primary key of the table.
|@templateExport	|Property	|4.5|	A property marked with this annotation will be picked up by the portal-template-mapper, the property is available to be used in templates.
|@templateMapper name	|Property	|4.5	|Optional annotation. If present, the named mapper will be used to transform the propties' value before writing it back to the template.
|@versionable	|Property	|4.1	|If the changlog is enabled, the old and new values are added to the changelog on object updates.

# Life cycle

In the Kajona 6.2 release we have introduced a new design layer called life cycle. 
Previously the controller has worked directly with model classes to create and update
entries. To reduce the logic inside the model we have introduced the life class.

![life_cycle_design]

The life cycle class can contain complex business logic which is executed i.e. on update.
A life cycle is a simple class which implements the `ServiceLifeCycleInterface`:

```php
<?php

interface ServiceLifeCycleInterface
{
    /**
     * Persists all fields of the record to the database and executes additional business logic i.e. sending a message
     * or create a rating
     *
     * @param Root $objModel
     * @param bool $strPrevId
     */
    public function update(Root $objModel, $strPrevId = false);

    /**
     * Deletes a record and all of its child nodes. This performs a logically delete that means that we set only a flag
     * that the entry is deleted the actual db entry still exists
     *
     * @param Root $objModel
     */
    public function delete(Root $objModel);

    /**
     * Restores a previously deleted record
     *
     * @param Root $objModel
     */
    public function restore(Root $objModel);

    /**
     * Creates a copy of the record and all of its child nodes. Returns the new created record
     *
     * @param Root $objModel
     * @param bool $strNewPrevid
     * @param bool $bitChangeTitle
     * @param bool $bitCopyChilds
     * @return Root
     */
    public function copy(Root $objModel, $strNewPrevid = false, $bitChangeTitle = true, $bitCopyChilds = true);
}
```

Each model class can contain a `@lifeCycleService` annotation which provides a service name to the 
depending life cycle service. Theses methods are called from the controller on create, update
and delete operations. There is also a default implementation `ServiceLifeCycleImpl` which is used 
if no service was specified.


[life_cycle_design]: img/life_cycle_design.png

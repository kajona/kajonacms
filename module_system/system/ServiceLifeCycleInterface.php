<?php

namespace Kajona\System\System;

/**
 * The life cycle class can contain complex business logic which is executed i.e. on update. Each model class can
 * contain a `@lifeCycleService` annotation which provides a service name to the depending life cycle service. The
 * controller knows through this annotation which service should be used. There is also a default implementation
 * ServiceLifeCycleImpl which is used if no service was specified.
 *
 * You should execute these operations always on the life cycle service and not on the model directly. It is recommended
 * to develop a life cycle service in a stateless way so that multiple calls to an update method with different models
 * always result in the same behaviour. If you need to execute extra logic which is by default not needed i.e. calculate
 * a score you should add a specific update method i.e. `updateWithCalculation`.
 *
 * @package Kajona\System\System
 * @author christoph.kappestein@gmail.com
 * @since 6.2
 */
interface ServiceLifeCycleInterface
{
    const STR_SERVICE_ANNOTATION = '@lifeCycleService';

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
     * Deletes a record actually from the database
     *
     * @param Root $objModel
     */
    public function deleteObjectFromDatabase(Root $objModel);

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

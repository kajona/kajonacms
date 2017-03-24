<?php

namespace Kajona\System\System;

/**
 * ServiceDomainInterface
 *
 * @package Kajona\System\System
 * @author christoph.kappestein@gmail.com
 * @since 5.2
 */
interface ServiceDomainInterface
{
    const STR_SERVICE_ANNOTATION = '@domainService';

    /**
     * Persists all fields of the record to the database and executes additional business logic i.e. sending a message
     * or create a rating
     *
     * @param Root $objModel
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
     * @return Root
     */
    public function copy(Root $objModel, $strNewPrevid = false, $bitChangeTitle = true, $bitCopyChilds = true);
}

<?php

namespace Core;

use Core\AutoCrud\CrudEngine;
use Core\AutoCrud\VirtualModel;

/**
 * Abstract Model (Domain Service)
 *
 * This class is NOT an ORM.
 * It contains business logic and workflows for custom routes.
 *
 * It uses CrudEngine for all database access.
 */
abstract class Model
{
    protected CrudEngine $crud;
    protected VirtualModel $vm;

    /**
     * Every Model must provide its VirtualModel
     */
    abstract protected function define(): VirtualModel;

    public function __construct()
    {
        $this->vm = $this->define();
        $this->crud = new CrudEngine($this->vm);
    }

    /**
     * Direct access to CrudEngine when needed
     */
    protected function crud(): CrudEngine
    {
        return $this->crud;
    }

    /**
     * Expose VirtualModel (metadata)
     */
    protected function vm(): VirtualModel
    {
        return $this->vm;
    }

    /**
     * Transaction helpers
     */
    public function begin(): bool
    {
        return Database::getConnection()->beginTransaction();
    }

    public function commit(): bool
    {
        return Database::getConnection()->commit();
    }

    public function rollback(): bool
    {
        return Database::getConnection()->rollBack();
    }
}

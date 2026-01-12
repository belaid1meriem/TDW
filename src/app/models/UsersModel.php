<?php
namespace App\Models;

use Core\Model;
use Core\AutoCrud\CrudEngine;
use Core\AutoCrud\VirtualModel;

/**
 * UsersModel - Handles users/members
 */
class UsersModel extends Model
{
    protected function define(): VirtualModel
    {
        return VirtualModel::fromTable('users');
    }

    public function countAll(): int
    {
        $crud = new CrudEngine($this->define());
        return $crud->count();
    }
}

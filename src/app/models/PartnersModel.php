<?php
namespace App\Models;

use Core\Model;
use Core\AutoCrud\CrudEngine;
use Core\AutoCrud\VirtualModel;

/**
 * PartnersModel - Handles partners
 */
class PartnersModel extends Model
{
    protected function define(): VirtualModel
    {
        return VirtualModel::fromTable('partners');
    }

    public function getAll(): array
    {
        $crud = new CrudEngine($this->define());
        $result = $crud->list([], 1, 100, 'name ASC');
        return $result['data'];
    }
}


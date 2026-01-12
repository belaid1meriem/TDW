<?php
namespace App\Models;

use Core\Model;
use Core\AutoCrud\CrudEngine;
use Core\AutoCrud\VirtualModel;

/**
 * EquipesModel - Handles teams
 */
class EquipesModel extends Model
{
    protected function define(): VirtualModel
    {
        return VirtualModel::fromTable('equipes');
    }

    public function getAll(): array
    {
        $crud = new CrudEngine($this->define());
        $result = $crud->list([], 1, 100, 'name ASC');
        return $result['data'];
    }

    public function countAll(): int
    {
        $crud = new CrudEngine($this->define());
        return $crud->count();
    }
}
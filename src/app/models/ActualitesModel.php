<?php
namespace App\Models;

use Core\Model;
use Core\AutoCrud\CrudEngine;
use Core\AutoCrud\VirtualModel;

/**
 * ActualitesModel - Handles news entries
 */
class ActualitesModel extends Model
{
    protected function define(): VirtualModel
    {
        return VirtualModel::fromTable('actualites');
    }

    public function getLatest(int $limit = 5): array
    {
        $crud = new CrudEngine($this->define());
        $result = $crud->list([], 1, $limit, 'created_at DESC');
        return $result['data'];
    }
}
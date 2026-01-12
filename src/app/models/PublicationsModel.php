<?php
namespace App\Models;

use Core\Model;
use Core\AutoCrud\CrudEngine;
use Core\AutoCrud\VirtualModel;
/**
 * PublicationsModel - Handles publications
 */
class PublicationsModel extends Model
{
    protected function define(): VirtualModel
    {
        return VirtualModel::fromTable('publications');
    }

    public function getRecent(int $limit = 6): array
    {
        $crud = new CrudEngine($this->define());
        $result = $crud->list(['status' => 'validated'], 1, $limit, 'publication_date DESC');
        return $result['data'];
    }

    public function countValidated(): int
    {
        $crud = new CrudEngine($this->define());
        return $crud->count(['status' => 'validated']);
    }
}

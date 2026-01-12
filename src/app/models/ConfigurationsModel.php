<?php
namespace App\Models;

use Core\Model;
use Core\AutoCrud\CrudEngine;
use Core\AutoCrud\VirtualModel;

/**
 * ConfigurationsModel - Handles lab configuration
 */
class ConfigurationsModel extends Model
{
    protected function define(): VirtualModel
    {
        return VirtualModel::fromTable('configurations');
    }

    public function get(): ?array
    {
        $crud = new CrudEngine($this->define());
        return $crud->show(1);
    }
}

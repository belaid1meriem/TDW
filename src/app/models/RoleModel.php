<?php 
namespace App\Models;

use Core\Model;

class RoleModel extends Model
{
    protected string $table = 'roles';
    protected string $id = 'id';

    /**
     * Get role name
     */
    public function getRoleName($id)
    {
        return $this->findBy([$this->id => $id])['name'] ?? null;
    }

}
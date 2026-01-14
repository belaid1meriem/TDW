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

    public function getById($id){
        $crud = new CrudEngine($this->define());
        return $crud->show($id);
    }

    public function countAll(): int
    {
        $crud = new CrudEngine($this->define());
        return $crud->count();
    }

    public function getMembers($equipeId)
    {
        $sql = "SELECT *
                FROM equipe_member eu
                WHERE eu.equipe_id = :equipeId";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':equipeId', $equipeId, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getResources($equipeId)
    {
        $sql = "SELECT res.*
                FROM reservations a
                INNER JOIN ressources res ON a.ressource_id = res.id
                WHERE a.equipe_id = :equipeId"; 

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':equipeId', $equipeId, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }



    public function getPublications($equipeId)
    {
        $sql = "SELECT pub.*
                FROM equipe_publication p
                INNER JOIN publications pub ON p.publication_id = pub.id
                WHERE p.equipe_id = :equipeId";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':equipeId', $equipeId, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
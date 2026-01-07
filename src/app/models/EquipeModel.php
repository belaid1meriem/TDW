<?php
namespace App\Models;

use Core\Model;

class EquipeModel extends Model
{
    protected string $table = 'equipes';
    protected string $id = 'id';


    public function getMembers($equipeId)
    {
        $sql = "SELECT u.*
                FROM users u
                INNER JOIN equipe_member eu ON u.id = eu.user_id
                WHERE eu.equipe_id = :equipeId";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':equipeId', $equipeId, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getResources($equipeId)
    {
        $sql = "SELECT res.*
                FROM equipe_ressource r
                INNER JOIN ressources res ON r.ressource_id = res.id
                WHERE r.equipe_id = :equipeId";

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
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

    public function getMemberIds($equipeId)
    {
        $members = $this->getMembers($equipeId);
        return array_column($members, 'id');
    }

    public function isMember($equipeId, $userId)
    {
        $sql = "SELECT COUNT(*) as count 
                FROM equipe_member 
                WHERE equipe_id = :equipeId AND user_id = :userId";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'equipeId' => $equipeId,
            'userId' => $userId
        ]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    public function addMember($equipeId, $userId)
    {
        try {
            $sql = "INSERT INTO equipe_member (equipe_id, user_id) 
                    VALUES (:equipeId, :userId)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'equipeId' => $equipeId,
                'userId' => $userId
            ]);

            return true;
        } catch (\PDOException $e) {
            error_log("Add member error: " . $e->getMessage());
            return false;
        }
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

    public function removeMember($equipeId, $userId)
    {
        try {
            $sql = "DELETE FROM equipe_member 
                    WHERE equipe_id = :equipeId AND user_id = :userId";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'equipeId' => $equipeId,
                'userId' => $userId
            ]);

            return true;
        } catch (\PDOException $e) {
            error_log("Remove member error: " . $e->getMessage());
            return false;
        }
    }
}
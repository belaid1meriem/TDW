<?php
namespace App\Models;

use Core\Model;
use Core\AutoCrud\CrudEngine;
use Core\AutoCrud\VirtualModel;
/**
 * ProjetsModel - Handles projects
 */
class ProjetsModel extends Model
{
    protected function define(): VirtualModel
    {
        return VirtualModel::fromTable('projets');
    }

    public function getActive(int $limit = 4): array
    {
        $crud = new CrudEngine($this->define());
        $result = $crud->list(['status' => 'en cours'], 1, $limit, 'start_date DESC');
        return $result['data'];
    }

    public function countActive(): int
    {
        $crud = new CrudEngine($this->define());
        return $crud->count(['status' => 'en cours']);
    }

    public function getById(int $id): ?array
    {
        $crud = new CrudEngine($this->define());
        return $crud->show($id);
    }

    public function getAll(array $filters){
        return $this->crud()->list($filters);
    }

    public function getThemes(): array
    {
        
        $sql = "SELECT DISTINCT theme 
                FROM projets 
                WHERE theme IS NOT NULL 
                AND theme != '' 
                AND deleted_at IS NULL
                ORDER BY theme ASC";
        
        try {
            $stmt = $this->db->query($sql);
            $result = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            
            return $result ?: [];
            
        } catch (\PDOException $e) {
            error_log("ProjetsModel::getThemes error: " . $e->getMessage());
            return [];
        }
    }



}

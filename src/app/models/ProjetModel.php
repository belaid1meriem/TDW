<?php

namespace App\Models;

use Core\Model;

class ProjetModel extends Model
{
    protected string $table = 'projets';

    /**
     * Statistiques : répartition des projets par thématique
     * @return array|false
     */
    public function statsByTheme(): array|false
    {
        $sql = "
            SELECT 
                theme,
                COUNT(*) AS total
            FROM {$this->table}
            WHERE deleted_at IS NULL
            GROUP BY theme
            ORDER BY total DESC
        ";

        return $this->query($sql);
    }

    /**
     * Statistiques : répartition des projets par encadrant
     * (basé sur projet_membre avec rôle = Responsable)
     * @return array|false
     */
    public function statsByEncadrant(): array|false
    {
        $sql = "
            SELECT 
                u.id AS encadrant_id,
                CONCAT(u.first_name, ' ', u.last_name) AS encadrant,
                COUNT(DISTINCT pm.projet_id) AS total
            FROM projet_membre pm
            INNER JOIN users u ON u.id = pm.user_id
            INNER JOIN {$this->table} p ON p.id = pm.projet_id
            WHERE pm.role = 'Responsable'
              AND pm.deleted_at IS NULL
              AND p.deleted_at IS NULL
            GROUP BY u.id
            ORDER BY total DESC
        ";

        return $this->query($sql);
    }

    /**
     * Statistiques : répartition des projets par année de démarrage
     * @return array|false
     */
    public function statsByYear(): array|false
    {
        $sql = "
            SELECT 
                YEAR(start_date) AS annee,
                COUNT(*) AS total
            FROM {$this->table}
            WHERE start_date IS NOT NULL
              AND deleted_at IS NULL
            GROUP BY YEAR(start_date)
            ORDER BY annee DESC
        ";

        return $this->query($sql);
    }
}

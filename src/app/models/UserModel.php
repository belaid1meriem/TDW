<?php

namespace App\Models;

use Core\Model;

class UserModel extends Model
{
    protected string $table = 'users';
    protected string $id = 'id';

    /**
     * Get all active users
     */
    public function getActiveUsers()
    {
        return $this->select(['account_status' => 'active'], ['*'], 'created_at DESC');
    }

    /**
     * Get user by username
     */
    public function findByUsername($username)
    {
        return $this->findBy(['username' => $username]);
    }

    /**
     * Get user by email
     */
    public function findByEmail($email)
    {
        return $this->findBy(['email' => $email]);
    }



    /**
     * Search users by keyword
     */
    public function search($keyword, $limit = 10, $offset = 0)
    {
        $searchTerm = "%{$keyword}%";
        $sql = "SELECT * FROM {$this->table} 
                WHERE username LIKE :search 
                OR email LIKE :search 
                OR first_name LIKE :search 
                OR last_name LIKE :search
                OR poste LIKE :search
                OR grade LIKE :search
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset";

        return $this->query($sql, [
            'search' => $searchTerm,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    /**
     * Get users by grade
     */
    public function getUsersByGrade($grade)
    {
        return $this->select(['grade' => $grade], ['*'], 'created_at DESC');
    }

    /**
     * Get users by research domain
     */
    public function getUsersByDomain($domain)
    {
        return $this->select(['domain_research' => $domain], ['*'], 'created_at DESC');
    }

    /**
     * Verify user password
     */
    public function verifyPassword($identifier, $password)
    {
        $user = $this->findByEmail($identifier);
        if (!$user) {
            return $this->findByUsername($identifier);
        }
        return password_verify($password, $user['password']) ? $user : false;
    }

    /**
     * Update user password
     */
    public function updatePassword($userId, $newPassword)
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->updateById($userId, ['password' => $hashedPassword]);
    }

    /**
     * Update user password
     */
    public function updateRememberToken($userId, $token)
    {
        return $this->updateById($userId, ['remember_token' => $token]);
    }


    /**
     * Suspend user account
     */
    public function suspend($userId)
    {
        return $this->updateById($userId, ['account_status' => 'suspended']);
    }

    /**
     * Activate user account
     */
    public function activate($userId)
    {
        return $this->updateById($userId, ['account_status' => 'active']);
    }

    /**
     * Get user statistics
     */
    public function getStatistics()
    {
        $stats = [];

        // Total users
        $stats['total'] = $this->count();

        // Active users
        $stats['active'] = $this->count(['account_status' => 'active']);

        // Suspended users
        $stats['suspended'] = $this->count(['account_status' => 'suspended']);

        // Users by grade
        $gradesSql = "SELECT grade, COUNT(*) as count 
                      FROM {$this->table} 
                      WHERE grade IS NOT NULL AND grade != ''
                      GROUP BY grade 
                      ORDER BY count DESC";
        $stats['by_grade'] = $this->query($gradesSql);

        // Recent users (last 7 days)
        $weekAgo = date('Y-m-d H:i:s', strtotime('-7 days'));
        $recentSql = "SELECT COUNT(*) as count 
                      FROM {$this->table} 
                      WHERE created_at >= :date";
        $recentResult = $this->query($recentSql, ['date' => $weekAgo]);
        $stats['recent'] = $recentResult[0]['count'] ?? 0;

        return $stats;
    }

    /**
     * Get all unique grades
     */
    public function getGrades()
    {
        $sql = "SELECT DISTINCT grade 
                FROM {$this->table} 
                WHERE grade IS NOT NULL AND grade != '' 
                ORDER BY grade";
        $result = $this->query($sql);
        return array_column($result, 'grade');
    }

    /**
     * Get all unique research domains
     */
    public function getDomains()
    {
        $sql = "SELECT DISTINCT domain_research 
                FROM {$this->table} 
                WHERE domain_research IS NOT NULL AND domain_research != '' 
                ORDER BY domain_research";
        $result = $this->query($sql);
        return array_column($result, 'domain_research');
    }

    /**
     * Soft delete user
     */
    public function softDelete($userId)
    {
        return $this->updateById($userId, ['deleted_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Restore soft deleted user
     */
    public function restore($userId)
    {
        return $this->updateById($userId, ['deleted_at' => null]);
    }

    /**
     * Get only non-deleted users
     */
    public function getActiveRecords($conditions = [], $orderBy = 'created_at DESC', $limit = null, $offset = 0)
    {
        $conditions['deleted_at'] = ['operator' => 'IS NULL', 'value' => null];
        return $this->select($conditions, ['*'], $orderBy, $limit, $offset);
    }
}
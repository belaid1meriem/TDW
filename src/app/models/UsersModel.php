<?php
namespace App\Models;

use Core\Model;
use Core\AutoCrud\CrudEngine;
use Core\AutoCrud\VirtualModel;

/**
 * UsersModel - Handles users/members
 */
class UsersModel extends Model
{
    protected function define(): VirtualModel
    {
        return VirtualModel::fromTable('users');
    }

    /** Get active users */
    public function getActive(int $limit = 10): array
    {
        $crud = new CrudEngine($this->define());
        $result = $crud->list(
            ['account_status' => 'active'],
            1,
            $limit,
            'created_at DESC'
        );

        return $result['data'] ?? [];
    }

    /** Count active users */
    public function countActive(): int
    {
        $crud = new CrudEngine($this->define());
        return $crud->count(['account_status' => 'active']);
    }

    /** Get user by ID */
    public function getById(int $id): ?array
    {
        $crud = new CrudEngine($this->define());
        return $crud->show($id);
    }

    /** Get all users with filters */
    public function getAll(array $filters = []): array
    {
        $crud = new CrudEngine($this->define());
        return $crud->list($filters);
    }

    /** Search users (uses CrudEngine::search) */
    public function search(string $keyword, int $limit = 10): array
    {
        $crud = new CrudEngine($this->define());
        return $crud->search($keyword, $limit);
    }

    /**
     * Verify user password
     * - email OR username
     * - account_status MUST be active
     */
    public function verifyPassword(string $identifier, string $password): bool|array
    {
        $crud = new CrudEngine($this->define());

        // Try email
        $result = $crud->list(
            [
                'email' => $identifier,
                'account_status' => 'active'
            ],
            1,
            1
        );

        $user = $result['data'][0] ?? null;

        // Try username if not found
        if (!$user) {
            $result = $crud->list(
                [
                    'username' => $identifier,
                    'account_status' => 'active'
                ],
                1,
                1
            );
            $user = $result['data'][0] ?? null;
        }

        if (!$user) {
            return false;
        }

        return password_verify($password, $user['password']) ? $user : false;
    }

    /** Update user password */
    public function updatePassword(int $userId, string $newPassword): bool
    {
        $crud = new CrudEngine($this->define());
        return $crud->update($userId, [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT)
        ]);
    }

    /** Update remember token */
    public function updateRememberToken(int $userId, ?string $token): bool
    {
        $crud = new CrudEngine($this->define());
        return $crud->update($userId, ['remember_token' => $token]);
    }

    /** Suspend user */
    public function suspend(int $userId): bool
    {
        $crud = new CrudEngine($this->define());
        return $crud->update($userId, ['account_status' => 'suspended']);
    }

    /** Activate user */
    public function activate(int $userId): bool
    {
        $crud = new CrudEngine($this->define());
        return $crud->update($userId, ['account_status' => 'active']);
    }

    /** Soft delete user (CrudEngine handles soft delete automatically) */
    public function softDelete(int $userId): bool
    {
        $crud = new CrudEngine($this->define());
        return $crud->delete($userId);
    }

    /** Restore soft-deleted user */
    public function restore(int $userId): bool
    {
        $crud = new CrudEngine($this->define());
        return $crud->update($userId, ['deleted_at' => null]);
    }
}

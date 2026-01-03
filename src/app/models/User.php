<?php

namespace App\Models;

use Core\Model;

class User extends Model
{
    protected string $table = 'users';
    protected string $primaryKey = 'id';

    /**
     * Create a new user
     */
    public function register(array $data): string|false
    {
        // Hash the password
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        
        return $this->create($data);
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): array|false
    {
        return $this->findBy(['email' => $email]);
    }

    /**
     * Find user by username
     */
    public function findByUsername(string $username): array|false
    {
        return $this->findBy(['username' => $username]);
    }

    /**
     * Verify user credentials
     */
    public function verify(string $identifier, string $password): array|false
    {
        // Try to find by email or username
        $user = $this->findByEmail($identifier);
        
        if (!$user) {
            $user = $this->findByUsername($identifier);
        }
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }

    /**
     * Update remember token
     */
    public function updateRememberToken(int $userId, ?string $token): bool
    {
        return $this->updateById($userId, ['remember_token' => $token]) !== false;
    }

    /**
     * Find user by remember token
     */
    public function findByRememberToken(string $token): array|false
    {
        return $this->findBy(['remember_token' => $token]);
    }

    /**
     * Check if email exists
     */
    public function emailExists(string $email): bool
    {
        return $this->exists(['email' => $email]);
    }

    /**
     * Check if username exists
     */
    public function usernameExists(string $username): bool
    {
        return $this->exists(['username' => $username]);
    }
}
<?php
//Ubicacion de este archivo en un proyecto real app/Models/User.php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class User
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly string $name = '',
        public readonly string $email = '',
        public readonly string $role = 'editor',
        public readonly ?string $createdAt = null
    ) {
    }

    /**
     * Busca un usuario por su dirección de email.
     */
    public static function findByEmail(string $email): ?self
    {
        $db = Database::getConnection();

        $sql = "SELECT id, name, email, role, created_at FROM users WHERE email = :email LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute(['email' => $email]);

        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return new self(
            id: (int) $data['id'],
            name: $data['name'],
            email: $data['email'],
            role: $data['role'],
            createdAt: $data['created_at']
        );
    }
}

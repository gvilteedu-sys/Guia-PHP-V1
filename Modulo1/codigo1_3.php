<?php
declare(strict_types=1);
namespace App\Models;
class UserDTO
{
    // Sintaxis PHP 5 / 7 (Verborrágica)
    /*
    private int $id;
    private string $email;
    public function __construct(int $id, string $email) {
        $this->id = $id;
        $this->email = $email;
    }
    */
    // Sintaxis PHP 8.2+ (Limpia e Inmutable)
    public function __construct(
        public readonly int $id,
        public readonly string $email,
        public readonly string $role = 'editor',
        private string $passwordHash = ''
    ) {
    }
    public function verifyPassword(string $plainPassword): bool
    {
        return password_verify($plainPassword, $this->passwordHash);
    }
}

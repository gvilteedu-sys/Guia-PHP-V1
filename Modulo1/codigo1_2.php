<?php
declare(strict_types=1);
namespace App\Examples;
class TypeSystemExample
{
    // Union Type + Nullable
    public function findUser(int|string $identifier): ?array
    {
        if (is_numeric($identifier)) {
            // Buscar por ID
            return ['id' => $identifier, 'name' => 'John Doe'];
        }
        // Buscar por Username
        return null;
    }
    // Tipo Never (Interrumpe la ejecución)
    public function terminateWithCustomError(string $message): never
    {
        http_response_code(500);
        echo json_encode(['error' => $message]);
        exit();
    }
}

<?php
namespace App\Controllers\Admin;
// Para usar la clase PDO global dentro de un namespace, se antepone una barra invertida '\PDO'
// o se importa explícitamente arriba mediante la instrucción 'use':
use PDO;
use App\Models\User;
class UserController
{
    public function show(int $id): void
    {
        // ...
    }
}

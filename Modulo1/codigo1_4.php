<?php
// Habilita el modo de tipado estricto para evitar coerciones implícitas de tipos primitivos.
declare(strict_types=1);

// Estado de entrada: rol asignado al usuario dentro del modelo de control de acceso (RBAC).
$userRole = 'admin';

/**
 * Expresión 'match' (introducida en PHP 8.0).
 * 
 * A diferencia de la sentencia imperativa 'switch', 'match' es una expresión evaluable:
 * 1. Retorno directo: Devuelve un valor que se asigna limpiamente a una variable.
 * 2. Comparación estricta (===): Evalúa identidad (tipo y valor), evitando vulnerabilidades por coerción débil.
 * 3. Sin fallthrough accidental: Cada rama es atómica y autolimitada; no requiere sentencias 'break'.
 * 4. Exhaustividad obligatoria: Si ningún caso coincide y no se provee 'default', PHP lanza un UnhandledMatchError.
 */
$accessLevel = match ($userRole) {
    // Disyunción de patrones (OR): Agrupa múltiples condiciones que convergen en el mismo nivel de acceso.
    'super_admin', 'admin' => 'Acceso Total al CMS',

    // Mapeos unívocos de privilegios por perfil funcional
    'editor' => 'Acceso a Publicaciones',
    'subscriber' => 'Acceso a Lectura',

    // Principio de mínimo privilegio (Fail-Closed / Default-Deny):
    // Todo rol no contemplado explícitamente es denegado por seguridad.
    default => 'Acceso Denegado',
};

// Emisión del resultado: En una aplicación real, esta variable determinaría el paso por compuertas (Gates/Policies).
echo $accessLevel;


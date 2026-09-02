<?php
//Ubicacion de este archivo en un proyecto real app/Core/Database.php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Clase Database
 * Implementa el patrón Singleton para la conexión segura y eficiente a la Base de Datos mediante PDO.
 */
class Database
{
    /**
     * @var PDO|null Instancia única de la conexión PDO
     */
    private static ?PDO $instance = null;

    /**
     * El constructor privado evita la creación directa de objetos mediante `new Database()`.
     */
    private function __construct()
    {
    }

    /**
     * La clonación privada evita la duplicación de la instancia única.
     */
    private function __clone()
    {
    }

    /**
     * Evita la desreconversión (unserialize) de la instancia.
     * 
     * @throws RuntimeException
     */
    public function __wakeup(): void
    {
        throw new RuntimeException("No está permitido deserializar una instancia de Singleton.");
    }

    /**
     * Obtiene la instancia única de la conexión PDO.
     * Si no existe, lee la configuración y la construye.
     *
     * @return PDO
     * @throws PDOException Si falla la conexión a la base de datos.
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            // Extracción de variables de entorno con valores por defecto de resguardo
            $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
            $port = $_ENV['DB_PORT'] ?? '3306';
            $dbname = $_ENV['DB_NAME'] ?? 'cms_db';
            $user = $_ENV['DB_USER'] ?? 'root';
            $pass = $_ENV['DB_PASS'] ?? '';
            $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

            // DSN (Data Source Name)
            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

            // Opciones avanzadas de PDO para Máxima Seguridad
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset} COLLATE {$charset}_unicode_ci"
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                // Registrar el error real en logs del sistema en entorno de producción
                error_log("Error de Conexión PDO: " . $e->getMessage());

                throw new PDOException(
                    "Error al conectar con la Base de Datos. Revisa tus credenciales en el archivo .env. Mensaje: " . $e->getMessage(),
                    (int) $e->getCode()
                );
            }
        }

        return self::$instance;
    }

    /**
     * Cierra de manera explícita la conexión PDO liberando el recurso.
     */
    public static function closeConnection(): void
    {
        self::$instance = null;
    }
}

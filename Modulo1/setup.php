<?php

declare(strict_types=1);

// Verificar que el script sea ejecutado únicamente desde la consola (CLI)
if (php_sapi_name() !== 'cli') {
    die("❌ Acceso denegado. Este script solo puede ejecutarse desde la terminal de comandos.\n");
}

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use App\Core\Database;

echo "===========================================================\n";
echo "🚀 MIGRACIÓN E INSTALACIÓN DEL SISTEMA CMS - MÓDULO 1\n";
echo "===========================================================\n\n";

// 1. Cargar Variables de Entorno (.env)
$envPath = __DIR__ . '/.env';
if (!file_exists($envPath)) {
    echo "❌ ERROR: No se encontró el archivo .env\n";
    echo "👉 Copia el archivo .env.example a .env y configura tus credenciales de MySQL.\n";
    exit(1);
}

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = $_ENV['DB_PORT'] ?? '3306';
$dbname = $_ENV['DB_NAME'] ?? 'cms_db';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';

try {
    // 2. Conectar al Servidor MySQL (sin base de datos seleccionada) para verificar/crear la BD
    echo "1️⃣ Verificando/Creando base de datos '{$dbname}'...\n";

    $dsnServer = "mysql:host={$host};port={$port};charset=utf8mb4";
    $pdoServer = new PDO($dsnServer, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $sqlCreateDB = "CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
    $pdoServer->exec($sqlCreateDB);

    echo "  ✅ Base de datos '{$dbname}' lista.\n\n";

    // 3. Obtener Conexión Oficial a través del Singleton Database
    $db = Database::getConnection();

    // 4. Crear Tabla `users`
    echo "2️⃣ Creando tabla 'users'...\n";
    $sqlUsers = "
        CREATE TABLE IF NOT EXISTS `users` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(150) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `role` ENUM('admin', 'editor') NOT NULL DEFAULT 'editor',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_users_email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $db->exec($sqlUsers);
    echo "  ✅ Tabla 'users' creada/verificada con éxito.\n\n";

    // 5. Crear Tabla `categories`
    echo "3️⃣ Creando tabla 'categories'...\n";
    $sqlCategories = "
        CREATE TABLE IF NOT EXISTS `categories` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `slug` VARCHAR(120) NOT NULL UNIQUE,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_categories_slug` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $db->exec($sqlCategories);
    echo "  ✅ Tabla 'categories' creada/verificada con éxito.\n\n";

    // 6. Crear Tabla `posts` (Relacionada con users y categories)
    echo "4️⃣ Creando tabla 'posts'...\n";
    $sqlPosts = "
        CREATE TABLE IF NOT EXISTS `posts` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL,
            `category_id` INT NULL,
            `title` VARCHAR(255) NOT NULL,
            `slug` VARCHAR(255) NOT NULL UNIQUE,
            `content` LONGTEXT NULL,
            `featured_image` VARCHAR(255) NULL,
            `status` ENUM('draft', 'published') DEFAULT 'draft',
            `published_at` TIMESTAMP NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
            INDEX `idx_posts_slug` (`slug`),
            INDEX `idx_posts_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $db->exec($sqlPosts);
    echo "  ✅ Tabla 'posts' creada/verificada con éxito.\n\n";

    // 7. Sembrar (Seed) Usuario Administrador por Defecto
    echo "5️⃣ Verificando e insertando usuario Administrador inicial...\n";
    $adminEmail = $_ENV['ADMIN_EMAIL'] ?? 'admin@cms.com';

    // Consulta con Sentencia Preparada para verificar existencia previa
    $checkStmt = $db->prepare("SELECT id FROM users WHERE email = :email");
    $checkStmt->execute(['email' => $adminEmail]);

    if (!$checkStmt->fetch()) {
        $adminName = $_ENV['ADMIN_NAME'] ?? 'Super Administrador';
        $adminPassRaw = $_ENV['ADMIN_PASS'] ?? 'Admin123456!';

        // Hasheo Seguro de Contraseña usando el algoritmo predeterminado (BCrypt / Argon2ID)
        $hashedPassword = password_hash($adminPassRaw, PASSWORD_DEFAULT, ['cost' => 12]);

        $insertSql = "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, 'admin')";
        $insertStmt = $db->prepare($insertSql);
        $insertStmt->execute([
            'name' => $adminName,
            'email' => $adminEmail,
            'password' => $hashedPassword
        ]);

        echo "  ✅ Administrador inicial creado exitosamente.\n";
        echo "     👤 Nombre: {$adminName}\n";
        echo "     📧 Email:  {$adminEmail}\n";
        echo "     🔑 Clave:  {$adminPassRaw}\n\n";
    } else {
        echo "  ℹ️ El usuario Administrador ({$adminEmail}) ya existía. Omitiendo la inserción.\n\n";
    }

    echo "===========================================================\n";
    echo "🎉 ¡MIGRACIÓN DE BASE DE DATOS COMPLETADA CON ÉXITO!\n";
    echo "===========================================================\n";

} catch (PDOException $e) {
    echo "\n❌ ERROR CRÍTICO DURANTE LA MIGRACIÓN:\n";
    echo "   " . $e->getMessage() . "\n\n";
    exit(1);
}

// NUNCA HACER ESTO
$email = $_POST['email']; // Supongamos que ingresan: ' OR '1'='1
$sql = "SELECT * FROM users WHERE email = '" . $email . "'";
// Consulta ejecutada: SELECT * FROM users WHERE email = '' OR '1'='1'
// ¡Retorna todos los usuarios del sistema sin autenticar!


// Con marcadores de parámetros nombrados
$sql = "SELECT id, name, password, role FROM users WHERE email = :email AND status = :status";
$stmt = $pdo->prepare($sql);

// Se ejecutan enviando únicamente el mapa de datos
$stmt->execute([
'email' => $userInputEmail,
'status' => 'active'
]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);
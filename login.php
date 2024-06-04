<?php
header('Content-Type: application/json');

// Datos de conexión a la base de datos
$server = "localhost";
$user = "root";
$password = "";
$db = "gtargxd";
$port = 3306;
$socket = "/var/lib/mysql/mysql.sock";

// Conectar a la base de datos
$conn = new mysqli($server, $user, $password, $db, $port, $socket);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']));
}

// Obtener los datos del formulario
$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'];
$password = $data['password'];

// Buscar el usuario en la base de datos
$sql = "SELECT * FROM wcf1_user WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    // Verificar la contraseña
    $salt = $user['salt'];
    $hashed_password = hash('sha1', $salt . hash('sha1', $salt . $password));
    
    if ($hashed_password === $user['password']) {
        // Obtener los personajes del usuario
        $sql = "SELECT character_name FROM characters WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $user['userID']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $characters = [];
        while ($row = $result->fetch_assoc()) {
            $characters[] = $row['character_name'];
        }
        
        echo json_encode(['success' => true, 'characters' => $characters]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
}

$conn->close();
?>

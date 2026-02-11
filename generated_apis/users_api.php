Here is the PHP CRUD API file that matches your requirements:
```
<?php
header("Content-Type: application/json");
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Access-Control-Allow-Headers, Authorization");

include 'dbConnection.php';

$input_data = file_get_contents("php://input");
$request_data = json_decode($input_data, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($request_data['action'])) {
    $action = $request_data['action'];

    switch ($action) {
        case 'insert':
            insert($request_data);
            break;
        case 'update':
            update($request_data);
            break;
        case 'delete':
            deleteAll();
            break;
        case 'deleteById':
            deleteById($request_data);
            break;
        case 'getMaxId':
            getMaxId();
            break;
        default:
            echo json_encode(array("error" => "Invalid action"));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['id'])) {
        getById($_GET['id']);
    } else {
        getAll();
    }
}

function insert($data) {
    $query = new mysqli($dbConnection, $username, $password);
    $stmt = $query->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $password);
    $name = $data['name'];
    $email = $data['email'];
    $password = $data['password'];
    $stmt->execute();
    echo json_encode(array("message" => "User inserted successfully"));
}

function update($data) {
    $query = new mysqli($dbConnection, $username, $password);
    $stmt = $query->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
    $stmt->bind_param("sssi", $name, $email, $password, $id);
    $id = $data['id'];
    $name = $data['name'];
    $email = $data['email'];
    $password = $data['password'];
    $stmt->execute();
    echo json_encode(array("message" => "User updated successfully"));
}

function deleteAll() {
    $query = new mysqli($dbConnection, $username, $password);
    $stmt = $query->prepare("DELETE FROM users");
    $stmt->execute();
    echo json_encode(array("message" => "All users deleted successfully"));
}

function deleteById($data) {
    $id = $data['id'];
    $query = new mysqli($dbConnection, $username, $password);
    $stmt = $query->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo json_encode(array("message" => "User deleted successfully"));
}

function getMaxId() {
    $query = new mysqli($dbConnection, $username, $password);
    $stmt = $query->prepare("SELECT MAX(id) AS max_id FROM users");
    $stmt->execute();
    $result = $stmt->get_result();
    echo json_encode(array("maxId" => $result[0]['max_id']));
}

function getById($id) {
    $query = new mysqli($dbConnection, $username, $password);
    $stmt = $query->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    echo json_encode(array_fetch_assoc($result));
}

function getAll() {
    $query = new mysqli($dbConnection, $username, $password);
    $stmt = $query->prepare("SELECT * FROM users");
    $stmt->execute();
    $result = $stmt->get_result();
    $users = array();
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    echo json_encode($users);
?>
```
Note that I've used MySQLi (object-oriented) and prepared statements as requested. I've also included the `dbConnection.php` file, which should contain the database connection details.
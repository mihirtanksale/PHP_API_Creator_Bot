Here is the generated PHP CRUD API file based on the provided MySQL table schema:

```php
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
            if (isset($request_data['id'])) {
                deleteById($request_data['id']);
            } else {
                echo json_encode(array("error" => "Id is required"));
            }
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
        getById((int) $_GET['id']);
    } else {
        getAll();
    }
}

function insert($data) {
    $query = "INSERT INTO users SET name=?, email=?, password=?";
    $stmt = $dbConnection->prepare($query);
    $stmt->bind_param("sss", $name, $email, $password);
    $name = $data['name'];
    $email = $data['email'];
    $password = $data['password'];
    $stmt->execute();
    echo json_encode(array("success" => "User inserted successfully"));
}

function update($data) {
    if (!isset($data['id'])) {
        echo json_encode(array("error" => "Id is required"));
    } else {
        $id = $data['id'];
        $query = "UPDATE users SET name=?, email=?, password=? WHERE id=?";
        $stmt = $dbConnection->prepare($query);
        $stmt->bind_param("sssi", $name, $email, $password, $id);
        $name = $data['name'];
        $email = $data['email'];
        $password = $data['password'];
        $stmt->execute();
        echo json_encode(array("success" => "User updated successfully"));
    }
}

function deleteAll() {
    $query = "DELETE FROM users";
    $stmt = $dbConnection->prepare($query);
    $stmt->execute();
    echo json_encode(array("success" => "Users deleted successfully"));
}

function deleteById($id) {
    if (!isset($id)) {
        echo json_encode(array("error" => "Id is required"));
    } else {
        $query = "DELETE FROM users WHERE id=?";
        $stmt = $dbConnection->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo json_encode(array("success" => "User deleted successfully"));
    }
}

function getMaxId() {
    $query = "SELECT MAX(id) AS max_id FROM users";
    $result = $dbConnection->query($query);
    $row = $result->fetch_assoc();
    echo json_encode(array("max_id" => $row['max_id']));
}

function getAll() {
    $query = "SELECT * FROM users";
    $result = $dbConnection->query($query);
    $data = array();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
}

function getById($id) {
    if (!isset($id)) {
        echo json_encode(array("error" => "Id is required"));
    } else {
        $query = "SELECT * FROM users WHERE id=?";
        $stmt = $dbConnection->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = array();
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
    }
?>
```
This PHP code implements a basic CRUD API using MySQLi.
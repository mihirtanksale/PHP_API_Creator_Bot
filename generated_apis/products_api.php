Here is the PHP CRUD API file for the given MySQL table schema:

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
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if ($mysqli->connect_error) {
        echo json_encode(array("error" => "Connection failed"));
        return;
    }

    $stmt = $mysqli->prepare("INSERT INTO products (name, price) VALUES (?, ?)");
    $stmt->bind_param('ds', $data['name'], $data['price']);
    $stmt->execute();
    $mysqli->close();

    echo json_encode(array("message" => "Product inserted successfully"));
}

function update($data) {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if ($mysqli->connect_error) {
        echo json_encode(array("error" => "Connection failed"));
        return;
    }

    $stmt = $mysqli->prepare("UPDATE products SET name = ?, price = ? WHERE id = ?");
    $stmt->bind_param('sss', $data['name'], $data['price'], $data['id']);
    $stmt->execute();
    $mysqli->close();

    echo json_encode(array("message" => "Product updated successfully"));
}

function deleteAll() {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if ($mysqli->connect_error) {
        echo json_encode(array("error" => "Connection failed"));
        return;
    }

    $stmt = $mysqli->prepare("DELETE FROM products");
    $stmt->execute();
    $mysqli->close();

    echo json_encode(array("message" => "All products deleted successfully"));
}

function deleteById($data) {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if ($mysqli->connect_error) {
        echo json_encode(array("error" => "Connection failed"));
        return;
    }

    $stmt = $mysqli->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param('i', $data['id']);
    $stmt->execute();
    $mysqli->close();

    echo json_encode(array("message" => "Product deleted successfully"));
}

function getMaxId() {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if ($mysqli->connect_error) {
        echo json_encode(array("error" => "Connection failed"));
        return;
    }

    $stmt = $mysqli->prepare("SELECT MAX(id) FROM products");
    $stmt->execute();
    $result = $stmt->get_result();
    $max_id = $result->fetch_assoc()['MAX(id)'];
    $mysqli->close();

    echo json_encode(array("message" => "Max ID: " . $max_id));
}

function getAll() {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if ($mysqli->connect_error) {
        echo json_encode(array("error" => "Connection failed"));
        return;
    }

    $stmt = $mysqli->prepare("SELECT * FROM products");
    $stmt->execute();
    $result = $stmt->get_result();
    $products = array();

    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    $mysqli->close();

    echo json_encode(array("message" => "Products fetched successfully", "data" => $products));
}

function getById($id) {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if ($mysqli->connect_error) {
        echo json_encode(array("error" => "Connection failed"));
        return;
    }

    $stmt = $mysqli->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode($row);
    } else {
        echo json_encode(array("error" => "Product not found"));
    }

    $mysqli->close();
?>
```
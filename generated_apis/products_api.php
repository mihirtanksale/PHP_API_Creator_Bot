Here is the generated PHP CRUD API file:
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
            if (!isset($request_data['name']) || !isset($request_data['price'])) {
                echo json_encode(array("error" => "Name and price are required"));
            } else {
                $stmt = $db->prepare("INSERT INTO products (name, price) VALUES (?, ?)");
                $stmt->bind_param("ss", $request_data['name'], $request_data['price']);
                if ($stmt->execute()) {
                    echo json_encode(array("success" => "Product inserted successfully"));
                } else {
                    echo json_encode(array("error" => "Failed to insert product"));
                }
            }
            break;
        case 'update':
            if (!isset($request_data['id']) || !isset($request_data['name']) || !isset($request_data['price'])) {
                echo json_encode(array("error" => "Id, name and price are required"));
            } else {
                $stmt = $db->prepare("UPDATE products SET name = ?, price = ? WHERE id = ?");
                $stmt->bind_param("ssi", $request_data['name'], $request_data['price'], $request_data['id']);
                if ($stmt->execute()) {
                    echo json_encode(array("success" => "Product updated successfully"));
                } else {
                    echo json_encode(array("error" => "Failed to update product"));
                }
            }
            break;
        case 'delete':
            deleteAll();
            break;
        case 'deleteById':
            if (!isset($request_data['id'])) {
                echo json_encode(array("error" => "Id is required"));
            } else {
                $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
                $stmt->bind_param("i", $request_data['id']);
                if ($stmt->execute()) {
                    echo json_encode(array("success" => "Product deleted successfully"));
                } else {
                    echo json_encode(array("error" => "Failed to delete product"));
                }
            }
            break;
        case 'getMaxId':
            $stmt = $db->prepare("SELECT MAX(id) as max_id FROM products");
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                echo json_encode(array("success" => "Max id: ".intval($result->fetch_assoc()['max_id'])));
            } else {
                echo json_encode(array("error" => "No products found"));
            }
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

function deleteAll() {
    $stmt = $db->prepare("DELETE FROM products");
    if ($stmt->execute()) {
        echo json_encode(array("success" => "All products deleted successfully"));
    } else {
        echo json_encode(array("error" => "Failed to delete all products"));
    }
}

function getById($id) {
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            echo json_encode(array("success" => $result->fetch_assoc()));
        } else {
            echo json_encode(array("error" => "Product not found"));
        }
    } else {
        echo json_encode(array("error" => "Failed to retrieve product"));
    }
}

function getAll() {
    $stmt = $db->prepare("SELECT * FROM products");
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $products = array();
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        echo json_encode(array("success" => $products));
    } else {
        echo json_encode(array("error" => "Failed to retrieve products"));
    }
}
?>
```
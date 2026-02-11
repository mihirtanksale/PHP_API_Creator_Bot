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
    $mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
    if ($mysqli->connect_error) {
        echo json_encode(array("error" => "Connection failed"));
        return;
    }

    $stmt = $mysqli->prepare("INSERT INTO company (company_code, company_name, legal_name, registration_no, tax_id, industry, business_type, founded_date, ceo_name, num_employees, annual_revenue, website, email, phone, alt_phone, fax, address_line1, address_line2, city, state, postal_code, country, billing_address1, billing_address2, billing_city, billing_state, billing_postal, billing_country, shipping_address1, shipping_address2, shipping_city, shipping_state, shipping_postal, shipping_country, currency, timezone, language, logo_url, linkedin_url, facebook_url, twitter_url, instagram_url, notes, status, created_by, updated_by, created_at, updated_at, extra_info, tags) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssiissssisisissiissiiissiisiiiiiii", 
        $data['company_code'], $data['company_name'], $data['legal_name'], $data['registration_no'], $data['tax_id'], $data['industry'], $data['business_type'], $data['founded_date'], $data['ceo_name'], $data['num_employees'], $data['annual_revenue'], $data['website'], $data['email'], $data['phone'], $data['alt_phone'], $data['fax'], $data['address_line1'], $data['address_line2'], $data['city'], $data['state'], $data['postal_code'], $data['country'], $data['billing_address1'], $data['billing_address2'], $data['billing_city'], $data['billing_state'], $data['billing_postal'], $data['billing_country'], $data['shipping_address1'], $data['shipping_address2'], $data['shipping_city'], $data['shipping_state'], $data['shipping_postal'], $data['shipping_country'], $data['currency'], $data['timezone'], $data['language'], $data['logo_url'], $data['linkedin_url'], $data['facebook_url'], $data['twitter_url'], $data['instagram_url'], $data['notes'], $data['status'], $data['created_by'], $data['updated_by'], date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), $data['extra_info'], $data['tags']);
    $stmt->execute();
    echo json_encode(array("message" => "Company inserted successfully"));
}

function update($data) {
    $mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
    if ($mysqli->connect_error) {
        echo json_encode(array("error" => "Connection failed"));
        return;
    }

    $stmt = $mysqli->prepare("UPDATE company SET company_code = ?, company_name = ?, legal_name = ?, registration_no = ?, tax_id = ?, industry = ?, business_type = ?, founded_date = ?, ceo_name = ?, num_employees = ?, annual_revenue = ?, website = ?, email = ?, phone = ?, alt_phone = ?, fax = ?, address_line1 = ?, address_line2 = ?, city = ?, state = ?, postal_code = ?, country = ?, billing_address1 = ?, billing_address2 = ?, billing_city = ?, billing_state = ?, billing_postal = ?, billing_country = ?, shipping_address1 = ?, shipping_address2 = ?, shipping_city = ?, shipping_state = ?, shipping_postal = ?, shipping_country = ?, currency = ?, timezone = ?, language = ?, logo_url = ?, linkedin_url = ?, facebook_url = ?, twitter_url = ?, instagram_url = ?, notes = ?, status = ?, created_by = ?, updated_by = ?, updated_at = ?, extra_info = ?, tags = ? WHERE id = ?");
    $stmt->bind_param("ssssiissssisisissiissiiissiisiiiiiiiisi", 
        $data['company_code'], $data['company_name'], $data['legal_name'], $data['registration_no'], $data['tax_id'], $data['industry'], $data['business_type'], $data['founded_date'], $data['ceo_name'], $data['num_employees'], $data['annual_revenue'], $data['website'], $data['email'], $data['phone'], $data['alt_phone'], $data['fax'], $data['address_line1'], $data['address_line2'], $data['city'], $data['state'], $data['postal_code'], $data['country'], $data['billing_address1'], $data['billing_address2'], $data['billing_city'], $data['billing_state'], $data['billing_postal'], $data['billing_country'], $data['shipping_address1'], $data['shipping_address2'], $data['shipping_city'], $data['shipping_state'], $data['shipping_postal'], $data['shipping_country'], $data['currency'], $data['timezone'], $data['language'], $data['logo_url'], $data['linkedin_url'], $data['facebook_url'], $data['twitter_url'], $data['instagram_url'], $data['notes'], $data['status'], $data['created_by'], $data['updated_by'], date("Y-m-d H:i:s"), $data['extra_info'], $data['tags'], $data['id']);
    $stmt->execute();
    echo json_encode(array("message" => "Company updated successfully"));
}

function deleteAll() {
    $mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
    if ($mysqli->connect_error) {
        echo json_encode(array("error" => "Connection failed"));
        return;
    }

    $stmt = $mysqli->prepare("DELETE FROM company");
    $stmt->execute();
    echo json_encode(array("message" => "All companies deleted successfully"));
}

function deleteById($data) {
    $mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
    if ($mysqli->connect_error) {
        echo json_encode(array("error" => "Connection failed"));
        return;
    }

    $stmt = $mysqli->prepare("DELETE FROM company WHERE id = ?");
    $stmt->bind_param("i", $data['id']);
    $stmt->execute();
    echo json_encode(array("message" => "Company deleted successfully"));
}

function getMaxId() {
    $mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
    if ($mysqli->connect_error) {
        echo json_encode(array("error" => "Connection failed"));
        return;
    }

    $stmt = $mysqli->prepare("SELECT MAX(id) AS max_id FROM company");
    $stmt->execute();
    $result = $stmt->get_result();
    $maxId = $result->fetch_assoc()['max_id'];
    echo json_encode(array("message" => "Max ID: " . $maxId));
}

function getById($id) {
    $mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
    if ($mysqli->connect_error) {
        echo json_encode(array("error" => "Connection failed"));
        return;
    }

    $stmt = $mysqli->prepare("SELECT * FROM company WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $companyData = $result->fetch_assoc();
    echo json_encode($companyData);
}

function getAll() {
    $mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
    if ($mysqli->connect_error) {
        echo json_encode(array("error" => "Connection failed"));
        return;
    }

    $stmt = $mysqli->prepare("SELECT * FROM company");
    $stmt->execute();
    $result = $stmt->get_result();
    $companyData = array();
    while ($row = $result->fetch_assoc()) {
        $companyData[] = $row;
    }
    echo json_encode($companyData);
?>
```
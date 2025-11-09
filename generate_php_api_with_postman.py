import re
import os
import json
import requests

# === CONFIG ===
MODEL = "llama3"
INPUT_FILE = "schema.txt"
OUTPUT_DIR = "./generated_apis/"
POSTMAN_FILE = "crud_collection.json"
API_BASE_URL = "http://localhost"

os.makedirs(OUTPUT_DIR, exist_ok=True)

def generate_from_ollama(prompt):
    response = requests.post(
        "http://localhost:11434/api/generate",
        json={"model": MODEL, "prompt": prompt, "stream": False}
    )

    try:
        data = response.json()
    except Exception as e:
        print("❌ Invalid JSON response from Ollama:", response.text)
        raise e

    # Debug output
    print("🔍 Ollama response:", json.dumps(data, indent=2))

    if "response" not in data:
        raise KeyError(f"Ollama response missing 'response' key. Got keys: {list(data.keys())}")

    return data["response"]

# === READ SCHEMA ===
with open(INPUT_FILE, "r", encoding="utf-8") as f:
    schema_text = f.read()

table_blocks = re.findall(r"(CREATE TABLE.*?;)", schema_text, flags=re.DOTALL | re.IGNORECASE)
if not table_blocks:
    print("❌ No CREATE TABLE statements found.")
    exit()

postman_collection = {
    "info": {
        "name": "Auto-generated PHP CRUD APIs",
        "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
    },
    "item": []
}

for block in table_blocks:
    match = re.search(r"CREATE TABLE\s+`?(\w+)`?", block, re.IGNORECASE)
    if not match:
        continue
    table_name = match.group(1)
    print(f"🧩 Generating API for {table_name}...")

    # === Updated prompt with escaped curly braces ===
    prompt = f"""
You are an expert PHP backend developer.
Generate a PHP CRUD API file for the following MySQL table schema:

{block}

The generated PHP file must follow this **sample structure and logic** exactly (use it as a reference pattern):

---------------- SAMPLE API STRUCTURE ----------------
<?php
header("Content-Type: application/json");
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Access-Control-Allow-Headers, Authorization");

include 'dbConnection.php';

$input_data = file_get_contents("php://input");
$request_data = json_decode($input_data, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($request_data['action'])) {{
    $action = $request_data['action'];

    switch ($action) {{
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
    }}
}}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {{
    if (isset($_GET['id'])) {{
        getById($_GET['id']);
    }} else {{
        getAll();
    }}
}}
?>
------------------------------------------------------

Follow this exact structure and logic:
- Use **generic POST actions**: "insert", "update", "delete", "deleteById", "getMaxId"
- Use **GET** for fetching (single or all records)
- Use **MySQLi (object-oriented)**, not PDO
- Include **CORS headers** and **JSON responses**
- Use **prepared statements** with parameter binding
- Validate all required input parameters
- Return clear success/error messages as JSON
- Include `include 'dbConnection.php';`
- Function names: insert(), update(), deleteAll(), deleteById(), getMaxId(), getById(), getAll()
- Output **only valid PHP code**, no markdown or extra text
"""

    php_code = generate_from_ollama(prompt).strip()

    out_file = os.path.join(OUTPUT_DIR, f"{table_name}_api.php")
    with open(out_file, "w", encoding="utf-8") as f:
        f.write(php_code)
    print(f"✅ Saved {out_file}")

    base_url = f"{API_BASE_URL}/{table_name}_api.php"
    postman_collection["item"].append({
        "name": table_name,
        "item": [
            {"name": "Get All", "request": {"method": "GET", "url": {"raw": base_url}}},
            {"name": "Get by ID", "request": {"method": "GET", "url": {"raw": f"{base_url}?id=1"}}},
            {"name": "Insert", "request": {
                "method": "POST",
                "header": [{"key": "Content-Type", "value": "application/json"}],
                "body": {"mode": "raw", "raw": '{"action":"insert","key":"value"}'},
                "url": {"raw": base_url}
            }},
            {"name": "Update", "request": {
                "method": "POST",
                "header": [{"key": "Content-Type", "value": "application/json"}],
                "body": {"mode": "raw", "raw": '{"action":"update","id":1,"key":"new_value"}'},
                "url": {"raw": base_url}
            }},
            {"name": "Delete All", "request": {
                "method": "POST",
                "header": [{"key": "Content-Type", "value": "application/json"}],
                "body": {"mode": "raw", "raw": '{"action":"delete"}'},
                "url": {"raw": base_url}
            }},
            {"name": "Delete by ID", "request": {
                "method": "POST",
                "header": [{"key": "Content-Type", "value": "application/json"}],
                "body": {"mode": "raw", "raw": '{"action":"deleteById","id":1}'},
                "url": {"raw": base_url}
            }}
        ]
    })

with open(POSTMAN_FILE, "w", encoding="utf-8") as f:
    json.dump(postman_collection, f, indent=2)

print("\n🎉 Done! Generated PHP CRUD APIs and Postman collection.")
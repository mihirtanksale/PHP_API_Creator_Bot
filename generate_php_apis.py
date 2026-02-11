import re
import os
import json
import requests

# === CONFIG ===
MODEL = "llama3"
INPUT_FILE = "schema.txt"
OUTPUT_DIR = "./generated_apis/"
API_BASE_URL = "{{baseUrl}}"  # ✅ Postman variable instead of localhost

os.makedirs(OUTPUT_DIR, exist_ok=True)

def generate_from_ollama(prompt):
    response = requests.post(
        "http://localhost:11500/api/generate",
        json={"model": MODEL, "prompt": prompt, "stream": False}
    )

    try:
        data = response.json()
    except Exception as e:
        print("❌ Invalid JSON response from Ollama:", response.text)
        raise e

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

for block in table_blocks:
    match = re.search(r"CREATE TABLE\s+`?(\w+)`?", block, re.IGNORECASE)
    if not match:
        continue

    table_name = match.group(1)
    print(f"🧩 Generating API for {table_name}...")

    # === Extract column names from schema ===
    columns = re.findall(r"`(\w+)`\s+[\w()]+", block)
    columns = [c for c in columns if c.lower() not in ("id", "created_at", "updated_at")]

    # === Prompt to Ollama ===
    prompt = f"""
You are an expert PHP backend developer.
Generate a PHP CRUD API file for the following MySQL table schema:

{block}

Follow this **structure and logic** exactly:

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

Rules:
- Use MySQLi (object-oriented)
- Use prepared statements
- Include `include 'dbConnection.php';`
- Functions: insert(), update(), deleteAll(), deleteById(), getMaxId(), getById(), getAll()
- Output only valid PHP code (no markdown, comments, or text)
"""

    php_code = generate_from_ollama(prompt).strip()

    # === Save generated PHP ===
    out_file = os.path.join(OUTPUT_DIR, f"{table_name}_api.php")
    with open(out_file, "w", encoding="utf-8") as f:
        f.write(php_code)
    print(f"✅ Saved {out_file}")

print("\n🎉 Done! Generated PHP CRUD API files.")
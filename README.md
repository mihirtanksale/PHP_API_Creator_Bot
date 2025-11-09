# PHP API Creator Bot

Auto-generate simple PHP CRUD APIs from a SQL schema using an LLM (local Ollama) and produce a Postman collection.

## What this does

- Reads `schema.txt` for `CREATE TABLE` statements.
- For each table, calls a local LLM endpoint (configured in `generate_php_api_with_postman.py`) to generate a PHP CRUD API file that follows a fixed pattern.
- Saves generated API files under `generated_apis/` named `<table>_api.php`.
- Writes a Postman collection to `crud_collection.json` with sample requests for each API.

## Repository layout

- `generate_php_api_with_postman.py` - main script. Reads `schema.txt`, generates PHP files via Ollama, writes `crud_collection.json`.
- `schema.txt` - example schema containing `CREATE TABLE` statements (one or more). The script extracts blocks matching `CREATE TABLE ...;`.
- `crud_collection.json` - Postman collection produced by the script (auto-generated).
- `generated_apis/` - output folder for generated PHP files (e.g. `users_api.php`, `products_api.php`).

## Requirements

- Python 3.8+
- `requests` Python package
- A running Ollama-like local model endpoint at `http://localhost:11434/api/generate` (the script expects this by default). The script can be adjusted to use different LLM endpoints or models.
- A web server + PHP + MySQL for running the generated PHP APIs. The generated PHP files expect an `include 'dbConnection.php';` to exist and to provide a working MySQLi connection.

## Quick start

1. Install Python deps:

```powershell
python -m pip install requests
```

2. Edit `schema.txt` to include the CREATE TABLE statements you want to generate APIs for. Example (already present):

```sql
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  email VARCHAR(100),
  password VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255),
  price DECIMAL(10,2),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

3. Configure the script (optional):

Open `generate_php_api_with_postman.py` and change these constants if necessary:

- `MODEL` - LLM model name
- `INPUT_FILE` - schema file path
- `OUTPUT_DIR` - where PHP files are written
- `POSTMAN_FILE` - name/path for the generated Postman collection
- `API_BASE_URL` - base url used in the Postman collection (default: `http://localhost`)

4. Run the generator:

```powershell
python .\generate_php_api_with_postman.py
```

After a successful run you'll see new files in `generated_apis/` and `crud_collection.json` updated.

## Running the generated PHP APIs

- Place the generated PHP files in a directory served by a PHP-capable web server (e.g., XAMPP `htdocs`), or point your web server root to the repository folder.
- Provide a `dbConnection.php` file that the generated code can include. The generator's PHP expects a MySQLi connection object; generated files use names like `$db` or `$dbConnection` (there is inconsistent naming across different outputs), so ensure your `dbConnection.php` defines whichever variable the generated file expects.

Example minimal `dbConnection.php` (edit credentials):

```php
<?php
$servername = "localhost";
$username = "root";
$password = ""; // set your password
$dbname = "your_database";

// Using MySQLi (object-oriented)
$db = new mysqli($servername, $username, $password, $dbname);
if ($db->connect_error) {
    die(json_encode(["error" => "DB connection failed: " . $db->connect_error]));
}
?>
```

Note: some generated files may refer to `$dbConnection` instead of `$db` — if so, either change the generated file or create `$dbConnection = $db;` in `dbConnection.php` to satisfy both names.

## Postman collection

`crud_collection.json` will be created/overwritten by the script and contains simple requests for `Get All`, `Get by ID`, `Insert`, `Update`, `Delete All`, and `Delete by ID` for each table.

## Security & limitations

- The generated PHP code uses prepared statements in many outputs, but the generator may produce inconsistencies or minor bugs. Review generated code before deploying to production.
- Passwords in the `users` table example are plain strings. Do NOT store plaintext passwords in production — use proper hashing (password_hash / password_verify).
- The script depends on a local LLM endpoint. If you don't have Ollama, adapt `generate_from_ollama()` to call another model endpoint or stub responses for offline development.

## Troubleshooting

- If no `CREATE TABLE` blocks are found, confirm `schema.txt` contains valid `CREATE TABLE ...;` statements (script uses a regex to extract blocks).
- If the generated PHP fails due to undefined `$db` or `$dbConnection`, update `dbConnection.php` to define both variables:

```php
<?php
// after creating $db
$dbConnection = $db;
?>
```

- If the script prints `Invalid JSON response from Ollama`, check that your local LLM endpoint is up and returning JSON with a top-level `response` key.

## Next steps / suggestions

- Add a small unit test harness that validates generated PHP files parse cleanly (e.g., `php -l file.php` lint check).
- Add optional `--dry-run` or `--local` mode to the generator to produce sample PHP templates without calling an LLM.
- Normalize generated PHP to consistently use a single connection variable name and consistent prepared-statement patterns.
- Add support for choosing the DB connector (PDO vs MySQLi) via a CLI flag.

## License

This repository contains example code. Add a license file if you plan to share or publish.

---

If you want, I can also:
- Add a minimal `dbConnection.php` file in the repo (tested locally),
- Add a PowerShell script to run the generator and open the Postman collection,
- Or normalize variable names in the generated files after generation.

Tell me which of these you'd like next.
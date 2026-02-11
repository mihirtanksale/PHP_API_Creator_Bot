import os
import json
import re
import requests

# === CONFIG ===
MODEL = "llama3"
API_URL = "http://localhost:11434/api/generate"  # ✅ Correct Ollama default port
GENERATED_DIR = "./generated_apis"
POSTMAN_FILE = "crud_collection.json"
BASE_URL = "https://mihirsystems.com/"

# === Step 1: Find generated PHP files ===
php_files = [f for f in os.listdir(GENERATED_DIR) if f.endswith(".php")]
if not php_files:
    print("❌ No PHP API files found in ./generated_apis/")
    exit()

print(f"🧩 Found {len(php_files)} PHP API files.")

# === Step 2: Read schema text if available ===
schema_text = ""
if os.path.exists("schema.txt"):
    with open("schema.txt", "r", encoding="utf-8") as f:
        schema_text = f.read()

# === Step 3: Build prompt for Ollama ===
file_list = "\n".join([f"- {f}" for f in php_files])

prompt = f"""
You are an expert API and Postman collection generator.
Generate a **valid Postman collection (v2.1 JSON)** for these PHP CRUD APIs hosted at {BASE_URL}.

Each API endpoint corresponds to one PHP file and supports the following actions:
- GET (getAll, getById)
- POST (insert, update, delete, deleteById, getMaxId)

Here are the API PHP files:
{file_list}

If available, here is the MySQL schema (use column names for realistic example JSON bodies):
{schema_text}

Requirements:
- Output only valid JSON (no markdown, no commentary)
- Use {BASE_URL} as the base URL for requests
- Each PHP file should be a separate folder in the collection
- Include sample data in request bodies (e.g., {{ "name": "John", "email": "test@example.com" }})
- The structure should follow Postman Collection v2.1 format exactly
"""

# === Step 4: Call Ollama API (streamed to avoid timeout) ===
print("🤖 Generating Postman collection via Ollama (streaming)...")

try:
    with requests.post(
        API_URL,
        json={"model": MODEL, "prompt": prompt, "stream": True},
        stream=True,
        timeout=300
    ) as response:
        response.raise_for_status()
        output_chunks = []
        for line in response.iter_lines(decode_unicode=True):
            if not line:
                continue
            try:
                data = json.loads(line)
                if "response" in data:
                    output_chunks.append(data["response"])
                if data.get("done"):
                    break
            except json.JSONDecodeError:
                continue
        ollama_output = "".join(output_chunks).strip()
except requests.exceptions.RequestException as e:
    print(f"❌ Failed to connect or stream from Ollama: {e}")
    exit()

# === Step 5: Validate and save JSON ===
try:
    collection_json = json.loads(ollama_output)
except json.JSONDecodeError:
    print("❌ Ollama did not return valid JSON. Here's a preview of the first 500 chars:")
    print(ollama_output[:500])
    exit()

with open(POSTMAN_FILE, "w", encoding="utf-8") as f:
    json.dump(collection_json, f, indent=2)

print(f"✅ Postman collection successfully generated and saved to: {POSTMAN_FILE}")
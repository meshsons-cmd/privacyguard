import subprocess
import sys

def run_cmd(cmd):
    try:
        result = subprocess.run(cmd, capture_output=True, text=True, check=True)
        print(f"SUCCESS: {result.stdout}")
    except subprocess.CalledProcessError as e:
        print(f"ERROR: {e.stderr}\n{e.stdout}")

if __name__ == "__main__":
    print("Installing requirements...")
    run_cmd([sys.executable, "-m", "pip", "install", "fastapi", "uvicorn", "pydantic", "pydantic-settings", "beautifulsoup4", "requests", "langchain-google-genai", "langchain"])
    print("Checking pydantic_settings...")
    run_cmd([sys.executable, "-c", "import pydantic_settings; print('OK')"])

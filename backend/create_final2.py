import os
import subprocess
import sys

venv_dir = "venv_final2"
print("Creating venv_final2...")
subprocess.run([sys.executable, "-m", "venv", venv_dir], check=True)

pip_exe = os.path.join(venv_dir, "Scripts", "python.exe")

packages = [
    "fastapi", "uvicorn", "pydantic", "pydantic-settings", 
    "beautifulsoup4", "requests", "langchain-google-genai", "langchain", "python-dotenv"
]

print("Installing packages quietly...")
result = subprocess.run([pip_exe, "-m", "pip", "install", "-q", "--progress-bar", "off"] + packages, capture_output=True, text=True)
print("Return code:", result.returncode)
print("STDOUT:", result.stdout)
print("STDERR:", result.stderr)

print("Checking uvicorn...")
result2 = subprocess.run([pip_exe, "-m", "uvicorn", "--version"], capture_output=True, text=True)
print("UVICORN Return code:", result2.returncode)
print("UVICORN STDOUT:", result2.stdout)
print("UVICORN STDERR:", result2.stderr)

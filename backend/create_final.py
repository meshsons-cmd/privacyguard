import os
import subprocess
import sys

venv_dir = "venv_final"

print("Creating venv_final...")
subprocess.run([sys.executable, "-m", "venv", venv_dir], check=True)

pip_exe = os.path.join(venv_dir, "Scripts", "python.exe")

packages = [
    "fastapi", "uvicorn", "pydantic", "pydantic-settings", 
    "beautifulsoup4", "requests", "langchain-google-genai", "langchain", "python-dotenv"
]

print("Installing packages quietly...")
subprocess.run([pip_exe, "-m", "pip", "install", "-q", "--progress-bar", "off"] + packages, check=True)

print("Checking uvicorn...")
subprocess.run([pip_exe, "-m", "uvicorn", "--version"], check=True)

print("ALL SUCCESS!")

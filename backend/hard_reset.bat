@echo off
cd /d D:\www\PrivacyGuard\backend
echo "Renaming old venv to venv_old_2..."
move venv venv_old_2 >nul 2>&1

echo "Creating fresh venv..."
python -m venv venv

echo "Installing pip packages..."
venv\Scripts\python.exe -m pip install --upgrade pip
venv\Scripts\python.exe -m pip install fastapi uvicorn pydantic pydantic-settings beautifulsoup4 requests langchain-google-genai langchain python-dotenv

echo "Verifying fastapi..."
venv\Scripts\python.exe -c "import fastapi; print('FASTAPI INSTALLED')"

echo "Starting uvicorn test..."
venv\Scripts\python.exe -m uvicorn main:app --host 127.0.0.1 --port 8001

@echo off
cd /d D:\www\PrivacyGuard\backend
echo "Creating venv_prod..."
python -m venv venv_prod

echo "Installing pip packages..."
venv_prod\Scripts\python.exe -m pip install --upgrade pip
venv_prod\Scripts\python.exe -m pip install fastapi uvicorn pydantic pydantic-settings beautifulsoup4 requests langchain-google-genai langchain python-dotenv

echo "Verifying fastapi..."
venv_prod\Scripts\python.exe -c "import fastapi; print('FASTAPI INSTALLED')"

echo "Starting uvicorn test..."
venv_prod\Scripts\python.exe -m uvicorn main:app --host 127.0.0.1 --port 8001

@echo off
cd /d D:\www\PrivacyGuard\backend
echo Fixing Python Environment...
rmdir /s /q venv
python -m venv venv
venv\Scripts\python.exe -m pip install fastapi uvicorn pydantic pydantic-settings pydantic-core
venv\Scripts\python.exe -m pip install -r requirements.txt
echo Checking imports...
venv\Scripts\python.exe -c "import pydantic_settings; print('Pydantic Settings OK')"
venv\Scripts\python.exe -c "from pydantic import BaseModel; print('Pydantic OK')"
echo Ready.

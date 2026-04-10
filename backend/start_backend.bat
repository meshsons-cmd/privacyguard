@echo off
cd /d D:\www\PrivacyGuard\backend
python -m venv venv
call venv\Scripts\activate.bat
python -m pip install -r requirements.txt
uvicorn main:app --host 127.0.0.1 --port 8001 --reload
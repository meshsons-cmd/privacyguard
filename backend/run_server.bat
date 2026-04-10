@echo off
cd /d D:\www\PrivacyGuard\backend
venv\Scripts\python.exe -m uvicorn main:app --host 0.0.0.0 --port 8001 --reload > uvicorn.log 2>&1

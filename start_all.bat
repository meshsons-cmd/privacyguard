@echo off
echo Starting PrivacyGuard AI Servers...

start "Backend API" cmd /k "cd backend && call venv\Scripts\activate && uvicorn main:app --port 8001"
start "Frontend Server" cmd /k "cd frontend && php artisan serve --port=8000"
start "Frontend Assets" cmd /k "cd frontend && npm run dev"

echo All services are booting up in separate windows.
exit
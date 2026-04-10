@echo off
cd /d D:\www\PrivacyGuard\backend
echo "Removing old venv..." > setup_status.txt
rmdir /s /q venv >> setup_status.txt 2>&1
echo "Creating new venv..." >> setup_status.txt
python -m venv venv >> setup_status.txt 2>&1
echo "Installing pip packages..." >> setup_status.txt
venv\Scripts\python.exe -m pip install fastapi uvicorn pydantic pydantic-settings beautifulsoup4 requests langchain-google-genai langchain python-dotenv >> setup_status.txt 2>&1
venv\Scripts\python.exe -m pip install -r requirements.txt >> setup_status.txt 2>&1
echo "Testing import..." >> setup_status.txt
venv\Scripts\python.exe -c "import pydantic_settings; print('ALL GOOD')" >> setup_status.txt 2>&1
echo "Done" >> setup_status.txt

@echo off
cd /d D:\www\PrivacyGuard\backend
venv\Scripts\python.exe -m pip install "pydantic-core==2.41.5" "pydantic==2.10.6"
venv\Scripts\python.exe -c "import pydantic; print(pydantic.VERSION)"

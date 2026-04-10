import os
import shutil

venv_path = "venv"
if os.path.exists(venv_path):
    print("Removing old venv...")
    shutil.rmtree(venv_path, ignore_errors=True)
    print("Removed.")
else:
    print("No old venv.")

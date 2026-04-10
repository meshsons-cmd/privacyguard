import os
from pydantic_settings import BaseSettings

class Settings(BaseSettings):
    PROJECT_NAME: str = "PrivacyGuard AI Auditor Engine"
    API_V1_STR: str = "/api/v1"
    GOOGLE_API_KEY: str = ""
    API_SECRET_KEY: str = ""
    MODEL_NAME: str = "gemini-1.5-flash"

    class Config:
        extra = "allow"
        env_file = ".env"

settings = Settings()

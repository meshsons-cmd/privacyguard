from fastapi import FastAPI, HTTPException, Depends
from fastapi.middleware.cors import CORSMiddleware
from core.config import settings
from models.schemas import AuditRequest, AuditResponse, LegalTextAuditRequest
from services.scraper import ScraperService
from services.auditor import AuditorService
import logging

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

app = FastAPI(
    title=settings.PROJECT_NAME,
    openapi_url=f"{settings.API_V1_STR}/openapi.json"
)

# CORS configuration for Laravel frontend
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

auditor_service = AuditorService()
scraper_service = ScraperService()

@app.get("/")
def read_root():
    return {"message": "Welcome to PrivacyGuard AI Auditor Engine API"}

@app.post(f"{settings.API_V1_STR}/audit/url", response_model=AuditResponse)
async def audit_url(request: AuditRequest):
    """
    1. Takes a URL.
    2. Scrapes the homepage to find the Privacy Policy.
    3. Extracts text.
    4. Passes to Gemini 1.5 Flash Auditor.
    """
    try:
        logger.info(f"Scraping URL: {request.url}")
        legal_text = scraper_service.extract_legal_text(request.url)
        
        logger.info(f"Analyzing extracted legal text...")
        audit_result = await auditor_service.analyze_text(legal_text)
        
        return AuditResponse(**audit_result)
        
    except ValueError as e:
        logger.error(f"Value error: {str(e)}")
        raise HTTPException(status_code=400, detail=str(e))
    except Exception as e:
        logger.error(f"Unexpected error: {str(e)}")
        raise HTTPException(status_code=500, detail="An internal server error occurred.")

@app.post(f"{settings.API_V1_STR}/audit/text", response_model=AuditResponse)
async def audit_text(request: LegalTextAuditRequest):
    """
    1. Takes raw legal text.
    2. Passes to Gemini 1.5 Flash Auditor.
    """
    try:
        logger.info(f"Analyzing raw legal text...")
        audit_result = await auditor_service.analyze_text(request.legal_text)
        
        return AuditResponse(**audit_result)
        
    except ValueError as e:
        logger.error(f"Value error: {str(e)}")
        raise HTTPException(status_code=400, detail=str(e))
    except Exception as e:
        logger.error(f"Unexpected error: {str(e)}")
        raise HTTPException(status_code=500, detail="An internal server error occurred.")

if __name__ == "__main__":
    import uvicorn
    uvicorn.run("main:app", host="0.0.0.0", port=8001, reload=True)

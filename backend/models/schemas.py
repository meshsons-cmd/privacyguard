from typing import List
from pydantic import BaseModel, HttpUrl

class AuditRequest(BaseModel):
    url: str

class LegalTextAuditRequest(BaseModel):
    legal_text: str

class AuditResponse(BaseModel):
    compliance_score: int
    missing_clauses: List[str]
    risk_level: str
    summary: str

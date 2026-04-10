import json
import logging
from langchain_google_genai import ChatGoogleGenerativeAI
from langchain_core.prompts import PromptTemplate
from langchain_core.output_parsers import JsonOutputParser
from core.config import settings

logger = logging.getLogger(__name__)

class AuditorService:
    def __init__(self):
        # We enforce a JSON output format via the prompt and LangChain's JSON parser
        self.parser = JsonOutputParser()
        
        self.prompt = PromptTemplate(
            template="""You are a strict, expert EU data protection lawyer specializing in GDPR compliance audits. 
You are analyzing a provided privacy policy or legal text.
Your task is to identify gaps against the core requirements of GDPR and return a structured JSON response.

Requirements to check:
1. Transparency & Information (Articles 13 & 14): Identity of controller, DPO details, purposes of processing, legal basis, data subjects' rights, retention periods, data transfers outside EU.
2. Lawfulness of Processing (Article 6): Clear statement of legal bases used.
3. User Rights (Articles 15-22): Right to access, rectification, erasure, restrict processing, data portability, object to processing.

Evaluate the following legal text:
---
{legal_text}
---

Return ONLY valid JSON that conforms to the following schema:
{{
  "compliance_score": (integer between 0 and 100),
  "missing_clauses": ["List of missing or inadequate GDPR requirements"],
  "risk_level": (string: "Low", "Medium", "High", or "Critical"),
  "summary": (A concise, 2-3 sentence summary of the main compliance issues found)
}}

{format_instructions}""",
            input_variables=["legal_text"],
            partial_variables={"format_instructions": self.parser.get_format_instructions()},
        )

    def _get_chain(self):
        if not settings.GOOGLE_API_KEY:
            raise ValueError("GOOGLE_API_KEY is not configured in the backend environment.")
            
        # Using Gemini 1.5 Flash via langchain
        llm = ChatGoogleGenerativeAI(
            model=settings.MODEL_NAME,
            google_api_key=settings.GOOGLE_API_KEY,
            temperature=0.0
        )
        return self.prompt | llm | self.parser

    async def analyze_text(self, text: str) -> dict:
        """
        Takes raw legal text and returns a structured GDPR compliance audit dictionary.
        """
        try:
            chain = self._get_chain()
            # We use an async invocation
            result = await chain.ainvoke({"legal_text": text})
            return result
        except ValueError as e:
            raise e
        except Exception as e:
            logger.error(f"Failed to analyze text with Gemini: {str(e)}")
            raise ValueError(f"Error communicating with AI Auditor Engine: {str(e)}")

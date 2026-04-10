# PrivacyGuard AI - Technical Architecture Document

## Overview
PrivacyGuard AI is a SaaS platform that audits websites for GDPR compliance using AI, generating remediation reports for international clients.

## System Components

### 1. Python AI Auditor Engine (Backend)
- **Framework**: FastAPI (High performance, async support, built-in Swagger UI)
- **AI Integration**: LangChain + Google Gemini 1.5 Flash
- **Web Scraper**: BeautifulSoup4 + requests (for extracting Privacy Policy text)
- **Role**: Exposes secure HTTP endpoints for the Laravel frontend to trigger audits, scrape URLs, and analyze legal text.

### 2. Laravel SaaS Dashboard (Frontend)
- **Framework**: Laravel 11 + Inertia.js + Vue 3 (or Blade)
- **Styling**: Tailwind CSS (Elite Minimalist, Ultra-Premium, deep blues, clean whites)
- **Database**: MySQL / PostgreSQL
- **Payments**: Stripe Checkout
- **Role**: Manages user authentication, subscription, dashboard UI, and displays the generated audit reports.

## Data Flow
1. **User Action**: Client enters a website URL in the Laravel Dashboard.
2. **Frontend Request**: Laravel sends a secure HTTP POST request to the Python API (`/api/v1/audit/url`).
3. **Web Scraper**: Python API uses the Web Scraper service to crawl the homepage, locate the "Privacy Policy" link, and extract its text.
4. **AI Auditor Engine**: The extracted text is passed to the LangChain Agent. Gemini 1.5 Flash evaluates the text against GDPR requirements and returns a structured JSON (Compliance Score, Missing Clauses, Risk Level).
5. **Response**: Python API returns the structured JSON back to Laravel.
6. **Display**: Laravel saves the report to the database and displays it to the client using the premium UI.

## File Structure (Python Backend)
```
backend/
├── main.py                 # FastAPI application and route definitions
├── requirements.txt        # Python dependencies
├── .env                    # Environment variables (e.g., GOOGLE_API_KEY)
├── core/
│   └── config.py           # Application settings and configurations
├── models/
│   └── schemas.py          # Pydantic models for structured data validation
└── services/
    ├── auditor.py          # LangChain and Gemini 1.5 Flash integration
    └── scraper.py          # Web crawling and text extraction logic
```

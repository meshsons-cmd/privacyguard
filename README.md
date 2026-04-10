# 🛡️ PrivacyGuard AI

**Automated AI-Powered GDPR Compliance Auditor**

PrivacyGuard AI is a cutting-edge SaaS platform designed to help businesses instantly evaluate their website's privacy policies against strict EU data protection standards (GDPR). Powered by advanced AI models, it provides rapid, actionable compliance insights.

---

## ✨ Key Features

*   🤖 **AI-Powered Auditing:** Simply input a website URL. Our AI engine automatically crawls the site, locates the privacy policy, and extracts the legal text for deep analysis.
*   📊 **Compliance Scoring:** Receive an immediate compliance score out of 100, along with a distinct Risk Level assessment (Low, Medium, High, Critical).
*   💡 **Vulnerability Detection:** Identifies exact missing clauses, legal loopholes, and areas of non-compliance within the text.
*   💳 **Seamless Monetization:** Integrated with Razorpay for a smooth upgrade path to unlock premium remediation steps.
*   📄 **PDF Reporting:** Generate and download professional, boardroom-ready PDF compliance reports.

## 🛠️ Technology Stack

This platform is built using a modern, decoupled microservices architecture to ensure high performance, scalability, and seamless AI integration.

**Frontend Portal:**
*   **Framework:** Laravel (PHP)
*   **UI/UX:** Vue.js 3 via Inertia.js (Single Page Application experience)
*   **Styling:** Tailwind CSS
*   **Payments:** Razorpay SDK
*   **Reporting:** DomPDF

**AI Auditor Engine (Backend):**
*   **Framework:** FastAPI (Python)
*   **AI Model:** Google Gemini 1.5 Flash via LangChain
*   **Web Scraping:** BeautifulSoup4 & Requests

## 🚀 Cloud Architecture
This repository is fully configured for modern PaaS deployment (Render, Railway) with isolated microservices, secure API proxies, and dynamic cloud environment variables.
from playwright.sync_api import sync_playwright
from bs4 import BeautifulSoup
from typing import Optional
import urllib.parse
import re
import logging
import traceback

logger = logging.getLogger(__name__)

class BrowserResponse:
    def __init__(self, text, url, status_code=200):
        self.text = text
        self.url = url
        self.status_code = status_code

class ScraperService:
    @staticmethod
    def fetch_with_browser(url: str):
        try:
            with sync_playwright() as p:
                browser = p.chromium.launch(headless=True)
                page = browser.new_page()
                
                response = page.goto(url, timeout=60000)
                
                content = page.content()
                final_url = page.url
                status_code = response.status if response else 200
                
                browser.close()
                
                logger.info(f"Fetched {url} - Status: {status_code}")
                if content:
                    logger.info(f"HTML (first 300 chars): {content[:300]}")
                    
                return BrowserResponse(text=content, url=final_url, status_code=status_code)
        except Exception as e:
            print("Playwright Error:", str(e))
            print(traceback.format_exc())
            logger.error(f"Fetch error for {url}: {e}")
            return None

    @staticmethod
    def get_privacy_policy_url(base_url: str) -> Optional[str]:
        response = ScraperService.fetch_with_browser(base_url)
        
        if not response:
            raise ValueError("❌ Unable to access website. It may be blocking bots or is down.")
        
        if response.status_code in [403, 401, 503]:
            # It might be a protected site like Cloudflare that we couldn't bypass
            raise ValueError("⚠️ Website is protected (Cloudflare/bot protection). Try manual URL.")
            
        soup = BeautifulSoup(response.text, 'html.parser')
        
        keywords = [
            "privacy",
            "privacy-policy",
            "privacy policy",
            "data protection",
            "gdpr"
        ]
        
        exact_match = None
        partial_match = None
        other_match = None
        
        for a_tag in soup.find_all('a', href=True):
            text = a_tag.get_text().strip().lower()
            href = a_tag['href'].strip().lower()
            
            if not href or href.startswith('#') or href.startswith('javascript:') or href.startswith('mailto:') or href.startswith('tel:'):
                continue
                
            absolute_url = urllib.parse.urljoin(response.url, a_tag['href'].strip())
            path = urllib.parse.urlparse(absolute_url).path.strip('/').lower()
            
            # 1. Exact match "privacy-policy"
            if path == 'privacy-policy' or text == 'privacy policy' or text == 'privacy-policy':
                exact_match = absolute_url
                break
                
            # 2. Contains "privacy"
            if not partial_match:
                if 'privacy' in text or 'privacy' in path:
                    partial_match = absolute_url
                    
            # 3. Others (data protection, gdpr)
            if not other_match and not partial_match:
                if any(kw in text for kw in keywords) or any(kw in path for kw in keywords):
                    other_match = absolute_url
                    
        best_match = exact_match or partial_match or other_match
        
        if best_match:
            logger.info(f"Detected privacy URL from links: {best_match}")
            return best_match
            
        # Fallback URLs
        fallbacks = [
            '/privacy-policy',
            '/privacy',
            '/privacy-policy.html',
            '/legal/privacy-policy'
        ]
        
        for fallback in fallbacks:
            fallback_url = urllib.parse.urljoin(response.url, fallback)
            fb_response = ScraperService.fetch_with_browser(fallback_url)
            if fb_response and fb_response.status_code == 200:
                logger.info(f"Detected privacy URL from fallbacks: {fallback_url}")
                return fallback_url
                
        return None

    @staticmethod
    def extract_legal_text(url: str) -> str:
        """Crawls a URL, finds the privacy policy, and extracts its text."""
        try:
            # 1. Find the privacy policy URL
            privacy_url = ScraperService.get_privacy_policy_url(url)
            
            if not privacy_url:
                raise ValueError("❌ Privacy Policy page not found automatically. Please enter it manually.")
                
            logger.info(f"Final selected privacy URL: {privacy_url}")
            
            # 2. Fetch the actual privacy policy page
            privacy_response = ScraperService.fetch_with_browser(privacy_url)
            
            if not privacy_response or privacy_response.status_code != 200:
                status = privacy_response.status_code if privacy_response else 'Failed'
                raise ValueError(f"❌ Unable to access the detected Privacy Policy page. Status: {status}")
                
            privacy_soup = BeautifulSoup(privacy_response.text, 'html.parser')
            
            # Extract text (remove scripts, styles, etc.)
            for script in privacy_soup(["script", "style", "nav", "footer", "header", "noscript", "svg", "iframe"]):
                script.extract()
                
            text = privacy_soup.get_text(separator=' ')
            
            # Clean up whitespace
            cleaned_text = re.sub(r'\s+', ' ', text).strip()
            
            if len(cleaned_text) < 100:
                raise ValueError("❌ The detected Privacy Policy page does not contain enough text.")
                
            return cleaned_text
        except Exception as e:
            print("ERROR:", str(e))
            print(traceback.format_exc())
            raise ValueError(str(e))

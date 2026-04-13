import requests
from bs4 import BeautifulSoup
from typing import Optional
import urllib.parse
import re

class ScraperService:
    @staticmethod
    def _find_privacy_link(base_url: str, soup: BeautifulSoup) -> Optional[str]:
        """Finds the 'Privacy Policy' link on the homepage."""
        keywords = ['privacy', 'privacy-policy', 'privacy policy', 'data protection']
        
        exact_match = None
        partial_match = None
        
        for a_tag in soup.find_all('a', href=True):
            text = a_tag.get_text().strip().lower()
            href = a_tag['href'].strip().lower()
            
            if not href or href.startswith('#') or href.startswith('javascript:'):
                continue
                
            absolute_url = urllib.parse.urljoin(base_url, a_tag['href'].strip())
            
            # Priority 1: Exact match on link text or URL path
            path = urllib.parse.urlparse(absolute_url).path.strip('/').lower()
            if text in keywords or path in keywords:
                exact_match = absolute_url
                break
                
            # Priority 2: Partial match (contains keyword)
            if not partial_match:
                if any(kw in text for kw in keywords) or any(kw in href for kw in keywords):
                    partial_match = absolute_url
                    
        return exact_match or partial_match

    @staticmethod
    def extract_legal_text(url: str) -> str:
        """Crawls a URL, finds the privacy policy, and extracts its text."""
        headers = {
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) PrivacyGuard-AuditorBot/1.0'
        }
        
        try:
            # 1. Fetch homepage HTML
            response = requests.get(url, headers=headers, timeout=10)
            response.raise_for_status()
            base_url = response.url # Use final resolved URL
            soup = BeautifulSoup(response.text, 'html.parser')
            
            # 2. & 3. & 4. & 5. Parse tags, search keywords, normalize, and select best match
            privacy_url = ScraperService._find_privacy_link(base_url, soup)
            
            # 7. Fallback only if no link found
            if not privacy_url:
                for fallback in ['/privacy-policy', '/privacy']:
                    fallback_url = urllib.parse.urljoin(base_url, fallback)
                    try:
                        fb_response = requests.get(fallback_url, headers=headers, timeout=5)
                        if fb_response.status_code == 200 and 'text/html' in fb_response.headers.get('Content-Type', '').lower():
                            privacy_url = fallback_url
                            break
                    except requests.RequestException:
                        continue
            
            if not privacy_url:
                raise ValueError("Privacy Policy not found")
            
            # Fetch Privacy Policy page
            privacy_response = requests.get(privacy_url, headers=headers, timeout=10)
            privacy_response.raise_for_status()
            privacy_soup = BeautifulSoup(privacy_response.text, 'html.parser')
            
            # Extract text (remove scripts, styles, etc.)
            for script in privacy_soup(["script", "style", "nav", "footer", "header"]):
                script.extract()
                
            text = privacy_soup.get_text(separator=' ')
            
            # Clean up whitespace
            cleaned_text = re.sub(r'\s+', ' ', text).strip()
            return cleaned_text
            
        except requests.RequestException as e:
            raise ValueError(f"Failed to access URL: {str(e)}")

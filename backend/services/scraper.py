import requests
from bs4 import BeautifulSoup
from typing import Optional
import urllib.parse
import re

class ScraperService:
    @staticmethod
    def _find_privacy_link(base_url: str, soup: BeautifulSoup) -> Optional[str]:
        """Finds the 'Privacy Policy' link on the homepage."""
        # Common text for privacy policy links
        privacy_keywords = ['privacy', 'privacy policy', 'data protection', 'legal']
        
        for a_tag in soup.find_all('a', href=True):
            text = a_tag.get_text().strip().lower()
            href = a_tag['href'].lower()
            
            # Look for keywords in either the link text OR the href URL itself
            if any(keyword in text for keyword in privacy_keywords) or any(keyword in href for keyword in privacy_keywords):
                # Handle relative URLs
                return urllib.parse.urljoin(base_url, a_tag['href'])
        return None

    @staticmethod
    def extract_legal_text(url: str) -> str:
        """Crawls a URL, finds the privacy policy, and extracts its text."""
        headers = {
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) PrivacyGuard-AuditorBot/1.0'
        }
        
        try:
            # 1. Fetch homepage
            response = requests.get(url, headers=headers, timeout=10)
            response.raise_for_status()
            soup = BeautifulSoup(response.text, 'html.parser')
            
            # 2. Find Privacy Policy link
            privacy_url = ScraperService._find_privacy_link(url, soup)
            
            if not privacy_url:
                raise ValueError("Could not locate a Privacy Policy link on the provided URL.")
            
            # 3. Fetch Privacy Policy page
            privacy_response = requests.get(privacy_url, headers=headers, timeout=10)
            privacy_response.raise_for_status()
            privacy_soup = BeautifulSoup(privacy_response.text, 'html.parser')
            
            # 4. Extract text (remove scripts, styles, etc.)
            for script in privacy_soup(["script", "style", "nav", "footer", "header"]):
                script.extract()
                
            text = privacy_soup.get_text(separator=' ')
            
            # Clean up whitespace
            cleaned_text = re.sub(r'\s+', ' ', text).strip()
            return cleaned_text
            
        except requests.RequestException as e:
            raise ValueError(f"Failed to access URL: {str(e)}")

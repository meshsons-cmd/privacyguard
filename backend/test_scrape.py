import requests
from bs4 import BeautifulSoup
import urllib.parse
import sys

def test_scrape(url):
    headers = {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
    }
    print(f"Fetching {url}...")
    try:
        response = requests.get(url, headers=headers, timeout=10)
        print(f"Status Code: {response.status_code}")
        
        soup = BeautifulSoup(response.text, 'html.parser')
        
        privacy_keywords = ['privacy', 'privacy policy', 'data protection', 'legal']
        found = False
        for a_tag in soup.find_all('a', href=True):
            text = a_tag.get_text().strip().lower()
            href = a_tag['href'].lower()
            
            # Check text OR href for keywords
            if any(keyword in text for keyword in privacy_keywords) or any(keyword in href for keyword in privacy_keywords):
                full_url = urllib.parse.urljoin(url, a_tag['href'])
                print(f"FOUND: Href='{a_tag['href']}' | Full='{full_url}'")
                found = True
                
        if not found:
            print("NO PRIVACY LINK FOUND!")
            
    except Exception as e:
        print(f"Error: {e}")

test_scrape("https://ourastroguruji.com/")

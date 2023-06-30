#/bin/python3

# workaround for a sphinx issue
# https://github.com/sphinx-doc/sphinx/issues/9730

from re import search
from sys import stdin

html = stdin.read().strip()

url = search(r"<link rel=\"canonical\" href=\"(?P<url>.+)\" \/>", html).group("url")

if url.endswith("/index.html"):
    newurl = url[:-10]
else:
    newurl = url[:-5] + "/"

print(html.replace(url, newurl))

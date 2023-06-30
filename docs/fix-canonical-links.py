#/bin/python3

# workaround for a sphinx issue
# https://github.com/sphinx-doc/sphinx/issues/9730

from re import search
from sys import stdin
from argparse import ArgumentParser, FileType

parser = ArgumentParser(
    description="Fix canonical links in Sphinx documentation built using DirectoryHTMLBuilder",
    allow_abbrev=False,
)

parser.add_argument(
    "html_file",
    help="the HTML file to fix",
)

args = parser.parse_args()

with open(file=args.html_file, mode="r", encoding="utf-8") as html_file:
    html = html_file.read()

url = search(r"<link rel=\"canonical\" href=\"(?P<url>.+)\" \/>", html).group("url")

if url.endswith("/index.html"):
    newurl = url[:-10]
else:
    newurl = url[:-5] + "/"

html = html.replace(url, newurl)

with open(file=args.html_file, mode="w", encoding="utf-8") as html_file:
    html_file.write(html)

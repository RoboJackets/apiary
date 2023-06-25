#/bin/python3

# workaround for a sphinx issue
# https://github.com/sphinx-doc/sphinx/issues/9730

from re import sub
from sys import stdin

print(sub(r"<link rel=\"canonical\" href=\"(?P<url>.+)\.html\" \/>", "<link rel=\"canonical\" href=\"\\g<url>/\" \\/>", stdin.read().strip()))

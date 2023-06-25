#/bin/python3

from re import sub
from sys import stdin

print(sub(r"<link rel=\"canonical\" href=\"(?P<url>.+)\.html\" \/>", "<link rel=\"canonical\" href=\"\\g<url>/\" \\/>", stdin.read().strip()))

# File: i (Python 2.7)

import json
import sys
print 'Content-Type: text/txt\n'
q = sys.argv[-1]

def error(m):
    print m
    sys.exit()

if len(q) == 0:
    error('Input missing')

try:
    (h, p) = q.split(':')
except ValueError:
    error('Password missing')


try:
    i = json.load(open('../keys.json'))[h]
except:
    error('Invalid input')

if p.isdigit() and len(p) < 15 and pow(i[0], int(p), i[1]) == i[2]:
    pass
print 'Wrong!\n' + '\n'.join(map(str, i))

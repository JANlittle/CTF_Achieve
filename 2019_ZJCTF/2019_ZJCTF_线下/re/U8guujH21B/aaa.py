from random import randint
from math import floor, sqrt
v1 = ''
v2 = 'zjctf{ThisRandomIsNotSafe}'
v4 = [ ord(i) for i in v2 ]
v5 = randint(65, max(v4)) * 255

while str(int(floor(float(v5 + v4[0]) / 2 + sqrt(v5 * v4[0])) % 255)) != '57':
    v5 = randint(65, max(v4)) * 255

for i in range(len(v2)):
    v1 += str(int(floor(float(v5 + v4[i]) / 2 + sqrt(v5 * v4[i])) % 255)) + ' '
print(v1)
# encrypted flag:
# 57, 183, 124, 9, 149, 65, 245, 166, 175, 1, 226, 106, 216, 132, 224, 208, 139, 1, 188, 224, 9, 235, 106, 149, 141, 80
'''
from random import randint
from math import floor, sqrt
_ = ''
__ = '_'
____ = [ ord(___) for ___ in __ ]
_____ = randint(65, max(____)) * 255
for ___ in range(len(__)):
    _ += str(int(floor(float(_____ + ____[___]) / 2 + sqrt(_____ * ____[___])) % 255)) + ' '

print _
'''
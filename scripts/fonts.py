#!/usr/local/munkireport/munkireport-python3

"""
Fonts information generator for munkireport.
"""

import subprocess
import os
import plistlib
import sys

from SystemConfiguration import SCDynamicStoreCopyConsoleUser

from ctypes import (CDLL,
                    Structure,
                    POINTER,
                    c_int64,
                    c_int32,
                    c_int16,
                    c_char,
                    c_uint32)
from ctypes.util import find_library

class timeval(Structure):
    _fields_ = [
                ("tv_sec",  c_int64),
                ("tv_usec", c_int32),
               ]

class utmpx(Structure):
    _fields_ = [
                ("ut_user", c_char*256),
                ("ut_id",   c_char*4),
                ("ut_line", c_char*32),
                ("ut_pid",  c_int32),
                ("ut_type", c_int16),
                ("ut_tv",   timeval),
                ("ut_host", c_char*256),
                ("ut_pad",  c_uint32*16),
               ]

def get_fonts():
    '''Uses system profiler to get fonts for this machine.'''

    username=current_user()

    cmd = ['/bin/launchctl', 'asuser', get_uid(username), '/usr/bin/sudo', '-u', username, '/usr/sbin/system_profiler', 'SPFontsDataType', '-xml']
    proc = subprocess.Popen(cmd, shell=False, bufsize=-1,
                                stdin=subprocess.PIPE,
                                stdout=subprocess.PIPE, stderr=subprocess.PIPE)
    (output, unused_error) = proc.communicate()

    try:
        try:
            plist = plistlib.readPlistFromString(output)
        except AttributeError as e:
            plist = plistlib.loads(output)
        # system_profiler xml is an array
        sp_dict = plist[0]
        items = sp_dict['_items']
        return items
    except Exception:
        return {}

def flatten_get_fonts(array):
    '''Un-nest fonts, return array with objects with relevant keys'''
    out = []
    for obj in array:
        device = {'name': '', 'enabled': 0, 'type_enabled': 0, 'copy_protected': 0, 'duplicate': 0, 'embeddable': 0, 'outline': 0, 'valid': 0}

        # Only process fonts in /Library/Fonts
        if 'path' in obj and "/System/Library/" in obj['path']:
            continue

        for item in obj:
            if item == '_items':
                out = out + flatten_get_fonts(obj['_items'])
            elif item == '_name':
                device['name'] = obj[item]
            elif item == 'path':
                device['path'] = obj[item]
            elif item == 'type':
                device['type'] = obj[item]
            elif item == 'enabled' and obj[item] == 'Yes':
                device['enabled'] = 1

            # Process each typeface within font
            elif item == 'typefaces':
                for font in obj['typefaces']:
                    for fontitem in font:
                        if fontitem == '_name':
                            device['type_name'] = font[fontitem]
                        elif fontitem == 'family':
                            device['family'] = font[fontitem]
                        elif fontitem == 'fullname':
                            device['fullname'] = font[fontitem]
                        elif fontitem == 'style':
                            device['style'] = font[fontitem]
                        elif fontitem == 'unique':
                            device['unique_id'] = font[fontitem]
                        elif fontitem == 'version':
                            device['version'] = font[fontitem]
                        elif fontitem == 'vendor':
                            device['vendor'] = font[fontitem]
                        elif fontitem == 'trademark':
                            device['trademark'] = font[fontitem]
                        elif fontitem == 'copyright':
                            device['copyright'] = font[fontitem]
                        elif fontitem == 'description':
                            device['description'] = font[fontitem]
                        elif fontitem == 'designer':
                            device['designer'] = font[fontitem]
                        elif fontitem == 'copy_protected' and font[fontitem] == 'yes':
                            device['copy_protected'] = 1
                        elif fontitem == 'duplicate' and font[fontitem] == 'yes':
                            device['duplicate'] = 1
                        elif fontitem == 'embeddable' and font[fontitem] == 'yes':
                            device['embeddable'] = 1
                        elif fontitem == 'enabled' and font[fontitem] == 'yes':
                            device['type_enabled'] = 1
                        elif fontitem == 'outline' and font[fontitem] == 'yes':
                            device['outline'] = 1
                        elif fontitem == 'valid' and font[fontitem] == 'yes':
                            device['valid'] = 1

        out.append(device)
    return out

def current_user():

    # local constants
    c = CDLL(find_library("System"))
    username = (SCDynamicStoreCopyConsoleUser(None, None, None) or [None])[0]
    username = [username,""][username in ["loginwindow", None, ""]]

    # If we can't get the current user, get last console login
    if username == "":
        setutxent_wtmp = c.setutxent_wtmp
        setutxent_wtmp.restype = None
        getutxent_wtmp = c.getutxent_wtmp
        getutxent_wtmp.restype = POINTER(utmpx)
        endutxent_wtmp = c.setutxent_wtmp
        endutxent_wtmp.restype = None
        # initialize
        setutxent_wtmp(0)
        entry = getutxent_wtmp()
        while entry:
            e = entry.contents
            entry = getutxent_wtmp()
            if (e.ut_type == 7 and e.ut_line == b"console" and e.ut_user != "root" and e.ut_user != "" and e.ut_user != b"root" and e.ut_user != b""):
                endutxent_wtmp()
                return e.ut_user
    else:
        return username

def get_uid(username):

    # Decode if username is bytes
    if isinstance(username, bytes):
        username = username.decode("utf-8", errors="ignore")

    cmd = ['/usr/bin/id', '-u', username]
    proc = subprocess.Popen(cmd, shell=False, bufsize=-1,
                            stdin=subprocess.PIPE,
                            stdout=subprocess.PIPE, stderr=subprocess.PIPE)
    (output, unused_error) = proc.communicate()
    return output.decode("utf-8", errors="ignore").strip()

def main():
    """Main"""

    # Get results
    result = dict()
    result = flatten_get_fonts(get_fonts())

    # Write font results to cache
    cachedir = '%s/cache' % os.path.dirname(os.path.realpath(__file__))
    output_plist = os.path.join(cachedir, 'fonts.plist')
    try:
        plistlib.writePlist(result, output_plist)
    except:
        with open(output_plist, 'wb') as fp:
            plistlib.dump(result, fp, fmt=plistlib.FMT_XML)

if __name__ == "__main__":
    main()

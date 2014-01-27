#!/usr/bin/env python
# encoding: utf-8

import polib
import sys
import optparse
import os
try:
    import json
except:
    import simplejson as json
import urllib
import requests
import re
import HTMLParser
import logging

LOGLEVEL = logging.INFO

logging.basicConfig(
	filename='translation.log', 
	filemode='w',
	format='%(levelname) - %(message)',
	level=LOGLEVEL
)

reload(sys)
sys.setdefaultencoding('utf-8')

h = HTMLParser.HTMLParser()

nefarious_regex = re.compile('shinken|icinga|op5|opsview|zabbix', re.IGNORECASE) 

def translate(text, tl, sl='en', key=None):
    
    TRANSLATE_API_ADDRESS = 'https://www.googleapis.com/language/translate/v2'
    get_params = { 'q' : text, 'source': sl, 'target': tl, 'key': key }
    
    raw_json = requests.get(TRANSLATE_API_ADDRESS, params=get_params)
    pro_json = json.loads(raw_json.text)
    
    if 'error' in pro_json:
        print pro_json
        print 'Error encountered: terminate? [y/n]'
        inp = raw_input()
        if inp == 'n':
            return None
        else:
            sys.exit(1)
    else:
        return pro_json['data']['translations'][0]['translatedText']

def clean_msgstr(msgstr):
    entities = "&.+?;"
    html_entities = re.findall(entities, msgstr)
    for entity in html_entities:
        tmp = h.unescape(entity)
        msgstr = msgstr.replace(entity, tmp)
    return msgstr

def parse_args():
    
    parser = optparse.OptionParser()
    
    parser.add_option('-p', '--po-file', help='The PO file to translate.')
    parser.add_option('-k', '--google-key', help='Google API key.')
    parser.add_option('-s', '--save-as-mo', help='Compile them down to MO files.', action='store_true')
    
    options, args = parser.parse_args()
    
    if not options.po_file:
        parser.error('Must give the pot file.')
    if not options.google_key:
        options.google_key = "AIzaSyADtIZXY9Ycb0E67jLXH-8dQ1-C-8AyAY0"
    
    return options

def guess_language(po_filename):
    
    try:
        _, filename = po_filename.rsplit('/', 1)
    except ValueError:
        filename = po_filename
    country_code, _       = filename.rsplit('.', 1)
        
    prefix, suffix = country_code.split('_')
    prefix = prefix.lower()
    suffix = suffix.lower()
    if prefix == suffix or prefix == 'en':
        return prefix.lower()
    if prefix == 'ko':
        return prefix
    else:
        return country_code

def main():
    
    options = parse_args()
    logging.debug('Opening %s' % options.po_file)
    po_file = polib.pofile(options.po_file)
    
    language = guess_language(options.po_file)
        
    total = len(po_file.untranslated_entries())
    count = 1
    #~ Translate with Google Translate
    print '--- Working on %s : %d entries to translate.' % (language.upper(), total)
        
    for entry in po_file.untranslated_entries():
        entry.msgstr = translate(entry.msgid.lower(), language, key=options.google_key)
        entry.flags.append(u'fuzzy')
        print '%s [%d/%d] ...%s... --> ...%s...' % (language, count, total, entry.msgid[:10], entry.msgstr[:10])
        count += 1
    #~ Clean up msgstr entries
    total = len(po_file)
    count = 1
    for entry in po_file:
        tmp_msgstr = entry.msgstr
        entry.msgstr = clean_msgstr(entry.msgstr)
        if tmp_msgstr != entry.msgstr:
            logging.info('Cleaned: %s --> %s' % (tmp_msgstr, entry.msgstr))
        if nefarious_regex.search(entry.msgstr):
            match = nefarious_regex.match(entry.msgstr).group(1)
            logging.warning('Warning: Found improper match %s in %s' % (match, entry.msgstr))
        
    po_file.save()
        
    if options.save_as_mo:
        filename, suffix = options.po_file.rsplit('.', 1)
        mo_file = filename + '.mo'
        try:
            os.remove(mo_file)
        except OSError:
            logging.warning("%s didn't exist." % mo_file)
        logging.info('Saving mo as %s' % mo_file)
        os.system('msgfmt -o %s --use-fuzzy %s' % (mo_file, filename))
    
if __name__ == '__main__':
    main()

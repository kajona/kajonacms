#!/bin/bash
#
# sidler, 2006
#
# Kopiert alle Dateien in ein lokales Zip-archiv	
 

tar -czf ../backup_$(date +%Y%m%d_%H%M).tar.gz ../_module
echo "created file backup_$(date +%Y%m%d_%H%M).tar.gz"

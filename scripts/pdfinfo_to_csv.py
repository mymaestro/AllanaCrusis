# pdfinfo yourfile.pdf | python3 pdfinfo_to_csv.py yourfile.pdf > output.csv
"""
first=1
for p in *.pdf; do
  if [ $first -eq 1 ]; then
    pdfinfo "$p" | python3 pdfinfo_to_csv.py "$p" > partsinfo.csv
    first=0
  else
    pdfinfo "$p" | python3 pdfinfo_to_csv.py "$p" | tail -n 1 >> partsinfo.csv
  fi
done

# Move to /var/tmp for easier grepping
mv partsinfo.csv /var/tmp/partsinfo.csv

# Find files with empty titles
grep ',,' /var/tmp/partsinfo.csv | cut -d, -f1

# Count how many files are missing author information  
awk -F, '$6=="" {print $1}' /var/tmp/partsinfo.csv

# Find files with unusually large or small page counts
awk -F, '$17!="" {print $1","$17}' /var/tmp/partsinfo.csv | sort -t, -k2 -n
"""
import sys
import csv
import os

# Define consistent column order for all PDF files
STANDARD_COLUMNS = [
    'Filename', 'Title', 'Subject', 'Keywords', 'Author', 'Creator', 'Producer', 
    'CreationDate', 'ModDate', 'Custom Metadata', 'Metadata Stream', 'Tagged', 
    'UserProperties', 'Suspects', 'Form', 'JavaScript', 'Pages', 'Encrypted', 
    'Page size', 'Page rot', 'File size', 'Optimized', 'PDF version'
]

def parse_pdfinfo(lines):
    data = {}
    for line in lines:
        if ':' in line:
            key, value = line.split(':', 1)
            data[key.strip()] = value.strip()
    return data

if __name__ == "__main__":
    # Get filename from command line argument
    filename = sys.argv[1] if len(sys.argv) > 1 else "unknown"
    
    # Read from stdin
    lines = sys.stdin.read().splitlines()
    info = parse_pdfinfo(lines)
    
    # Create ordered data with filename first, then standard columns
    ordered_data = {}
    ordered_data['Filename'] = os.path.basename(filename)
    
    # Add other columns in standard order, using empty string if not present
    for col in STANDARD_COLUMNS[1:]:  # Skip 'Filename' since we already added it
        ordered_data[col] = info.get(col, '')
    
    writer = csv.writer(sys.stdout)
    writer.writerow(ordered_data.keys())
    writer.writerow(ordered_data.values())

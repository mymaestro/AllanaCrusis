# pdfinfo yourfile.pdf | python3 pdfinfo_to_csv.py > output.csv
"""
first=1
for p in f*pdf; do
  if [ $first -eq 1 ]; then
    pdfinfo "$p" | python3 pdfinfo_to_csv.py > partsinfo.csv
    first=0
  else
    pdfinfo "$p" | python3 pdfinfo_to_csv.py | tail -n 1 >> partsinfo.csv
  fi
done
"""
import sys
import csv

def parse_pdfinfo(lines):
    data = {}
    for line in lines:
        if ':' in line:
            key, value = line.split(':', 1)
            data[key.strip()] = value.strip()
    return data

if __name__ == "__main__":
    # Read from stdin or a file
    lines = sys.stdin.read().splitlines()
    info = parse_pdfinfo(lines)
    writer = csv.writer(sys.stdout)
    writer.writerow(info.keys())
    writer.writerow(info.values())

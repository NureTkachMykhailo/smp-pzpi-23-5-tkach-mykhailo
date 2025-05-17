#!/bin/bash

# Set UTF-8 locale for correct handling of Cyrillic characters
export LC_ALL=en_US.UTF-8
export LANG=en_US.UTF-8

function show_help() {
    echo "Parse TimeTable CSV file and convert it to Google Calendar format"
    echo ""
    echo "Usage: ./script.sh [--help | --version] | [[-q|--quiet] [academic_group] file_from_cist.csv]"
    echo ""
    echo "Arguments:"
    echo "  academic_group    Academic group code (e.g. ПЗПІ-23-1)"
    echo "  file_from_cist.csv  CSV file with schedule exported from CIST"
    echo ""
    echo "Options:"
    echo "  --help            Show this help message"
    echo "  --version         Show version information"
    echo "  -q, --quiet       Hide processing output"
    echo ""
    echo "Examples:"
    echo "  ./script.sh --help                          # Show help"
    echo "  ./script.sh ПЗПІ-23-1 TimeTable_10_05_2025.csv  # Process schedule for group ПЗПІ-23-1"
    echo "  ./script.sh -q ПЗПІ-23-1 TimeTable_10_05_2025.csv  # Process without showing details"
    echo "  ./script.sh                                # Interactive file and group selection"
}

# Function to show version information
function show_version() {
    echo "Script version 1.0.0"
}

# Initialize parameters
quiet_mode=false
csv_file=""
academic_group=""

if [[ $# -gt 3 ]]
then
    echo "Error: Too many arguments."
    exit 1
fi

# Process command line arguments
case "$1" in
    --help)
        if [[ "$#" -gt 1 ]]
        then
            echo "Error: You can't pass other arguments with --help flag."
            exit 1
        fi

        show_help
        exit 0
        ;;
    --version)
        if [[ "$#" -gt 1 ]]
        then
            echo "Error: You can't pass other arguments with --version flag."
            exit 1
        fi

        show_version
        exit 0
        ;;
    -q|--quiet)
        quiet_mode=true

        if [[ "$2" =~ ^ПЗПІ-[0-9]+-[0-9]+$ ]]
        then
            academic_group="$2"

            if [[ "$3" = *.csv ]]
            then
                csv_file="$3"
            elif [[ -n "$3" ]]
            then
                echo "Error: Third argument must be a CSV file."
                exit 1
            fi

        elif [[ "$2" = *.csv ]]
        then
            csv_file="$2"
        elif [[ -n "$2" ]]
        then
            echo "Error: Unknown option or invalid group format."
            exit 1
        fi

        ;;
    *)
        if [[ "$1" =~ ^ПЗПІ-[0-9]+-[0-9]+$ ]]
        then
            academic_group="$1"

            if [[ "$2" = *.csv ]]
            then
                csv_file="$2"
            elif [[ -n "$2" ]]
            then
                echo "Error: Second argument must be a CSV file."
                exit 1
            fi

        elif [[ "$1" = *.csv ]]
        then
            csv_file="$1"
        elif [[ -n "$1" ]]
        then
            echo "Error: Unknown option or invalid group format."
            exit 1
        fi
        ;;
esac

# If no file specified, offer to choose one
if [ -z "$csv_file" ]
then
    echo "Available CSV files:"
    select csv_file in TimeTable_*.csv
    do
        [ -n "$csv_file" ] && break
        echo "⚠️ Invalid choice. Try again."
    done
fi

# Check if the file exists
if [ ! -f "$csv_file" ]
then
    echo "❌ File '$csv_file' not found."
    exit 1
fi

# Extract groups from the selected file
if ! $quiet_mode
then
    echo "Reading file '$csv_file'..."
fi

# Convert file from Windows-1251 to UTF-8
utf8_content=$(iconv -f windows-1251 -t utf-8 "$csv_file")

# Normalize line endings to Unix format (\n)
utf8_content=$(echo "$utf8_content" | sed -e 's/\r\n/\n/g' -e 's/\r/\n/g')

# Remove BOM (Byte Order Mark) if present
utf8_content=$(echo "$utf8_content" | sed 's/^\xEF\xBB\xBF//')

# Remove all quotes from the text
utf8_content=$(echo "$utf8_content" | sed 's/"//g')

# Extract group names (left part of the first column before " - "), skipping the header
group_list=$(echo "$utf8_content" | awk -F',' 'NR > 1 { split($1, parts, " - "); print parts[1] }')

# Sort and remove duplicates
unique_groups=$(echo "$group_list" | sort -V | uniq)

# If no groups found, show error message
if [ -z "$unique_groups" ]
then
    echo "❌ No groups found in the file."
    exit 1
fi

# If no group was specified, offer to choose one
if [ -z "$academic_group" ]
then
    echo "👥 Select a group:"
    select selected_group in $unique_groups
    do
        [ -n "$selected_group" ] && break
        echo "⚠️ Invalid choice. Try again."
    done
else
    # Check if the specified group exists in the file
    if ! echo "$unique_groups" | grep -q "^$academic_group$"
    then
        echo "❌ Group '$academic_group' not found in the file."
        echo "Available groups:"
        echo "$unique_groups"
        exit 1
    fi
    selected_group="$academic_group"
fi

output_file="Google_${csv_file}"

# Create the header for the output file
echo "\"Subject\",\"Start Date\",\"Start Time\",\"End Date\",\"End Time\",\"Description\"" > "$output_file"

if ! $quiet_mode
then
    echo "Processing schedule for group '$selected_group'..."
fi

# Function to process the CSV file
function process_csv() {
    iconv -f windows-1251 -t utf-8 "$csv_file" |
    sed -e 's/\r\n/\n/g' -e 's/\r/\n/g' -e 's/^\xEF\xBB\xBF//' |
    awk -v group="$selected_group" -v quiet="$quiet_mode" '
    BEGIN {
        FS="\",\"";
        OFS="|";
        if (quiet == "false") print "Analyzing CSV file..."
    }

    # Create a sort key: YYYYMMDD + time
    function sortkey(date, time) {
        split(date, date_parts, ".")
        split(time, time_parts, ":")
        return sprintf("%04d%02d%02d%02d%02d", date_parts[3], date_parts[2], date_parts[1], time_parts[1], time_parts[2])
    }

    NR > 1 {
        gsub(/^"/, "", $1)
        gsub(/"$/, "", $(NF))

        split($1, parts, " - ")
        group_name = parts[1]
        lesson_type = parts[2]

        if (group_name == group) {
            key = sortkey($2, $3)
            # Show details if not in quiet mode
            if (quiet == "false") {
                print "Found: " lesson_type " on " $2 " " $3
                print key "|" lesson_type "|" $2 "|" $3 "|" $4 "|" $5 "|" $12
            }
            # Using pipe separator for easier processing
            # key|lesson_type|start_date|start_time|end_date|end_time|description
            print key, lesson_type, $2, $3, $4, $5, $12
        }
    }
    '
}

# Create a temporary file for the processed data
temp_file=$(mktemp)

# Process the CSV and display the output if not in quiet mode
if ! $quiet_mode
then
    # In non-quiet mode, show the output and also save to temp file
    process_csv | tee "$temp_file"
else
    # In quiet mode, just save to temp file without showing
    process_csv > "$temp_file"
fi

# Use the temp file for further processing
cat "$temp_file" | sort -t'|' -k1,1 |
awk -F'|' '
BEGIN {
    OFS=",";
}

function ampm(time_str,  hr, min, ampm_val) {
    split(time_str, t, ":")
    hr = t[1]+0
    min = t[2]+0
    ampm_val = (hr >= 12) ? "PM" : "AM"
    if (hr == 0) hr = 12
    else if (hr > 12) hr -= 12
    return sprintf("%02d:%02d %s", hr, min, ampm_val)
}

function format_date(date_str,   parts) {
    split(date_str, parts, ".")
    return sprintf("%02d/%02d/%04d", parts[2], parts[1], parts[3])
}

{
    # $1 = sortkey, $2 = lesson_type, $3 = start_date, $4 = start_time,
    # $5 = end_date, $6 = end_time, $7 = description

    type = $2
    raw_date = $3

    # Create a date_key for grouping lessons on the same day
    split(raw_date, date_parts, ".")
    date_key = sprintf("%04d%02d%02d", date_parts[3], date_parts[2], date_parts[1])
    combo_key = type "-" date_key

    if (!(combo_key in combo_seen)) {
        lesson_count[type]++
        combo_seen[combo_key] = lesson_count[type]
    }

    lesson_number = combo_seen[combo_key]

    # Output in Google Calendar CSV format with proper quoting
    formatted_output = "\"" type "; #" lesson_number "\"," \
          "\"" format_date($3) "\"," \
          "\"" ampm($4) "\"," \
          "\"" format_date($5) "\"," \
          "\"" ampm($6) "\"," \
          "\"" $7 "\""

    print formatted_output
}
' >> "$output_file"

# Remove the temporary file
rm -f "$temp_file"

if ! $quiet_mode
then
    echo "CSV saved as: $output_file"
    echo "File is ready for import into Google Calendar."
fi
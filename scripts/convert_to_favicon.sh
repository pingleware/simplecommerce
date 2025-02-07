#!/bin/bash

# Check if ImageMagick is installed
if ! command -v convert &> /dev/null; then
    echo "ImageMagick is not installed. Please install it first."
    exit 1
fi

# Check if input file is provided
if [ $# -lt 1 ]; then
    echo "Usage: $0 <input.jpg> [output.ico]"
    exit 1
fi

# Input and output file names
INPUT_FILE=$1
OUTPUT_FILE=${2:-favicon.ico}

# Convert JPG to favicon.ico
convert "$INPUT_FILE" -define icon:auto-resize=64,48,32,16 "$OUTPUT_FILE"

if [ $? -eq 0 ]; then
    echo "Favicon successfully created: $OUTPUT_FILE"
else
    echo "Failed to create favicon."
fi

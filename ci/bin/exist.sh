set -e -x -o pipefail

if [[ ! -f readme.txt ]]; then
    echo "Readme not found" && exit 1
fi


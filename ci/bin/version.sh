set -e -x -o pipefail

export APP_VERSION=$(cat VERSION)

if [[ ! -v APP_VERSION ]]; then
    echo "APP_VERSION is not set" && exit 1
fi


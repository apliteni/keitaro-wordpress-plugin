set -e -x -o pipefail


if [[ ! -v APP_VERSION ]]; then
    echo "APP_VERSION is not set" && exit 1
fi

commit_msg="Release ${APP_VERSION}"


set -e -x -o pipefail


if [[ ! -v APP_VERSION ]]; then
    echo "APP_VERSION is not set" && exit 1
fi

commit_msg="Release ${APP_VERSION}"
password=$(echo "'${WP_PASSWORD}'")

svn co --username=${WP_USERNAME} "${WP_REPO}"
mv keitaro-tracker-integration/.svn ./svn/.svn
cd ./svn/
svn add ./assets/* ./trunc/* 2>/dev/null || true
svn ci -m ${commit_msg} --username "${WP_USERNAME}" --password ${password} --no-auth-cache
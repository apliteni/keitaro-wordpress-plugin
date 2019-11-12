set -e -x -o pipefail


if [[ ! -v APP_VERSION ]]; then
    echo "APP_VERSION is not set" && exit 1
fi


svn co --username=${WP_USERNAME} "${WP_REPO}"
mv keitaro-tracker-integration/.svn ./svn/.svn
cd ./svn/
svn add ./assets/* ./trunc/* 2>/dev/null || true
svn ci -m "Release ${APP_VERSION}" --username "${WP_USERNAME}" --password ${WP_PASSWORD} #--no-auth-cache
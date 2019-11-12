set -e -x -o pipefail

svn co --username=${WP_USERNAME} "${WP_REPO}"
mvkeitaro-tracker-integration/.svn ./svn/
cd ./svn/
svn add ./assets/* ./trunc/* 2>/dev/null
svn ci -m "Release ${APP_VERSION}" --username ${WP_USERNAME} --password "${WP_PASSWORD}" --no-auth-cache
set -e -x -o pipefail

cd ./svn/
svn co --username=${WP_USERNAME} "${WP_REPO}"
svn add ./assets/* ./trunc/* 2>/dev/null
svn ci -m "Release ${APP_VERSION}" --username ${WP_USERNAME} --password "${WP_PASSWORD}" --no-auth-cache
set -e -x -o pipefail

svn co --username=${WP_USERNAME} "${WP_REPO}"
mv keitaro-tracker-integration/.svn ./svn/.svn
cd ./svn/
ls ./
svn ci -m "Release ${APP_VERSION}" --username ${WP_USERNAME} --password "${WP_PASSWORD}" --no-auth-cache
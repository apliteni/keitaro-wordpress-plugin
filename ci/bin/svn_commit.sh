set -e -x -o pipefail


if [[ ! -v APP_VERSION ]]; then
    echo "APP_VERSION is not set" && exit 1
fi

commit_msg="Release ${APP_VERSION}"

svn co --username=${WP_USERNAME} "${WP_REPO}"
mv keitaro-tracker-integration/.svn ./svn/.svn
rm -rf ./assets ./trunc
cp -r svn/assets keitaro-tracker-integration/
cp -r svn/trunk keitaro-tracker-integration/
cd keitaro-tracker-integration
svn add ./assets/* ./trunk/*
svn ci -m ${commit_msg} --username "${WP_USERNAME}" --password "${WP_PASSWORD}" --no-auth-cache
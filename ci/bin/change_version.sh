set -e -x -o pipefail

export APP_VERSION=$(cat VERSION)

sed -i -e "s/%VERSION%/${APP_VERSION}/g" svn/trunk/keitaro.php

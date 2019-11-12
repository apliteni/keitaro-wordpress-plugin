set -e -x -o pipefail

sed -i -e "s/%VERSION%/${APP_VERSION}/g" svn/trunk/keitaro.php

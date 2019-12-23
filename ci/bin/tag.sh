#!/usr/bin/env bash
set -e -x -o pipefail

export APP_VERSION=$(cat VERSION)

if [[ ! -v APP_VERSION ]]; then
    echo "APP_VERSION is not set" && exit 1
fi

curl -X POST --show-error --fail "https://${CI_SERVER_HOST}/api/v4/projects/${CI_PROJECT_ID}/repository/tags?ref=${CI_COMMIT_SHA}&tag_name=${APP_VERSION}&private_token=${GITLAB_TOKEN}"

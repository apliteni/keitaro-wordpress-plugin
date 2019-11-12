#!/usr/bin/env bash
set -e -x -o pipefail

curl -X POST --show-error --fail "https://{GITLAB_URL}/api/v4/projects/${CI_PROJECT_ID}/repository/tags?ref=${CI_COMMIT_SHA}&tag_name=${APP_VERSION}&private_token=${GITLAB_TOKEN}"

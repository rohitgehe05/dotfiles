#!/usr/bin/env bash

curl -X DELETE -H "Content-Type: application/json" -H "Authorization: Bearer 2b09bd35c4f61ad02974d6d4c926036ec403149c8a122e1905922eb6cb0bd633" "https://api.digitalocean.com/v2/droplets?tag_name=test"
rm build-151* setup-151*
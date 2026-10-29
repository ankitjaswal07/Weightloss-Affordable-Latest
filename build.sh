#!/usr/bin/env bash
# Rebuild the two installable packages from src/.
set -euo pipefail
cd "$(dirname "$0")"

rm -f weightloss-affordable.zip weightloss-affordable-core.zip

( cd src && zip -rqX ../weightloss-affordable.zip weightloss-affordable -x '*.DS_Store' '*__MACOSX*' )
( cd src && zip -rqX ../weightloss-affordable-core.zip weightloss-affordable-core -x '*.DS_Store' '*__MACOSX*' )

echo "Built:"
ls -lh weightloss-affordable*.zip | awk '{print "  " $9 "  " $5}'
unzip -l weightloss-affordable.zip | tail -1
unzip -l weightloss-affordable-core.zip | tail -1

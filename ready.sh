#!/bin/bash
# Prepares plugin for sale by compressing into a .zip file (sans .sh,.git, tests/),
# moving it out to another folder on your local system,
# and pushing to GitHub.
# Also makes a seperate backup of the whole thing.
# Prerequisite: Main plugin file slug must be the same as the plugin folder name.
# Prerequisite: Existing git repo with its remote origin set up on GitHub. Both repo names must match the plugin slug, exactly.
# Configure the first 2 variables.

set -e

#config
READYFORSALE="${HOME}/readyforsale/" # destination folder for sellable .zip file 
BACKUPDIR="${HOME}/Documents/LOG/" # destination folder for the backup development .zip

timestamp=$(date +%Y%m%d_%H%M%S) # +%Y%m%d_%H%M%S
SLUG=${PWD##*/}

# Get version from main plugin file

CURRENTDIR=`pwd`
NEWVERSION=`grep "^Version" "$CURRENTDIR/${SLUG}.php" | awk -F' ' '{print $2}' | sed 's/[[:space:]]//g'`

if [[ -z "$NEWVERSION" ]]; then echo "ERROR: Cannot find version. Exiting early...."; exit 1; fi

#Tag and push to GitHub
echo "Tagging new version in git"
git tag -a "$NEWVERSION" -m "Tagging version $NEWVERSION"
echo "Pushing latest commit to GitHub, with tags"
git push origin master
git push origin master --tags

# zip the release plugin
echo "Zipping $SLUG version: $NEWVERSION"
cd ../
zip -r ${SLUG}.${NEWVERSION}.zip $SLUG -x '*tests*' '*.git*' '*README.md' '*.xml' '*.sh' '*~*'
echo "Moving the zipped plugin out to $READYFORSALE"
mv ${SLUG}.${NEWVERSION}.zip $READYFORSALE

# zip a backup of everything
zip -r ${SLUG}.${timestamp}.zip $SLUG
echo "Moving the backup out to $BACKUPDIR"
mv ${SLUG}.${timestamp}.zip $BACKUPDIR

echo "*** FIN ***"

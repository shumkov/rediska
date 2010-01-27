#!/bin/sh

git-for-each-ref refs/remotes/tags | cut -d / -f 4- |
while read ref
do
	git tag -a "$ref" -m"say farewell to SVN" "refs/remotes/tags/$ref"
	git push origin ":refs/heads/tags/$ref"
	git push origin tag "$ref"
done


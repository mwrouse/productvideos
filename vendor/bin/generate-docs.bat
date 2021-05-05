@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../dereuromark/media-embed/bin/generate-docs
sh "%BIN_TARGET%" %*

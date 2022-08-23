<?php

function aetherupload_display_link($savedPath)
{
    return \AetherUpload\Util::getDisplayLink($savedPath);
}

function aetherupload_download_link($savedPath, $newName)
{
    return \AetherUpload\Util::getDownloadLink($savedPath, $newName);
}



<?php

namespace App\Support;

class CmsUploadLimits
{
    public const CAMPUS_AVP_MAX_MB = 128;
    public const CAMPUS_AVP_MAX_KB = self::CAMPUS_AVP_MAX_MB * 1024;
    public const CAMPUS_AVP_MAX_BYTES = self::CAMPUS_AVP_MAX_KB * 1024;

    public const FILE_TOO_LARGE_MESSAGE = 'File too large!';
}

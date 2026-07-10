<?php

namespace App\Support;

class UploadLimits
{
    /** App cap for student submission uploads (KB). */
    public const SUBMISSION_MAX_KB = 20480;

    public const ASSIGNMENT_ATTACHMENT_MAX_COUNT = 3;

    public const ASSIGNMENT_ATTACHMENT_MAX_KB = 2048;

    public static function assignmentAttachmentMaxMegabytes(): int
    {
        return (int) floor(self::ASSIGNMENT_ATTACHMENT_MAX_KB / 1024);
    }

    public static function submissionMaxKilobytes(): int
    {
        return min(self::SUBMISSION_MAX_KB, self::phpUploadMaxKilobytes());
    }

    public static function submissionMaxMegabytes(): int
    {
        return (int) floor(self::submissionMaxKilobytes() / 1024);
    }

    public static function phpUploadMaxKilobytes(): int
    {
        return self::iniSizeToKilobytes(ini_get('upload_max_filesize'));
    }

    public static function phpPostMaxKilobytes(): int
    {
        return self::iniSizeToKilobytes(ini_get('post_max_size'));
    }

    public static function fileUploadErrorMessage(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE => 'This file exceeds the server upload limit ('.ini_get('upload_max_filesize').'). Stop the server and run: composer serve (uses php-local.ini). Do not use php artisan serve alone.',
            UPLOAD_ERR_FORM_SIZE => 'This file is too large for this form.',
            UPLOAD_ERR_PARTIAL => 'The upload was interrupted. Please try again.',
            UPLOAD_ERR_NO_FILE => 'Please choose a file to upload.',
            UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE, UPLOAD_ERR_EXTENSION => 'The server could not store the upload. Contact support.',
            default => 'The file failed to upload. Please try again.',
        };
    }

    private static function iniSizeToKilobytes(string|false $value): int
    {
        if ($value === false || $value === '') {
            return 0;
        }

        $value = trim($value);
        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        return (int) match ($unit) {
            'g' => $number * 1024 * 1024,
            'm' => $number * 1024,
            'k' => $number,
            default => $number / 1024,
        };
    }
}

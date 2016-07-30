<?php
namespace Kambo\Http\Message\Utils;

/**
 * Simple wrapper for methods connected with upload.
 *
 * @package Kambo\Http\Message\Utils
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 *
 * @codeCoverageIgnore
 */
class UploadFile
{
    /**
     * Wrapper for method is_uploaded_file - tells whether the file was uploaded via HTTP POST.
     *
     * @param string $filename 
     *
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function is($filename)
    {
        return is_uploaded_file($filename);
    }

    /**
     * Wrapper for method move_uploaded_file - moves an uploaded file to a new location.
     *
     * @param string $filename    The filename of the uploaded file.
     * @param string $destination The destination of the moved file.
     *
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function move($filename, $destination)
    {
        return move_uploaded_file($filename, $destination);
    }
}

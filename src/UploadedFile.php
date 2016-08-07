<?php

namespace Kambo\Http\Message;

// \Spl
use RuntimeException;
use InvalidArgumentException;

// \Psr
use Psr\Http\Message\UploadedFileInterface;

// \Http\Message
use Kambo\Http\Message\Stream;
use Kambo\Http\Message\Utils\UploadFile;

/**
 * Value object representing a file uploaded through an HTTP request.
 *
 * Instances of this interface are immutable; all methods that might change
 * state retain the internal state of the current instance and return
 * an instance that contains the changed state.
 *
 * @package Kambo\Http\Message
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class UploadedFile implements UploadedFileInterface
{
    /**
     * Flag signaling that the file has been moved
     *
     * @var boolean
     */
    private $moved = false;

    /**
     * Size of the file
     *
     * @var int|null
     */
    private $size;

    /**
     * File error code
     *
     * @var int
     */
    private $error;

    /**
     * The file media type provided by the client.
     *
     * @var string|null
     */
    private $clientMediaType;

    /**
     * The filename provided by the client.
     *
     * @var string|null
     */
    private $clientName;

    /**
     * The full path to the uploaded file provided by the client.
     *
     * @var string
     */
    private $file;

    /**
     * Stream representation of the uploaded file.
     *
     * @var \Psr\Http\Message\StreamInterface
     */
    private $stream;

    /**
     * Simple wrapper for methods connected with upload eg.: is_uploaded_file
     *
     * @var UploadFile
     */
    private $uploadFile;

    /**
     * Construct a new UploadedFile instance.
     *
     * @param string          $file            The full path to the uploaded file provided by the client.
     * @param string|null     $clientName      The filename provided by the client.
     * @param string|null     $clientMediaType The file media type provided by the client.
     * @param int|null        $size            The file size in bytes.
     * @param int             $error           The UPLOAD_ERR_XXX code representing the status of the upload.
     * @param UploadFile|null $uploadFile      Helper for manipulating with uploaded files.
     */
    public function __construct(
        $file,
        $clientName,
        $clientMediaType,
        $size,
        $error,
        $uploadFile = null
    ) {
        $this->file            = $file;
        $this->clientName      = $clientName;
        $this->clientMediaType = $clientMediaType;
        $this->size            = $size;
        $this->error           = $error;

        if (isset($uploadFile)) {
            $this->uploadFile = $uploadFile;
        } else {
            $this->uploadFile = new UploadFile();
        }
    }

    /**
     * Retrieve a stream representing the uploaded file.
     *
     * This method return a StreamInterface instance, representing the
     * uploaded file. The purpose of this method is to allow utilizing native PHP
     * stream functionality to manipulate the file upload, such as
     * stream_copy_to_stream() (though the result will need to be decorated in a
     * native PHP stream wrapper to work with such functions).
     *
     * If the moveTo() method has been called previously, this method will raise
     * an exception.
     *
     * @return StreamInterface Stream representation of the uploaded file.
     *
     * @throws \RuntimeException in cases when no stream is available or can be
     *                           created.
     */
    public function getStream()
    {
        if ($this->moved) {
            throw new \RuntimeException(sprintf('Uploaded file %1s has already been moved', $this->clientName));
        }

        if ($this->stream === null) {
            $this->stream = new Stream(fopen($this->file, 'r'));
        }

        return $this->stream;
    }

    /**
     * Move the uploaded file to a new location.
     *
     * Use this method as an alternative to move_uploaded_file(). This method is
     * guaranteed to work in both SAPI and non-SAPI environments.
     * Implementations must determine which environment they are in, and use the
     * appropriate method (move_uploaded_file(), rename(), or a stream
     * operation) to perform the operation.
     *
     * $targetPath may be an absolute path, or a relative path. If it is a
     * relative path, resolution should be the same as used by PHP's rename()
     * function.
     *
     * The original file or stream is removed on completion.
     *
     * If this method is called more than once, any subsequent calls MUST raise
     * an exception.
     *
     * When used in an SAPI environment where $_FILES is populated, when writing
     * files via moveTo(), is_uploaded_file() and move_uploaded_file() are
     * used to ensure permissions and upload status are verified correctly.
     *
     * If you wish to move to a stream, use getStream(), as SAPI operations
     * cannot guarantee writing to stream destinations.
     *
     * @see http://php.net/is_uploaded_file
     * @see http://php.net/move_uploaded_file
     *
     * @param string $targetPath Path to which to move the uploaded file.
     *
     * @throws \InvalidArgumentException if the $path specified is invalid.
     * @throws \RuntimeException         on any error during the move operation, or on
     *                                   the second or subsequent call to the method.
     */
    public function moveTo($targetPath)
    {
        if ($this->moved) {
            throw new RuntimeException('Uploaded file already moved');
        }

        if (!is_writable(dirname($targetPath))) {
            throw new InvalidArgumentException('Upload target path is not writable');
        }

        if (!$this->uploadFile->is($this->file)) {
            throw new RuntimeException(sprintf('%1s is not a valid uploaded file', $this->file));
        }

        if (!$this->uploadFile->move($this->file, $targetPath)) {
            throw new RuntimeException(
                sprintf('Error moving uploaded file %1s to %2s', $this->clientName, $targetPath)
            );
        }

        $this->moved = true;
    }

    /**
     * Retrieve the file size.
     *
     * Implementations returns the value stored in the "size" key of
     * the file in the $_FILES array if available, as PHP calculates this based
     * on the actual size transmitted.
     *
     * @return int|null The file size in bytes or null if unknown.
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Retrieve the error associated with the uploaded file.
     *
     * The return value is one of PHP's UPLOAD_ERR_XXX constants.
     *
     * If the file was uploaded successfully, this method return UPLOAD_ERR_OK.
     *
     * Implementation returns the value stored in the "error" key of
     * the file in the $_FILES array.
     *
     * @see http://php.net/manual/en/features.file-upload.errors.php
     *
     * @return int One of PHP's UPLOAD_ERR_XXX constants.
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Retrieve the filename sent by the client.
     *
     * Do not trust the value returned by this method. A client could send
     * a malicious filename with the intention to corrupt or hack your
     * application.
     *
     * Implementation returns the value stored in the "name" key of
     * the file in the $_FILES array.
     *
     * @return string|null The filename sent by the client or null if none
     *                     was provided.
     */
    public function getClientFilename()
    {
        return $this->clientName;
    }

    /**
     * Retrieve the media type sent by the client.
     *
     * Do not trust the value returned by this method. A client could send
     * a malicious media type with the intention to corrupt or hack your
     * application.
     *
     * Implementation returns the value stored in the "type" key of
     * the file in the $_FILES array.
     *
     * @return string|null The media type sent by the client or null if none
     *                     was provided.
     */
    public function getClientMediaType()
    {
        return $this->clientMediaType;
    }
}

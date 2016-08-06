<?php
namespace Kambo\Http\Message\Factories\Environment\Superglobal;

// \Http\Message
use Kambo\Http\Message\UploadedFile;
use Kambo\Http\Message\Environment\Environment;
use Kambo\Http\Message\Factories\Environment\Interfaces\FactoryInterface;

/**
 * Create instances of UploadedFile object from instance of Environment object
 *
 * @package Kambo\Http\Message\Factories\Environment\Superglobal
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class FilesFactory implements FactoryInterface
{
    /**
     * Create instances of UploadedFile object from instance of Environment object
     *
     * @param Environment $environment environment data
     *
     * @return array An associative array containing instances of UploadedFile, if there 
     *               are no uploads an empty array will be returned:
     *               [<field name> => <instance of UploadedFile>, ...]
     */
    public function create(Environment $environment)
    {
        $files  = $environment->getFiles();
        $parsed = $this->parseFiles($files);

        return $parsed;
    }

    /**
     * Parse files data into instances of UploadedFile
     *
     * @param array $files An associative array of items with same structure as $_FILES 
     *
     * @return array An associative array containing instances of UploadedFile, if there 
     *               are no uploads an empty array will be returned:
     *               [<field name> => <instance of UploadedFile>, ...]           
     */
    private function parseFiles($files)
    {
        $parsed = [];
        if (!empty($files)) {
            foreach ($files as $field => $file) {
                $parsed[$field] = $this->parseFile($file);
            }
        }

        return $parsed;
    }

    /**
     * Parse particular fields in files data into instances of UploadedFile
     *
     * @param array $uploadedFile An associative array of items from $_FILES from one single field
     *
     * @return array array with instances of UploadedFile
     */
    private function parseFile($uploadedFile)
    {
        $item = [];

        if (is_array($uploadedFile)) {
            if (!is_array($uploadedFile['error'])) {
                $item[] = new UploadedFile(
                    $uploadedFile['tmp_name'],
                    isset($uploadedFile['name']) ? $uploadedFile['name'] : null,
                    isset($uploadedFile['type']) ? $uploadedFile['type'] : null,
                    isset($uploadedFile['size']) ? $uploadedFile['size'] : null,
                    $uploadedFile['error']
                );
            } else {
                foreach ($uploadedFile['error'] as $fieldIndex => $void) {
                    $item[] = new UploadedFile(
                        $uploadedFile['tmp_name'][$fieldIndex],
                        isset($uploadedFile['name']) ? $uploadedFile['name'][$fieldIndex] : null,
                        isset($uploadedFile['type']) ? $uploadedFile['type'][$fieldIndex] : null,
                        isset($uploadedFile['size']) ? $uploadedFile['size'][$fieldIndex] : null,
                        $uploadedFile['error'][$fieldIndex]
                    );
                }
            }
        }

        return $item;
    }
}

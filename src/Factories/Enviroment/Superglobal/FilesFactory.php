<?php
namespace Kambo\HttpMessage\Factories\Enviroment\Superglobal;

// \HttpMessage
use Kambo\HttpMessage\UploadedFile;
use Kambo\HttpMessage\Enviroment\Enviroment;
use Kambo\HttpMessage\Factories\Enviroment\Interfaces\Factory;

/**
 * Create instances of UploadedFile object from instance of Enviroment object
 *
 * @package Kambo\HttpMessage\Factories\Enviroment\Superglobal
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class FilesFactory implements Factory
{
    /**
     * Create instances of UploadedFile object from instance of Enviroment object
     *
     * @param Enviroment $enviroment enviroment data
     *
     * @return array An associative array containing instances of UploadedFile, if there 
     *               are no uploads an empty array will be returned:
     *               [<field name> => <instance of UploadedFile>, ...]
     */
    public static function fromEnviroment(Enviroment $enviroment)
    {
        $files  = $enviroment->getFiles();
        $parsed = (new self())->parseFiles($files);

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
                $parsed[$field] = (new self())->parseFile($file);
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

        return $item;
    }
}

<?php

namespace PHPdfer;

use Exception;

class MetadataDirector
{
    private $tempFilePath;
    
    public function createMetadataFile(array $arMetadata)
    {
        $pdfMetaData = $this->getMetaData($arMetadata);
        
        // Get system temporary directory with fallback options
        $tempDir = $this->getTempDirectory();
        $tempFile = $tempDir . DIRECTORY_SEPARATOR . 'tmp_meta_data.pdf';
        
        $writeResult = file_put_contents($tempFile, $pdfMetaData);

        if (!$writeResult) {
            throw new Exception('Failed create temporary pdf file with new meta data at: ' . $tempFile);
        }
        
        // Store the temp file path for cleanup
        $this->tempFilePath = $tempFile;
    }
    
    /**
     * Get a writable temporary directory
     */
    private function getTempDirectory()
    {
        // Try system temp directory first
        $tempDir = sys_get_temp_dir();
        if (is_writable($tempDir)) {
            return $tempDir;
        }
        
        // Try current directory's temp subfolder
        $localTemp = __DIR__ . DIRECTORY_SEPARATOR . 'temp';
        if (!is_dir($localTemp)) {
            mkdir($localTemp, 0755, true);
        }
        if (is_writable($localTemp)) {
            return $localTemp;
        }
        
        // Fallback to current directory
        if (is_writable(__DIR__)) {
            return __DIR__;
        }
        
        throw new Exception('No writable temporary directory found');
    }
    
    /**
     * Get the temporary file path for external access
     */
    public function getTempFilePath()
    {
        return isset($this->tempFilePath) ? $this->tempFilePath : null;
    }
    
    /**
     * Clean up temporary file
     */
    public function cleanup()
    {
        if (isset($this->tempFilePath) && file_exists($this->tempFilePath)) {
            unlink($this->tempFilePath);
            unset($this->tempFilePath);
        }
    }

    private function getMetaData(array $arMetadata)
    {
        $metaDataBuilder = new MetadataBuilder();
        $metaData = $metaDataBuilder->setFirstCharacter();
        $now = date('Ymdhis');

        if ($arMetadata['TITLE']) {
            $metaData .= $metaDataBuilder->setTitle($arMetadata['TITLE']);
        }

        if ($arMetadata['AUTHOR']) {
            $metaData .= $metaDataBuilder->setAuthor($arMetadata['AUTHOR']);
        }

        if ($arMetadata['SUBJECT']) {
            $metaData .= $metaDataBuilder->setSubject($arMetadata['SUBJECT']);
        }

        if ($arMetadata['KEYWORDS']) {
            $metaData .= $metaDataBuilder->setKeywords($arMetadata['KEYWORDS']);
        }

        if ($arMetadata['MOD_DATE']) {
            $metaData .= $metaDataBuilder->setModDate($arMetadata['MOD_DATE']);
        } else {
            $metaData .= $metaDataBuilder->setModDate($now);
        }

        if ($arMetadata['CREATION_DATE']) {
            $metaData .= $metaDataBuilder->setCreationDate($arMetadata['CREATION_DATE']);
        } else {
            $metaData .= $metaDataBuilder->setCreationDate($now);
        }

        if ($arMetadata['CREATOR']) {
            $metaData .= $metaDataBuilder->setCreator($arMetadata['CREATOR']);
        } else {
            $metaData .= $metaDataBuilder->setCreator();
        }

        $metaData .= $metaDataBuilder->setLastCharacter();

        return $metaData;
    }
}

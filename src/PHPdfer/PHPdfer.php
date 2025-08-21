<?php

namespace PHPdfer;

use Exception;

class PHPdfer
{
    private $outputFilePath;
    
    /**
     * @param string $pdf - path to PDF file
     * @param array $arMetadata - array with metadata for PDF file
     * @param bool $logMode - enables the mode in which the output of the CLI command is saved to a log file
     *
     * @throws Exception - if it will not succeed change metadata in PDF file
     */
    public function changeMetadata(string $pdf, array $arMetadata, bool $logMode = false): void
    {
        $metaDataDirector = new MetadataDirector();

        try {
            $metaDataDirector->createMetadataFile($arMetadata);
        } catch (Exception $e) {
            throw new Exception("Failed to create metadata file: " . $e->getMessage());
        }

        // Get the temporary file path
        $tempMetaFile = $metaDataDirector->getTempFilePath();
        if (!$tempMetaFile) {
            throw new Exception("Failed to create temporary metadata file");
        }

        // Get writable output directory
        $outputDir = $this->getWritableDirectory();
        
        // Extract filename without path and extension, with better regex and sanitization
        $baseName = pathinfo($pdf, PATHINFO_FILENAME);
        if (empty($baseName)) {
            $baseName = 'output_' . uniqid();
        }
        
        // Sanitize filename for filesystem safety
        $baseName = $this->sanitizeFilename($baseName);
        
        $newFileName = $outputDir . DIRECTORY_SEPARATOR . "phpdfer_{$baseName}.pdf";

        // Create empty output file
        if (file_put_contents($newFileName, '') === false) {
            throw new Exception("Failed to create output file: $newFileName");
        }

        $commandResult = exec(
            "gs -dSAFER -dBATCH -dNOPAUSE -sDEVICE=pdfwrite -sOutputFile=\"$newFileName\" \"$pdf\" \"$tempMetaFile\"",
            $arCommandOutput,
            $commandStatus
        );
        
        // Clean up temporary file
        $metaDataDirector->cleanup();

        if ($logMode) {
            $logFile = $outputDir . DIRECTORY_SEPARATOR . 'command_output.log';
            file_put_contents($logFile, implode("\n", $arCommandOutput));
        }

        if ($commandStatus !== 0) {
            throw new Exception("Failed execute Ghost Script command, command status: $commandStatus. Output: " . implode("\n", $arCommandOutput));
        }
        
        // Store the output file path for retrieval
        $this->outputFilePath = $newFileName;
    }
    
    /**
     * Get a writable directory for output files
     */
    private function getWritableDirectory()
    {
        // Try current directory first
        if (is_writable(__DIR__)) {
            return __DIR__;
        }
        
        // Try current working directory
        if (is_writable(getcwd())) {
            return getcwd();
        }
        
        // Try current directory's output subfolder
        $localOutput = __DIR__ . DIRECTORY_SEPARATOR . 'output';
        if (!is_dir($localOutput)) {
            mkdir($localOutput, 0755, true);
        }
        if (is_writable($localOutput)) {
            return $localOutput;
        }
        
        // Try system temp directory as last resort
        $tempDir = sys_get_temp_dir();
        if (is_writable($tempDir)) {
            return $tempDir;
        }
        
        throw new Exception('No writable output directory found');
    }
    
    /**
     * Get the path of the last processed output file
     */
    public function getOutputFilePath()
    {
        return $this->outputFilePath;
    }
    
    /**
     * Sanitize filename for filesystem safety
     */
    private function sanitizeFilename($filename)
    {
        // Remove or replace problematic characters
        $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', $filename);
        // Remove multiple underscores
        $filename = preg_replace('/_+/', '_', $filename);
        // Remove leading/trailing underscores
        $filename = trim($filename, '_');
        // Ensure we have something
        if (empty($filename)) {
            $filename = 'output_' . uniqid();
        }
        return $filename;
    }
}

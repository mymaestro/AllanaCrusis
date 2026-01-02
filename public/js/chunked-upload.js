/**
 * ChunkedUploader - Handle large file uploads by splitting into chunks
 * 
 * Licensed Materials - Property of ACWE
 * (C) Copyright Austin Civic Wind Ensemble, 2025 All rights reserved.
 * 
 * @param {File} file - The file to upload
 * @param {Object} options - Configuration options
 * @param {number} options.chunkSize - Size of each chunk in bytes (default: 2MB)
 * @param {string} options.uploadUrl - URL to send chunks to
 * @param {Function} options.onProgress - Callback for progress updates (percent, message)
 * @param {Function} options.onComplete - Callback when upload completes (response)
 * @param {Function} options.onError - Callback on error (error message)
 */
class ChunkedUploader {
    constructor(file, options = {}) {
        this.file = file;
        this.chunkSize = options.chunkSize || 2 * 1024 * 1024; // 2MB default
        this.uploadUrl = options.uploadUrl || '/index.php?action=upload_chunk'; // Use absolute path
        this.onProgress = options.onProgress || (() => {});
        this.onComplete = options.onComplete || (() => {});
        this.onError = options.onError || (() => {});
        
        this.uploadId = this.generateUploadId();
        this.totalChunks = Math.ceil(file.size / this.chunkSize);
        this.currentChunk = 0;
        this.aborted = false;
    }
    
    /**
     * Generate a unique upload ID
     */
    generateUploadId() {
        return 'upload_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    /**
     * Start the chunked upload
     */
    async start() {
        this.aborted = false;
        this.currentChunk = 0;
        
        try {
            await this.uploadNextChunk();
        } catch (error) {
            this.onError(error.message || 'Upload failed');
        }
    }
    
    /**
     * Abort the current upload
     */
    abort() {
        this.aborted = true;
        if (this.currentRequest) {
            this.currentRequest.abort();
        }
    }
    
    /**
     * Upload the next chunk
     */
    async uploadNextChunk() {
        if (this.aborted) {
            throw new Error('Upload aborted');
        }
        
        if (this.currentChunk >= this.totalChunks) {
            return;
        }
        
        const start = this.currentChunk * this.chunkSize;
        const end = Math.min(start + this.chunkSize, this.file.size);
        const chunk = this.file.slice(start, end);
        
        const formData = new FormData();
        formData.append('chunk', chunk);
        formData.append('fileName', this.file.name);
        formData.append('chunkIndex', this.currentChunk);
        formData.append('totalChunks', this.totalChunks);
        formData.append('uploadId', this.uploadId);
        
        // Calculate progress
        const percentComplete = Math.round((this.currentChunk / this.totalChunks) * 100);
        this.onProgress(
            percentComplete,
            `Uploading chunk ${this.currentChunk + 1} of ${this.totalChunks}...`
        );
        
        try {
            const response = await this.sendChunk(formData);
            
            if (response.status === 'error') {
                throw new Error(response.message || 'Chunk upload failed');
            }
            
            if (response.status === 'complete') {
                // All chunks uploaded successfully
                this.onProgress(100, 'Upload complete, processing...');
                this.onComplete(response);
            } else if (response.status === 'progress') {
                // Move to next chunk
                this.currentChunk++;
                await this.uploadNextChunk();
            }
        } catch (error) {
            throw error;
        }
    }
    
    /**
     * Send a chunk to the server
     */
    sendChunk(formData) {
        return new Promise((resolve, reject) => {
            this.currentRequest = $.ajax({
                url: this.uploadUrl,
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                timeout: 60000, // 60 second timeout per chunk
                success: (response) => {
                    this.currentRequest = null;
                    resolve(response);
                },
                error: (xhr, status, error) => {
                    this.currentRequest = null;
                    if (status === 'abort') {
                        reject(new Error('Upload aborted'));
                    } else {
                        reject(new Error(`Network error: ${error}`));
                    }
                }
            });
        });
    }
}

/**
 * Helper function to determine if a file should use chunked upload
 * @param {File} file - The file to check
 * @param {number} threshold - Size threshold in bytes (default: 7MB)
 * @returns {boolean}
 */
function shouldUseChunkedUpload(file, threshold = 7 * 1024 * 1024) {
    return file && file.size > threshold;
}

/**
 * Helper function to format file size
 * @param {number} bytes - File size in bytes
 * @returns {string}
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

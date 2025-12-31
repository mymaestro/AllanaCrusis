# Chunked Upload Implementation

## Overview
Implemented a chunked upload mechanism to handle large files (parts PDFs and recording audio files) that exceed PHP upload limits or timeout restrictions.

## How It Works

### Architecture
1. **Client-side (JavaScript)**: Splits large files into 2MB chunks and uploads them sequentially
2. **Server-side (PHP)**: Receives and assembles chunks into the complete file
3. **Processing**: Existing insert_parts.php and insert_recordings.php process the assembled file normally

### Threshold
- Files **> 10MB** automatically use chunked upload
- Files **≤ 10MB** use standard upload
- Threshold can be adjusted in the JavaScript code

## New Files Created

### 1. Backend Handler
**File**: `src/includes/upload_chunk.php`
- Receives individual chunks via POST
- Saves chunks to temporary directory: `/tmp/allanacrusis_uploads/{uploadId}/`
- Assembles complete file when all chunks received
- Returns JSON status updates during upload

### 2. JavaScript Library
**File**: `public/js/chunked-upload.js`
- `ChunkedUploader` class handles file splitting and upload
- `shouldUseChunkedUpload()` helper determines if chunked upload needed
- `formatFileSize()` utility for display
- Progress callbacks for UI updates

## Modified Files

### Recordings
1. **src/recordings.php**
   - Added progress bar UI
   - Included chunked-upload.js script
   - Modified form submission to detect large files
   - Added `handleChunkedRecordingUpload()` function
   - Added `handleStandardRecordingUpload()` function
   - Added `processRecordingSubmission()` shared function

2. **src/includes/insert_recordings.php**
   - Detects chunked uploads via `uploadedFilePath` parameter
   - Uses `copy()` instead of `move_uploaded_file()` for chunked uploads
   - Cleans up temporary files after processing

### Parts
1. **src/parts.php**
   - Added progress bar UI
   - Included chunked-upload.js script
   - Modified form submission to detect large files
   - Added `handleChunkedPartUpload()` function
   - Added `handleStandardPartUpload()` function
   - Added `processPartSubmission()` shared function

2. **src/includes/insert_parts.php**
   - Detects chunked uploads via `uploadedFilePath` parameter
   - Uses `copy()` instead of `move_uploaded_file()` for chunked uploads
   - Cleans up temporary files after processing
   - Works with existing PHPdfer PDF metadata system

## User Experience

### Upload Flow
1. User selects large file (e.g., 50MB conductor score)
2. System detects file size > 10MB
3. Progress bar appears showing: "Uploading chunk X of Y..."
4. Each 2MB chunk uploads sequentially
5. When complete: "Upload complete, processing..."
6. File processed normally (PDF metadata, ID3 tags, etc.)
7. Success message displayed

### Visual Feedback
- File size displayed when selected
- Progress bar with percentage
- Status messages:
  - "Preparing chunked upload..."
  - "Uploading chunk 5 of 25..."
  - "Upload complete, processing..."
  - Success or error messages

## Technical Details

### Chunk Size
- Default: 2MB per chunk
- Configurable in ChunkedUploader constructor
- Recommended: 1-5MB depending on network conditions

### Temporary Storage
- Location: `/tmp/allanacrusis_uploads/`
- Each upload gets unique ID: `upload_{timestamp}_{random}`
- Cleanup: Automatic after file assembly
- Manual cleanup: Can be added via cron job if needed

### Security
- Upload ID sanitized (alphanumeric only)
- Filename sanitized with `basename()`
- MIME type validation still applies
- Existing file size limits enforced
- Role-based access control unchanged

### Browser Compatibility
- Requires: File API, FormData, Blob.slice()
- Supported: All modern browsers (Chrome, Firefox, Safari, Edge)
- Graceful degradation: Falls back to standard upload if chunk upload fails

## Testing Recommendations

1. **Small files (< 10MB)**: Should use standard upload
2. **Medium files (10-40MB)**: Test chunked upload
3. **Large files (40-100MB)**: Test with parts
4. **Very large files (100MB+)**: Test with increased max file size
5. **Network interruption**: Test error handling and recovery
6. **Timeout scenarios**: Verify no timeout issues

## Configuration

### PHP Settings (php.ini or .htaccess)
```ini
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
max_input_time = 300
memory_limit = 256M
```

### JavaScript Configuration
```javascript
// In recordings.php or parts.php
var uploader = new ChunkedUploader(file, {
    chunkSize: 2 * 1024 * 1024,  // Adjust chunk size
    // ... callbacks
});

// Change threshold
if (file && shouldUseChunkedUpload(file, 5 * 1024 * 1024)) {
    // Use chunked upload for files > 5MB
}
```

## Troubleshooting

### Uploads Fail
1. Check `/tmp` directory permissions
2. Verify `sys_get_temp_dir()` is writable
3. Check PHP error logs
4. Verify network connectivity

### Chunks Not Assembling
1. Check chunk naming/ordering
2. Verify all chunks received (check logs)
3. Confirm file permissions on temp directory

### Performance Issues
1. Reduce chunk size (1MB instead of 2MB)
2. Add delay between chunks if server overloaded
3. Check network bandwidth

## Future Enhancements

1. **Resume capability**: Save upload state, allow resuming interrupted uploads
2. **Parallel uploads**: Upload multiple chunks simultaneously
3. **Compression**: Compress chunks before upload
4. **Client-side validation**: Check file before uploading
5. **Progress persistence**: Store progress in session/localStorage
6. **Drag-and-drop**: Enhanced file selection UI
7. **Multiple files**: Upload multiple files in sequence

## Maintenance

### Cleanup Script
Consider adding a cron job to clean up old temporary files:
```bash
# Clean up temp files older than 24 hours
find /tmp/allanacrusis_uploads -type f -mtime +1 -delete
find /tmp/allanacrusis_uploads -type d -empty -delete
```

### Monitoring
- Monitor `/tmp` disk space usage
- Track failed upload rates
- Log analysis for optimization

## License
Licensed Materials - Property of ACWE  
(C) Copyright Austin Civic Wind Ensemble, 2025 All rights reserved.

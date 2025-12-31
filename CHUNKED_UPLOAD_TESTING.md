# Chunked Upload Testing Checklist

## Pre-Testing Setup
- [ ] Verify PHP configuration allows large uploads (100MB+)
- [ ] Check `/tmp` directory is writable
- [ ] Ensure you have librarian or admin role
- [ ] Clear browser cache

## Test Files to Prepare
- [ ] Small recording (~5MB MP3)
- [ ] Medium recording (~15MB FLAC)
- [ ] Large recording (~50MB WAV)
- [ ] Small part (~3MB PDF)
- [ ] Medium part (~15MB PDF)
- [ ] Large part (~60MB conductor score PDF)

## Recording Upload Tests

### Small File (< 10MB)
- [ ] Select small recording file
- [ ] Verify file size displays correctly
- [ ] Submit form
- [ ] Verify standard upload is used (no progress bar)
- [ ] Check file uploads successfully
- [ ] Verify recording appears in table
- [ ] Check ID3 tags are written correctly

### Medium File (10-40MB)
- [ ] Select medium recording file
- [ ] Verify file size displays correctly
- [ ] Submit form
- [ ] Verify progress bar appears
- [ ] Watch chunk upload progress (e.g., "Uploading chunk 5 of 8...")
- [ ] Verify "Upload complete, processing..." message
- [ ] Check file uploads successfully
- [ ] Verify recording appears in table
- [ ] Test playback works
- [ ] Check ID3 tags are written correctly

### Large File (40MB+)
- [ ] Select large recording file
- [ ] Verify file size displays correctly
- [ ] Submit form
- [ ] Verify progress bar appears
- [ ] Watch chunk upload progress
- [ ] Verify completion
- [ ] Check file uploads successfully
- [ ] Verify recording appears in table
- [ ] Test playback works

## Part Upload Tests

### Small File (< 10MB)
- [ ] Select composition
- [ ] Select part type
- [ ] Select small PDF file
- [ ] Submit form
- [ ] Verify standard upload is used (no progress bar)
- [ ] Check file uploads successfully
- [ ] Verify part appears in table
- [ ] Test PDF download
- [ ] Verify PDF metadata is correct

### Medium File (10-40MB)
- [ ] Select composition
- [ ] Select part type
- [ ] Select medium PDF file
- [ ] Submit form
- [ ] Verify progress bar appears
- [ ] Watch chunk upload progress
- [ ] Verify completion
- [ ] Check file uploads successfully
- [ ] Verify part appears in table
- [ ] Test PDF download
- [ ] Verify PDF metadata is correct

### Large File (40MB+)
- [ ] Select composition
- [ ] Select part type (use "Conductor Score")
- [ ] Select large PDF file (60MB+)
- [ ] Submit form
- [ ] Verify progress bar appears
- [ ] Watch chunk upload progress (may take 30+ chunks)
- [ ] Verify completion
- [ ] Check file uploads successfully
- [ ] Verify part appears in table
- [ ] Test PDF download
- [ ] Verify PDF metadata is correct

## Error Handling Tests

### File Too Large
- [ ] Try uploading 150MB recording (should fail gracefully)
- [ ] Verify appropriate error message

### Invalid File Type
- [ ] Try uploading .txt file as recording
- [ ] Verify MIME type rejection
- [ ] Try uploading .jpg file as part
- [ ] Verify file type rejection

### Network Interruption
- [ ] Start large upload
- [ ] Disable network mid-upload
- [ ] Verify error message appears
- [ ] Re-enable network
- [ ] Verify can upload again (no stuck state)

### Permission Issues
- [ ] Test upload as non-librarian user
- [ ] Verify access denied

## Browser Compatibility

### Desktop Browsers
- [ ] Chrome/Edge (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)

### Mobile Browsers (if applicable)
- [ ] Chrome Mobile
- [ ] Safari iOS

## Performance Tests

### Concurrent Uploads
- [ ] Open two browser tabs
- [ ] Start upload in both simultaneously
- [ ] Verify both complete successfully

### Server Load
- [ ] Monitor server resources during large upload
- [ ] Check PHP error logs
- [ ] Verify no timeout errors

## Database Verification

### After Recording Upload
```sql
SELECT * FROM recordings ORDER BY last_update DESC LIMIT 5;
```
- [ ] Verify new recording exists
- [ ] Check `link` field has correct filename
- [ ] Verify other metadata populated

### After Part Upload
```sql
SELECT * FROM parts ORDER BY last_update DESC LIMIT 5;
```
- [ ] Verify new part exists
- [ ] Check `image_path` field has hash filename
- [ ] Verify other metadata populated

## File System Verification

### Recording Files
```bash
ls -lh /path/to/public/recordings/{date}/
```
- [ ] Verify file exists
- [ ] Check file size matches upload
- [ ] Verify file permissions (644)

### Part Files
```bash
ls -lh /path/to/private/parts/
```
- [ ] Verify file exists with hash filename
- [ ] Check file size matches upload
- [ ] Verify file permissions (644)

### Temporary Files Cleanup
```bash
ls -la /tmp/allanacrusis_uploads/
```
- [ ] Verify directory is empty or only has recent uploads
- [ ] Check no old temp files remain

## Log Verification

### Check PHP Error Log
```bash
tail -f /var/log/php/error.log
```
- [ ] No PHP errors during upload
- [ ] No warnings about file operations

### Check Application Log
```bash
tail -f /path/to/application.log
```
- [ ] Verify "Processing chunked upload" messages
- [ ] Check "All chunks received" messages
- [ ] Verify successful assembly messages
- [ ] No error messages

## Edge Cases

### Zero-Byte File
- [ ] Create empty file
- [ ] Try to upload
- [ ] Verify appropriate handling

### Special Characters in Filename
- [ ] Upload file with spaces: "My Recording.mp3"
- [ ] Upload file with special chars: "Piece #1 (2024).pdf"
- [ ] Verify files upload and display correctly

### Very Long Filename
- [ ] Upload file with 200+ character name
- [ ] Verify handled correctly

## Update Tests

### Update Existing Recording
- [ ] Edit existing recording
- [ ] Upload new file (no file change)
- [ ] Verify update works
- [ ] Upload different file
- [ ] Verify file replaced

### Update Existing Part
- [ ] Edit existing part
- [ ] Change metadata (no file change)
- [ ] Verify update works
- [ ] Upload different PDF
- [ ] Verify file replaced

## Cleanup
- [ ] Delete test recordings
- [ ] Delete test parts
- [ ] Check test files removed from filesystem
- [ ] Verify no orphaned temp files

## Notes
Record any issues, errors, or unexpected behavior:

---
Date: _________________
Tester: _______________
Browser: ______________
Result: ☐ Pass ☐ Fail

Issues Found:

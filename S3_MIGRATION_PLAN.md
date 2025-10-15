# AWS S3 Migration Plan for AllanaCrusis Recordings

## Overview

This document outlines the plan to migrate audio recordings from local filesystem storage to AWS S3, providing better scalability, reliability, and performance for the AllanaCrusis music library system.

## Current Architecture

### Storage Structure
- **Local Path**: `ORGPUBLIC` directory (currently `../../public/files/recordings/`)
- **Web Access**: `ORGRECORDINGS` URL (currently `http://library1.local/files/recordings/`)
- **Organization**: Date-based folder structure (`/recordings/YYYY-MM-DD/filename.mp3`)
- **File Types**: MP3, WAV, FLAC, OGG audio files
- **Size Limit**: 40MB per file

### Current File Flow
1. User uploads audio file via recordings form
2. File saved to `ORGPUBLIC/YYYY-MM-DD/` directory
3. ID3 metadata written using getID3 library
4. Database stores filename and date for URL construction
5. Audio served directly via `ORGRECORDINGS` base URL

## Target Architecture

### S3 Structure
- **Bucket**: `allanacrusis-recordings` (or organization-specific name)
- **Key Structure**: `recordings/YYYY-MM-DD/filename.mp3`
- **Access**: Public read via S3 URLs or CloudFront distribution
- **Backup**: S3 versioning and cross-region replication
- **CDN**: CloudFront distribution for global performance

### New File Flow
1. User uploads audio file via recordings form
2. File uploaded directly to S3 bucket
3. ID3 metadata processing (local temp file or S3-based)
4. Database stores S3 key path
5. Audio served via CloudFront or S3 URLs

## Implementation Plan

### Phase 1: Infrastructure Setup

#### AWS Resources
- [ ] Create S3 bucket with appropriate naming
- [ ] Configure bucket policy for public read access
- [ ] Set up CloudFront distribution (optional but recommended)
- [ ] Create IAM user/role with minimal S3 permissions
- [ ] Generate access keys for application use

#### Local Environment
- [ ] Install AWS SDK for PHP via Composer: `composer require aws/aws-sdk-php`
- [ ] Add S3 configuration to `config.php` and `config.example.php`

### Phase 2: Configuration Changes

#### New Configuration Constants
```php
// Add to config.php
define('AWS_S3_ENABLED', true);
define('AWS_S3_BUCKET', 'allanacrusis-recordings');
define('AWS_S3_REGION', 'us-east-1');
define('AWS_ACCESS_KEY_ID', 'your_access_key');
define('AWS_SECRET_ACCESS_KEY', 'your_secret_key');
define('AWS_CLOUDFRONT_DOMAIN', 'https://d1234567890.cloudfront.net'); // Optional
```

#### Backward Compatibility
```php
// Modified ORGRECORDINGS for S3 or local
if (AWS_S3_ENABLED) {
    define('ORGRECORDINGS', AWS_CLOUDFRONT_DOMAIN . '/recordings/');
} else {
    define('ORGRECORDINGS', 'http://library1.local/files/recordings/');
}
```

### Phase 3: Code Modifications

#### Files to Modify

**1. `src/includes/insert_recordings.php`**
- Replace local file upload with S3 putObject
- Maintain same folder structure in S3 keys
- Handle ID3 metadata (temp file approach)
- Error handling for S3 operations

**2. `src/includes/select_recordings.php`**
- Update URL generation for S3/CloudFront
- Maintain same audio player functionality

**3. `src/includes/upload_recording.php`**
- Similar S3 upload modifications
- Unified upload function

**4. `src/index.php`**
- Update homepage audio player URLs

**5. `scripts/find_unreferenced_audio.php`**
- Modify to work with S3 bucket listing
- Update cleanup logic for S3 objects

#### New Helper Functions
```php
// src/includes/s3_functions.php
function uploadRecordingToS3($tempFile, $s3Key, $metadata = []) {
    // S3 upload logic
}

function getRecordingUrl($dateFolder, $filename) {
    // Generate S3 or CloudFront URL
}

function deleteRecordingFromS3($s3Key) {
    // S3 deletion logic
}
```

### Phase 4: Migration Script

#### Data Migration
Create `scripts/migrate_recordings_to_s3.php`:
- [ ] Scan existing recordings directory
- [ ] Upload each file to S3 with proper key structure
- [ ] Verify successful upload
- [ ] Update database if needed (URLs vs. paths)
- [ ] Generate migration report

#### Migration Steps
1. **Backup existing recordings** (tar/zip local files)
2. **Run migration script** in test mode first
3. **Verify all files accessible** via S3 URLs
4. **Update configuration** to enable S3
5. **Test upload/playback functionality**
6. **Clean up local files** after verification

### Phase 5: Testing & Validation

#### Test Cases
- [ ] Upload new recording via web interface
- [ ] Play existing migrated recordings
- [ ] Edit recording metadata
- [ ] Delete recordings (both UI and cleanup script)
- [ ] Homepage random recording playback
- [ ] Permission checks for different user roles

#### Performance Testing
- [ ] Compare load times: local vs S3 vs CloudFront
- [ ] Test with multiple concurrent audio streams
- [ ] Verify mobile/responsive playback

## Risk Mitigation

### Rollback Plan
- Keep local files until migration fully verified
- Configuration flag to switch back to local storage
- Database backup before any schema changes

### Error Handling
- Graceful fallback if S3 unavailable
- Proper error messages for upload failures
- Logging for S3 operations

### Security Considerations
- Minimal IAM permissions (S3 bucket access only)
- Secure credential storage (environment variables)
- Public read-only access to recordings bucket
- Consider signed URLs for private recordings (future enhancement)

## Cost Analysis

### AWS Costs (Estimated Monthly)
- **S3 Storage**: ~$0.023/GB for standard storage
- **S3 Requests**: ~$0.0004 per 1,000 GET requests
- **CloudFront**: ~$0.085/GB for first 10TB transfer
- **Estimated Total**: $5-20/month depending on usage

### Benefits vs. Costs
- **Eliminated**: Local storage requirements
- **Improved**: Global performance via CDN
- **Enhanced**: Reliability and backup
- **Scalable**: No server storage limits

## Timeline

### Estimated Effort
- **Development**: 2-3 days
- **Infrastructure Setup**: 1 day
- **Testing**: 1 day
- **Migration**: 1 day
- **Total**: ~1 week

### Dependencies
- AWS account setup and billing
- Testing environment for validation
- Backup strategy for existing recordings
- User communication for potential downtime

## Future Enhancements

### Advanced Features (Post-Migration)
- **Transcoding**: Automatic format conversion via AWS MediaConvert
- **Streaming**: Adaptive bitrate streaming for better performance
- **Analytics**: CloudWatch metrics for usage tracking
- **Private Recordings**: Signed URLs for restricted access
- **Compression**: Automatic audio compression/optimization

### Integration Opportunities
- **Lambda Functions**: Automated processing workflows
- **API Gateway**: RESTful API for recordings management
- **ElasticSearch**: Enhanced audio metadata search
- **Machine Learning**: Audio analysis and categorization

## Maintenance

### Ongoing Tasks
- Monitor S3 costs and usage
- Regular cleanup of unreferenced recordings
- Update AWS SDK and dependencies
- Review and rotate access credentials
- Monitor CloudFront performance metrics

### Documentation Updates
- Update user documentation for any workflow changes
- Document new deployment procedures
- Update troubleshooting guides for S3-related issues

---

**Last Updated**: October 15, 2025  
**Document Version**: 1.0  
**Next Review**: After implementation completion
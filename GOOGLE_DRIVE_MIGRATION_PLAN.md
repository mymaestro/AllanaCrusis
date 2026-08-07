# Google Drive Migration Plan for AllanaCrusis Recordings

## Overview

This document outlines a plan to migrate audio recordings from local filesystem storage to Google Drive, with a focus on a Google for Nonprofits environment. It is designed to be a practical alternative to the AWS S3 migration path while preserving existing AllanaCrusis upload and playback workflows.

## Important Platform Note

Google Drive can work for archival and internal sharing, but it is not a CDN-style object store. For public, high-volume playback, Google Cloud Storage + Cloud CDN is generally a better long-term fit.

If this project chooses Google Drive anyway, the architecture below minimizes disruption and keeps a future migration path open.

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

## Target Architecture (Google Drive)

### Drive Structure
- **Shared Drive**: `AllanaCrusis Recordings` (recommended over personal My Drive)
- **Folder Structure**: `recordings/YYYY-MM-DD/filename.mp3`
- **Identity**: Service account with domain-wide delegation or explicit Shared Drive access
- **Access**: Application-controlled access via Drive API and a local proxy endpoint
- **Optional Public Access**: Per-file sharing where policy permits (not preferred)

### New File Flow
1. User uploads audio file via recordings form
2. Application uploads file to Google Drive folder via Drive API
3. ID3 metadata processing remains local temp-file based
4. Database stores Drive file ID and normalized path metadata
5. Audio served through application endpoint (recommended) or shared link strategy

## Implementation Plan

### Phase 1: Google Workspace and API Setup

#### Google Resources
- [ ] Confirm Google for Nonprofits eligibility and plan limits
- [ ] Create Shared Drive for recordings
- [ ] Enable Google Drive API in Google Cloud project
- [ ] Create service account and secure key material
- [ ] Grant service account least-privilege access to Shared Drive

#### Application Environment
- [ ] Install Google API client library for PHP: `composer require google/apiclient:^2.17`
- [ ] Add Google Drive configuration to `config.php` and `config.example.php`
- [ ] Define secure location for service account credentials JSON

### Phase 2: Configuration Changes

#### New Configuration Constants
```php
// Add to config.php
define('GOOGLE_DRIVE_ENABLED', true);
define('GOOGLE_DRIVE_SHARED_DRIVE_ID', 'your_shared_drive_id');
define('GOOGLE_DRIVE_ROOT_FOLDER_ID', 'your_root_recordings_folder_id');
define('GOOGLE_DRIVE_SERVICE_ACCOUNT_JSON', '/secure/path/service-account.json');

// Playback mode: 'proxy' (recommended), 'public_link' (limited use)
define('GOOGLE_DRIVE_PLAYBACK_MODE', 'proxy');
```

#### Backward Compatibility
```php
if (defined('GOOGLE_DRIVE_ENABLED') && GOOGLE_DRIVE_ENABLED) {
    // Local endpoint that streams bytes from Drive to audio player
    define('ORGRECORDINGS', '/src/download_token.php?provider=drive&key=');
} else {
    define('ORGRECORDINGS', 'http://library1.local/files/recordings/');
}
```

## Phase 3: Code Modifications

### Files to Modify

**1. `src/includes/insert_recordings.php`**
- Replace local file write with Drive API upload
- Preserve date-based folder organization in Drive
- Persist Drive file ID for each upload
- Add retry/error handling for API quota and transient failures

**2. `src/includes/select_recordings.php`**
- Generate playback URL based on provider (`local`, `s3`, or `drive`)
- Keep existing player markup and behavior unchanged

**3. `src/includes/upload_recording.php`**
- Reuse centralized provider upload logic
- Standardize validation across storage backends

**4. `src/download_token.php`**
- Extend to support `provider=drive`
- Stream bytes from Drive API with range request support for audio seek

**5. `scripts/find_unreferenced_audio.php`**
- Add Drive folder traversal mode
- Compare DB references against Drive file IDs/paths

### New Helper Functions
```php
// src/includes/drive_functions.php
function getDriveClient() {
    // Create and return authenticated Google Drive client
}

function uploadRecordingToDrive($tempFile, $targetFolderId, $filename, $mimeType) {
    // Upload and return Drive file metadata including file ID
}

function ensureDateFolder($parentFolderId, $dateFolderName) {
    // Find or create YYYY-MM-DD folder and return folder ID
}

function streamDriveFileToOutput($fileId) {
    // Proxy file stream to HTTP response with range support
}
```

### Database Notes
- Add nullable columns where useful:
  - `storage_provider` (`local`, `s3`, `drive`)
  - `storage_object_id` (Drive file ID)
  - `storage_path` (`recordings/YYYY-MM-DD/filename.mp3`)
- Keep existing fields for backward compatibility during migration window

## Phase 4: Migration Script

### Data Migration
Create `scripts/migrate_recordings_to_drive.php`:
- [ ] Scan existing recordings directory
- [ ] Create/fetch corresponding date folder in Drive
- [ ] Upload each file and capture Drive file ID
- [ ] Update database `storage_provider` and `storage_object_id`
- [ ] Emit CSV/JSON report for audit

### Migration Steps
1. **Backup existing recordings** (tar/zip local files)
2. **Run dry-run mode** to validate path mapping only
3. **Run upload mode** in batches (for quota safety)
4. **Verify playback** via application proxy endpoint
5. **Enable GOOGLE_DRIVE_ENABLED** in staging
6. **Production cutover** after validation
7. **Retain local files** until sign-off

## Phase 5: Testing and Validation

### Functional Tests
- [ ] Upload new recording via web interface
- [ ] Playback including seek/scrub in browser audio player
- [ ] Edit metadata and confirm no file regression
- [ ] Delete recording and confirm Drive delete policy
- [ ] Homepage random playback compatibility
- [ ] Permissions by user role

### Resilience and Performance Tests
- [ ] Verify behavior under Drive API transient errors
- [ ] Test rate-limit handling and retries
- [ ] Compare first-byte latency local vs Drive proxy
- [ ] Validate partial-content (`206`) responses for seeking

## Risk Mitigation

### Operational Risks
- **API Quotas**: Drive API request limits can throttle playback-heavy workloads
- **Public Link Fragility**: Link permission changes can break playback
- **Latency Variability**: Proxying through app can increase server load
- **Governance Changes**: Workspace policy changes can affect sharing behavior

### Rollback Plan
- Keep local files intact until acceptance testing is complete
- Feature flag (`GOOGLE_DRIVE_ENABLED`) for instant fallback
- Database backup before schema migration

### Security Considerations
- Store service account JSON outside web root
- Restrict service account to required Shared Drive scope only
- Audit and rotate credentials periodically
- Prefer app-mediated access over broad public sharing

## Cost and Nonprofit Considerations

### Google for Nonprofits Notes
- Workspace nonprofit plans may reduce operational cost for collaboration tools
- Storage and sharing policy limits vary by edition and organization settings
- Confirm whether expected recording volume and public traffic fit your plan

### Expected Cost Profile
- **Drive itself**: Often attractive for internal storage and team workflows
- **App proxy overhead**: Increased app-server bandwidth and CPU compared with direct CDN delivery
- **Potential add-ons**: If traffic grows, Cloud Storage + CDN may become cheaper and faster

## Timeline

### Estimated Effort
- **Development**: 3-5 days
- **Google setup and security review**: 1-2 days
- **Testing**: 1-2 days
- **Migration and cutover**: 1 day
- **Total**: ~1-2 weeks

### Dependencies
- Google Workspace admin support
- Cloud project and API approval process
- Staging environment for load/performance validation
- User communication for cutover window

## Future Enhancements

### If Staying on Google Ecosystem
- Migrate runtime storage backend from Drive to Google Cloud Storage
- Add Cloud CDN for lower latency and global delivery
- Introduce signed URLs for restricted recordings
- Add background jobs for metadata extraction and validation

### Cross-Provider Abstraction
- Implement provider-agnostic storage interface (`local`, `s3`, `drive`)
- Keep migration scripts reusable for future backend shifts
- Centralize URL generation and file lifecycle operations

## Maintenance

### Ongoing Tasks
- Monitor Drive API quota usage and error rates
- Clean up unreferenced files regularly
- Rotate service account credentials and review IAM-like access
- Validate Shared Drive permissions after admin policy changes

### Documentation Updates
- Update deployment docs with service account setup steps
- Add troubleshooting guide for Drive API and auth failures
- Document fallback/cutover procedures for support users

---

**Last Updated**: July 21, 2026  
**Document Version**: 1.0  
**Next Review**: After proof-of-concept and staging validation
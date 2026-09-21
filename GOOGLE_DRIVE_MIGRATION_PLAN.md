# Google Drive Migration Plan for AllanaCrusis Recordings

## Overview

AllanaCrusis should treat its recordings as an archive of historical performance material, not as a media delivery platform. The project’s purpose is to preserve and catalog the organization’s audio heritage while keeping a familiar ingestion and metadata workflow for librarians and administrators.

This plan covers only audio recordings. PDF documents and other non-audio materials remain local to the project and are not part of this cloud archive strategy. A future, separate project may evaluate Dropbox or another document-storage service for PDFs.

This document is a technical implementation guide for using Google Drive as the archival backend for recordings. It assumes the project will remain archive-first and metadata-first, with AllanaCrusis as the intake point for all recordings and with public-facing slideshow or video outputs kept separate from the core app.

## Core Operating Principle

AllanaCrusis is the first point of contact for all media ingestion. All uploaded source audio files should flow through the app, where the system writes ID3 metadata directly into the file and stores catalog-level context in the database.

This is a critical operational requirement: the source audio file must remain self-describing and usable by standard media players, external scanners, and downstream automation even when it is no longer being served by the application.

## Why Google Drive Fits This Project

Google Drive is a valid archival choice when the project values:

- low operational overhead
- shared access for team members
- nonprofit-friendly storage capacity
- simple collaboration and administration
- a managed cloud archive without a complex infrastructure plan

Google Drive is not a CDN and is not a high-volume public media delivery platform. It is best used as a durable, managed archive backend for historical recordings, not as a streaming service.

## Storage and Access Model

### Recommended Architecture
- Shared Drive: `AllanaCrusis Recordings`
- Folder pattern: `recordings/YYYY-MM-DD/filename.ext`
- Application-managed access through the Drive API
- Optional local cache or fallback retained during migration only
- Public-facing video exports kept outside the application

### Recommended File Flow
1. Upload audio file through the AllanaCrusis app
2. Validate MIME type and file size
3. Write ID3 metadata directly into the source file
4. Save the canonical file into the Google Drive archive
5. Record the Drive file ID and storage metadata in the database
6. Serve audio through a controlled app endpoint when in-app playback is needed

## Technical Requirements

### Google Workspace and API Setup
- [ ] Confirm Google Workspace or Google for Nonprofits eligibility
- [ ] Create a Shared Drive for recordings
- [ ] Create a folder structure matching the archive organization pattern
- [ ] Enable the Google Drive API in the Google Cloud project
- [ ] Create a service account with least-privilege access
- [ ] Store service account credentials outside the web root
- [ ] Grant the service account access only to the relevant Shared Drive

### Application Configuration
Add the following configuration values to `config.php` and `config.example.php`:

```php
// config.php
if (!defined('RECORDINGS_STORAGE_PROVIDER')) {
    define('RECORDINGS_STORAGE_PROVIDER', 'drive');
}

define('GOOGLE_DRIVE_ENABLED', true);
define('GOOGLE_DRIVE_SHARED_DRIVE_ID', 'shared_drive_id_here');
define('GOOGLE_DRIVE_ROOT_FOLDER_ID', 'root_folder_id_here');
define('GOOGLE_DRIVE_SERVICE_ACCOUNT_JSON', '/secure/path/service-account.json');
```

### Database Schema Additions
Add provider-aware metadata while keeping the existing fields intact for compatibility:

- `storage_provider` (`local`, `drive`, `s3`)
- `storage_object_id` (Drive file ID or other object key)
- `storage_path` (relative archive path)
- `last_sync_at` (optional)
- `is_public_export_ready` (optional)
- `youtube_url` or `external_media_url` (optional)

The older fields such as `link` and date can remain as legacy fields during transition.

## ID3 Metadata Requirements

All uploads should be processed through the existing ID3 logic in the app. This should be considered a required step, not a secondary feature.

Recommended fields:

- title
- artist / performer
- album / concert or performance collection
- date
- genre
- comments or historical notes
- track/recording identifier
- related catalog number when available

These tags should be written directly into the audio file at ingest time.

## App-Level Changes Needed

### Files to update
- [config/config.php](config/config.php)
- [src/includes/upload_recording.php](src/includes/upload_recording.php)
- [src/includes/select_recordings.php](src/includes/select_recordings.php)
- [src/recordings.php](src/recordings.php)
- [src/index.php](src/index.php) if playback URLs are generated there
- [src/download_token.php](src/download_token.php) or a new stream endpoint

### New helper layer
Create a lightweight provider abstraction:

- `src/includes/storage/storage_interface.php`
- `src/includes/storage/local_storage.php`
- `src/includes/storage/drive_storage.php`

Minimum methods:

```php
interface RecordingStorageInterface {
    public function saveUpload($tempPath, $filename, $date, $mimeType);
    public function deleteFile($recordingId, $path);
    public function buildPlaybackUrl($recording);
    public function streamFile($recording, $rangeHeader = null);
    public function getMetadata($recording);
}
```

## Playback and Access Strategy

The app should not depend on raw public Google Drive links as the primary access method.

Instead:

- the browser requests a controlled app endpoint
- the app resolves the recording to its object and provider
- the app streams the file with the proper headers and range support
- the app keeps the same user-facing audio player experience

This is necessary because Drive is not designed as a public CDN-style audio host for volume playback.

## Migration Strategy

### Recommended rollout plan
1. Back up the current recording directory and database
2. Run a dry-run inventory of all existing recordings
3. Upload files to the Drive Shared Drive in batches
4. Record the Drive file ID and the archive path in the DB
5. Validate playback through the application endpoint
6. Keep a local fallback copy until acceptance testing passes
7. Switch the app to the drive provider in staging
8. Cut over to production only after validation

### Migration script
Create a script such as `scripts/migrate_recordings_to_drive.php` with the following responsibilities:

- scan the local recordings directory
- map files to the intended date-based Drive path
- create missing date folders in the Shared Drive
- upload each file and capture the file ID
- update the database with provider and storage metadata
- generate a CSV or JSON report of results and failures

## Operational Risks and Mitigations

### Risk: Drive quota and rate limits
Mitigation: keep the archive process batch-based and monitor API activity.

### Risk: broken or brittle public links
Mitigation: do not rely on public file URLs for app playback; use app-controlled access.

### Risk: overbuilding the app
Mitigation: keep the storage abstraction minimal and archive-first.

### Risk: metadata inconsistency
Mitigation: write ID3 tags during intake and keep DB metadata as a secondary reference layer.

## Rollback Plan

- retain the original local files until sign-off
- keep `storage_provider` nullable during transition
- support a fallback order such as `drive -> local`
- keep a feature flag to switch providers back immediately

## Final Recommendation

Google Drive is a strong archival backend for this project when the goal is durable storage, team access, low operational overhead, and historical preservation. It should not be treated as a public media CDN.

The correct implementation approach is:

- AllanaCrusis as the intake and cataloging system
- embedded ID3 metadata at upload time
- Google Drive as the canonical archive backend
- a separate external media-production workflow for slideshow videos and YouTube content

This preserves the historical record while keeping the application focused on cataloging and metadata rather than public media hosting.

---

**Last Updated**: September 21, 2026  
**Document Version**: 3.0  
**Next Review**: Before implementation of the Google Drive archival migration
If usage remains low and the archive remains primarily historical/reference, the most cost-effective and least-risky path is a Google Drive-backed archive with controlled app access, not a more elaborate CDN-style delivery system.

---

**Last Updated**: September 21, 2026  
**Document Version**: 2.1  
**Next Review**: Before any archival migration to Google Drive
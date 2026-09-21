# AWS S3 Migration Plan for AllanaCrusis Recordings

## Overview

AllanaCrusis should treat its recordings as an archive of historical performance material, not as a media delivery platform. The project’s purpose is to preserve and catalog the organization’s audio heritage while keeping a familiar ingestion and metadata workflow for librarians and administrators.

This plan covers only audio recordings. PDF documents and other non-audio materials remain local to the project and are not part of this cloud archive strategy. A future, separate project may evaluate Dropbox or another document-storage service for PDFs.

This document is a technical implementation guide for using Amazon S3 as the archival backend for recordings. It assumes the project will remain archive-first and metadata-first, with AllanaCrusis as the intake point for all recordings and with public-facing slideshow or video outputs kept separate from the core app.

## Core Operating Principle

AllanaCrusis is the first point of contact for all media ingestion. All uploaded source audio files should flow through the app, where the system writes ID3 metadata directly into the file and stores catalog-level context in the database.

This is a critical operational requirement: the source audio file must remain self-describing and usable by standard media players, external scanners, and downstream automation even when it is no longer being served by the application.

## Why S3 Fits This Project

Amazon S3 is a valid archival choice when the project values:

- durable object storage
- strong lifecycle management
- scalable long-term retention
- a future-ready path for automation and integrations
- infrastructure-level control over storage policy and access

S3 is not a CDN and is not a public media delivery platform by default. It is best used as a durable cloud archive backend for historical recordings, not as a streaming service.

## Storage and Access Model

### Recommended Architecture
- Bucket: `allanacrusis-recordings` or organization-specific equivalent
- Key pattern: `recordings/YYYY-MM-DD/filename.ext`
- Optional CloudFront or application-managed endpoint for playback
- Versioning and lifecycle controls enabled for archive integrity
- Public-facing video exports kept outside the application

### Recommended File Flow
1. Upload audio file through the AllanaCrusis app
2. Validate MIME type and file size
3. Write ID3 metadata directly into the source file
4. Save the canonical file into the S3 archive
5. Record the object key and storage metadata in the database
6. Serve audio through a controlled app endpoint when in-app playback is needed

## Technical Requirements

### AWS Setup
- [ ] Create the S3 bucket for recordings
- [ ] Configure bucket lifecycle rules and versioning
- [ ] Define access policy for archival use
- [ ] Add CloudFront if managed playback delivery is needed
- [ ] Create an IAM user or role with least-privilege permissions
- [ ] Store credentials outside the web root and rotate them periodically

### Application Configuration
Add the following configuration values to `config.php` and `config.example.php`:

```php
// config.php
if (!defined('RECORDINGS_STORAGE_PROVIDER')) {
    define('RECORDINGS_STORAGE_PROVIDER', 's3');
}

define('AWS_S3_ENABLED', true);
define('AWS_S3_BUCKET', 'allanacrusis-recordings');
define('AWS_S3_REGION', 'us-east-1');
define('AWS_ACCESS_KEY_ID', 'your_access_key');
define('AWS_SECRET_ACCESS_KEY', 'your_secret_key');
define('AWS_CLOUDFRONT_DOMAIN', 'https://d1234567890.cloudfront.net'); // optional
```

### Database Schema Additions
Add provider-aware metadata while keeping the existing fields intact for compatibility:

- `storage_provider` (`local`, `drive`, `s3`)
- `storage_object_id` (S3 object key or object ID)
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
- `src/includes/storage/s3_storage.php`

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

The app should not depend on raw public S3 object URLs as the primary access method.

Instead:

- the browser requests a controlled app endpoint
- the app resolves the recording to its object and provider
- the app streams the file with the proper headers and range support
- the app keeps the same user-facing audio player experience

This keeps playback stable without treating S3 as a public CDN.

## Migration Strategy

### Recommended rollout plan
1. Back up the current recording directory and database
2. Run a dry-run inventory of all existing recordings
3. Upload files to the S3 bucket in batches
4. Record the object key and storage metadata in the DB
5. Validate playback through the application endpoint
6. Keep a local fallback copy until acceptance testing passes
7. Switch the app to the S3 provider in staging
8. Cut over to production only after validation

### Migration script
Create a script such as `scripts/migrate_recordings_to_s3.php` with the following responsibilities:

- scan the local recordings directory
- map files to the intended date-based S3 key path
- create any required prefixes or folders in the bucket structure
- upload each file and capture the result
- update the database with provider and storage metadata
- generate a CSV or JSON report of results and failures

## Operational Risks and Mitigations

### Risk: object lifecycle or permission mistakes
Mitigation: configure bucket policies carefully and test access before cutover.

### Risk: public access becoming too brittle
Mitigation: do not rely on public object URLs for primary playback; use app-managed access.

### Risk: overbuilding the app
Mitigation: keep the storage abstraction minimal and archive-first.

### Risk: metadata inconsistency
Mitigation: write ID3 tags during intake and keep DB metadata as a secondary reference layer.

## Rollback Plan

- retain the original local files until sign-off
- keep `storage_provider` nullable during transition
- support a fallback order such as `s3 -> local`
- keep a feature flag to switch providers back immediately

## Final Recommendation

S3 is a strong archival backend for this project when the goal is durable storage, lifecycle control, and a future-ready cloud architecture. It should not be treated as a public media CDN.

The correct implementation approach is:

- AllanaCrusis as the intake and cataloging system
- embedded ID3 metadata at upload time
- S3 as the canonical archive backend
- a separate external media-production workflow for slideshow videos and YouTube content

This preserves the historical record while keeping the application focused on cataloging and metadata rather than public media hosting.

---

**Last Updated**: September 21, 2026  
**Document Version**: 3.0  
**Next Review**: Before implementation of the S3 archival migration
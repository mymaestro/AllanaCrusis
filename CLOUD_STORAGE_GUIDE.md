# Cloud Storage Guide for AllanaCrusis Recordings

## Purpose

This guide is the decision document for choosing a storage backend for the AllanaCrusis recording archive.

This comparison covers only audio recordings. PDFs and other local document files remain outside the cloud storage decision and stay local to the project. A future, separate project may evaluate Dropbox or another document-storage service for PDFs.

It is intentionally different from the technical implementation guides:

- [GOOGLE_DRIVE_MIGRATION_PLAN.md](GOOGLE_DRIVE_MIGRATION_PLAN.md) is the technical implementation guide for Google Drive
- [S3_MIGRATION_PLAN.md](S3_MIGRATION_PLAN.md) is the technical implementation guide for Amazon S3
- This document is the practical comparison guide for deciding which option fits the project best

## Core Principle

AllanaCrusis remains the first point of contact for all recordings. The app receives the source audio, writes ID3 metadata into the file, and preserves the historical record. The cloud storage system is simply the archive backend that stores the canonical source files.

Public-facing slideshow videos or YouTube exports remain separate from the core archive workflow.

## Decision Criteria

Choose a storage backend based on these criteria:

- administrative access and support
- cost and operating model
- required collaboration features
- expected traffic and access patterns
- long-term archive durability needs
- willingness to manage operational configuration

## Options Compared

### 1) Local storage
Best for: small archives, simple self-hosted environments, no cloud access

Advantages:
- simplest and least expensive
- no vendor dependency
- easy to understand and operate
- good for a small archive or early-stage deployment

Disadvantages:
- limited durability without good backup strategy
- no built-in versioning or distributed redundancy
- more operational burden for backup and recovery
- harder to share across teams

Use local storage when:
- the archive is small
- the team is not using a cloud provider
- operational simplicity matters more than managed infrastructure

### 2) Google Drive
Best for: nonprofit or Google Workspace environments, collaborative archive management, simple admin model

Advantages:
- low operational overhead
- easy shared team access
- large nonprofit storage allowance
- simple collaboration for staff and administrators
- natural fit with YouTube and Google ecosystem workflows

Disadvantages:
- not designed as a CDN-style media delivery platform
- not ideal for high-volume public audio streaming
- playback performance is not the same as object storage with a dedicated CDN layer
- app access must still be administered carefully

Use Google Drive when:
- the organization already has Google Workspace or Google for Nonprofits access
- collaboration and management simplicity matter
- the project is primarily archival and low-demand
- the goal is durable preservation, not a public music-streaming service

### 3) Amazon S3
Best for: infrastructure-oriented environments, durable archive operations, automation-heavy workflows

Advantages:
- strong durability and lifecycle controls
- scalable object-storage architecture
- mature ecosystem for automation and integration
- more control over permissions, backups, and operational policies
- good fit for future expansion and custom workflows

Disadvantages:
- requires more admin and configuration knowledge
- higher operational complexity than Drive or local storage
- not useful as a public media platform without further delivery configuration
- more overhead if the project does not need advanced infrastructure features

Use S3 when:
- the organization already has AWS access or cloud administration support
- the archive may grow substantially
- the team wants stricter operational control and automation
- the archive is being treated as a long-term infrastructure asset

## Recommended Rule of Thumb

Use this rule when choosing:

- If the organization is already in Google Workspace and wants simplicity, choose Google Drive.
- If the organization already has AWS operations and wants more control, choose S3.
- If neither cloud service is available, choose local storage as the fallback.

## Archive-First Recommendation

The project should not be built as a streaming platform. It should be built as a preservation archive with occasional access and metadata-driven discovery.

That means the main decision is not which platform is best for public playback. The main decision is which backend is best for durable, manageable historical storage.

From that perspective:

- Google Drive is an excellent option for organizations already working in Google Workspace
- S3 is an excellent option for organizations with cloud operations and stronger infrastructure requirements
- local storage remains a valid fallback when simplicity and self-hosting are preferred

## Good Practice for Any Option

Regardless of which backend is chosen:

- AllanaCrusis remains the intake and metadata point
- ID3 metadata should be embedded directly into each audio file at ingest time
- the archive should be separate from any public media export workflows
- the app should use provider-aware metadata fields for storage location tracking
- public media outputs should be separated from the master archive

## Final Recommendation

For most volunteer or nonprofit groups with modest playback demand, the best practical path is usually:

- Google Drive if the organization is already using Google Workspace
- S3 if the organization has stronger infrastructure support and wants more control
- local storage only when a cloud solution is not available or appropriate

The key requirement is not raw public playback performance. The key requirement is preserving the historical recording archive in a durable, manageable, and portable way.

---

**Last Updated**: September 21, 2026  
**Document Version**: 1.0  
**Next Review**: Before any vendor selection or migration planning cycle

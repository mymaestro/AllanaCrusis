# Music Library DB

![Music library logo](public/images/logo.png)

A comprehensive web-based music library management system designed for concert bands, wind ensembles, orchestras, and other large musical groups. Track your sheet music collection, manage parts, organize concerts, and maintain performance recordings with this full-featured database application.

## 🎵 Overview


The musicLibraryDB is a sophisticated music library management system that helps music organizations:

### Features You Will Love
- **No more spreadsheets:** Catalog your entire music library, including compositions, parts, concerts, and recordings, in one organized, searchable system.
- **Easy part distribution:** Instantly see which parts are missing, preview distribution lists, and share download links with your musicians; no more email chains or lost PDFs.
- **Quick answers for Music directors:** Find out what pieces you own, who played what, and which parts are available with just a few clicks.
- **Effortless reporting:** Generate inventory, concert history, and statistics reports for your board or director; no manual tallying required.
- **Simple user management:** Add new users, assign librarian/admin roles, and control access to sensitive features, all from one dashboard.
- **Secure & Reliable:** Your files and data are protected, with secure downloads and automatic HTTPS. Only authorized users can access sensitive materials.
- **Fast Search & Sort:** Instantly search and sort your library by composer, title, grade, or any field. Perfect for planning concerts or finding missing parts.

### Now you can:
- **Catalog compositions** with detailed metadata (composer, arranger, grade, genre, etc.)
- **Manage sheet music parts** for different instruments and sections
- **Organize concerts and programs** with playgrams (concert playlists)
- **Store and manage performance recordings** with audio file uploads
- **Track instrument parts** and their physical storage locations
- **Generate reports** about your music collection
- **Control access** with user roles and permissions

Whether you're managing a small community band library or a large institutional collection, musicLibraryDB scales to meet your needs.

## 🏗️ System Architecture

**Technology Stack:**
- **Backend**: PHP 7.4+ with MySQL/MariaDB
- **Frontend**: Bootstrap 5 responsive web interface
- **Audio Processing**: getID3 library for metadata extraction
- **Web Server**: Apache/Nginx (LAMP/LEMP stack)

**Key Features:**
- Role-based access control (Administrator, Librarian, User)
- Responsive design that works on desktop, tablet, and mobile
- Audio file upload with automatic metadata tagging
- Full-text search across compositions
- Part distribution tracking for concerts
- Paper size management for physical parts

## 📋 Requirements

### System Requirements
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **PHP**: 7.4 or higher with extensions:
  - `mysqli` (MySQL/MariaDB connectivity)
  - `json` (JSON handling)
  - `fileinfo` (file type detection)
  - `mbstring` (multibyte string handling)
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **GhostScript**: Uses `gs` to set metadata in PDF parts you upload (optional)
- **Vorbis tools**: Uses `vorbiscomment` to set metadata in OGG and FLAC audio files (optional)
- **Storage**: Minimum 500MB for application + space for recordings and parts

### Browser Support
- Chrome 90+ (recommended)
- Firefox 88+
- Safari 14+
- Edge 90+

## 🚀 Installation

### Step 1: Download and Setup Files

```bash
# Clone to your web server's document root
cd /var/www/html
git clone https://github.com/mymaestro/musicLibraryDB1.git
cd musicLibraryDB1

# Set proper permissions
chmod 755 .
chmod -R 755 src/includes/
chmod -R 777 public/files/  # For file uploads
```

### Step 2: Database Setup

Create your database and user:

```sql
CREATE DATABASE musicLibraryDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'musicLibraryDB'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON musicLibraryDB.* TO 'musicLibraryDB'@'localhost';
FLUSH PRIVILEGES;
```

Choose and import one of the SQL setup files from the `setup/` directory:

- **musicLibraryDB-core.sql** - Minimal setup with empty tables
- **musicLibraryDB-demo.sql** - Full demo with sample compositions and recordings

```sql
USE musicLibraryDB;
SOURCE src/setup/musicLibraryDB-demo.sql;
```

### Step 3: Configuration

Copy the example configuration file and customize it:

```bash
cd includes/
cp config.example.php config.php
```

Edit `config.php` with your settings. Key options include:

- `ORGNAME`: Short name or acronym for your organization
- `ORGDESC`: Full organization name
- `ORGLOGO`: Path to your logo image
- `ORGMAIL`: Contact email address
- `ORGHOME`: Main site URL (with trailing slash)
- `ORGRECORDINGS`: Public URL for recordings
- `ORGPUBLIC`: Directory for recordings (relative to src/includes)
- `ORGPRIVATE`: Directory for parts/distributions (absolute path, outside web root recommended)
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `DB_CHARSET`: Database connection settings
- `REGION`: Default region/homepage
- `DEBUG`: Set to 1 for verbose error logging

### Step 4: File Permissions and Directories

Ensure the web server can write to upload directories:

```bash
mkdir -p public/files/recordings public/files/parts public/files/distributions
chmod -R 755 public/files/
chown -R www-data:www-data public/files/  # Use your web server user
```

### Step 5: First Login

1. Navigate to your installation URL: `https://yourdomain.com/musicLibraryDB/`
2. Click the login icon (🔒) in the navigation bar
3. Use one of the demo user accounts (available if you imported `musicLibraryDB-demo.sql`):

| Username | Password | Role | Description |
|----------|----------|------|-------------|
| `admin` | `admin123` | Administrator | Full system access, user management |
| `librarian` | `librarian123` | Librarian | Can manage music library content |
| `conductor` | `conductor123` | User | Read-only access for conductors |
| `user` | `user123` | User | Basic read-only access |

4. **Important**: Change the default passwords immediately in a production environment!


## 📊 Database Schema

### Core Tables

**compositions** - The heart of your music library
- Catalog numbers, titles, composers, arrangers
- Difficulty grades, performance notes, storage locations
- Links to genres, ensembles, and paper sizes

**parts** - Individual instrument parts for each composition
- Links parts to compositions and part types
- Tracks physical copies, page counts, and storage

**concerts** - Performance events
- Links to playgrams (concert programs)
- Venue, date, conductor information

**recordings** - Audio/video recordings of performances
- Links recordings to specific concerts and compositions
- Supports file uploads with metadata extraction

**playgrams** - Concert programs/playlists
- Groups compositions into performance programs
- Supports multiple concerts per playgram

### Supporting Tables

- **part_types** - Instrument part definitions (Flute 1, Trumpet 2, etc.)
- **instruments** - Master list of all instruments
- **genres** - Music classifications (March, Jazz, Classical, etc.)
- **ensembles** - Performing groups (Wind Ensemble, Brass Quintet, etc.)
- **paper_sizes** - Physical dimensions of sheet music
- **users** - System users with role-based permissions

## 🎭 Understanding Music Library Concepts

### For Non-Musicians

If you're not familiar with large group music organization, here are key concepts:

**Composition vs. Parts**
- A **composition** is a complete musical work (like "Stars and Stripes Forever")
- **Parts** are individual sheets for each instrument (Flute 1 part, Trumpet 2 part, etc.)
- One composition might have 20-50 different parts for a full band

**Playgrams (Concert Programs)**
- A **playgram** is a playlist of compositions for a concert
- Like a setlist, it defines the order of pieces to be performed
- One playgram can be used for multiple concert performances

**Ensembles and Instrumentation**
- **Ensembles** are different sized groups (Full Band, Brass Quintet, etc.)
- Each composition specifies which ensemble can perform it
- Different ensembles require different instrumental parts

**Grading System**
- Music is graded 1-6 based on difficulty
- Grade 1: Beginner/Elementary
- Grade 6: Professional/Advanced

## 📖 User Guide

### User Roles

**Administrator**
- Full system access
- User management
- System configuration
- Can enable/disable any feature

**Librarian**
- Add/edit compositions, parts, concerts
- Manage recordings and file uploads
- Generate reports
- Most day-to-day operations

**User**
- View-only access to browse the library
- Search compositions and recordings
- Cannot modify data

### Main Functions

#### Managing Compositions
1. **Add New Composition**: Enter title, composer, catalog number, difficulty grade
2. **Set Instrumentation**: Define which parts exist for this piece
3. **Track Physical Location**: Note where sheet music is stored
4. **Performance History**: Record when and where performed

#### Working with Parts
1. **Part Types**: Define instrument parts (Clarinet 1, Horn 2, etc.)
2. **Physical Parts**: Track original vs. copies, page counts
3. **Part Collections**: Group multiple instruments on one part (Percussion 1)
4. **Storage**: Note physical location and paper size

#### Planning Concerts
1. **Create Playgram**: Build a concert program/playlist
2. **Add Compositions**: Select pieces and set performance order
3. **Schedule Concert**: Set date, venue, conductor
4. **Generate Parts List**: See which parts are needed

#### Managing Recordings
1. **Upload Audio Files**: MP3 files with automatic metadata tagging
2. **Link to Concerts**: Connect recordings to specific performances
3. **Organize by Date**: Browse recordings by performance date
4. **Audio Playback**: Built-in player for listening to recordings

### Navigation

**Main Pages:**
- **Home page** - Overview statistics and quick access
- **Search** - Browse and search your library
- **Enter** - Data entry and management (librarians only)
- **Reports** - Generated lists and statistics

**Data Management:**
- **Compositions** - Main music catalog
- **Parts** - Individual instrument parts
- **Concerts** - Performance events
- **Recordings** - Audio/video files
- **Playgrams** - Concert programs

## 🔧 Configuration Options

### Upload Limits
Adjust PHP settings for larger files:
```ini
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
```

## 🛠️ Administration

### User Management
1. Navigate to **Users** (administrators only)
2. Add new users with appropriate roles
3. Users can be: `administrator`, `librarian`, `user`, or combinations

### Backup Procedures
```bash
# Database backup
mysqldump -u musicLibraryDB -p musicLibraryDB > backup_$(date +%Y%m%d).sql

# File backup  
tar -czf files_backup_$(date +%Y%m%d).tar.gz files/
```

### Security Best Practices
1. **Change default passwords** immediately
2. **Use HTTPS** for all access (enforced by `.htaccess`)
3. **Regular backups** of database and files
4. **Limit file upload types** to audio formats and PDFs only
5. **Keep PHP and database updated**
6. **Store parts and recordings outside the web root** and use the secure download handler
7. **Centralized role-based access control** ensures only authorized users can access sensitive features
8. **Protect your parts directory** with an htaccess file if you must store files inside the web root

## 📈 Reporting Features

The system includes several built-in reports:

- **Composition Statistics** - Counts by genre, grade, ensemble
- **Parts Inventory** - Missing parts, copy counts
- **Concert History** - Performance frequency, popular pieces
- **Recording Catalog** - Available recordings by composition
- **Custom Searches** - Filter by any combination of criteria

## 🚫 Troubleshooting

### Common Issues

**"Database connection failed"**
- Check database credentials in `config.php`
- Verify database server is running
- Confirm user permissions

**File upload errors**
- Check directory permissions (755 for directories, 644 for files)
- Verify PHP upload settings
- Ensure adequate disk space

**Audio files not playing**
- Confirm file format is supported (MP3, WAV)
- Check file path configuration
- Verify web server can serve media files

**Permission denied errors**
- Check user roles in database
- Verify session is active
- Confirm role-based access controls (see `public/index.php`)

### Debug Mode
Enable debug mode in `config.php`:
```php
define('DEBUG', 1);
```

This provides detailed error logging to help diagnose issues.

## 🤝 Contributing

We welcome contributions! Please:

1. Fork the repository
2. Create a feature branch
3. Submit a pull request with clear documentation
4. Include tests for new functionality

### Development Setup
```bash
# Enable error reporting for development
define('DEBUG', 1);

# Use a separate development database
define('DB_NAME', 'musicLibraryDB_dev');
```

## 📝 License

This project is licensed under the terms specified in the source files. Please see individual file headers for copyright and licensing information.

## 🙋 Support

- **Documentation**: This README and in-app help
- **Issues**: Report bugs via GitHub issues
- **Discussions**: Community support and feature requests

## 🗺️ Roadmap

Future enhancements may include:

- **API Development** - REST API for external integrations
- **Mobile App** - Native mobile applications
- **Advanced Reporting** - More detailed analytics and exports
- **Digital Scores** - PDF storage and viewing
- **Inventory Management** - Physical music tracking with barcodes
- **Practice Room Integration** - Part checkout system

---

## Quick Start Checklist

- [ ] Install LAMP/LEMP stack
- [ ] Create database and import schema
- [ ] Upload your logo
- [ ] Configure `config.php` with your settings (see new options for secure file storage and tokens)
- [ ] Set file permissions for uploads and storage directories
- [ ] Login with default credentials
- [ ] Change default password
- [ ] Set up supporting data (ensembles, instruments, genres, paper sizes, part types) to fit your organization
- [ ] Add your first composition
- [ ] Create user accounts for your organization
- [ ] Verify email addresses for new users (if enabled)
- [ ] Begin cataloging your music library!

---

*The musicLibraryDB system was designed by and for musicians who understand the unique challenges of managing large music libraries. Whether you're running a community band, school ensemble, or professional organization, this system provides the tools you need to keep your music organized and accessible.*

---

**Key Differences from the Original:**
- All requests are routed through `public/index.php` using a minimal `.htaccess` file.
- Application logic is organized in the `src/` directory, with routing handled in `public/index.php`.
- The project is ready for further modernization (e.g., adding controllers, autoloading, or a lightweight framework).
- The original monolithic structure has been refactored for better maintainability and scalability.

**Migration Note:**
If you are upgrading from the original `musicLibraryDB`, review the new routing and directory structure. Update your Apache configuration to set the document root to the `public/` directory and use the provided `.htaccess` for clean URLs.

---
layout: default
title: "Compositions"
---

# Composition Management

This section covers how to add, edit, and manage musical compositions in AllanaCrusis. Compositions are the core organizational unit of your music library.

## Table of contents
- [Understanding Compositions](#understanding-compositions)
- [Viewing Compositions](#viewing-compositions)
- [Adding New Compositions](#adding-new-compositions)
- [Editing Existing Compositions](#editing-existing-compositions)
- [Composer Name Management](#composer-name-management)
- [Metadata Best Practices](#metadata-best-practices)
- [Bulk Operations](#bulk-operations)

---

## Understanding compositions

### What is a Composition?
A **composition** in AllanaCrusis represents a complete musical work that encompasses all the essential details needed for library management. This includes basic information such as the title, composer, and arranger, along with publishing details like the publisher and catalog number. Performance data is also captured, including grade level, duration, and instrumentation requirements. The system organizes compositions by genre and ensemble assignment, while also tracking physical details such as paper size and page counts for practical library management.


![Composition overview showing relationship to parts](images/images/composition-cover.png)
*Figure 1: Stars and Stripes Forever composition*


### Composition vs. parts
Understanding the relationship between compositions and parts is fundamental to using AllanaCrusis effectively. A composition represents the overall musical work and contains all the metadata about the piece—everything from the composer and title to the grade level and performance duration. Parts, on the other hand, are the conductor's score, and individual instrument parts that belong to that composition, such as the first trumpet part, the clarinet parts, or the percussion parts. This creates a one-to-many relationship where a single composition can have many different parts associated with it, each representing what a specific instrument or section would play during a performance.

![Part overview showing relationship to parts](images/images/composition-part-score.png)
*Figure 2: Stars and Stripes Forever score*

---

## Viewing compositions

### Accessing the list of compositions
1. **Navigate to MATERIALS > Compositions**
2. **View the complete library** of compositions
3. **Use filters and search** to find specific works
4. **Sort by different criteria** (title, composer, date added, etc.)
5. **Choose an action button** to manage composition details

### Compositions table features
- **Select a composition**: Select a composition to edit or delete
- **Links to composition details**: Show details of a composition in the table
- **Real-time filtering**: Type in filter boxes to narrow results
- **Sortable columns**: Click headers to sort by that field
- **Sticky headers**: Column headers stay visible while scrolling
- **Action buttons**: Quick access to edit, view, or manage each composition

![Compositions list with filtering and sorting options](images/screenshots/compositions.png)
*Figure 3: Main compositions list with search and filter capabilities*

### Composition details view
Click on any composition title to see complete metadata:

- **Catalog**, for example, **M001**
- **Composition**, for example, *Stars and Stripes Forever, The*
- **Enabled**, Yes or No
- **Description**, for example, *As performed by the President's Own United States Marine Band*
- **Composer**, for example, "Sousa, John Philip"
- **Arranger**
- **Editor**, for example, "United States Marine Band"
- **Publisher**, for example, "Public Domain"
- **Genre**, for example, "Military march"
- **Ensemble**, for example, "Fourth Wind Wind Ensemble"
- **Grade**, for example, 4.0
- **Last performed**
- **Duration (secs)**, for example, 210
- **Performance notes**, for example, "The U.S. Marine Band score contains extensive performance and historical notes."
- **Listening link**
- **Paper size**, for example, Folio
- **Picture**
- **windrep.org link**, for example, https://www.windrep.org/Stars_and_Stripes_Forever,_The_(1896)
- **Record updated**, for example, 2020-07-04 10:05:27

---

## Adding new compositions

### Perequisites
- **Librarian or Manager role** required
- **Supporting data configured**: Genres, ensembles, publishers
- **Catalog numbering system** understood

### step-by-Step Process

#### 1. Start the Add Process
1. **Go to MATERIALS > Compositions**
2. **Click "Add New Composition"**
3. **The composition form opens**

#### 2. Required Fields
Fill in the essential information:

**Catalog Number**
- Format: Letter + 3 digits (C###, M###, X###)
- Must be unique in the system
- Use organizational numbering scheme

**Title**
- Complete composition title
- Articles at the end: "Liberty Bell, The"
- Include subtitles if relevant

**Composer**
- Use "Last, First" format: "Beethoven, Ludwig van"
- Autocomplete will suggest existing composers
- Required field - use "Traditional" or "(n/a)" if unknown

![Add composition form showing required fields](images/screenshots/compositions-add-edit.png)
*Figure 4: New composition form with required fields highlighted*

#### 3. Optional but Important Fields

**Arranger/Editor**
- Use same "Last, First" format as composer
- Leave blank if not applicable
- Autocomplete available

**Publisher**
- Choose from existing list or add new
- Abbreviate common words: "Hal Leonard" not "Hal Leonard Corporation"

**Genre**
- Select most appropriate category
- Default to "Wind Ensemble" if uncertain
- Affects search and organization

**Ensemble**
- Primary performing group for this composition
- Usually set by your organization

![Composition form showing optional fields](images/screenshots/compositions-add-edit-b.png)
*Figure 5: Optional fields section of the composition form*

#### 4. Performance Information

**Grade Level**
- Difficulty rating (1-7 scale typically)
- Check score or Wind Repertory Project for guidance
- Leave blank if unknown

**Duration**
- Performance time in minutes
- Can be approximate
- Helpful for concert planning

**Paper Size**
- Most band music: Folio (9x12)
- Some music: Letter (8.5x11)
- Marching band: Often special sizes

#### 5. Save the Composition
1. **Review all information** for accuracy
2. **Click "Add Composition"**
3. **Confirmation message** appears
4. **Composition is now in the system**

---

## Editing existing compositions

### when to Edit Compositions
- **Correcting errors** in metadata
- **Adding missing information** (grade level, duration)
- **Updating publisher information**
- **Changing organizational assignments**

### editing Process
1. **Find the composition** using search or browse
2. **Click the blue "Edit" button**
3. **Make necessary changes**
4. **Click "Update"** to save changes

![Edit composition form with existing data](images/screenshots/compositions-add-edit.png)
*Figure 6: Editing an existing composition with pre-filled data*

### common Edits
- **Title corrections**: Fix spelling or formatting
- **Composer standardization**: Ensure consistent naming
- **Missing metadata**: Add grade level or duration
- **Genre reclassification**: Move to more appropriate category

---

## Composer name management

### importance of Consistent Naming
- **Search effectiveness**: Users can find all works by a composer
- **Reporting accuracy**: Statistics and analysis work properly
- **Professional appearance**: Library looks organized and authoritative

### composer Normalization Features
AllanaCrusis includes tools to help maintain consistent composer names:

#### Autocomplete System
- **Start typing** a composer name
- **Suggestions appear** based on existing entries
- **Select from list** to ensure consistency
- **Automatic formatting** to "Last, First" format

![Composer autocomplete showing suggestions](images/screenshots/compositions-add-edit.png)
*Figure 7: Composer autocomplete suggesting existing entries*

#### Name Formatting Rules
- **Standard format**: "Last, First Middle"
- **Multiple names**: "Beethoven, Ludwig van"
- **Titles and suffixes**: "Bach, Johann Sebastian"
- **Single names**: "Palestrina" (historical figures)

#### Special Cases
- **Traditional works**: Use "Traditional"
- **Unknown composer**: Use "(n/a)"
- **Multiple composers**: List primary composer, note others in arranger field
- **Pseudonyms**: Use most recognized form, note real name in description

### bulk Composer Updates
For managers, tools may be available to:
- **Standardize existing names** across the library
- **Merge duplicate entries** with slight variations
- **Apply formatting rules** to all composer names
- **Generate reports** on naming inconsistencies

---

## Metadata best practices

### title Guidelines
- **Complete titles**: Include full title and subtitle
- **Article placement**: Move articles to end with comma
- **Capitalization**: Follow standard title case rules
- **Punctuation**: Be consistent with periods, commas

### publisher Information
- **Standard abbreviations**: Use consistent shortened forms
- **Current publishers**: Update if companies merge or change
- **Historical accuracy**: Keep original publisher for historical works
- **Multiple editions**: Note which edition you have

### genre Classification
- **Primary purpose**: Choose based on intended use
- **Consistent categories**: Use established genre list
- **Special collections**: Create genres for unique organizational needs
- **Regular review**: Update genres as collection evolves

![Metadata best practices checklist](images/screenshots/metadata-checklist.png)
*Figure 8: Checklist for ensuring quality metadata entry*

---

## Bulk operations

### when to Use Bulk Operations
- **Large imports** from other systems
- **Systematic corrections** across multiple compositions
- **Organizational changes** (ensemble reassignments, genre updates)
- **Publisher updates** due to mergers or acquisitions

### available Bulk Functions
Depending on your role and system configuration:

#### Export Functions
- **CSV export** of composition data
- **Filtered exports** based on search criteria
- **Custom field selection** for specific reports

#### Import Functions
- **CSV import** of new compositions
- **Update existing** compositions via import
- **Validation tools** to check data before import

#### Batch Updates
- **Genre reassignment** for multiple works
- **Publisher standardization** across collections
- **Composer name normalization** in bulk

![Bulk operations interface](images/screenshots/bulk-operations.png)
*Figure 9: Bulk operations tools for managing multiple compositions*

### import Guidelines
When importing composition data:
1. **Prepare clean data** in required format
2. **Test with small batch** first
3. **Validate required fields** are present
4. **Check for duplicates** before importing
5. **Review results** after import completion

---

## Search and discovery

### making Compositions Findable
Good metadata entry ensures users can find compositions through:

#### Search Methods
- **Title searches**: Partial or complete titles
- **Composer searches**: Various name formats
- **Catalog number lookup**: Direct access via number
- **Genre browsing**: Category-based discovery
- **Advanced filtering**: Multiple criteria combined

#### Search Optimization Tips
- **Complete information**: Fill in all available fields
- **Standard terminology**: Use consistent language
- **Alternative names**: Note variations in description fields
- **Tags and keywords**: Use description for searchable terms

![Search results showing well-organized compositions](images/screenshots/search-results.png)
*Figure 10: Search results demonstrating effective metadata organization*

---

## Quality control

### regular Maintenance Tasks
- **Review new entries** for completeness and accuracy
- **Standardize naming** across the collection
- **Update missing information** as it becomes available
- **Clean up duplicates** or near-duplicates

### validation Checks
- **Required fields**: Ensure all essential data is present
- **Format consistency**: Check catalog numbers, names, titles
- **Logical relationships**: Verify ensemble assignments make sense
- **External verification**: Cross-check with authoritative sources

### collaboration Tools
- **Review queues**: Flag compositions needing attention
- **Change logging**: Track who made what changes when
- **Discussion notes**: Communicate about questionable entries
- **Approval workflows**: Require review for certain changes

---

## Integration with other functions

### connection to Parts Management
- **Automatic relationships**: Parts link to their composition
- **Inherited metadata**: Parts get information from composition
- **Instrumentation tracking**: Composition shows what parts exist

### performance Tracking
- **Concert programming**: Link compositions to performances
- **Usage statistics**: Track which works are performed most
- **Planning tools**: Use metadata for concert planning

### reporting and Analysis
- **Collection statistics**: Analyze by composer, genre, grade level
- **Acquisition planning**: Identify gaps in collection
- **Usage reports**: See which compositions are accessed most

---

## Troubleshooting common issues

### duplicate Compositions
**Problem**: Same composition entered multiple times
**Solution**: 
- Use search before adding new compositions
- Check alternate titles and spellings
- Merge or remove duplicates as appropriate

### missing Information
**Problem**: Incomplete metadata affecting usefulness
**Solution**:
- Research using Wind Repertory Project or other sources
- Check physical scores for missing information
- Flag for later completion if information unavailable

### naming Inconsistencies
**Problem**: Same composer with different name formats
**Solution**:
- Use autocomplete to maintain consistency
- Run periodic reports to identify variations
- Standardize using bulk update tools

![Troubleshooting guide for common composition issues](images/screenshots/composition-troubleshooting.png)
*Figure 11: Common issues and their solutions*

---

## Next steps

With compositions properly managed:

1. **[Learn Parts Management](parts.html)** - Handle individual instrument parts
2. **[Explore Concert Tracking](concerts-recordings.html)** - Connect compositions to performances
3. **[Set up Distribution](distribution.html)** - Share compositions with users

---

*Continue to the next section: [Parts Management](parts.html) to learn about managing individual instrument parts and files.*
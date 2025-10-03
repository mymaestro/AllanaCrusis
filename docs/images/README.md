# Image Guidelines for AllanaCrusis Documentation

## Directory Structure
```
docs/images/
├── screenshots/     # General application screenshots
├── ui/             # User interface elements and components
├── tutorials/      # Step-by-step tutorial images
└── logos/          # Logos and branding (if needed)
```

## Image Requirements

### File Formats
- **PNG**: Preferred for screenshots (lossless, supports transparency)
- **JPG**: For photos or images with many colors
- **SVG**: For icons and simple graphics (scalable)

### Naming Convention
Use descriptive, kebab-case names:
- `compositions-search-interface.png`
- `user-login-screen.png`
- `parts-management-table.png`
- `composer-autocomplete-example.png`

### Image Sizes
- **Screenshots**: Capture at standard resolution (1920x1080 or similar)
- **UI Elements**: Crop to show relevant portions
- **Thumbnails**: Resize large images to ~800px width for web display

### Best Practices
1. **Consistent Browser**: Use the same browser for all screenshots
2. **Clean Interface**: Clear any personal data, use demo data
3. **Highlighting**: Use arrows, boxes, or highlights to point out important features
4. **Alt Text**: Always include descriptive alt text for accessibility

## Adding Images to Documentation

### Basic Image Syntax
```markdown
![Alt text description](images/screenshots/filename.png)
```

### Image with Caption
```markdown
![Login screen showing username and password fields](images/screenshots/login-screen.png)
*Figure 1: AllanaCrusis login interface*
```

### Responsive Images
```markdown
![Dashboard overview](images/screenshots/dashboard.png){: style="max-width: 100%; height: auto;"}
```

### Images with Links
```markdown
[![Thumbnail](images/screenshots/thumbnail.png)](images/screenshots/full-size.png)
```

## Tools for Screenshots

### Recommended Tools
- **macOS**: Command+Shift+4 (built-in)
- **Windows**: Snipping Tool or Snip & Sketch
- **Linux**: GNOME Screenshot, Flameshot, or Shutter
- **Browser Extensions**: Full Page Screen Capture, Awesome Screenshot

### Editing Tools
- **Simple Editing**: Built-in preview tools
- **Advanced Editing**: GIMP, Photoshop, or online tools like Canva
- **Annotations**: Draw.io, Skitch, or built-in annotation tools

## Accessibility
- Always include meaningful alt text
- Ensure good contrast in annotated images
- Provide text descriptions for complex diagrams
- Consider users with visual impairments

## File Management
- Keep original high-resolution versions
- Optimize web versions for faster loading
- Use version control for image updates
- Regular cleanup of unused images
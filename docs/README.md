# AllanaCrusis Documentation Site

This directory contains the GitHub Pages documentation site for AllanaCrusis Music Library System.

## Setup Instructions

### 1. Enable GitHub Pages
1. Go to your repository settings on GitHub
2. Navigate to "Pages" in the left sidebar
3. Under "Source", select "Deploy from a branch"
4. Choose "main" branch and "/docs" folder
5. Click "Save"

### 2. Configure Your Site
Edit `_config.yml` and update these values:
- `baseurl`: Change to your repository name (e.g., "/AllanaCrusis")
- `url`: Change to your GitHub Pages URL (e.g., "https://yourusername.github.io")
- `author.name`: Your name or organization
- `author.email`: Contact email

### 3. Custom Domain (Optional)
If you want to use a custom domain:
1. Add a `CNAME` file to the `/docs` folder with your domain name
2. Configure DNS settings with your domain provider
3. Update the `url` in `_config.yml` to your custom domain

## Local Development

To run the site locally for testing:

```bash
# Navigate to the docs directory
cd docs

# Install dependencies (first time only)
bundle install

# Serve the site locally
bundle exec jekyll serve

# Open http://localhost:4000 in your browser
```

## File Structure

```
docs/
├── _config.yml          # Jekyll configuration
├── Gemfile              # Ruby dependencies
├── index.md             # Homepage
├── user-guide.md        # Complete user documentation
├── api-docs.md          # API documentation (placeholder)
└── README.md            # This file
```

## Updating Documentation

### Adding New Pages
1. Create a new `.md` file in the `/docs` directory
2. Add front matter at the top:
   ```yaml
   ---
   layout: default
   title: "Page Title"
   ---
   ```
3. Add the page to navigation in `_config.yml` if needed

### Editing Existing Content
- Edit the `.md` files directly
- Changes will be automatically deployed when pushed to GitHub
- For local testing, run `bundle exec jekyll serve`

## Themes and Customization

The site uses the "minima" theme by default. To customize:

1. **Override layouts**: Create `_layouts/` directory and add custom layouts
2. **Add CSS**: Create `assets/css/style.scss` for custom styles
3. **Change theme**: Update the `theme` setting in `_config.yml`

Popular Jekyll themes for documentation:
- [minima](https://github.com/jekyll/minima) (current)
- [just-the-docs](https://github.com/pmarsceill/just-the-docs)
- [cayman](https://github.com/pages-themes/cayman)
- [architect](https://github.com/pages-themes/architect)

## Maintenance

### Regular Updates
- Keep user guide synchronized with application changes
- Update version information and feature lists
- Review and update external links
- Add new troubleshooting information as needed

### Monitoring
- Check GitHub Pages build status in repository settings
- Monitor site analytics if configured
- Review user feedback and update documentation accordingly

## Support

For questions about the documentation site setup or content, contact your system administrator or check the main AllanaCrusis repository issues.
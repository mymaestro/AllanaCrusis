// Composer Name Normalization Functions
// Add to compositions.php to help standardize composer names

// Function to normalize composer names to "Last, First Middle" format
function normalizeComposerName(name) {
    if (!name || name.trim() === '') return name;
    
    name = name.trim();
    
    // If already in "Last, First" format, return as-is
    if (name.includes(',')) {
        return name;
    }
    
    // Common patterns to handle
    const patterns = [
        // "John Philip Sousa" -> "Sousa, John Philip"
        /^([A-Z][a-z]+(?:\s[A-Z][a-z]+)*)\s([A-Z][a-z]+)$/,
        // "J. P. Sousa" -> "Sousa, J. P."
        /^([A-Z]\.?\s*[A-Z]\.?)\s([A-Z][a-z]+)$/,
        // "J P Sousa" -> "Sousa, J P"
        /^([A-Z]\s[A-Z])\s([A-Z][a-z]+)$/
    ];
    
    for (let pattern of patterns) {
        const match = name.match(pattern);
        if (match) {
            return `${match[2]}, ${match[1]}`;
        }
    }
    
    // If no pattern matches, return original
    return name;
}

// Function to suggest composer names from existing database
async function suggestComposerNames(query, limit = 10) {
    if (query.length < 2) return [];
    
    try {
        const response = await fetch('index.php?action=search_composers', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ query, limit })
        });
        
        if (response.ok) {
            return await response.json();
        }
    } catch (error) {
        console.error('Error fetching composer suggestions:', error);
    }
    
    return [];
}

// Autocomplete setup for composer field
function setupComposerAutocomplete() {
    const composerInput = document.getElementById('composer');
    if (!composerInput) return;
    
    let suggestionsDiv = document.createElement('div');
    suggestionsDiv.className = 'composer-suggestions';
    suggestionsDiv.style.cssText = `
        position: absolute;
        background: white;
        border: 1px solid #ddd;
        border-top: none;
        max-height: 200px;
        overflow-y: auto;
        width: 100%;
        z-index: 1000;
        display: none;
    `;
    
    composerInput.parentNode.style.position = 'relative';
    composerInput.parentNode.appendChild(suggestionsDiv);
    
    let timeout;
    composerInput.addEventListener('input', function() {
        clearTimeout(timeout);
        const query = this.value.trim();
        
        if (query.length < 2) {
            suggestionsDiv.style.display = 'none';
            return;
        }
        
        timeout = setTimeout(async () => {
            const suggestions = await suggestComposerNames(query);
            displaySuggestions(suggestions, suggestionsDiv, composerInput);
        }, 300);
    });
    
    // Normalize on blur
    composerInput.addEventListener('blur', function() {
        setTimeout(() => {
            if (this.value.trim()) {
                this.value = normalizeComposerName(this.value.trim());
            }
            suggestionsDiv.style.display = 'none';
        }, 200); // Delay to allow clicking suggestions
    });
}

function displaySuggestions(suggestions, container, input) {
    container.innerHTML = '';
    
    if (suggestions.length === 0) {
        container.style.display = 'none';
        return;
    }
    
    suggestions.forEach(suggestion => {
        const div = document.createElement('div');
        div.style.cssText = 'padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #eee;';
        div.textContent = suggestion.name_display || suggestion.composer;
        
        div.addEventListener('mouseenter', () => {
            div.style.backgroundColor = '#f0f0f0';
        });
        
        div.addEventListener('mouseleave', () => {
            div.style.backgroundColor = '';
        });
        
        div.addEventListener('click', () => {
            input.value = suggestion.name_display || suggestion.composer;
            container.style.display = 'none';
            input.focus();
        });
        
        container.appendChild(div);
    });
    
    container.style.display = 'block';
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', setupComposerAutocomplete);
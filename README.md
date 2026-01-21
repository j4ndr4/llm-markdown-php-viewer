# LLM Markdown Viewer

A PHP-based application that converts LLM-specific markdown syntax to HTML for easy viewing in a browser.

## Features

- Converts standard markdown syntax to HTML
- Properly renders headers (h1, h2, h3, etc.)
- Bold text parsing (**text** or __text__)
- Italic text parsing (*text* or _text_)
- Improved list formatting with proper nesting based on indentation
- Preserves ordered list numbering using the `value` attribute
- Table parsing and responsive styling
- Blockquote parsing and styling
- Horizontal rule parsing (---, ***, ___) and styling
- Handles LLM-specific syntax elements:
  - Function calls: `[FUNCTION_CALL: ...]`
  - Code suggestions: `[CODE_SUGGESTION: ...]`
  - Thought processes: `[THOUGHT: ...]`
- Syntax highlighting for code blocks
- Responsive design for different screen sizes
- Copy buttons for code blocks

## Installation

1. Clone or download this repository
2. Place the files in your web server directory
3. Ensure your server supports PHP 7.0 or higher

## Usage

1. Access the application through your web browser
2. Enter the path to a markdown file in the input field
3. Click "Load File" to view the converted HTML

Alternatively, you can directly access a file by appending the file parameter to the URL:
```
index.php?file=path/to/your/markdown/file.md
```

For browsing multiple markdown files in a directory, use the selector.php script:
1. Access `selector.php` through your web browser
2. Enter the path to a folder containing markdown files
3. Click "Show Directory Tree" to view a hierarchical tree of all markdown files
4. Click on any markdown file to open it in the viewer

## Sample File

The project includes a sample LLM response file (`sample-llm-response.md`) demonstrating various syntax elements.

## Customization

You can customize the appearance by modifying the CSS file in the `css/` directory.
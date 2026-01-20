<?php
/**
 * LLM Markdown Viewer
 * Converts LLM-specific markdown syntax to HTML
 */

class LlmMarkdownParser 
{
    /**
     * Parse LLM markdown syntax and convert to HTML
     */
    public function parse($markdown)
    {
        // Ensure markdown is a string
        $markdown = $markdown ?? '';

        // Convert headers
        $markdown = preg_replace_callback('/^(#{1,6})\s+(.*)$/m', function($matches) {
            $level = strlen($matches[1]); // Count the number of # symbols
            return '<h' . $level . '>' . $matches[2] . '</h' . $level . '>';
        }, $markdown);
        
        // Handle horizontal rules first to avoid conflicts with emphasis
        $markdown = $this->parseHorizontalRules($markdown);

        // Handle code blocks (properly match code blocks with language specification) - do this BEFORE inline code
        if ($markdown !== null) {
            // Use a pattern that properly handles multiple code blocks in sequence
            $markdown = preg_replace_callback('/^```(\w+)?[\n\r]+((?:(?!^[\t ]*```[\n\r]).)*?)[\n\r]*^[\t ]*```[\n\r]*/sm', function($matches) {
                $language = !empty($matches[1]) ? $matches[1] : '';
                $code = htmlspecialchars($matches[2]);
                if (!empty($language)) {
                    return "<pre><code class=\"language-$language\">" . trim($code) . "</code></pre>";
                } else {
                    return "<pre><code>" . trim($code) . "</code></pre>";
                }
            }, $markdown);
        }

        // Handle bold and italic
        // Bold: **text** or __text__
        $markdown = $markdown !== null ? preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $markdown) : '';
        $markdown = $markdown !== null ? preg_replace('/__(.*?)__/', '<strong>$1</strong>', $markdown) : '';
        // Italic: *text* or _text_
        $markdown = $markdown !== null ? preg_replace('/\*(.*?)\*/', '<em>$1</em>', $markdown) : '';
        $markdown = $markdown !== null ? preg_replace('/_(.*?)_/', '<em>$1</em>', $markdown) : '';

        // Handle inline code (after code blocks to avoid conflicts)
        $markdown = $markdown !== null ? preg_replace('/`(.*?)`/', '<code>$1</code>', $markdown) : '';

        // Handle images (with optional title) - do this BEFORE links to avoid conflicts
        $markdown = $markdown !== null ? preg_replace_callback('/!\[([^\]]*)\]\(([^)\s]+)(?:\s+"([^"]*)")?\)/', function($matches) {
            $alt = $matches[1];
            $src = $matches[2];
            $title = isset($matches[3]) && !empty($matches[3]) ? $matches[3] : '';

            if (!empty($title)) {
                return '<img src="' . htmlspecialchars($src) . '" alt="' . htmlspecialchars($alt) . '" title="' . htmlspecialchars($title) . '">';
            } else {
                return '<img src="' . htmlspecialchars($src) . '" alt="' . htmlspecialchars($alt) . '">';
            }
        }, $markdown) : '';

        // Handle links
        $markdown = $markdown !== null ? preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $markdown) : '';

        // Handle tables
        $markdown = $this->parseTables($markdown);

        // Handle blockquotes
        $markdown = $this->parseBlockquotes($markdown);

        // Handle lists - improved logic to handle nested lists based on indentation and preserve ordered list numbers
        // First, let's parse the lines to identify all list items and their hierarchy
        $lines = explode("\n", $markdown);
        $processedLines = [];
        $listItems = [];

        foreach ($lines as $line) {
            $originalLine = $line;
            $indentLevel = 0;

            // Calculate indentation level (count spaces/tabs at the beginning)
            $leadingWhitespace = strlen($originalLine) - strlen(ltrim($originalLine, " \t"));
            $indentLevel = floor($leadingWhitespace / 2); // Using 2 spaces as 1 indent level

            $trimmedLine = ltrim($originalLine);

            // Check for unordered list item
            if (preg_match('/^-\s+(.+)$/', $trimmedLine, $matches)) {
                $listItems[] = [
                    'type' => 'ul',
                    'content' => $matches[1],
                    'indent' => $indentLevel,
                    'original' => $originalLine,
                    'number' => null  // For unordered lists
                ];
            }
            // Check for ordered list item
            elseif (preg_match('/^(\d+)\.\s+(.+)$/', $trimmedLine, $matches)) {
                $listItems[] = [
                    'type' => 'ol',
                    'content' => $matches[2],
                    'indent' => $indentLevel,
                    'original' => $originalLine,
                    'number' => $matches[1]  // For ordered lists
                ];
            }
            // Not a list item
            else {
                // Process any accumulated list items before adding non-list content
                if (!empty($listItems)) {
                    $processedLines[] = $this->processNestedLists($listItems);
                    $listItems = [];
                }
                $processedLines[] = $originalLine;
            }
        }

        // Process any remaining list items
        if (!empty($listItems)) {
            $processedLines[] = $this->processNestedLists($listItems);
        }

        $markdown = implode("\n", $processedLines);
        
        // Handle paragraphs
        $markdown = $markdown ?? '';
        $paragraphs = explode("\n\n", $markdown);
        $result = '';
        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if (!empty($paragraph) && !preg_match('/^<(h[1-6]|ul|ol|li|pre|blockquote|hr|img)>/', $paragraph)) {
                $paragraph = '<p>' . $paragraph . '</p>';
            }
            $result .= $paragraph . "\n\n";
        }

        // Clean up extra newlines
        $result = preg_replace('/\n\s*\n/', "\n", $result);

        return $result;
    }
    
    /**
     * Process nested lists based on indentation
     */
    private function processNestedLists($listItems) {
        if (empty($listItems)) {
            return '';
        }

        // Find minimum indentation level
        $minIndent = min(array_column($listItems, 'indent'));

        // Build the nested structure starting from the minimum level
        $result = $this->buildNestedList($listItems, $minIndent, 0);

        return $result;
    }

    /**
     * Recursively build nested list structure
     */
    private function buildNestedList($items, $currentLevel, $startIndex) {
        $result = '';
        $i = $startIndex;

        // Determine the type of the outermost list
        $outerType = 'ul'; // Default to unordered
        foreach ($items as $item) {
            if ($item['indent'] == $currentLevel) {
                $outerType = $item['type'];
                break;
            }
        }

        // Start the outer list
        $result .= str_repeat("  ", $currentLevel) . '<' . $outerType . ">\n";

        while ($i < count($items)) {
            $item = $items[$i];

            if ($item['indent'] < $currentLevel) {
                // This item belongs to a parent level, so stop processing here
                break;
            } elseif ($item['indent'] == $currentLevel) {
                // This is an item at the current level
                if ($item['type'] == 'ul') {
                    $result .= str_repeat("  ", $currentLevel + 1) . '  <li>' . $item['content'];
                } else {
                    $result .= str_repeat("  ", $currentLevel + 1) . '  <li value="' . $item['number'] . '">' . $item['content'];
                }

                // Check if next items are at a deeper level (nested)
                if ($i + 1 < count($items) && $items[$i + 1]['indent'] > $currentLevel) {
                    // Find all nested items
                    $nestedStart = $i + 1;
                    $nestedEnd = $nestedStart;

                    while ($nestedEnd < count($items) && $items[$nestedEnd]['indent'] > $currentLevel) {
                        $nestedEnd++;
                    }

                    // Process the nested items
                    $nestedItems = array_slice($items, $nestedStart, $nestedEnd - $nestedStart);
                    $nestedResult = $this->buildNestedList($nestedItems, $currentLevel + 1, 0);
                    $result .= "\n" . $nestedResult;

                    // Skip the processed nested items
                    $i = $nestedEnd - 1;
                }

                $result .= "</li>\n";
            } else {
                // This shouldn't happen in a properly structured list, but just in case
                break;
            }

            $i++;
        }

        // Close the outer list
        $result .= str_repeat("  ", $currentLevel) . '</' . $outerType . '>';

        return $result;
    }
    
    /**
     * Parse tables in markdown
     */
    private function parseTables($markdown)
    {
        $markdown = $markdown ?? '';
        $lines = explode("\n", $markdown);
        $newLines = [];
        $inTable = false;
        $tableRows = [];

        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];

            // Check if this line is a table row (contains | and at least one :- or -:)
            if (preg_match('/^\|.*\|$/', trim($line))) {
                if (!$inTable) {
                    // Starting a new table
                    $inTable = true;
                    $tableRows = [];
                }
                $tableRows[] = trim($line);
            } else {
                if ($inTable) {
                    // End of table, process it
                    $newLines[] = $this->convertTableToHtml($tableRows);
                    $inTable = false;
                    $tableRows = [];
                }
                $newLines[] = $line;
            }
        }

        // Handle table at the end of content
        if ($inTable && !empty($tableRows)) {
            $newLines[] = $this->convertTableToHtml($tableRows);
        }

        return implode("\n", $newLines);
    }

    /**
     * Convert markdown table rows to HTML table
     */
    private function convertTableToHtml($rows)
    {
        if (empty($rows)) {
            return '';
        }

        $html = "<table>\n";

        foreach ($rows as $index => $row) {
            $row = $row ?? '';
            $cells = array_map('trim', explode('|', trim($row, '|')));

            // Skip the separator row (usually second row with :- stuff)
            if ($index == 1 && $this->isSeparatorRow($row)) {
                continue;
            }

            if ($index == 0) {
                $html .= "  <thead>\n    <tr>\n";
                foreach ($cells as $cell) {
                    $html .= "      <th>" . htmlspecialchars($cell ?? '') . "</th>\n";
                }
                $html .= "    </tr>\n  </thead>\n  <tbody>\n";
            } else {
                $html .= "    <tr>\n";
                foreach ($cells as $cell) {
                    $html .= "      <td>" . htmlspecialchars($cell ?? '') . "</td>\n";
                }
                $html .= "    </tr>\n";
            }
        }

        $html .= "  </tbody>\n</table>\n";
        return $html;
    }

    /**
     * Check if a row is a separator row (contains :- or -: or --)
     */
    private function isSeparatorRow($row)
    {
        $row = $row ?? '';
        $cells = array_map('trim', explode('|', trim($row, '|')));
        $separatorPattern = '/^:?-+:?$/'; // Matches patterns like -, --, :-, -:, :-:, etc.

        $separatorCells = 0;
        foreach ($cells as $cell) {
            if (preg_match($separatorPattern, $cell ?? '')) {
                $separatorCells++;
            }
        }

        return $separatorCells > 0 && $separatorCells == count($cells);
    }

    /**
     * Parse blockquotes in markdown
     */
    private function parseBlockquotes($markdown)
    {
        $markdown = $markdown ?? '';
        $lines = explode("\n", $markdown);
        $newLines = [];
        $inBlockquote = false;
        $blockquoteContent = [];

        foreach ($lines as $line) {
            // Check if this line starts with >
            if (preg_match('/^>\s*(.*)/', $line, $matches)) {
                if (!$inBlockquote) {
                    // Starting a new blockquote
                    $inBlockquote = true;
                    $blockquoteContent = [];
                }
                $blockquoteContent[] = $matches[1]; // Get the content after the >
            } else {
                if ($inBlockquote) {
                    // End of blockquote, process it
                    $newLines[] = '<blockquote>' . implode('<br>', $blockquoteContent) . '</blockquote>';
                    $inBlockquote = false;
                    $blockquoteContent = [];
                }
                $newLines[] = $line;
            }
        }

        // Handle blockquote at the end of content
        if ($inBlockquote && !empty($blockquoteContent)) {
            $newLines[] = '<blockquote>' . implode('<br>', $blockquoteContent) . '</blockquote>';
        }

        return implode("\n", $newLines);
    }

    /**
     * Parse horizontal rules in markdown
     */
    private function parseHorizontalRules($markdown)
    {
        $markdown = $markdown ?? '';
        // Match horizontal rules: ---, ***, or ___ (with optional spaces between characters)
        // This pattern matches lines that consist of 3 or more of the same character (-, *, _) with optional spaces
        $markdown = preg_replace('/^(\s*)([-*_])(\s*\2){2,}\s*$/m', '$1<hr>', $markdown);
        return $markdown;
    }

    /**
     * Parse LLM-specific syntax elements
     */
    public function parseLlmSyntax($content)
    {
        $content = $content ?? '';
        // Handle LLM-specific syntax like function calls, code suggestions, etc.
        $content = $this->parseFunctionCalls($content);
        $content = $this->parseCodeSuggestions($content);
        $content = $this->parseThoughtBlocks($content);

        return $content;
    }
    
    /**
     * Parse function calls in LLM responses
     */
    private function parseFunctionCalls($content)
    {
        $content = $content ?? '';
        // Look for function call patterns like [FUNCTION_CALL: ...]
        $content = preg_replace('/\[FUNCTION_CALL:\s*(.*?)\]/', '<div class="function-call"><strong>Function Call:</strong> $1</div>', $content);
        return $content;
    }
    
    /**
     * Parse code suggestions
     */
    private function parseCodeSuggestions($content)
    {
        $content = $content ?? '';
        // Look for code suggestion patterns
        $content = preg_replace('/\[CODE_SUGGESTION:\s*(.*?)\]/', '<div class="code-suggestion"><strong>Code Suggestion:</strong> $1</div>', $content);
        return $content;
    }
    
    /**
     * Parse thought blocks
     */
    private function parseThoughtBlocks($content)
    {
        $content = $content ?? '';
        // Look for thought block patterns
        $content = preg_replace('/\[THOUGHT:\s*(.*?)\]/s', '<details class="thought-block"><summary>Thinking Process</summary><p>$1</p></details>', $content);
        return $content;
    }
}

// Main execution (only run if accessed as a web page)
if (php_sapi_name() !== 'cli') {
    $parser = new LlmMarkdownParser();
    $markdownContent = '';

    // Check if a file is provided via GET parameter
    if (isset($_GET['file']) && !empty($_GET['file'])) {
        $filePath = $_GET['file'];
        if (file_exists($filePath) && pathinfo($filePath, PATHINFO_EXTENSION) === 'md') {
            $markdownContent = file_get_contents($filePath);
        } else {
            $markdownContent = "# Error\nFile not found or invalid file type.";
        }
    } else {
        // Default markdown content
        $markdownContent = "# Welcome to LLM Markdown Viewer\n\nThis viewer converts LLM-specific markdown syntax to HTML.\n\n## Features\n- Standard markdown support\n- LLM-specific syntax highlighting\n- Code block formatting\n\n### Example Code Block\n```\nfunction helloWorld() {\n  console.log('Hello, world!');\n}\n```";
    }

    // Parse the markdown content
    $htmlContent = $parser->parse($markdownContent ?? '');
    $htmlContent = $parser->parseLlmSyntax($htmlContent ?? '');

    // Sanitize output
    $htmlContent = htmlspecialchars_decode($htmlContent ?? '');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LLM Markdown Viewer</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/themes/prism.min.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>LLM Markdown Viewer</h1>
            <p>Converts LLM-specific markdown syntax to HTML</p>
        </header>
        
        <main>
            <div class="input-section">
                <form method="get">
                    <label for="file">Load Markdown File:</label>
                    <input type="text" id="file" name="file" placeholder="Enter path to .md file" value="<?php echo isset($_GET['file']) ? htmlspecialchars($_GET['file']) : ''; ?>">
                    <button type="submit">Load File</button>
                </form>
            </div>
            
            <div class="output-section">
                <h2>Rendered Output</h2>
                <div class="markdown-output">
                    <?php echo $htmlContent; ?>
                </div>
            </div>
        </main>
        
        <footer>
            <p>LLM Markdown Viewer &copy; <?php echo date('Y'); ?></p>
        </footer>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/components/prism-core.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/plugins/autoloader/prism-autoloader.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
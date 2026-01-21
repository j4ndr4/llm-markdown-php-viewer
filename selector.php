<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Markdown File Selector</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }

        form {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: #555;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 15px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        h2 {
            color: #333;
            margin-top: 30px;
        }

        pre {
            background-color: #f4f4f4;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            font-size: 14px;
            line-height: 1.4;
        }

        .tree {
            background-color: #f4f4f4;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            font-size: 14px;
            line-height: 1.8;
        }

        .tree ul {
            list-style-type: none;
            margin: 0;
            padding-left: 20px;
        }

        .tree li {
            margin: 5px 0;
            position: relative;
        }

        .tree a {
            color: #1a73e8;
            text-decoration: none;
            display: inline-block;
            padding: 2px 0;
        }

        .tree a:hover {
            text-decoration: underline;
        }

        .folder {
            font-weight: bold;
            color: #333;
            cursor: pointer;
            user-select: none;
        }

        .folder::before {
            content: "📁 ";
        }

        .folder.collapsed::before {
            content: "📂 ";
        }

        .file {
            color: #555;
        }

        .file::before {
            content: "📄 ";
        }

        .md-file::before {
            content: "📝 ";
        }

        .no-files {
            color: #666;
            font-style: italic;
        }

        .error {
            color: #d32f2f;
            background-color: #ffebee;
            padding: 10px;
            border-radius: 4px;
            border-left: 4px solid #d32f2f;
        }

        .controls {
            margin: 15px 0;
            text-align: center;
        }

        .controls button {
            background-color: #008CBA;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin: 0 5px;
        }

        .controls button:hover {
            background-color: #007B9A;
        }

        .collapsible-content {
            display: block;
        }

        .collapsed + .collapsible-content {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Select Folder to Find Markdown Files</h1>

        <form method="GET">
            <label for="folder">Enter Folder Path:</label>
            <input type="text" id="folder" name="folder" placeholder="Enter path to folder" value="<?php echo isset($_GET['folder']) ? htmlspecialchars($_GET['folder']) : ''; ?>" />
            <input type="submit" value="Show Directory Tree" />
        </form>

        <?php
        // selector.php
        // This script creates a tree view of the directory structure and highlights markdown files

        function scanDirectoryTree($dir, $basePath = '') {
            $result = [];
            $items = scandir($dir);

            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }

                $fullPath = $dir . DIRECTORY_SEPARATOR . $item;
                $relativePath = $basePath ? $basePath . DIRECTORY_SEPARATOR . $item : $item;

                if (is_dir($fullPath)) {
                    $children = scanDirectoryTree($fullPath, $relativePath);
                    // Only add the folder if it contains markdown files
                    if (!empty($children)) {
                        $result[$item] = [
                            'type' => 'folder',
                            'path' => $fullPath,
                            'relative_path' => $relativePath,
                            'children' => $children
                        ];
                    }
                } else {
                    $extension = pathinfo($fullPath, PATHINFO_EXTENSION);
                    if ($extension === 'md') {
                        $result[$item] = [
                            'type' => 'file',
                            'path' => $fullPath,
                            'relative_path' => $relativePath,
                            'extension' => $extension
                        ];
                    }
                }
            }

            return $result;
        }

        function renderTree($tree, $basePath = '') {
            echo "<ul>\n";
            $hasMdFiles = false;

            foreach ($tree as $name => $item) {
                echo "<li>\n";

                if ($item['type'] === 'folder') {
                    echo "<span class='folder collapsed' onclick='toggleFolder(this)'>" . htmlspecialchars($name) . "</span>\n";
                    echo "<div class='collapsible-content'>";
                    renderTree($item['children'], $item['relative_path']);
                    echo "</div>";
                } else {
                    $extension = $item['extension'];
                    if ($extension === 'md') {
                        echo "<a class='file md-file' href='index.php?file=" . urlencode($item['path']) . "' target='_blank'>" . htmlspecialchars($name) . "</a>\n";
                        $hasMdFiles = true;
                    }
                }

                echo "</li>\n";
            }

            echo "</ul>\n";
            return $hasMdFiles;
        }

        if (isset($_GET['folder']) && !empty($_GET['folder'])) {
            $folderPath = $_GET['folder'];

            if (!is_dir($folderPath)) {
                echo "<p class='error'>Error: The provided path is not a directory.</p>";
            } else {
                echo "<h2>Directory Tree:</h2>\n";
                echo "<div class='tree'>\n";

                $tree = scanDirectoryTree($folderPath);
                $hasMdFiles = renderTree($tree);

                if (!$hasMdFiles) {
                    echo "<p class='no-files'>No markdown files found in the selected directory.</p>\n";
                }
                echo "</div>\n";

                echo "<div class='controls'>";
                echo "<button type='button' onclick='collapseAll()'>Collapse All</button>";
                echo "<button type='button' onclick='expandAll()'>Expand All</button>";
                echo "</div>";
            }
        }
        ?>

        <script>
            function toggleFolder(element) {
                element.classList.toggle('collapsed');
            }

            function collapseAll() {
                const folders = document.querySelectorAll('.folder');
                folders.forEach(folder => {
                    if (!folder.classList.contains('collapsed')) {
                        folder.classList.add('collapsed');
                    }
                });
            }

            function expandAll() {
                const folders = document.querySelectorAll('.folder');
                folders.forEach(folder => {
                    if (folder.classList.contains('collapsed')) {
                        folder.classList.remove('collapsed');
                    }
                });
            }
        </script>
    </div>
</body>
</html>
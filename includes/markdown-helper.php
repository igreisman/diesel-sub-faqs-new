<?php
// Markdown rendering helper for Submarine FAQ app
// Provides smart content rendering that handles both Markdown and plain text

// Include Parsedown library
require_once __DIR__ . '/Parsedown.php';

/**
 * Render content as Markdown with security features
 */
function render_markdown($text) {
    static $parsedown = null;

    if ($parsedown === null) {
        $parsedown = new Parsedown();

        // Security: escape HTML by default to prevent XSS
        $parsedown->setSafeMode(true);

        // Enable line breaks (GitHub-style)
        $parsedown->setBreaksEnabled(true);
    }

    // Let Parsedown handle tables natively now that duplicate helper is removed
    $html = $parsedown->text($text);

    // Add spacing between table columns
    $html = preg_replace_callback('#<table>(.*?)</table>#s', function($matches) {
        $tableHtml = $matches[0];
        // Ensure table has expected classes
        if (strpos($tableHtml, 'class="md-table"') === false) {
            $tableHtml = str_replace('<table', '<table class="md-table table table-sm"', $tableHtml);
        }

        // Try to compute column widths from content length (character count)
        try {
            libxml_use_internal_errors(true);
            $doc = new DOMDocument();
            // Load a fragment by wrapping in a full HTML structure
            $doc->loadHTML('<?xml encoding="utf-8"?><html><body>' . $tableHtml . '</body></html>');
            $xpath = new DOMXPath($doc);
            $tableNode = $xpath->query('//table')->item(0);
            if ($tableNode) {
                // Collect rows (header + body)
                $rows = [];
                // Header cells
                $thead = $tableNode->getElementsByTagName('thead')->item(0);
                if ($thead) {
                    foreach ($thead->getElementsByTagName('tr') as $tr) {
                        $cells = [];
                        foreach ($tr->childNodes as $cell) {
                            if ($cell->nodeType === XML_ELEMENT_NODE) {
                                $cells[] = trim($cell->textContent);
                            }
                        }
                        if ($cells) $rows[] = $cells;
                    }
                }
                // Body rows
                $tbody = $tableNode->getElementsByTagName('tbody')->item(0);
                if ($tbody) {
                    foreach ($tbody->getElementsByTagName('tr') as $tr) {
                        $cells = [];
                        foreach ($tr->childNodes as $cell) {
                            if ($cell->nodeType === XML_ELEMENT_NODE) {
                                $cells[] = trim($cell->textContent);
                            }
                        }
                        if ($cells) $rows[] = $cells;
                    }
                }

                if (!empty($rows)) {
                    // Determine column count (max cells in any row)
                    $colCount = 0;
                    foreach ($rows as $r) {
                        $colCount = max($colCount, count($r));
                    }

                    // Compute max length per column
                    $maxLens = array_fill(0, $colCount, 0);
                    foreach ($rows as $r) {
                        for ($i = 0; $i < $colCount; $i++) {
                            $cell = $r[$i] ?? '';
                            $len = mb_strlen($cell, 'UTF-8');
                            if ($len > $maxLens[$i]) $maxLens[$i] = $len;
                        }
                    }

                    $total = array_sum($maxLens) ?: 1;
                    // Minimum percentage per column to avoid zero width
                    $minPct = 5;
                    $widths = [];
                    foreach ($maxLens as $len) {
                        $pct = max($minPct, round(($len / $total) * 100));
                        $widths[] = $pct;
                    }

                    // Normalize to 100% if sum differs
                    $sum = array_sum($widths);
                    if ($sum !== 100) {
                        // Adjust largest column to absorb difference
                        $diff = 100 - $sum;
                        $maxIdx = array_search(max($widths), $widths);
                        $widths[$maxIdx] += $diff;
                    }

                    // Build colgroup
                    $colgroup = $doc->createElement('colgroup');
                    foreach ($widths as $w) {
                        $col = $doc->createElement('col');
                        $col->setAttribute('style', 'width: ' . $w . '%');
                        $colgroup->appendChild($col);
                    }

                    // Insert colgroup as first child of table (before thead/tbody)
                    if ($tableNode->firstChild) {
                        $tableNode->insertBefore($colgroup, $tableNode->firstChild);
                    } else {
                        $tableNode->appendChild($colgroup);
                    }

                    // Export the modified table HTML
                    $newTable = '';
                    foreach ($tableNode->childNodes as $child) {
                        $newTable .= $doc->saveHTML($child);
                    }
                    // Reconstruct full table tag with attributes
                    $tableTag = '<table';
                    if ($tableNode->hasAttributes()) {
                        foreach ($tableNode->attributes as $attr) {
                            $tableTag .= ' ' . $attr->nodeName . '="' . htmlspecialchars($attr->nodeValue, ENT_QUOTES) . '"';
                        }
                    }
                    $tableTag .= '>' . $newTable . '</table>';

                    return $tableTag;
                }
            }
        } catch (Exception $e) {
            // on failure, fall back to original table HTML
        }

        return $tableHtml;
    }, $html);

    return $html;
}

/**
 * Render Markdown with HTML allowed (for trusted content)
 */
function render_markdown_unsafe($text) {
    static $parsedown = null;
    
    if ($parsedown === null) {
        $parsedown = new Parsedown();
        $parsedown->setSafeMode(false); // Allow HTML
        $parsedown->setBreaksEnabled(true);
    }
    
    return $parsedown->text($text);
}

/**
 * Detect if content contains Markdown syntax
 */
function is_markdown_content($text) {
    if (empty($text)) {
        return false;
    }
    
    // Common Markdown patterns
    $markdown_patterns = [
        '/^#{1,6}\s+.+$/m',      // Headers (# ## ###)
        '/\*\*[^*]+\*\*/',       // Bold (**text**)
        '/\*[^*\n]+\*/',         // Italic (*text*)
        '/^\s*[-*+]\s+.+$/m',    // Unordered lists (- * +)
        '/^\s*\d+\.\s+.+$/m',    // Ordered lists (1. 2. 3.)
        '/\[[^\]]+\]\([^)]+\)/', // Links [text](url)
        '/```[^`]*```/s',        // Code blocks
        '/`[^`\n]+`/',           // Inline code
        '/^\s*>\s+.+$/m',        // Blockquotes
        '/\|.*\|/',              // Tables
        '/^---+$/m',             // Horizontal rules
    ];
    
    foreach ($markdown_patterns as $pattern) {
        if (preg_match($pattern, $text)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Detect if content contains HTML tags
 */
function is_html_content($text) {
    if (empty($text)) {
        return false;
    }
    
    // Check for HTML tags
    return $text !== strip_tags($text);
}

/**
 * Sanitize HTML content to prevent XSS while preserving formatting
 */
function sanitize_html_content($html) {
    // Allowed HTML tags for FAQ content
    $allowed_tags = '<p><br><strong><b><em><i><u><strike><del><h1><h2><h3><h4><h5><h6><ul><ol><li><table><thead><tbody><tr><td><th><blockquote><pre><code><a><img><div><span><sub><sup>';
    
    // Strip disallowed tags
    $html = strip_tags($html, $allowed_tags);
    
    // Remove dangerous attributes
    $html = preg_replace('/on\w+="[^"]*"/i', '', $html); // Remove onclick, onload, etc.
    $html = preg_replace('/javascript:/i', '', $html);    // Remove javascript: URLs
    
    return $html;
}

/**
 * Smart content renderer - auto-detects HTML vs Markdown vs plain text
 */
function render_content($text) {
    if (empty($text)) {
        return '';
    }
    
    // Auto-detect content type
    if (is_html_content($text)) {
        // HTML content from WYSIWYG editor
        return sanitize_html_content($text);
    } elseif (is_markdown_content($text)) {
        // Markdown content 
        return render_markdown($text);
    } else {
        // Plain text - convert line breaks to HTML
        return nl2br(htmlspecialchars($text));
    }
}

/**
 * Render content with explicit type specification
 */
function render_content_typed($text, $type = 'auto') {
    if (empty($text)) {
        return '';
    }
    
    switch ($type) {
        case 'markdown':
            return render_markdown($text);
        case 'html':
            return render_markdown_unsafe($text);
        case 'text':
            return nl2br(htmlspecialchars($text));
        case 'auto':
        default:
            return render_content($text);
    }
}

/**
 * Convert plain text to Markdown (for migration)
 */
function text_to_markdown($text) {
    if (empty($text)) {
        return '';
    }
    
    // Basic conversions
    $markdown = $text;
    
    // Convert line breaks to Markdown line breaks
    $markdown = str_replace("\r\n", "\n", $markdown);
    $markdown = str_replace("\r", "\n", $markdown);
    
    // Convert double line breaks to paragraph breaks
    $markdown = preg_replace('/\n\n+/', "\n\n", $markdown);
    
    // Escape special Markdown characters if they appear to be literal
    $markdown = preg_replace('/([*_`#\[\](){}])/', '\\$1', $markdown);
    
    return $markdown;
}

/**
 * Get content preview (for search results, etc.)
 */
function get_content_preview($text, $length = 160) {
    if (empty($text)) {
        return '';
    }
    
    // Strip Markdown formatting for preview
    $preview = $text;
    
    // Remove Markdown syntax
    $preview = preg_replace('/#{1,6}\s+/', '', $preview);  // Headers
    $preview = preg_replace('/\*\*([^*]+)\*\*/', '$1', $preview);  // Bold
    $preview = preg_replace('/\*([^*\n]+)\*/', '$1', $preview);    // Italic
    $preview = preg_replace('/`([^`\n]+)`/', '$1', $preview);      // Code
    $preview = preg_replace('/\[[^\]]+\]\([^)]+\)/', '$1', $preview); // Links
    $preview = preg_replace('/^\s*[-*+]\s+/', '', $preview);       // Lists
    $preview = preg_replace('/^\s*\d+\.\s+/', '', $preview);       // Numbered lists
    
    // Clean up whitespace
    $preview = preg_replace('/\s+/', ' ', $preview);
    $preview = trim($preview);
    
    // Truncate to length
    if (strlen($preview) > $length) {
        $preview = substr($preview, 0, $length) . '...';
    }
    
    return htmlspecialchars($preview);
}

/**
 * Convert GitHub-style pipe tables into HTML placeholders, capturing the HTML for later insertion.
 */
function convert_markdown_tables($text, &$tableMap) {
    $lines = explode("\n", $text);
    $output = [];
    $i = 0;

    while ($i < count($lines)) {
        $line = $lines[$i];
        $next = $lines[$i + 1] ?? '';

        // Detect header + separator line
        $looksLikeHeader = strpos($line, '|') !== false;
        $looksLikeSeparator = preg_match('/^\\s*\\|?\\s*[:\\-]+[\\s\\|:\\-]*$/', $next);

        if ($looksLikeHeader && $looksLikeSeparator) {
            // Parse header cells
            $headers = array_values(array_filter(array_map('trim', explode('|', $line)), 'strlen'));
            // Collect body rows
            $rows = [];
            $i += 2; // skip header + separator
            while ($i < count($lines)) {
                $rowLine = $lines[$i];
                if (trim($rowLine) === '' || strpos($rowLine, '|') === false) {
                    break;
                }
                $cells = array_values(array_filter(array_map('trim', explode('|', $rowLine)), 'strlen'));
                $rows[] = $cells;
                $i++;
            }

            // Build HTML table with calculated column widths
            $colCount = 0;
            foreach ($rows as $r) {
                $colCount = max($colCount, count($r));
            }

            $maxLens = array_fill(0, $colCount, 0);
            foreach (array_merge([$headers], $rows) as $r) {
                for ($i = 0; $i < $colCount; $i++) {
                    $cell = $r[$i] ?? '';
                    $len = mb_strlen($cell, 'UTF-8');
                    if ($len > $maxLens[$i]) $maxLens[$i] = $len;
                }
            }

            $total = array_sum($maxLens) ?: 1;
            $minPct = 5;
            $widths = [];
            foreach ($maxLens as $len) {
                $pct = max($minPct, round(($len / $total) * 100));
                $widths[] = $pct;
            }
            $sum = array_sum($widths);
            if ($sum !== 100) {
                $diff = 100 - $sum;
                $maxIdx = array_search(max($widths), $widths);
                $widths[$maxIdx] += $diff;
            }

            $colgroupHtml = '<colgroup>';
            for ($i = 0; $i < $colCount; $i++) {
                $colgroupHtml .= '<col style="width: ' . $widths[$i] . '%">';
            }
            $colgroupHtml .= '</colgroup>';

            // Build table HTML
            $tableHtml = '<table class="table table-bordered table-sm">' . $colgroupHtml . '<thead><tr>';
            foreach ($headers as $cell) {
                $tableHtml .= '<th>' . htmlspecialchars($cell, ENT_QUOTES, 'UTF-8') . '</th>';
            }
            $tableHtml .= '</tr></thead><tbody>';
            foreach ($rows as $cells) {
                $tableHtml .= '<tr>';
                for ($i = 0; $i < $colCount; $i++) {
                    $cell = $cells[$i] ?? '';
                    $tableHtml .= '<td>' . htmlspecialchars($cell, ENT_QUOTES, 'UTF-8') . '</td>';
                }
                $tableHtml .= '</tr>';
            }
            $tableHtml .= '</tbody></table>';

            $placeholder = '%%TABLE_' . count($tableMap) . '%%';
            $tableMap[$placeholder] = $tableHtml;
            $output[] = $placeholder;
            continue;
        } else {
            $output[] = $line;
            $i++;
        }
    }

    return implode("\n", $output);
}
?>

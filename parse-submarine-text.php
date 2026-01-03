<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$page_title = 'Parse Submarine Text';
$page_description = 'Admin: Parse submarine text and import';
require_once 'config/database.php';

// Admin gate
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit;
}

$parsedData = null;
$error = '';

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['submarine_text'])
) {
    $text = $_POST['submarine_text'];
    try {
        // Parse the text
        $data = array(
            'boat_number' => '',
            'name' => '',
            'designation' => '',
            'captain_name' => '',
            'date_lost' => '',
            'location' => '',
            'description' => '',
            'prior_history' => ''
        );
        $lines = explode("\n", $text);
        $descriptionLines = array();
        $priorHistoryLines = array();
        $skipNextEmpty = false;
        $inPriorHistory = false;
        foreach ($lines as $line) {
            $line = trim($line);
            // Detect start of Prior History section
            if (preg_match('/^Prior history:?/i', $line)) {
                $inPriorHistory = true;
                // If the line has content after the colon, add it
                $afterColon = trim(preg_replace('/^Prior history:?/i', '', $line));
                if (!empty($afterColon)) {
                    $priorHistoryLines[] = $afterColon;
                }
                continue;
            }
            // If we're in prior history, keep collecting until a blank line or a new section
            if ($inPriorHistory) {
                if ($line === '' || preg_match('/^(Last captain:|Date lost:|Location:|Fatalities:|Cause:)/i', $line)) {
                    $inPriorHistory = false;
                } else {
                    $priorHistoryLines[] = $line;
                    continue;
                }
            }
            // Extract boat number and name from first line (e.g., "USS E-2 (SS-25)")
            if (empty($data['name']) && preg_match('/USS\s+([^\s\(]+)\s*\(([^\)]+)\)/', $line, $matches)) {
                $data['name'] = $matches[1];
                $data['boat_number'] = $matches[2];
                // Full designation is the complete USS Name (Number) format
                $data['designation'] = 'USS ' . $matches[1] . ' (' . $matches[2] . ')';
                continue;
            }
            // Extract captain
            if (preg_match('/Last captain:\s*(.+?)\.?\s*$/i', $line, $matches)) {
                $data['captain_name'] = trim($matches[1]);
                $skipNextEmpty = true;
                continue;
            }
            // Extract date lost
            if (preg_match('/Date lost:\s*(.+?)\.?\s*$/i', $line, $matches)) {
                $dateStr = trim($matches[1]);
                // Remove any trailing period
                $dateStr = rtrim($dateStr, '.');
                // Try multiple date formats and convert to "Month Day, Year" format
                $date = date_create_from_format('d F Y', $dateStr);
                if (!$date) {
                    $date = date_create_from_format('F d, Y', $dateStr);
                }
                if (!$date) {
                    $date = date_create($dateStr);
                }
                if ($date) {
                    // Convert to format like "January 15, 1916"
                    $data['date_lost'] = $date->format('F j, Y');
                } else {
                    // If parsing fails, keep original
                    $data['date_lost'] = $dateStr;
                }
                $skipNextEmpty = true;
                continue;
            }
            // Extract location
            if (preg_match('/Location:\s*(.+)/i', $line, $matches)) {
                $data['location'] = trim($matches[1]);
                $skipNextEmpty = true;
                continue;
            }
            // Skip metadata lines
            if (preg_match('/^(Fatalities|Cause):/i', $line)) {
                $descriptionLines[] = $line;
                $skipNextEmpty = true;
                continue;
            }
            // Skip empty lines after metadata
            if (empty($line) && $skipNextEmpty) {
                $skipNextEmpty = false;
                continue;
            }
            // Add everything else to description
            if (!empty($line)) {
                $descriptionLines[] = $line;
            } elseif (!empty($descriptionLines)) {
                // Preserve paragraph breaks
                $descriptionLines[] = '';
            }
        }
        // Build description
        $data['description'] = implode("\n\n", array_filter(array_map(function($para) {
            return trim($para);
        }, explode("\n\n", implode("\n", $descriptionLines)))));
        // Build prior history
        $data['prior_history'] = trim(implode("\n", $priorHistoryLines));
        // Also parse date_lost into YYYY-MM-DD for calculations
        $dateForCalc = '';
        if (!empty($data['date_lost'])) {
            // Try to parse as YYYY-MM-DD
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date_lost'])) {
                $dateForCalc = $data['date_lost'];
            } else {
                $ts = strtotime($data['date_lost']);
                if ($ts !== false) {
                    $dateForCalc = date('Y-m-d', $ts);
                }
            }
            // Validate the date is actually valid
            if (!empty($dateForCalc)) {
                $parts = explode('-', $dateForCalc);
                if (count($parts) === 3 && !checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0])) {
                    // Invalid date, don't use it
                    $dateForCalc = '';
                }
            }
        }
        $data['date_lost_sort'] = $dateForCalc;
        // Set calculated_era from date_lost_sort
        $calculated_era = '';
        if (!empty($dateForCalc)) {
            if ($dateForCalc < '1941-12-07') {
                $calculated_era = 'pre-ww2';
            } elseif ($dateForCalc > '1945-09-02') {
                $calculated_era = 'post-ww2';
            } else {
                $calculated_era = 'ww2';
            }
        }
        $data['calculated_era'] = $calculated_era;
        $parsedData = $data;
    } catch (Exception $e) {
        $error = 'Error parsing text: ' . $e->getMessage();
    }
}
// End PHP logic block cleanly before HTML
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parse Submarine Text - Diesel-Electric Submarine FAQs</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #1a1a1a;
            color: #e0e0e0;
            padding-bottom: 50px;
        }
        .container {
            max-width: 1200px;
        }
        .admin-header {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            padding: 2rem;
            margin-bottom: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }
        .form-label {
            color: #e0e0e0;
            font-weight: 500;
        }
        .form-control, .form-select {
            background: #2d2d2d;
            border: 1px solid #444;
            color: #e0e0e0;
        }
        .form-control:focus, .form-select:focus {
            background: #2d2d2d;
            border-color: #3498db;
            color: #e0e0e0;
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
        }
        .card {
            background: #2d2d2d;
            border: 1px solid #444;
        }
        textarea {
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        .preview-section {
            background: #252525;
            border: 1px solid #444;
            border-radius: 8px;
            padding: 1.5rem;
        }
        .preview-field {
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #444;
        }
        .preview-field:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .preview-label {
            color: #3498db;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .preview-value {
            color: #e0e0e0;
            margin-top: 0.5rem;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="admin-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-2">📋 Parse Submarine Text</h1>
                    <p class="mb-0 text-muted">Paste submarine text to automatically extract and parse data</p>
                </div>
                <div>
                    <a href="admin-eternal-patrol.php" class="btn btn-secondary">← Back to List</a>
                </div>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Paste Submarine Text</h5>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="submarine_text" class="form-label">Submarine Text</label>
                                <textarea class="form-control" id="submarine_text" name="submarine_text" 
                                          rows="20" required placeholder="USS E-2 (SS-25).
E-2 was an E class submarine completed in February of 1912...

Last captain: LT Charles M. Cooke.
Date lost: 15 January 1916.
Location: New York Navy Yard.
..."><?= htmlspecialchars($_POST['submarine_text'] ?? '') ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Parse Text</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <?php if ($parsedData): ?>
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Parsed Data</h5>
                            <div class="preview-section">
                                <div class="preview-field">
                                    <div class="preview-label">Boat Number</div>
                                    <div class="preview-value"><?= htmlspecialchars($parsedData['boat_number']) ?: '(not found)' ?></div>
                                </div>
                                
                                <div class="preview-field">
                                    <div class="preview-label">Name</div>
                                    <div class="preview-value"><?= htmlspecialchars($parsedData['name']) ?: '(not found)' ?></div>
                                </div>
                                
                                <div class="preview-field">
                                    <div class="preview-label">Designation</div>
                                    <div class="preview-value"><?= htmlspecialchars($parsedData['designation']) ?: '(not found)' ?></div>
                                </div>
                                
                                <div class="preview-field">
                                    <div class="preview-label">Captain Name</div>
                                    <div class="preview-value"><?= htmlspecialchars($parsedData['captain_name']) ?: '(not found)' ?></div>
                                </div>
                                
                                <div class="preview-field">
                                    <div class="preview-label">Date Lost</div>
                                    <div class="preview-value"><?= htmlspecialchars($parsedData['date_lost']) ?: '(not found)' ?></div>
                                </div>
                                
                                <div class="preview-field">
                                    <div class="preview-label">Location</div>
                                    <div class="preview-value"><?= htmlspecialchars($parsedData['location']) ?: '(not found)' ?></div>
                                </div>
                                
                                <div class="preview-field">
                                    <div class="preview-label">Description</div>
                                    <div class="preview-value"><?= htmlspecialchars($parsedData['description']) ?></div>
                                </div>
                                <?php if (!empty($parsedData['prior_history'])): ?>
                                <div class="preview-field">
                                    <div class="preview-label">Prior History</div>
                                    <div class="preview-value"><?= nl2br(htmlspecialchars($parsedData['prior_history'])) ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            
                            <form method="POST" action="admin-eternal-patrol-edit.php" class="mt-3">
                                <input type="hidden" name="boat_number" value="<?= htmlspecialchars($parsedData['boat_number'] ?? '') ?>">
                                <input type="hidden" name="name" value="<?= htmlspecialchars($parsedData['name'] ?? '') ?>">
                                <input type="hidden" name="designation" value="<?= htmlspecialchars($parsedData['designation'] ?? '') ?>">
                                <input type="hidden" name="captain_name" value="<?= htmlspecialchars($parsedData['captain_name'] ?? '') ?>">
                                <input type="hidden" name="date_lost" value="<?= htmlspecialchars($parsedData['date_lost'] ?? '') ?>">
                                <input type="hidden" name="date_lost_sort" value="<?= htmlspecialchars($parsedData['date_lost_sort'] ?? '') ?>">
                                <input type="hidden" name="location" value="<?= htmlspecialchars($parsedData['location'] ?? '') ?>">
                                <input type="hidden" name="description" value="<?= htmlspecialchars($parsedData['description'] ?? '') ?>">
                                <input type="hidden" name="prior_history" value="<?= htmlspecialchars($parsedData['prior_history'] ?? '') ?>">
                                <input type="hidden" name="prefilled" value="1">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-arrow-right-circle"></i> Continue to Add Lost Boat Form
                                </button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body text-center text-muted">
                            <p class="mb-0">Paste submarine text on the left and click "Parse Text" to see extracted data here.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<!-- End of file: all PHP blocks and braces are closed. -->

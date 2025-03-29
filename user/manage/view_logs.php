<?php
session_start();
require_once '../../includes/db_config.php';

// Check if user is logged in with admin privileges
if (!isset($_SESSION['admin_id'])) {
    die('Unauthorized access');
}

// Define log files to check
$logFiles = [
    '/logs/email_errors.log' => 'Email Error Logs',
    '/logs/alumni_email_queue.log' => 'Alumni Email Queue',
    '/logs/email_queue.log' => 'Student Email Queue',
    '/logs/debug.log' => 'General Debug Logs',
];

// Handle log clearing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_log'])) {
    $logFile = $_POST['log_file'];
    $fullPath = __DIR__ . '/../../' . trim($logFile, '/');
    
    if (file_exists($fullPath) && is_writeable($fullPath)) {
        file_put_contents($fullPath, '');
        $success_message = "Log file cleared successfully!";
    } else {
        $error_message = "Could not clear log file. Check permissions.";
    }
}

// Get current log file to view
$currentLog = isset($_GET['log']) ? $_GET['log'] : 'logs/email_errors.log';
$currentTitle = $logFiles['/' . $currentLog] ?? 'Log Viewer';

// Get log content
$logPath = __DIR__ . '/../../' . $currentLog;
$logContent = '';
if (file_exists($logPath)) {
    $logContent = file_get_contents($logPath);
    
    // If requested, filter logs
    if (isset($_GET['filter']) && !empty($_GET['filter'])) {
        $filter = $_GET['filter'];
        $filteredLines = [];
        $lines = explode("\n", $logContent);
        
        foreach ($lines as $line) {
            if (stripos($line, $filter) !== false) {
                $filteredLines[] = $line;
            }
        }
        
        $logContent = implode("\n", $filteredLines);
    }
} else {
    $logContent = "Log file not found.";
}

// Get log file size
$logSize = file_exists($logPath) ? filesize($logPath) : 0;
$logSizeFormatted = formatBytes($logSize);

// Format bytes to human-readable format
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= (1 << (10 * $pow));
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Viewer - maJIStic Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            padding-top: 20px;
        }
        .log-container {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            padding: 1rem;
            max-height: 70vh;
            overflow-y: auto;
            font-family: monospace;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .log-container .error {
            color: #dc3545;
        }
        .log-container .warning {
            color: #ffc107;
        }
        .log-container .info {
            color: #0d6efd;
        }
        .log-container .timestamp {
            color: #6c757d;
        }
        .log-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        .empty-log {
            color: #6c757d;
            font-style: italic;
            text-align: center;
            padding: 2rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><?php echo htmlspecialchars($currentTitle); ?></h1>
            <a href="index.php?page=all_registrations" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
        
        <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Available Log Files</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <?php foreach ($logFiles as $file => $title): 
                                $path = __DIR__ . '/../../' . trim($file, '/');
                                $fileSize = file_exists($path) ? formatBytes(filesize($path)) : '0 B';
                                $fileExists = file_exists($path);
                                $activeClass = ('/' . $currentLog === $file) ? 'active' : '';
                            ?>
                            <a href="?log=<?php echo urlencode(trim($file, '/')); ?>" class="list-group-item list-group-item-action <?php echo $activeClass; ?>">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($title); ?></h6>
                                    <small><?php echo $fileExists ? $fileSize : 'Not found'; ?></small>
                                </div>
                                <small class="text-muted"><?php echo htmlspecialchars(trim($file, '/')); ?></small>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Actions</h5>
                    </div>
                    <div class="card-body">
                        <!-- Filter form -->
                        <form method="get" class="mb-3">
                            <input type="hidden" name="log" value="<?php echo htmlspecialchars($currentLog); ?>">
                            <div class="input-group">
                                <input type="text" class="form-control" name="filter" placeholder="Filter logs..." value="<?php echo isset($_GET['filter']) ? htmlspecialchars($_GET['filter']) : ''; ?>">
                                <button class="btn btn-outline-primary" type="submit">
                                    <i class="bi bi-search"></i> Filter
                                </button>
                            </div>
                            <div class="form-text">Filter logs by text (case-insensitive)</div>
                        </form>
                        
                        <!-- Clear log form -->
                        <form method="post" onsubmit="return confirm('Are you sure you want to clear this log file?');">
                            <input type="hidden" name="log_file" value="<?php echo htmlspecialchars($currentLog); ?>">
                            <button type="submit" name="clear_log" class="btn btn-danger w-100">
                                <i class="bi bi-trash"></i> Clear Log File
                            </button>
                        </form>
                        
                        <!-- Log info -->
                        <div class="mt-3">
                            <p class="mb-1"><strong>Log file:</strong> <?php echo htmlspecialchars($currentLog); ?></p>
                            <p class="mb-1"><strong>Size:</strong> <?php echo $logSizeFormatted; ?></p>
                            <p class="mb-0"><strong>Path:</strong> <?php echo htmlspecialchars($logPath); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0">Help</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><strong>Email Error Logs:</strong> Contains detailed errors from email sending attempts.</p>
                        <p class="mb-1"><strong>Email Queue:</strong> Records of emails that couldn't be sent due to config issues.</p>
                        <p class="mb-0"><strong>Debug Logs:</strong> General application debugging information.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="log-header card-header">
                <h5 class="mb-0">Log Content</h5>
                <span class="text-muted"><?php echo $logSizeFormatted; ?></span>
            </div>
            <div class="card-body p-0">
                <div class="log-container" id="logContainer">
                    <?php if (empty($logContent) || $logContent === "Log file not found."): ?>
                        <div class="empty-log">
                            <?php echo $logContent === "Log file not found." ? "Log file not found." : "Log file is empty."; ?>
                        </div>
                    <?php else: 
                        // Syntax highlight log content
                        $lines = explode("\n", htmlspecialchars($logContent));
                        foreach ($lines as $line) {
                            if (empty(trim($line))) continue;
                            
                            // Determine line class based on content
                            $lineClass = '';
                            if (stripos($line, 'error') !== false || stripos($line, 'exception') !== false || stripos($line, 'failed') !== false) {
                                $lineClass = 'error';
                            } elseif (stripos($line, 'warning') !== false) {
                                $lineClass = 'warning';
                            } elseif (stripos($line, 'info') !== false || stripos($line, 'success') !== false) {
                                $lineClass = 'info';
                            }
                            
                            // Highlight timestamp [YYYY-MM-DD HH:MM:SS]
                            $line = preg_replace('/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]/', '<span class="timestamp">$0</span>', $line);
                            
                            echo "<div class=\"$lineClass\">$line</div>";
                        }
                    ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-sm btn-primary" id="scrollToTop">
                    <i class="bi bi-arrow-up"></i> Top
                </button>
                <button class="btn btn-sm btn-primary" id="scrollToBottom">
                    <i class="bi bi-arrow-down"></i> Bottom
                </button>
                <button class="btn btn-sm btn-outline-secondary" id="copyLogBtn">
                    <i class="bi bi-clipboard"></i> Copy
                </button>
                <a href="<?php echo '?log=' . urlencode($currentLog) . (isset($_GET['filter']) ? '&filter=' . urlencode($_GET['filter']) : ''); ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </a>
            </div>
        </div>
        
        <footer class="mt-4 mb-2 text-center text-muted">
            <small>&copy; <?php echo date('Y'); ?> maJIStic Admin | Log Viewer</small>
        </footer>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const logContainer = document.getElementById('logContainer');
        const scrollToTopBtn = document.getElementById('scrollToTop');
        const scrollToBottomBtn = document.getElementById('scrollToBottom');
        const copyLogBtn = document.getElementById('copyLogBtn');
        
        // Auto-scroll to the bottom when loaded (most recent logs)
        logContainer.scrollTop = logContainer.scrollHeight;
        
        // Scroll to top button
        scrollToTopBtn.addEventListener('click', function() {
            logContainer.scrollTop = 0;
        });
        
        // Scroll to bottom button
        scrollToBottomBtn.addEventListener('click', function() {
            logContainer.scrollTop = logContainer.scrollHeight;
        });
        
        // Copy log content
        copyLogBtn.addEventListener('click', function() {
            const logText = logContainer.innerText;
            
            // Create a temporary textarea element to copy from
            const textarea = document.createElement('textarea');
            textarea.value = logText;
            document.body.appendChild(textarea);
            textarea.select();
            
            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    copyLogBtn.innerHTML = '<i class="bi bi-check"></i> Copied!';
                    setTimeout(() => {
                        copyLogBtn.innerHTML = '<i class="bi bi-clipboard"></i> Copy';
                    }, 2000);
                } else {
                    copyLogBtn.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Failed';
                    setTimeout(() => {
                        copyLogBtn.innerHTML = '<i class="bi bi-clipboard"></i> Copy';
                    }, 2000);
                }
            } catch (err) {
                console.error('Failed to copy log content', err);
                copyLogBtn.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Failed';
                setTimeout(() => {
                    copyLogBtn.innerHTML = '<i class="bi bi-clipboard"></i> Copy';
                }, 2000);
            }
            
            document.body.removeChild(textarea);
        });
    });
    </script>
</body>
</html>

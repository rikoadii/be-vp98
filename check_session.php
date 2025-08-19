<?php
session_start();

echo "<h2>Session Debug Information</h2>";
echo "<p><strong>Session Status:</strong> " . session_status() . "</p>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";

echo "<h3>Session Variables:</h3>";
if (!empty($_SESSION)) {
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
} else {
    echo "<p>No session variables found.</p>";
}

echo "<h3>Headers Sent:</h3>";
echo "<p>Headers sent: " . (headers_sent() ? "Yes" : "No") . "</p>";

if (headers_sent($file, $line)) {
    echo "<p>Headers were sent in file: $file on line: $line</p>";
}
?>

<?php
include 'includes/session.php';

$return = isset($_GET['return']) ? $_GET['return'] : 'home.php';

if (isset($_POST['save'])) {
    $title = trim($_POST['title']);
    $title = preg_replace('/\s+/', ' ', $title); // normalize spaces

    $isValid = true;

    // ✅ Reject empty or too short/long
    if (strlen($title) < 3 || strlen($title) > 100) {
        $isValid = false;
    }

    // ✅ Reject if contains any disallowed characters (symbols, commas, punctuation)
    // Only allow letters, digits (for years), and spaces
    if (!preg_match('/^[A-Za-z0-9 ]+$/', $title)) {
        $isValid = false;
    }

    // ✅ Optional: Ensure it includes the word "Election" or "Council"
    // (you can comment this out if not required)
    if (!preg_match('/\b(Election|Council|Voting|Vote)\b/i', $title)) {
        $isValid = false;
    }

    // ✅ Check each word for nonsense or gibberish
    $words = explode(' ', $title);
    foreach ($words as $word) {
        $word = trim($word);
        if ($word === '') continue;

        // Allow 4-digit years like 2025
        if (preg_match('/^\d{4}$/', $word)) {
            continue;
        }

        // Reject words with no vowels or too short
        $vowels = preg_match_all('/[aeiouAEIOU]/', $word);
        if (strlen($word) < 3 && !preg_match('/^\d{4}$/', $word)) {
            $isValid = false;
            break;
        }
        if ($vowels === 0 && !preg_match('/^\d{4}$/', $word)) {
            $isValid = false;
            break;
        }
    }

    if ($isValid) {
        // ✅ Escape and save
        $safe_title = str_replace('"', '', $title);
        $content = 'election_title = "' . $safe_title . '"';

        if (file_put_contents('config.ini', $content) !== false) {
            $_SESSION['success'] = 'Election title updated successfully';
        } else {
            $_SESSION['error'] = 'Failed to update config.ini';
        }
    } else {
        $_SESSION['error'] = '⚠️ Invalid election title. Please use only letters, spaces, and optionally numbers (e.g., “Student Council Election 2025”).';
    }
} else {
    $_SESSION['error'] = "Fill up config form first";
}

header('location: ' . $return);
exit();
?>

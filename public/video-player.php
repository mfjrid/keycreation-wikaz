<?php
/**
 * Standalone Video Player Wrapper for Local Embeds
 */

$video_url = isset($_GET['src']) ? $_GET['src'] : '';

if (empty($video_url)) {
    exit('No video source provided.');
}

// Basic sanitization
$video_url = filter_var($video_url, FILTER_SANITIZE_URL);

// Basic security: only allow videos from the same domain or common extensions
$parsed_url = parse_url($video_url);
$host = isset($parsed_url['host']) ? $parsed_url['host'] : '';

// Simple validation for video file extensions
$is_video = preg_match('/\.(mp4|webm|ogg|mov)(\?.*)?$/i', $video_url);

if (!$is_video) {
    exit('Invalid video format.');
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Player</title>
    <style>
        body,
        html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            background: transparent;
        }

        video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
    </style>
</head>

<body>
    <video src="<?php echo htmlspecialchars($video_url, ENT_QUOTES, 'UTF-8'); ?>" autoplay muted loop playsinline></video>
</body>

</html>
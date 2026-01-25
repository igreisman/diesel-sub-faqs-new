<?php
// This script fetches the 3 latest videos from the Diesel Subs YouTube channel using the YouTube Data API v3
// and returns them as a JSON array for use on the homepage.

header('Content-Type: application/json');

$apiKey = 'AIzaSyAbd_g8bWWDLiqsrLUULYojoBaGltJW998';
$channelId = 'UC5mvn2ZZ6V508cVxHg4NwKA'; // Diesel Subs channel ID
$maxResults = 3;

// Get uploads playlist ID for the channel
$channelApiUrl = "https://www.googleapis.com/youtube/v3/channels?part=contentDetails&id=$channelId&key=$apiKey";
$channelData = json_decode(file_get_contents($channelApiUrl), true);

if (!isset($channelData['items'][0]['contentDetails']['relatedPlaylists']['uploads'])) {
    echo json_encode(['success' => false, 'message' => 'Could not fetch uploads playlist.']);
    exit;
}

$uploadsPlaylistId = $channelData['items'][0]['contentDetails']['relatedPlaylists']['uploads'];

// Get latest videos from uploads playlist
$playlistApiUrl = "https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&playlistId=$uploadsPlaylistId&maxResults=$maxResults&key=$apiKey";
$playlistData = json_decode(file_get_contents($playlistApiUrl), true);

$videos = [];
if (isset($playlistData['items'])) {
    foreach ($playlistData['items'] as $item) {
        $snippet = $item['snippet'];
        $videos[] = [
            'title' => $snippet['title'],
            'description' => $snippet['description'],
            'videoId' => $snippet['resourceId']['videoId'],
            'thumbnail' => $snippet['thumbnails']['medium']['url'],
        ];
    }
}

echo json_encode(['success' => true, 'videos' => $videos]);

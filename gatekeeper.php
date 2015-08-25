<?php
session_start();
require_once('common.php');

# Settings #
$window = 2; # seconds #
$maxAge = 1800; # seconds #
$vault = $_SERVER['DOCUMENT_ROOT'] . "/vault/";

# Request #
$getFile = pathinfo(_get('file'));
$file = $getFile['basename'];
$token = _get('token');

# Clean Up #
if (isset($_SESSION['vaultFiles'])) {
  $expired = array();
  foreach ($_SESSION['vaultFiles'] as $key => $fileInfo) {
    if (isset($fileInfo['time']) && time() > ($fileInfo['time'] + $maxAge)) $expired[] = $key;
  }
  foreach ($expired as $key) {
    unset($_SESSION['vaultFiles'][$key]);
  }
}

# File Token Requests #
if ($file) {
  if (! isset($_SESSION['vaultFiles'])) $_SESSION['vaultFiles'] = array();
  $key = _genkey(20);
  while (array_key_exists($key, $_SESSION['vaultFiles'])) {
    $key = _genkey(20);
  }
  $_SESSION['vaultFiles'][$key] = array('file' => $file, 'time' => time());
  _re("api/gatekeeper.php?token=$key", null);
}

# File Content Requests #
elseif ($token && isset($_SESSION['vaultFiles']) && array_key_exists($token, $_SESSION['vaultFiles'])) {

  # File Info #
  $fullFileName = $vault . $_SESSION['vaultFiles'][$token]['file'];
  $filesize = filesize($fullFileName);
  $fileType = "audio/mpeg";
  $offset = 0;
  $length = $filesize;
  $tokenAge = time() - $_SESSION['vaultFiles'][$token]['time'];

  # Parial Content Requests #
  if (isset($_SERVER['HTTP_RANGE'])) {
    #preg_match('/bytes=(\d*)-(\d*)?/', $_SERVER['HTTP_RANGE'], $matches);
    preg_match('/bytes=(\d+)?/', $_SERVER['HTTP_RANGE'], $matches);
    $offset = intval($matches[1]);
    #$endOffset = intval($matches[2]);
    #$length = $endOffset - $offset;
    #header('HTTP/1.1 206 Partial Content');
  }

  # Must be within window for initial request, or within max age for subsequent partial content requests. #
  if ( $tokenAge <= $window || ( $offset != 0 && $tokenAge <= $maxAge ) ) {

    #unset($_SESSION['vaultFiles'][$token]);
    session_write_close();

    # Response Headers #
    header("Accept-Ranges: bytes");
    header("Content-Type: $fileType");
    if ($offset != 0) {
      header('HTTP/1.1 206 Partial Content');
      header("Content-Range: bytes $offset-$filesize/$filesize");
    }
    header("Content-Length: " . ($filesize - $offset));
    $fh = fopen($fullFileName, 'rb');
    fseek($fh, $offset);
    fpassthru($fh);
  } else {
    _re(null, "Invalid or expired token.", 400);
  }
}
?>
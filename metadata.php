<?php

define('CACHE_IN_SECONDS', 5);
include_once('conf.inc.php');

header('Access-Control-Allow-Origin: *');

// Léger cache Memcached
$m = new Memcached();
$m->addServer('localhost', 11211);

$obj = $m->get('metadata');
if (!$obj || empty($obj)) {
    // Ce script retourne le fichier exact en cours de lecture par Ezstream.
    // Il utilise lsof :
    // $ lsof -c ezstream -Fn|grep '^n/var' | sed -e 's/^n//g'
    #exec('/usr/bin/sudo /etc/ezstream/get_running_ziq.sh', $output, $rcode);
    // En compilant le script avec shc et en lui mettant un setuid
    // cela permet de l'executer sans sudo, ce qui allége les logs
    exec('/etc/ezstream/get_running_ziq.sh.x', $output, $rcode);

    // Pas de fichier, ezstream ne tourne peut-être pas.
    if (empty($output)) {
        die('No music');
    }

    $ziq = $output[0];

    $db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME) or die("Database error");

    $query = "
  select
    a.name as artist,
    a.id as artist_id,
    al.name as album,
    al.id as album_id,
    s.title,
    s.id as title_id
  from
    song s,
    artist a,
    album al
  where
    a.id = s.artist
    and al.id = s.album
    and s.file='" . $db->real_escape_string($ziq) . "'
  limit 1;";

    $result = $db->query($query);

    $obj = $result->fetch_object() ;

    if (is_null($obj)) {
        die();
    }

    $m->set('metadata', $obj, CACHE_IN_SECONDS);

    $db->close();
}


if (isset($_GET['wanted'])) {
    $wanted=strtolower(trim($_GET['wanted']));
} elseif ($argc == 2) {
    $wanted=trim(strtolower($argv[1]));
} else {
    $wanted='default';
}

// Add some usefull properties
$obj->artist_url="https://play.dogmazic.net/artists.php?action=show&artist=" . $obj->artist_id;
$obj->album_url ="https://play.dogmazic.net/albums.php?action=show&album=" . $obj->album_id;
$obj->song_url  ="https://play.dogmazic.net/song.php?action=show_song&song_id=" . $obj->title_id;
$obj->label_img = "https://play.dogmazic.net/image.php?object_id=" . $obj->album_id . "&object_type=album&size=200x200";

// If you want to redirect somewhere...
function go($url)
{
    header('Location: ' . $url);
    exit();
}

switch ($wanted) {
    case 'img':
        echo $obj->label_img;
        break;
    case 'artist':
        echo $obj->artist;
        break;
    case 'artist_url':
        echo $obj->artist_url;
        break;
    case 'artist_go':
        go($obj->artist_url);
        break;
    case 'album':
        echo $obj->album;
        break;
    case 'album_url':
        echo $obj->album_url;
        break;
    case 'album_go':
        go($obj->album_url);
        break;
    case 'title':
    case 'song':
        echo $obj->title;
        break;
    case 'title_url':
    case 'song_url':
        echo $obj->song_url;
        break;
    case 'title_go':
    case 'song_go':
        go($obj->song_url);
        break;
    case 'json':
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($obj);
        exit();
        break;
    default:
        #echo $obj->artist." - ".$obj->album." - ".$obj->title;
        echo $obj->artist . " - " . $obj->title;
        break;
}

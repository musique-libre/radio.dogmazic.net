<?php

define('CACHE_IN_SECONDS', 500);
include_once('conf.inc.php');

header('Access-Control-Allow-Origin: *');

$song_id=intval($_REQUEST['song_id']);
if ($song_id == 0) {
    die('No song id');
}

// Léger cache Memcached
$m = new Memcached();
$m->addServer('localhost', 11211);

$metadata_keyname = 'metadata_song_v2_' . intval($song_id);

$obj = $m->get($metadata_keyname);
if (!$obj || empty($obj)) {

    $db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME) or die("Database error");

    // NB : jointures explicites. En melangeant « from a, b » et « left join »,
    // MySQL evalue le JOIN avant la virgule et rejette la clause ON qui
    // reference s.license (« Unknown column in on clause »).
    $query = "
  select
    a.name as artist,
    a.id as artist_id,
    al.name as album,
    al.id as album_id,
    s.title,
    s.id as title_id,
    s.license as license_id,
    l.name as license,
    l.external_link as license_url
  from song s
    join artist a on a.id = s.artist
    join album al on al.id = s.album
    left join license l on l.id = s.license
  where
    s.id='" . $db->real_escape_string($song_id) . "'
  limit 1;";

    $result = $db->query($query);

    $obj = $result->fetch_object() ;

    if (is_null($obj)) {
        die();
    }

    $m->set($metadata_keyname, $obj, CACHE_IN_SECONDS);

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

// Licence. Un morceau peut ne pas en avoir (license NULL ou 0) : on renvoie
// alors des chaines vides plutot que null, pour que les clients JS puissent
// tester la valeur sans se soucier du type.
$obj->license     = isset($obj->license) && $obj->license !== null ? $obj->license : '';
$obj->license_url = isset($obj->license_url) && $obj->license_url !== null ? $obj->license_url : '';
// Recherche de tous les morceaux partageant cette licence dans l'archive.
$obj->license_search = $obj->license_id
    ? "https://play.dogmazic.net/search.php?type=song&action=search&operator=and"
      . "&rule_1=license&rule_1_operator=0&rule_1_input=" . intval($obj->license_id)
    : '';

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
    case 'img_go':
        go($obj->label_img);
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
    case 'license':
    case 'licence':
        echo $obj->license;
        break;
    case 'license_url':
    case 'licence_url':
        echo $obj->license_url;
        break;
    case 'license_go':
    case 'licence_go':
        if ($obj->license_url === '') {
            die('No license url');
        }
        go($obj->license_url);
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

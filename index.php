<?php
header("Access-Control-Allow-Origin: https://play.dogmazic.net/");
$flux = 'https://radio.dogmazic.net:8001/stream.mp3';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Radio Dogmazic</title>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=Edge">
  <meta name="description" content="">
  <meta name="keywords" content="">
  <meta name="team" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

  <script src="/jquery-3.7.1.min.js" ></script>

  <style>
    body {
      background-color: #91949B;
      background-color: #222;
      color: #ff9d00;
    }

    .center {
      text-align:center;
      width:100%;
    }

    a {
      color: #ff9d00;
    }
  </style>
</head>

<body>

<div class="center">

  <img src="/Logo-DGZ-TRANSPARENT.png" alt="Dogmazic Logo" width="400" height="515" style="width: 80%; max-width: 400px; height: auto;">

  <br>
  <br>

  <audio controls id="dogplayer" onpause="refreshInfos()" onplay="refreshInfos()" >
    <source src="<?php echo $flux; ?>" type="audio/mpeg">
    <p>
      Look like your browser can't handle HTML5, here the <a href="<?php echo $flux; ?>">direct link</a>.
    </p>
  </audio>

  <br>
  <br>

  <div id="display_infos">
    <span id="metainfos">
      <a href='#' title='Show this artist on Dogmazic' id="link_artist" target=_blank ></a> -
      <a href='#' title='Show this song on Dogmazic' id="link_song" target=_blank ></a>
    </span>

    <br>
    <br>

    <a href="#" target=_blank id="link_album">
      <img src='/blank_album_art.png' alt="Album Art" title="Show this album on Dogmazic" id="albumart" width="125" height="125" style="width:60%; max-width: 125px; height: auto;">
      <br>
      <span id="album_title"></span>
    </a>
  </div>

  <img src='/pause.png' alt="Pause" title="Paused" id="pauseimg" width="125" height="125" onclick="playRadio()" style="width:60%; max-width: 125px; height: auto;">

</div>

<br/>

<script>

function playRadio() {
  document.getElementById('dogplayer').play();
}

// Need this as a global var for refreshInfos()
var current_song_id = null;

function refreshInfos() {
  // No refresh if the page isn't visible
  if (document.hidden) {
    return;
  }

  // No refresh if the player is paused
  if ( document.getElementById('dogplayer').paused ) {
    $("#display_infos").hide();
    $("#pauseimg").show();
    return;
  }

  // Ok, get the refresh infos
  $.getJSON("/metadata.php?wanted=json", function( obj ) {

    // If we already set this song infos, quit
    if ( current_song_id == obj['title_id'] ) {
      return;
    }
    current_song_id = obj['title_id'];

    // Set all the informations
    $("#album_title").html( obj['album']);
    $("#albumart").attr('src', obj['label_img'] );
    $("#link_album").attr('href', obj['album_url'] );
    $("#link_artist").attr('href', obj['artist_url']);
    $("#link_artist").html(obj['artist']);
    $("#link_song").attr('href', obj['song_url']);
    $("#link_song").html(obj['title']);

    // And display them
    $("#pauseimg").hide();
    $("#display_infos").show();

    navigator.mediaSession.metadata = new MediaMetadata({
      title: obj['title'],
      artist: obj['artist'],
      artwork: [{
          src: obj['label_img'],
          sizes: "96x96",
          type: "image/png"
        },
        {
          // Not the right size, but 256x256 is necessary for
          // Android device to display the artwork
          src: obj['label_img'],
          sizes: "256x256",
          type: "image/png"
        }
      ],
      album: obj['album'],
    }); // navigator.mediaSession.metadata

  }); // getJSON
}


// ---- REFRESH INFOS, when?
// at page load...
refreshInfos();

// refresh every X milliseconds
setInterval(function(){
  refreshInfos()
}, 5000); // 5 seconds

// and when we display the page (ex: switching tabs)
document.addEventListener("visibilitychange", () => {
  if (document.visibilityState === "visible") {
    refreshInfos();
  }
});

// ---- END REFRESH INFOS

</script>

</body>
</html>

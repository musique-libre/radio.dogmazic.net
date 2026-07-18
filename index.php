<?php
header("Access-Control-Allow-Origin: https://play.dogmazic.net");
$flux = 'https://radio.dogmazic.net:8001/stream.mp3';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Radio Dogmazic</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" id="metaDesc" content="Le compagnon musical de l'association Musique Libre : musique libre en continu, licence choisie par chaque artiste.">  <link rel="icon" href="/favicon.ico">
  <style>
    /* ---- Jetons de design ---------------------------------------------- */
    :root{
      --bg:#161419;          /* fond, plus riche que l'ancien #222 */
      --bg2:#201c24;
      --ink:#f2eee6;         /* texte principal (blanc chaud) */
      --muted:#9a93a0;       /* texte secondaire */
      --line:rgba(255,255,255,.09);
      --accent:#f06000;   /* orange réel du logo Dogmazic */       /* couleur-signal historique de la radio */
      --accent-2:#ff8536;
      --ember:#5f2600;       /* bas du spectre */
      --font-ui:system-ui,-apple-system,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;
      --font-mono:ui-monospace,"SF Mono","Cascadia Code","Roboto Mono",Menlo,Consolas,monospace;
    }

    *{box-sizing:border-box;}
    html,body{height:100%;}
    body{
      margin:0;
      font-family:var(--font-ui);
      color:var(--ink);
      background:
        radial-gradient(120% 90% at 50% 115%, rgba(240,96,0,.10), transparent 60%),
        linear-gradient(180deg,var(--bg2),var(--bg) 45%);
      background-attachment:fixed;
      min-height:100dvh;
      overflow-x:hidden;
    }

    /* Le spectre : bande pleine largeur ancrée en bas, derrière le contenu */
    #spectrum{
      position:fixed;
      left:0;right:0;bottom:0;
      width:100%;height:180px;
      z-index:0;
      pointer-events:none;
    }

    .radio{
      position:relative;
      z-index:1;
      display:flex;
      flex-direction:column;
      min-height:100dvh;
      padding:22px clamp(18px,5vw,48px) 40px;
    }

    /* ---- Barre haute ---------------------------------------------------- */
    .radio__top{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:16px;
    }
    .status{
      display:inline-flex;align-items:center;gap:9px;
      font-family:var(--font-mono);
      font-size:12px;letter-spacing:.18em;text-transform:uppercase;
      color:var(--muted);
    }
    .status__dot{
      width:9px;height:9px;border-radius:50%;
      background:var(--muted);
      box-shadow:0 0 0 0 rgba(240,96,0,.5);
    }
    .radio.is-live .status{color:var(--accent);}
    .radio.is-live .status__dot{
      background:var(--accent);
      animation:tally 1.8s ease-out infinite;
    }
    @keyframes tally{
      0%{box-shadow:0 0 0 0 rgba(240,96,0,.55);}
      100%{box-shadow:0 0 0 12px rgba(240,96,0,0);}
    }
    .top-right{display:inline-flex;align-items:center;gap:14px;}
    .lang{
      font-family:var(--font-mono);
      font-size:11px;letter-spacing:.14em;
      color:var(--muted);
      background:transparent;
      border:1px solid var(--line);
      border-radius:999px;
      padding:6px 12px;
      cursor:pointer;
      transition:color .15s ease,border-color .15s ease;
    }
    .lang:hover{color:var(--ink);border-color:rgba(255,255,255,.3);}
    .lang:focus-visible{outline:2px solid var(--accent-2);outline-offset:2px;}
    .brand{
      display:inline-flex;
      background:var(--ink);          /* plaque claire : le médaillon sombre redevient lisible */
      border-radius:14px;
      box-shadow:0 6px 18px rgba(0,0,0,.35);
      padding:9px;
    }
    /* On ne montre que le médaillon (haut ~78 % de l'image) ; le script orange,
       illisible sur clair, est masqué par le débordement. */
    a.brand{transition:box-shadow .2s ease;}
    a.brand:hover{box-shadow:0 6px 18px rgba(240,96,0,.45);}
    a.brand:focus-visible{outline:3px solid var(--accent-2);outline-offset:2px;}
    .brand__clip{width:50px;height:50px;overflow:hidden;line-height:0;}
    .brand__clip img{width:50px;height:auto;display:block;}

    /* ---- Zone centrale -------------------------------------------------- */
    .radio__stage{
      flex:1;
      display:flex;flex-direction:column;
      align-items:center;justify-content:center;
      text-align:center;
      gap:26px;
      padding:32px 0;
    }

    .art{
      --level:0;
      position:relative;
      width:clamp(180px,42vw,260px);
      aspect-ratio:1/1;
      border-radius:14px;
      overflow:hidden;
      background:#000;
      border:1px solid var(--line);
      box-shadow:
        0 18px 50px rgba(0,0,0,.55),
        0 0 calc(20px + var(--level) * 70px) rgba(240,96,0,calc(.10 + var(--level) * .45));
      transition:box-shadow .12s linear;
    }
    .art img{width:100%;height:100%;object-fit:cover;display:block;}
    /* Indice « lecture » sur la jaquette quand la radio est à l'arrêt */
    .art__hint{
      position:absolute;inset:0;
      display:flex;align-items:center;justify-content:center;
      background:rgba(15,14,18,.45);
      opacity:1;transition:opacity .25s ease;
      pointer-events:none;
    }
    .art__hint svg{
      width:64px;height:64px;fill:var(--ink);
      filter:drop-shadow(0 4px 14px rgba(0,0,0,.6));
    }
    .radio.is-live .art__hint{opacity:0;}

    .now{max-width:640px;display:flex;flex-direction:column;gap:6px;align-items:center;}
    .now__artist{
      font-size:clamp(26px,6vw,44px);
      font-weight:800;
      letter-spacing:-.02em;
      line-height:1.05;
      margin:0;
    }
    .now__artist a{color:var(--ink);text-decoration:none;}
    .now__artist a:hover{color:var(--accent-2);}
    .now__title{
      font-size:clamp(15px,2.6vw,19px);
      color:var(--accent);
      font-weight:600;
      margin:0;
    }
    .now__title a{color:inherit;text-decoration:none;}
    .now__title a:hover{text-decoration:underline;}
    .now__album{
      font-family:var(--font-mono);
      font-size:12px;letter-spacing:.04em;
      color:var(--muted);
      margin:2px 0 0;
    }
    .now__album a{color:inherit;text-decoration:none;}
    .now__album a:hover{color:var(--ink);}

    /* état par défaut / pause / démarrage */
    .now--idle .now__artist{color:var(--ink);}
    .now--idle .now__title,.now--idle .now__album{color:var(--muted);}

    /* ---- Commande de lecture ------------------------------------------- */
    .controls{display:flex;flex-direction:column;align-items:center;gap:18px;}
    .play{
      display:inline-flex;align-items:center;gap:12px;
      font-family:var(--font-mono);
      font-size:14px;letter-spacing:.14em;text-transform:uppercase;
      color:var(--bg);
      background:var(--accent);
      border:none;border-radius:999px;
      padding:15px 30px;
      cursor:pointer;
      transition:transform .12s ease,background .2s ease,box-shadow .2s ease;
      box-shadow:0 10px 30px rgba(240,96,0,.25);
    }
    .play:hover{background:var(--accent-2);transform:translateY(-1px);}
    .play:active{transform:translateY(0);}
    .play:focus-visible{outline:3px solid var(--accent-2);outline-offset:3px;}
    .play svg{width:16px;height:16px;fill:currentColor;}

    .volume{display:flex;align-items:center;gap:10px;color:var(--muted);}
    .volume svg{width:16px;height:16px;fill:currentColor;opacity:.8;}
    .volume input[type=range]{
      -webkit-appearance:none;appearance:none;
      width:130px;height:4px;border-radius:2px;
      background:rgba(255,255,255,.16);
      cursor:pointer;
    }
    .volume input[type=range]::-webkit-slider-thumb{
      -webkit-appearance:none;appearance:none;
      width:14px;height:14px;border-radius:50%;
      background:var(--accent);border:none;
    }
    .volume input[type=range]::-moz-range-thumb{
      width:14px;height:14px;border-radius:50%;
      background:var(--accent);border:none;
    }

    .animtoggle{
      font-family:var(--font-mono);
      font-size:11px;letter-spacing:.06em;
      color:var(--muted);
      background:none;border:none;cursor:pointer;
      text-decoration:underline dotted;
      padding:2px 4px;
    }
    .animtoggle:hover{color:var(--ink);}
    .animtoggle:focus-visible{outline:2px solid var(--accent-2);outline-offset:2px;}

    .fallback{color:var(--muted);font-size:13px;}
    .fallback a{color:var(--accent);}

    /* ---- Pied ----------------------------------------------------------- */
    .radio__foot{
      text-align:center;
      font-family:var(--font-mono);
      font-size:11px;letter-spacing:.06em;line-height:1.75;
      color:var(--muted);
    }
    .radio__foot a{color:var(--muted);text-decoration:none;}
    .radio__foot a:hover{color:var(--ink);}

    @media (prefers-reduced-motion:reduce){
      .radio.is-live .status__dot{animation:none;}
      .art{transition:none;}
    }
  </style>
</head>

<body>
  <canvas id="spectrum" aria-hidden="true"></canvas>

  <main class="radio" id="radio">
    <header class="radio__top">
      <span class="status" id="status">
        <span class="status__dot"></span><span id="statusText">Hors antenne</span>
      </span>
      <span class="top-right">
        <button class="lang" id="langBtn" type="button" aria-label="Switch to English">EN</button>
        <a class="brand" id="brandLink" href="https://play.dogmazic.net" target="_blank" rel="noopener"><span class="brand__clip"><img src="/Logo-DGZ-TRANSPARENT.png" alt="Dogmazic" width="400" height="515"></span></a>
      </span>
    </header>

    <section class="radio__stage">
      <div class="art" id="art">
        <a id="linkAlbum" href="#" target="_blank" rel="noopener" title="Voir l'album sur Dogmazic">
          <img id="albumart" src="/blank_album_art.png?v=2" alt="Pochette de l'album" width="260" height="260">
        </a>
        <span class="art__hint" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg></span>
      </div>

      <div class="now now--idle" id="now">
        <h1 class="now__artist" id="artistWrap"><a id="linkArtist" href="#" target="_blank" rel="noopener">Radio Dogmazic</a></h1>
        <p class="now__title" id="titleWrap"><a id="linkSong" href="#" target="_blank" rel="noopener">Musique libre, en continu</a></p>
        <p class="now__album"><span id="albumPre">extrait de «&nbsp;</span><a id="albumTitle" href="#" target="_blank" rel="noopener">—</a><span id="albumPost">&nbsp;»</span></p>
      </div>

      <div class="controls">
        <button class="play" id="play" type="button" aria-pressed="false" aria-label="Écouter la radio">
          <svg id="playIcon" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 5v14l12-7z"/></svg>
          <span id="playLabel">Écouter</span>
        </button>

        <div class="volume">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3a4.5 4.5 0 0 0-2.5-4v8a4.5 4.5 0 0 0 2.5-4z"/></svg>
          <input type="range" id="volume" min="0" max="1" step="0.01" value="0.9" aria-label="Volume">
        </div>

        <button class="animtoggle" id="animBtn" type="button" aria-pressed="false"></button>

        <p class="fallback">
          <span id="fbText">Lecteur HTML5 indisponible ? </span><a id="fbLink" href="<?php echo $flux; ?>">Flux direct</a><span id="fbEnd">.</span>
        </p>
      </div>
    </section>

    <footer class="radio__foot">
      <span id="footLine1">Musique libre · licence choisie par chaque artiste</span><br>
      <span id="footLine2">Le compagnon musical de l’</span><a id="footAssoc" href="https://musique-libre.org" target="_blank" rel="noopener">association Musique&nbsp;Libre</a>
      · <a id="footLib" href="https://play.dogmazic.net" target="_blank" rel="noopener">Bibliothèque Dogmazic</a>
    </footer>
  </main>

  <audio id="player" preload="none">
    <source src="<?php echo $flux; ?>" type="audio/mpeg">
  </audio>

<script>
"use strict";

/* Passe à true dans preview.html (aucune modif nécessaire ici). */
var PREVIEW = false;

var CFG = {
  metadataUrl: "/metadata.php?wanted=json",
  pollMs: 5000,
  useWebAudio: false   /* IMPORTANT — laisser à false tant qu'Icecast (:8001) n'envoie pas
                          d'en-têtes CORS. Le flux est cross-origin (port différent) : routé
                          dans Web Audio sans CORS, il est réduit AU SILENCE par le navigateur,
                          et c'est irréversible pour la session. Pour activer le vrai spectre :
                          1) en-têtes CORS côté Icecast, 2) crossorigin="anonymous" sur <audio>,
                          3) repasser ce drapeau à true. Le spectre synthétique reste actif. */
};

/* Préférence utilisateur : animation du spectre (persistée localement) */
var animOn = true;
try { animOn = localStorage.getItem("dgz-anim") !== "off"; } catch(e){}

var $ = function(id){ return document.getElementById(id); };

/* ------------------------------------------------------------------ */
/*  Internationalisation (FR par défaut, EN via bouton ou ?lang=en)    */
/* ------------------------------------------------------------------ */
var I18N = {
  fr: {
    offAir:"Hors antenne", live:"En direct", paused:"En pause",
    listen:"Écouter", pause:"Pause",
    ariaListen:"Écouter la radio", ariaPause:"Mettre en pause",
    idleTitle:"Musique libre, en continu",
    albumPre:"extrait de «\u00a0", albumPost:"\u00a0»",
    albumLinkTitle:"Voir cet album sur Dogmazic",
    coverAlt:"Pochette de l’album",
    fallback:"Lecteur HTML5 indisponible ? ", fallbackLink:"Flux direct", fallbackEnd:".",
    footLine1:"Musique libre · licence choisie par chaque artiste",
    footLine2:"Le compagnon musical de l’", footAssoc:"association Musique\u00a0Libre", footLib:"Bibliothèque Dogmazic",
    brandLink:"Ouvrir la Bibliothèque Dogmazic",
    metaDesc:"Le compagnon musical de l’association Musique Libre : musique libre en continu, licence choisie par chaque artiste.",
    langBtn:"EN", langBtnAria:"Switch to English", volume:"Volume",
    animStop:"Couper l\u2019animation", animStart:"R\u00e9activer l\u2019animation",
    connecting:"Connexion\u2026"
  },
  en: {
    offAir:"Off air", live:"On air", paused:"Paused",
    listen:"Listen", pause:"Pause",
    ariaListen:"Listen to the radio", ariaPause:"Pause playback",
    idleTitle:"Free music, around the clock",
    albumPre:"from \u201c", albumPost:"\u201d",
    albumLinkTitle:"View this album on Dogmazic",
    coverAlt:"Album cover",
    fallback:"HTML5 player not working? ", fallbackLink:"Direct stream", fallbackEnd:".",
    footLine1:"Free music · each artist chooses their own license",
    footLine2:"A musical companion by the ", footAssoc:"Musique\u00a0Libre association", footLib:"Dogmazic Music Library",
    brandLink:"Open the Dogmazic Music Library",
    metaDesc:"Musique Libre’s musical companion: free music around the clock, each artist chooses their own license.",
    langBtn:"FR", langBtnAria:"Passer en fran\u00e7ais", volume:"Volume",
    animStop:"Turn animation off", animStart:"Turn animation on",
    connecting:"Connecting\u2026"
  }
};

var lang = "fr";
try {
  var qs = new URLSearchParams(location.search).get("lang");
  lang = (qs || (navigator.language || "fr")).slice(0,2).toLowerCase() === "en" ? "en" : "fr";
} catch(e){}

function T(key){ return I18N[lang][key]; }

function setLang(l){
  lang = (l === "en") ? "en" : "fr";
  document.documentElement.lang = lang;
  $("metaDesc").setAttribute("content", T("metaDesc"));
  $("albumPre").textContent  = T("albumPre");
  $("albumPost").textContent = T("albumPost");
  $("linkAlbum").title = T("albumLinkTitle");
  $("albumart").alt = T("coverAlt");
  $("fbText").textContent = T("fallback");
  $("fbLink").textContent = T("fallbackLink");
  $("fbEnd").textContent  = T("fallbackEnd");
  $("footLine1").textContent = T("footLine1");
  $("footLine2").textContent = T("footLine2");
  $("footAssoc").textContent = T("footAssoc");
  $("footLib").textContent   = T("footLib");
  $("langBtn").textContent = T("langBtn");
  $("langBtn").setAttribute("aria-label", T("langBtnAria"));
  $("volume").setAttribute("aria-label", T("volume"));
  $("brandLink").title = T("brandLink");
  $("brandLink").setAttribute("aria-label", T("brandLink"));
  updateAnimBtn();
  if ($("now").classList.contains("now--idle")) $("linkSong").textContent = T("idleTitle");
  setPlayingUI(!player.paused);   /* réapplique statut + bouton dans la nouvelle langue */
}

var radio   = $("radio");
var player  = $("player");
var playBtn = $("play");

/* ------------------------------------------------------------------ */
/*  Lecture                                                            */
/* ------------------------------------------------------------------ */
var hasStarted = false;
function setPlayingUI(on){
  radio.classList.toggle("is-live", on);
  if (on) hasStarted = true;
  $("statusText").textContent = on ? T("live") : (hasStarted ? T("paused") : T("offAir"));
  $("playLabel").textContent  = on ? T("pause") : T("listen");
  playBtn.setAttribute("aria-pressed", on ? "true" : "false");
  playBtn.setAttribute("aria-label", on ? T("ariaPause") : T("ariaListen"));
  $("linkAlbum").title = on ? T("albumLinkTitle") : T("ariaListen");
  $("playIcon").innerHTML = on
    ? '<path d="M6 5h4v14H6zM14 5h4v14h-4z"/>'
    : '<path d="M7 5v14l12-7z"/>';
}

var everLoaded = false, pausedAt = 0;
var RELOAD_AFTER_MS = 30000;   /* longue pause : on recharge pour revenir au direct */

playBtn.addEventListener("click", function(){
  ensureAudioGraph();
  if (player.paused){
    /* Charger une seule fois. NB : avec une balise <source>, player.src reste vide,
       tester dessus rechargeait le flux à CHAQUE reprise (re-buffering complet). */
    if (!everLoaded){
      player.load();
      everLoaded = true;
    } else if (pausedAt && (Date.now() - pausedAt) > RELOAD_AFTER_MS){
      player.load();               /* retour au direct après une longue pause */
    }
    player.play().catch(function(){ /* l'utilisateur peut relancer */ });
  } else {
    player.pause();
  }
});

player.addEventListener("play",  function(){
  setPlayingUI(true);
  if (player.readyState < 3) $("statusText").textContent = T("connecting");
  refreshInfos();
});
/* la lecture démarre (ou reprend après un creux de buffering) réellement ici */
player.addEventListener("playing", function(){ $("statusText").textContent = T("live"); });
player.addEventListener("waiting", function(){ $("statusText").textContent = T("connecting"); });
player.addEventListener("pause", function(){
  pausedAt = Date.now();
  setPlayingUI(false);
  refreshInfos();
});

$("volume").addEventListener("input", function(e){ player.volume = parseFloat(e.target.value); });
player.volume = 0.9;

/* Jaquette : à l'arrêt, un clic lance la lecture ; en cours de lecture,
   le lien vers l'album garde son comportement normal. */
$("linkAlbum").addEventListener("click", function(e){
  if (player.paused){
    e.preventDefault();
    playBtn.click();
  }
});

/* ------------------------------------------------------------------ */
/*  Métadonnées (même contrat que metadata.php?wanted=json)            */
/* ------------------------------------------------------------------ */
var currentSongId = null;

function applyIdle(){
  $("now").classList.add("now--idle");
  $("linkSong").textContent = T("idleTitle");
  currentSongId = null;
  document.title = "Radio Dogmazic";
}

$("langBtn").addEventListener("click", function(){ setLang(lang === "fr" ? "en" : "fr"); });
setLang(lang);   /* applique la langue détectée (navigateur ou ?lang=) */

/* Décline l'URL de pochette (image.php) dans une taille donnée. */
function artURL(u, size){
  if (!u || u.indexOf("data:") === 0) return u;   /* ne pas altérer une image en data: URI */
  return /size=\d+x\d+/i.test(u)
    ? u.replace(/size=\d+x\d+/i, "size=" + size)
    : u + (u.indexOf("?") < 0 ? "?" : "&") + "size=" + size;
}

function applyTrack(o){
  $("now").classList.remove("now--idle");
  document.title = "▶ " + o.artist + " — " + o.title + " · Radio Dogmazic";

  if (currentSongId === o.title_id) return;   /* pas de reflow inutile */
  currentSongId = o.title_id;

  $("linkArtist").textContent = o.artist;
  $("linkArtist").href = o.artist_url;
  $("linkSong").textContent = o.title;
  $("linkSong").href = o.song_url;
  $("albumTitle").textContent = o.album;
  $("albumTitle").href = o.album_url;
  $("linkAlbum").href = o.album_url;
  $("albumart").src = artURL(o.label_img, "512x512");

  if ("mediaSession" in navigator){
    navigator.mediaSession.metadata = new MediaMetadata({
      title:o.title, artist:o.artist, album:o.album,
      artwork:[
        {src:artURL(o.label_img,"96x96"),   sizes:"96x96",   type:"image/png"},
        {src:artURL(o.label_img,"192x192"), sizes:"192x192", type:"image/png"},
        {src:artURL(o.label_img,"256x256"), sizes:"256x256", type:"image/png"},
        {src:artURL(o.label_img,"512x512"), sizes:"512x512", type:"image/png"}
      ]
    });
  }
}

function refreshInfos(){
  if (PREVIEW) return;                 /* preview.html gère ses propres données */
  if (document.hidden) return;
  if (player.paused){ applyIdle(); return; }

  fetch(CFG.metadataUrl, {cache:"no-store"})
    .then(function(r){ return r.ok ? r.text() : Promise.reject(); })
    .then(function(txt){
      var o;
      try { o = JSON.parse(txt); } catch(e){ o = null; }   /* "No music" -> texte non-JSON */
      if (o && o.title) applyTrack(o);
      else applyIdle();
    })
    .catch(function(){ /* on garde le dernier état connu */ });
}

if (!PREVIEW){
  refreshInfos();
  setInterval(refreshInfos, CFG.pollMs);
  document.addEventListener("visibilitychange", function(){
    if (document.visibilityState === "visible") refreshInfos();
  });
}

/* ------------------------------------------------------------------ */
/*  Spectre audio — élément signature                                  */
/*  Vrai spectre via Web Audio si le flux est CORS-friendly,           */
/*  sinon repli sur une animation synthétique (jamais cassé).          */
/* ------------------------------------------------------------------ */
var canvas = $("spectrum");
var ctx2d  = canvas.getContext("2d");
var artEl  = $("art");
var reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

var audioCtx=null, analyser=null, freq=null, srcNode=null, graphReady=false;
var bars=[], targets=[], barCount=0, dpr=1, silentFrames=0, useSim=false;

function sizeCanvas(){
  dpr = Math.min(window.devicePixelRatio || 1, 2);
  canvas.width  = Math.floor(canvas.clientWidth  * dpr);
  canvas.height = Math.floor(canvas.clientHeight * dpr);
  barCount = Math.max(24, Math.min(96, Math.floor(canvas.clientWidth / 14)));
  if (barCount % 2) barCount--;
  bars = new Array(barCount).fill(0);
  targets = new Array(barCount).fill(0);
}
window.addEventListener("resize", function(){
  sizeCanvas();
  if (rafId === null) renderStatic();   /* boucle arrêtée : on repeint une frame */
});
sizeCanvas();

function ensureAudioGraph(){
  if (graphReady || PREVIEW) return;
  /* Double sécurité : même drapeau à true, on ne route jamais un flux
     sans attribut crossorigin — sinon le navigateur coupe le son. */
  if (!CFG.useWebAudio || !player.crossOrigin){ useSim = true; return; }
  try{
    audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    analyser = audioCtx.createAnalyser();
    analyser.fftSize = 512;
    analyser.smoothingTimeConstant = 0.82;
    freq = new Uint8Array(analyser.frequencyBinCount);
    srcNode = audioCtx.createMediaElementSource(player);
    srcNode.connect(analyser);
    analyser.connect(audioCtx.destination);  /* sinon le son est coupé */
    graphReady = true;
  }catch(e){ useSim = true; }               /* pas de Web Audio -> synthétique */
  if (audioCtx && audioCtx.state === "suspended") audioCtx.resume();
}

function computeTargets(t){
  var half = barCount / 2;

  var haveData = false;
  if (graphReady && !useSim){
    analyser.getByteFrequencyData(freq);
    var sum=0, usable=Math.floor(freq.length*0.72);
    for (var k=0;k<half;k++){
      var idx = Math.floor(k/half * usable);
      var v = freq[idx]/255;
      sum += freq[idx];
      var val = 0.05 + v*0.95;
      targets[half + k]     = val;          /* moitié droite  */
      targets[half - 1 - k] = val;          /* miroir gauche  */
    }
    haveData = sum > 0;
    /* Flux non-CORS => données à zéro : on bascule en synthétique */
    if (!haveData){ silentFrames++; if (silentFrames > 45) useSim = true; }
    else silentFrames = 0;
    if (haveData) return;
  }

  /* Mode synthétique : quelques oscillateurs pour un rendu « musical » */
  for (var j=0;j<half;j++){
    var env = Math.pow(1 - j/half, 0.7);    /* plus d'énergie dans les graves */
    var wob = 0.5 + 0.5*Math.sin(t/260 + j*0.55)
                  * Math.sin(t/970 + j*0.2);
    var beat = 0.15*Math.max(0, Math.sin(t/430));
    var val2 = 0.06 + env*(0.55*wob + beat) + Math.random()*0.05;
    targets[half + j]     = val2;
    targets[half - 1 - j] = val2;
  }
}

function paint(){
  var W=canvas.width, H=canvas.height;
  ctx2d.clearRect(0,0,W,H);
  var gap = 3*dpr;
  var bw = (W - gap*(barCount-1)) / barCount;
  for (var b=0;b<barCount;b++){
    var h = Math.max(2*dpr, bars[b]*H*0.92);
    var x = b*(bw+gap);
    var y = H - h;
    var g = ctx2d.createLinearGradient(0, H, 0, y);
    g.addColorStop(0, "rgba(95,38,0,.35)");
    g.addColorStop(0.55, "rgba(240,96,0,.85)");
    g.addColorStop(1, "#ff8536");
    ctx2d.fillStyle = g;
    ctx2d.fillRect(x, y, bw, h);
  }
}

function step(t){
  computeTargets(t);
  var level = 0;
  for (var i=0;i<barCount;i++){
    bars[i] += (targets[i] - bars[i]) * 0.32;
    level += bars[i];
  }
  artEl.style.setProperty("--level", (level/barCount).toFixed(3));
  paint();
}

/* Une seule frame, au repos : spectre bas, lueur éteinte. */
function renderStatic(){
  for (var i=0;i<barCount;i++) bars[i] = 0.05 + 0.02*Math.sin(i*0.5);
  artEl.style.setProperty("--level","0");
  paint();
}

/* ---- Contrôleur d'animation (éco CPU) ------------------------------ */
/* La boucle ne tourne que si : lecture en cours, page visible,         */
/* animation non coupée par l'utilisateur, pas de motion réduit.        */
/* ~30 i/s pendant la lecture : suffisant pour un spectre.              */
var FRAME_MS = 33;
var rafId = null, lastFrame = -1e9;

function isPlaying(){
  if (PREVIEW) return (typeof previewPlaying !== "undefined") && previewPlaying;
  return !player.paused;
}
function shouldAnimate(){
  return animOn && !reduce && !document.hidden && isPlaying();
}

function frame(t){
  if (!shouldAnimate()){ rafId = null; renderStatic(); return; }
  if (t - lastFrame >= FRAME_MS){ lastFrame = t; step(t); }
  rafId = requestAnimationFrame(frame);
}

/* (Re)démarre ou arrête la boucle selon l'état courant. */
function kick(){
  if (shouldAnimate()){
    if (rafId === null) rafId = requestAnimationFrame(frame);
  } else if (rafId === null){
    renderStatic();
  }
}

player.addEventListener("play",  kick);
player.addEventListener("pause", kick);
/* Écran éteint ou onglet caché : la boucle s'arrête, l'audio continue. */
document.addEventListener("visibilitychange", kick);

function updateAnimBtn(){
  $("animBtn").textContent = animOn ? T("animStop") : T("animStart");
  $("animBtn").setAttribute("aria-pressed", animOn ? "false" : "true");
}
$("animBtn").addEventListener("click", function(){
  animOn = !animOn;
  try { localStorage.setItem("dgz-anim", animOn ? "on" : "off"); } catch(e){}
  updateAnimBtn();
  kick();
});

kick();   /* état initial : rendu statique tant que rien ne joue */
</script>
</body>
</html>

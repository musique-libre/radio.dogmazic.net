<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output omit-xml-declaration="no" method="html" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" indent="yes" encoding="UTF-8" />
<xsl:template match="/icestats" >

<html>
<head>
   <title>Now Playing...</title>
   <link rel="stylesheet" href="//play.dogmazic.net/themes/reborn/templates/default.css" type="text/css" media="screen" />
   <link rel="stylesheet" href="//play.dogmazic.net/themes/reborn/templates/dark.css" type="text/css" media="screen" />
   
   <meta http-equiv="Pragma" content="no-cache" />
   <meta http-equiv="Expires" content="-1" />
   <meta http-equiv="refresh" content="5" />
   
   <style type="text/css">
    #song_info span {
        text-overflow: ellipsis;
        max-width: 200px;
        overflow: hidden;
        display: inline-block;
        white-space: nowrap;
        padding-right: 4px;
        padding-left: 4px;
    }
    
    #listeners {
        float: right;
    }
   </style>
</head>
<body>

<xsl:for-each select="source">
  <xsl:if test="title">
     <span id="song_info">
         <span><a href="//radio.dogmazic.net/metadata.php?wanted=artist_go" title="Show this artist on Dogmazic" id="idartist" target="_blank"><xsl:value-of select="artist" /></a></span>
         <span>-</span>
         <span><a href="//radio.dogmazic.net/metadata.php?wanted=song_go" title="Show this song on Dogmazic" id="idtitle" target="_blank"><xsl:value-of select="title" /></a></span>
         <script type="text/javascript">
             var artist='<xsl:value-of select="artist" />'.trim();
             var title='<xsl:value-of select="title" />'.trim();

             // Handle "special" chars.
             // Icecast give some chars in HTML (like "徒 setto セ")
             // So we need to decode HTML before handling them
             // Debug example:
             // var title='&#24466; Setto &#12475;&#12483;&#12488; - Menomoia'.trim();
             var textArea = document.createElement('textarea');
             textArea.innerHTML = artist;
             artist = textArea.value;
             textArea.innerHTML = title;
             title = textArea.value;

             // Handle when everything is just in the "title"
             if ( artist.length == 0 ) {
                 const regex = /(.*) - (.*)/;
                 const found = title.match(regex);
                 if (found.length == 3 ) {
                     artist = found[1]
                     title = found[2]
                 }
             }

             // Now that everything is clean, go with if
             document.getElementById('idartist').innerHTML = artist;
             document.getElementById('idtitle').innerHTML = title; // Handle UTF-8

         </script>
     </span>
  </xsl:if>
</xsl:for-each>

<span id="listeners">Listeners: <xsl:value-of select="sum(source/listeners)"/></span>

</body>
</html>

</xsl:template>
</xsl:stylesheet>

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
        max-width: 300px;
        overflow: hidden;
        display: inline-block;
        white-space: nowrap;
        padding-right: 4px;
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
         <span><a href="#" onClick="goSearchArtist();" title="Show this artist on Dogmazic" id="idartist"><xsl:value-of select="artist" /></a></span>
         <span>-</span>
         <span><a href="#" onClick="goSearchSong();" title="Show this song on Dogmazic" id="idtitle"><xsl:value-of select="title" /></a></span>
         <script type="text/javascript">
             var artist='<xsl:value-of select="artist" />'.trim();
             var title='<xsl:value-of select="title" />'.trim();

             // Handle when everything is just in the "title"
             if ( artist.length == 0 ) {
                 const regex = /(.*) - (.*)/;
                 const found = title.match(regex);
                 if (found.length == 3 ) {
                     artist = found[1]
                     title = found[2]
                 }
             }
             artist = decodeURI(artist);
             title = decodeURI(title);

             document.getElementById('idartist').innerHTML = artist;
             document.getElementById('idtitle').innerHTML = title; // Handle UTF-8

             
             function goSearchArtist() {
                window.open('//play.dogmazic.net/search.php?type=song&amp;action=search&amp;type=artist&amp;rule_1_operator=0&amp;rule_1=name&amp;rule_1_input=' + encodeURI(artist));
             }
             
             function goSearchSong() {
                window.open('//play.dogmazic.net/search.php?type=song&amp;action=search&amp;type=song&amp;rule_1_operator=0&amp;rule_1=artist&amp;rule_1_input=' + encodeURI(artist) + '&amp;rule_2_operator=0&amp;rule_2=title&amp;rule_2_input=' + encodeURI(title));
             }
         </script>
     </span>
  </xsl:if>
</xsl:for-each>

<span id="listeners">Listeners: <xsl:value-of select="sum(source/listeners)"/></span>

</body>
</html>

</xsl:template>
</xsl:stylesheet>

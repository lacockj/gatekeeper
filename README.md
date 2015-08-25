# gatekeeper #
Discourage the direct download of certain files by only allowing access through a time-sensitive, two-step process.

## Usage ##

1. On your server, create a folder in which you will keep your protected files. I called my 'vault'.
2. Be sure to add the 'vault/.htaccess' file to that folder. It reads "deny from all", indicating no access from anyone outside the server.
3. Configure the 'gatekeeper.php' script to point to the file vault, relative to the gatekeeper's location. (i.e. '../vault/')
4. In the HTML, do not include the usual 'src' attibute, but add the 'protected-source' class, and a 'data-src' attribute.
5. When refering to protected files in your HTML, do not include the file's path, only it's name. The gatekeeper is already looking in the file vault folder.

<pre>
&lt;body>
  &lt;audio>
    &lt;source class="protected-source" type="audio/mpeg" data-src="abcdefg.mp3">
  &lt;/audio>
&lt;/body>
&lt;script>
keymaster = new namespace.Keymaster();

activateProtectedSources = function(){
  $('.protected-source').each(function(){
    keymaster.getFileToken($(this).data('src'), activateProtectedSourcesPartTwo, this);
  });
}

activateProtectedSourcesPartTwo = function( response ){
  this.setAttribute('src', response);
  $(this).parent().load();
}

activateProtectedSources();
&lt;/script>
</pre>

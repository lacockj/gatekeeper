// Your Namespace //
if (typeof namespace === 'undefined') namespace = {};

namespace.Keymaster = function(){
  this.getFileToken = function( fileName, callback, bindToThis ){
    if (bindToThis) {
      $.get('api/gatekeeper.php', {file: fileName}, callback.bind(bindToThis), 'json')
    } else {
      $.get('api/gatekeeper.php', {file: fileName}, callback, 'json')
    }
  }
}

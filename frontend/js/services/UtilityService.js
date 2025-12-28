/* UtilityService.js - Shared utility functions */
var UtilityService = {
  // Show alert notification
  showAlert: function(container, message, type) {
    var $c = $(container);
    var $alert = $('<div class="alert alert-' + (type || 'info') + ' alert-dismissible" role="alert">' + message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
    $c.prepend($alert);
    setTimeout(function() {
      $alert.alert('close');
    }, 3000);
  },

  // Get backend candidates
  getBackendCandidates: function() {
    var p = window.location.pathname || '/';
    var candidates = [];
    try {
      var idx = p.indexOf('/frontend');
      if (idx !== -1) candidates.push(p.substring(0, idx) + '/backend');
    } catch (e) {}
    try {
      var removed = p.replace(/\/[^\/]*$/, '');
      if (removed && removed !== p) candidates.push(removed + '/backend');
    } catch (e) {}
    candidates.push('/WebProgramming/WebProgramming/backend');
    candidates.push('/WebProgramming/backend');
    candidates.push('/backend');
    var seen = {};
    return candidates
      .map(function(c) {
        return c.replace(/\/+$/, '');
      })
      .filter(function(c) {
        if (seen[c]) return false;
        seen[c] = 1;
        return true;
      });
  },

  // AJAX with fallback
  ajaxWithFallback: function(opts) {
    var candidates = this.getBackendCandidates();
    var i = 0;
    var self = this;
    
    function tryNext() {
      if (i >= candidates.length) {
        if (opts.error) opts.error({ message: 'All backend candidates failed' });
        return;
      }
      var base = candidates[i++];
      var url = base + opts.path;
      console.debug('Trying backend URL:', url);
      
      // Build ajax options with auth token if available
      var ajaxOptions = $.extend({}, opts.ajaxOpts, {
        url: url
      });
      
      // Add Authorization header with JWT token
      var token = localStorage.getItem('token');
      if (token) {
        ajaxOptions.headers = ajaxOptions.headers || {};
        ajaxOptions.headers['Authorization'] = 'Bearer ' + token;
      }
      
      $.ajax(ajaxOptions)
        .done(function() {
          if (opts.success) opts.success.apply(this, arguments);
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
          var status = jqXHR && jqXHR.status ? jqXHR.status : 0;
          console.warn('Request failed for', url, textStatus, status);
          if (status === 0 || status === 404) {
            tryNext();
          } else {
            if (opts.error) opts.error(jqXHR);
          }
        });
    }
    tryNext();
  },

  // Escape HTML to prevent XSS
  escapeHtml: function(text) {
    if (text === null || text === undefined) return '';
    return String(text).replace(/[&<>"'`]/g, function(ch) {
      return {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
        '`': '&#96;'
      }[ch];
    });
  }
};

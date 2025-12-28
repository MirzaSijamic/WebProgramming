/* PlaylistService.js - Playlist service */
var PlaylistService = {
  // Load playlists
  loadPlaylists: function() {
    var $area = $('.row.playlist-area');
    if (!$area.length) return;
    console.log('PlaylistService.loadPlaylists: loading playlists for current user');

    var userStr = localStorage.getItem('user');
    if (!userStr) {
      console.warn(
        'PlaylistService.loadPlaylists: no localStorage.user found — attempting to load all playlists'
      );
      PlaylistService.loadAllPlaylists($area);
      return;
    }

    var user = {};
    try {
      user = JSON.parse(userStr);
    } catch (e) {
      user = {};
    }
    var userId = user.id || user.user_id || null;
    if (!userId) {
      UtilityService.showAlert('.playlist-section', 'Unable to determine logged-in user id', 'warning');
      return;
    }

    PlaylistService.loadPlaylistsByUser(userId, $area);
  },

  // Load all playlists
  loadAllPlaylists: function($area) {
    UtilityService.ajaxWithFallback({
      path: '/playlists',
      ajaxOpts: { method: 'GET', dataType: 'json' },
      success: function(resp) {
        var playlists = resp.data || resp || [];
        if (!Array.isArray(playlists)) playlists = [playlists];
        $area.empty();
        playlists.forEach(function(p, idx) {
          var html = PlaylistService.renderPlaylistItem(p, idx);
          $area.append(html);
        });
      },
      error: function(err) {
        console.error('PlaylistService.loadAllPlaylists failed', err);
        UtilityService.showAlert('.playlist-section', 'Failed to load playlists', 'danger');
      }
    });
  },

  // Load playlists by user
  loadPlaylistsByUser: function(userId, $area) {
    UtilityService.ajaxWithFallback({
      path: '/playlists/user/' + encodeURIComponent(userId),
      ajaxOpts: { method: 'GET', dataType: 'json' },
      success: function(resp) {
        var playlists = resp.data || resp || [];
        if (!Array.isArray(playlists)) playlists = [playlists];
        $area.empty();
        playlists.forEach(function(p, idx) {
          var html = PlaylistService.renderPlaylistItem(p, idx);
          $area.append(html);
        });
      },
      error: function(err) {
        console.error('PlaylistService.loadPlaylistsByUser failed', err);
        UtilityService.showAlert('.playlist-section', 'Failed to load playlists from server', 'danger');
      }
    });
  },

  // Render single playlist item
  renderPlaylistItem: function(p, idx) {
    var img = p.image || 'img/playlist/' + (idx % 15 + 1) + '.jpg';
    var title = p.title || p.name || 'Untitled';
    var cssClass = 'mix col-lg-3 col-md-4 col-sm-6 genres';
    var html =
      '<div class="' +
      cssClass +
      '">' +
      '<div class="playlist-item">' +
      '<img src="' +
      img +
      '" alt="">' +
      '<h5>' +
      UtilityService.escapeHtml(title) +
      '</h5>' +
      '</div>' +
      '</div>';
    return html;
  }
};

/* TrackService.js - Track/Category service */
var TrackService = {
  // Load tracks
  loadTracks: function() {
    var $container = $('.songs-section .container');
    if (!$container.length) return;
    console.log('TrackService.loadTracks: loading tracks from backend');

    // add a visible loader so user sees activity
    var $loader = $container.find('.category-loading');
    if (!$loader.length) {
      $loader = $(
        '<div class="category-loading" style="padding:14px; text-align:center;">Loading tracks…</div>'
      );
      $container.prepend($loader);
    }

    UtilityService.ajaxWithFallback({
      path: '/tracks',
      ajaxOpts: { method: 'GET', dataType: 'json' },
      success: function(resp) {
        $loader.remove();
        var tracks = resp.data || resp || [];
        if (!Array.isArray(tracks)) tracks = [tracks];

        // remove existing song-item blocks only when we have real data
        if (tracks.length) {
          $container.find('.song-item').remove();
        }

        var $pagination = $container.find('.site-pagination');
        tracks.forEach(function(t, idx) {
          var html = TrackService.renderTrackItem(t, idx);
          if ($pagination.length) {
            $pagination.before(html);
          } else {
            $container.append(html);
          }
        });
      },
      error: function(err) {
        $loader.remove();
        console.error('TrackService.loadTracks: failed to load tracks', err);
        UtilityService.showAlert('.songs-section', 'Failed to load tracks from server', 'danger');
      }
    });
  },

  // Render single track item
  renderTrackItem: function(t, idx) {
    var artists = t.artists || t.artist || 'Unknown';
    var title = t.name || t.title || 'Untitled';
    var img = t.image || 'img/songs/' + ((idx % 8) + 1) + '.jpg';
    var preview = t.preview_url || t.external_url || 'music-files/1.mp3';
    var jpAncestor = '.jp_container_cat_' + idx;

    var html =
      '\n<div class="song-item">\n  <div class="row">\n    <div class="col-lg-4">\n      <div class="song-info-box">\n        <img src="' +
      img +
      '" alt="">\n        <div class="song-info">\n          <h4>' +
      UtilityService.escapeHtml(artists) +
      '</h4>\n          <p>' +
      UtilityService.escapeHtml(title) +
      '</p>\n        </div>\n      </div>\n    </div>\n    <div class="col-lg-6">\n      <div class="single_player_container">\n        <div class="single_player">\n          <div class="jp-jplayer jplayer" data-ancestor="' +
      jpAncestor +
      '" data-url="' +
      preview +
      '"></div>\n          <div class="jp-audio ' +
      jpAncestor.replace('.', '').trim() +
      '" role="application" aria-label="media player">\n            <div class="jp-gui jp-interface">\n              <div class="player_controls_box">\n                <button class="jp-prev player_button" tabindex="0"></button>\n                <button class="jp-play player_button" tabindex="0"></button>\n                <button class="jp-next player_button" tabindex="0"></button>\n                <button class="jp-stop player_button" tabindex="0"></button>\n              </div>\n              <div class="player_bars">\n                <div class="jp-progress">\n                  <div class="jp-seek-bar">\n                    <div>\n                      <div class="jp-play-bar"><div class="jp-current-time" role="timer" aria-label="time">0:00</div></div>\n                    </div>\n                  </div>\n                </div>\n                <div class="jp-duration ml-auto" role="timer" aria-label="duration">00:00</div>\n              </div>\n            </div>\n          </div>\n        </div>\n      </div>\n    </div>\n    <div class="col-lg-2">\n      <div class="songs-links">\n        <a href=""><img src="img/icons/p-1.png" alt=""></a>\n        <a href=""><img src="img/icons/p-2.png" alt=""></a>\n        <a href=""><img src="img/icons/p-3.png" alt=""></a>\n      </div>\n    </div>\n  </div>\n</div>\n';

    return html;
  }
};

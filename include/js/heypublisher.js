// Javascript library for HeyPublisher Wordpress plugin
//
// Copyright (c) 2010-2014 Loudlever, Inc.
// Copyright (c) 2014-2017 Richard Luck
// Author - Richard Luck <richard@heypublisher.com>
(function( HeyPublisher, $, undefined ) {

  HeyPublisher.clickCheck = function(src,dest) {
    var elem = $('#' + dest);
    console.log("src: ", $(src).attr('checked'), ": dest: ", dest);
    if ($(src).attr('checked') == 'checked') {
      elem.show();
    } else {
      elem.hide();
    }
  };

  HeyPublisher.toggleGuidelines = function(sel) {
    if ($(sel).val() && $(sel).val() > 0) {
      $('#heypub-no-guidelines').hide();
      $('#heypub-yes-guidelines').show();
    } else {
      $('#heypub-no-guidelines').show();
      $('#heypub-yes-guidelines').hide();
    }
  };
  HeyPublisher.selectToggle = function(sel,div) {
    if ($(sel).val() == '1') {
      $(div).show();
    } else {
      $(div).hide();
    }
    return false;
  };
  HeyPublisher.clickToggle  = function(elem,div) {
    event.preventDefault();
    var span = $(elem).find('span')[0];
    if ($(div).is(":visible")) {
      // hide it
      $(div).hide();
      $(span).removeClass('dashicons-dismiss').addClass('dashicons-plus-alt');
    } else {
      $(div).show();
      $(span).removeClass('dashicons-plus-alt').addClass('dashicons-dismiss');
    }
    return false;
  };

  HeyPublisher.toggleDetails = function(elem) {
    event.preventDefault();
    var id = $(elem).closest('tr').data('sid');
    var span = $(elem).find('span')[0];
    if ($('#post_bio_' + id).is(":visible")) {
      // hide it
      $('#post_bio_' + id).hide();
      $(span).removeClass('dashicons-dismiss').addClass('dashicons-plus-alt');
    } else {
      // show it
      $('#post_bio_' + id).show();
      $(span).removeClass('dashicons-plus-alt').addClass('dashicons-dismiss');
    }
  };

}( window.HeyPublisher = window.HeyPublisher || {}, jQuery ));

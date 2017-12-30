// Javascript library for HeyPublisher Wordpress plugin
//
// Copyright (c) 2010-2014 Loudlever, Inc.
// Copyright (c) 2014-2017 Richard Luck, HeyPublisher
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
  // Toggle the editor notes in side-bar
  // TODO : consolidate all of our toggle functions
  HeyPublisher.toggleEditorNotes = function(ex) {
    event.preventDefault();
    if (ex == 'show') {
      // hide it
      $('#editor_notes_off').hide();
      $('#editor_notes_on').show();
    } else {
      // default is to hide
      $('#editor_notes_on').hide();
      $('#heypub_ed_note').value = '';
      $('#editor_notes_off').show();
    }
    return false;
  };

  var domain = null;
  var editor_id = null;
  var token = null;
  var submission_id = null;

  HeyPublisher.ajax_init = function(d,e,t,s) {
    domain    = d;
    editor_id = e;
    token     = t;
    submission_id = s;
    return true;
  };

  HeyPublisher.vote = function(vote) {
    console.log('editor ', editor_id, ' is voting: ',vote);
    var url = domain + '/submissions/' + submission_id + '/votes'
    var data = {'vote': vote, 'editor_id': editor_id};
    // TODO: Add timeout if server is down
    $.ajax (
      {
        type: "POST",
        url: url,
        dataType: 'json',
        headers: {
          "Authorization": "Basic " + token
        },
        data: data,
        success: function (){
          alert('Thanks for your vote!');
        }
      }
    );
    return false;
  }

}( window.HeyPublisher = window.HeyPublisher || {}, jQuery ));

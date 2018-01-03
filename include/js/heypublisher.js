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
    console.log('in ajax_init');

    $('#heypub-note-submit').on('click', function() {
      HeyPublisher.note();
    });

    $('#vote-yes').on('click', function() {
      HeyPublisher.vote('up');
    });

    $('#vote-no').on('click', function() {
      HeyPublisher.vote('down');
    });

    return true;
  };

  HeyPublisher.vote = function(vote) {
    event.preventDefault();
    var url = domain + '/submissions/' + submission_id + '/votes'
    var data = {'vote': vote, 'editor_id': editor_id};
    $.ajax (
      {
        type: "POST",
        url: url,
        timeout: 8000,
        dataType: 'json',
        headers: {
          "Authorization": "Basic " + token
        },
        data: data,
        success: function (){
          // ensure the notes are visible
          // TODO: conver this to promise chain
          $('#heypub_vote_sumary').show();
          styleVotes(vote);
          updateVotesDisplay(vote);
        },
        error: function() {
          alert('Unable to process your vote.  Please try again.');
        }
      }
    );
    return false;
  };
  function styleVotes(vote) {
    if (vote == 'up') {
      $('.vote-yes').addClass('on');
      $('.vote-no').removeClass('on');
    } else {
      $('.vote-no').addClass('on');
      $('.vote-yes').removeClass('on');
    }
    return true;
  };

  function updateVotesDisplay(vote) {
    console.log('in updateVotesDisplay');
    var url = domain + '/submissions/' + submission_id + '/votes'
    var data = {'editor_id': editor_id};
    $.ajax (
      {
        type: "GET",
        url: url,
        timeout: 8000,
        dataType: 'json',
        headers: {
          "Authorization": "Basic " + token
        },
        data: data,
        success: function(data){
          console.log("we have response from query, data:", data);
          var $up = data.meta.up;
          var $down = data.meta.down;
          var $votesUp =  $up + (($up == 1) ? ' vote' : ' votes');
          var $votesDown =  $down + (($down == 1) ? ' vote' : ' votes');

          $('#votes-up-total').text($votesUp);
          $('#votes-down-total').text($votesDown);
        },
        error: function() {
          alert('Unable to fetch the latest votes.  Try reloading the page.');
        }
      }
    );
    return true;
  };


  HeyPublisher.note = function() {
    event.preventDefault();
    var note = $('#heypub_editor_note').val();
    if (note == '') {
      alert('Please provide a note and try again.');
      return false;
    }
    var url = domain + '/submissions/' + submission_id + '/notes'
    var data = {'note': note, 'editor_id': editor_id};
    $.ajax (
      {
        type: "POST",
        url: url,
        timeout: 8000,
        dataType: 'json',
        headers: {
          "Authorization": "Basic " + token
        },
        data: data,
        success: function (){
          // TODO: add the note to the top of the list of notes
          // Clean out the existing note:
          $('#heypub_editor_note').val('');
          alert('Your note has been saved.');
        },
        error: function() {
          alert('Unable to save your note.  Please try again later.');
        }
      }
    );
    return false;
  }


}( window.HeyPublisher = window.HeyPublisher || {}, jQuery ));

// Javascript library for HeyPublisher Wordpress plugin
// -----------------------------------
// Copyright 2010-2014 Loudlever, Inc. (wordpress@loudlever.com)
// Copyright 2014-2018 Richard Luck (https://github.com/aguywithanidea/)
// Copyright 2019-2020 HeyPublisher, LLC (https://www.heypublisher.com/)
// Author - Richard Luck <richard@heypublisher.com>
// -----------------------------------

(function( HeyPublisher, $, undefined ) {

  var domain = null;
  var editor_id = null;
  var token = null;
  var submission_id = null;
  // var debug = false;

  // -----------------------------------
  // Bind all listening events on the Submission Details page
  function bindButtons() {
    console.log("binding buttons...");

    $('#heypub-note-submit').click(function(event) {
      event.preventDefault();
      HeyPublisher.note();
    });

    $.each($('[data-vote]'), function(idx,val) {
      $(val).click(function(event) {
        event.preventDefault();
        HeyPublisher.vote($(val).data('vote'));
      })
    });

    $.each($('[data-toggle]'), function(idx,val) {
      $(val).click(function(event) {
        event.preventDefault();
        toggleExpandCollapse(this,$(val).data('toggle'));
      })
    });

    $.each($('[data-notes]'), function(idx,val) {
      $(val).click(function(event) {
        event.preventDefault();
        toggleEditorNotes($(val).data('notes'));
      })
    });
  };
  // -----------------------------------
  // Disable console calls in production
  function initConsole(d) {
    if (d != 1) {
      var console = {};
      console.log = function(){};
      window.console = console;
    }
  };
  // -----------------------------------
  // Expand / Collapse author bios on Submission List page
  function toggleBioDetails(elem) {
    var id = $(elem).data('sid');
    console.log('=>toggleBioDetails => id: ', id);
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
  // -----------------------------------
  // Toggle the editor notes in side-bar of Submission Details page
  function toggleEditorNotes(val) {
    if (val == 'on') {
      // hide it
      $('#editor_notes_off').hide();
      $('#editor_notes_on').show();
    } else {
      // default is to hide
      $('#editor_notes_on').hide();
      $('#editor_notes_on').find('textarea')[0].value = '';
      $('#editor_notes_off').show();
    }
    return false;
  };
  // -----------------------------------
  // Dashicon expand/collapse areas on Submission Detail Pages
  function toggleExpandCollapse(elem,div) {
    div = '#'+div;
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
  // -----------------------------------
  // Style votes buttons on Submission Detail page
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
  // -----------------------------------
  // Update the votes display block on the submission detail page
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
          "Authorization": "Basic " + token,
          "HeyPublisherAjax": true
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
  // -----------------------------------

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

  // -----------------------------------
  // External facing - initializes the JS on the Email List Page
  HeyPublisher.emailListInit = function() {
    console.log('emailListInit() => ');
    // bind the buttons on this page
    $.each($('[data-email]'), function(idx,val) {
      $(val).click(function(event) {
        // event.preventDefault();
        var id = $(val).data('email');
        return confirm("Are you sure you want to delete the " + id + " template?" );
      })
    });
  };

  // -----------------------------------
  // External facing - initializes the JS on the Submission Details Page
  HeyPublisher.submissionDetailInit = function(d,e,t,s,b) {
    domain    = d;
    editor_id = e;
    token     = t;
    submission_id = s;
    initConsole(b);
    console.log('in submissionDetailInit');
    bindButtons();
    return true;
  };

  // -----------------------------------
  // External facing - initializes the JS on the Submission List Page
  HeyPublisher.submissionListInit = function() {
    console.log('submissionListInit() => ');
    // bind the buttons on this page
    $.each($('[data-sid]'), function(idx,val) {
      $(val).click(function(event) {
        event.preventDefault();
        toggleBioDetails($(val));
      })
    });
  };

  HeyPublisher.vote = function(vote) {
    console.log("vote = " + vote);
    var url = domain + '/submissions/' + submission_id + '/votes'
    var data = {'vote': vote, 'editor_id': editor_id};
    $.ajax (
      {
        type: "POST",
        url: url,
        timeout: 8000,
        dataType: 'json',
        headers: {
          "Authorization": "Basic " + token,
          "HeyPublisherAjax": true
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

  HeyPublisher.note = function() {
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
          "Authorization": "Basic " + token,
          "HeyPublisherAjax": true
        },
        data: data,
        success: function (){
          // TODO: add the note to the top of the list of notes
          // Clean out the existing note:
          $('#heypub_editor_note').val('');
          var row = $('<tr></tr>');
          row.addClass('mine');
          var cols = $('<td>You</td><td>Just now</td><td>'+note+'</td>');
          row.append(cols).prependTo('#heypub-notes-list');
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

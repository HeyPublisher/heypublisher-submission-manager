// Javascript library for HeyPublisher Wordpress plugin
//
// Copyright (c) 2010-2016 Loudlever, Inc.
// Author - Richard Luck <richard@loudlever.com>

function heypub_auto_check(src,form) {
  var checked = false;
  if ($(src).checked == true) { checked = true; }
	for (var i = 0; i < document.getElementById(form).elements.length; i++) {
	  document.getElementById(form).elements[i].checked = checked;
	}
}

function heypub_toggle(chk,div) {
  // alert('input = checkbox id '+chk+' and div '+div);
  if ($(chk).checked == true) {
    $(div).show();
  } else {
    $(div).hide();
  }

  return false;
}

function heypub_select_toggle(sel,div) {
  if ($(sel).value == '1') {
    $(div).show();
  } else {
    $(div).hide();
  }
  return false;
}

function heypub_click_toggle(div) {
 if ($(div).visible()) {
   $(div).hide();
 } else {
   $(div).show();
 }
 return false;

}

function heypub_click_check(src,dest) {
  // alert("passed in " + $(src).id + " and looking for " + dest);
  if ($(src).checked == true) {
    $(dest).show();
  } else {
    $(dest).hide();
  }
}

function heypub_toggle_tabs(on) {
  var div = "heypub_"+on+"_info";
  var tab = "heypub_"+on+"_tab";
  var keys = new Array('p','c','s','n','m');
	for (var i = 0; i < keys.length; i++) {
    if (on != keys[i]) {
      if ($("heypub_"+keys[i]+"_info")) {
        $("heypub_"+keys[i]+"_info").hide();
      }
      if ($("heypub_"+keys[i]+"_tab")) {
        $("heypub_"+keys[i]+"_tab").removeClassName('heypub-tab-pressed');
      }
    }
  }
  $(div).show();
  $(tab).addClassName('heypub-tab-pressed');
}
// Library of HeyPub functions
(function( HeyPublisher, $, undefined ) {

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

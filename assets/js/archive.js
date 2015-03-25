// -- /assets/js/archive.js

(function($, w, d){
  $(d).ready(function(){
    $('#menu-to-edit .menu-item-snarfer').each(function(){
      var $this = $(this);
      $this.find('.edit-menu-item-url').attr('disabled', 'disabled');
    });
  });
})(jQuery, window, document);
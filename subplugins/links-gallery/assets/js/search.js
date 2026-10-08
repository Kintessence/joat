jQuery(document).ready(function($){
  $('#linkSearch').on('input', function(){
    var term = $(this).val().toLowerCase();
    $('.link-card').each(function(){
      var title = $(this).data('title').toLowerCase();
      var tags = $(this).data('tags').toLowerCase();
      $(this).toggle(title.includes(term) || tags.includes(term));
    });
  });
});

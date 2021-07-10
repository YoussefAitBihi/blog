$(function(){
   $('.more').on('click', function(){
         
      let description   = $(this).siblings('.description'),
         data           = description.data('description');

      description.text(data);

      $(this).fadeOut().remove();

   });        
});

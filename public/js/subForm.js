// L'ajout du prototype au DOM
function addForm($id) {

   $('#add_image').on('click', function(){
            
      const postImage = $($id);

      // Le compteur
      let counter = +$(this).parent().find('#counter').val() || postImage.children().length;

      // Prototype
      prototype = postImage.data('prototype').replace(/__name__/g, counter);

      // Ajouter le prototype au DOM
      postImage.append(prototype);

      counter++;

      $(this).parent().find('#counter').val(counter);

      hndBtnDlt();

      // Suppression de Prototype
      deletePrototype($id);
      
   });
}

// Suppression de Prototype
function deletePrototype($id) {

   $($id).each(function() {
      var lengthOfPrototype = $(this).children().length;

      if (lengthOfPrototype == 4) {
         $(this).removeAttr('data-prototype');
         $('.add_image').remove();
      }

   });

}

// La suppression du prototype
function hndBtnDlt() {
   $("button[data-action='delete']").on('click', function(){
      $($(this).data('id')).remove();
   });
}

hndBtnDlt();

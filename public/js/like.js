$(function(){
   $('.like').on('click', function(event) {

      event.preventDefault();

      // L'URL
      const url = $(this).attr('href');
      // Class
      const icon = $(this).children().eq(1);
      // Child
      let child = $(this).children().eq(0);

      // Gérer la requête, et renvoyer les bonnes données
      axios.get(url)
         .then(function(response) {
            const count = response.data.count;

            child.text(count);

            if (icon.hasClass('fas')) {
               icon.addClass('far').removeClass('fas');
            } else {
               icon.addClass('fas').removeClass('far');
            }
         })
         .catch(function(error){
            if (error.response.status == 403) {
               location.replace("http://127.0.0.1:8000/account/login");
            }
         });
   });
});

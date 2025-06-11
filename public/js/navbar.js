$(document).ready(function () {
  // Transition effect for navbar
  $(window).scroll(function () {
    if ($(this).scrollTop() > 500) {
      $('.navbar').addClass('solid');
    } else {
      $('.navbar').removeClass('solid');
    }
  });
});

function toggleMagbeadPromoPopup(){
  let magbead_popup_wrapper = document.getElementById('magbead-promo-overlay');
  document.documentElement.classList.toggle('scroll-lock');
  if(magbead_popup_wrapper) magbead_popup_wrapper.classList.toggle('active');
}

let magbead_popup_wrapper = document.getElementById('magbead-promo-overlay');
if(magbead_popup_wrapper){
  magbead_popup_wrapper.addEventListener('click', function(e){
    if(e.target === this){
      toggleMagbeadPromoPopup();
    }
  });
}

toggleMagbeadPromoPopup(); // open popup on page load
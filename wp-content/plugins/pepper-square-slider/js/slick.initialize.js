jQuery(function() {
  
  jQuery("#slides").slick({
    dots: (setting.ps_dots == 1) ? true : false,
    arrows: (setting.ps_arrows == 1) ? true : false,
    speed: setting.ps_speed,
    slidesToShow: setting.ps_slide_num,
  }); 
});


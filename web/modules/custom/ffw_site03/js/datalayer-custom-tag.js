(function (Drupal, $) {

  "use strict";

  Drupal.behaviors.ffw_site03 = {
    attach: function (context, settings) {    
      if (context != document) {
        return;
      }
      window.dataLayer = window.dataLayer || [];
      if (settings.dataLayer !== undefined && settings.dataLayer.tag !== undefined) {               
        window.dataLayer.push({
          'tag': settings.dataLayer.tag    
        })
        
      }
    }
  };

}) (Drupal, jQuery);

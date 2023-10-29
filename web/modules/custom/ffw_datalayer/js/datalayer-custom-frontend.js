(function (Drupal, $) {

  "use strict";

  Drupal.behaviors.ffw_datalayer = {
    attach: function (context, settings) {    
      if (context != document) {
        return;
      }
      window.dataLayer = window.dataLayer || [];
      if (settings.dataLayer !== undefined && settings.dataLayer.landingpage !== undefined) {               
        window.dataLayer.push({
          'homepage': settings.dataLayer.landingpage    
        })
        
      }
    }
  };

}) (Drupal, jQuery);

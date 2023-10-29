(function ($, Drupal) {

  /**
   * EBT Core behavior.
   */
  Drupal.behaviors.ebtCore = {
    attach: function (context, settings) {
      $.each(drupalSettings['ebtCore'], function(block_class, value) {
        if (value['ebtCoreParallax'] != undefined) {
          $('.' + block_class).parallax({
            imageSrc: Drupal.checkPlain(value['ebtCoreParallax']['mediaUrl'])
          });
        }

        if (value['ebtCoreBackgroundRemoteVideo'] != undefined) {
          if (value['ebtCoreBackgroundRemoteVideo']['mediaProvider'] == 'YouTube') {
            const $elements =  $(once('youtube-video', '.' + block_class + ' .bg-inner', context));
            $elements.YTPlayer({
              videoURL: Drupal.checkPlain(value['ebtCoreBackgroundRemoteVideo']['mediaUrl']),
              containment: '.' + block_class,
              autoPlay: 1,
              showControls: 0,
              mute: 1,
              startAt: 0,
              opacity: 1,
              addRaster: 1,
              quality: 'default',
              loop: 1
            });
          }
        }
      });
    }
  };

})(jQuery, Drupal);

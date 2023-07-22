 $(document).ready(function () {
    

    // ...
        var _CaptionTransitions = [];
        _CaptionTransitions["CLIP|LR"] = {$Duration: 900, $Clip: 3, $Easing: $JssorEasing$.$EaseInOutCubic };
        // var options = {
        //     ...

        //     ...
        // };
        // ...
        // var jssor_slider1 = new $JssorSlider$("slider1_container", options);
        
    var jssor_1_options = {
      $AutoPlay: true,
      $ArrowNavigatorOptions: {
        $Class: $JssorArrowNavigator$
      },
        $CaptionSliderOptions: {                            //[Optional] Options which specifies how to animate caption
            // $Class: $JssorCaptionSlider$,                   //[Required] Class to create instance to animate caption
            $CaptionTransitions: _CaptionTransitions,       //[Required] An array of caption transitions to play caption, see caption transition section at jssor slideshow transition builder
            $PlayInMode: 1,                                 //[Optional] 0 None (no play), 1 Chain (goes after main slide), 3 Chain Flatten (goes after main slide and flatten all caption animations), default value is 1
            $PlayOutMode: 3                                 //[Optional] 0 None (no play), 1 Chain (goes before main slide), 3 Chain Flatten (goes before main slide and flatten all caption animations), default value is 1
        },
      $ThumbnailNavigatorOptions: {
        $Class: $JssorThumbnailNavigator$,
        $Cols: 9,
        $SpacingX: 3,
        $SpacingY: 3,
        $Align: 260
      }
    };
    
    var jssor_1_slider = new $JssorSlider$("jssor_1", jssor_1_options);
    
    // responsive code begin
    // you can remove responsive code if you don\'t want the slider scales while window resizing
    function ScaleSlider() {
        var refSize = jssor_1_slider.$Elmt.parentNode.clientWidth;
        if (refSize) {
            refSize = Math.min(refSize, 600);
            jssor_1_slider.$ScaleWidth(refSize);
        }
        else {
            window.setTimeout(ScaleSlider, 30);
        }
    }
    ScaleSlider();
    $(window).bind("load", ScaleSlider);
    $(window).bind("resize", ScaleSlider);
    $(window).bind("orientationchange", ScaleSlider);
    // responsive code end
});
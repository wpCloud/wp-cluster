define("udx.ui.gallery",["udx.utility.imagesloaded","jquery.isotope","jquery.fancybox"],function(){function a(a,b){jQuery("a",a).fancybox(b)}function b(a,b){var c=require("jquery.isotope");return require("jquery.isotope")?void jQuery(a).each(function(){new c(this,b)}):void console.error("udx.ui.gallery","isotope not available as expected")}return function(){var c=this,d=require("udx.utility.imagesloaded")(this);return d.on("done",function(){c.options.isotope&&b(jQuery(c),c.options.isotope),c.options.fancybox&&a(jQuery(c),c.options.fancybox)}),d.on("fail",function(a){console.error("udx.ui.gallery",a)}),this.options=jQuery.extend(this.options,{isotope:{cellsByColumn:{columnWidth:240,rowHeight:360}},fancybox:{speedIn:600,speedOut:200,helpers:{title:{type:"inside"},overlay:{showEarly:!1}}}}),this}});
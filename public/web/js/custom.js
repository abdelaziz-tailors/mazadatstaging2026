/**
/**
Core script to handle the entire theme and core functions
**/
var Zapolo = function(){
	/* Search Bar ============ */
	siteUrl = '';

	var screenWidth = $( window ).width();

	var homeSearch = function() {
		'use strict';
		/* top search in header on click function */
		var quikSearch = jQuery("#quik-search-btn");
		var quikSearchRemove = jQuery("#quik-search-remove");

		quikSearch.on('click',function() {
			jQuery('.dz-quik-search').fadeIn(500);
			jQuery('.dz-quik-search').addClass('On');
		});

		quikSearchRemove.on('click',function() {
			jQuery('.dz-quik-search').fadeOut(500);
			jQuery('.dz-quik-search').removeClass('On');
		});
		/* top search in header on click function End*/
	}

	/* One Page Layout ============ */
	var onePageLayout = function() {
		'use strict';
		var headerHeight =   parseInt($('.onepage').css('height'), 10);

		$(".scroll").unbind().on('click',function(event)
		{
			event.preventDefault();

			if (this.hash !== "") {
				var hash = this.hash;
				var seactionPosition = $(hash).offset().top;
				var headerHeight =   parseInt($('.onepage').css('height'), 10);


				$('body').scrollspy({target: ".navbar", offset: headerHeight+2});

				var scrollTopPosition = seactionPosition - (headerHeight);

				$('html, body').animate({
					scrollTop: scrollTopPosition
				}, 800, function(){

				});
			}
		});
		$('body').scrollspy({target: ".navbar", offset: headerHeight + 2});
	}

	/* Header Height ============ */
	var handleResizeElement = function(){
		var headerTop = 0;
		var headerNav = 0;

		$('.header .sticky-header').removeClass('is-fixed');
		$('.header').removeAttr('style');

		if(jQuery('.header .top-bar').length > 0 &&  screenWidth > 991)
		{
			headerTop = parseInt($('.header .top-bar').outerHeight());
		}

		if(jQuery('.header').length > 0 )
		{
			headerNav = parseInt($('.header').height());
			headerNav =	(headerNav == 0)?parseInt($('.header .main-bar').outerHeight()):headerNav;
		}

		var headerHeight = headerNav + headerTop;

		jQuery('.header').css('height', headerHeight);
	}

	var handleResizeElementOnResize = function(){
		var headerTop = 0;
		var headerNav = 0;

		$('.header .sticky-header').removeClass('is-fixed');
		$('.header').removeAttr('style');


		setTimeout(function(){

			if(jQuery('.header .top-bar').length > 0 &&  screenWidth > 991)
			{
				headerTop = parseInt($('.header .top-bar').outerHeight());
			}

			if(jQuery('.header').length > 0 )
			{
				headerNav = parseInt($('.header').height());
				headerNav =	(headerNav == 0)?parseInt($('.header .main-bar').outerHeight()):headerNav;
			}

			var headerHeight = headerNav + headerTop;

			jQuery('.header').css('height', headerHeight);

		}, 500);
    }

	/* Load File ============ */
	var dzTheme = function(){
		'use strict';
		var loadingImage = '<img src="images/loading.gif">';
		jQuery('.dzload').each(function(){
		var dzsrc =   siteUrl + $(this).attr('dzsrc');
		  //jQuery(this).html(loadingImage);
		 	jQuery(this).hide(function(){
				jQuery(this).load(dzsrc, function(){
					jQuery(this).fadeIn('slow');
				});
			})

		});


		if(screenWidth <= 991 ){
			jQuery('.navbar-nav > li > a, .sub-menu > li > a').unbind().on('click', function(e){
				if(jQuery(this).parent().hasClass('open'))
				{
					jQuery(this).parent().removeClass('open');
				}
				else{
					jQuery(this).parent().parent().find('li').removeClass('open');
					jQuery(this).parent().addClass('open');
				}
			});
		}

		jQuery('.menu-btn, .openbtn').on('click',function(){
			jQuery('.contact-sidebar').addClass('active');
		});
		jQuery('.menu-close').on('click',function(){
			jQuery('.contact-sidebar').removeClass('active');
			jQuery('.menu-btn').removeClass('open');
		});

	}

	/* Magnific Popup ============ */
	var MagnificPopup = function(){
		'use strict';

		if(jQuery('.mfp-gallery').length > 0)
		{
			/* magnificPopup function */
			jQuery('.mfp-gallery').magnificPopup({
				delegate: '.mfp-link',
				type: 'image',
				tLoading: 'Loading image #%curr%...',
				mainClass: 'mfp-img-mobile',
				gallery: {
					enabled: true,
					navigateByImgClick: true,
					preload: [0,1] // Will preload 0 - before current, and 1 after the current image
				},
				image: {
					tError: '<a href="%url%">The image #%curr%</a> could not be loaded.',
					titleSrc: function(item) {
						return item.el.attr('title') + '<small></small>';
					}
				}
			});
			/* magnificPopup function end */
		}

		if(jQuery('.mfp-video').length > 0)
		{
			/* magnificPopup for Play video function */
			jQuery('.mfp-video').magnificPopup({
				type: 'iframe',
				iframe: {
					markup: '<div class="mfp-iframe-scaler">'+
							 '<div class="mfp-close"></div>'+
							 '<iframe class="mfp-iframe" frameborder="0" allowfullscreen></iframe>'+
							 '<div class="mfp-title">Some caption</div>'+
							 '</div>'
				},
				callbacks: {
					markupParse: function(template, values, item) {
						values.title = item.el.attr('title');
					}
				}
			});

		}

		if(jQuery('.popup-youtube, .popup-vimeo, .popup-gmaps').length > 0)
		{
			/* magnificPopup for Play video function end */
			$('.popup-youtube, .popup-vimeo, .popup-gmaps').magnificPopup({
				disableOn: 700,
				type: 'iframe',
				mainClass: 'mfp-fade',
				removalDelay: 160,
				preloader: false,

				fixedContentPos: false
			});

		}

	}

	/* Scroll To Top ============ */
	var scrollTop = function (){
		'use strict';
		var scrollTop = jQuery("button.scroltop");
		/* page scroll top on click function */
		scrollTop.on('click',function() {
			jQuery("html, body").animate({
				scrollTop: 0
			}, 1000);
			return false;
		})

		jQuery(window).bind("scroll", function() {
			var scroll = jQuery(window).scrollTop();
			if (scroll > 900) {
				jQuery("button.scroltop").fadeIn(1000);
			} else {
				jQuery("button.scroltop").fadeOut(1000);
			}
		});
		/* page scroll top on click function end*/
	}

	/* Header Fixed ============ */
	var headerFix = function(){
		'use strict';
		/* Main navigation fixed on top  when scroll down function custom */
		jQuery(window).on('scroll', function () {
			if(jQuery('.sticky-header').length > 0){
				var menu = jQuery('.sticky-header');
				if ($(window).scrollTop() > menu.offset().top) {
					menu.addClass('is-fixed');
					$('.site-header .container > .logo-header .logo').attr('src','images/logo.png');
					$('.site-header .container > .logo-header .logo-2').attr('src','images/logo-2.png');
					$('.site-header .container > .logo-header .logo-3').attr('src','images/logo-3.png');
				} else {
					menu.removeClass('is-fixed');
					$('.site-header .container > .logo-header .logo, .site-header .container > .logo-header .logo-2, .site-header .container > .logo-header .logo-3').attr('src','images/logo-white.png')
				}
			}
		});
		/* Main navigation fixed on top  when scroll down function custom end*/
	}

	/* Masonry Box ============ */
	var masonryBox = function(){
		'use strict';
		/* masonry by  = bootstrap-select.min.js */
		if(jQuery('#masonry, .masonry').length > 0)
			{
			var self = jQuery("#masonry, .masonry");

			if(jQuery('.card-container').length > 0)
			{
				var gutterEnable = self.data('gutter');

				var gutter = (self.data('gutter') === undefined)?0:self.data('gutter');
				gutter = parseInt(gutter);


				var columnWidthValue = (self.attr('data-column-width') === undefined)?'':self.attr('data-column-width');
				if(columnWidthValue != ''){columnWidthValue = parseInt(columnWidthValue);}

				 self.imagesLoaded(function () {
					self.masonry({
						//gutter: gutter,
						//columnWidth:columnWidthValue,
						gutterWidth: 15,
						isAnimated: true,
						itemSelector: ".card-container",
						//percentPosition: true
					});

				});
			}
		}
		if(jQuery('.filters').length)
		{
			jQuery(".filters li:first").addClass('active');

			jQuery(".filters").on("click", "li", function() {
				jQuery('.filters li').removeClass('active');
				jQuery(this).addClass('active');

				var filterValue = $(this).attr("data-filter");
				self.isotope({ filter: filterValue });
			});
		}
		/* masonry by  = bootstrap-select.min.js end */
	}

	/* Counter Number ============ */
	var counter = function(){
		if(jQuery('.counter').length)
		{
			jQuery('.counter').counterUp({
				delay: 10,
				time: 3000
			});
		}
	}

	/* Video Popup ============ */
	var handleVideo = function(){
		/* Video responsive function */
		jQuery('iframe[src*="youtube.com"]').wrap('<div class="embed-responsive embed-responsive-16by9"></div>');
		jQuery('iframe[src*="vimeo.com"]').wrap('<div class="embed-responsive embed-responsive-16by9"></div>');
		/* Video responsive function end */
	}

	/* Gallery Filter ============ */
	var handleFilterMasonary = function(){
		/* gallery filter activation = jquery.mixitup.min.js */
		if (jQuery('#image-gallery-mix').length) {
			jQuery('.gallery-filter').find('li').each(function () {
				$(this).addClass('filter');
			});
			jQuery('#image-gallery-mix').mixItUp();
		};
		if(jQuery('.gallery-filter.masonary').length){
			jQuery('.gallery-filter.masonary').on('click','span', function(){
				var selector = $(this).parent().attr('data-filter');
				jQuery('.gallery-filter.masonary span').parent().removeClass('active');
				jQuery(this).parent().addClass('active');
				jQuery('#image-gallery-isotope').isotope({ filter: selector });
				return false;
			});
		}
		/* gallery filter activation = jquery.mixitup.min.js */
	}

	/* Resizebanner ============ */
	var handleBannerResize = function(){
		$(".full-height").css("height", $(window).height());
	}

	/* BGEFFECT ============ */
	var reposition = function (){
		'use strict';
		var modal = jQuery(this),
		dialog = modal.find('.modal-dialog');
		modal.css('display', 'block');

		/* Dividing by two centers the modal exactly, but dividing by three
		 or four works better for larger screens.  */
		dialog.css("margin-top", Math.max(0, (jQuery(window).height() - dialog.height()) / 2));
	}

	var handelResize = function (){

		/* Reposition when the window is resized */
		jQuery(window).on('resize', function() {
			jQuery('.modal:visible').each(reposition);


		});
	}

	/* Countdown ============ */
	var handleCountDown = function(WebsiteLaunchDate){
		/* Time Countr Down Js */
		if($(".countdown").length)
		{
			$('.countdown').countdown({date: WebsiteLaunchDate+' 23:5'}, function() {
				$('.countdown').text('we are live');
			});
		}
		/* Time Countr Down Js End */
	}

	/* Website Launch Date */
	var WebsiteLaunchDate = new Date();
	monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
	WebsiteLaunchDate.setMonth(WebsiteLaunchDate.getMonth() + 1);
	WebsiteLaunchDate =  WebsiteLaunchDate.getDate() + " " + monthNames[WebsiteLaunchDate.getMonth()] + " " + WebsiteLaunchDate.getFullYear();
	/* Website Launch Date END */


	/* Light Gallery ============ */
	var lightGallery = function (){
		if(($('#lightgallery, .lightgallery').length > 0)){
			$('#lightgallery, .lightgallery').lightGallery({
				selector : '.lightimg',
				loop:true,
				download:false,
				thumbnail:true,
				share:false,
				exThumbImage: 'data-exthumbimage'
			});
		}
	}

	var boxHover = function(){
		jQuery('.box-hover').on('mouseenter',function(){
			var selector = jQuery(this).parent().parent();
			selector.find('.box-hover').removeClass('active');
			jQuery(this).addClass('active');
		});
	}


	var handleSupport = function(){
		var support = '<a href="https://wa.me/+201011806410?text=hi" target="_blank" class="bt-buy-now theme-btn"><i class="fab fa-whatsapp"></i><span>Help </span></a>';
		jQuery('body').append(support);
	}


	/* WOW ANIMATION ============ */
	var wow_animation = function(){
		if($('.wow').length > 0)
		{
			var wow = new WOW(
			{
			  boxClass:     'wow',      // animated element css class (default is wow)
			  animateClass: 'animated', // animation css class (default is animated)
			  offset:       0,          // distance to the element when triggering the animation (default is 0)
			  mobile:       false       // trigger animations on mobile devices (true is default)
			});
			wow.init();
		}
	}

	var handleSvgRadiaChart = function(){
		// Remove svg.radial-progress .complete inline styling
		$('svg.radial-progress').each(function( index, value ) {
			$(this).find($('circle.complete')).removeAttr( 'style' );
		});

		// Activate progress animation on scroll
		$(window).scroll(function(){
			$('svg.radial-progress').each(function( index, value ) {
				// If svg.radial-progress is approximately 25% vertically into the window when scrolling from the top or the bottom
				if (
					$(window).scrollTop() > $(this).offset().top - ($(window).height() * 0.75) &&
					$(window).scrollTop() < $(this).offset().top + $(this).height() - ($(window).height() * 0.25)
				) {
					// Get percentage of progress
					percent = $(value).data('percentage');
					// Get radius of the svg's circle.complete
					radius = $(this).find($('circle.complete')).attr('r');
					// Get circumference (2πr)
					circumference = 2 * Math.PI * radius;
					// Get stroke-dashoffset value based on the percentage of the circumference
					strokeDashOffset = circumference - ((percent * circumference) / 100);
					// Transition progress for 1.25 seconds
					$(this).find($('circle.complete')).animate({'stroke-dashoffset': strokeDashOffset}, 1250);
				}
			});
		}).trigger('scroll');
	}

	var handleCurrentActive = function() {
		for (var nk = window.location,
			o = $("ul.navbar a").filter(function() {

				return this.href == nk;

			})
			.addClass("active")
			.parent()
			.addClass("active");;)
		{

			if (!o.is("li")) break;

			o = o.parent()
				.addClass("show")
				.parent('li')
				.addClass("active");
		}
	}


	/* Function ============ */
	return {
		init:function(){
			boxHover();
			onePageLayout();
			dzTheme();
			homeSearch();
			MagnificPopup();
			scrollTop();
			headerFix();
			handleVideo();
			handleFilterMasonary();
			handleCountDown(WebsiteLaunchDate);
			handleBannerResize();
			handelResize();
			lightGallery();
			jQuery('.modal').on('show.bs.modal', reposition);
			handleSupport();
			//wow_animation();
			handleSvgRadiaChart();
			handleCurrentActive();
		},

		load:function(){
			counter();
			masonryBox();
		},

		resize:function(){
			screenWidth = $(window).width();
			dzTheme();
		}
	}

}();

/* Document.ready Start */
jQuery(document).ready(function() {
    'use strict';

	Zapolo.init();

	$('a[data-toggle="tab"]').click(function(){
		// todo remove snippet on bootstrap v4
		$('a[data-toggle="tab"]').click(function() {
		  $($(this).attr('href')).show().addClass('show active').siblings().hide();
		})
	});

	jQuery('.navicon').on('click',function(){
		$(this).toggleClass('open');
	});
	if($('.js-tilt').length > 0){
		$('.js-tilt').tilt({
			perspective:    2000,
			transition:     true,
		});
	}

});
/* Document.ready END */

/* Window Load START */
jQuery(window).on('load',function () {
	'use strict';

	Zapolo.load();

	setTimeout(function(){
		jQuery('#loading-area').remove();
	}, 1000);

});
/*  Window Load END */
/* Window Resize START */
jQuery(window).on('resize',function () {
	'use strict';
	Zapolo.resize();
});
/*  Window Resize END */


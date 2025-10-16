/* JavaScript Document */
jQuery(window).on('load', function() {
    'use strict';
	
	
	function changeItemBoxed() {
		if(jQuery("body").hasClass("boxed")) {
			return 3;
		} else {
			return 4;
		}
	}
	
	// service-silder-swiper
	if(jQuery('.service-slider').length > 0){
		var swiper = new Swiper('.service-slider', {
			speed: 1500,
			parallax: true,
			slidesPerView: changeItemBoxed(),
			spaceBetween: 30,
			loop:false,
			autoplay: {
			   delay: 3000,
			},
			breakpoints: {
				1401: {
					slidesPerView: changeItemBoxed(),
				},
				992: {
					slidesPerView: 3,
				},
				650: {
					slidesPerView: 2,
				},
				320: {
					slidesPerView: 1,
				},
			}
		});
	}
	
	// appscreen-slider
	if(jQuery('.appscreen-slider').length > 0){
		var swiper = new Swiper('.appscreen-slider', {
			speed: 1500,
			slidesPerView: 6,
			spaceBetween: 30,
			loop:true,
			autoplay: {
			   delay: 3000,
			},
			pagination: {
				el: '.swiper-pagination',
				clickable: true,
				
			},
			breakpoints: {
				1191: {
					slidesPerView: 6,
				},
				768: {
					slidesPerView: 4,
				},
				400: {
					slidesPerView: 2,
				},
				320: {
					slidesPerView: 1,
				},
			}
		});
	}
	
	// testimonial-swiper
	if(jQuery('.testimonial-swiper').length > 0){
		var swiper = new Swiper('.testimonial-swiper', {
			speed: 1500,
			parallax: true,
			slidesPerView: 4,
			spaceBetween: 0,
			loop:true,
			autoplay: {
			   delay: 3000,
			},
			navigation: {
				nextEl: '.swiper-button-next',
				prevEl: '.swiper-button-prev',
			},
			breakpoints: {
				1300: {
					slidesPerView: 4,
				},
				991: {
					slidesPerView: 3,
				},
				600: {
					slidesPerView: 2,
				},
				320: {
					slidesPerView: 1,
				},
			}
		});
	}
	
	// blog-slider
	if(jQuery('.blog-slider').length > 0){
		var swiper = new Swiper('.blog-slider', {
			speed: 1500,
			parallax: true,
			slidesPerView: 3,
			spaceBetween: 0,
			loop:true,
			autoplay: {
			   delay: 3000,
			},
			navigation: {
				nextEl: '.swiper-button-next',
				prevEl: '.swiper-button-prev',
			},
			breakpoints: {
				1200: {
					slidesPerView: 3,
				},
				768: {
					slidesPerView: 2,
				},
				320: {
					slidesPerView: 1,
				},
			}
		});
	}
	
	
	// blog-slider-full
	if(jQuery('.blog-slider-full').length > 0){
		var swiper = new Swiper('.blog-slider-full', {
			speed: 1500,
			parallax: true,
			slidesPerView: changeItemBoxed(),
			spaceBetween: 0,
			loop:true,
			autoplay: {
			   delay: 3000,
			},
			navigation: {
				nextEl: '.swiper-button-next',
				prevEl: '.swiper-button-prev',
			},
			breakpoints: {
				1600: {
					slidesPerView: changeItemBoxed(),
				},
				1100: {
					slidesPerView: 3,
				},
				730: {
					slidesPerView: 2,
				},
				320: {
					slidesPerView: 1,
				},
			}
		});
	}
	
	// post-swiper
	if(jQuery('.post-swiper').length > 0){
		var swiper = new Swiper('.post-swiper', {
			speed: 1500,
			parallax: true,
			slidesPerView: 1,
			spaceBetween: 0,
			loop:true,
			autoplay: {
			   delay: 3000,
			},
			navigation: {
				nextEl: '.next-post-swiper-btn',
				prevEl: '.prev-post-swiper-btn',
			}
		});
	}
	
	// Clients Swiper
	if(jQuery('.clients-swiper').length > 0){
		var swiper5 = new Swiper('.clients-swiper', {
			speed: 1500,
			parallax: true,
			slidesPerView: 4,
			spaceBetween: 30,
			autoplay: {
			   delay: 3000,
			},
			loop:true,
			navigation: {
				nextEl: '.swiper-button-next5',
				prevEl: '.swiper-button-prev5',
			},
			breakpoints: {
				1191: {
					slidesPerView: 6,
				},
				991: {
					slidesPerView: 5,
				},
				691: {
					slidesPerView: 4,
				},
				591: {
					slidesPerView: 3,
				},
				320: {
					slidesPerView: 2,
				},
			}
		});
	}
	
});
/* Document .ready END */
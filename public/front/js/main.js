const languageButton = document.getElementById('language-btn');

languageButton.addEventListener('click', () => {
  if (document.documentElement.lang === 'en') {
    document.documentElement.lang = 'ar';
    document.body.dir = 'rtl'; 
    languageButton.innerHTML = '<i class="fas fa-globe"></i> العربيه'; 
  } else {
    document.documentElement.lang = 'en';
    document.body.dir = 'ltr'; 
    languageButton.innerHTML = '<i class="fas fa-globe"></i> English'; 
  }
});

new Swiper(".reviewsSlider", {
    navigation: {
        nextEl: ".rightArrow",
        prevEl: ".leftArrow",
    },

    scrollbar: {
        el: ".swiper-scrollbar",
        draggable: true,
    },

    autoplay: {
        delay: 3000,
        disableOnInteraction: false, 
    },

    freeMode: {
        enabled: true,
        sticky: true,
    },

    slidesPerView: 1, // العرض الافتراضي
    spaceBetween: 20, // المسافة الافتراضية بين الشرائح

    breakpoints: {
        480: {
            slidesPerView: 1.5,
            spaceBetween: 20,
        },
        768: {
            slidesPerView: 2,
            spaceBetween: 30,
        },
        1024: {
            slidesPerView: 3,
            spaceBetween: 40,
        },
        1440: {
            slidesPerView: 4,
            spaceBetween: 50,
        },
    },
});


{{-- <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VLOG</title>
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/10.3.0/swiper-bundle.min.css"
          integrity="sha512-ryx4QW6sYyQthh6MIGW1cDEfNuIwTsvWtORXg5t3sqmh3TSNmqMr+VBN5N0T+z0GqqsiDJ5O8YhP4diuBGmcrw=="
          crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{asset('/')}}/front/css/bootstrap.min.css">
    <!-- Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{asset('/')}}/front/css/style.css">
</head>

<body>
<!-- ============ header ============ -->
<header>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <!-- Logo -->
            <a class="navbar-brand" href="#">
                <img src="{{asset('/')}}/front/img/logo.png" alt="">
                <span class="text-white">VLOG</span>
            </a>
            <!-- Toggler Button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                    aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <!-- Navbar Links -->
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 d-flex gap-5">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">الرئيسية</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="#about-section">من نحن</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="#features-section">الميزات</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="#contact-us">تواصل معنا </a>
                    </li>
                </ul>

                <!-- Language Button -->
                <button class="btn btn-outline-light btn-sm" type="button" id="language-btn">
                    <i class="fas fa-globe"></i> Language
                </button>

            </div>
        </div>
    </nav>
</header>

<!-- ============= maim ============== -->
<main>

    <section class="hero-section">
        <div class="overlay"></div>
        <div class="container">
            <!-- <div class="row align-items-center">
                <div class="col-md-6">
                  <div class="text-hero">
                    <h1>Welcome to Vlog</h1>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed vel velit at purus ullamcorper mollis. Donec facilisis, nunc vitae auctor convallis, lectus justo fermentum massa, et auctor ipsum arcu ac neque.</p>
                    <button class="btn-watch">Watch Video</button>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="hero-img">
                    <img src="{{asset('/')}}/front/img/tow.png" alt="">
                  </div>
                </div>
              </div> -->
        </div>
    </section>

    <section id="about-section" class="about-section">
        <div class="container">
            <div class="row align-items-center">

                <div class="col-md-6">
                    <img src="{{asset('/')}}/front/img/three.png" alt="Vlog Image" class="img-fluid">
                </div>
                <div class="col-md-6">
                    <div class="text-hero">
                        <h1>من نحن</h1>
                        <p>
                            اكتشف مقاطع الفيديو والموسيقى والبث المباشر من جميع أنحاء العالم وأنشئ محتواك الخاص باستخدام أدوات سهلة الاستخدام لالتقاط لحظات حياتك. من فنجان قهوتك الصباحي إلى تنقلاتك المسائية، فلوج يقدم لك مقاطع الفيديو التي تضفي البهجة على يومك. سواء كنت من عشاق الرياضة أو محبي السفر أو تبحث فقط عن ضحكة، هناك شيء للجميع على فلوج.

                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="features-section" class="features-section">
        <div class="container">
            <div class="text-center">
                <h2>مميزات التطبيق</h2>
                <p>اكتشف المميزات الرائعة التي يقدمها تطبيق Vlog لتعزيز تجربتك.</p>
            </div>

            <div class="row">
                <div class="col-md-4 pb-3">
                    <div class="feature-box">
                        <div class="img-futsher">
                            <img src="{{asset('/')}}/front/img/viewer.png" alt="">
                        </div>
                        <h4>شاهد
                        </h4>
                        <p>المبدعون على فلوج يقدمون لك أفضل المحتويات من الإنترنت مباشرة إلى هاتفك.</p>
                    </div>
                </div>

                <div class="col-md-4 pb-3">
                    <div class="feature-box">
                        <div class="img-futsher">
                            <img src="{{asset('/')}}/front/img/write.png" alt="">
                        </div>
                        <h4>أنشئ
                        </h4>
                        <p>قم بإيقاف واستئناف الفيديو الخاص بك بلمسة واحدة. مرات عديدة كما تحتاج.
                            استخدم المؤثرات والانتقالات لإضافة اللمسات النهائية على الفيديو الخاص بك قبل نشره.</p>
                    </div>
                </div>

                <div class="col-md-4 pb-3">
                    <div class="feature-box">
                        <div class="img-futsher">
                            <img src="{{asset('/')}}/front/img/headphones.png" alt="">
                        </div>
                        <h4>استمتع
                        </h4>
                        <p>استمتع واستوحِ الإلهام من مجتمع عالمي من المبدعين.
                            خذ إنشاء الفيديو إلى مستوى جديد باستخدام المؤثرات الخاصة، الفلاتر، الموسيقى، والمزيد. افتح الفلاتر، </p>
                    </div>
                </div>

                <div class="col-md-4 pb-3">
                    <div class="feature-box">
                        <div class="img-futsher">
                            <img src="{{asset('/')}}/front/img/checklists.png" alt="">
                        </div>
                        <h4>خصص
                        </h4>
                        <p>
                            أدوات التحرير المتكاملة لدينا تسمح لك بتحرير مقاطع الفيديو الخاصة بك بسهولة.
                            أدوات تحرير لقص، ودمج، ونسخ مقاطع الفيديو دون مغادرة التطبيق.            </div>
                </div>

                <div class="col-md-4 pb-3">
                    <div class="feature-box">
                        <div class="img-futsher">
                            <img src="{{asset('/')}}/front/img/music.png" alt="">
                        </div>
                        <h4>أضف الموسيقى
                        </h4>
                        <p>أضف صوتك المفضل إلى مقاطع الفيديو الخاصة بك باستخدام ملايين المقاطع الموسيقية والأصوات.

                    </div>
                </div>

            </div>
        </div>
    </section>


    <section class="reviews theme-BG">
        <div class="theme-BG-ultra-blur">
            <div class="container">
                <div class="reviewsSliderContainer">
                    <div class="reviewsSlider swiper" dir="ltr">
                        <div class="swiper-wrapper">

                            <div class="swiper-slide">
                                <div class="review">
                                    <div class="header">
                                        <div class="icon">
                                            <img src="{{asset('/')}}/front/img/logo (1).png" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="swiper-slide">
                                <div class="review">
                                    <div class="header">
                                        <div class="icon">
                                            <img src="{{asset('/')}}/front/img/select.png" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="swiper-slide">
                                <div class="review">
                                    <div class="header">
                                        <div class="icon">
                                            <img src="{{asset('/')}}/front/img/splash1.png" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="swiper-slide">
                                <div class="review">
                                    <div class="header">
                                        <div class="icon">
                                            <img src="{{asset('/')}}/front/img/Home.png" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="swiper-slide">
                                <div class="review">
                                    <div class="header">
                                        <div class="icon">
                                            <img src="{{asset('/')}}/front/img/gifts.png" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <button class="arrows leftArrow">
                        <i class="fa-solid fa-angle-left"></i>
                    </button>

                    <button class="arrows rightArrow">
                        <i class="fa-solid fa-angle-right"></i>
                    </button>

                </div>
            </div>
        </div>
    </section>

    <section class="about-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="text-hero">
                        <h1>قم بتنزيل Vlog الآن</h1>
                        <p>قم بتحميل تطبيق Vlog وابدأ في الاستمتاع بالترفيه. قم بتنزيله الآن واكتشف ميزات مذهلة مباشرة على جهازك!</p>

                        <div class="downlodd">
                            <a href="https://apps.apple.com/eg/app/vlog-%D9%81%D9%84%D9%88%D8%AC/id6621208735" class="btn-download">
                                <img src="{{asset('/')}}/front/img/appstorebtn.png" alt="App Store">
                            </a>
                            <a href="https://play.google.com/store/apps/details?id=com.vllog.app" class="btn-download">
                                <img src="{{asset('/')}}/front/img/googleplay.png" alt="Google Play">
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="hero-img">
                        <img src="{{asset('/')}}/front/img/donlod.png" alt="تحميل التطبيق">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="contact-us" class="contact-us">
        <div class="container">
            <div class="text-contact text-center">
                <h2>اتصل بنا</h2>
                <p>إذا كان لديك أي أسئلة أو تحتاج إلى دعم، لا تتردد في التواصل معنا!</p>
            </div>

            <form action="" method="">
               <form action="{{route('send-massage')}}" method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <label for="name">الاسم</label>
                        <input type="text" id="name" name="name" placeholder="أدخل اسمك" required />
                    </div>
                    <div class="col-md-6">
                        <label for="email">البريد الإلكتروني</label>
                        <input type="email" id="email" name="email" placeholder="أدخل بريدك الإلكتروني" required />
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <label for="message">الرسالة</label>
                        <textarea id="message" name="message" rows="5" placeholder="اكتب رسالتك هنا..." required></textarea>
                    </div>
                </div>

                <button type="submit" class="submit-btn">إرسال الرسالة</button>
            </form>
        </div>
    </section>


</main>
<!--  ============= footer ============ -->
<footer class="theme-BG">
    <div class="theme-BG-ultra-blur">
        <div class="footerInner container">
            <div class="main">
                <div class="logo">
                    <img src="{{asset('/')}}/front/img/logo.png" alt="">
                    <span>VLOG</span>
                </div>

                <p>
                    اكتشف مقاطع الفيديو والموسيقى والبث المباشر من جميع أنحاء العالم وأنشئ محتواك الخاص باستخدام أدوات سهلة الاستخدام لالتقاط لحظات حياتك.
                </p>

                <!-- <div class="social">
                  <a href="#"><i class="fa-brands fa-facebook-f"></i></a>
                  <a href="#"><i class="fa-brands fa-x-twitter"></i></a>
                  <a href="#"><i class="fa-brands fa-linkedin-in"></i></a>
                  <a href="#"><i class="fa-brands fa-pinterest-p"></i></a>
                </div> -->
            </div>

            <div class="links important">
                <h4>روابط مهمة</h4>
                <div class="line"></div>

                <ul>
                    <li><a href="#about-section">من نحن</a></li>
                </ul>
            </div>

            <div class="links">
                <h4>الدعم</h4>
                <div class="line"></div>

                <ul>
                    <li><a href="#">الدعم</a></li>
                </ul>
            </div>

        </div>

        <div class="lowerFooter container">
            <div class="copy">
                جميع الحقوق محفوظة لعام 2025 © تم برمجتها بواسطة
                <a href="#">Matrix Clouds</a>
            </div>
            <ul>
                <li><a href="#about-section">من نحن</a></li>
                <li><a href="#">شروط الاستخدام</a></li>
                <li><a href="#contact-us">اتصل بنا</a></li>
            </ul>
        </div>
    </div>
</footer>
<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/10.3.0/swiper-bundle.min.js"
        integrity="sha512-QokzG/B/9i5X3BYbmuyNn2ah9EiApK5KY4saOYZRCQINuB+X52ED0L3RCc/1x7YUA85qaFZ9uoB4x5SmkLGCJQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<!-- Bootstrap JS -->
<script src="{{asset('/')}}/front/js/bootstrap.bundle.min.js"></script>
<!-- jQuery -->
<script src="{{asset('/')}}/front/js/jquery-3.7.1.js"></script>
<!-- Custom JS -->
<script src="{{asset('/')}}/front/js/main.js"></script>

</html>
</body>

</html> --}}

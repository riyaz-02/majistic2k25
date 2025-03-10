<section id="proshows-carousel">
  <div class="swiper1">
    <div class="swiper-wrapper">
      <div class="swiper-slide">
        <img src="../images/carousel/snig1.png" alt="Snigdhajit and Liveline" loading="lazy" />
        <!-- <div class="slide-content">
          <h1>Snigdhajit and Liveline</h1>
          <p>An electrifying performance by Snigdhajit and Liveline.</p>
        </div> -->
      </div>
      <div class="swiper-slide">
        <img src="../images/carousel/somlata1.png" alt="Somlata and Aces" loading="lazy" />
        <!-- <div class="slide-content">
          <h1>Somlata and Aces</h1>
          <p>Experience the magic of Somlata and Aces.</p>
        </div> -->
      </div>
      <div class="swiper-slide">
        <img src="../images/carousel/trap1.png" alt="TRAP" loading="lazy" />
        <!-- <div class="slide-content">
          <h1>TRAP</h1>
          <p>Get ready for an unforgettable show by TRAP.</p>
        </div> -->
      </div>
    </div>
    <!-- Navigation arrows -->
    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>
    <!-- Pagination -->
    <div class="swiper-pagination"></div>
  </div>
</section>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js" type="module"></script>
<script src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js" nomodule></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var swiper = new Swiper(".swiper1", {
      slidesPerView: 1,
      spaceBetween: 0,
      loop: true,
      centeredSlides: true,
      autoHeight: true, // Add this to enable auto height based on slides
      autoplay: {
        delay: 5000,
        disableOnInteraction: false
      },
      pagination: {
        el: ".swiper-pagination",
        clickable: true,
      },
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
      },
      effect: "fade",
      fadeEffect: {
        crossFade: true
      },
      keyboard: {
        enabled: true
      }
    });
  });
</script>

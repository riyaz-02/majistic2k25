<section id="proshows-carousel" class="fade-in">
  <div class="swiper1">
    <div class="swiper-wrapper">
      <div class="swiper-slide">
        <div class="gradient"></div>
        <h1 data-animate="bottom" class="title">Snigdhajit and Liveline</h1>
        <div class="description-box">An electrifying performance by Snigdhajit and Liveline.</div>
        <img class="hero" src="../images/carousel/snig.png" alt="Snigdhajit and Liveline" loading="lazy" />
      </div>
      <div class="swiper-slide">
        <div class="gradient"></div>
        <h1 data-animate="bottom" class="title">Somlata and Aces</h1>
        <div class="description-box">Experience the magic of Somlata and Aces.</div>
        <img class="hero" src="../images/carousel/somlata.png" alt="Somlata and Aces" loading="lazy" />
      </div>
      <div class="swiper-slide">
        <div class="gradient"></div>
        <h1 data-animate="bottom" class="title">TRAP</h1>
        <div class="description-box">Get ready for an unforgettable show by TRAP.</div>
        <img class="hero" src="../images/carousel/trap.png" alt="TRAP" loading="lazy" />
      </div>
    </div>
  </div>
</section>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js" type="module"></script>
<script src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js" nomodule></script>
<script>
  var swiper = new Swiper(".swiper1", {
    effect: "fade",
    fadeEffect: {
      crossFade: true
    },
    grabCursor: true,
    centeredSlides: true,
    slidesPerView: "auto",
    keyboard: {
      enabled: true
    },
    mousewheel: {
      forceToAxis: true,
      releaseOnEdges: true
    },
    spaceBetween: 60,
    loop: true,
    pagination: {
      el: ".swiper-pagination",
      clickable: true
    }
  });

  // Fade-in and fade-out animation on scroll
  document.addEventListener('DOMContentLoaded', function() {
    const proshowsSection = document.querySelector('#proshows-carousel');
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          proshowsSection.classList.add('visible');
        } else {
          proshowsSection.classList.remove('visible');
        }
      });
    }, { threshold: 0.1 });

    observer.observe(proshowsSection);
  });
</script>
<style>
  .fade-in {
    opacity: 0;
    transition: opacity 1s ease-in-out;
  }
  .fade-in.visible {
    opacity: 1;
  }
</style>

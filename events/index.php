<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events Carousel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<section id="events-carousel" class="zoom-in">
  <div class="swiper">
    <div class="swiper-wrapper">
      <div class="swiper-slide swiper-slide--one">
        <span>TAAL SE TAAL MILA</span>
        <div>
          <h2>Groove to Win, Dance to Shine!</h2>
          <div class="register-btn-container">
            <button id="registerBtn" class="register-btn">Register</button>
          </div>
        </div>
      </div>
      <div class="swiper-slide swiper-slide--two">
        <span>ACTOMANIA</span>
        <div>
          <h2>Lights, Camera, Action—Steal the Show!</h2>
          <div class="register-btn-container">
            <button id="registerBtn" class="register-btn">Register</button>
          </div>
        </div>
      </div>

      <div class="swiper-slide swiper-slide--three">
        <span>THE POETRY SLAM</span>
        <div>
          <h2>Words That Echo, Verses That Inspire!</h2>
          <div class="register-btn-container">
            <button id="registerBtn" class="register-btn">Register</button>
          </div>
        </div>
      </div>

      <div class="swiper-slide swiper-slide--four">
        <span>JAM ROOM</span>
        <div>
          <h2>Feel the Beat, Rock the Street!</h2>
          <div class="register-btn-container">
            <button id="registerBtn" class="register-btn">Register</button>
          </div>
        </div>
      </div>

      <div class="swiper-slide swiper-slide--five">
        <span>FASHION FIESTA</span>
        <div>
          <h2>Walk the Ramp, Own the Glam!</h2>
          <div class="register-btn-container">
            <button id="registerBtn" class="register-btn">Register</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Add Pagination -->
    <div class="swiper-pagination"></div>
  </div>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/8.4.5/swiper-bundle.min.js"></script>
<script>
    var swiper = new Swiper(".swiper", {
  effect: "coverflow",
  grabCursor: true,
  centeredSlides: true,
  slidesPerView: "auto",
  coverflowEffect: {
    rotate: 0,
    stretch: 0,
    depth: 100,
    modifier: 2,
    slideShadows: true
  },
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
  },
  autoplay: {
    delay: 3000,
    disableOnInteraction: false
  }
});

// Enable page scrolling when scrolling up or down
document.querySelector('.swiper').addEventListener('wheel', function(event) {
  if (event.deltaY !== 0) {
    window.scrollBy(0, event.deltaY);
  }
});

// Zoom-in and zoom-out animation on scroll
document.addEventListener('DOMContentLoaded', function() {
  const eventsSection = document.querySelector('#events-carousel');
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        eventsSection.classList.add('zoom-in');
        eventsSection.classList.remove('zoom-out');
      } else {
        eventsSection.classList.add('zoom-out');
        eventsSection.classList.remove('zoom-in');
      }
    });
  }, { threshold: 0.1 });

  observer.observe(eventsSection);
});
</script>
</body>
</html>